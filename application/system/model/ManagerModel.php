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
 * @Date:       2020-02-10 19:41:57
 *
 * @Last Modified By:   Aaron
 * @Last Modified Time: 2020-02-13 11:45:07
 */
 // ------------------------------------------------------------------------

namespace app\system\model;

use app\common\model\BaseModel;

class ManagerModel extends BaseModel
{
	protected $name = 'sys_manager';

    public function roles()
    {
        return $this->belongsToMany('RoleModel', 'sys_role_manager', 'role_id', 'manager_id');
    }

	public function getLastLoginTimeAttr($value)
    {
        return empty($value) ? '' : date('Y-m-d H:i', $value);
    }
}