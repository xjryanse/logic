<?php
namespace xjryanse\logic;

use xjryanse\logic\Arrays;
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
        if($array){
            $str = "(CASE ". $field ;
            foreach( $array as $key=>$value){
                $strVal = is_array($value) ? Arrays::value($value, 'label') : $value;
                $str .= " WHEN '". $key ."' THEN '". $strVal  ."'";
            }
            $str .= " ELSE '' END)";
        } else {
            //解决$array 为空报错-20210115
            $str = $field . ' ';
        }
        
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
