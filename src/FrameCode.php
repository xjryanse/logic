<?php

namespace xjryanse\logic;

use think\Container;
use think\Loader;
use xjryanse\logic\Strings;
use xjryanse\system\service\SystemServiceMethodLogService;
use xjryanse\system\service\SystemLogService;

/**
 * 框架代码
 */
class FrameCode {
    /**
     * 20230613:提取类库文件中的方法
     * @param type $path
     */
    protected static function fileFunctions($path){
        $content = file_get_contents($path);
        preg_match_all("/.*?public.*?function (.*?)\(.*?\)/i", $content, $matches);
        $functions = $matches[1];
        return $functions;
    }
    /**
     * 20230613:正则提取方法代码
     * @param type $path
     * @param type $function
     * @param type $withComment     带注释?
     * @return type
     */
    public static function fileFuncCode($path, $function ,$withComment = false){
        $content = file_get_contents($path);
        // 第一步：写一个不能支持嵌套的表达式只能匹配最内层  {[^{}]*}
        
        // preg_match_all("/.*?public.*?function (.*?)\(.*?\)/i", $content, $matches);
        // preg_match_all('/{((?:[^{}]++|(?R))*+)}/', $str, $matches);
        if($withComment){
            // preg_match_all('/{(([^{}]*|(?R))*)}/', $content, $matches);
//            dump($matches);
            // 神贴链接：https://blog.csdn.net/technofiend/article/details/49906755
            // TODO:没有注释的还是出不来？？
            preg_match_all('/[\x20]\/\*[^\/]*\*\/*[\s]*public.*?function '.$function.'\(.*?({(?>[^{}]+|(?1))*})/', $content, $matches);
        } else {
            $regStr = '/[\x20]*public.*?function '.$function.'\(.*?({(?>[^{}]+|(?1))*})/';
            preg_match_all($regStr, $content, $matches);
        }
        $functions = $matches[0];
        return $functions ? $functions[0] : '';
    }
    /*
     * 20230615:文件方法，注释
     */
    public static function fileFuncComment($path, $function ){
        $content = file_get_contents($path);
        preg_match_all('/(\/\*([^\/]*)\*\/)(?=([\s]*public.*?function '.$function .'))/', $content, $matches);
        $comments = $matches[0];
        return $comments ? $comments[0] : '';
    }
    /**
     * 20230615:服务类方法，提取代码
     */
    public static function serviceFuncCode($service, $method, $withComment = false){
        $filePath   = self::classGetFilePath($service);
        $code       = self::fileFuncCode($filePath, $method, $withComment);
        return $code;
    }
    /**
     * 20230615：服务类方法，提取注释
     * @param type $service
     * @param type $method
     */
    public static function serviceFuncComment($service, $method){
        $filePath   = self::classGetFilePath($service);
        $comment    = self::fileFuncComment($filePath, $method);
        return $comment;
    }
    
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
     * 20230803：获取指定路径下php类名
     */
    protected static function getPathClasses($folderPath) {
        if(!is_dir($folderPath)){
            return [];
        }
        $folderPath .= '*.php';
        $aryFiles = glob($folderPath);
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
        return self::getPathClasses($modulePath);
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
        return self::getPathClasses($modulePath);
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
        return self::getPathClasses($modulePath);
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
        return self::getPathClasses($modulePath);
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
        return self::getPathClasses($modulePath);
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
        $functions = self::fileFunctions($path);
        
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
                $fileFuncs = self::fileFunctions($filePath);
                // 类反射提取方法
                $reflMethods = $func->getMethods();
//                [0] => object(ReflectionMethod)#52 (2) {
//                    ["name"] => string(11) "getOutPhone"
//                    ["class"] => string(35) "app\ali\service\AliCallPhoneService"
//                  }
                foreach($reflMethods as $method){
                    if(!in_array($method->name, $fileFuncs)){
                        continue;
                    }
                    // $tmp['document']    = $method->getDocComment();
                    $arr[]              = self::serviceDetail($service, $method->name, 'list');
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
        // 不带注释方法
        $code       = self::serviceFuncCode($service, $method, false);
        // 带注释方法
        $codeFull   = self::serviceFuncCode($service, $method, true);
        // 注释
        $comment    = self::serviceFuncComment($service, $method);
        if($type == 'detail'){
            $data['code']       = self::serviceFuncCode($service, $method, false);
            $data['codeFull']   = self::serviceFuncCode($service, $method, true);
            $data['comment']    = self::serviceFuncComment($service, $method);
        }
        // 方法行数（不带注释）
        $data['lines']      = Strings::lineCount($code);
        // 20230616:带注释行数
        $data['linesWithComment']       = Strings::lineCount($codeFull);

        // 注释
        $commentArr         = FrameCode::parseComment($comment , '');
        // 接口标题
        $data['title']      = Arrays::value($commentArr, 'title');
        // 接口描述
        $data['describe']   = Arrays::value($commentArr, 'describe');
        // 第三方参考文档url:比如，微信文档，阿里文档
        $data['refUrl']     = Arrays::value($commentArr, 'refUrl');
        $data['hasRefUrl']  = $data['refUrl'] ? 1: 0;
        // TODO作者
        $data['creater']     = Arrays::value($commentArr, 'creater');
        $data['updater']     = Arrays::value($commentArr, 'updater');
        $data['create_time'] = Arrays::value($commentArr, 'create_time');
        $data['update_time'] = Arrays::value($commentArr, 'update_time');
        // 20230711:方法调用次数
        $data['callCount']   = SystemServiceMethodLogService::serviceMethodCount($service, $method);
        
        return $data;
    }
    /**
     * 20230615:服务类提取源文件
     * @param type $class
     * @return type
     */
    protected static function classGetFilePath($class){
        /*
        $func = new \ReflectionClass($class);
        $filePathRaw   = $func->getFileName();
         */
        // 20230711
        $filePathRaw = Loader::findFile(ltrim($class,'\\'));
        // 20230613：本系统挖坑
        $filePath   = str_replace('application', 'app', $filePathRaw);
        return $filePath;
    }
    /**
     * 20230616:解析注释
     * @param string $comment
     * @param string $default
     * @return array
     */
    public static function parseComment(string $comment, string $default = ''): array
    {
        // $text = strtr($comment, "\n", ' ');
        $text = $comment;
        // $title = preg_replace('/^\/\*\s*\*\s*\*\s*(.*?)\s*\*.*?$/', '$1', $text);
        // if (in_array(substr($title, 0, 5), ['@auth', '@menu', '@logi'])) $title = $default;
        $res = [
            'title'     => preg_match('/\/\*\*[\n\r][\x20]*\*[\x20]*(.*?)[\n\r]/', $text, $matches) ? $matches[1] : '' ?: $default,
            // 20230616：方法描述
            'describe'  => preg_match('/@describe\s*(.*)\s*/i', $text, $matches) ? $matches[1] : '',
            // 20230616：外部文档地址
            'refUrl'        => preg_match('/@refUrl\s*(.*)\s*/i', $text, $matches) ? $matches[1] : '',
            'creater'       => preg_match('/@creater\s*(.*)\s*/i', $text, $matches) ? $matches[1] : '',
            'create_time'   => preg_match('/@createTime\s*(.*)\s*/i', $text, $matches) ? $matches[1] : '',
            'updater'       => preg_match('/@updater\s*(.*)\s*/i', $text, $matches) ? $matches[1] : '',
            'update_time'   => preg_match('/@updateTime\s*(.*)\s*/i', $text, $matches) ? $matches[1] : '',
        ];
        return $res;
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
        // 不带注释方法
        $code                       = self::actionFuncCode($module, $controller,$action, false);
        // 带注释方法
        $codeFull                   = self::actionFuncCode($module, $controller,$action, true);
        // 注释
        $comment                    = self::actionFuncComment($module, $controller,$action);
        // 方法行数（不带注释）
        $data['lines']              = Strings::lineCount($code);
        // 20230616:带注释行数
        $data['linesWithComment']   = Strings::lineCount($codeFull);

        if($type == 'detail'){
            $data['code']       = $code;
            $data['codeFull']   = $codeFull;
            // $data['comment']    = self::serviceFuncComment($service, $method);
        }

        // 注释
        $commentArr         = FrameCode::parseComment($comment , '');
        // 接口标题
        $data['title']      = Arrays::value($commentArr, 'title');
        // 接口描述
        $data['describe']   = Arrays::value($commentArr, 'describe');
        // 第三方参考文档url:比如，微信文档，阿里文档
        $data['refUrl']     = Arrays::value($commentArr, 'refUrl');
        $data['hasRefUrl']  = $data['refUrl'] ? 1: 0;
        // TODO作者
        $data['creater']     = Arrays::value($commentArr, 'creater');
        $data['updater']     = Arrays::value($commentArr, 'updater');
        $data['create_time'] = Arrays::value($commentArr, 'create_time');
        $data['update_time'] = Arrays::value($commentArr, 'update_time');

        // 20230711:方法调用次数
        $data['callCount']   = SystemLogService::controllerMethodCount($module, $controller, $action);
        
        return $data;
    }
    /**
     * 20230620:控制器提取代码
     */
    public static function actionFuncCode($module, $controller, $action, $withComment = false){
        $class      = '\\app\\'.$module.'\\controller\\'.$controller;
        $filePath   = self::classGetFilePath($class);
        $code       = self::fileFuncCode($filePath, $action, $withComment);
        return $code;
    }
    
    /**
     * 20230615：服务类方法，提取注释
     * @param type $service
     * @param type $method
     */
    public static function actionFuncComment($module, $controller, $action){
        $class      = '\\app\\'.$module.'\\controller\\'.$controller;
        $filePath   = self::classGetFilePath($class);
        $comment    = self::fileFuncComment($filePath, $action);
        return $comment;
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
