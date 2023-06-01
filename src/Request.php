<?php

namespace xjryanse\logic;

use think\facade\Request as TpRequest;
use think\facade\Cache;
use Exception;
/**
 * 请求
 */
class Request {

    public static function param($name = '', $default = "") {
        //请求优先，请求没有则取路由
        if ($name) {
            return TpRequest::param($name, $default) ?: TpRequest::route($name, $default);
        } else {
            return array_merge(TpRequest::route(), TpRequest::param());
        }
    }

    public static function post($name = '', $default = "") {
        return TpRequest::post($name, $default);
    }

    public static function route($name = '', $default = "") {
        return TpRequest::route($name, $default);
    }

    public static function __callStatic($name, $arguments) {
        return TpRequest::$name($arguments);
    }

    /**
     * 只取一些
     * @param type $name
     * @return type
     */
    public static function only($name) {
        if (!is_array($name)) {
            $name = [$name];
        }
        $params = array_merge(TpRequest::route(), TpRequest::param());
        return Arrays::getByKeys($params, $name);
    }

    /**
     * 通用接口防抖函数
     */
    public static function antiRepeat() {
        // 模块名
        $modules    = TpRequest::module();
        // 控制器名称
        $controller = TpRequest::controller();
        // 方法名称
        $method     = TpRequest::action();
        
        $param      = TpRequest::param();
        $md5        = md5(json_encode($param, JSON_UNESCAPED_UNICODE));
        $key = 'AntiRepeat_' . $modules . '.' . $controller . '.' . $method . '.'.$md5;
        // 访问进行中
        if (Cache::get($key)) {
            throw new Exception('操作频繁');
        } else {
            // 3秒不能重复点击
            Cache::set($key,1,3);
        }
    }
    /**
     * 20230517:从请求头中提取访问来源信息
     */
    public static function source () {
        $header = TpRequest::header();
        return Arrays::value($header, 'source');
    }

    /**
     * 60秒内，同一个key,请求不超过5次
     * @param type $key
     * @param int $limit
     * @param type $second
     */
    public static function limit($key, $limit = 5, $second = 60){
	$redis = new \Redis();
	$redis->connect("127.0.0.1", 6379);
	// $redis->auth("php001");
	// 这个key记录该ip的访问次数 也可改成用户id
	// $key = "userid_11100";
	// $key = Request::ip();
	// 限制次数为5
	$check = $redis->exists($key);
	if($check){
            $redis->incr($key);  
            $count = $redis->get($key);
            if($count > $limit){
                throw new Exception('请求太频繁，请稍后再试！');
            }
	} else {
            $redis->incr($key);  
            //限制时间为60秒  
            $redis->expire($key, $second );
	}
    }
    
}
