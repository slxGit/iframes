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
 * @Date:       2020-02-11 19:14:11
 *
 * @Last Modified By:   Aaron
 * @Last Modified Time: 2020-03-06 19:05:35
 */
 // ------------------------------------------------------------------------

namespace app\cms\model;

use app\common\model\BaseModel;

class CategoryModel extends BaseModel
{
	protected $name = 'cms_category';

	public function getCreatedAttr($value)
    {
    	return date('Y-m-d H:i', $value);
    }
}