<?php
namespace xjryanse\logic;

/**
 * Sql语句处理逻辑
 */
class Sql
{
    /**
     * case when 语句创建
     * @param type $field   字段名
     * @param array $array  数组
     */
    public static function buildCaseWhen( $field , array $array)
    {
        $str = "(CASE ". $field ;
        foreach( $array as $key=>$value){
            $str .= " WHEN '". $key ."' THEN '". $value  ."'";
        }
        $str .= " ELSE '' END)";
        
        return $str;
    }
    
    /**
     * groupConcat整理
     * @param type $tableName       表名
     * @param type $whereCondition  where条件
     * @param type $field           字段名
     * @param type $label           别名
     * @return string
     */
    public static function buildGroupConcat( $tableName,$whereCondition,$field,$label)
    {
        $str = "( SELECT GROUP_CONCAT(". $field .")"
                . " as ". $label
                . " FROM ". $tableName 
                . " WHERE ". $whereCondition ." )";
        return $str;
    }
}
