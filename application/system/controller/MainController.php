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
 * @Date:       2020-02-10 13:02:08
 *
 * @Last Modified By:   Aaron
 * @Last Modified Time: 2020-04-04 13:58:31
 */
 // ------------------------------------------------------------------------

namespace app\system\controller;

use app\common\controller\AdminBaseController;

class MainController extends AdminBaseController
{
	/**
     * 后台首页界面
     */
	public function index()
	{
		return $this->fetch();
	}
}