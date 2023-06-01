<?php

namespace xjryanse\logic;

/**
 * 文件处理
 */
class File {
    /**
     * 删除文件的方法，加上了必要的判断，比直接调用系统unlink函数更加安全
     */
    public static function unlink($pathFull){
        if(is_file($pathFull) && file_exists( $pathFull )){
            unlink( $pathFull );
        }
    }
    /**
     * 获取扩展
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
     * 获取名称
     * @param type $url
     * @return type
     */
    public static function getName( $url) {
        $data       = explode('?', $url);
        $basename   = basename($data[0]);
        $basenames  = explode('.', $basename);
        return isset($basenames[0]) ? $basenames[0] : '' ;
    }
    /**
     * 
     * @param type $filePath
     * @param type $newName
     */
    public static function rename($filePath,$newName){
        return rename($filePath, $newName);
    }    
    /**
     * 判断文件是否存在
     * @param type $filePath
     * @return type
     */
    public static function isExist($filePath){
        return file_exists($filePath);
    }
    /**
     * 20221111下载远程文件存入本地
     */
    public static function saveUrlFile($url,$savePath){
        if(file_exists($savePath)){
            return false;
        }
        try{
            $file       = file_get_contents( $url );
            $dirPath    = dirname($savePath);
            if (!file_exists($dirPath) && !mkdir($dirPath, 0777, true)) {
                throw new Exception('创建目录'. $dirPath .'失败');
            }
            //写入本地服务器
            return file_put_contents( $savePath, $file );
        } catch (\Exception $e){
            return false;
        }
    }
}
