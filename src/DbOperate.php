<?php
namespace xjryanse\logic;

use think\Db;
use think\facade\Cache;
use xjryanse\logic\Cachex;
use Exception;
/**
 * 数据库操作类库
 */
class DbOperate
{
    public static function createTableSql( $tableName ){
        $createTableSql = Db::cache(60)->query("show create table ". $tableName );
        return $createTableSql[0]['Create Table'];
    }
    
    /**
     * 判断数据表是否存在
     * @return type
     */
    public static function isTableExist( $tableName )
    {
        $cacheKey = __CLASS__.__METHOD__;
        $exist = Cache::get($cacheKey);
        if(!$exist){
            //判断数据表是否存在
            $exist = Db::cache(60)->query("show tables like '". $tableName ."'");
            Cache::set($cacheKey,$exist);
        }
        return $exist;
    }
    
    /**
     * 用于替代show columns from 的sql语句
     */
    public static function columns($tableName){
        $cacheKey = __CLASS__.__METHOD__;
        return Cachex::funcGet( $cacheKey.'_'.$tableName, function() use ($tableName){
            $database     = config('database.database');
            $sql = "SELECT
                    table_name,
                    column_name AS Field,
                    column_type AS Type,
                    is_nullable AS `Null`,
                    column_key AS `Key`,
                    column_default AS `Default`,
                    extra as Extra ,
                    COLUMN_COMMENT
                FROM
                    information_schema.`COLUMNS` 
                WHERE
                    table_schema = '".$database."' and TABLE_NAME = '".$tableName."'";
            $tableColumn = Db::query($sql);
//
//            $con[] = ['TABLE_NAME','=',$tableName];
//            $tableColumn = Arrays2d::listFilter($res, $con);

            return $tableColumn;
        });
    }
    
    /**
     * 数据表是否存在某字段
     */
    public static function hasField($tableName, $fieldName )
    {
        $tableColumns   = self::columns($tableName);
        $fields    = array_column( $tableColumns,'Field');
        return in_array($fieldName, $fields);
    }
    /**
     * 实际字段：排除虚拟字段
     */
    public static function realFieldsArr( $tableName )
    {
        $columns    = self::columns($tableName);
        $fieldArr   = [];
        foreach($columns as $key=>$value){
            if($value['Extra'] != 'VIRTUAL GENERATED'){
                $fieldArr[] = $value['Field'];
            }
        }
        return $fieldArr;
    }
    /**
     * 索引是否存在
     * @param type $tableName   表名
     * @param type $columnName  字段名
     */
    public static function isColumnIndexExist( $tableName ,$columnName )
    {
        $cacheKey = __CLASS__.__METHOD__.$tableName.$columnName;
        $exist = Cache::get($cacheKey);
        if(!$exist){        
            $sql = "select * from information_schema.STATISTICS "
                    . "WHERE table_name ='" . $tableName . "' "
                    . "AND COLUMN_NAME = '". $columnName ."'";
            $exist = Db::query( $sql );
            Cache::set($cacheKey,$exist);            
        }
        return $exist;        
    }
    /**
     * 字段添加索引
     */
    public static function addColumnIndex( $tableName ,$columnName ,$indexName='')
    {
        if( !self::getService( $tableName )::mainModel()->hasField( $columnName )){
            return false;
        }
        if(self::isColumnIndexExist($tableName, $columnName)){
            return false;
        }
        $indexNameK = $indexName ? "`".$indexName."`" : "";
        $sql = "ALTER TABLE `".$tableName."` ADD INDEX ". $indexNameK ."(`".$columnName."`)";

        $res = Db::query( $sql );
        return $res;                
    }
    /*
     * 表名获取对应服务类
     */
    public static function getService( $tableName )
    {
        if(!$tableName){
            return '';
        }
        //优先拿项目中的类
        $res = explode('_',$tableName);
        $str = '\\app\\'.$res[1].'\\service\\';
        foreach($res as &$v){
            $v = ucfirst($v);
        }
        //去除前缀
        unset($res[0]);
        //拼接类名
        $str .= implode('',$res).'Service';
        //项目类不存在则拿框架类
        if(!class_exists($str)){
            $str = str_replace('app','xjryanse',$str);
        }
        return $str;
    }
    /**
     * 当前表末条id
     * @param type $tableName
     */
    public static function lastId( $tableName ,$con = [],$cache=0 ) 
    {
        return Db::table( $tableName )->where( $con )->order('id desc')->cache( $cache )->value('id');
    }
    
    /**
     * 
     * @param type $tableName
     * @param type $data        
     * @param type $covData     转换参数
     * @return string
     */
    public static function saveAllSql($tableName,$data,$covData=[])
    {
        $fields = array_keys($data[0]);
        $fieldStrs = [];
        foreach($fields as $kk=>$vv){
            if(self::getService( $tableName )::mainModel()->hasField($vv)){
                $fieldStrs[] = $vv;
            }
        }
        //$fieldStr = implode(',',$fieldStrs);
        $fieldStr = '`'.implode('`,`', $fieldStrs).'`';        

        $dataStr = '';
        foreach( $data as $key=>$value){
            $resVal = [];
            foreach($value as $kk=>$vv){
                //self::getService( $tableName )::mainModel()->hasField($kk)
                if( in_array($kk,$fieldStrs)){
                    $resVal[] = (isset($covData[$kk]) && $covData[$kk][$vv]) ?  $covData[$kk][$vv] : $vv;
                }
            }
//            dump($resVal);
            if($dataStr){
                $dataStr .= ",";
            }
            $dataStr.= "('";
            $dataStr.= implode("','",$resVal);
            $dataStr.= "')";
        }
        $sql = "INSERT IGNORE INTO ". $tableName ." (". $fieldStr .") 
            VALUES ".$dataStr;
        return $sql;
    }
    /**
     * 外键是否有数据，一般用于关联删除时判断
     * @param type $foreignArrs
     * @param type $dataId
     */
    public function foreignKeyHasData( $foreignArrs, $dataId )
    {
        foreach( $foreignArrs as $tableName=>$key){
            
        }
    }
    /**
     * 过滤出数据表中有的字段（一般用于保存前处理）
     */
    public static function dataFilter( $tableName,array $data)
    {
        $tableColumns   = self::columns($tableName);
        Debug::debug('$tableColumns', $tableColumns);
        $tableFields    = array_column( $tableColumns,'Field');
        Debug::debug('$tableFields', $tableFields);
        $res            = Arrays::getByKeys($data, $tableFields);
        return $res;
    }
    /**
     * 在主表不在从表中的字段
     */
    /**
     * 
     * @param type $table       取字段的表
     * @param type $exceptTable 
     */
    public static function fieldsExceptByTable( $table,$exceptTable)
    {
        $tableColumns   = self::columns($table);
        $tableFields    = array_column( $tableColumns,'Field');
        $exceptColumns  = self::columns($exceptTable);
        $exceptFields   = array_column( $exceptColumns,'Field');
        return array_diff($tableFields, $exceptFields);
    }
    /**
     * 字段数组和表别名组合成字符串（用于sql中查询字段）
     * @param type $fields     字段
     * @param type $alias       别名
     */
    public static function fieldsAliasStr( $fields , $alias )
    {
        if($alias){
            foreach($fields as &$field){
                $field = $alias. '.' . $field;
            }
        }
        return implode( ',',$fields );
    }
    
    /*
     * 拼接求和字段
     */
    public static function sumFieldStr($fieldsArr){
        $arr = [];
        foreach($fieldsArr as $v){
            $arr[] = 'round(sum('.$v.')) as '.$v;
        }
        return implode(',',$arr);
    }
    
    /**
     * 高效率的pdo查询，一般用于大数据量导出
     */
    public static function pdoQuery($sql){
        //TODO，目前只有TP框架可用
        $dbInfo     = config('database.');
        //新建PDO连接
        $connectStr = 'mysql:host='.$dbInfo['hostname'].';port='.$dbInfo['hostport'].';dbname='.$dbInfo['database'];
        
        $pdo        = new \PDO( $connectStr , $dbInfo['username'], $dbInfo['password']);
        $pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        //防中文乱码
//        $pdo->query("set names 'utf8'");
        $pdo->query("set names 'ANSI'");

        $rows = $pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        return $rows;
    }
    /**
     * 应在控制器层最外循环结尾调用，并加事务
     * 如何解决锁的问题？？
     */
    public static function dealGlobal(){
        Db::startTrans();
            global $glSaveData, $glUpdateData, $glDeleteData, $glSqlQuery;
            //【1】保存的数据
            foreach($glSaveData as $tableName=>$dataArr){
                //20220621;解决批量字段不同步bug                
                $saveArr = [];
                foreach($dataArr as $id=>$data){
                    $keys = array_keys($data);
                    sort($keys);
                    ksort($data);
                    $keyStr = md5(implode(',', $keys));
                    $saveArr[$keyStr][] = $data;
                }
                // 20220621
                foreach($saveArr as $k=>$arr){
                    $sql = self::saveAllSql($tableName, array_values($arr));
                    Db::query($sql);
                }
            }
            //【2】更新的数据
            foreach($glUpdateData as $tableName=>$dataArr){
                foreach($dataArr as $id=>$data){
                    Db::table($tableName)->where('id',$id)->update($data);
                }
            }
            //【3】删除的数据
            foreach($glDeleteData as $tableName=>$ids){
                $con = [];
                $con[] = ['id','in', array_unique($ids)];
                Db::table($tableName)->where($con)->delete();
            }
            //【4】执行自定义sql
            $glSqlQuery = array_unique($glSqlQuery);
            foreach($glSqlQuery as $sql){
                Db::query($sql);
            }
            
//        dump('$glSaveData');
//        dump($glSaveData);
//        dump('$glUpdateData');
//        dump($glUpdateData);
//        dump('$glDeleteData');
//        dump($glDeleteData);
        // throw new Exception('测试');
        Db::commit();
        return true;
    }
    /**
     * 是否在全局删除中
     */
    public static function isGlobalDelete($tableName,$id){
        global $glDeleteData;
        $delDatas = Arrays::value($glDeleteData, $tableName, []);
        return in_array($id, $delDatas);
    }
}
