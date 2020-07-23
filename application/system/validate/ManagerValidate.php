<?php

/**
 * KirinBDF
 *
 * A PHP Basic Development Framework, Based on ThinkPHP 5.1 LTS.
 *
 * @System:     KirinBDF
 * @Link:       http://www.kirinbdf.com/
 * @Copyright:  Copyright (c) 2017-2019  KirinBDF Team.
 *
 * @Author:     Aaron
 * @Date:       2020-02-11 13:37:18
 *
 * @Last Modified By:   Aaron
 * @Last Modified Time: 2020-02-18 15:05:46
 */
 // ------------------------------------------------------------------------

namespace app\system\validate;

use think\Validate;

class ManagerValidate extends Validate
{
    protected $rule = [
        'username' => 'require|unique:sys_manager',
        'password' => 'length:6,20',
        'mobile'   => 'mobile',
    ];
    protected $message = [
        'username.require'   => '用户名不能为空',
        'username.unique'    => '用户名已存在',
        'password.require'   => '密码不能为空',
        'password.length'    => '密码长度6-20位',
        'mobile.mobile'      => '手机号不正确'
    ];

    protected $scene = [
        'edit' => ['password', 'mobile']
    ];

    // add 验证场景
    public function sceneAdd()
    {
        return $this->only(['username', 'password', 'mobile'])->append('password', 'require');
    }    
}