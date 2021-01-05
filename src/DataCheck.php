<?php
namespace xjryanse\logic;

use Exception;
/**
 * 数据校验
 */
class DataCheck
{
    /**
     * 必填
     */
    public static function must( $data, $keys, $notices=[] )
    {
        foreach( $keys as $key){
            //key不存在
            if( !isset( $data[ $key ] ) || !$data[ $key ] ){
                throw new Exception( isset($notices[ $key ]) 
                        ? $notices[ $key ] 
                        : $key.'必须' );
            }
        }
    }
    /**
     * 校验是否json格式
     * @param type $data
     */
    public static function isJson( $data )
    {
        //不是字符串，或者是数值型，则不是json
        if(!is_string( $data ) || is_numeric($data)){
            return false;
        }
        return json_decode( $data ) ? true : false;
    }

}
