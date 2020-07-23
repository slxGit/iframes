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
 * @Last Modified Time: 2020-03-07 19:03:02
 */
 // ------------------------------------------------------------------------
namespace app\cms\validate;

use think\Validate;

class PageValidate extends Validate
{
    protected $rule = [
        'title' => 'require',
    ];

    protected $message = [
        'title.require' => '标题不能为空',
    ];
}
	