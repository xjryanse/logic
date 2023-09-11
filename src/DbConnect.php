<?php
namespace xjryanse\logic;

use xjryanse\logic\Sql;
use think\Db;
/**
 * 20230828:直接操作DB连接的类库
 */
class DbConnect
{
    //连接参数的字符串
    protected $connStr = '';
    
    public function __construct($connStr) {
        $this->connStr = $connStr;
    }
    /**
     * 拆解连接参数
     */
    public function parse(){
        return  parse_url($this->connStr);
    }
    /*
     * 获取数据库
     */
    public function getDataBase(){
        $ps = $this->parse();
        $db   = ltrim($ps['path'], '/');
        return $db;
    }
    
    /**
     * 库站
     * @return type
     */
    public function getHost(){
        $ps = $this->parse();
        return $ps['host'];
    }
    
    /**
     * 连接
     */
    public function getConnect(){
        return Db::connect($this->connStr);
    }
    

    /**
     * 真实字段
     * @param type $table
     * @return type
     */
    public function realFields( $table ){
        $sql        = Sql::getColumn($table);
        $columns    = $this->getConnect()->query($sql);

        $fieldArr   = [];
        foreach($columns as $value){
            if($value['Extra'] != 'VIRTUAL GENERATED'){
                $fieldArr[] = $value['Field'];
            }
        }
        return $fieldArr;
    }
    
    public function tables(){
        $sql = Sql::getTable($this->getDataBase());
        $res =  $this->getConnect()->query($sql);
        return array_column($res,'table');
    }
}
