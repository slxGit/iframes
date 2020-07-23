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
 * @Last Modified Time: 2020-03-30 16:19:43
 */
 // ------------------------------------------------------------------------
namespace app\system\validate;

use think\Validate;

class HookValidate extends Validate
{
    protected $rule = [
        'name|钩子名称' => 'require|regex:^[a-zA-Z]\w{0,39}$|unique:sys_hook'
    ];

    protected $message = [
        'name.regex' => '钩子名称格式不正确',
    ];
}
	