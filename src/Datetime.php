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
        $startTime = self::monthStartTime($month);
        $endTime = self::monthEndTime($month);
        return [$startTime,$endTime];
    }
    /**
     * 20230509：提取月份开始时间
     */
    public static function monthStartTime($month){
        return date('Y-m-01 00:00:00',strtotime($month));
    }
    /*
     * 20230509：提取月份结束时间
     */
    public static function monthEndTime($month){
        $startTime = self::monthStartTime($month);
        return date('Y-m-d 23:59:59',strtotime($startTime.' +1 month -1 day'));
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
     * 20230509：提取年份开始时间
     */
    public static function yearStartTime($year){
        return $year.'-01-01 00:00:00';
    }
    /*
     * 20230509：提取年份结束时间
     */
    public static function yearEndTime($year){
        return $year.'-12-31 23:59:59';
    }
    /**
     * 20230509：日期开始时间
     * @param type $date
     */
    public static function dateStartTime($date){
        return date('Y-m-d 00:00:00',strtotime($date));
    }
    /**
     * 20230509：日期结束时间
     * @param type $date
     * @return type
     */
    public static function dateEndTime($date){
        return date('Y-m-d 23:59:59',strtotime($date));
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
     * 2022-11-27:是否最近刚添加的记录
     * @param type $time
     */
    public static function isRecent($time,$seconds = 3600 ){
        // 当前时间，大于给定时间，且当前时间距给定时间不足 $seconds 秒。
        return $time && time() > strtotime($time) && (time() - strtotime($time)) < $seconds;
    }
    /**
     * 20230527:字符串是否合法的时间格式
     */
    public static function isDate($dateStr){
        $preg = '/^([12]\d\d\d)-(0?[1-9]|1[0-2])-(0?[1-9]|[12]\d|3[0-1])$/';
        return preg_match($preg, $dateStr);
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

    /**
     * 20230721:分钟差
     * @param type $time
     * @param type $compareTime
     * @param type $ceil        不足一分钟是否补齐
     * @return type
     */
    public static function minuteDiff($time,$compareTime, $ceil = true){
        $secondDiff = strtotime($time) - strtotime($compareTime);
        return $ceil ? ceil($secondDiff / 60) : round($secondDiff / 60, 1);
    }

    /**
     * 20220928：传入两个时间，获得时间差，不足1小时按1小时算
     */
    public static function hourDiff($time,$compareTime, $ceil = true){
        $secondDiff = strtotime($time) - strtotime($compareTime);
        return $ceil ? ceil($secondDiff / 3600) : round($secondDiff / 3600, 2);
    }
    /**
     * 20230409:传入两个时间，获得日期差天数，不足1天按1天算
     * @param type $time
     * @param type $compareTime
     * @return type
     */
    public static function dayDiff($time,$compareTime, $ceil = true){
        $secondDiff = strtotime($time) - strtotime($compareTime);
        return $ceil ? ceil($secondDiff / 86400) : round($secondDiff / 86400, 2);
    }
    /**
     * 天数，时间差，返回数组
     */
    public static function dayHourDiffStr($time,$compareTime){
        $secondDiff = strtotime($time) - strtotime($compareTime);
        $days       = intval($secondDiff / 86400);
        $remains    = $secondDiff - $days * 86400;
        $hours      = intval($remains / 3600);
        $str = '';
        if($days){
            $str .= $days.'天';
        }
        if($hours){
            $str .= $hours.'时';
        }
        return $str;
    }
    
    /**
     * 20220610 : 秒数转日期时间描述
     * @param type $seconds
     */
    public static function toTimeStr($seconds){
        $days       = intval($seconds / 86400);

        $hours      = intval(($seconds - 86400 * $days) / 3600);

        $minutes    = intval(($seconds - 86400 * $days - 3600 * $hours) / 60);

        $str = '';
        if($days){
            $str .= $days.'天';
        }
        if($hours){
            $str .= $hours.'时';
        }
        //没有天才显示分钟
        if(!$days && $minutes){
            $str .= $minutes.'分';
        }

        return $str;
    }
    /**
     * 2022-11-23：年月和时间范围转成一个日期时间
     * @param type $yearmonth
     * @param type $timeScope
     */
    public static function getDateStr($yearmonth, $timeScope = []){
        if(!$timeScope){
            return $yearmonth;
        }
        
        if($yearmonth){
            $startDate  = date('Y-m-01 00:00:00',strtotime($yearmonth));
            $endDate    = date('Y-m-d 23:59:59',strtotime($yearmonth ." +1 month -1 day"));
            // 开始日期取大
            if($timeScope[0] < $startDate){
                $timeScope[0] = $startDate;
            }
            // 结束日期取小
            if($timeScope[1] > $endDate){
                $timeScope[1] = $endDate;
            }
        }
        // 0点开始，24点结束，只保留日期
        if(date('H:i:s',strtotime($timeScope[0])) == '00:00:00'){
            $timeScope[0] = date('Y-m-d',strtotime($timeScope[0]));
        }
        // TODO修前端bug
        if(date('H:i:s',strtotime($timeScope[1])) == '00:00:00' || date('H:i:s',strtotime($timeScope[1])) == '23:59:59'){
            $timeScope[1] = date('Y-m-d',strtotime($timeScope[1]));
        }
        
        return implode('至',$timeScope);
    }
    /**
     * 两个时间段是否有交集
     * @param type $timeScope1      时间段1
     * @param type $timeScope2      时间段2
     */
    public static function isIntersect(array $timeScope1, array $timeScope2){
        //开始时间大于另一个结束时间，没交集
        if($timeScope1[0] > $timeScope2[1] || $timeScope2[0] > $timeScope1[1]){
            return false;
        }
        return true;
    }
    /**
     * 20230518:
     * 传入开始时间，结束时间，返回一个简洁的时间段表达字串
     * 
     * 例如：2023-05-18 08:00 至 12:00
     */
    public static function scopeTimeStr($startTime, $endTime){
        // 同一天，只保留时；分         2023年5月18日10:00至12:00
        // 跨天不跨月，保留日时分；     2023年5月18日10:00至19日12:00
        // 跨月不跨年，保留月日时分；   2023年5月18日10:00至6月1日12:00
        // 跨年，全部                   2023年12月31日10:00至2024年6月1日12:00
        $startStr   = $startTime ? date('Y年m月d日H:i',strtotime($startTime)) : '';
        $startDay   = date('Y-m-d',strtotime($startTime));
        $startMonth = date('Y-m',strtotime($startTime));
        $startYear  = date('Y',strtotime($startTime));

        $endDay   = date('Y-m-d',strtotime($endTime));
        $endMonth = date('Y-m',strtotime($endTime));
        $endYear  = date('Y',strtotime($endTime));
        
        $endStr = '';
        // 同一天，只保留时；分         2023年5月18日10:00至12:00
        if($startDay == $endDay){
            $endStr = date('H:i',strtotime($endTime));
        } else {
            if($startMonth == $endMonth){
                // 跨天不跨月，保留日时分；     2023年5月18日10:00至19日12:00
                $endStr = date('d日H:i',strtotime($endTime));
            } else {
                if($startYear == $endYear){
                    // 跨月不跨年，保留月日时分；   2023年5月18日10:00至6月1日12:00
                    $endStr = date('m月d日H:i',strtotime($endTime));
                } else {
                    // 跨年，全部                   2023年12月31日10:00至2024年6月1日12:00
                    $endStr = date('Y年m月d日H:i',strtotime($endTime));
                }
            }
        }

        return $endStr ? $startStr.'至'.$endStr : $startStr;
    }
    /**
     * 日期延期：
     * 传入延期天数；开始时间，返回结束时间
     */
    public static function datetimeDayExt($days, $startTime = ''){
        $startTimeStamp = $startTime ? strtotime($startTime) : time();
        $endTimeStamp   = $startTimeStamp + $days * 86400;
        return date('Y-m-d H:i:s',$endTimeStamp);
    }
}
