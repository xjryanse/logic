<?php

namespace xjryanse\logic;

use Exception;
use think\facade\Request;
use xjryanse\logic\Arrays;
/**
 * 操作确认
 * 20231123：
 * 后台返回一个提示，让前台进行二次确认，如果已确认，则通过。
 */
class Operate {
    /**
     * 请求参数：
     * {"id":"5539321601631780864","eat_money":9,"CONFIRM_KEYS":["salaryLockDescConfirm"]}
     */
    public static function confirm($key, $confirm){
        $hasKeys = Request::param('CONFIRM_KEYS')? : [];
        // key未确认
        if(!in_array($key, $hasKeys)){
            // 全局返回data
            global $glRespData;
            // 2专门用于操作确认
            $data['confirmKeys'] = $key;
            $glRespData = $data;
            // 操作确认固定用2
            throw new Exception($confirm, 2);
        }
        // key 已确认
        return true;
    }

}
