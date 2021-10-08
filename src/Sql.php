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
    /**
     * 统计结果直接更新（使用内联）
     * @param type $mainTable       主表
     * @param type $mainField       主表字段
     * @param type $dtlTable        明细表
     * @param type $dtlStaticField  明细表统计字段
     * @param type $dtlUniField     明细表关联主表id的字段
     * @param type $dtlCon          明细表查询条件
     * @param type $staticCate      统计类型：sum;count
     * @return string
     */
    public static function staticUpdate($mainTable, $mainField, $dtlTable, $dtlStaticField, $dtlUniField, $dtlCon = [], $staticCate = 'sum'){
        // 明细表查询条件
        $whereCon = ModelQueryCon::conditionParse($dtlCon);
        $sql    = "update ".$mainTable." as staticMain ";
        $dtlSql = "select sum(`" . $dtlStaticField . "`) as staticTotal," . $dtlUniField . " from " . $dtlTable;
        if ( $whereCon ) {
            $dtlSql .= " where ".$whereCon;
        }
        $dtlSql .= " group by ". $dtlUniField ;
        $sql   .= " inner join (" . $dtlSql . ") as staticDtl set ".$mainField." = staticDtl.staticTotal where staticMain.id = staticDtl.". $dtlUniField;
        return $sql;
    }
}
