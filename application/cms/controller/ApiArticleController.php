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
 * @Last Modified Time: 2020-03-30 09:37:40
 */
 // ------------------------------------------------------------------------

namespace app\cms\controller;

use app\cms\model\ArticleModel;
use app\common\controller\ApiBaseController;

class ApiArticleController extends ApiBaseController
{
	protected $whiteList = '*';

	/**
     * 文章详情
     */
	public function detail($id)
	{  
		$article = ArticleModel::field('id,category_id,title,author,clicks,content,is_recommend,is_top,published_time')
            ->where('id', $id)
			->where('is_publish', 1)
			->find($id);

        $this->sendSuccess($article);
    }
}