<?php
namespace xjryanse\logic;

use think\facade\Request;
use think\facade\Cache;
use Exception;
/**
 * 调试复用
 */
class Debug
{    
    //输出调试变量
    public static function debug($name='',$value='',$group='')
    {
        if ( self::isDebug() && self::isGroupMatch($group)) {
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
        // return true;
        $debug = Request::param('debug','');
        return $debug == 'xjryanse';
    }
    /**
     * 20220729：调试分组是否匹配
     */
    public static function isGroupMatch($group){
        $debugGroup = Request::param('debugGroup','');
        return $debugGroup == $group;
    }
    /**
     * 测试时抛异常，便利数据回滚
     */
    public static function testThrow()
    {
        throw new Exception('测试中……');
    }
    /**
     * 调试模式下输出
     */
    public static function dump($data){
        if(Request::ip() == Cache::get('devRequestIp')){
            dump($data);
        }
    }
    /**
     * 2022-11-20:调试打印退出
     * @param type $data
     */
    public static function exit($data){
        dump($data);
        exit;
    }
}
