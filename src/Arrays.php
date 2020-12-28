<?php
namespace xjryanse\logic;

/**
 * 一维数组处理逻辑
 */
class Arrays
{
    /**
     * 数组取值
     */
    public static function value( array $array , string $key,$default='' )
    {
        return isset($array[ $key ]) ? $array[ $key ] : $default;
    }
    
    /**
     * 数组指定键取值
     * @param type $array   数据数组
     * @param type $keys    键值数组
     * @return type
     */
    public static function getByKeys(array $array,array $keys )
    {
        $match = array_fill_keys($keys, "");
        //比较两个（或更多个）数组的键名 ，并返回交集。
        return array_intersect_key( $array , $match);
    }
    /**
     * 移除指定键
     */
    public static function unset( &$array, $keys)
    {
        if(!is_array($keys)){
            $keys = [$keys];
        }
        foreach( $keys as $key){
            if(isset($array[$key])){
                unset( $array[$key] );
            }
        }
        return $array;
    }
    /**
     * 一维数组键名替换
     * @param array $data =  ['key1'=>'value1','key2'=>'value2','key3'=>'value3'];
     * @param array $keys =  ['key1'=>'res1','key3'=>'res3','key4'=>'res4'];
     * @return type          ['res1'=>'value1','res3'=>'value3'];
     */
    public static function keyReplace( array $data, array $keys )
    {
        $values     = array_intersect_key($data, $keys);
        ksort($values);
        $repKeys    = array_intersect_key($keys, $values);
        ksort($repKeys);
        
        return array_combine($repKeys, $values);
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
            } else {
                //没有该字段，数据置空
                $data[$v] = '';
            }
        }
        return $data;
    }    
    /**
     * 值查找键，返回包含该值的全部键名
     * @param type $array   数组
     * @param type $value   值
     * @return type
     */
    public static function valueKeys(array $array, $value)
    {
        if(!$value){
            return array_keys($array);
        }
        $arrayKeys = [];
        foreach( $array as $k=>$v){
            if( !is_array($v) && $v == $value ){
                $arrayKeys[] = $k;
            }
            if( is_array($v) && in_array( $value, $v ) ){
                $arrayKeys[] = $k;
            }
        }
        return $arrayKeys;
    }
    /**
     * 形如prizeInfo.sellerTmAuthDeposit的key，转为['prizeInfo']['sellerTmAuthDeposit']
     */
    public static function keySplit( $array, $split = "." )
    {
        foreach( $array as $key=>&$value ){
            if( !strstr( $key, $split )){    continue;    }
            $keys = explode('.',$key);
            $tmpArr = &$array;
            foreach( $keys as $kk=>$vv){
                $tmpArr[$vv] = isset($tmpArr[$vv]) ? $tmpArr[$vv] : [] ;
                $tmpArr = &$tmpArr[$vv];
            }
            //最根根赋值
            $tmpArr = $value;
            unset($array[$key]);
        }
        return $array;
    }
}
