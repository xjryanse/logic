<?php
namespace xjryanse\logic;

/**
 * 数组处理逻辑
 */
class Arrays
{
    /**
     * 数组指定键取值
     * @param type $array   数据数组
     * @param type $keys    键值数组
     * @return type
     */
    public static function getByKeys(array $array,array $keys )
    {
        $tmpArr = [];
        foreach( $keys as $v){
            if(isset($array[$v])){
                $tmpArr[$v] = $array[$v];
            }
        }
        return $tmpArr;
    }

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
     * 去除多余参数，只保留id
     * @param type $data    数据包
     * @param type $fields  待留键的字段
     * @param type $key     键名
     * @return type
     */
    public static function onlyKey( &$data, $fields, $key="id" )
    {
        if(!is_array($fields)){
            $fields = [ $fields ];
        }
        foreach( $fields as $v){
            //数据存在，数据是数组，待取的键存在
            if(isset($data[$v]) && is_array($data[$v]) && isset( $data[$v][$key] ) ){
                $data[$v] = $data[$v][$key];
            } 
        }
        return $data;
    }    
}
