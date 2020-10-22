<?php
namespace xjryanse\logic\FileMake\logic\make;

use xjryanse\logic\FileMake\logic\Make;
use think\facade\Env;

class Model extends Make
{
    protected function getStub()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'stubs' . DIRECTORY_SEPARATOR . 'model.stub';
    }
    
    protected function getPathName($module,$modelName)
    {
        return Env::get('app_path') . $module. DIRECTORY_SEPARATOR. 'model' . DIRECTORY_SEPARATOR. $modelName. '.php';
    }
    
}