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
 * @Last Modified Time: 2020-04-05 18:36:16
 */
 // ------------------------------------------------------------------------
namespace app\system\validate;

use think\Validate;

class ConfigValidate extends Validate
{
    protected $rule = [
        'group|配置分组' => 'require',
        'type|配置类型'  => 'require',
        'name|配置名称'  => 'require|regex:^[a-zA-Z]\w{0,39}$|unique:sys_config',
        'title|配置标题' => 'require',
    ];

    protected $message = [
        'name.regex' => '配置名称由字母和下划线组成',
    ];
}
	