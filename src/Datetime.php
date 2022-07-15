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
     * 传月份输出每一天
     */
    public static function monthlyDates($month){
        $startTime = date('Y-m-01 00:00:00',strtotime($month));
        $endTime = date('Y-m-d 23:59:59',strtotime($startTime.' +1 month -1 day'));
        return self::getWithinDate($startTime, $endTime);
    }
    /**
     * 20220704：获取月份的开始时间，结束时间范围
     */
    public static function monthlyScopeTimes($month){
        $startTime = date('Y-m-01 00:00:00',strtotime($month));
        $endTime = date('Y-m-d 23:59:59',strtotime($startTime.' +1 month -1 day'));
        return [$startTime,$endTime];
    }
    
    /**
     * 传月份输出每一月
     */
    public static function yearlyMonthes($year){
        $yearArr[] = $year.'-01';
        $yearArr[] = $year.'-02';
        $yearArr[] = $year.'-03';
        $yearArr[] = $year.'-04';
        $yearArr[] = $year.'-05';
        $yearArr[] = $year.'-06';
        $yearArr[] = $year.'-07';
        $yearArr[] = $year.'-08';
        $yearArr[] = $year.'-09';
        $yearArr[] = $year.'-10';
        $yearArr[] = $year.'-11';
        $yearArr[] = $year.'-12';
        return $yearArr;
    }
    /**
     * 20220704：获取年份的开始时间，结束时间范围
     */
    public static function yearlyScopeTimes($year){
        $startTime  = $year.'-01-01 00:00:00';
        $endTime    = $year.'-12-31 23:59:59';
        return [$startTime,$endTime];
    }
    /**
     * 
     * @param type $startTime
     * @param type $endTime
     * @return type
     */
    public static function getWithinMonth( $startTime, $endTime ){
        //转为时间戳
        if(!is_numeric($startTime)){
            $startTime = strtotime( $startTime );
        }
        if(!is_numeric($endTime)){
            $endTime = strtotime( $endTime );
        }
        $dateArr = [];
        while( $endTime >= $startTime){
            $dateArr[] = date('Y-m',$startTime);
            $startTime = strtotime(date('Y-m-d H:i:s',$startTime).' +1 month' );    //下一月
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
    
    /**
     * 获取区间时间
     * @param type $period
     * @param type $unit
     * @return type
     */
    public static function periodTime( $period, $unit, $time = '' )
    {
        if( !$time ){
            $time = date('Y-m-d H:i:s');
        }
        //时间戳的处理：TODO优化
        if(is_numeric($time)){
            $time = date('Y-m-d H:i:s',$time);
        }
        //日
        if($unit == 'day'){
            $date['fromTime']  = date('Y-m-d 00:00:00',strtotime( '-'.($period - 1) .' '. $unit, strtotime($time) ) );
            $date['toTime']    = date('Y-m-d 23:59:59',strtotime( $time) );
        }
        //月
        if($unit == 'month'){
            $date['fromTime']  = date('Y-m-01 00:00:00',strtotime( '-'.($period - 1) .' '. $unit, strtotime($time) ) );
            $date['toTime']    = date('Y-m-d H:i:s',strtotime( '+1 '. $unit.' -1 second', strtotime( $date['from_time'] ) ) );
        }
        //年
        if($unit == 'year'){
            $date['fromTime']  = date('Y-01-01 00:00:00',strtotime( '-'.($period - 1) .' '. $unit, strtotime($time) ) );
            $date['toTime']    = date('Y-m-d H:i:s',strtotime( '+1 '. $unit.' -1 second', strtotime( $date['from_time'] ) ) );
        }

        return $date;
    }
    /**
     * 时间是否已过
     * @param type $time
     */
    public static function isExpire($time){
        if(!is_numeric($time)){
            $time = strtotime($time);
        }

        return time() > $time;
    }
    /**
     * 剩余天数
     * @param type $endDate
     * @param type $startDate
     */
    public static function remainDays($endDate, $startDate = ''){
        $startTime  = $startDate ? strtotime($startDate): time();
        $endTime    = strtotime($endDate);

        return intval(($endTime - $startTime) / 86400);
    }
    
    /**
     * 年月时间条件
     * @param type $key         字段key
     * @param type $yearmonth   年月值
     * @param type $day        日
     * @return type
     */
    public static function yearMonthTimeCon( $key ,$yearmonth, $day = '')
    {
        $con = [];
        if( $day ){
            //①日
            $day = $yearmonth 
                    ? $yearmonth .'-'.$day 
                    : date('Y-m') .'-'.$day ;
            $startDate  = date('Y-m-d 00:00:00',strtotime( $day ));
            $endDate    = date('Y-m-d 23:59:59',strtotime( $day ));
        } else {
            //②年月，必有
            $startDate  = date('Y-m-01 00:00:00',strtotime($yearmonth));
            $endDate    = date('Y-m-d 23:59:59',strtotime($yearmonth ." +1 month -1 day"));
        }
        $con[] = [ $key ,'>=',$startDate];
        $con[] = [ $key ,'<=',$endDate];
        return $con;
    }
}
