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
 * @Last Modified Time: 2020-03-09 21:07:40
 */
 // ------------------------------------------------------------------------

namespace app\cms\controller;

use app\cms\model\ArticleModel;
use app\common\controller\CmsBaseController;

class CategoryController extends CmsBaseController
{
    /**
     * 初始化
     */
    protected function initialize()
    {
        parent::initialize();
        $this->getReadRanking();
        $this->getLatestArticles();
        $this->getLinks();
    }
	
	/**
     * 分类文章列表
     */
	public function index()
	{  
		$id = $this->request->param('id', 0, 'intval');
		$recommend = $this->request->param('recommend', '');

		$where = [['is_publish', '=', 1]];

		// 文章分类下的导航栏状态 1：综合 2：推荐
		$navStatus = 1;

		if (!empty($id)) {
			$where[] = ['category_id', '=', $id];
		}

		if ($recommend == 1) {
			$where[] = ['is_recommend', '=', 1];
			$navStatus = 2;
		}

		$articleList = ArticleModel::field('id,category_id,title,author,thumbnail,clicks,is_recommend,is_top,published_time')
			->where($where)
			->order('is_top', 'desc')
			->order('published_time', 'desc')
			->paginate();

		// 综合的链接地址
		$complexUrl = empty($id) ? url('index') : url('index', ['id' => $id]);

		// 推荐的链接地址
		$recommendUrl = empty($id) ? url('index', ['recommend' => 1]) : url('index', ['id' => $id, 'recommend' => 1]);

		$this->assign([
			'nav_status'    => $navStatus,
			'complex_url'   => $complexUrl,
			'recommend_url' => $recommendUrl,
			'article_list'  => $articleList
		]);

        return $this->fetch();
    }

}