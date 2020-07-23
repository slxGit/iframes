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
 * @Last Modified Time: 2020-02-13 10:03:26
 */
 // ------------------------------------------------------------------------

namespace app\system\model;

use think\model\Pivot;

class RoleManagerModel extends Pivot
{
	protected $name = 'sys_role_manager';
}