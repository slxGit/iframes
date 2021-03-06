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
 * @Last Modified Time: 2020-03-07 15:48:30
 */
 // ------------------------------------------------------------------------
namespace app\adv\validate;

use think\Validate;

class PositionByIdValidate extends Validate
{
    protected $rule = [
        'position_id'  => 'require',
        'adv_platform' => 'require'
    ];

    protected $message = [
        'position_id.require'  => 'ID不能為空',
        'adv_platform.require' => '投放平台ID不能为空'
    ];

}
	