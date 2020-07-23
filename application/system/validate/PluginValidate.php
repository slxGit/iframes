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
 * @Last Modified Time: 2020-03-31 17:48:22
 */
 // ------------------------------------------------------------------------
namespace app\system\validate;

use think\Validate;

class PluginValidate extends Validate
{
    protected $rule = [
        'name|插件名称'    => 'require|unique:sys_plugin',
        'title|插件标题'   => 'require',
        'author|插件作者'  => 'require',
        'version|插件版本' => 'require'
    ];
}
	