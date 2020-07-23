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
 * @Last Modified Time: 2020-03-30 15:42:18
 */
 // ------------------------------------------------------------------------

namespace app\cms\controller;

use app\common\builder\Builder;
use app\cms\model\PageModel;
use app\cms\validate\PageValidate;
use app\common\controller\AdminBaseController;

class AdminPageController extends AdminBaseController
{
	/**
     * 页面列表
     */
	public function index()
	{  
        $params = $this->request->param();

        // 查询条件
        $map = [];

        // 标题(查询条件)
        if (!empty($params['title'])) {
            $map[] = ['title', 'like', '%' . $params['title'] . '%'];
        }

        // 获取页面列表
        $pageList = PageModel::where($map)
            ->field('id,title,is_publish,published_time')
            ->order('updated', 'desc')
            ->paginate()->each(function($item, $key){
                $item->title = [url('cms/page/detail',['id'=>$item->id]), $item->title];
            });

        return Builder::table()
            ->addColumns([
                ['id', 'ID'],
                ['title', '标题', 'link', '', '', ['width' => '40%']],
                ['is_publish', '发布状态', 'switch'],
                ['published_time', '发布时间']
            ])
            ->addSearchItem('text', 'title', '标题')
            ->addTopButtons('add,delete')
            ->addRightButtons('edit,delete')
            ->setTableData($pageList)
            ->fetch();
	}

    /**
     * 页面添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();

            $validate = new PageValidate;
            if (!$validate->check($params)) {
                $this->error($validate->getError());
            }

            // 发布状态
            if (isset($params['is_publish']) && $params['is_publish'] == 1) {
                $params['published_time'] = time();
            } else {
                $params['is_publish'] = 0;
            }

            $result = PageModel::create($params);
   
            if ($result) {
                $this->success('添加成功', url('index'));
            } else {
                $this->error('添加失败');
            }
        } else {
            return Builder::form()
                ->setPageTitle('页面添加')
                ->addFormItems([
                    ['text', 'title', '标题'],
                    ['tags', 'keywords', '关键字'],
                    ['image', 'thumbnail', '缩略图'],
                    ['switch', 'is_publish', '发布状态'],
                    ['textarea', 'excerpt', '内容摘要'],
                    ['ueditor', 'content', '内容']
                ])
                ->fetch();
        }
    }

    /**
     * 页面编辑
     */
    public function edit()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();

            $validate = new PageValidate;
            if (!$validate->check($params)) {
                $this->error($validate->getError());
            }

            // 发布状态
            if (isset($params['is_publish']) && $params['is_publish'] == 1) {
                $dbIsPublish = PageModel::where('id', $params['id'])->value('is_publish');
                if ($dbIsPublish == 0) $params['published_time'] = time();
            } else {
                $params['is_publish'] = 0;
            }
   
            $pageModel = new PageModel;
            $result = $pageModel->save($params, ['id' => $params['id']]);

            if ($result) {
                $this->success('保存成功', url('index'));
            } else {
                $this->error('保存失败');
            }
        } else {
            $id = $this->request->param('id', 0, 'intval');

            $page = PageModel::get($id);

            return Builder::form()
                ->setPageTitle('页面编辑')
                ->addFormItems([
                    ['hidden', 'id'],
                    ['text', 'title', '标题'],
                    ['tags', 'keywords', '关键字'],
                    ['image', 'thumbnail', '缩略图'],
                    ['switch', 'is_publish', '发布状态'],
                    ['textarea', 'excerpt', '内容摘要'],
                    ['ueditor', 'content', '内容']
                ])
                ->setFormData($page)
                ->fetch();
        }     
    }

    /**
     * 快捷编辑
     */
    public function quickEdit()
    {
        $params = $this->request->param();

        if ($params['name'] == 'is_publish' && $params['value'] == 1) {
            $result = PageModel::where('id', $params['id'])
                ->update(['published_time' => time(), $params['name'] => $params['value']]);
        } else {
            $result = PageModel::where('id', $params['id'])->update([$params['name'] => $params['value']]);
        }

        if ($result !== false) {
            $this->success('保存成功');
        } else {
            $this->error('保存失败');
        }
    }
}