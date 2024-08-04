<?php
namespace xjryanse\logic;

use xjryanse\logic\DbOperate;
use xjryanse\logic\File;
use think\Container;
use think\Db;
use Exception;
/**
 * 生成文件
 */
class Runtime
{
    /**
     * 生成模型和服务层类
     * @param type $tableName   表名
     * @param type $modelDesc   描述
     * @param type $prefix      支持前缀
     */
    public static function generate( )
    {
        $tableName = 'w_user';
        $columns = DbOperate::columns($tableName);
        
        $content = '<?php ' . PHP_EOL . 'return ';
        $content .= var_export($columns, true) . ';';

        file_put_contents(Container::get('app')->getRuntimePath() . 'xjryanse' . DIRECTORY_SEPARATOR .  $tableName . '.php', $content);
    }
    /**
     * 表字段，缓存文件名
     */
    public static function tableColumnFileName($tableName){
        $database   = config('database.database');
        $fileName   = md5($database).$tableName;

        return Container::get('app')->getRuntimePath() . 'xjryanse' . DIRECTORY_SEPARATOR . 'db_column' . DIRECTORY_SEPARATOR .  $fileName . '.php';
    }
    /**
     * 生成表字段
     */
    public static function tableColumnsGenerate(){
        $tables = Db::query('show tables');
        foreach($tables as $v){
            $t          = array_values($v);
            self::tableColumnGenerate($t[0]);
        }
    }
    /**
     * 20230729:单表
     * @param type $tableName
     */
    public static function tableColumnGenerate($tableName){
        // 数据内容
        $columns    = DbOperate::columns($tableName);
        // 保存路径
        $filePath = self::tableColumnFileName($tableName);
        return self::dataToFile($columns, $filePath);
    }
    /**
     * 缓存数据写入文件
     * @param type $data
     * @param type $filePath
     * @return type
     * @throws Exception
     */
    public static function dataToFile($data,$filePath){
        $serData = serialize($data);
        $cmpData = gzcompress($serData);

        $content    = '<?php ' . PHP_EOL . 'return ';
        $content    .= var_export($cmpData, true) . ';';

        File::unlink($filePath);
        if (!file_exists(dirname($filePath)) && !mkdir(dirname($filePath), 0777, true)) {
            throw new Exception('创建目录'. dirname($filePath) .'失败');
        }
        // 写入缓存文件
        return file_put_contents($filePath, $content);            
    }
    /**
     * 20230829:反序列化输出
     * @param type $filePath
     * @return type
     */
    public static function dataFromFile($filePath){
        $serData    = include $filePath;
        $uData      = gzuncompress($serData);
        return unserialize($uData);
    }
    
    /**
     * 数据表全量缓存（一般为系统表/配置表）
     * @param type $tableName
     * @return type
     */
    public static function tableFullCacheFileName($tableName){
        $database   = config('database.database');
        $fileName   = md5($database).$tableName;
        return Container::get('app')->getRuntimePath() . 'xjryanse' . DIRECTORY_SEPARATOR . 'tb_data' . DIRECTORY_SEPARATOR .  $fileName . '.php';
    }

    /**
     * 数据表全量缓存
     */
    public static function allTableFullCache(){
        $tables = DbOperate::cacheToFileTables();

        foreach($tables as $tableName){
            self::tableFullCache($tableName);
        }
    }
    /**
     * 20320728：单表缓存
     * @param type $tableName
     * @return bool
     */
    public static function tableFullCache($tableName){
        $tables = DbOperate::cacheToFileTables();
        if(!in_array($tableName, $tables)){
            throw new Exception('数据表'.$tableName.'不可全量缓存');
        }

        $con = [];
        // 20230729：恢复隐藏
//        if(DbOperate::hasField($tableName,'status')){
//            $con[] = ['status','=',1];
//        }
        $service = DbOperate::getService($tableName);
        if(!$service){
            return false;
        }
        // 数据内容
        // $dataAll    = Db::table($t)->where($con)->select();
        $dataAll    = $service::selectDb($con);
        // 保存路径
        $filePath   = self::tableFullCacheFileName($tableName);
        return self::dataToFile($dataAll, $filePath);
    }
    /**
     * 20230729:删除数据表缓存文件
     * @param type $tableName
     */
    public static function tableCacheDel($tableName){
        $filePath   = self::tableFullCacheFileName($tableName);
        if(file_exists($filePath)){
            unlink($filePath);
        }
    }
    
    
}
