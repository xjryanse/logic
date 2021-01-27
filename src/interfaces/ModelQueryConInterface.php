<?php
namespace xjryanse\logic\interfaces;

/**
 * 列表字段逻辑
 */
interface ModelQueryConInterface
{
    /**
     * 拼接查询数组
     * @param type $optionStr   &符号连接的字符串
     */
    public static function getCon( $key,$value );
}
