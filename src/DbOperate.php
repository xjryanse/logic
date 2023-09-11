<?php
namespace xjryanse\logic;

use think\Db;
use think\facade\Cache;
use xjryanse\logic\Cachex;
use xjryanse\logic\Debug;
use xjryanse\logic\DbOperate;
use xjryanse\logic\Runtime;
use xjryanse\logic\Strings;
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
        return in_array($tableName, self::allTableNames());
    }
    /**
     * 获取库中所有的数据表
     */
    public static function allTableArr(){
        $cacheKey = __METHOD__;
        return Cachex::funcGet($cacheKey, function(){
            $database     = config('database.database');
            $sql = "SELECT
                    TABLE_NAME as `table`
                FROM
                    information_schema.`TABLES` 
                WHERE
                    table_schema = '".$database."'";
            $tables = Db::query($sql);
            // 20230619:干脆加一个字段
            foreach($tables as &$v){
                $arr            = explode('_', $v['table']);
                $v['module']    = Arrays::value($arr, 1,'');
            }

            return $tables;
        });
    }
    /**
     * 所有的数据表名
     */
    public static function allTableNames(){
        return array_column(self::allTableArr(), 'table');
    }
    /**
     * 20230728：数据需要写入文件缓存的表名
     */
    public static function cacheToFileTables(){
        // if(!property_exists($class, $property))
        return Cachex::funcGet(__METHOD__, function(){
            $tables = self::allTableNames();
            $arrs = [];
            foreach($tables as $table){
                $service = self::getService($table);
                if(!class_exists($service)){
                    continue;
                }
                // 存储在模型类中
                $modelClass  = $service::mainModelClass();
                if(!class_exists($modelClass) || !property_exists($modelClass, 'cacheToFile')){
                    continue;
                }
                if(!$modelClass::$cacheToFile){
                    continue;
                }
                $arrs[] = $table;
            }
            return $arrs;
        });
    }
    /**
     * 提取所有模块信息
     * @createTime 2023-06-19 08:35:00
     */
    public static function allModules(){
        return array_unique(array_column(self::allTableArr(), 'module'));
    }
    /**
     * 用于替代show columns from 的sql语句
     */
    public static function columns($tableName){
        $cacheFile = Runtime::tableColumnFileName($tableName);
        if(is_file($cacheFile)){
            return Runtime::dataFromFile($cacheFile);
            // return include $cacheFile;
        }
        // 没有缓存文件，按原方式提取
        $cacheKey = __CLASS__.__METHOD__;
        return Cachex::funcGet( $cacheKey.'_'.$tableName, function() use ($tableName){
//            $database     = config('database.database');
//            $sql = "SELECT
//                    table_name,
//                    column_name AS Field,
//                    column_type AS Type,
//                    is_nullable AS `Null`,
//                    column_key AS `Key`,
//                    column_default AS `Default`,
//                    extra as Extra ,
//                    COLUMN_COMMENT
//                FROM
//                    information_schema.`COLUMNS` 
//                WHERE
//                    table_schema = '".$database."' and TABLE_NAME = '".$tableName."'";
//            $tableColumn = Db::query($sql);
//            dump($tableColumn);
//            
            // 20230905:尝试优化
            $sql = 'DESCRIBE '.$tableName;
            $tableColumn = Db::query($sql);

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
     * w_view_store_dtl
     * 优先级
     * app\store\service\ViewStoreDtlService
     * app\view\service\ViewStoreDtlService
     * xjryanse\store\service\ViewStoreDtlService
     * xjryanse\view\service\ViewStoreDtlService
     * 
     */
    public static function getService( $tableName )
    {
        if(!$tableName){
            return '';
        }

        $res = explode('_',$tableName);
        $isView = $res[1] == 'view';
        $module = $isView ? $res[2] : $res[1];
        foreach($res as &$v){
            $v = ucfirst($v);
        }
        //去除前缀
        unset($res[0]);
        //拼接类名
        $serviceName = implode('',$res).'Service';
        $serviceArr = [];
        if($isView){
            $serviceArr[] = '\\app\\'.$module.'\\service\\'.$serviceName;
            $serviceArr[] = '\\app\\view\\service\\'.$serviceName;
            $serviceArr[] = '\\xjryanse\\'.$module.'\\service\\'.$serviceName;
            $serviceArr[] = '\\xjryanse\\view\\service\\'.$serviceName;
        } else {
            $serviceArr[] = '\\app\\'.$module.'\\service\\'.$serviceName;
            $serviceArr[] = '\\xjryanse\\'.$module.'\\service\\'.$serviceName;            
        }
        
        foreach($serviceArr as $serv){
            if(class_exists($serv)){
                return $serv;
            }
        }

        return $serviceArr[0];
    }
    /**
     * 当前表末条id
     * @param type $tableName
     */
    public static function lastId( $tableName ,$con = [],$cache=0 ) 
    {
        if(DbOperate::isTableExist($tableName)){
            return Db::table( $tableName )->where( $con )->order('id desc')->cache( $cache )->value('id');
        } else {
            return  '';
        }
    }
    
    /**
     * 
     * @param type $tableName
     * @param type $data        
     * @param type $covData     转换参数
     * @return string
     */
    public static function saveAllSql($tableName,$dataRaw,$covData=[])
    {
        // 2022-11-27：只保留表有实字段
        $realFieldArr   = self::realFieldsArr($tableName);
        $data           = Arrays2d::getByKeys($dataRaw, $realFieldArr);
        // 原
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
            //20220718:解决关键词字段bug
            // $arr[] = 'sum(`'.$v.'`) as `'.$v.'`';
            // 20221025：去除多余0
            $arr[] = '0 + cast(sum(`'.$v.'`) as char) as `'.$v.'`';
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
        Debug::debug('pdoQuery的sql',$sql);
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
            // dump($glUpdateData);
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
                    Db::execute($sql);
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
                Db::execute($sql);
            }
        Debug::debug('$glSaveData',$glSaveData,'DbOperate');
        Debug::debug('$glUpdateData',$glUpdateData,'DbOperate');
        Debug::debug('$glDeleteData',$glDeleteData,'DbOperate');
        // exit;
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
    /**
     * 是否在全局添加中
     */
    public static function isGlobalSave($tableName,$id){
        global $glSaveData;
        $saveDatas = Arrays::value($glSaveData, $tableName, []);
        return in_array($id, array_column($saveDatas,'id'));
    }
    
    /****** 给框架用的 *********/
    /**
     * 获取控制器
     * @param type $tableName
     * @return type
     */
    public static function getController($tableName){
        $tableArr = explode('_', $tableName);
        return $tableArr[1];
    }
    /**
     * 获取表key
     * @param type $tableName
     * @return type
     */
    public static function getTableKey($tableName){
        $tableArr = explode('_', $tableName);
        unset($tableArr[0]);
        unset($tableArr[1]);
        return camelize(implode('_',$tableArr)) ? :'index';
    }
    /**
     * 用于接口输出隐藏的key，精简数据，节约带宽
     */
    public static function keysForHide($extraKeys = []){
        $keys = ['has_used','is_lock','is_delete','remark','creater','updater','create_time','update_time'];
        return array_merge($keys, $extraKeys);
    }
    /**
     * 20230518：提取全系统配置数组
     * @return type
     */
    public static function objAttrConfArr($con = []){
        $cacheKey = __METHOD__;
        $listsAll = Cachex::funcGet($cacheKey, function(){
            $tables = self::allTableNames();
            $objAttrs = [];
            foreach($tables as $table){
                $service = self::getService($table);
                if (method_exists($service, 'objAttrConfArr')) {
                    $tmpAttrs = $service::objAttrConfArr();
                    $objAttrs = array_merge($objAttrs,$tmpAttrs);
                }
            }
            return $objAttrs;
        });
        return Arrays2d::listFilter($listsAll, $con);
    }

    /**
     * 20230528：提取全系统配置数组（注入模式）
baseClass: "xjryanse\customer\service\CustomerService"
class: "xjryanse\customer\service\CustomerAnliService"
keyField: "customer_id"
master: true
property: "customerAnli"
     * 
     */
//    public static function uniAttrConfArr($con = []){
//        $cacheKey = __METHOD__;
//        $listsAll = Cachex::funcGet($cacheKey, function(){
//            $tables = self::allTableNames();
//            $objAttrs = [];
//            foreach($tables as $table){
//                $service = self::getService($table);
//                if (method_exists($service, 'uniAttrConfArr')) {
//                    $tmpAttrs = $service::uniAttrConfArr();
//                    $objAttrs = array_merge($objAttrs,$tmpAttrs);
//                }
//            }
//            return $objAttrs;
//        });
//        return Arrays2d::listFilter($listsAll, $con);
//    }
    
    public static function uniAttrConfArr($con = []){
        $cacheKey = __METHOD__;
        $listsAll = Cachex::funcGet($cacheKey, function(){
            $fieldsArr = self::uniFieldsArr();
            $objAttrs = [];
            foreach($fieldsArr as $v){
                $tmp                = [];
                $tmp['baseClass']   = self::getService(Arrays::value($v, 'uniTable'));
                $tmp['class']       = self::getService(Arrays::value($v, 'thisTable'));
                $tmp['keyField']    = Arrays::value($v, 'field');
                // TODO先默认主库
                $tmp['master']      = true; 
                $tmp['property']    = Arrays::value($v, 'property'); 
                // 20230608：
                $tmp['inList']      = Arrays::value($v, 'in_list');
                $tmp['inStatics']   = Arrays::value($v, 'in_statics');
                $tmp['inExist']     = Arrays::value($v, 'in_exist'); 
                // 20230726
                $tmp['uniField']    = Arrays::value($v, 'uni_field' ,'id'); 
                // 20230608
                $tmp['existField']  = Arrays::value($v, 'existField'); 
                // 20230807：匹配条件
                $tmp['condition']   = Arrays::value($v, 'condition',[]); 
                $objAttrs[]         = $tmp;
            }
            return $objAttrs;
        });
        return Arrays2d::listFilter($listsAll, $con);
    }
    
    /**
     * 20230528：提取全系统触发钩子（注入模式）
     */
    public static function triggerArr($con = []){
        $cacheKey = __METHOD__;
        $listsAll = Cachex::funcGet($cacheKey, function(){
            $tables = self::allTableNames();
            $triggers = [];
            foreach($tables as $table){
                $service = self::getService($table);
                if (property_exists($service, 'trigger')) {
                    $tmpAttrs = $service::confArrTrigger();
                    $triggers = array_merge($triggers,$tmpAttrs);
                }
            }
            return $triggers;
        });
        return Arrays2d::listFilter($listsAll, $con);
    }
    
    /**
     * 20230601：关联字段，控制联动删除
     * @return type
     */
    public static function uniFieldsArr($con = []){
        // if(!property_exists($class, $property))
        $cacheKey = __METHOD__;
        $listsAll = Cachex::funcGet($cacheKey, function(){
            $tables = self::allTableNames();
            $arrs = [];
            foreach($tables as $table){
                $service = self::getService($table);
                if(!class_exists($service)){
                    continue;
                }
                // 存储在模型类中
                $modelClass  = $service::mainModelClass();
                if(!class_exists($modelClass) || !property_exists($modelClass, 'uniFields')){
                    continue;
                }
                $tmpAttrs = $modelClass::uniFieldsArr();
                $arrs = array_merge($arrs,$tmpAttrs);
            }
            return $arrs;
        });
        return Arrays2d::listFilter($listsAll, $con);
    }
    /**
     * 20230601:验证某张表某条记录是否可被删除
     * @param type $tableName       w_user
     * @param type $id              XXX
     * @return boolean
     */
    public static function checkCanDelete($tableName, $id ){
        $service = self::getService($tableName);
        if(!$tableName || !class_exists($service)){
            return true;
        }
        $info = $service::getInstance($id)->get();
        if(!$info){
            return true;
        }
        $con[] = ['uniTable','=',$tableName];
        $uniFields = self::uniFieldsArr($con);
        foreach($uniFields as $field){
            // 不需要删除校验则不校验
            if(!$field['del_check']){
                continue;
            }

            $thisTable  = Arrays::value($field, 'thisTable'); 
            $thisField  = Arrays::value($field, 'field');
            // 一般是id
            $uniField   = Arrays::value($field, 'uni_field');
            $thisValue  = Arrays::value($info, $uniField);
            
            $tService   = self::getService($thisTable);
            $tCon = [];
            $tCon[] = [$thisField,'=',$thisValue];
            $count = $tService::where($tCon)->count();
            if($count){
                $msgRaw = Arrays::value($field, 'del_msg','数据已使用不可删');
                
                $data['count'] = $count;
                //消息替换
                $msgStr = Strings::dataReplace($msgRaw, $data);
                throw new Exception($msgStr);
            }
        }
        return true;
    }
    /**
     * 
     * @param type $tableName
     * @param type $con
     * @throws Exception
     */
    public static function checkConFields($tableName,$con = []){
        foreach($con as $v){
            if(!self::hasField($tableName, $v[0])){
                throw new Exception('数据表'.$tableName.'没有'.$v[0].'字段');
            }
        }
    }
    
    /**
     * 20230605:数据表前缀
     */
    public static function prefix(){
        return config('database.prefix');
    }
    /**
     * 20230608:存在字段名称
     * circuit_id→isCircuitExist
     * @param type $fieldName
     */
    public static function fieldNameForExist($fieldName){
        $arr = explode('_',$fieldName);
        // 去除最后，一般为id
        array_pop($arr);
        // 首尾添加is 和 exist
        array_unshift($arr, 'is');
        array_push($arr,'exist');
        return Strings::camelize(implode('_',$arr));
    }
}
