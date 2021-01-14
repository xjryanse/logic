<?php
namespace xjryanse\logic;

/**
 * 日期时间逻辑
 */
class Datetime
{
    /*
     * 返回指定两个时间范围内的全部日期数组
     */
    public static function getWithinDate( $startTime, $endTime )
    {
        //转为时间戳
        if(!is_numeric($startTime)){
            $startTime = strtotime( $startTime );
        }
        if(!is_numeric($endTime)){
            $endTime = strtotime( $endTime );
        }
        $dateArr = [];
        while( $startTime < strtotime(date('Y-m-d',$endTime + 86400 ) ) ){
            $dateArr[] = date('Y-m-d',$startTime);
            $startTime += 86400;    //下一天
        }
        return $dateArr;
    }
    /**
     * 最近n天的日期
     * @param type $days    天数
     * @param type $to      0截止今天，1截止昨天
     * @param type $format  格式
     */
    public static function lastDaysArr( $days = 7,$to = 1, $format = "Y-m-d" )
    {
        $startTime = time()- 86400 * ($days + $to - 1);
        $dateArr = [];
        for($i=0;$i<$days;$i++){
            $dateArr[] = date($format,$startTime + $i * 86400);
        }
        return $dateArr;
    }
}
