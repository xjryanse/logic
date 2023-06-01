<?php
namespace xjryanse\logic;

/**
 * 客户端设备
 */
class Device
{
    /*
     * 是否微信浏览器中打开
     */
    public static function isWxBrowser()
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            return true;
        }
        return false;
    }
    /**
     * 客户端操作系统
     * @return string
     */
    public static function system()
    {
        //全部变成小写字母
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $type ='other';
        //分别进行判断
        if(strpos($agent,'iphone') || strpos($agent,'ipad')){
            $type ='ios';
        }
        if(strpos($agent,'android')){
            $type ='android';
        }
        return $type;
    }
    /**
     * 是否ipad
     */
    public static function isIpad(){
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        return strpos($agent, 'ipad');
    }
}
