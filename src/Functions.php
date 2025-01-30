<?php

namespace xjryanse\logic;

use think\facade\Cache;
/**
 * 运行方法
 */
class Functions {
    /**
     * 20230607:批量id数据梳理
     * @param type $id
     */
    public static function batchId($ids, $func = null){
        //数组返回多个，非数组返回一个
        $isMulti = is_array($ids);
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        //20220619:优化性能
        if(!$ids){
            return [];
        }
        $res = [];
        foreach($ids as $id){
            // 调用闭包方法
            $res[] = $func($id);
        }
        
        return $isMulti ? $res : $res[0];
    }
    /**
     * 20230811:接口防抖
     * @param type $method  方法名
     * @param type $param   参数
     * @param type $func    闭包
     */
    public static function anti($method, $param, $func = null){
        $paramJson = json_encode($param);
        $cacheKey = 'data_'.$method.'_'.md5($paramJson);
        // 处理
        $isDoing = Cachex::isDoing($method, $param);
        if($isDoing){
            $res = [];
            //死循环
            $loop = true;
            $startTime = time();
            while($loop){
                $res = Cache::get($cacheKey);
                // 有取到结果，或者超时5秒;
                if($res || time() - $startTime >=5 ){
                    $loop = false;
                } else {
                    usleep(200);
                }
            }
        } else {
            // 标记为处理中
            Cachex::markDoing($method, $param);
            $res = $func($param);
            Cache::set($cacheKey, $res);
        }
        return $res;
    }
    
    /**
     * 20240830 计算脚本执行时长
     */
    public static function execTime($func = null){
        $startTime  = microtime(true);
        
        call_user_func($func);

        $endTime    = microtime(true);
        return $endTime - $startTime;
    }
}
