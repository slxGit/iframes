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
 * @Last Modified Time: 2020-03-23 14:03:40
 */
 // ------------------------------------------------------------------------
namespace app\cms\validate;

use think\Validate;

class CategoryValidate extends Validate
{
    protected $rule = [
        'parent_id'  => 'checkParentId',
        'name'       => 'require'
    ];

    protected $message = [
        'parent_id.checkParentId' => '超过了2级',
        'name.require'            => '名称不能为空'
    ];

    protected function checkParentId($value)
    {
        // 查询父ID的次数
        static $num = 0; 
        $parentId = db('cms_category')->where('id', $value)->value('parent_id');
        $num += 1;
        // 超过2级的触发条件
        if ($parentId != 0 && $num >= 1) {
            return false;
        }
        // 检验通过的触发条件
        if ($parentId == 0 && $num <= 1) {
            return true;
        }
        return $this->checkParentId($parentId);
    }
}
	