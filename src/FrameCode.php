<?php

namespace xjryanse\logic;

use think\Container;
use think\Loader;
use think\facade\Request;
use xjryanse\logic\Strings;
use xjryanse\system\service\SystemServiceMethodLogService;
use xjryanse\system\service\SystemLogService;
use xjryanse\logic\DbOperate;

/**
 * 框架代码
 */
class FrameCode {
    
    /**
     * 20230528:提取全部模块名
     * @return type
     */
    public static function modules(){
        $cacheKey = __METHOD__;
        return Cachex::funcGet($cacheKey, function(){
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
        });
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
        return CodeFile::getPathClasses($modulePath);
    }
    /**
     * 20230803：提取指定模块app路径下全部模型类
     * @param type $module
     * @return type
     */
    public static function modelsApp($module){
        if(empty($module)){
            return [];
        }
        $appPath = Container::get('app')->getAppPath();
        $modulePath = $appPath . DIRECTORY_SEPARATOR . $module .DIRECTORY_SEPARATOR. 'model'.DIRECTORY_SEPARATOR;  //控制器路径
        return CodeFile::getPathClasses($modulePath);
    }
    
    public static function modelsAppArr($con = []){
        // if(!property_exists($class, $property))
        $cacheKey = __METHOD__;
        $listsAll = Cachex::funcGet($cacheKey, function(){
            $tableModules   = DbOperate::allModules();
            $codeModules    = self::modules();
            $modules        = array_unique(array_merge($tableModules, $codeModules));
            $arr = [];
            foreach($modules as &$module){
                $modelsApps = self::modelsApp($module);
                foreach($modelsApps as &$modelsApp){
                    $arr[] = self::classDetail('app', $module, 'model',$modelsApp);
                }
            }
            return $arr;
        });
        return Arrays2d::listFilter($listsAll, $con);
    }
    
    /**
     * 20230803：提取指定模块xjryanse框架路径下全部模型类
     * @param type $module
     * @return type
     */
    public static function modelsXie($module){
        if(empty($module)){
            return [];
        }
        $rootPath   = Container::get('app')->getRootPath();
        $modulePath = $rootPath . 'vendor' . DIRECTORY_SEPARATOR . 'xjryanse' . DIRECTORY_SEPARATOR 
                . $module .DIRECTORY_SEPARATOR .'src'.DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR;  //控制器路径
        return CodeFile::getPathClasses($modulePath);
    }
        
    public static function modelsXieArr($con = []){
        // if(!property_exists($class, $property))
        $cacheKey = __METHOD__;
        $listsAll = Cachex::funcGet($cacheKey, function(){
            $tableModules   = DbOperate::allModules();
            $codeModules    = self::modules();
            $modules        = array_unique(array_merge($tableModules, $codeModules));
            $arr = [];
            foreach($modules as &$module){
                $modelsApps = self::modelsXie($module);
                foreach($modelsApps as &$modelsApp){
                    $arr[] = self::classDetail('xjryanse',$module, 'model', $modelsApp);
                }
            }
            return $arr;
        });
        return Arrays2d::listFilter($listsAll, $con);
    }

    public static function serviceXie($module){
        if(empty($module)){
            return [];
        }
        $rootPath   = Container::get('app')->getRootPath();
        $modulePath = $rootPath . 'vendor' . DIRECTORY_SEPARATOR . 'xjryanse' . DIRECTORY_SEPARATOR 
                . $module .DIRECTORY_SEPARATOR .'src'.DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR;  //控制器路径
        return CodeFile::getPathClasses($modulePath);
    }
        
    public static function serviceXieArr($con = []){
        // if(!property_exists($class, $property))
        $cacheKey = __METHOD__;
        $listsAll = Cachex::funcGet($cacheKey, function(){
            $tableModules   = DbOperate::allModules();
            $codeModules    = self::modules();
            $modules        = array_unique(array_merge($tableModules, $codeModules));
            $arr = [];
            foreach($modules as &$module){
                $serviceApps = self::serviceXie($module);
                foreach($serviceApps as &$serviceApp){
                    $arr[] = self::classDetail('xjryanse',$module, 'service',$serviceApp);
                }
            }
            return $arr;
        });
        return Arrays2d::listFilter($listsAll, $con);
    }
    
    public static function serviceApp($module){
        if(empty($module)){
            return [];
        }
        $rootPath   = Container::get('app')->getRootPath();
        $modulePath = $rootPath . DIRECTORY_SEPARATOR . $module .DIRECTORY_SEPARATOR. 'service'.DIRECTORY_SEPARATOR;  //控制器路径
        return CodeFile::getPathClasses($modulePath);
    }
        
    public static function serviceAppArr($con = []){
        // if(!property_exists($class, $property))
        $cacheKey = __METHOD__;
        $listsAll = Cachex::funcGet($cacheKey, function(){
            $tableModules   = DbOperate::allModules();
            $codeModules    = self::modules();
            $modules        = array_unique(array_merge($tableModules, $codeModules));
            $arr        = [];
            foreach($modules as &$module){
                $serviceApps = self::serviceApp($module);
                foreach($serviceApps as &$serviceApp){
                    $arr[] = self::classDetail('app',$module, 'service', $serviceApp);
                }
            }
            return $arr;
        });
        return Arrays2d::listFilter($listsAll, $con);
    }
    /**
     * 类详情
     * @param type $source      文件来源：app,xjryanse
     * @param type $module      所属模块：order
     * @param type $folderName  文件名称： module,service ……
     * @param type $fileName    orderBaoService
     */
    protected static function classDetail($source, $module, $folderName, $fileName){
        $data['source']     = $source;
        $data['module']     = $module;
        $data['folder']     = $folderName;
        $data['file']       = $fileName;

        return $data;
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

//        $content = file_get_contents($appPath .DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR.'controller'.DIRECTORY_SEPARATOR.$controller.'.php');
//        
//        preg_match_all("/.*?public.*?function(.*?)\(.*?\)/i", $content, $matches);
//        $functions = $matches[1];
        $path = $appPath .DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR.'controller'.DIRECTORY_SEPARATOR.$controller.'.php';
        $functions = CodeFile::fileFunctions($path);
        
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
     * 20240418:复用trait方法
     * @param type $con
     */
    public static function traitsArr($con = []){
        $cacheKey = __METHOD__;
        $listsAll = Cachex::funcGet($cacheKey, function(){
            $tables = DbOperate::allTableNames();
            $arr    = [];
            foreach($tables as $tableName){
                $service = DbOperate::getService($tableName);
                if(!class_exists($service)){
                    continue;
                }
                $func = new \ReflectionClass($service);
                // $filePath = $func->getFileName();
                $filePath = Loader::findFile(ltrim($service,'\\'));
                // 类反射提取方法
                $traits     = $func->getTraits();
                // 项目根目录：/www/wwwroot/gsjk
                // $projectLocalPath = 'D:\phpstudy_pro\WWW\tenancy';
                foreach($traits as $k=>$trait){
                    $tmp['table']       = $tableName;
                    $tmp['traitName']   = $k;
                    $tmp['methodCount']= count($trait->getMethods());

                    $arr[]              = $tmp;
                }
            }
            return $arr;
        });
        return $con ? Arrays2d::listFilter($listsAll, $con) : $listsAll;
    }
    
    /**
     * 20240418:复用trait方法
     * @param type $con
     */
    public static function traitsMethodArr($tableName, $con = []){
        $cacheKey = __METHOD__. $tableName;
        $listsAll = Cachex::funcGet($cacheKey, function() use ($tableName){
            $arr    = [];
            $service = DbOperate::getService($tableName);
            if(!class_exists($service)){
                return [];
            }
            $func = new \ReflectionClass($service);
            // 类反射提取方法
            $traits     = $func->getTraits();
            // 项目根目录：/www/wwwroot/gsjk
            $projectBasePath = dirname($_SERVER['DOCUMENT_ROOT']);            
            // 复用trait
            foreach($traits as $k=>$trait){
                $filePath = Loader::findFile(ltrim($k,'\\'));
                // trait内方法
                $methods = $trait->getMethods();
                foreach($methods as $mt){
                    $tmp                = [];
                    $tmp                = self::traitDetail($k, $mt->name, 'list');
                    $tmp['table']       = $tableName;
                    $tmp['traitName']   = $k;

                    $arr[]              = $tmp;
                }
            }
            return $arr;
        });
        return $con ? Arrays2d::listFilter($listsAll, $con) : $listsAll;
    }
    
    /**
     * 20230613:服务类库方法
     * @param type $con
     */
    public static function servicesArr($con = []){
        $cacheKey = __METHOD__;
        $listsAll = Cachex::funcGet($cacheKey, function(){
            $tables = DbOperate::allTableNames();
            $arr    = [];
            foreach($tables as $tableName){
                $service = DbOperate::getService($tableName);
                if(!class_exists($service)){
                    continue;
                }
                $func = new \ReflectionClass($service);
                // $filePath = $func->getFileName();
                $filePath = Loader::findFile(ltrim($service,'\\'));
                // 文件正则匹配方法名                
                $fileFuncs = CodeFile::fileFunctions($filePath);
                // 类反射提取方法
                $reflMethods = $func->getMethods();
                foreach($reflMethods as $method){
                    if(!in_array($method->name, $fileFuncs)){
                        continue;
                    }
                    // $tmp['document']    = $method->getDocComment();
                    $tmp                = self::serviceDetail($service, $method->name, 'list');
                    $arr[]              = $tmp;
                }
            }
            return $arr;
        });
        return $con ? Arrays2d::listFilter($listsAll, $con) : $listsAll;
    }
    /**
     * 20230616：详情
     */
    public static function serviceDetail($service, $method, $type = 'detail'){
        $data['table']       = $service::getTable();
        $data['service']     = $service;
        $data['method']      = $method;
        // 20230617:方法类型：field:字段映射；eTrigger:传统增删改触发器；rTrigger:
        $data['methodType']  = self::calServiceMethodType($method);
        // 主数据
        $dataMain = CodeClass::classMethodDetail($service, $method, $type);
        $data = array_merge($data, $dataMain);
        
        // 20230711:方法调用次数
        $data['callCount']   = SystemServiceMethodLogService::serviceMethodCount($service, $method);
        
        return $data;
    }
    /**
     * 20230616：详情
     */
    public static function traitDetail($classStr, $method, $type = 'detail'){
        $data['method']      = $method;
        $data['traitName']   = $classStr;
        // 主数据
        $dataMain = CodeClass::classMethodDetail($classStr, $method, $type);
        $data = array_merge($data, $dataMain);

        return $data;
    }

    /**
     * 20230617:计算服务类的方法类型
     * 方法类型：field:字段映射；eTrigger:传统增删改触发器；rTrigger:优化后的增删改触发器
     */
    protected static function calServiceMethodType($method){
        if(preg_match('/^f[A-Z]/', $method)){
            return 'field';
        }
        if(preg_match('/^extra[(Pre)|(After)]/', $method)){
            return 'eTrigger';
        }
        if(preg_match('/^ram[(Pre)|(After)]/', $method)){
            return 'rTrigger';
        }
        // 列表分页方法
        if(preg_match('/^paginateFor[A-Z]/', $method)){
            return 'paginate';
        }
        return 'other';
    }
    
    /**********控制器方法****************/
    
    /**
     * 20230620：模块详情
     */
    public static function moduleDetail($module, $type = 'detail'){
        $data['module']          = $module;
        // 20230620:备用字段
        $tablesArr               = DbOperate::allTableArr();
        $con                     = [['module', '=', $module]];
        // 表数
        $data['tableCount']      = count(Arrays2d::listFilter($tablesArr, $con));
        // 控制器数
        $data['controllerCount'] = count(self::controllerArr($con));
        // 模型文件数
        $data['modelAppCount']   = count(self::modelsAppArr($con));
        $data['modelXieCount']   = count(self::modelsXieArr($con));
        // 服务类文件数
        $data['serviceAppCount']   = count(self::serviceAppArr($con));
        $data['serviceXieCount']   = count(self::serviceXieArr($con));

        // 用于筛选
        $data['hasTable']        = $data['tableCount'] ? 1 : 0;
        $data['hasController']   = $data['controllerCount'] ? 1 : 0;
        $data['hasModelApp']     = $data['modelAppCount'] ? 1 : 0;
        $data['hasModelXie']     = $data['modelXieCount'] ? 1 : 0;
        $data['hasServiceApp']   = $data['serviceAppCount'] ? 1 : 0;
        $data['hasServiceXie']   = $data['serviceXieCount'] ? 1 : 0;

        return $data;
    }
    
    /**
     * 20230616：控制器方法详情
     */
    public static function controllerDetail($module, $controller, $type = 'detail'){
        $data['module']         = $module;
        $data['controller']     = $controller;
        
        $class      = '\\app\\'.$module.'\\controller\\'.$controller;
        // 20240418:提取主数据
        $dataMain   = CodeClass::classDetail($class, $type);
        $data       = array_merge($data, $dataMain);
        
        
        // 20230620:备用字段
        // 方法数量
        $con = [];
        $con[]                  = ['module', '=', $module];
        $con[]                  = ['controller', '=', $controller];
        $data['actionCount']    = count(self::actionsArr($con));

        return $data;
    }
    
    /**
     * 20230616：控制器方法详情
     */
    public static function actionDetail($module, $controller, $action, $type = 'detail'){
        $data['module']         = $module;
        $data['controller']     = $controller;
        $data['action']         = $action;
        
        $class      = '\\app\\'.$module.'\\controller\\'.$controller;
        // 20240418:提取主数据
        $dataMain   = CodeClass::classMethodDetail($class, $action, $type);
        $data = array_merge($data, $dataMain);
        // 20230711:方法调用次数
        $data['callCount']   = SystemLogService::controllerMethodCount($module, $controller, $action);
        
        return $data;
    }

    /******* 供前台页面管理的分页数据 *****************/
    
    /**
     * 20230620:控制器数组
     * @return type
     */
    public static function controllerArr($con = []){
        // if(!property_exists($class, $property))
        $cacheKey = __METHOD__;
        $listsAll = Cachex::funcGet($cacheKey, function(){
            $modules = self::modules();
            $arr = [];
            foreach($modules as &$module){
                $controllers = self::controllers($module);
                foreach($controllers as &$controller){
                    $arr[] = self::controllerDetail($module, $controller, 'list');
                }
            }
            return $arr;
        });
        return Arrays2d::listFilter($listsAll, $con);
    }
    
    /**
     * 20230528:方法数组（三合一）
     * @return type
     */
    public static function actionsArr($con = []){
        // if(!property_exists($class, $property))
        $cacheKey = __METHOD__;
        $listsAll = Cachex::funcGet($cacheKey, function(){
            $modules = self::modules();
            $arr = [];
            foreach($modules as &$module){
                $controllers = self::controllers($module);
                foreach($controllers as &$controller){
                    $actions = self::actions($module, $controller);
                    foreach($actions as &$action){
                        // 20230620
                        $arr[] = self::actionDetail($module, $controller, $action, 'list');
                    }
                }
            }
            return $arr;
        });
        return Arrays2d::listFilter($listsAll, $con);
    }

    /**
     * 模块清单
     * @param type $con
     */
    public static function modulesArr($con){
        $cacheKey = __METHOD__;
        $listsAll = Cachex::funcGet($cacheKey, function(){
            // 拼接模块
            $tableModules   = DbOperate::allModules();
            $codeModules    = self::modules();
            $modules        = array_unique(array_merge($tableModules, $codeModules));
            $arr = [];
            foreach ($modules as &$module) {
                $arr[] = self::moduleDetail($module, 'list');
            }
            return $arr;
        });
        return Arrays2d::listFilter($listsAll, $con);
    }

}
