<?php
namespace xjryanse\logic;

/**
 * 身份证号码处理函数
 */
class IdNo
{
    /**
     * 校验字符串是否合法身份证
     */
    public static function isIdNo($string){
        if(mb_strlen($string) != 18){
            return false;
        }
        $regx = "/^(\d{6})+(\d{4})+(\d{2})+(\d{2})+(\d{3})([0-9]|X)$/";
        @preg_match($regx, $string, $arr_split);
        $dtm_birth = $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];
        //检查生日日期是否正确
        if (!strtotime($dtm_birth)) {
            return false;
        }
        //检验18位身份证的校验码是否正确。
        //校验位按照ISO 7064:1983.MOD 11-2的规定生成，X可以认为是数字10。
        $arr_int = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
        $arr_ch = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
        $sign = 0;
        for ($i = 0; $i < 17; $i++) {
            $b = (int)$string{$i};
            $w = $arr_int[$i];
            $sign += $b * $w;
        }
        $n = $sign % 11;
        $val_num = $arr_ch[$n];
        return $val_num == substr($string, 17, 1);
    }
    /**
     * 获取生日
     */
    public static function getBirthday($idNo){
        $regx = "/^(\d{6})+(\d{4})+(\d{2})+(\d{2})+(\d{3})([0-9]|X)$/";
        @preg_match($regx, $idNo, $arr_split);
        return $arr_split[2] . '-' . $arr_split[3] . '-' . $arr_split[4];        
    }
    /**
     * 获取性别：1男；2女
     */
    public static function getSex($idNo){
        //取倒数第二位
        $number = substr($idNo, strlen($idNo) - 2, 1);
        //偶数女2；奇数男1；
        return $number % 2 == 0 ? 2:1;
    }
}
