<?php
namespace xjryanse\logic;

/**
 * url处理逻辑
 */
class Url
{
    /**
     * 一段url取后缀名
     * @param type $url
     * @return type
     */
    public static function getExt( $url) {
        $data       = explode('?', $url);
        $basename   = basename($data[0]);
        $basenames  = explode('.', $basename);
        return isset($basenames[1]) ? $basenames[1] : '' ;
    }
}
