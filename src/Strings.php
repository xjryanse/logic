<?php

namespace xjryanse\logic;

/**
 * 字符串处理函数
 */
class Strings {

    /**
     * 比如 202103010001，取下一个值
     * 202103010002
     * @param type $str     字符串  202103010001
     * @param type $start   开始位  8
     * @param type $count   总计位  3
     * @param type $fill    使用什么填充，默认0
     */
    public static function getNextNo($str, $start, $count, $fill = "0") {
        $number = ( substr($str, $start, $count) );
        $next = str_pad((int) $number + 1, $count, $fill, STR_PAD_LEFT);
        $prefix = substr($str, 0, $start);
        return $prefix . $next;
    }

    /**
     * 有些变态空格，使用trim无法去除，用本方法
     * @param type $value
     * @return type
     */
    public static function clearEmptyChar($value) {
        $value = preg_replace("/^[\s\v" . chr(194) . chr(160) . "]+/", "", $value); //替换开头空字符
        $value = preg_replace("/[\s\v" . chr(194) . chr(160) . "]+$/", "", $value); //替换结尾空字符
        return $value;
    }

    /**
     * 清除前导0
     */
    public static function clearPreZero($value) {
        return preg_replace('/^0*/', '', $value);
    }

    /**
     * 是否包含中文
     */
    public static function hasChineseChar($str) {
        if (preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $str) > 0) {
            return 2;   // '全是中文';
        } else if (preg_match('/[\x{4e00}-\x{9fa5}]/u', $str) > 0) {
            return 1;   // '含有中文';
        } else {
            return 0;   //  '没有中文'
        }
    }

    /**
     * 是否包含英文
     * @param type $str
     */
    public static function hasEnglishChar($str) {
        $preg2 = '/[a-zA-Z]/';
        if (preg_match($preg2, $str)) {
            return 1;   //包含字母
        } else {
            return 0;   //不包含字母
        }
    }
    
    public static function toUtf8($str = '') {
        $current_encode = mb_detect_encoding($str, array("ASCII", "GB2312", "GBK", 'BIG5', 'UTF-8'));
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
    public static function jsonToArray($dataStr, $assoc = false) {
        if (!self::isJson($dataStr)) {
            return [];
        }
        return json_decode($dataStr, $assoc);
    }

    /**
     * 将csv格式的数据，转成数组，
     * 形如微信账单：20240722：
交易时间,公众账号ID,商户号,特约商户号,设备号,微信订单号,商户订单号,用户标识,交易类型,交易状态,付款银行,货币种类,应结订单金额,代金券金额,微信退款单号,商户退款单号,退款金额,充值券退款金额,退款类型,退款状态,商品名称,商户数据包,手续费,费率,订单金额,申请退款金额,费率备注
`2024-07-09 16:47:58,`wxe1696e7c7db8e7f4,`1579507281,`0,`,`4200002166202406250324636876,`5617363390111199232,`oVgfJ5d35c6iAXrRCnfhOGF9aHT8,`JSAPI,`REFUND,`OTHERS,`CNY,`0.00,`0.00,`50302709992024070991956632208,`5622435300878286848,`50.00,`0.00,`ORIGINAL,`SUCCESS,`2024-06-28 18:30 客户付款,`{\"statement_id\":\"5617363390111199232\"},`-0.27000,`0.54%,`0.00,`50.00,`
`2024-07-09 10:06:33,`wxe1696e7c7db8e7f4,`1579507281,`0,`,`4200002360202407091150650780,`5622334254839656448,`oVgfJ5TrnzDOSuaxbFc33mw-5LUY,`JSAPI,`SUCCESS,`OTHERS,`CNY,`60.00,`0.00,`0,`0,`0.00,`0.00,`,`,`2024-07-10 13:50 客户付款,`{\"statement_id\":\"5622334254839656448\"},`0.32000,`0.54%,`60.00,`0.00,`
总交易单数,应结订单总金额,退款总金额,充值券退款总金额,手续费总金额,订单总金额,申请退款总金额
`29,`1300.00,`490.00,`0.00,`4.33000,`1300.00,`490.00
     * 
     */
    public static function csvToArray($dataStrRaw){
        // 去除excel前标识
        $dataStr = str_replace('`', '', $dataStrRaw);
        $csvArray = array_map('str_getcsv',explode("\n",$dataStr));
        
        $fieldCount = 0;
        $fieldName = [];
        
        $resArr = [];
        $tmpArr = [];
        foreach($csvArray as $v){
            $count = count($v);
            if($fieldCount != $count){
                if($tmpArr){
                    $resArr[] = $tmpArr;
                }
                $fieldName = $v;
                // 重置
                $tmpArr = [];
            } else {
                // 拼接
                $tmpArr[] = array_combine($fieldName, $v);
            }

            $fieldCount = $count;
        }
        if($tmpArr){
            $resArr[] = $tmpArr;
        }
        return $resArr;
    }
    
    /**
     * 字符串是否一个手机号码
     * @param type $dataStr
     */
    public static function isPhone($dataStr) {
        return preg_match('/^1[3456789]{1}\d{9}$/', $dataStr) ? true : false;
    }

    /**
     * 是否19位雪花算法id
     */
    public static function isSnowId($string) {
        return is_numeric($string) && mb_strlen($string) == 19;
    }

    /**
     * 保留几位，剩下的……
     */
    public static function keepLength($str, $length) {
        return mb_strlen($str) > $length ? mb_substr($str, 0, $length) . '…' : $str;
    }

    /**
     * 保留字符串中的中文数字和阿拉伯数字
     * 场景：初一年3班，处理成一3
     */
    public static function keepCNNumber($str){
        // $str = "你好，123，一二三，456，七八九";
        // 输出：123一二三456七八九
        // return preg_replace("/[^\x{4e00}-\x{9fa5}一二三四五六七八九十百千万0-9]/u", "", $str);
        return preg_replace("/[^一二三四五六七八九十百千万亿0-9]/u", "", $str);
    }
    /**
     * 保持长度，前补0
     */
    public static function preKeepLength($str, $length, $preChar = '0') {
        return str_pad($str, $length, $preChar, STR_PAD_LEFT);
    }

    /**
     * 字符串是否以某字符串开始
     * @param type $str
     * @param type $start
     * @return type
     */
    public static function isStartWith($str, $start) {
        return substr($str, 0, strlen($start)) === $start;
    }

    /**
     * 字符串是否以某字符串结束
     * @param type $str
     * @param type $end
     */
    public static function isEndWith($str, $end) {
        return substr(strrev($str), 0, strlen($end)) === strrev($end);
    }
    /**
     * 20230711:字符串是否包含子字符串
     * @param type $string
     * @param type $substring
     * @return type
     */
    public static function hasStr($string, $substring){
        return strpos($string, $substring);
    }
    /**
     * 20231207:判断是否有空格
     */
    public static function hasBlank($string){
        return strpos($string, ' ');
    }
    
    /**
     * 以数据的键值对替换字符串
     * @param type $str
     * @param array $data
     * @param string $keyPreFix 适用于递归
     * @return type
     */
    public static function dataReplace($str, array $data, $keyPreFix = '') {
        // 20240427：似乎一行可以搞定？？
        // $template = str_replace(array_keys($this->data), $this->data, $template);
        foreach ($data as $key => &$value) {
            // 20230902增加判断
            if(is_array($value) || is_object($value)){
                // continue;
                // 递归
                $str = self::dataReplace($str, $value, $key.'.');
            } else {
                // 不是数组的才替换
                $str = str_replace('{$' . $keyPreFix.$key . '}', $value, $str);
            }
        }
        return $str;
    }

    /**
     * 文件名取后缀
     * @param type $str
     * @return type
     */
    public static function getExt($str) {
        $array = explode('.', $str);
        return end($array);
    }

    /**
      　　* 下划线转驼峰
      　　* 思路:
      　　* step1.原字符串转小写,原字符串中的分隔符用空格替换,在字符串开头加上分隔符
      　　* step2.将字符串中每个单词的首字母转换为大写,再去空格,去字符串首部附加的分隔符.
      　　 */
    public static function camelize($uncamelized_words, $separator = '_') {
        $uncamelized_words = $separator . str_replace($separator, " ", strtolower($uncamelized_words));
        return ltrim(str_replace(" ", "", ucwords($uncamelized_words)), $separator);
    }

    /**
     * 驼峰命名转下划线命名
     * 思路:
     * 小写和大写紧挨一起的地方,加上分隔符,然后全部转小写
     * @param type $camelCaps
     * @param type $separator
     * @return type
     */
    public static function uncamelize($camelCaps, $separator = '_') {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
    }

    /**
     * 20220717超出部分小数点隐藏
     */
    public static function hideMore($string, $length = 20) {
        if (mb_strlen($string) > $length) {
            $string = substr($string, 0, $length) . '…';
        }
        return $string;
    }

    /**
     * 20221111：unicode解码
     * @param type $str
     * @return type
     */
    public static function unicodeDecode($str) {
        return preg_replace_callback("#\\\u([0-9a-f]{4})#i",
                function ($r) {
            return iconv('UCS-2BE', 'UTF-8', pack('H4', $r[1]));
        },
                $str);
    }

    /**
     * 生成随机长度的字符串
     * @param type $length
     * @return type
     */
    public static function rand($length = 4) {
        //从ASCII码中获取
        $captcha = '';
        //随机取：大写、小写、数字
        for ($i = 0; $i < $length; $i++) {
            //随机确定是字母还是数字
            switch (mt_rand(1, 3)) {
                case 1:                //数字：49-57分别代表1-9
                    $captcha .= chr(mt_rand(49, 57));
                    break;
                case 2:                //小写字母:a-z
                    $captcha .= chr(mt_rand(65, 90));
                    break;
                case 3:                //大写字母:A-Z
                    $captcha .= chr(mt_rand(97, 122));
                    break;
            }
        }
        //返回
        return $captcha;
    }

    /**
     * 20230330将字符串转换成二进制
     * @param type $str
     * @return type
     */
    public static function strToBin($str) {
        //1.列出每个字符
        $arr = preg_split('/(?<!^)(?!$)/u', $str);
        //2.unpack字符
        foreach ($arr as &$v) {
            $temp = unpack('H*', $v);
            $v = base_convert($temp[1], 16, 2);
            unset($temp);
        }
        return join(' ', $arr);
    }

    /**
     * 将二进制转换成字符串
     * @param type $str
     * @return type
     */
    public static function binToStr($str) {
        $arr = explode(' ', $str);
        foreach ($arr as &$v) {
            $v = pack("H" . strlen(base_convert($v, 2, 16)), base_convert($v, 2, 16));
        }
        return join('', $arr);
    }

    /**
     * 20230417:&连接转键值对数组
     * @param type $string
     * @param type $explode
     * @return type
     */
    public static function equalsToKeyValue($string, $explode = '&') {
        $arr = explode($explode, $string);
        foreach ($arr as $v) {
            $t = explode('=', $v);
            $key = $t[0];
            unset($t[0]); //移除键
            $newArr[$key] = implode('=', $t);    //防止数据中有等号出bug
        }
        return $newArr;
    }

    /**
     * 20230615:字符串行数
     * @param type $string
     * @return type
     */
    public static function lineCount($string) {
        $lines_arr = preg_split('/\n|\r/', $string);
        return count($lines_arr);
    }
    /**
     * 提取最外层括号内容
     * @param type $fullString
     */
    public static function getInnerBlank($fullString,$begin='(',$end=')'){
        $regStr = '/(?<=\\'.$begin.').*(?=\\'.$end.')/is';
        preg_match($regStr, $fullString, $matches);
        return $matches ? $matches[0] : '';
    }
    /**
     * 20231221:提取字符串中的参数
     */
    public static function getParams($str){
        $pattern = '/{\$([\w]+)\}/';
        preg_match_all($pattern, $str, $matches);
        return $matches[1];
    }
    /**
     * 各行开始位置添加空格
     * @param type $text            文本
     * @param type $numberOfSpaces  空格数
     * @return type
     */
    public static function addPreLineSpaces($text, $numberOfSpaces) {
        $lines  = explode("\n", $text);
        $spaces = str_repeat(' ', $numberOfSpaces);

        foreach ($lines as &$line) {
            $line = $spaces . $line;
        }
        // unset($line); // 取消最后一个元素的引用

        return implode("\n", $lines);
    }
    /**
     * 字符串按空格拆分成数组：多种空格
     * 2024-04-28
     * @param type $text
     * @return type
     */
    public static function blankExplode($text){
        return preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
    }
    
}
