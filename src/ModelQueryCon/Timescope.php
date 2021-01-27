<?php
namespace xjryanse\logic\ModelQueryCon;

use xjryanse\logic\interfaces\ModelQueryConInterface;
/**
 * 拼装模型查询条件
 */
class Timescope implements ModelQueryConInterface
{
    /**
     * 选项转换
     * @param type $type    类型
     * @param type $key     key
     * @param type $value   值
     * @return type
     */
    public static function getCon( $key, $value) {
        $value[0] = date('Y-m-d H:i:s', strtotime($value[0]));
        $value[1] = date('Y-m-d H:i:s', strtotime($value[1]));
        
        return [ $key,'between',[$value[0],$value[1]]];
    }
}
