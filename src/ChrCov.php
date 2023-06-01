<?php
namespace xjryanse\logic;

/**
 * 字节转换
 * 应用场景：JT808;GPS
 */
class ChrCov
{
    /**
     * 16进制字符串 转 二进制字符串
     * 7e08 转0111 1110 0000 1000
     * 16进制转为二进制字符串
     * 主要用于调试分析
     * @param string $data
     * @param type $blank
     * @return type
     */
    public static function hex2binStr(string $data, $blank = true){
        $length = strlen($data);
        // 20230330:每个16进制字符对应4个二进制字符
        $str = Strings::preKeepLength(base_convert($data, 16, 2), 4 * $length);
        if($blank){
            // 每4个字符添加一个空格
            $str = implode(' ', str_split($str, 4));
        }
        return $str;
    }

}
