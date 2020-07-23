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
 * @Last Modified Time: 2020-02-11 13:33:49
 */
 // ------------------------------------------------------------------------

namespace app\system\model;

use app\common\model\BaseModel;

class MenuModel extends BaseModel
{
	protected $name = 'sys_menu';

	public function setModuleAttr($value)
    {
        return strtolower($value);
    }

    public function setControllerAttr($value)
    {
        return strtolower($value);
    }

    public function setActionAttr($value)
    {
        return strtolower($value);
    }
}