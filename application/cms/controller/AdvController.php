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
 * @Last Modified Time: 2020-03-09 14:39:35
 */
 // ------------------------------------------------------------------------

namespace app\cms\controller;

use app\cms\model\PageModel;
use app\common\controller\CmsBaseController;

class AdvController extends CmsBaseController
{
	/**
     * 单页详情
     */
	public function detail($id)
	{  
		$page = PageModel::field('id,content')
			->where('id', $id)
			->where('is_publish', 1)
			->find($id);
		
		$this->assign('page', $page);

        return $this->fetch();
    }

}