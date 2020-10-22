<?php
namespace xjryanse\logic;

use app\filemake\logic\make\Model;
use app\filemake\logic\make\ModelBase;
use app\filemake\logic\make\Service;
use Exception;
use think\Db;
/**
 * 生成文件
 */
class FileMake
{
    /**
     * 生成模型和服务层类
     * @param type $tableName   表名
     * @param type $modelDesc   描述
     * @param type $prefix      支持前缀
     */
    public static function generate( $tableName, $modelDesc, $prefix)
    {
        //此处亲测需!==20200710
        if($prefix && strpos($tableName,$prefix) !== 0 ){
            throw new Exception('不支持该数据表前缀');
        }
        
        if(!self::isTableExist( $tableName )){
            throw new Exception('数据表不存在');
        }

        //拆成数组
        $arr = explode('_',$tableName);
        //提取模块名
        $module     = $arr[1];
        array_shift($arr);
        //提取模型名
        $modelName = '';
        foreach( $arr as $v){
            $modelName .= ucfirst($v);
        }
        //分别创建模型，模型基类，服务类（如文件已存在，将跳过不创建）
        $res['model']       = (new Model())->generate( $module, $modelName, $modelDesc);
        $res['modelBase']   = (new ModelBase())->generate( $module, $modelName, $modelDesc);
        $res['service']     = (new Service())->generate( $module, $modelName, $modelDesc);
        return json($res);
    }
    /**
     * 判断数据表是否存在
     * @return type
     */
    private static function isTableExist( $tableName )
    {
        //判断数据表是否存在
        $exist = Db::cache(60)->query("show tables like '". $tableName ."'");
        return $exist;
    }
    

}
