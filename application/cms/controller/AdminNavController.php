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
 * @Last Modified Time: 2020-03-22 22:54:40
 */
 // ------------------------------------------------------------------------

namespace app\cms\controller;

use app\common\builder\Builder;
use app\cms\model\NavModel;
use app\cms\validate\NavValidate;
use app\common\controller\AdminBaseController;

class AdminNavController extends AdminBaseController
{
	/**
     * 导航列表
     */
	public function index()
	{  
        $navList = NavModel::select();

        return Builder::table()
            ->addColumns([
                ['id', 'ID'],
                ['title', '标题', 'text'],
                ['mark', '标识', 'text'],
                ['status', '状态', 'switch']
            ])
            ->addTopButtons('add, delete')
            ->addRightButton('nav_menu', [
                'title' => '菜单管理',
                'url'   => url('admin_nav_menu/index', ['nav_id' => '__id__']),
                'class' => 'layui-btn layui-btn-warm layui-btn-xs'
            ])
            ->addRightButtons('edit,delete')
            ->setRightToolbar(['width' => '20%'])
            ->setTableData($navList)
            ->fetch();
	}

    /**
     * 导航添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();

            $validate = new NavValidate;
            if (!$validate->check($params)) {
                $this->error($validate->getError());
            }

            if (!isset($params['status']) || $params['status'] != 1) {
                $params['status'] = 0;
            }

            $result = NavModel::create($params);
            
            if ($result !== false) {
                $this->success('保存成功', url('index'));
            } else {
                $this->error('保存失败');
            }
        } else {

            return Builder::form()
                ->setPageTitle('导航编辑')
                ->addFormItems([
                    ['text', 'title', '标题'],
                    ['text', 'mark', '标识'],
                    ['switch', 'status', '状态']
                ])
                ->fetch();
        }   
    }

    /**
     * 导航编辑
     */
    public function edit()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();

            $validate = new NavValidate;
            if (!$validate->check($params)) {
                $this->error($validate->getError());
            }

            if (!isset($params['status']) || $params['status'] != 1) {
                $params['status'] = 0;
            }

            $result = NavModel::where('id', $params['id'])->update($params);
            
            if ($result !== false) {
                $this->success('保存成功', url('index'));
            } else {
                $this->error('保存失败');
            }
        } else {
            $id = $this->request->param('id', 0, 'intval');

            $nav = NavModel::get($id);

            return Builder::form()
                ->setPageTitle('导航编辑')
                ->addFormItems([
                    ['hidden', 'id'],
                    ['text', 'title', '标题'],
                    ['text', 'mark', '标识'],
                    ['switch', 'status', '状态']
                ])
                ->setFormData($nav)
                ->fetch();
        }   
    }

    /**
     * 导航删除
     */
    public function delete()
    {
        $ids = $this->request->param('ids');

        $ids = is_array($ids) ? $ids : [$ids];

        foreach ($ids as $id) {
            $nav = NavModel::get($id, 'navmenus');
            $nav->together('navmenus')->delete();
        }

        $this->success('删除成功');
    }
}