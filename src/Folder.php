<?php

namespace xjryanse\logic;

/**
 * 文件夹处理
 */
class Folder {

    /**
     * 
     * @param string $path   要获取的目录
     */
    public static function getFiles($path) {
        if (!is_dir($path)) {
            return false;
        }
        //scandir方法
        $arr = array();
        $data = scandir($path);
        foreach ($data as $value) {
            if ($value != '.' && $value != '..') {
                $arr[] = $value;
            }
        }
        return $arr;
    }

}
