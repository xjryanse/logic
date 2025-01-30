<?php

namespace xjryanse\logic;

use xjryanse\system\logic\ConfigLogic;
use xjryanse\curl\Query;
use xjryanse\logic\Url;
use Exception;
use think\facade\Request;
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
        // 20240107:增加入参基本站 系统基本站点
        $baseHost   = Request::param('systemBaseHost') ? : ConfigLogic::config('systemBaseHost');
        if(!$baseHost){
            return false;
        }
        $companyKey = Request::param('comKey') ? : session(SESSION_COMPANY_KEY);
        // $url        = $baseHost.'/'.session(SESSION_COMPANY_KEY).'/webapi/Universal/pageGet';
        $url        = $baseHost.$companyKey.$url;
        $finalUrl   = Url::addParam($url, $param);

        $res        = Query::get($finalUrl);
        if($res['code'] == 0){
            return $res['data'];
        } else {
            throw new Exception('基本站异常：'.$res['message']);
        }
    }
}
