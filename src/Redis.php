<?php

namespace xjryanse\logic;

use Redis as RedisSys;

/**
 * 请求
 */
class Redis {

    protected static $redis;

    /**
     * 获取redis连接实例
     * @return type
     */
    protected static function getRedis() {
        if (!self::$redis) {
            self::$redis = new RedisSys();
            self::$redis->connect('127.0.0.1', 6379);
        }
        return self::$redis;
    }

    /**
     * 20230621:输入测试数据
     */
    public static function logTestData($data, $key = 'testKey') {
        $lData['data'] = $data;
        $lData['time'] = date('Y-m-d H:i:s');

        self::getRedis()->set($key, json_encode($lData, JSON_UNESCAPED_UNICODE));
    }

    /**
     * 20230621:打印测试数据
     * @param type $key
     */
    public static function dumpTestData($key = 'testKey') {
        $res = self::getRedis()->get($key);
        dump($res);
    }
}
