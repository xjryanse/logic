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
    public static function buildCaseWhen( $field , array $array, $label = "")
    {
        $str = "(CASE ". $field ;
        foreach( $array as $key=>$value){
            $str .= " WHEN '". $key ."' THEN '". $value  ."'";
        }
        $str .= " ELSE '' END) as `".$label.'`';
        
        return $str;
    }
}
