<?php
namespace xjryanse\logic;

use Exception;
/**
 * 数据处理
 * 添加一些默认值之类的操作
 */
class DataDeal
{

    /**
     * 当key值无值时，用$defaults值替代
     * 
     * @param type $data        源数据
     * @param type $defaults    键值对
     * 
     */
    public static function emptyDefault(&$data, $defaults = []){
        foreach($defaults as $k=>$v){
            if(!Arrays::value($data, $k)){
                $data[$k] = $v;
            }
        }
        return $data;
    }
    
    /**
     * 提取单一值
     * @param type $data    输入原始对象数据
     * @param type $key     key
     * @param type $default 默认值
     * @return type
     */
    public static function valWDefault($data, $key, $default = ''){
        $kVal = isset($data[$key])
                ?  Arrays::value($data, $key)
                : $default;
        return $kVal;
    }
    /**
     * 20240610:当有该参数，且无值时，写默认值
     * 用于
     * @param type $data
     */
    public static function issetDefault(&$data, $defaultData = []){
        foreach($defaultData as $k=>$v){
            if(isset($data[$k]) && !$data[$k]){
                $data[$k] = $v;
            }
        }
    }

}
