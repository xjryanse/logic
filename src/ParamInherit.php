<?php
namespace xjryanse\logic;

use think\facade\Request;
/**
 * 请求参数继承类库
 */
class ParamInherit
{
    public static function get( $params = [])
    {
        //      admKey:表键
        //      comKey:公司键
        //  pCompanyId:父级公司id
        $defaultArr     = ['admKey','comKey','pCompanyId'];
        $requestParams  = Request::only( array_merge( $params, $defaultArr ) );
        //comKey：参数优先
        $res = array_merge(['comKey'=>session('scopeCompanyKey')],$requestParams);
        return $res;
    }
}
