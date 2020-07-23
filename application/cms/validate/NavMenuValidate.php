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
 * @Last Modified Time: 2020-03-08 12:25:11
 */
 // ------------------------------------------------------------------------
namespace app\cms\validate;

use think\Validate;

class NavMenuValidate extends Validate
{
    protected $rule = [
        'title' => 'require',
        'type'  => 'require|checkType'
    ];

    protected $message = [
        'title.require' => '标题不能为空',
        'type.require'  => '请选择类型'
    ];

    protected function checkType($value, $rule, $data = [])
    {
		switch ($value) {
			case 1:
				if (empty($data['category_id'])) {
					return '请选择分类';
				}
				break;

			case 2:
				if (empty($data['page_id'])) {
					return '请选择单页';
				}
				break;
			
			case 3:
				if (empty($data['url'])) {
					return '链接不能为空';
				}
				break;

			default:
				return '请选择类型';
				break;
		}
		
		return true;
    }

}
	