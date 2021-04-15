<?php

namespace xjryanse\logic;

/**
 * 金额处理
 */
class Prize {

    /**
     * 金额转大写
     * @param type $pdf
     * @param type $path
     * @return boolean|string
     */
    public static function toBig($num) {
        $digits = ['零', '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖'];
        $radices = ['', '拾', '佰', '仟', '万', '亿'];
        $bigRadices = ['', '万', '亿'];
        $decimals = ['角', '分'];
        $cn_dollar = '元';
//        $cn_integer = '整';
        $num_arr = explode('.', $num);
        $int_str = $num_arr[0] ?? '';
        $float_str = $num_arr[1] ?? '';
        $outputCharacters = '';
        if ($int_str) {
            $int_len = strlen($int_str);
            $zeroCount = 0;
            for ($i = 0; $i < $int_len; $i++) {
                $p = $int_len - $i - 1;
                $d = substr($int_str, $i, 1);
                $quotient = $p / 4;
                $modulus = $p % 4;
                if ($d == "0") {
                    $zeroCount++;
                } else {
                    if ($zeroCount > 0) {
                        $outputCharacters .= $digits[0];
                    }
                    $zeroCount = 0;
                    $outputCharacters .= $digits[$d] . $radices[$modulus];
                }
                if ($modulus == 0 && $zeroCount < 4) {
                    $outputCharacters .= $bigRadices[$quotient];
                    $zeroCount = 0;
                }
            }
            $outputCharacters .= $cn_dollar;
        }
        if ($float_str) {
            $float_len = strlen($float_str);
            for ($i = 0; $i < $float_len; $i++) {
                $d = substr($float_str, $i, 1);
                if ($d != "0") {
                    $outputCharacters .= $digits[$d] . $decimals[$i];
                }
            }
        }
        if ($outputCharacters == "") {
            $outputCharacters = $digits[0] . $cn_dollar;
        }
        if ($float_str && $float_str!="00") {
//            $outputCharacters .= $cn_integer;
        }
        return $outputCharacters."整";
    }
}
