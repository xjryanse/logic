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
     * 弃用，使用strings同名方法
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
    
    public static function isEmpty($data){
        // 空字符串，数组
        if(empty($data)){
            return true;
        }
        // 空对象
        if(is_object($data)){
            //有属性非空，没属性空
            return get_object_vars($data) ? false : true;
        }
        // 不是空
        return false;
    }
    
    
    /***********************************/
    
    /**
     * 20220531生成校验参数，适用于无法控制的场景
     */
    public static function authParamGenerate(){
        $time = time();
        $mode = $time % 5;
        $timeCov = base_convert($time + 7 * $mode,10,32);
        return $time.$timeCov;
    }
    /**
     * 验证校验参数是否合法（合法参数由上方authParamGenerate生成）
     * @param type $authStr
     * @return type
     */
    public static function authParamCheck($authStr){
        $time = substr ( $authStr , 0, 10 );
        $mode = $time % 5;
        $timeCov = substr ( $authStr , 10 );
        $timeCovDescent = base_convert($timeCov,32,10) - 7 * $mode;
        return $time == $timeCovDescent;
    }
}
