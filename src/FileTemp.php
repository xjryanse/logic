<?php

namespace xjryanse\logic;

use xjryanse\logic\Strings;
use xjryanse\logic\File;
/**
 * 临时文件
 */
class FileTemp {
    /**
     * 临时文件清理
     * 创建时间超过5分钟的清理
     */
    public static function unlink(){
        $dir = './Uploads/Download/CanDelete/';
        $files = scandir($dir);
        
        foreach($files as $file){
            if(Strings::isStartWith($file, '.')){
                continue;
            }
            $pathFull = $dir.$file;
            // 超过5分钟的则删除
            if(time() - filectime($pathFull) > 300){
                File::unlink($pathFull);
            }
        }
    }
    

}
