<?php
namespace xjryanse\logic;

use xjryanse\curl\Call;
use Exception;
/**
 * session会话增强
 */
class Sessionx
{    
    /**
     * 直接执行after触发器
     * @param type $func
     * @throws Exception
     */
    public static function directAfter($func){
        //回调方法
        $res = $func();
        //TODO动态化
        Call::get('http://tenancy.xiesemi.cn/system/async_trigger/do');
        return $res;
    }
}
