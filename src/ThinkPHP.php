<?php

namespace xjryanse\logic;

use think\Container;
/**
 * TP框架相关的处理
 */
class ThinkPHP {
    /**
     * 20230528:提取全部模块名
     * @return type
     */
    public static function modules(){
        $appPath = Container::get('app')->getAppPath();
        $modulePath = $appPath . DIRECTORY_SEPARATOR;  //控制器路径
        if(!is_dir($modulePath)){
            return [];
        }
        $aryPath = glob($modulePath . '/*' , GLOB_ONLYDIR);
        $modules = [];
        foreach ($aryPath as $path) {
            $modules[] = basename($path);
        }
        return $modules;
    }
    
    /**
     * 20230528：提取指定模块全部控制器
     * @param type $module
     * @return type
     */
    public static function controllers($module){
        if(empty($module)){
            return [];
        }
        $appPath = Container::get('app')->getAppPath();
        $modulePath = $appPath . DIRECTORY_SEPARATOR . $module .DIRECTORY_SEPARATOR. 'controller'.DIRECTORY_SEPARATOR;  //控制器路径
        if(!is_dir($modulePath)){
            return [];
        }
        $modulePath .= '*.php';
        $aryFiles = glob($modulePath);
        foreach ($aryFiles as $file) {
            if (is_dir($file)) {
                continue;
            }else {
                $files[] = basename($file,'.php');
            }
        }
        return $files;
    }
    
    /**
     * @note 获取方法
     *
     * @param $module
     * @param $controller
     *
     * @return array|null
     */
    public static  function actions($module, $controller){
        if(empty($controller)) {
            return [];
        }
        
        $appPath = Container::get('app')->getAppPath();

        $content = file_get_contents($appPath .DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR.'controller'.DIRECTORY_SEPARATOR.$controller.'.php');
        
        preg_match_all("/.*?public.*?function(.*?)\(.*?\)/i", $content, $matches);
        $functions = $matches[1];
        
        $customerFunctions = [];
        foreach ($functions as $func){
            $func = trim($func);
            if (strlen($func)>0){
                $customerFunctions[] = $func;
            }
        }

        return $customerFunctions;
    }
    /**
     * 20230528:方法数组（三合一）
     * @return type
     */
    public static function methodsArr(){
        $modules = ThinkPHP::modules();
        $arr = [];
        foreach($modules as $module){
            $controllers = ThinkPHP::controllers($module);
            foreach($controllers as $controller){
                $actions = ThinkPHP::actions($module, $controller);
                foreach($actions as $action){
                    $tmp = [];
                    $tmp['module']      = $module;
                    $tmp['controller']  = $controller;
                    $tmp['action']      = $action;
                    $arr[] = $tmp;
                }
            }
        }
        return $arr;
    }
}
