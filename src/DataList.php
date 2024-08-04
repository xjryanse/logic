<?php
namespace xjryanse\logic;

use xjryanse\logic\Arrays;
/**
 * 数据库查询的数组数据处理
 * 是Arrays2d的扩展
 */
class DataList
{
    /**
     * 转为按年统计
     */
    /**
     * 
     * @param type $year
     * @param type $listsArr
     * @param type $groupFields
     * @param type $typeFieldName
     * @param type $typesArr
     * @return int
     */
    public static function toYearlyData($year, $listsArr, $groupFields, $typeFieldName, $typesArr,$isSum=true){
        if(is_string($groupFields)){
            $groupFields = [$groupFields];
        }
        $dataArr = self::listToObj($listsArr, $groupFields, ['year','month']);
        
        $customerInfoArr = array_unique(Arrays2d::getByKeys($listsArr, $groupFields), SORT_REGULAR);
        //③数据组装
        $dataRes = [];
        foreach($customerInfoArr as $cust){
            $tmpData = [];
            // 2023-01-12:替换
            foreach($groupFields as $groupField){
                $tmpData[$groupField]   = $cust[$groupField];
            }
            $tmpData['year']        = $year;
            $dataRes                = array_merge($dataRes, DataList::dataSplitMonthArr($dataArr, $typeFieldName, $typesArr, $tmpData));
        }
        //③数据求和
        $sumData = [];
        for($i = 1;$i<=12;$i++){
            // $sumData['m'.$i] = array_sum(array_column($dataRes, 'm'.$i));
            $sumData['m'.$i] = Arrays::sum(array_column($dataRes, 'm'.$i));
        }
        // 2022-11-18:单个才求和
        if(!is_array($typesArr) || count($typesArr) == 1){
            // $sumData['sum'] = array_sum(array_column($dataRes, 'sum'));
            $sumData['sum'] = Arrays::sum(array_column($dataRes, 'sum'));
        }
        
        $res['sumData']         = $sumData;
        $res['data']            = $dataRes;
        $res['withSum'] = 1;
        return $res;
    }
    /**
     * 
     * @param type $yearmonth
     * @param type $listsArr    数组，必备 yearmonth，date
     * @param type $groupFields  数组
     * @param type $typeFieldName
     * @param type $typesArr
     * @return int
     */
    public static function toMonthlyData($yearmonth, $listsArr, $groupFields, $typeFieldName, $typesArr, $isSum=true){
        if(is_string($groupFields)){
            $groupFields = [$groupFields];
        }
        // Debug::dump($listsArr);
        $dataArr = self::listToObj($listsArr, $groupFields, ['yearmonth','date']);
        // Debug::dump($dataArr);
        //$customerIds = array_unique(array_column($listsArr, $groupField));
        $customerInfoArr = array_unique(Arrays2d::getByKeys($listsArr, $groupFields), SORT_REGULAR);
        //③数据组装
        $dataRes = [];
        foreach($customerInfoArr as $cust){
            $tmpData = [];
            // 2023-01-12:替换
            foreach($groupFields as $groupField){
                $tmpData[$groupField]   = $cust[$groupField];
            }
            $tmpData['yearmonth']   = $yearmonth;
            $dataRes                = array_merge($dataRes, DataList::dataSplitDayArr($dataArr, $typeFieldName, $typesArr, $tmpData, $isSum));
        }
        //③数据求和
        if($isSum){
            $sumData = [];
            for($i = 1;$i<=31;$i++){
                $sumData['d'.$i] = Arrays::sum(array_column($dataRes, 'd'.$i));
            }
            // 2022-11-18:单个才求和
            if(!is_array($typesArr) || count($typesArr) == 1){
                // $sumData['sum'] = array_sum(array_column($dataRes, 'sum'));
                $sumData['sum'] = Arrays::sum(array_column($dataRes, 'sum'));
            }

            $res['sumData']         = $sumData;
        }
        $res['data']            = $dataRes;
        $res['withSum']         = 1;
        return $res;
    }
    /**
     * 将数据拆分成
     * @param type $dataArr         一维数据数组
     * @param type $typeFieldName   字段类型名:eg:moneyType
     * @param type $typesArr        字段类型数组
     */
    public static function dataSplitMonthArr($dataArr, $typeFieldName, $typesArr, $tmpData = []){
        if(!$typesArr){
            return [];
        }
        if(!is_array($typesArr)){
            $typesArr = [$typesArr];
        }

        $dataRes = [];
        foreach($typesArr as $mT){
            $tmp = $tmpData;
            // $tmp['customer_id'] = $customerId;
            // $tmp['year']        = $year;
            $tmp[$typeFieldName]   = $mT;
            $sum1000 = 0;
            $iArr = [1,2,3,4,5,6,7,8,9,10,11,12];
            // key的前序
            $keyPreStr = implode('_',$tmpData);
            foreach($iArr as $i){
                // 2023-01-12：拼接月份数组
                $month          = str_pad($i, 2, 0, STR_PAD_LEFT);
                $key            = $keyPreStr.'_'.$month;
                $tmp['m'.$i]    = Arrays::value($dataArr, $key) ? $dataArr[$key][$mT] : 0;
                $sum1000 += $tmp['m'.$i] * 1000;
            }
            $tmp['sum'] = $sum1000 / 1000;
            $dataRes[] = $tmp;
        }
        return $dataRes;
    }
    
    /**
     * 将数据拆分成
     * @param type $dataArr         一维数据数组
     * @param type $typeFieldName   字段类型名:eg:moneyType
     * @param type $typesArr        字段类型数组
     */
    public static function dataSplitDayArr($dataArr, $typeFieldName, $typesArr, $tmpData = [], $isSum=true){
        if(!$typesArr){
            return [];
        }
        if(!is_array($typesArr)){
            $typesArr = [$typesArr];
        }

        $dataRes = [];
        foreach($typesArr as $mT){

            $tmp = $tmpData;
            // $tmp['customer_id'] = $customerId;
            // $tmp['year']        = $year;
            $tmp[$typeFieldName]   = $mT;
            $sum1000 = 0;
            // $iArr = [1,2,3,4,5,6,7,8,9,10,11,12];
            // key的前序
            $keyPreStr = implode('_',$tmpData);
            // foreach($iArr as $i){
            for($i = 1;$i<=31;$i++){
                // 2023-01-12：拼接月份数组
                $month          = str_pad($i, 2, 0, STR_PAD_LEFT);
                $key            = $keyPreStr.'_'.$month;

                $tmp['d'.$i]    = Arrays::value($dataArr, $key) ? $dataArr[$key][$mT] : 0;
                // 20240511
                if($isSum && is_numeric($tmp['d'.$i])){
                    $sum1000 += $tmp['d'.$i] * 1000;
                }
            }
            if($isSum){
                $tmp['sum'] = $sum1000 / 1000;
            }
            $dataRes[] = $tmp;
        }
        return $dataRes;
    }
    /**
     * 20230117 数组转键值对对象
     * @param type $listsArr        原始数据
     * @param type $groupFields     聚合字段
     * @param type $timeKeys        时间字段 例如['yearmonth','date']
     */
    protected static function listToObj($listsArr, $groupFields, $timeKeys){
        if(is_string($groupFields)){
            $groupFields = [$groupFields];
        }
        $dataArr    = [];
        foreach($listsArr as $v){
            $keys = [];
            foreach($groupFields as $groupField){
                $keys[] = $v[$groupField];
            }            
            // $key .= $v['yearmonth'].'_'.$v['date'];
            foreach($timeKeys as $timeKey){
                $keys[] = $v[$timeKey];
            }
            $key = implode('_',$keys);
            // $key = $v[$groupField].'_'.$v['yearmonth'].'_'.$v['date'];
            $dataArr[$key] = $v;
        }
        return $dataArr;
    }
    /*
     * 20230421:数据列表，转换为分页的数据格式
     * @param type $list
     * @param type $withSum
     * @param type $sumFields
     * @param type $perPage
     * @param type $thisPage
     * @return type
     */
    public static function dataPackPaginate($list, $withSum = false, $sumFields = [], $perPage = 50, $thisPage = 1){
        $start = $perPage * ($thisPage - 1);
        $data['data']           = $list ? array_slice($list, $start, $perPage) : [];
        $data['last_page']      = ceil(count($list) / $perPage);
        $data['current_page']   = $thisPage;
        $data['per_page']       = $perPage;
        $data['total']          = count($list);
        $data['withSum']        = $withSum ? 1 : 0;

        if($withSum && $sumFields){
            $sumData = [];
            foreach($sumFields as $sumField){
                $sumData[$sumField] = Arrays::sum(array_column($list, $sumField));
            }
            $data['sumData'] = $sumData;
        }

        return $data;
    }
    /**
     * 20230730:将查询结果存在内存中，如果已有同样查询内容，直接返回，缺失的再查数组补。
     * 性能优化
     * @param type $instValues
     * @param type $ids
     * @param type $func
     */
    public static function dataObjAdd(&$instValues, $ids, $func ){
        // 数据处理开始
        // 20230730:兼容传单个id的情况
        if(is_string($ids) || is_numeric($ids)){
            $ids = [$ids];
        }
        $keys = $instValues ? array_keys($instValues) : [];
        // 提取不在$keys 中的id记录，用于查询
        $qIds       = array_diff($ids, $keys);
        // 20230730:如果没有id就不需要查了，节约开销
        if($qIds){
            $newArr     = $func($qIds);
            $instValues = Arrays::concat($instValues, $newArr);
        }

        return $instValues;
    }
    /**
     * 聚合成线性的数据，主要用于列转行，方便前端编辑
     * 20240531
     * @param type $listsArr    原始数据列表
     * @param type $colField    列字段
     * @param type $rowField    行字段
     * @param type $valField    值字段
     */
    public static function toLinelyData($listsArr, $colField, $rowField, $valField, $colAll, $isSum=false){
        $res['data'] = self::toLinelyArr($listsArr, $colField, $rowField, $valField, $colAll, $isSum);

        return $res;
    }

    
    /**
     * 聚合成线性的数据，主要用于列转行，方便前端编辑
     * 20240531
     * @param type $listsArr    原始数据列表
     * @param type $colField    列字段
     * @param type $rowField    行字段
     * @param type $valField    值字段
     * @param type $colAll      全部列
     * @return type
     */
    private static function toLinelyArr($listsArr, $colField, $rowField, $valField, $colAll = [], $isSum=false){
        $colFArr = explode(',',$colField);
        
        //【1】拼接成键值数据
        $listObj = [];
        foreach($listsArr as $v){
            //对应第二步key
            $keyArr          = [];
            foreach($colFArr as $cf){
                $keyArr[] = $v[$cf];
            }
            $key = implode('_', $keyArr).'_'.$v[$rowField];
            //对应第二步key
            // $key            = $v[$colField].'_'.$v[$rowField];
            $listObj[$key]  = Arrays::value($v, $valField);
        }
        //【2】组合列行数据
        $colUnique = $colAll ? : Arrays2d::unique(Arrays2d::getByKeys($listsArr, $colFArr));
        // 行
        $rows = Arrays2d::uniqueColumn($listsArr, $rowField);

        $arr = [];
        foreach($colUnique as $cu){
            $keyArr          = [];
            foreach($colFArr as $cf){
                $keyArr[] = $cu[$cf];
            }

        // foreach($cols as $c){
            $tmp            = $cu;
            // $tmp[$colField] = $c;
            $sum1000 = 0;
            foreach($rows as $r){
                $key = implode('_', $keyArr).'_'.$r;
                
                // 对应第一步key
                // $key            = $c.'_'.$r;
                $value          = Arrays::value($listObj, $key);
                $tmp['l'.$r]    = is_numeric($value) ? number_format($value,0,'.','') : $value;
                // 20240511
                if($isSum && is_numeric($tmp['l'.$r])){
                    $sum1000 += $tmp['l'.$r] * 1000;
                }
            }
            if($isSum){
                $tmp['sum'] = $sum1000 / 1000;
            }
            $arr[] = $tmp;
        }
        
        return $arr;
    }
    
    
}
