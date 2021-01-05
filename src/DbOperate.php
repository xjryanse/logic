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
    
    /**
     * 获取表全部字段
     * @param type $tableName   表名
     * @param type $columnName  字段名
     */
    public static function columns( $tableName  )
    {
        $sql = "select * from information_schema.COLUMNS "
                . "WHERE table_name ='" . $tableName . "'";
        $columns = Db::query( $sql );
        return $columns;        
    }
    /**
     * 索引是否存在
     * @param type $tableName   表名
     * @param type $columnName  字段名
     */
    public static function isColumnIndexExist( $tableName ,$columnName )
    {
        $sql = "select * from information_schema.STATISTICS "
                . "WHERE table_name ='" . $tableName . "' "
                . "AND COLUMN_NAME = '". $columnName ."'";
        $exist = Db::query( $sql );
        return $exist;        
    }
    /**
     * 字段添加索引
     */
    public static function addColumnIndex( $tableName ,$columnName ,$indexName='')
    {
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
    public static function lastId( $tableName ,$con = []) 
    {
        return Db::table( $tableName )->where( $con )->order('id desc')->value('id');
    }

}
