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
 * @Last Modified Time: 2020-03-07 19:02:03
 */
 // ------------------------------------------------------------------------

namespace app\cms\model;

use app\common\model\BaseModel;

class PageModel extends BaseModel
{
	protected $name = 'cms_page';

    public function getPublishedTimeAttr($value)
    {
    	return $value ? date('Y-m-d H:i', $value) : '';
    }
}