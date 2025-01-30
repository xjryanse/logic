<?php
namespace xjryanse\logic;

use think\facade\Request;
use think\facade\Cache;
use think\Db;
use Exception;
/**
 * 调试复用
 */
class Debug
{    
    //输出调试变量
    public static function debug($name='',$value='',$group='')
    {
        //  || self::isDevIp()
        if ( self::isDebug() && self::isGroupMatch($group)) {
            echo $name;
            dump($value);                
        }
    }
    /**
     * 是否调试环境
     * @return type
     */
    public static function isDebug($compare = 'xjryanse')
    {
        // return true;
        $debug = Request::param('debug','');
        return $debug == $compare;
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
        if(self::isDevIp()){
            throw new Exception('测试中……');
        }
    }
    /**
     * 调试模式下输出
     */
    public static function dump($data, $data2 = ''){
        if(self::isDevIp()){
            dump($data);
            if($data2){
                dump($data2);
            }
        }
    }
    /*
     * 20240419:打印结束流程：调试永
     */
    public static function dumpExit($data, $data2= ''){
        if(self::isDevIp()){
            self::dump($data,$data2);
            exit;
        }
    }
    
    /**
     * 打印全部sql
     */
    public static function dumpAllSql(){
//        $allSqls = Db::getAllQueryLog();
//        foreach ($allSqls as $query) {
//            echo $query['sql']; // 逐条输出每条执行的SQL语句
//        }
    }
    /**
     * 
     */
    public static function debugTime(){
        global $runMicTime;
        if ( self::isDebug('time')){
            $startTime = $runMicTime ? : 0;
            $runMicTime = microtime(true);
            $executionTime = $runMicTime - $startTime;
            dump($executionTime * 1000);
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
    /**
     * 当前访问ip是否开发者ip
     * @return type
     */
    public static function isDevIp(){
        return Request::ip() == Cache::get('devRequestIp');
    }    
}
