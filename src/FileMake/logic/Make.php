<?php
namespace xjryanse\logic\FileMake\logic;

/**
 * 创建文件
 */
abstract class Make
{
    abstract protected function getStub();
    
    abstract protected function getPathName($module,$modelName);
    /**
     * 生成文件
     * @param type $module      模块
     * @param type $modelName   模型名称
     * @param type $modelDesc   模型描述
     * @return type
     */
    public function generate( $module, $modelName,$modelDesc)
    {
        $stub       = $this->getStub();
        $content    = file_get_contents($stub);
        //文件内容
        $afterReplaceContent = str_replace(['{%module%}', '{%modelName%}', '{%modelDesc%}'], [
            $module,
            $modelName,
            $modelDesc,
        ], $content);
        //文件路径
        $filePath = $this->getPathName( $module, $modelName );
        if(file_exists($filePath)){
//            throw new Exception( $filePath. '文件已存在，不能创建!');
            return false;
        }
        
        //不存在时，创建文件目录
        if (!is_dir(dirname($filePath))) {
            mkdir(dirname( $filePath ), 0755, true);
        }
        //生成后的文件内容写入
        $res = file_put_contents($filePath, $afterReplaceContent);
        return $res;
    }
}