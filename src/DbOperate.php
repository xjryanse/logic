<?php
namespace xjryanse\logic;

use think\Db;
use think\facade\Cache;
use xjryanse\logic\Cachex;
use xjryanse\logic\Debug;
use xjryanse\logic\DbOperate;
use xjryanse\logic\Runtime;
use xjryanse\logic\Strings;
use xjryanse\logic\Arrays;
use Exception;
/**
 * 数据库操作类库
 */
class DbOperate
{
    // 20231014表绑定类名：一般适用于分表
    public static $bindServiceObj = [];
    
    public static function createTableSql( $tableName ){
        $createTableSql = Db::cache(60)->query("show create table ". $tableName );
        return $createTableSql[0]['Create Table'];
    }
    
    /**
     * 2024-08-24：创建数据表
     * $fields[]=[
     * 'fieldName'=>'school_id'
     * ,'type'=>'char'
     * ,'length'=>'19'
     * ,'charSet'=>'utf8'
     * ,'collage'=>'utf8_general_ci'
     * ,'default'=>''
     * ,'comment'=>''
     * ,'nullable'=>1
     * ];
     */
    public static function createTable($tableName, $fields = [], $tbComment = ''){
        if(self::isTableExist($tableName)){
            throw new Exception('数据表已存在不可操作'.$tableName);
        }

        $sql = 'CREATE TABLE `'.$tableName.'` (';
        $fArr = [];
        // $fArr[] = '`id` char(19) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL';
        foreach($fields as &$f){
            $fieldName  = Arrays::value($f, 'fieldName');
            $type       = Arrays::value($f, 'type');
            // 可空
            $nullable   = Arrays::value($f, 'nullable', true);
            $length     = Arrays::value($f, 'length');
            $charSet    = Arrays::value($f, 'charSet', 'utf8');
            $collage    = Arrays::value($f, 'collage', 'utf8_general_ci');
            // '\'\'' 空字符串
            $default    = isset($f['default']) 
                    ? ($f['default'] === '' ? '\'\'' : $f['default']) 
                    : 'null' ;
            $comment    = Arrays::value($f, 'comment');

            $tmp = '`'.$fieldName.'` '.$type;
            if($length && $length <= 255){
                $tmp .= '('.$length.')';
            }
            if(($length && !in_array($type,['int','tinyint']) ) || in_array($type,['text'])){
                $tmp .= ' CHARACTER SET '.$charSet.' COLLATE '.$collage;
            }
            if(!$nullable){
                $tmp .= ' NOT NULL';
            } else {
                $tmp .= ' DEFAULT '.$default;
            }

            $tmp .= ' COMMENT \''.$comment. '\'';
            $fArr[] = $tmp;
        }
        $fArr[] = '  PRIMARY KEY (`id`) USING BTREE';
        $sql .= implode(',',$fArr);
        $sql .= ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT=\''.$tbComment.'\'';
        // dump($sql);exit;
        return Db::execute($sql);
    }
    
    /**
     * 判断数据表是否存在
     * @return type
     */
    public static function isTableExist( $tableName )
    {
        return in_array($tableName, self::allTableNames());
    }
    
    private static function allTableArrCacheKey(){
        return __CLASS__.'::allTableArr';
    }
    /**
     * 获取库中所有的数据表
     */
    public static function allTableArr(){
        $cacheKey = self::allTableArrCacheKey();
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
     * 20231019：所有的分表
     */
    public static function allSubTableNames($tableName){
        $allTableNames = self::allTableNames();
        $arr = [];
        foreach($allTableNames as $table){
            if(strpos($table, $tableName) !== false ){
                $arr[] = $table;
            }
        }
        return $arr;
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
        // 20230915:增加判断
        if(!self::isTableExist($tableName)){
            return [];
        }
        $cacheFile = Runtime::tableColumnFileName($tableName);
        if(is_file($cacheFile)){
            return Runtime::dataFromFile($cacheFile);
            // return include $cacheFile;
        }
        // 没有缓存文件，按原方式提取
        $cacheKey = __CLASS__.__METHOD__;
        return Cachex::funcGet( $cacheKey.'_'.$tableName, function() use ($tableName){
            // 20230905:尝试优化
            // $sql = 'DESCRIBE '.$tableName;
            // $tableColumn = Db::query($sql);
            
            $sql = "select * from information_schema.COLUMNS "
                    . "WHERE table_name ='" . $tableName . "'";
            $tableColumn = Db::query( $sql );
            foreach($tableColumn as &$v){
                $v['Field']         = $v['COLUMN_NAME'];
                // 字串最大长度
                $v['charMaxLength'] = $v['CHARACTER_MAXIMUM_LENGTH'];
            }
            
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
            if($value['EXTRA'] != 'VIRTUAL GENERATED'){
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
        // 20231014:有绑定取绑定
        if(Arrays::value(self::$bindServiceObj, $tableName)){
            return Arrays::value(self::$bindServiceObj, $tableName);
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
     * 控制器和admKey取表
     */
    public static function controllerAdmKeyToTable($controller, $admKey){
        $tableArr   = [];
        $tableArr[] = lcfirst($controller);
        if($admKey !='index'){
            $tableArr[] = Strings::uncamelize($admKey);
        }
        return self::prefix().implode('_',$tableArr);
    }
    
    /**
     * 20231026：提取逻辑处理类库
     * @param type $module  模块名：例order
     * @param type $name    处理类名：例bao
     * @return string
     */
    public static function getLogic( $module, $name )
    {
        if(!$module || !$name){
            return '';
        }
        // BaoLogic
        $logicName = ucfirst($name.'Logic');
        
        $logicArr[] = '\\app\\'.$module.'\\logic\\'.$logicName;
        $logicArr[] = '\\xjryanse\\'.$module.'\\logic\\'.$logicName;            

        foreach($logicArr as $serv){
            if(class_exists($serv)){
                return $serv;
            }
        }

        return $logicArr[0];
    }
    /**
     * 20240121：自定义单独使用traits的目录
     */
    public static function traitsDir($tableName){
        $arr = explode('_',$tableName);
        // 移除w
        array_shift($arr);
        // 移除order
        array_shift($arr);
        // array_shift();
        // 空数组返回index
        if(!$arr){
            return 'index';
        }
        return Strings::camelize(implode('_',$arr));
    }
    /**
     * 20231014:分表绑定类名；
     */
    public static function bindService($tableName, $service){
        self::$bindServiceObj[$tableName] = $service;
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
    public static function saveAllSql($tableName,$dataRaw,$covData=[], $isCover = false)
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
        
        if($isCover){
            // 20231204:覆盖更新
            $updateStrs = [];
            foreach($fieldStrs as $ve){
                $updateStrs[] = $ve.'= VALUES('.$ve.')';
            }
            $sql = "INSERT INTO ". $tableName ." (". $fieldStr .") 
                VALUES ".$dataStr.' ON DUPLICATE KEY UPDATE '.implode(',',$updateStrs);
        } else {
            $sql = "INSERT IGNORE INTO ". $tableName ." (". $fieldStr .") 
                VALUES ".$dataStr;
        }
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
            $arr[] = '0 + round(cast(sum(`'.$v.'`) as char),2) as `'.$v.'`';
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
        // Debug::dump('pdoQuery的sql',$sql);
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
            //【3】删除的数据
            // 20230921:发现没删的写有bug，唯一约束
            foreach($glDeleteData as $tableName=>$ids){
                $con = [];
                $con[] = ['id','in', array_unique($ids)];
                Db::table($tableName)->where($con)->delete();
            }
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
            //【4】执行自定义sql
            $glSqlQueryU = array_unique($glSqlQuery);
            foreach($glSqlQueryU as $sql){
                Db::execute($sql);
            }
        Debug::debug('$glSaveData',$glSaveData,'DbOperate');
        Debug::debug('$glUpdateData',$glUpdateData,'DbOperate');
        Debug::debug('$glDeleteData',$glDeleteData,'DbOperate');
        // exit;
        Db::commit();
        // 20231116:执行完毕后清空
        $glSaveData     = [];
        $glUpdateData   = [];
        $glDeleteData   = [];
        $glSqlQuery     = [];
        return true;
    }
    /**
     * 增加一条全局执行sql
     * @global array $glSqlQuery
     * @param type $sql
     * @return bool
     */
    public static function pushGlobalSql($sql){
        global $glSqlQuery;
        //扔一条sql到全局变量，方法执行结束后执行
        $glSqlQuery[] = $sql;
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
    /**
     * 20230914:提取指定数据表处于全局删除中的id
     * @param type $tableName
     */
    public static function tableGlobalDeleteIds($tableName){
        global $glDeleteData;
        return Arrays::value($glDeleteData, $tableName,[]);
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
     * 
     * @createTime 2023-05-28
     * @param type $con
     * @return type
     */
    public static function uniAttrConfArr($con = []){
        $cacheKey = __METHOD__;
        $listsAll = Cachex::funcGet($cacheKey, function(){
            $fieldsArr = self::uniFieldsArr();
            $objAttrs = [];
            foreach($fieldsArr as $v){
                $tmp                = Arrays::getByKeys($v, ['thisTable','uniTable','property','existField']);
                $tmp['baseClass']   = self::getService(Arrays::value($v, 'uniTable'));
                $tmp['class']       = self::getService(Arrays::value($v, 'thisTable'));
                $tmp['keyField']    = Arrays::value($v, 'field');
                // TODO先默认主库
                $tmp['master']      = 1; 
                // 20230608：
                $tmp['inList']      = Arrays::value($v, 'in_list') ? 1 : 0;
                $tmp['inStatics']   = Arrays::value($v, 'in_statics') ? 1 : 0;
                $tmp['inExist']     = Arrays::value($v, 'in_exist') ? 1 : 0; 
                // 20230726
                $tmp['uniField']    = Arrays::value($v, 'uni_field' ,'id'); 
                // 20230807：匹配条件
                $tmp['condition']   = Arrays::value($v, 'condition',[]); 
                // 20241130
                $tmp['countField']  = Arrays::value($v, 'countField'); 
                
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
            // 20230914：排除已删除，未指向的项目
            $deletedIds = self::tableGlobalDeleteIds($thisTable);
            if($deletedIds){
                $tCon[] = ['id','not in',$deletedIds];
            }
            // 20231102:设置分表:固定用setConTable了
            if(method_exists($tService::mainModel(), 'setConTable')){
                $tService::mainModel()->setConTable($tCon);
            }
            // dump($tService::mainModel()->getTable());exit;
            // 校验是否有数据
            // 20241208:增加内存判断优化性能
            $property  = Arrays::value($field, 'property');
            $uniTable  = Arrays::value($field, 'uniTable'); 
            $uService  = self::getService($uniTable);
            if($uniField == 'id' && $property && $uService && method_exists($uService, 'objAttrExist') 
                    && $uService::objAttrExist($property)){
                // eg: $this->objAttrsList('financeVoucherDtl');
                $count = count($uService::getInstance($thisValue)->objAttrsList($property));
            } else {
                // 20241208:原来的判断方法
                $count = $tService::where($tCon)->count();
            }
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
    
    public static function checkCanDeleteBatch($tableName, $id ){
        if(!is_array($id)){
            $id = [$id];
        }
        foreach($id as $i){
            self::checkCanDelete($tableName, $i);
        }
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
    /**
     * 20231014：字段异常抛出
     * @param type $table
     * @param type $record
     * @param type $field
     */
    public static function fieldErr($table,$record,$field){
        throw new Exception('数据异常:'.$table.':'.$record.':'.$field);
    }
    /**
     * 20231014:复制某表结构，一般用于创建分表
     */
    public static function copyTableStruct($table, $newTableName){
        $sql = 'CREATE TABLE '.$newTableName.' LIKE '.$table;
        $res = Db::query( $sql );
        // 清理数据表全量缓存
        Cachex::rm(self::allTableArrCacheKey());
        return $res;
    }
    
    /**
     * 取分表(进行一系列创建，绑定等动作)
     * @createTime 2023-10-14
     * @param string $rawTable  源表
     * @param type $subFix      后缀
     * @throws Exception
     */
    public static function getSepTable($rawTable, $subFix){
        if(!$rawTable){
            throw new Exception('源表不存在:'.$rawTable);
        }
        $table = $rawTable;
        if(!$subFix){
            return $table;
        }

        $table .= '_'.$subFix;
        // 20231014:绑定类名为源表类，避免找不到
        $rawService = self::getService($rawTable);
        self::bindService($table, $rawService);

        if(!self::isTableExist($table)){
            // 复制数据表结构
            self::copyTableStruct($rawTable, $table);
        }

        return $table;
    }
    /**
     * 保留数据表有的字段条件（关联表内查字段）
     * 仅适用标准查询条件
     * @createTime 2023-10-20
     */
    public static function keepHasFieldCon($con, $tableName){

        foreach($con as $k=>$v){
            if(!self::hasField($tableName, $v[0])){
                unset($con[$k]);
            }
        }

        return $con;
    }
    /**
     * 20231207:比service类方法更加灵活
     * @param type $key
     * @param type $keyIds
     * @param type $sumField
     * @param type $con
     * @return type
     */
    public static function groupBatchSum($tableSql, $key, $keyIds, $sumField, $con = []) {
        $con[] = [$key, 'in', $keyIds];
//        if (self::mainModel()->hasField('is_delete')) {
//            $con[] = ['is_delete', '=', 0];
//        }

        return Db::table($tableSql)->where($con)->group($key)->column('sum(' . $sumField . ')', $key);
    }
    /**
     * 20240104：生成关联查询sql
        $arr[] = ['table_name'=>'w_system_company_dept','alias'=>'tA'];
        $arr[] = ['table_name'=>'w_system_company_job','alias'=>'tB','join_type'=>'inner','on'=>'tA.id=tB.dept_id'];
     * @param type $fields
     * @param type $arr
     * @param type $groupFields
     * @return string
     */
    public static function generateJoinSql($fields, $arr = [], $groupFields = [], $con = [], $orderBy = '', $whereFields = [], $havingFields = []){
//        $arr[] = ['table_name'=>'w_system_company_dept','alias'=>'tA'];
//        $arr[] = ['table_name'=>'w_system_company_job','alias'=>'tB','join_type'=>'inner','on'=>'tA.id=tB.dept_id'];
        $tSql = self::generateJoinTable($arr);
        
        if($con){
            $whereFields[]     = ModelQueryCon::conditionParse($con);
        }
        // 20240122
        if($whereFields){
            $tSql   .= ' where '.implode(' and ', $whereFields);
        }

        // 聚合
        $groupStr = $groupFields ? implode(',',$groupFields) : '';
        if($groupStr){
            $tSql .= ' group by '. $groupStr;
        }
        
        // 20240126
        if($havingFields){
            // dump($havingFields);
            $tSql   .= ' having '.implode(' and ', $havingFields);
        }

        // 返回字段
        $fieldStr = $fields ? implode(',',$fields) : '*';

        $sqlFinal = 'select '.$fieldStr. ' from '. $tSql ;        
        if($orderBy){
            $sqlFinal .= ' order by '. $orderBy;
        }
        // Debug::dump($sqlFinal);

        return $sqlFinal;
    }
    /**
     * 只生成关联表
     * 20240107
     * @param type $fields
     * @param type $arr
     * @param type $groupFields
     * @param type $con
     * @param type $orderBy
     * @return string
     */
    private static function generateJoinTable($arr = []){
//        $arr[] = ['table_name'=>'w_system_company_dept','alias'=>'tA'];
//        $arr[] = ['table_name'=>'w_system_company_job','alias'=>'tB','join_type'=>'inner','on'=>'tA.id=tB.dept_id'];

        $tSql = '';
        foreach($arr as $v){
            if(Arrays::value($v, 'join_type')){
                $tSql .= ' '. $v['join_type'] .' join ';
            }
            $tSql .= $v['table_name'];
            if($v['alias']){
                $tSql .= ' as '. $v['alias'] .' ';
            }
            if(Arrays::value($v, 'on')){
                $tSql .= ' on '. $v['on'] .' ';
            }
        }

        return $tSql;
    }
    
    /**
     * 生成关联的union语句
     * @param type $tables    ['table1','table2','table3'];
     * @param type $fieldArr    [
     *      'id'    =>['id','id','id']
     *      'bus_id'=>['bus_id','bus_id','bus_id']
     * ]
     * @param type $whereArr
     */
    public static function generateUnionSql($tables, $fieldArr, $whereArr = [], $groupFields = []){
        $sqlArr = [];
        foreach($tables as $i=> $table){
            // 20240323:当i不是数字时，表示是表别名
            $alias  = is_numeric($i) ? 't'.$i : $i;
            $fields = [];
            // 字段
            foreach($fieldArr as $k=>$v){
                $fields[] = $v[$i] . ' as ' . $k;
            }
            // 条件
            $con = isset($whereArr[$i]) ? $whereArr[$i] : [] ;
            $inst = Db::table($table)->alias($alias)->where($con)->field(implode(',',$fields));
            // 20240429
            $groupFArr = isset($groupFields[$i]) ? $groupFields[$i] : [] ;
            // 聚合
            $groupStr = $groupFArr ? implode(',',$groupFArr) : '';
            if($groupStr){
                $inst->group($groupStr);
            }
            
            $sqlArr[] = $inst->buildSql();
        }

        return '('.implode(' union ', $sqlArr).')';
    }
    
    
}
