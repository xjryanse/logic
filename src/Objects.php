<?php
namespace xjryanse\logic;

/**
 * 字符串处理函数
 */
class Objects
{
    /**
     * 20230531:对象转数组
     */
    public static function toArray($object){
        return json_decode(json_encode($object), true);
    }

}
