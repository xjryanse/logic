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
     * 清除前导0
     */
    public static function clearPreZero( $value ){
        return preg_replace('/^0*/', '', $value);
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
     * JSON字符串转数组
     * @param type $dataStr
     * @param type $assoc
     * @return type
     */
    public static function jsonToArray( $dataStr , $assoc = false){
        if(!self::isJson($dataStr)){
            return [];
        }
        return json_decode($dataStr, $assoc);
    }
    /**
     * 字符串是否一个手机号码
     * @param type $dataStr
     */
    public static function isPhone($dataStr){
        return preg_match('/^1[3456789]{1}\d{9}$/',$dataStr) ? true : false;
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
    public static function isStartWith($str,$start){
        return substr($str, 0, strlen($start)) === $start;
    }
    /**
     * 字符串是否以某字符串结束
     * @param type $str
     * @param type $end
     */
    public static function isEndWith($str,$end){
        return substr(strrev($str), 0, strlen($end)) === strrev($end);
    }
    /**
     * 以数据的键值对替换字符串
     * @param type $str
     * @param type $data
     * @return type
     */
    public static function dataReplace($str,array $data){
        foreach($data as $key=>&$value){
            $str = str_replace('{$' . $key . '}', $value, $str);
        }
        return $str;
    }
    /**
     * 文件名取后缀
     * @param type $str
     * @return type
     */
    public static function getExt( $str) {
        $array  = explode('.', $str);
        return end($array);
    }
    
    /**
　　* 下划线转驼峰
　　* 思路:
　　* step1.原字符串转小写,原字符串中的分隔符用空格替换,在字符串开头加上分隔符
　　* step2.将字符串中每个单词的首字母转换为大写,再去空格,去字符串首部附加的分隔符.
　　*/
    public static function camelize($uncamelized_words,$separator='_'){
        $uncamelized_words = $separator. str_replace($separator, " ", strtolower($uncamelized_words));
        return ltrim(str_replace(" ", "", ucwords($uncamelized_words)), $separator );
    }
    
    /**
     * 驼峰命名转下划线命名
     * 思路:
     * 小写和大写紧挨一起的地方,加上分隔符,然后全部转小写
     * @param type $camelCaps
     * @param type $separator
     * @return type
     */
    public static function uncamelize($camelCaps,$separator='_'){
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
    }
}
