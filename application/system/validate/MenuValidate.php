<?php

/**
 * KirinBDF
 *
 * A PHP Basic Development Framework, Based on ThinkPHP 5.1 LTS.
 *
 * @System:     KirinBDF
 * @Link:       http://www.kirinbdf.com/
 * @Copyright:  Copyright (c) 2017-2019  KirinBDF Team.
 *
 * @Author:     Aaron
 * @Date:       2020-02-11 13:37:18
 *
 * @Last Modified By:   Aaron
 * @Last Modified Time: 2020-03-06 18:58:00
 */
 // ------------------------------------------------------------------------

namespace app\system\validate;

use think\Validate;

class MenuValidate extends Validate
{
	protected $rule = [
        'parent_id'  => 'checkParentId',
	    'name'       => 'require',
	    'module'     => 'require',
	    'controller' => 'require',
	    'action'     => 'require|unique:sys_menu,module^controller^action',
	];

	protected $message = [
        'parent_id.checkParentId' => '超过了4级',
	    'name.require'       => '名称不能为空',
	    'module.require'     => '应用不能为空',
	    'controller.require' => '控制器不能为空',
	    'action.require'     => '方法不能为空',
	    'action.unique'      => '路由地址已经存在!',
	];

    protected function checkParentId($value)
    {
    	// 查询父ID的次数
    	static $num = 0; 
        $parentId = db('sys_menu')->where('id', $value)->value('parent_id');
        $num += 1;
        // 超过4级的触发条件
        if ($parentId != 0 && $num >= 3) {
        	return false;
        }
        // 检验通过的触发条件
        if ($parentId == 0 && $num <= 3) {
        	return true;
        }
        return $this->checkParentId($parentId);
    }
}