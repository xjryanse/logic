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
        // 20220814:优化查询空值的缓存结果
        if(!TpCache::has($cacheKey)){
            //判断数据表是否存在
            $cacheKeyValue = $func();
            TpCache::set($cacheKey,$cacheKeyValue, $expire);
        }
        return $cacheKeyValue;
    }

    /**
     * 仅从缓存中提取
     * @param string $cacheKey      缓存key
     * @param type $func            生成缓存的方法
     * @param type $withCompanyId   是否带本公司id
     * @param type $expire          过期时间，默认不过期
     * @return type
     */
    public static function get($cacheKey , $withCompanyId = false)
    {
        if($withCompanyId){
            $cacheKey = $cacheKey.'_'.session(SESSION_COMPANY_ID);
        }
        $cacheKeyValue = TpCache::get($cacheKey);

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
     * 设定值
     */
    public static function setVal( $cacheKey , $cacheKeyValue, $withCompanyId = false){
        if($withCompanyId){
            $cacheKey = $cacheKey.'_'.session(SESSION_COMPANY_ID);
        }
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
    /**
     * 清除缓存，不清某些key
     */
    public static function clearExcept($keys = []){
        $arr = [];
        //【1】先存起来
        foreach($keys as $k){
            $arr[$k] = TpCache::get($k);
        }
        //【2】清缓存
        TpCache::clear();
        //【3】回写
        foreach($arr as $kk=>$vv){
            TpCache::set($kk,$vv);
        }
    }
}
