<?php

namespace xjryanse\logic\FileMake\logic\make;

use xjryanse\logic\FileMake\logic\Make;
use think\facade\Env;

class ServiceBase extends Make
{
    protected function getStub()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'stubs' . DIRECTORY_SEPARATOR . 'service_base.stub';
    }
    
    protected function getPathName($module,$modelName)
    {
        return Env::get('app_path') . $module. DIRECTORY_SEPARATOR. 'service' . DIRECTORY_SEPARATOR. 'Base.php';
    }
    
}