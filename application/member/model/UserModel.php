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
 * @Last Modified Time: 2020-02-15 09:40:41
 */
 // ------------------------------------------------------------------------

namespace app\member\model;

use app\common\model\BaseModel;

class UserModel extends BaseModel
{
	protected $name = 'mbr_user';

	public function register($info)
	{
		return $this->isUpdate(false)->allowField(true)->save($info);
	}

	public function afterLogin($id)
	{
		return $this->save([
			'last_login_ip'   => request()->ip(0, true),
			'last_login_time' => time()
		], ['id' => $id]);
	}
}