<?php

namespace xjryanse\logic;

use think\facade\Request as TpRequest;
/**
 * 请求
 */
class Request {
    public static function param( $name = '',$default ="" ){
        //请求优先，请求没有则取路由
        if($name){
            return TpRequest::param($name, $default ) ? : TpRequest::route($name,$default); 
        } else {
            return array_merge(TpRequest::route(),TpRequest::param());
        }
    }

    public static function __callStatic($name, $arguments) {
        return TpRequest::$name($arguments);
    }
    /**
     * 只取一些
     * @param type $name
     * @return type
     */
    public static function only($name){
        if(!is_array($name)){
            $name = [$name];
        }
        $params = array_merge(TpRequest::route(),TpRequest::param());
        return Arrays::getByKeys($params, $name);
    }
}
