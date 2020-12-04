<?php

/* 
 * 【公共常量】
 */

const RES_CODE_SUCCESS          = 0;    //请求成功返回码；
const RES_CODE_ERROR            = 1;    //请求失败返回码；
const RES_CODE_NOTOKEN          = 1000; //缺少访问凭据；
const RES_CODE_INVALID_TOKEN    = 1001; //无效访问凭据；
const RES_CODE_NO_LOGIN         = 1003; //用户未登录；
const RES_CODE_NO_INFO          = 1004; //用户未完善信息；

/**
 * 审核状态的常量
 */
const AUDIT_TODO    = 0;//待审核
const AUDIT_PASS    = 1;//审核通过
const AUDIT_REJECT  = 2;//审核拒绝

/**
 * 事件处理的常量
 */
const XJRYANSE_OP_TODO      = 'todo';   //待处理
const XJRYANSE_OP_DOING     = 'doing';  //进行中
const XJRYANSE_OP_FINISH    = 'finish'; //已完成
const XJRYANSE_OP_CLOSE     = 'close';  //已关闭

/**
 * session 名称常量
 */

const SESSION_APP_ID        =   'scopeAppId';           //全局APPID
const SESSION_COMPANY_ID    =   'scopeCompanyId';       //全局公司id
const SESSION_COMPANY_KEY   =   'scopeCompanyKey';      //全局公司key
const SESSION_USER_ID       =   'scopeUserId';          //全局用户id
const SESSION_WEPUB_CALLBACK = "wePubCallBackUrl";      //微信公众号授权回调链接

//框架字段类型:FR_框架
const FR_COL_TYPE_EMPTY         = 'empty';      //空
const FR_COL_TYPE_HIDDEN        = 'hidden';     //隐藏域
const FR_COL_TYPE_TEXT          = 'text';       //文本
const FR_COL_TYPE_PASSWORD      = 'password';   //密码
const FR_COL_TYPE_TEXTAREA      = 'textarea';   //文本框
const FR_COL_TYPE_CHECK         = 'check';      //复选勾选
const FR_COL_TYPE_RADIO         = 'radio';      //单选勾选
const FR_COL_TYPE_ENUM          = 'enum';       //枚举
const FR_COL_TYPE_DYNENUM       = 'dynenum';    //动态枚举
const FR_COL_TYPE_DYNTREE       = 'dyntree';    //动态树
const FR_COL_TYPE_TPLSET        = 'tplset';     //可根据模板来进行分组设定值：主要用于：价格设定
const FR_COL_TYPE_SWITCH        = 'switch';     //开关
const FR_COL_TYPE_MULTISELECT   = 'multiSelect';//复选框
const FR_COL_TYPE_SINGLESELECT  = 'singleSelect';//单选框
const FR_COL_TYPE_CITYPICKER    = 'citypicker'; //省市县选择器
const FR_COL_TYPE_UPLIMAGE      = 'uplimage';   //上传图片
const FR_COL_TYPE_DATE          = 'date';       //日期
const FR_COL_TYPE_EDITOR        = 'editor';     //编辑器
const FR_COL_TYPE_NUMBER        = 'number';     //只能输入数字
const FR_COL_TYPE_PRIZE         = 'prize';      //价格设置
const FR_COL_TYPE_PHONE         = 'phone';      //输入手机号码
const FR_COL_TYPE_IDNO          = 'idno';       //输入身份证号码

//框架关联option字段的含义
const FR_OPT_TABLE_NAME         = 'table_name'; //关联表名
const FR_OPT_PID                = 'pid';        //关联表指示父id的字段名（用于树状）
const FR_OPT_KEY                = 'key';        //关联表键字段
const FR_OPT_VALUE              = 'value';      //关联表值字段
    //写入表
const FR_OPT_TO_TABLE           = 'to_table';       //【写入表】数据写入表名
const FR_OPT_TO_FIELD           = 'to_field';       //【写入表】数据写入字段名
const FR_OPT_MAIN_FIELD         = 'main_field';     //【写入表】关联主表id的字段名
    //模板表
const FR_OPT_TPL_TABLE          = 'tpl_table';      //模板表名
const FR_OPT_TPL_MAIN_KEY       = 'tpl_main_key';   //模板主列key
const FR_OPT_TPL_GROUP_KEY      = 'tpl_group_key';  //模板分组key
const FR_OPT_TPL_DATA_KEY       = 'tpl_data_key';   //模板数据key，用于和数据表的key匹配;
const FR_OPT_TPL_COND           = 'tpl_cond';       //【写入表】关联主表id的字段名
const FR_OPT_OPTION_COV         = 'option_cov';     //【写入表】选项卡转换

    //写入表额外
const FR_OPT_MAIN_DATA_KEY      = 'main_data_key';  //【写入表】关联主表id的字段名
const FR_OPT_MAIN_COND          = 'main_cond';      //【写入表】关联主表的条件

#tpl_table=temp_goods_prize_key&tpl_main_key=main_key&tpl_group_key=belong_role&tpl_data_key=prize_key&to_table=ydzb_goods_prize&to_field=prize&main_field=goods_id&main_data_key=prize_key

#table_name=ydzb_user_auth_access&key=id&value=name&to_table=ydzb_user_auth_role_access&pid=pid&to_field=access_id&main_field=role_id

