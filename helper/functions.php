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
/**
 * 等号字符串转键值对
 */
if (!function_exists('equalsToKeyValue')) {
    function equalsToKeyValue( $string,$explode='&' )
    {
        $arr = explode( $explode ,$string );
        foreach($arr as $v) {
            $t = explode('=',$v);
            $newArr[$t[0]] = $t[1];
        }
        return $newArr;
    }
}

if (!function_exists('toE0Timestamp')) {
    /**
     * 转为0时区时间戳，js用
     * @param type $timestamp   时间戳
     * @param type $zoneId      东区+，西区-，默认北京东八区
     * @return type
     */
    function toE0Timestamp( $timestamp =0,$zoneId = 8 )
    {
        if(!$timestamp){
            $timestamp = time();
        }
        return $timestamp + 3600 * $zoneId;
    }
}
if (!function_exists('randomKeys')) {
    /**
     * 生成随机字符串
     * @param type $length  长度
     * @return string
     */
    function randomKeys($length){ 
        $key        = '';
        $pattern    = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
        for($i=0;$i<$length;$i++) { 
            $key .= $pattern{mt_rand(0,62)}; //生成php随机数 
        }
        return $key; 
    } 
}

if(!function_exists('idNOGetBirthday')){
    /**
     * 身份证号取年月日
     * @param type $idNo
     * @return type
     */
    function idNOGetBirthday($idNo){
        return date('Y-m-d',strtotime(substr($idNo, 6, 8)));
    }
}
if(!function_exists('birthdayGetAge')){
    /**
     * 生日取年龄
     * @param type $birthday
     * @param type $date
     * @return type
     */
    function birthdayGetAge($birthday,$date = ""){
        $year = $date ? date('Y',strtotime($date)) : date('Y');  
        return $year - date('Y',strtotime( $birthday ));
    }
}

if(!function_exists('myDouble')){
    /**
    * 自定义double转换
    * @param int $num
    * @return string
    */
   function myDouble($num)
   {
       if(floor($num)==$num){
           return floor($num);
       }
       return $num;
   }
}

if(!function_exists('urlAddParam')){
    /**
     * url中添加参数
     * @param type $url
     * @param type $param
     * @return string
     */
    function urlAddParam( $url, $param){
        foreach($param as $k=>$v){
            if( strstr ($url,'?')){
                $url .= '&'. $k .'='.$v;
            } else {
                $url .= '?'. $k .'='.$v;
            }
        }
        return $url;
    }
}