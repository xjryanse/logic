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
}
