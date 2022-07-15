<?php
namespace xjryanse\logic;

/**
 * url处理逻辑
 */
class Url
{
    /**
     * 一段url取后缀名
     * @param type $url
     * @return type
     */
    public static function getExt( $url) {
        $data       = explode('?', $url);
        $basename   = basename($data[0]);
        $basenames  = explode('.', $basename);
        return isset($basenames[1]) ? $basenames[1] : '' ;
    }
    /**
     * 往url中添加参数
     * @param type $url     url
     * @param type $param   参数数组
     * @return string
     */
    public static function addParam( $url, $param, $exceptParam = []){
        //拆解参数
        $parseUrl = explode('?',$url); 
        if(isset($parseUrl[1])){
            //合并参数
            $param = array_merge( equalsToKeyValue($parseUrl[1]) , $param );
        }
        //剔除参数
        if($exceptParam){
            foreach($exceptParam as $key){
                if(isset($param[$key])){
                    unset($param[$key]);
                }
            }
        }
        //拼接参数
        $urlRes = $parseUrl[0];
        foreach($param as $k=>$v){
            if( strstr ($urlRes,'?')){
                $urlRes .= '&'. $k .'='.$v;
            } else {
                $urlRes .= '?'. $k .'='.$v;
            }
        }
        return $urlRes;
    }
    /**
     * 空格转%20；中文转%
     * @param type $url
     * @return type
     */
    public static function encodeRaw($url){
        $uri = '';
        $cs = unpack('C*', $url);
        $len = count($cs);
        for ($i=1; $i<=$len; $i++) {
          $uri .= $cs[$i] > 127 ? '%'.strtoupper(dechex($cs[$i])) : $url{$i-1};
        }
        return $uri;
    }
    
}
