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
 * @Last Modified Time: 2020-03-07 15:52:06
 */
 // ------------------------------------------------------------------------
namespace app\cms\validate;

use think\Validate;

class TagValidate extends Validate
{
    protected $rule = [
        'name' => 'require|unique:cms_tag',
    ];

    protected $message = [
        'name.require' => '名称不能为空',
        'name.unique'  => '名称已存在'
    ];
}
	