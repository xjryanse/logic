<?php
namespace xjryanse\logic;

use xjryanse\logic\Arrays;
/**
 * 二维数组处理逻辑
 */
class Arrays2d
{
    /**
     * 二维数组矩阵转置
     */
    public static function transpose( array $data ) {
        $myData = array_values($data);
        if(!is_array($myData[0])){
            return false;
        }
        $keys = array_keys( $myData[0] );
        foreach($keys as &$v){
            $tmp    = array_merge( [$v],array_column($myData, $v) );
            $resData[]  = $tmp;
        }
        return $resData;
    }
    
    /**
    * 二维数组首行转键
    */
    public static function shiftToKey( array $data ) {
        $first = array_shift($data);
        $resData = [];
        foreach( $data as $k=>$v){
            $tmpData = [];
            foreach($first as $kk=>$kv){
                $tmpData[ $kv ] = $v[ $kk ];
            }
            $resData[] = $tmpData;
        }
        return $resData;
    }
    
    /**
     * 二维数组键名替换
     * @param array $data   
     * @param array $keys
     * @param type $mergeRaw    是否合并原数组
     * @return array
     */
    public static function keyReplace( array $data, array $keys, $mergeRaw = false ) {
        $resData = [];
        foreach( $data as $k=>$v){
            foreach($keys as $kk=>$kv){
                if(isset($v[$kk])){
                    $resData[$k][$kv] = $v[$kk];
                }
            }
            //是否合并原数组
            if($mergeRaw){
                $resData[$k] = array_merge($resData[$k], $v);
            }
        }

        return $resData;
    }
    
    /**
     * 无数据设默认值
     * @param array $data           数据
     * @param string $mainField     主字段：    如日期字段名
     * @param array $mainColumn     主字段数组：如日期数组
     * @param string $valueField
     * @param type $default
     */
    public static function noValueSetDefault( array $data, string $mainField, array $mainColumn, string $valueField, $default = "")
    {
        //用于存储返回结果的数组
        $respData = [];
        //循环全部待取key值
        foreach( $mainColumn as $v ){
            //比对数据数组，如有，则赋值
            foreach( $data as &$value){
                if($value[ $mainField ] == $v){
                    $respData[$v] = $value;
                    break;
                }
            }
            //已赋值的，进入下一个key循环
            if(isset($respData[$v])){
                continue;
            }
            //未赋值的，设置默认值。主字段$v；值字段$default
            $respData[$v] = [ $mainField => $v, $valueField => $default ];
        }
        return $respData;
    }
    
    /**
     * 数组指定键取值
     * @param type $array   数据数组
     * @param type $keys    键值数组
     * @return type
     */
    public static function getByKeys(array $array,array $keys )
    {
        foreach($array as &$value){
            $match = array_fill_keys($keys, "");
            //比较两个（或更多个）数组的键名 ，并返回交集。
            $value = array_intersect_key( $value , $match);
        }
        return $array;
    }
    /**
     * 将某个字段设为key（需唯一）
     */
    public static function fieldSetKey(array $array,$keyField){
        return array_column($array,null,$keyField);
    }
    /**
     * 转一维键值对
     */
    public static function toKeyValue(array $array, $keyField, $valueField){
        $keys = array_column($array, $keyField);
        $values = array_column($array, $valueField);
        return array_combine($keys, $values);
    }
    /**
     * 根据指定字段的值，返回新数组
     * 场景示例：批量提取了10个订单的流程节点，需要按每个订单进行拆分
     */
    public static function listByFieldValue( $array, $keyField, $value){
        $tempArr = [];
        foreach($array as &$arrItem){
            if(Arrays::value($arrItem, $keyField) == $value){
                $tempArr[] = $arrItem;
            }
        }
        return $tempArr;
    }
    /**
     * 列表数据过滤
     * @param type $listsAll 二维数组数据
     * @param type $con     过滤条件（兼容数据库查询）
     */
    public static function listFilter( $listsAll, $con = [] ){
        $dataArr = [];
        foreach($listsAll as $data){
            if(Arrays::isConMatch($data, $con)){
                $dataArr[] = $data;
            }
        }
        return $dataArr;
    }
    /**
     * 列表数据过滤,取单条
     * @param type $listsAll 二维数组数据
     * @param type $con     过滤条件（兼容数据库查询）
     */
    public static function listFind( $listsAll, $con = [] ){
        foreach($listsAll as $data){
            if(Arrays::isConMatch($data, $con)){
                return $data;
            }
        }
        return [];
    }
}
