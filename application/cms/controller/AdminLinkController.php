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
 * @Last Modified Time: 2020-03-28 23:43:53
 */
 // ------------------------------------------------------------------------

namespace app\cms\controller;

use app\common\builder\Builder;
use app\cms\model\LinkModel;
use app\cms\validate\LinkValidate;
use app\common\controller\AdminBaseController;

class AdminLinkController extends AdminBaseController
{
    // 打开方式
    protected $target = [
        '_self'  => '当前窗口',
        '_blank' => '新窗口'
    ];

	/**
     * 友情链接列表
     */
	public function index()
	{  
        $linkList = LinkModel::field('id,title,url,order,status')->order('order', 'asc')->select();

        return Builder::table()
            ->addColumns([
                ['id', 'ID'],
                ['title', '标题', 'text'],
                ['url', '链接地址', 'link'],
                ['order', '排序', 'text'],
                ['status', '状态', 'switch']
            ])
            ->addTopButtons('add,delete')
            ->addRightButtons('edit,delete')
            ->setTableData($linkList)
            ->fetch();
	}

    /**
     * 友情链接添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();

            $validate = new LinkValidate;
            if (!$validate->check($params)) {
                $this->error($validate->getError());
            }

            $result = LinkModel::create($params);
            
            if ($result) {
                $this->success('添加成功', url('index'));
            } else {
                $this->error('添加失败');
            }
        } else {
            return Builder::form()
                ->setPageTitle('友情链接添加')
                ->addFormItems([
                    ['text', 'title', '标题'],
                    ['text', 'url', '链接地址'],
                    ['select', 'target', '打开方式', '', $this->target, '_blank'],
                    ['image', 'logo', 'logo'],
                    ['textarea', 'description', '描述'],
                    ['number', 'order', '排序', '', 99, 0, 99, 0],
                    ['switch', 'status', '状态', '', 1]
                ])
                ->fetch();
        }
    }

    /**
     * 友情链接编辑
     */
    public function edit()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();

            $validate = new LinkValidate;
            if (!$validate->check($params)) {
                $this->error($validate->getError());
            }

            $linkModel = new LinkModel;
            $result = $linkModel->save($params, ['id' => $params['id']]);
            
            if ($result) {
                $this->success('保存成功', url('index'));
            } else {
                $this->error('保存失败');
            }
        } else {
            $id = $this->request->param('id', 0, 'intval');

            $link = LinkModel::get($id);

            return Builder::form()
                ->setPageTitle('友情链接编辑')
                ->addFormItems([
                    ['hidden', 'id'],
                    ['text', 'title', '标题'],
                    ['text', 'url', '链接地址'],
                    ['select', 'target', '打开方式', '', $this->target, '_blank'],
                    ['image', 'logo', 'logo'],
                    ['textarea', 'description', '描述'],
                    ['number', 'order', '排序', '', 99, 0, 99, 0],
                    ['switch', 'status', '状态', '', 1]
                ])
                ->setFormData($link)
                ->fetch();
        } 
    }
}