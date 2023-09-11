<?php

namespace xjryanse\logic;

use xjryanse\system\logic\ConfigLogic;
use xjryanse\curl\Query;
use xjryanse\logic\Url;
use Exception;
/**
 * 基本站系统逻辑
 */
class BaseSystem {
    /**
     * 通用的远端获取
     * @param type $id
     * @return boolean
     * @throws Exception
     */
    public static function baseSysGet($url, $param = []){
        // 系统基本站点
        $baseHost   = ConfigLogic::config('systemBaseHost');
        if(!$baseHost){
            return false;
        }

        // $url        = $baseHost.'/'.session(SESSION_COMPANY_KEY).'/webapi/Universal/pageGet';
        $url        = $baseHost.'/'.session(SESSION_COMPANY_KEY).$url;
        $finalUrl   = Url::addParam($url, $param);

        $res        = Query::get($finalUrl);
        if($res['code'] == 0){
            return $res['data'];
        } else {
            throw new Exception('基本站异常：'.$res['message']);
        }
    }
}
