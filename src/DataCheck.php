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
    public static function must( $data, $keys, $notices )
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

}
