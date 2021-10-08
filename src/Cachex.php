<?php
namespace xjryanse\logic;

use think\facade\Cache as TpCache;
/**
 * 缓存增强
 */
class Cachex
{    
    /**
     * 有缓存取缓存，无缓存闭包算
     * @param type $cacheKey
     * @param type $func
     * @return type
     */
    public static function funcGet($cacheKey , $func, $withCompanyId = false)
    {
        if($withCompanyId){
            $cacheKey = $cacheKey.'_'.session(SESSION_COMPANY_ID);
        }
        $cacheKeyValue = TpCache::get($cacheKey);
        if(!$cacheKeyValue){
            //判断数据表是否存在
            $cacheKeyValue = $func();
            TpCache::set($cacheKey,$cacheKeyValue);
        }
        return $cacheKeyValue;
    }
}
