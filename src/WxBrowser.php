<?php
namespace xjryanse\logic;

/**
 * 微信浏览器逻辑
 */
class WxBrowser
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

}
