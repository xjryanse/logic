<?php
namespace xjryanse\logic;

/**
 * 拼装模型查询条件
 */
class ModelQueryCon
{
    /**
     * 获取class
     * @param type $type
     */
    private static function getClassStr($type) {
        return '\\xjryanse\\logic\\ModelQueryCon\\' . ucfirst($type);
    }
    
    /**
     * 选项转换
     * @param type $type    类型
     * @param type $key     key
     * @param type $value   值
     * @return type
     */
    public static function getCon($type,$key, $value) {
        $class = self::getClassStr($type);
        return class_exists($class) ? $class::getCon($key,$value) : [];
    }
    /**
     * 条件拆解成and 连接
     */
    public static function conditionParse( $con ,$alias = "")
    {
        //条件参数的形式：$con[] = ['aa','=','bb'];
        $condition = [];
        foreach($con as $v){
            $tmpStr = "";
            if($v[1] == "in"){
                if(is_string($v[2])){
                    $v[2] = [$v[2]];
                }
                $v[2] = "(".implode(',',$v[2]).")";
            } else {
                $v[2] = '\''.$v[2].'\'';
            }
            $condition[] = $alias ? $alias . '.' .implode(' ',$v) : implode(' ',$v);
        }
        //将参数组装成 and 连接，没有条件时，丢个1，兼容where 
        $conStr = $condition ? implode( ' and ',$condition ) : 1 ;         
        return $conStr;
    }    
    
    /**
     * 查询条件封装
     *
     * @param array $param  参数列表
     * @param array $fields 字段列表    key值0:精确查找;1:模糊查找;2:in查找;3:数据范围查找;4:时间查找
     *                      示例
     *                      $param=['hello'=>'111','like'=>'222','timea'=>4445,'timeb'=>7859,]
     *                      $fields[0] = ['hi'=>'hello','like'=>'like'];
     *                      $fields[1] = ['like'=>'like'];
     *                      $fields[3] = ['num'=>['3','5']];
     */
    public static function queryCon(array $param, array $fields,$style = "where") {
        $con = [];
        //遍历每个字段列表
        foreach ($fields as $k => &$v) {
            //键对应查询条件：0精确查找、1模糊查找、2in查找
            //值为数组。值键对应数据库字段，值值对应参数组键名
            foreach ($v as $key => &$value) {
                if (is_int($key)) {
                    $key = $value;
                }
                //解析成where;
                $tmp = self::condition($k, $key, $param, $value);
                if ($tmp) {
                    $con = array_merge($con, $tmp);
                }
            }
        }
        //如果是having，聚合一下
        if($style == 'having'){
            foreach( $con as &$v){
                $v[0] = '`'.$v[0].'`';
                $v[2] = "'".$v[2]."'";
                $v = implode(' ',$v);
            }
        }
        
        
        return $con;
    }

    /**
     * 查询条件预封装
     *
     * @param int|string $k     equal:精确查找;like:模糊查找;2:in查找;numberscope:数据范围查找;timescope:时间范围查找
     * @param string     $key   数据库字段名
     * @param array      $param 入参数组
     * @param string     $value 入参字段名
     */
    private static function condition($k, $key, &$param, $value) {
        $con = [];
        switch ($k) {
            case "equal": //精确查找
                if (isset($param[$value]) && !is_array($param[$value]) && strlen($param[$value])) {
                    $con[] = [$key, '=', self::preg($param[$value])];
                }
                break;
            case "like": //模糊查找
                if (isset($param[$value]) && strlen($param[$value])) {
                    $con[] = [$key, 'like', '%' . self::preg($param[$value]) . '%'];
                }
                break;
            case "in": //in查找
                if (isset($param[$value])) {
                    $con[] = [$key, 'in', $param[$value]];
                }
                break;
            case "numberscope": //数据范围查找
                if (isset($param[$value][0]) && strlen($param[$value][0])) {
                    $con[] = [$key, '>=', $param[$value][0]];
                }
                if (isset($param[$value][1]) && strlen($param[$value][1])) {
                    $con[] = [$key, '<=', $param[$value][1]];
                }
                break;
            case "timescope": //时间范围查询
                if (isset($param[$value][0]) && strlen($param[$value][0])) {
                    $param[$value][0] = date('Y-m-d 00:00:00', strtotime($param[$value][0]));
                    $con[] = [$key, '>=', $param[$value][0]];
                }
                if (isset($param[$value][1]) && strlen($param[$value][1])) {
                    $param[$value][1] = date('Y-m-d 23:59:59', strtotime($param[$value][1]));
                    $con[] = [$key, '<=', $param[$value][1]];
                }
                break;
            case "notin": //not in查询
                if (isset($param[$value])) {
                    $con[] = [$key, 'not in', $param[$value]];
                }
                break;
            case 6: //not in查询
                if (isset($param[$value])) {
//					$con[] = [$key, 'FIND_IN_SET', $param[$value]];
                    $con[] = ['', "FIND_IN_SET('" . $param[$value] . "'," . $key . ')', ''];
                }
                break;
            default:
        }
        Debug::debug('ModelQueryCon查询条件', $con);
        return $con;
    }
    
    /**
     * 正则替换防注入
     *
     * @param type $str
     *
     * @return type
     */
    private static function preg($str) {
        return preg_replace("/\+|\`|\*|\-|\$|\#|\^|\!|\@|\%|\&|\~|\[|\]|\,|\'|\s|/", "", $str);
    }

    /**
     * 是否Y-m-d日期
     *
     * @param $str 待检查字符串
     *
     * @return bool
     */
    private static function isDate($str) {
        if (preg_match("/^((?:19|20)\d\d)-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])$/", $str)) {
            return true;
        } else {
            return false;
        }
    }    
}
