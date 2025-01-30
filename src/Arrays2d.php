<?php
namespace xjryanse\logic;

use xjryanse\logic\Arrays;
use xjryanse\system\service\SystemFileService;
/**
 * 二维数组处理逻辑
 */
class Arrays2d
{
    /**
     * 二维数组矩阵转置
     */
    public static function transpose( array $data ) {
        $myData = array_values($data);
        if(!is_array($myData[0])){
            return false;
        }
        $keys = array_keys( $myData[0] );
        foreach($keys as &$v){
            $tmp    = array_merge( [$v],array_column($myData, $v) );
            $resData[]  = $tmp;
        }
        return $resData;
    }
    
    /**
    * 二维数组首行转键
    */
    public static function shiftToKey( array $data ) {
        $first = array_shift($data);
        $resData = [];
        foreach( $data as $k=>$v){
            $tmpData = [];
            foreach($first as $kk=>$kv){
                $tmpData[ $kv ] = $v[ $kk ];
            }
            $resData[] = $tmpData;
        }
        return $resData;
    }
    
    /**
     * 二维数组键名替换
     * @param array $data   
     * @param array $keys
     * @param type $mergeRaw    是否合并原数组
     * @return array
     */
    public static function keyReplace( array $data, array $keys, $mergeRaw = false ) {
        $resData = [];
        foreach( $data as $k=>$v){
            foreach($keys as $kk=>$kv){
                if(isset($v[$kk])){
                    $resData[$k][$kv] = $v[$kk];
                }
            }
            //是否合并原数组
            if($mergeRaw){
                $resData[$k] = array_merge($resData[$k], $v);
            }
        }

        return $resData;
    }
    
    /**
     * 无数据设默认值
     * @param array $data           数据
     * @param string $mainField     主字段：    如日期字段名
     * @param array $mainColumn     主字段数组：如日期数组
     * @param string $valueField
     * @param type $default
     */
    public static function noValueSetDefault( array $data, string $mainField, array $mainColumn, string $valueField, $default = "")
    {
        //用于存储返回结果的数组
        $respData = [];
        //循环全部待取key值
        foreach( $mainColumn as $v ){
            //比对数据数组，如有，则赋值
            foreach( $data as &$value){
                if($value[ $mainField ] == $v){
                    $respData[$v] = $value;
                    break;
                }
            }
            //已赋值的，进入下一个key循环
            if(isset($respData[$v])){
                continue;
            }
            //未赋值的，设置默认值。主字段$v；值字段$default
            $respData[$v] = [ $mainField => $v, $valueField => $default ];
        }
        return $respData;
    }
    
    /**
     * 数组指定键取值
     * @param type $array   数据数组
     * @param type $keys    键值数组
     * @return type
     */
    public static function getByKeys(array $array, $keys )
    {
        // 20230609:兼容逗号分隔
        if(!is_array($keys)){
            $keys = explode(',',$keys);
        }
        
        foreach($array as &$value){
            $match = array_fill_keys($keys, "");
            //比较两个（或更多个）数组的键名 ，并返回交集。
            $value = array_intersect_key( $value , $match);
        }
        return $array;
    }
    
    public static function hideKeys(array $array,array $keys )
    {
        foreach($array as &$value){
            $match = array_fill_keys($keys, "");
            //比较两个（或更多个）数组的键名 ，并返回差集。
            $value = array_diff_key( $value , $match);
        }
        return $array;
    }
    /**
     * 将某个字段设为key（需唯一）
     */
    public static function fieldSetKey( $array,$keyField){
        return $array ? array_column($array,null,$keyField) : [];
    }
    /*
     * 将某个字段设为key，列表成为数组
     */
    public static function fieldSetKeyArr( $array,$keyField){
        $arr = [];
        foreach($array as $v){
            $arr[$v[$keyField]][] = $v;
        }
        return $arr;
    }
    /**
     * 转一维键值对
     */
    public static function toKeyValue(array $array, $keyField, $valueField){
        $keys = array_column($array, $keyField);
        $values = array_column($array, $valueField);
        return array_combine($keys, $values);
    }
    /**
     * 根据指定字段的值，返回新数组
     * 场景示例：批量提取了10个订单的流程节点，需要按每个订单进行拆分
     */
    public static function listByFieldValue( $array, $keyField, $value){
        $tempArr = [];
        foreach($array as &$arrItem){
            if(Arrays::value($arrItem, $keyField) == $value){
                $tempArr[] = $arrItem;
            }
        }
        return $tempArr;
    }
    /**
     * 列表数据过滤
     * @param type $listsAll 二维数组数据
     * @param type $con     过滤条件（兼容数据库查询）
     */
    public static function listFilter( $listsAll, $con = [] ){
        if(!$listsAll){
            return [];
        }
        /*
        $dataArr = [];
        foreach($listsAll as $data){
            if(Arrays::isConMatch($data, $con)){
                $dataArr[] = $data;
            }
        }
        return $dataArr;
        */
        // 20241202：尝试优化
        $dataArr = array_filter($listsAll, function ($data) use ($con) {
            return Arrays::isConMatch($data, $con);
        });
        // 20241204:客诉，排查发现是key没有重置
        return array_values($dataArr);
    }
    /**
     * 按条件统计数量
     * @createTime 2023-09-13
     * @param type $listsAll
     * @param type $con
     * @return type
     */
    public static function listCount($listsAll, $con = [] ){
        return count(self::listFilter($listsAll, $con));
    }
    /**
     * 列表数据过滤,取单条
     * @param type $listsAll 二维数组数据
     * @param type $con     过滤条件（兼容数据库查询）
     */
    public static function listFind( $listsAll, $con = [] ){
        foreach($listsAll as $data){
            if(Arrays::isConMatch($data, $con)){
                return $data;
            }
        }
        return [];
    }
    /**
     * 20241125:从二维中查找符合条件的一维
     * @param type $listsAll
     * @param type $data
     */
    public static function dataListFind($listsAll, $data){
        $con = [];
        foreach ($data as $k => $v) {
            $con[] = [$k, '=', $v];
        }
        return self::listFind($listsAll, $con);
    }
    
    /**
     * 键值对查询是否存在
     * 2023-09-15
     * @param array $listsAll
     * @param type $key
     * @param type $value
     */
    public static function listFindByField(array $listsAll, $key, $value){
        $con    = [];
        $con[]  = [$key,'=',$value];
        return self::listFind($listsAll, $con);
    }

    /**
     * 查某列的最大值，取整行数据
     * @param type $listsAll
     * @param type $con
     */
    public static function maxFind( $listsAll, $judgeField, $con = [] ){
        $arrays = self::listFilter($listsAll, $con);
        $arr = self::sort($arrays, $judgeField, 'desc');
        return $arr ? $arr[0] : [];
    }
    /**
     * 查某列的最小值，取整行数据
     * @param type $listsAll
     * @param type $con
     */
    public static function minFind( $listsAll, $judgeField, $con = [] ){
        $arrays = self::listFilter($listsAll, $con);
        $arr = self::sort($arrays, $judgeField, 'asc');
        return $arr ? $arr[0] : [];
    }

    /**
     * 排序
     */
    public static function sort( &$array ,$field, $sort="asc"){
        $arr_keys = [];
        foreach ($array as $items) {
            $arr_keys[] = $items[$field];
        }
        if($sort == 'desc'){
            array_multisort($arr_keys, SORT_DESC, SORT_NUMERIC, $array);
        } else {
            array_multisort($arr_keys, SORT_ASC, SORT_NUMERIC, $array);
        }
        return $array;
    }
    /**
     * 求和
     */
    public static function sum( $array, $field){
        // 20240531处理0值
        foreach($array as &$v){
            if(!Arrays::value($v, $field)){
                $v[$field] = 0;
            }
        }
        return array_sum(array_column($array, $field));
    }
    
    /**
     * 指定列，取唯一
     * @param type $arr
     * @param type $field
     */
    public static function uniqueColumn($arr, $field){
        return array_unique(array_column($arr, $field));
    }
    /**
     * 提取二维数组唯一值
     */
    public static function unique($arr){
        $r = [];
        foreach($arr as $v){
            $kJ     = json_encode($v);
            $k      = md5($kJ).sha1($kJ);
            $r[$k]  = $v;
        }
        return array_values($r);
    }
    
    /***以下部分有耦合SystemFileService类，和db方法，是否进行拆分比较科学？？20220305******************************************************************/
    /**
     * 二维数组，图像字段，id转数组
     */
    public static function picFieldCov(&$data,$picFields = []){
        if(!$picFields){
            return $data;
        }
        $picIds = [];
        foreach($picFields as &$picField){
            $picIds = array_merge($picIds, array_column($data,$picField));
        }
        Debug::debug('picFieldCov的$picIds',$picIds);
        if(array_unique($picIds)){
            //根据图像id，提取已有的图像列表
//            $conPic[] = ['id','in', array_unique($picIds)];
//            //$fileTable = SystemFileService::mainModel()->getTable();
//            $picObjs = SystemFileService::mainModel()->where( $conPic )->field('id,file_type,file_path,file_path as rawPath')->select();
//            $picArr = $picObjs ? $picObjs->toArray() : [];
//            
            $picArr = SystemFileService::filesWithSys($picIds);
            $picObj = self::fieldSetKey($picArr, 'id');
            //拼接图像
            foreach($data as &$v){
                foreach($picFields as &$picField){
                    // TODO [ 20220518 ] [8]未定义数组索引: icon_pic[/www/wwwroot/tenancy.xiesemi.cn/vendor/xjryanse/logic/src/Arrays2d.php:201]
                    // 20220807，非字符串不转
                    $v[$picField] = isset($picObj[$v[$picField]]) ? $picObj[$v[$picField]] : [];
                }
            }
        }

        return $data;
    }
    /*****多图*****/
    public static function multiPicFieldCov(&$data,$picFields = []){
        if(!$picFields){
            return $data;
        }
        $picIds = [];
        foreach($picFields as &$picField){
            $picIdStr = implode(',',array_column($data,$picField));
            $picIds = array_merge($picIds, explode(',',$picIdStr));
        }
        Debug::debug('picFieldCov的$picIds',$picIds);
        if(array_unique($picIds)){
            //根据图像id，提取已有的图像列表
//            $conPic[] = ['id','in', array_unique($picIds)];
            //$fileTable = SystemFileService::mainModel()->getTable();
//            $picObjs = SystemFileService::mainModel()->where( $conPic )->field('id,file_type,file_path,file_path as rawPath')->select();
//            $picArr = $picObjs ? $picObjs->toArray() : [];
            
            $picArr = SystemFileService::filesWithSys($picIds);
            $picObj = self::fieldSetKey($picArr, 'id');
            //拼接图像
            foreach($data as &$v){
                foreach($picFields as &$picField){
                    // TODO [ 20220518 ] [8]未定义数组索引: icon_pic[/www/wwwroot/tenancy.xiesemi.cn/vendor/xjryanse/logic/src/Arrays2d.php:201]
                    // 20220807，非字符串不转
                    $idArr = explode(',',$v[$picField]);
                    if($idArr){
                        $v[$picField] = [];
                    }
                    foreach($idArr as $picId){
                        // 20230414：客诉实名制多图
                        if($picId){
                            $v[$picField][] = isset($picObj[$picId]) ? $picObj[$picId] : [];
                        }
                        //$v[$picField][] = isset($picObj[$picId]) ? $picObj[$picId] : [];
                    }
                }
            }
        }

        return $data;
    }
    /**
     * 2022-12-18混合图片字段转换：用于配置表
     * @param type $listsAll
     * @param type $mixPicFields    示例如下：
     *  tKey:判断的key，tVal:当tKey为该值，对cKey的值进行转换
        public static $mixPicFields = [['tKey'=>'type','tVal'=>'uplimage','cKey'=>['value']]];
     */
    public static function mixPicFieldCov($listsAll, $mixPicFields ){
        foreach($mixPicFields as $pFields){
            $con    = [];
            $con[]  = [$pFields['tKey'],'=',$pFields['tVal']];
            $arr        = self::listFilter($listsAll, $con);
            $covData    = self::picFieldCov($arr, $pFields['cKey']);
            $covDataObj = self::fieldSetKey($covData, 'id');
            // 赋值
            foreach($listsAll as $k=>$v){
                if(isset($covDataObj[$v['id']])){
                    $listsAll[$k] = $covDataObj[$v['id']];
                }
            }
        }
        return $listsAll;
    }
    /**
     * 聚合统计
     * @param type $lists   二维数组
     * @param type $group   聚合字段
     * @return type
     */
    public static function groupCount($lists ,$group){
        $keys = self::uniqueColumn($lists, $group);
        $cArr = [];
        foreach($keys as $key){
            $con        = [];
            $con[]      = [$group,'=',$key];
            $cArr[$key] = count(self::listFilter($lists, $con));
        }
        return $cArr;
    }
    /**
     * 聚合求和
     * @param type $lists       二维数组
     * @param type $group       聚合字段
     * @param type $sumField    求和字段
     * @return type
     */
    public static function groupSum($lists, $group, $sumField){
        $keys = self::uniqueColumn($lists, $group);
        $cArr = [];
        foreach($keys as $key){
            $con        = [];
            $con[]      = [$group,'=',$key];
            $cArr[$key] = self::sum(self::listFilter($lists, $con),$sumField);
        }
        return $cArr;        
    }
    
    /**
     * 20230322：传二维数组，按指定字段求和
     */
    public static function fieldsSum($lists,$sumFields){
        if(!$sumFields){
            return [];
        }
        if(!is_array($sumFields)){
            $sumFields = [$sumFields];
        }

        $sumArr = [];
        foreach($sumFields as $v){
            $sumArr[$v] = array_sum(array_column($lists,$v));
        }
        
        return $sumArr;
    }
    /**
     * 20230428：一维数组组合成二维数组
     */
    public static function combination(array $arrays1,array $arrays2){
        $resArr = [];
        foreach($arrays1 as $v1){
            foreach($arrays2 as $v2){
                $resArr[] = [$v1, $v2];
            }
        }
        return $resArr;
    }

    /**
     * 二维数组转树状数组
     * 20230903:从treeTrait搬迁来，逐步替代
     * @param type $arr  二维数组
     * @param type $pid     父类id
     * @param type $pidname 父类字段名
     * @param type $child   子元素名
     * @return type
     */
    public static function makeTree($arr,$pid='',$pidname='pid',$child='list')
    {
        $trees = [];
        foreach ($arr as $item) {
            $iName = $item[$pidname] ? : '';
            if( $iName != $pid ){
                continue;
            }
            $sItem = self::makeTree($arr,$item['id'], $pidname, $child);
            if($sItem){
                $item[$child] = $sItem;
            }
            $trees[] = $item;
        }

        return $trees;
    }
    /**
     * 二维数组统一添加数据
     * 20230912
     * @param type $arr
     * @param type $data    一维数组
     */
    public static function addData(&$arr, $data = []){
        foreach($arr as &$v){
            $v = $data ? array_merge($v,$data) : $v;
        }
        return $arr;
    }

    /**
     * 二维，对象取值
     * @param type $arrObj  二维对象，一般是fieldSetKey处理后的结果
     * @param type $key     key值
     * @param type $field   第二层的字段值
     * @param type $default 取不到结果时的默认值
     * @return type
     */
    public static function objKeyFieldGet(&$arrObj, $key, $field, $default = ''){
        return Arrays::value($arrObj,$key) 
            ? Arrays::value($arrObj[$key], $field)
            : $default;
    }
    
    /**
     * 计算求和数组（列表底部的求和）
     */
    public static function calSumArray($list, $sumFields){
        $sumData = [];
        foreach($sumFields as $sumField){
            $sumData[$sumField] = Arrays::sum(array_column($list, $sumField));
        }
        return $sumData;
    }
    
    /**
     * 20240106-拼接时间字段
     * 与Datetime的paramScopeTime一致
     * 
     */
    public static function pushTimeField(&$listArr, $param){
        foreach($listArr as &$v){
            if(Arrays::value($param, 'yearmonthDate')){
                $v['yearmonthDate'] = $param['yearmonthDate'];
            }
            if(Arrays::value($param, 'yearmonth')){
                $v['yearmonth'] = $param['yearmonth'];
            }
            if(Arrays::value($param, 'year')){
                $v['year'] = $param['year'];
            }        
        }
        return $listArr;
    }
    /**
     * 
     * @param type $listArr
     */
    public static function pushDataFields (&$listArr, $param, $fields) {
        foreach($listArr as &$v){
            foreach($fields as $f){
                $v[$f] = Arrays::value($param, $f);
            }
        }
        return $listArr;        
    }
    /**
     * 20250126:二维数组列数
     */
    public static function colCount($arr){
        $maxColumnCount = 0;
        foreach ($arr as $subArray) {
            $currentColumnCount = count($subArray);
            if ($currentColumnCount > $maxColumnCount) {
                $maxColumnCount = $currentColumnCount;
            }
        }
        return $maxColumnCount;
    }
    
    /**
     * 20250126:二维数组行数
     */
    public static function rowCount($arr){
        return count($arr);
    }
    /**
     * 20250126：判断一个数组是否二维数组
     * @return bool
     */
    public static function isArrays2d($arr){
        if (!is_array($arr)) {
            return false;
        }
        foreach ($arr as $item) {
            if (is_array($item)) {
                return true;
            }
        }
        return false;
    }
}
