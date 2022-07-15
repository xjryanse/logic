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
     * @param string $cacheKey      缓存key
     * @param type $func            生成缓存的方法
     * @param type $withCompanyId   是否带本公司id
     * @param type $expire          过期时间，默认不过期
     * @return type
     */
    public static function funcGet($cacheKey , $func, $withCompanyId = false,$expire = null)
    {
        if($withCompanyId){
            $cacheKey = $cacheKey.'_'.session(SESSION_COMPANY_ID);
        }
        $cacheKeyValue = TpCache::get($cacheKey);
        //Debug::debug('$cacheKeyValue_'.$cacheKey,$cacheKeyValue);
        if(!$cacheKeyValue){
            //判断数据表是否存在
            $cacheKeyValue = $func();
            TpCache::set($cacheKey,$cacheKeyValue, $expire);
        }
        return $cacheKeyValue;
    }
    /**
     * set
     */
    public static function set( $cacheKey , $func, $withCompanyId = false){
        if($withCompanyId){
            $cacheKey = $cacheKey.'_'.session(SESSION_COMPANY_ID);
        }
        $cacheKeyValue = $func();
        TpCache::set($cacheKey,$cacheKeyValue);
        return $cacheKeyValue;
    }
    /**
     * 生成缓存key
     */
    public static function cacheKey(){
        $args = func_get_args();
        $jsonStr = json_encode($args);
        return md5($jsonStr);
    }
}
