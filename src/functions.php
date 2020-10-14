<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * 公共函数部分
 */

if (!function_exists('datetime')) {
    /**
     * 将时间戳转换为日期时间
     * @param int    $time   时间戳
     * @param string $format 日期时间格式
     * @return string
     */
    function datetime($time, $format = 'Y-m-d H:i:s')
    {
        $time2 = is_numeric($time) ? $time : strtotime($time);
        return date($format, $time2);
    }
}
