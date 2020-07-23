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
 * @Last Modified Time: 2020-03-07 20:09:13
 */
 // ------------------------------------------------------------------------
namespace app\cms\validate;

use think\Validate;

class NavValidate extends Validate
{
    protected $rule = [
        'title' => 'require',
        'mark'  => 'require|unique:cms_nav'
    ];

    protected $message = [
        'title.require' => '标题不能为空',
        'mark.require'  => '标识不能为空',
        'mark.unique'   => '标识已存在'
    ];

}
	