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
    public static function toYearlyData($year, $listsArr, $groupFields, $typeFieldName, $typesArr){
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
    public static function toMonthlyData($yearmonth, $listsArr, $groupFields, $typeFieldName, $typesArr){
        if(is_string($groupFields)){
            $groupFields = [$groupFields];
        }
        $dataArr = self::listToObj($listsArr, $groupFields, ['yearmonth','date']);
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
            $dataRes                = array_merge($dataRes, DataList::dataSplitDayArr($dataArr, $typeFieldName, $typesArr, $tmpData));
        }
        //③数据求和
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
    public static function dataSplitDayArr($dataArr, $typeFieldName, $typesArr, $tmpData = []){
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
                $sum1000 += $tmp['d'.$i] * 1000;
            }
            $tmp['sum'] = $sum1000 / 1000;
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
     */
    public static function dataPackPaginate($list, $withSum = false, $sumFields = []){
        $data['data']           = $list;
        $data['last_page']      = 1;
        $data['current_page']   = 1;
        $data['per_page']       = count($list);
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
    
}
