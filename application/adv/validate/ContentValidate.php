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

class ContentValidate extends Validate
{
    protected $rule = [
        'position_id'  => 'require',
        'title'        => 'require',
        'starttime'    => 'require',
        'endtime'      => 'require'
    ];

    protected $message = [
        'category_id.require' => '请选择位置',
        'title.require'       => '标题不能为空',
        'starttime.require'   => '开始时间不能为空',
        'endtime.require'     => '结束时间不能为空'
    ];

}
	