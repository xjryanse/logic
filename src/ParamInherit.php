<?php
namespace xjryanse\logic;

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
        //   methodKey:方法key
        $defaultArr     = ['admKey','comKey','pCompanyId','methodKey'];
        $requestParams  = Request::only( array_merge( $params, $defaultArr ) );
        //comKey：参数优先，openid，兼容微信
        $res = array_merge(['comKey'=>session(SESSION_COMPANY_KEY),'openid'=>session(SESSION_OPENID)],$requestParams);
        return $res;
    }
}
