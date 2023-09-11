<?php
namespace xjryanse\logic;
/*
 * 农历 节气 节日
 */
class Number {
    /**
     * 20230814：为了解决小数相减时，浮点不准确的问题，封装此方法
     */
    public static function minus($num1,$num2, $enlarge = 1000){
        //先乘以1000，再除以1000
        return ($num1 * $enlarge - $num2 * $enlarge) /$enlarge;
    }

}
