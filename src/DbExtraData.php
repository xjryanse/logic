<?php
namespace xjryanse\logic;

/**
 * 拼接额外的数据
 */
class DbExtraData
{    
    /**
     * 额外数据添加
     * 1对1关联
     * 数据结构：表名.字段名
     */
    public static function oneToOne( &$item, $tableName, $tableId )
    {
        $service        = DbOperate::getService( $tableName );
        $tableInfo      = $service::getInstance( $tableId )->get() ;
        $tableInfoArr   = $tableInfo ? $tableInfo->toArray() : [] ;
        //表名 加 点
        foreach( $tableInfoArr as $key=>$value ){
            $item[ $tableName.'.'.$key ] = $value;
        }
        $item[$tableName] = $tableInfoArr;
        return $item;
    }
    /**
     * 额外数据添加
     * 1对多，按键关联:如用户账户user_account
     * 数据结构：表名.键名.字段名
     * @param type $item
     * @param type $tableName   user_account
     * @param type $uniField    user_id
     * @param type $uniValue    
     * @param type $keyField    account_type
     * @param type $valueField  current
     * @return type
     */
    public static function oneToMoreByKey( &$item, $tableName, $uniField,$uniValue, $keyField, $valueField )
    {
        $service    = DbOperate::getService( $tableName );

        $con[]      = [$uniField , '=', $uniValue];
        $tableInfo      = $service::lists( $con ) ;
        $tableInfoArr   = $tableInfo ? $tableInfo->toArray() : [] ;
        //表名 加 点
        foreach( $tableInfoArr as $key=>$value ){
            $item[ $tableName .'.'. $value[ $keyField ] .'.'. $valueField] = $value[$valueField];
        }

        return $item;
    }    
}
