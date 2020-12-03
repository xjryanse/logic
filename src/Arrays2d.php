<?php
namespace xjryanse\logic;

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
     */
    public static function keyReplace( array $data, array $keys ) {
        $resData = [];
        foreach( $data as $k=>$v){
            foreach($keys as $kk=>$kv){
                if(isset($v[$kk])){
                    $resData[$k][$kv] = $v[$kk];
                }
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
}
