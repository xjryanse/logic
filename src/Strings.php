<?php
namespace xjryanse\logic;

/**
 * 字符串处理函数
 */
class Strings
{
    /**
     * 比如 202103010001，取下一个值
     * 202103010002
     * @param type $str     字符串  202103010001
     * @param type $start   开始位  8
     * @param type $count   总计位  3
     * @param type $fill    使用什么填充，默认0
     */
    public static function getNextNo( $str, $start, $count,$fill="0")
    {
        $number = ( substr($str, $start,$count) );
        $next   = str_pad((int) $number + 1, $count, $fill, STR_PAD_LEFT);
        $prefix = substr($str,0,$start);
        return $prefix.$next;
    }
    

}