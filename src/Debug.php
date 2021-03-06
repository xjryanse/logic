<?php
namespace xjryanse\logic;

use think\facade\Request;
use Exception;
/**
 * 调试复用
 */
class Debug
{    
    //输出调试变量
    public static function debug($name='',$value='')
    {
        if ( self::isDebug() ) {
            echo $name;
            dump($value);                
        }
    }
    /**
     * 是否调试环境
     * @return type
     */
    public static function isDebug()
    {
        $debug = Request::param('debug','');
        return $debug == 'xjryanse';
    }
    /**
     * 测试时抛异常，便利数据回滚
     */
    public static function testThrow()
    {
        throw new Exception('测试中……');
    }
}
