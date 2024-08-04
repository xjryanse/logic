<?php

namespace xjryanse\logic;

/**
 * 坐标系转换类库
 */
class Gps {

    const x_PI = 52.35987755982988;
    const PI = 3.1415926535897932384626;
    const a = 6378245.0;
    const ee = 0.00669342162296594323;

    /**
     * WGS84转GCj02(北斗转高德)
     * @param lng
     * @param lat
     * @returns {*[]}
     */
    public static function wgs84ToGcj02($lng, $lat) {
        if (self::outOfChina($lng, $lat)) {
            return array($lng, $lat);
        } else {
            $dlat0 = self::transformlat($lng - 105.0, $lat - 35.0);
            $dlng0 = self::transformlng($lng - 105.0, $lat - 35.0);
            $radlat = $lat / 180.0 * self::PI;
            $magic0 = sin($radlat);
            $magic = 1 - self::ee * $magic0 * $magic0;
            $sqrtmagic = sqrt($magic);
            $dlat = ($dlat0 * 180.0) / ((self::a * (1 - self::ee)) / ($magic * $sqrtmagic) * self::PI);
            $dlng = ($dlng0 * 180.0) / (self::a / $sqrtmagic * cos($radlat) * self::PI);
            $mglat = $lat + $dlat;
            $mglng = $lng + $dlng;
            return array($mglng, $mglat);
        }
    }

    /**
     * GCJ02 转换为 WGS84 (高德转北斗)
     * @param lng
     * @param lat
     * @return array(lng, lat);
     */
    public static function gcj02ToWgs84($lng, $lat) {
        if (self::outOfChina($lng, $lat)) {
            return array($lng, $lat);
        } else {
            $dlat0 = self::transformlat($lng - 105.0, $lat - 35.0);
            $dlng0 = self::transformlng($lng - 105.0, $lat - 35.0);
            $radlat = $lat / 180.0 * self::PI;
            $magic0 = sin($radlat);
            $magic = 1 - self::ee * $magic0 * $magic0;
            $sqrtmagic = sqrt($magic);
            $dlat = ($dlat0 * 180.0) / ((self::a * (1 - self::ee)) / ($magic * $sqrtmagic) * self::PI);
            $dlng = ($dlng0 * 180.0) / (self::a / $sqrtmagic * cos($radlat) * self::PI);
            $mglat = $lat + $dlat;
            $mglng = $lng + $dlng;
            return array($lng * 2 - $mglng, $lat * 2 - $mglat);
        }
    }

    /**
    * 百度坐标系 (BD-09) 与 火星坐标系 (GCJ-02)的转换
    * 即 百度 转 谷歌、高德
    * @param bd_lon
    * @param bd_lat
    * @returns
     */
    public static function bd09ToGcj02($bd_lon, $bd_lat) {
        $x_pi = 3.14159265358979324 * 3000.0 / 180.0;
        $x = $bd_lon - 0.0065;
        $y = $bd_lat - 0.006;
        $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $x_pi);
        $theta = atan2($y, $x) - 0.000003 * cos($x * $x_pi);
        $gg_lng = $z * cos($theta);
        $gg_lat = $z * sin($theta);
        return array($gg_lng, $gg_lat);
    }

    /**
     * GCJ-02 转换为 BD-09  （火星坐标系 转百度即谷歌、高德 转 百度）
     * @param $lng
     * @param $lat
     * @returns array(bd_lng, bd_lat)
     */
    public static function gcj02ToBd09($lng, $lat) {
        $z = sqrt($lng * $lng + $lat * $lat) + 0.00002 * Math . sin($lat * x_PI);
        $theta = Math . atan2($lat, $lng) + 0.000003 * Math . cos($lng * x_PI);
        $bd_lng = $z * cos($theta) + 0.0065;
        $bd_lat = z * sin($theta) + 0.006;
        return array($bd_lng, $bd_lat);
    }

    private static function transformlat($lng, $lat) {
        $ret = -100.0 + 2.0 * $lng + 3.0 * $lat + 0.2 * $lat * $lat + 0.1 * $lng * $lat + 0.2 * sqrt(abs($lng));
        $ret += (20.0 * sin(6.0 * $lng * self::PI) + 20.0 * sin(2.0 * $lng * self::PI)) * 2.0 / 3.0;
        $ret += (20.0 * sin($lat * self::PI) + 40.0 * sin($lat / 3.0 * self::PI)) * 2.0 / 3.0;
        $ret += (160.0 * sin($lat / 12.0 * self::PI) + 320 * sin($lat * self::PI / 30.0)) * 2.0 / 3.0;
        return $ret;
    }

    private static function transformlng($lng, $lat) {
        $ret = 300.0 + $lng + 2.0 * $lat + 0.1 * $lng * $lng + 0.1 * $lng * $lat + 0.1 * sqrt(abs($lng));
        $ret += (20.0 * sin(6.0 * $lng * self::PI) + 20.0 * sin(2.0 * $lng * self::PI)) * 2.0 / 3.0;
        $ret += (20.0 * sin($lng * self::PI) + 40.0 * sin($lng / 3.0 * self::PI)) * 2.0 / 3.0;
        $ret += (150.0 * sin($lng / 12.0 * self::PI) + 300.0 * sin($lng / 30.0 * self::PI)) * 2.0 / 3.0;
        return $ret;
    }

    private static function rad($param) {
        return $param * self::PI / 180.0;
    }

    /**
     * 判断是否在国内，不在国内则不做偏移
     * @param $lng
     * @param $lat
     * @returns {boolean}
     */
    private static function outOfChina($lng, $lat) {
        return ($lng < 72.004 || $lng > 137.8347) || (($lat < 0.8293 || $lat > 55.8271) || false);
    }
    /**
     * 两点之间的距离（注意是同一坐标系）
     * @param type $lng1
     * @param type $lat1
     * @param type $lng2
     * @param type $lat2
     * @param type $lenType 0:m; 1:km
     * @param type $decimal
     * @return type
     */
    public static function getDistance($lng1, $lat1, $lng2, $lat2, $lenType = 0, $decimal = 2) {
        $radLat1 = $lat1 * PI() / 180.0;   //PI()圆周率
        $radLat2 = $lat2 * PI() / 180.0;
        $a = $radLat1 - $radLat2;
        $b = ($lng1 * PI() / 180.0) - ($lng2 * PI() / 180.0);
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
        $s = $s * 6378.245;
        $s = round($s * 1000);
        if ($lenType ) {
            $s /= 1000;
        }
        return round($s, $decimal);
    }
    /**
     * 20231114:计算key
     * @return type
     */
    public static function regeoKey($lon,$lat){
        return hash('sha256', $lon.$lat);
    }
    
}
