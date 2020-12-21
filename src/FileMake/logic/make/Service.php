<?php

namespace xjryanse\logic\FileMake\logic\make;

use xjryanse\logic\FileMake\logic\Make;
use xjryanse\logic\DbOperate;
use think\facade\Env;

class Service extends Make
{
    protected function getStub()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'stubs' . DIRECTORY_SEPARATOR . 'service.stub';
    }
    
    protected function getPathName($module,$modelName)
    {
        return Env::get('app_path') . $module. DIRECTORY_SEPARATOR. 'service' . DIRECTORY_SEPARATOR. $modelName .'Service.php';
    }
    /**
     * 表名取方法
     * @param type $tableName
     */
    public static function getMethods( $tableName )
    {
        $columns = DbOperate::columns($tableName);
        $str = "";
        foreach( $columns as $fieldInfo){
            $str.="/**".PHP_EOL
                . "\t *".$fieldInfo['COLUMN_COMMENT'].PHP_EOL
                . "\t */".PHP_EOL
                . "\tpublic function f". ucfirst( camelize( $fieldInfo['COLUMN_NAME'] ))."()".PHP_EOL;
            $str.="\t{".PHP_EOL
                    ."\t\t" .'return $this->getFFieldValue(__FUNCTION__);'
                    ."\t".PHP_EOL."\t}".PHP_EOL."\t";
        }
        return $str;
    }
}