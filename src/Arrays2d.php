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
}
