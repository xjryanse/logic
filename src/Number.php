<?php
namespace xjryanse\logic;

use Exception;
/*
 * 农历 节气 节日
 */
class Number {
    /**
     * 20230814：为了解决小数相减时，浮点不准确的问题，封装此方法
     */
    public static function minus($num1,$num2, $enlarge = 1000){
        //先乘以1000，再除以1000
        $n1 = $num1 ? : 0;
        $n2 = $num2 ? : 0;
        // dump($n1 * $enlarge);
        
        return (intval($n1 * $enlarge) - intval($n2 * $enlarge)) /$enlarge;
    }
    /**
     * 解决相加时，不是有效数值类型的
     */
    public static function sum($num1,$num2){
        $n1 = $num1 ? : 0;
        $n2 = $num2 ? : 0;
        return $n1 + $n2;
    }
    
    /**
     * 20231106:多个数相加减
     * @param type $sum     相加
     * @param type $minus   相减
     * @param type $enlarge 放大倍数
     * @return type
     */
    public static function calMulti($sum,$minus, $enlarge = 1000){
        // 相加数组
        $sArr   = is_array($sum) ? $sum : [$sum];
        // 相减数组
        $mArr   = is_array($minus) ? $minus : [$minus];
        // 结果集
        $r      = 0 ;
        foreach($sArr as $s){
            $s = $s ? : 0;
            $r += intval($s * $enlarge);
        }
        foreach($mArr as $m){
            $m = $m ? : 0;
            $r -= intval($m * $enlarge);
        }
        return $r /$enlarge;
    }

    /**
     * 数字转汉字表示
     * @param type $number
     * @return string
     */
    public static function toChinese($number) {
        $chineseNumArray = array('零', '一', '二', '三', '四', '五', '六', '七', '八', '九');
        $chineseUnitArray = array('', '十', '百', '千', '万', '亿');

        if ($number == 0) {
            return $chineseNumArray[0];
        }

        $chineseNum = '';
        $unitIndex = 0;

        while ($number > 0) {
            $digit = $number % 10;

            // 处理零
            if ($digit == 0) {
                if ($unitIndex > 0 && $chineseNum[0] != $chineseNumArray[0]) {
                    $chineseNum = $chineseNumArray[0] . $chineseNum;
                }
            } else {
                $chineseNum = $chineseNumArray[$digit] . $chineseUnitArray[$unitIndex] . $chineseNum;
            }

            $number = floor($number / 10);
            $unitIndex++;
        }

        return $chineseNum;
    }
    /**
     * 百分比
     * @param type $child   分子
     * @param type $mother  分母
     * @return string
     */
    public static function rate($child, $mother){
        if(!$mother){
            return null;
        }
        if(!$child){
            return 0;
        }
        
        return round($child / $mother * 100, 2).'%';
    }
    /**
     * 20231212：解决系统round方法bug
     * @param type $number
     * @param type $length
     * @return type\
     */
    public static function round($number, $length=0){
        $enlarge = pow(10,$length + 1);
        // $newNum = number_format($number, 2);
        $num = intval($number * $enlarge);
        return $num / $enlarge;
    }
    /**
     * 20231213:符号函数
     */
    public static function signum($num){
        if($num>0){
            return 1;
        }
        if($num <0){
            return -1;
        }
        return 0;
    }
    /**
     * 公式计算
     * 20240307
     */
    public static function calFormula($fRaw, $data){
        $formula = Strings::dataReplace($fRaw, $data);
        try{
            $result = eval('return '.$formula.';');
            return $result;
        } catch(\Exception $e){
            throw new Exception('公式异常:'.$formula);
        }
    }

}
