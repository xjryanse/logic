<?php
namespace xjryanse\logic\ModelQueryCon;

use xjryanse\logic\interfaces\ModelQueryConInterface;
/**
 * 拼装模型查询条件
 */
class Like implements ModelQueryConInterface
{
    /**
     * 选项转换
     * @param type $type    类型
     * @param type $key     key
     * @param type $value   值
     * @return type
     */
    public static function getCon( $key, $value) {
        return [$key, 'like', '%' . $value . '%'];
    }
}
