<?php

namespace xjryanse\logic;

use think\Loader;
use xjryanse\logic\Strings;
use think\facade\Request;
use Exception;
/**
 * 类代码(通用)
 */
class CodeClass {
    /**
     * 20230615:服务类提取源文件
     * @param type $class
     * @return type
     */
    public static function classGetFilePath($class){
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
     * 20230615:服务类方法，提取代码
     */
    public static function classFuncCode($classStr, $method, $withComment = false){
        $filePath   = self::classGetFilePath($classStr);
        $code       = CodeFile::fileFuncCode($filePath, $method, $withComment);
        return $code;
    }
    /**
     * 20230615：服务类方法，提取注释
     * @param type $classStr
     * @param type $method
     */
    public static function classFuncComment($classStr, $method){
        $filePath   = self::classGetFilePath($classStr);
        $comment    = CodeFile::fileFuncComment($filePath, $method);
        return $comment;
    }
    /**
     * 20240418：类提取详情
     */
    public static function classMethodDetail($classStr, $method, $type = 'detail'){
        // 不带注释方法
        $code       = self::classFuncCode($classStr, $method, false);
        // 带注释方法
        $codeFull   = self::classFuncCode($classStr, $method, true);
        // 注释
        $comment    = self::classFuncComment($classStr, $method);
        if($type == 'detail'){
            $data['code']       = $code;
            $data['codeFull']   = $codeFull;
            $data['comment']    = $comment;
        }
        // 方法行数（不带注释）
        $data['lines']                  = Strings::lineCount($code);
        // 20230616:带注释行数
        $data['linesWithComment']       = Strings::lineCount($codeFull);
        if(!method_exists($classStr, $method)){
            throw new Exception('类方法不存在'.$classStr.'的'.$method);
        }
        $classObj   = new \ReflectionClass($classStr);
        $methodObj  = $classObj->getMethod($method);

        $projectBasePath = dirname($_SERVER['DOCUMENT_ROOT']);
        
        $data['isPublic']    = $methodObj->isPublic() ? 1 :0;
        $data['isProtected'] = $methodObj->isProtected() ? 1 :0;
        $data['isPrivate']   = $methodObj->isPrivate() ? 1 :0;
        $data['isStatic']    = $methodObj->isStatic() ? 1 :0;

        $data['startLine']   = $methodObj->getStartLine();                    
        // 对应的本地目录
        $filePath = Loader::findFile(ltrim($classStr,'\\'));
        $data['localPath']   = str_replace($projectBasePath, '', $filePath);
        $data['host']        = Request::host();

        // 注释
        $commentArr         = CodeFile::parseComment($comment , '');
        $data = array_merge($data, $commentArr);
        $data['hasRefUrl']  = $data['refUrl'] ? 1: 0;
        return $data;
    }
    
    /**
     * 20230615：服务类，提取注释
     * @param type $classStr
     * @param type $method
     */
    public static function classComment($classStr){
        $filePath   = self::classGetFilePath($classStr);
        $comment    = CodeFile::fileClassComment($filePath);
        return $comment;
    }
    
    /**
     * 20240418：类提取详情
     */
    public static function classDetail($classStr, $type = 'detail'){
        // $data['test'] = $classStr;
        // 不带注释方法
        // $code       = self::classFuncCode($classStr, $method, false);
        // 带注释方法
        // $codeFull   = self::classFuncCode($classStr, $method, true);
        // 注释
        $comment    = self::classComment($classStr);
        if($type == 'detail'){
            // $data['code']       = $code;
            // $data['codeFull']   = $codeFull;
            $data['comment']    = $comment;
        }
        // 方法行数（不带注释）
        // $data['lines']                  = Strings::lineCount($code);
        // 20230616:带注释行数
        // $data['linesWithComment']       = Strings::lineCount($codeFull);
        // if(!method_exists($classStr, $method)){
            // throw new Exception('类方法不存在'.$classStr.'的'.$method);
        // }
        // $classObj   = new \ReflectionClass($classStr);
        // $methodObj  = $classObj->getMethod($method);

        $projectBasePath = dirname($_SERVER['DOCUMENT_ROOT']);
        
        // $data['isPublic']    = $methodObj->isPublic() ? 1 :0;
        // $data['isProtected'] = $methodObj->isProtected() ? 1 :0;
        // $data['isPrivate']   = $methodObj->isPrivate() ? 1 :0;
        // $data['isStatic']    = $methodObj->isStatic() ? 1 :0;

        // $data['startLine']   = $methodObj->getStartLine();                    
        // 对应的本地目录
        $filePath = Loader::findFile(ltrim($classStr,'\\'));
        $data['localPath']   = str_replace($projectBasePath, '', $filePath);
        $data['host']        = Request::host();

        // 注释
        $commentArr         = CodeFile::parseComment($comment , '');
        $data = array_merge($data, $commentArr);
        // $data['hasRefUrl']  = $data['refUrl'] ? 1: 0;
        return $data;
    }
}
