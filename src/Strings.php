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
    /**
     * 有些变态空格，使用trim无法去除，用本方法
     * @param type $value
     * @return type
     */
    public static function clearEmptyChar( $value )
    {
        $value = preg_replace("/^[\s\v".chr(194).chr(160)."]+/","", $value); //替换开头空字符
        $value = preg_replace("/[\s\v".chr(194).chr(160)."]+$/","", $value); //替换结尾空字符
        return $value;
    }
    /**
     * 是否包含中文
     */
    public static function hasChineseChar( $str )
    {
        if (preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $str)>0) {
            return 2;   // '全是中文';
        } else if(preg_match('/[\x{4e00}-\x{9fa5}]/u', $str)>0) {
            return 1;   // '含有中文';
        } else {
            return 0;   //  '没有中文'
        }
    }
    /**
     * 是否包含英文
     * @param type $str
     */
    public static function hasEnglishChar( $str )
    {
        $preg2 = '/[a-zA-Z]/';
        if( preg_match($preg2,$str) ){
            return 1;   //包含字母
        } else {
            return 0;   //不包含字母
        }
    }
    
    public static function toUtf8 ($str = '') 
    {
        $current_encode = mb_detect_encoding($str, array("ASCII","GB2312","GBK",'BIG5','UTF-8')); 
        $encoded_str = mb_convert_encoding($str, 'UTF-8', $current_encode);
        return $encoded_str;
    }
    /**
     * 判断是否json格式
     * @param type $dataStr
     * @param type $assoc
     * @return boolean
     */
    public static function isJson($dataStr = '', $assoc = false) {
        $data = json_decode($dataStr, $assoc);
        if (($data && (is_object($data))) || (is_array($data) && !empty($data))) {
            return true;
        }
        return false;
    }
    /**
     * 保留几位，剩下的……
     */
    public static function keepLength($str,$length)
    {
        return mb_strlen($str) > $length ? mb_substr($str, 0,$length) .'…' : $str ;
    }
    
    /**
     * 字符串是否以某字符串开始
     * @param type $str
     * @param type $start
     * @return type
     */
    public static function startWith($str,$start){
        return substr($str, 0, strlen($start)) === $start;
    }
    /**
     * 字符串是否以某字符串结束
     * @param type $str
     * @param type $end
     */
    public static function endWith($str,$end){
        return substr(strrev($str), 0, strlen($end)) === strrev($end);
    }
}
