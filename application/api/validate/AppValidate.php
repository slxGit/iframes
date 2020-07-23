<?php

/**
 * KirinBDF
 *
 * A Kirin Base Development Framework Based on ThinkPHP & Layui
 *
 * @System:     KirinBDF
 * @Version:    Version 1.0
 * @Link:       http://www.kirinbdf.com/
 * @Copyright:  Copyright (c) 2017 - 2019, KirinBDF Team.
 *
 * @Author:  Aaron
 * @Email:   wo1425768@163.com
 * @Date:    2019-05-10 17:03:40
 *
 * @Last Modified By:   Aaron
 * @Last Modified Time: 2020-02-18 00:20:25
 */
 // ------------------------------------------------------------------------
namespace app\api\validate;

use think\Validate;

class AppValidate extends Validate
{
    protected $rule = [
    	'name'       => 'require',
        'appid'      => 'require',
        'appsecret'  => 'require'
    ];
    
    protected $message = [
    	'name.require'       => '应用名称不能为空',
        'appid.require'      => '应用ID不能为空',
        'appsecret.require'  => '应用密钥不能为空'
    ];

}