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
 * @Last Modified Time: 2020-03-06 14:47:17
 */
 // ------------------------------------------------------------------------

namespace app\api\model;

use app\common\model\BaseModel;

class ErrorLogModel extends BaseModel
{
	protected $name = 'api_error_log';

    public function getCreatedAttr($value)
    {
		return date('Y-m-d H:i', $value);
    }
}