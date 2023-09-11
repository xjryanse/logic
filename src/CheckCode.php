<?php

namespace xjryanse\logic;

/**
 * 校验码
 */
class CheckCode {

    /**
     * CRC16-CCITT
     * @param type $data
     * @return int
     */
    public static function crc16Ccitt($data) {
        $poly = 0x1021;
        $crc = 0xFFFF;
        $len = strlen($data);
        for ($i = 0; $i < $len; $i++) {
            $byte = ord($data[$i]);
            $crc ^= ($byte << 8);
            for ($j = 0; $j < 8; $j++) {
                if ($crc & 0x8000) {
                    $crc = ($crc << 1) ^ $poly;
                } else {
                    $crc <<= 1;
                }
            }
        }
        return $crc & 0xFFFF;
    }
    /**
     * 输入和输出都是十六进制
     * @param type $hexstr
     * @return type
     */
    public static function crc16CcittHex($hexstr) {
        $data = hex2bin($hexstr);
        $crc = self::crc16Ccitt($data);
        return sprintf("%04X", $crc);
    }
}
