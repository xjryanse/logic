<?php

namespace xjryanse\logic;

/**
 * 文件代码(通用)
 */
class CodeFile {
    /**
     * 20230613:提取类库文件中的方法
     * @param type $path
     */
    public static function fileFunctions($path){
        $content = file_get_contents($path);
        // 20240430:先删除注释，才不会报错
        $contentForFunc = self::removeComment($content);
        preg_match_all("/.*?(public|protected|private).*?function (.*?)\(.*?\)/i", $contentForFunc, $matches);
        $functions = $matches[2];
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
            preg_match_all('/[\x20]\/\*[^\/]*\*\/*[\s]*[public|protected|private].*?function '.$function.'\(.*?({(?>[^{}]+|(?1))*})/', $content, $matches);
        } else {
            /* $pattern = '/(public)*(\{(?>[^{}]+|(?R))*\})/s'; */ 
            $regStr = '/[\x20]*[public|protected|private].*?function '.$function.'\(.*?({(?>[^{}]+|(?1))*})/';

            preg_match_all($regStr, $content, $matches);
            // dump($matches);
        }
        $functions = $matches[0];
        return $functions ? $functions[0] : '';
    }
    
    
    /*
     * 20230615:文件方法，注释
     */
    public static function fileFuncComment($path, $function ){
        $content = file_get_contents($path);
        /* $pattern = '/(public)*(\{(?>[^{}]+|(?R))*\})/s'; */ 
        $pattern = '/(\/\*([^\/]*)\*\/)(?=([\s]*(public|protected|private).*?function '.$function .'\((.*?)))/';
        preg_match_all($pattern, $content, $matches);
        $comments = $matches[0];
        return $comments ? $comments[0] : '';
    }
    
    /*
     * 20230615:文件方法，注释
     */
    public static function fileClassComment($path ){
        $content = file_get_contents($path);
        /* $pattern = '/(public)*(\{(?>[^{}]+|(?R))*\})/s'; */ 
        // $pattern = '/(\/\*([^\/]*)\*\/)(?=([\s]*(public|protected|private).*?function '.$function .'\((.*?)))/';
        $pattern = '/(\/\*([^\/]*)\*\/)(?=([\s]*(abstract)*.*?class))/';
        preg_match_all($pattern, $content, $matches);
        // Debug::dump($matches);
        $comments = $matches[0];
        return $comments ? $comments[0] : '';
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
        preg_match('/\/\*\*[\n\r][\s]*\*[\s]*(.*?)[\n\r]/', $text, $matches);
        $res = [
            'title'         => preg_match('/\/\*\*[\n\r][\s]*\*[\s]*(.*?)[\n\r]/', $text, $matches) ? $matches[1] : '' ?: $default,
            // 20230616：方法描述
            'describe'      => preg_match('/@describe\s*(.*)\s*/i', $text, $matches) ? $matches[1] : '',
            // 20230616：外部文档地址
            'refUrl'        => preg_match('/@refUrl\s*(.*)\s*/i', $text, $matches) ? $matches[1] : '',
            'creater'       => preg_match('/@creater\s*(.*)\s*/i', $text, $matches) ? $matches[1] : '',
            'create_time'   => preg_match('/@createTime\s*(.*)\s*/i', $text, $matches) ? $matches[1] : '',
            'updater'       => preg_match('/@updater\s*(.*)\s*/i', $text, $matches) ? $matches[1] : '',
            'update_time'   => preg_match('/@updateTime\s*(.*)\s*/i', $text, $matches) ? $matches[1] : '',
            // 20240420:方法是否有用，0-1，用于淘汰无用方法
            'useFul'        => preg_match('/@useFul\s*(.*)\s*/i', $text, $matches) ? 1 : 0,
        ];
        // Debug::dump($text);
        // Debug::dump($res);
        return $res;
    }
    
    /**
     * 20230803：获取指定路径下php类名
     */
    public static function getPathClasses($folderPath) {
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
     * 20240430：删除注释
     * @param type $php
     * @return type
     */
    private static function removeComment($php) {
        $search = [
            '@/\*.*?\*/@s', // 去除多行注释
            '@\s+//.*$@m', // 去除单行注释
        ];
        $replace = [
            '',
            '',
        ];
        return preg_replace($search, $replace, $php);
    }
}
