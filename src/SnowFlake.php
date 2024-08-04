<?php
namespace xjryanse\logic;

/**
 * 雪花算法
 */
class SnowFlake
{
    const EPOCH = 1479533469598;    
    const max12bit = 4095;    
    const max41bit = 1099511627775;    
    static $machineId = null;    
    
    static $sequenceId      = 0;//顺序id，取代随机
    static $lastTimeStamp   = 0;//上次生成时间戳
    
    public static function machineId($mId) {    
        self::$machineId = $mId;    
    }
    public static function generateParticle() {    
        /*    
        * Time - 42 bits    
        */    
        $time = floor(microtime(true) * 1000);    
        /*    
        * Substract custom epoch from current time    
        */    
        $time -= self::EPOCH;    
        /*    
        * Create a base and add time to it    
        */    
        $base = decbin(self::max41bit + $time);    
        /*    
        * Configured machine id - 10 bits - up to 1024 machines    
        */    
        $machineid = str_pad(decbin(self::getMachineId()), 10, "0", STR_PAD_LEFT);    
        /*    
        * sequence number - 12 bits - up to 4096 random numbers per machine    
        */    
//        $random = str_pad(decbin(mt_rand(0, self::max12bit)), 12, "0", STR_PAD_LEFT);
        $sequence = str_pad(decbin( self::getSequenceId( $time ) ), 12, "0", STR_PAD_LEFT);
        /*
        * Pack    
        */    
//        $base = $base.$machineid.$random;    
        $base = $base.$machineid.$sequence;    
        /*    
        * Return unique time id no    
        */    
        return bindec($base);    
    }    
    /**
     * 获取顺序码
     */
    private static function getSequenceId( $time )
    {
        if($time == self::$lastTimeStamp){
            self::$sequenceId += 1;
        } else {
            self::$sequenceId = 0;
        }
        self::$lastTimeStamp = $time;
        return self::$sequenceId;
    }
    /*
     * 获取机器码
     * 并发场景当作多个机器处理
     */
    private static function getMachineId( )
    {
        //未指定机器id，则随机指派
        if(!self::$machineId ){
            self::$machineId = mt_rand(0, 1023);
        }
        return self::$machineId;
    }
    
    public static function timeFromParticle($particle) {    
        /*    
        * Return time    
        */    
        return bindec(substr(decbin($particle),0,41)) - self::max41bit + self::EPOCH;    
    }
    /*
     * 20221003:获取时间戳
     */
    public static function getTimestamp($particle){
        $microTime = self::timeFromParticle($particle);   
        return intval($microTime / 1000);
    }
    
    
    /* 
     * 以下是改进逻辑-前4位用年份替换，便利按时间分表（注：使用改进逻辑后，部分反向方法受限，例如获取时间戳）
     * 20231104
     */
    /**
     * 雪花id，前4位用年份替换
     * @param type $year
     */
    public static function generateParticleWithYear($year = ''){
        if(!$year){
            $year = date('Y');
        }
        $newId = self::generateParticle();
        return substr_replace($newId, $year, 0, 4);
    }
    /**
     * 计算年份
     * @param type $id
     */
    public static function getYear($id){
        //先提取前4位置
        $year       = substr($id, 0, 4);
        if($year > 1900 && $year <=2100){
            return $year;
        }
        // 不是有效年份，则计算时间戳，返回年
        $timestamp = self::getTimestamp($id);
        return date('Y',$timestamp);
    }

}
