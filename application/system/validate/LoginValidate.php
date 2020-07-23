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
 * @Date:    2019-05-06 15:17:44
 *
 * @Last Modified By:   Aaron
 * @Last Modified Time: 2020-03-30 16:17:33
 */
 // ------------------------------------------------------------------------
namespace app\system\validate;

use think\Validate;

class LoginValidate extends Validate
{
    protected $rule = [
        'username' => 'require',
        'password' => 'require',
        'vercode'  => 'require|captcha',
    ];
    
    protected $message = [
        'username.require' => '用户名不能为空',
        'password.require' => '密码不能为空',
        'vercode.require'  => '验证码不能为空',
        'vercode.captcha'  => '验证码不正确'
    ];
}
	