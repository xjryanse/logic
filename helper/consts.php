<?php

/* 
 * 【公共常量】
 */

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
const FR_COL_TYPE_TEXTAREA      = 'textarea';   //文本框
const FR_COL_TYPE_CHECK         = 'check';      //勾选
const FR_COL_TYPE_ENUM          = 'enum';       //枚举
const FR_COL_TYPE_DYNENUM       = 'dynenum';    //动态枚举
const FR_COL_TYPE_DYNTREE       = 'dyntree';    //动态树
const FR_COL_TYPE_SWITCH        = 'switch';     //开关
const FR_COL_TYPE_MULTISELECT   = 'multiSelect';//复选框
const FR_COL_TYPE_CITYPICKER    = 'citypicker'; //省市县选择器
const FR_COL_TYPE_UPLIMAGE      = 'uplimage';   //上传图片
const FR_COL_TYPE_DATE          = 'date';       //日期
const FR_COL_TYPE_EDITOR        = 'editor';     //编辑器

