<?php
namespace xjryanse\logic;

use Exception;
/**
 * 一维数组处理逻辑
 */
class Arrays
{
    /**
     * 数组取值
     */
    public static function value( $array , $key, $default='' )
    {
        return $array && isset($array[ $key ]) ? $array[ $key ] : $default;
    }
    
    /**
     * 数组指定键取值
     * @param type $array   数据数组
     * @param type $keys    键值数组
     * @return type
     */
    public static function getByKeys(array $array, $keys )
    {
        // 20230609:兼容逗号分隔
        if(!is_array($keys)){
            $keys = explode(',',$keys);
        }
        $match = array_fill_keys($keys, "");
        //比较两个（或更多个）数组的键名 ，并返回交集。
        return array_intersect_key( $array , $match);
    }
    /**
     * 2022-12-17：隐藏某些key
     * @param array $array
     * @param array $keys
     * @return array
     */
    public static function hideKeys(array $array,array $keys )
    {
        $match = array_fill_keys($keys, "");
        //比较两个（或更多个）数组的键名 ，并返回差集。
        return array_diff_key( $array , $match);
    }
    /**
     * 移除指定键
     */
    public static function unset( &$array, $keys)
    {
        if(!is_array($keys)){
            $keys = [$keys];
        }
        foreach( $keys as $key){
            if(isset($array[$key])){
                unset( $array[$key] );
            }
        }
        return $array;
    }
    /**
     * 一维数组键名替换
     * @param array $data =  ['key1'=>'value1','key2'=>'value2','key3'=>'value3'];
     * @param array $keys =  ['key1'=>'res1','key3'=>'res3','key4'=>'res4'];
     * @return type          ['res1'=>'value1','res3'=>'value3'];
     */
    public static function keyReplace( $data, array $keys )
    {
        if(!$data){
            return [];
        }
        
        $values     = array_intersect_key($data, $keys);
        ksort($values);
        $repKeys    = array_intersect_key($keys, $values);
        ksort($repKeys);
        
        return array_combine($repKeys, $values);
    }
    
    /**
     * 去除多余参数，只保留id
     * @param type $data    数据包
     * @param type $fields  待留键的字段
     * @param type $key     键名
     * @return type
     */
    public static function onlyKey( &$data, $fields, $key="id" )
    {
        if(!is_array($fields)){
            $fields = [ $fields ];
        }
        foreach( $fields as $v){
            //数据存在，数据是数组，待取的键存在
            if(isset($data[$v]) && is_array($data[$v]) && isset( $data[$v][$key] ) ){
                $data[$v] = $data[$v][$key];
            } else {
                //没有该字段，数据置空
                $data[$v] = '';
            }
        }
        return $data;
    }    
    /**
     * 值查找键，返回包含该值的全部键名
     * @param type $array   数组
     * @param type $value   值
     * @return type
     */
    public static function valueKeys(array $array, $value)
    {
        if(!$value){
            return array_keys($array);
        }
        $arrayKeys = [];
        foreach( $array as $k=>$v){
            if( !is_array($v) && $v == $value ){
                $arrayKeys[] = $k;
            }
            if( is_array($v) && in_array( $value, $v ) ){
                $arrayKeys[] = $k;
            }
        }
        return $arrayKeys;
    }
    /**
     * 形如prizeInfo.sellerTmAuthDeposit的key，转为['prizeInfo']['sellerTmAuthDeposit']
     */
    public static function keySplit( $array, $split = "." )
    {
        foreach( $array as $key=>&$value ){
            if( !strstr( $key, $split )){    continue;    }
            $keys = explode('.',$key);
            $tmpArr = &$array;
            foreach( $keys as $kk=>$vv){
                $tmpArr[$vv] = isset($tmpArr[$vv]) ? $tmpArr[$vv] : [] ;
                $tmpArr = &$tmpArr[$vv];
            }
            //最根根赋值
            $tmpArr = $value;
            unset($array[$key]);
        }
        return $array;
    }
    /**
     * 判断数组是否匹配子数组
     * @param type $arr     一维数组（主）
     * @param type $subArr  一维数组（子）,键值对
     * @return bool
     */
    public static function isMatch($arr,$subArr = []){
        if(!$subArr){
            return true;
        }
        foreach($subArr as $k=>$v){
            if(self::value($arr, $k) != $v){
                return false;
            }
        }
        return true;
        
    }
    
    /**
     * 查询条件判断数据是否匹配，查询条件格式兼容数据库查询
     * @param type $data
     * @param type $con
     * @return boolean
     */
    public static function isConMatch( $data, $con){
        if(!$con){
            return true;
        }
        //符号替换
        $signReplace['='] = '==';   // 等号'
        $signReplace['<>'] = '!=';  //不等号'
        
        foreach( $con as $cond){
            $key    = $cond[0];
            $sign   = $cond[1];
            $value  = $cond[2];
            
            if(!isset($data[$key])){
                return false;
            }
            // 等于号和不等于号
            if( in_array($sign,['=','<>','>','<','>=','<='])){
                //符号替换
                $signN = Arrays::value($signReplace, $sign, $sign);
                //有一个不匹配，则不匹配
                $evalStr = 'return \'' . $data[$key] . '\' ' . $signN. ' \'' . $value . '\';';
                if( !eval($evalStr)){
                    return false;
                }
            }
            // in
            if( $sign == 'in'){
                $dataArr = is_array($value) ? $value : [$value];
                if( !in_array($data[$key], $dataArr)){
                    return false;
                }
            }
            // 2022-12-18:like
            if( $sign == 'like'){
                $searchStr  = str_replace('%', '',$value);
                $reg = '';
                if(!Strings::isStartWith($value, '%')){
                    $reg = '^';
                }
                $reg .= $searchStr;
                if(!Strings::isEndWith($value, '%')){
                    $reg .= '$';
                }
                // 2022-12-18:大小写不敏感
                if(!preg_match('/'.$reg.'/i', $data[$key])){
                    return false;
                }
            }            
        }
        //全部匹配，才匹配
        return true;        
    }
    /**
     * 数组是否空
     */
    public static function isEmpty($array){
        if(!$array){
            return true;
        }
        //情况2：只有一个null值
        $arrUniq = array_unique($array);
        return count($arrUniq) == 1 && is_null($arrUniq[0]);
    }
    /**
     * 数组组合
     * @param type $mainArray
     * @param type $subArray
     * @param type $split
     * @return string
     */
    public static function combineArray( $mainArray,$subArray,$split='_'){
        $arr = [];
        foreach($mainArray as $value){
            foreach($subArray as $subValue){
                // $arr[] = $value ? $value.$split.$subValue : $subValue;
                $arr[] = $value ? array_merge($value,[$subValue]) : [$subValue];
            }
        }
        return $arr;
    }
    
    public static function md5($array){
        return md5(json_encode($array));
    }
    /**
     * 过滤空数据
     * @param type $param       参数
     * @param type $exceptKeys  不去除的key
     * @return type
     */
    public static function unsetEmpty( &$param ,$exceptKeys = ['id']){
        //20211106 解决报错 Invalid argument supplied for foreach()
        if(DataCheck::isEmpty($param)){
            return [];
        }
        foreach($param as $k=>&$v){
            // 值为空且（索引是数值，或不在排除key中）
            if((is_null($v) || $v === '') && (is_numeric($k) || !in_array($k, $exceptKeys))){
                unset($param[$k]);
            }
//            if(!is_array($v) && !strlen($v)){
//                unset($param[$k]);
//            }
            //递归
            if(is_array($v)){
                self::unsetEmpty( $v);
            }
        }
        return $param;
    }
    /**
     * 20221111转XML字串
     */
    public static function toXml($data,$withFix = true){
        $string = '';
        foreach($data as $k=>$v){
            $string .= '<'.$k.'>';
            $string .= $v;
            $string .= '</'.$k.'>';
        }
        return $withFix 
                ? '<xml>'. $string. '</xml>'
                : $string;
    }
    /**
     * 获取差异数组
     * [变更前，变更后]
     */
    public static function diffArr($preArr, $afterArr ){
        // 20230904: 处理null情况
        if(!$preArr){
            return $afterArr;
        }
        $diffArr = [];
        foreach($afterArr as $k=>$v){
            foreach($preArr as $kp=>$vp){
                if($kp == $k && $v != $vp){
                    $diffArr[$kp] = [$vp, $v];
                }
            }
        }
        return $diffArr;
    }
    /**
     * 20230203:解决array_sum小数点不准
     */
    public static function sum($array){
        $arrCov = [];
        foreach($array as $v){
            // 20240511:不是数值型的，返回空字符串
            if($v && !is_numeric($v)){
                return '';
            }
            if(!$v){
                $v = 0;
            }
            $arrCov[] = intval($v * 1000); 
        }
        return array_sum($arrCov) / 1000;
    }
    /**
     * 如果所给定的值是空的，用$replace的值替代
     * @param type $data
     * @param type $key
     * @param type $replace
     */
    public static function ifEmptyReplace(&$data,$key,$replace){
        if(!Arrays::value($data, $key)){
            $data[$key] = $replace;
        }
    }
    /**
     * 20230519：提取数组的最后一个值
     */
    public static function last($array){
        return array_pop($array);
    }
    /**
     * 20230718
     * @param type $data
     * @param type $picFields
     * @return type
     */
    public static function picFieldCov(&$data,$picFields = []){
        $arrCov = [$data];
        $arr = Arrays2d::picFieldCov($arrCov, $picFields);
        return $arr[0];
    }

    /**
     * 20230730:合并数组，支持带键
     */
    public static function concat($arr1, $arr2){
        if(!$arr1){
            return $arr2;
        }
        if(!$arr2){
            return $arr1;
        }
        // 雪花key被当成数字咋整
        // 如果要保持键为字符串，可以使用 + 运算符来进行数组合并，而不是使用 array_merge() 函数。它会保留字符串键，并避免将其视为数字。
        // 使用 + 运算符需要注意的一点是，如果键相同，后面的数组中具有相同键的元素将会被忽略。这意味着在合并数组时，如果有重复的键，只有第一个数组中的元素将被保留。
        return $arr2 + $arr1;
    }
    
    
    /**  参数排序拼接
     * @param $array
     * @return string
     */
    public static function toUrlParams(array $array){
        $buff = "";
        foreach ($array as $k => $v)
        {
            if($v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }
        return trim($buff, "&");
    }
    
    /*
     * 使用给定字符串替换已有字符串
     * 一般用于复制通用表单时，替换表单key
     * @param type $arr
     * @param type $replaceArr      替换字段：['T26N010'=>'T26N011'] 
     * @param type $minLen          安全起见限制的最小字符
     * @return type
     * @throws Exception
     */
    public static function strReplace($arr , $replaceArr = [], $minLen = 5){
        if(!$replaceArr){
            return $arr;
        }
        
        $str = json_encode($arr, JSON_UNESCAPED_UNICODE);
        foreach($replaceArr as $k=>$v){
            if(mb_strlen($k) < $minLen || mb_strlen($v) < $minLen){
                throw new Exception('不支持替换少于'.$minLen.'的字符串');
            }
            $str = str_replace($k, $v, $str);
        }

        return json_decode($str, JSON_UNESCAPED_UNICODE);
    }
    /**
     * 是否有这些key,有一个即可
     */
    public static function hasKeys($data, $keys){
        foreach($keys as $k){
            if(isset($data[$k])){
                return true;
            }
        }
        
        return false;
    }
    /**
     * 是否有这些key,需全部有
     */
    public static function hasKeysAll($data, $keys){
        foreach($keys as $k){
            if(!isset($data[$k])){
                return false;
            }
        }
        return true;
    }
    /**
     * 20240105:唯一合并
     * @param type $arr1
     * @param type $arr2
     * @return type
     */
    public static function uniqueMerge($arr1,$arr2){
        return array_unique(array_merge($arr1, $arr2));
    }
    
    /**
     * 通用，先字符串化，在回转
     * 20240306
     */
    public static function dataReplace($arr, $data){
        $str    = json_encode($arr,JSON_UNESCAPED_UNICODE);
        $fStr   = Strings::dataReplace($str, $data);
        return json_decode($fStr, JSON_UNESCAPED_UNICODE);
    }
    /**
     * 20250126:数组深度
     */
    public static function deepth($array){
        $maxDepth = 1;

        foreach ($array as $value) {
            if (is_array($value)) {
                $depth = self::deepth($value) + 1;
                if ($depth > $maxDepth) {
                    $maxDepth = $depth;
                }
            }
        }

        return $maxDepth;
    }
    /**
     * 计算数组占用行数;
     * 二维子数组，按实际行数计算
     */
    public static function ocpRowCount($array){
        foreach ($array as $k=>$item) {
            if(!is_array($item)){
                continue;
            }
            if (Arrays2d::isArrays2d($item)) {
                // 若有子数组，占用行数为子数组数量
                return count($item);
            }
            // 数组不是空的，才占2行，否则算占1行
            if($item){
                return 2;
            }
        }
        return 1;
    }
    
}
