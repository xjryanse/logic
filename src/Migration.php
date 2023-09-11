<?php
namespace xjryanse\logic;

use xjryanse\logic\Sql;
use xjryanse\logic\Arrays;
use xjryanse\logic\DbConnect;
use think\Db;
/**
 * 数据库迁移类库
 */
class Migration
{
    // 迁移源库配置
    protected $mainDb;
    // 迁移目标库配置
    protected $accDb;
    
    // 主连接实例：
    protected $mainConn;
    // 从连接实例：
    protected $accConn;
    
    
    public function __construct($mainDb,$accDb) {
        // 淘汰
        $this->mainDb = $mainDb;
        $this->accDb = $accDb;
        
        $this->mainConn = new DbConnect($mainDb);
        $this->accConn = new DbConnect($accDb);
    }

    /**
     * 主连接实例
     * @return type
     */
    public function getMainConn(){
        return $this->mainConn;
    }
    /**
     * 从连接实例
     * @return type
     */
    public function getAccConn(){
        return $this->accConn;
    }
    
    /**
     * 主入口，查询主从库全部数据表
     * @param type $con
     * @return type
     */
    public function tablesArr($con = []){
        $mainTables     = $this->mainConn->tables();
        $accDbTables    = $this->accConn->tables();
        $allTables      = array_unique(array_merge($mainTables, $accDbTables));
        
        $arr = [];
        foreach($allTables as $t){
            $tmp            = [];
            $tmp['table']   = $t;

            $tmp['mainHost']= Strings::keepLength($this->mainConn->getHost(),10);
            $tmp['accHost'] = Strings::keepLength($this->accConn->getHost(),10);

            $tmp['inMain']  = in_array($t,$mainTables) ? 1 : 0;
            $tmp['inAcc']   = in_array($t,$accDbTables) ? 1 : 0;
            // 主库记录数
            $tmp['mainCounts']  = '';
            // 从库记录数
            $tmp['accCounts']   = '';
            
            $arr[] = $tmp;
        }
        
        return Arrays2d::listFilter($arr, $con);
    }

    /**
     * 查询指定表，各端口记录数
     */
    public function recordsCountArr($table){
        $mains  = $this->mainConn->getConnect()->table($table)->group('company_id')->column('count(1)','company_id');

        $accs   = $this->accConn->getConnect()->table($table)->group('company_id')->column('count(1)','company_id');
        
        $companyIds = array_unique(array_merge(array_keys($mains),array_keys($accs)));

        $dataArr = [];
        foreach($companyIds as $companyId){
            $tmp                = [];
            $tmp['table']       = $table;
            $tmp['company_id']  = $companyId;
            $tmp['mainHost']    = Strings::keepLength($this->mainConn->getHost(),10);
            $tmp['accHost']     = Strings::keepLength($this->accConn->getHost(),10);

            $tmp['mainCount']   = Arrays::value($mains, $companyId);
            $tmp['accCount']    = Arrays::value($accs, $companyId);
            // 迁移完成率
            $tmp['migRate']       = $tmp['mainCount'] && $tmp['accCount']
                    ? round($tmp['accCount']/$tmp['mainCount'] * 100, 2).'%' 
                    : '';

            $dataArr[] = $tmp;
        }
        
        return $dataArr;
    }

    /**
     * 20230828：单一表数据迁移
     * @param type $table       迁移哪张表
     * @param type $companyId   迁移哪个端口
     * @param type $limit       迁移几条数据
     */
    public function moveTable($table, $companyId = '', $limit = 1000){
        // 实有字段
        $realFieldsArr = $this->mainConn->realFields($table);
        $hasCompanyId = in_array('company_id', $realFieldsArr);
        $con = [];
        if($hasCompanyId && $companyId){
            $con[] = ['company_id','=',$companyId];
        }

        // 获取从表末条记录id
        $lastId = $this->accConn->getConnect()->table($table)->where($con)->order('id desc')->value('id');
        // 获取主表下5条记录
        if($lastId){
            $con[] = ['id','>',$lastId];
        }

        $arr  = $this->mainConn->getConnect()->table($table)->where($con)->field(implode(',',$realFieldsArr))->limit($limit)->order('id')->select();
        return $this->accConn->getConnect()->table($table)->insertAll($arr);
    }

}
