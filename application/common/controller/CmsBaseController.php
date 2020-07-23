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
 * @Date:       2020-02-10 12:31:48
 *
 * @Last Modified By:   Aaron
 * @Last Modified Time: 2020-04-03 21:44:39
 */
 // ------------------------------------------------------------------------

namespace app\common\controller;

use utils\Tree;
use app\cms\model\NavModel;
use app\cms\model\NavMenuModel;
use app\common\controller\PortalBaseController;

class CmsBaseController extends PortalBaseController
{
    /**
     * 初始化
     */
    protected function initialize()
    {
        parent::initialize();
        $this->getNavs();
    }

    /**
     * 获取阅读排行
     */
    protected function getReadRanking()
    {
        $readRanking = \app\cms\model\ArticleModel::where('is_publish', 1)
            ->field('id, title, clicks')
            ->order('clicks', 'desc')
            ->limit(10)
            ->select()
            ->toArray();

        $this->assign('read_ranking', $readRanking);
    }

    /**
     * 获取最新文章
     */
    protected function getLatestArticles()
    {
        $latestArticles = \app\cms\model\ArticleModel::where('is_publish', 1)
            ->field('id, title, clicks')
            ->order('published_time', 'desc')
            ->limit(10)
            ->select()
            ->toArray();

        $this->assign('latest_articles', $latestArticles);
    }

    /**
     * 获取友情链接
     */
    protected function getLinks()
    {
        $linkList = \app\cms\model\LinkModel::where('status', 1)
            ->field('title,url,logo,target')->order('order', 'asc')->select()->toArray();

        $this->assign('link_list', $linkList);
    }

    /**
     * 获取导航
     */
    private function getNavs()
    {
        $navList = NavModel::where('status', 1)->column('id,mark');

        foreach ($navList as $id => $mark) {
            $navMenu = NavMenuModel::where('nav_id', $id)
                ->field('id,parent_id,title,type,category_id,page_id,url,target')
                ->where('status', 1)
                ->order('order', 'asc')
                ->select()
                ->each(function($item){
                    switch ($item->type) {
                        case 1:
                            $item->url = url('cms/category/index', ['id' => $item->category_id]);
                            break;
                        
                        case 2:
                            $item->url = url('cms/page/detail', ['id' => $item->page_id]);
                            break;
                    }
                });

            $navMenu = Tree::config(['name' => 'title'])->toLayer($navMenu, 0, function($item){
                $item = array_filter($item, function($value, $name){
                    if (in_array($name, ['title', 'url', 'target', 'children'])) return $value ?: ' ';
                }, ARRAY_FILTER_USE_BOTH);
                return $item;
            });

            $this->assign($mark, $navMenu);
        }
    }

    public function getAdv(){
//        $adv_position
    }
}