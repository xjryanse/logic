<?php
namespace xjryanse\logic;

use think\Db;
/**
 * 数据库操作类库
 */
class DbOperate
{
    /**
     * 判断数据表是否存在
     * @return type
     */
    public static function isTableExist( $tableName )
    {
        //判断数据表是否存在
        $exist = Db::cache(60)->query("show tables like '". $tableName ."'");
        return $exist;
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
    public static function lastId( $tableName ,$con = []) 
    {
        return Db::table( $tableName )->where( $con )->order('id desc')->value('id');
    }
}
