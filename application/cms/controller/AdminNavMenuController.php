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
 * @Last Modified Time: 2020-04-03 21:42:30
 */
 // ------------------------------------------------------------------------

namespace app\cms\controller;

use utils\Tree;
use app\common\builder\Builder;
use app\cms\model\PageModel;
use app\cms\model\CategoryModel;
use app\cms\model\NavMenuModel;
use app\cms\validate\NavMenuValidate;
use app\common\controller\AdminBaseController;

class AdminNavMenuController extends AdminBaseController
{
    // 类型
    protected $type = [
        1 => '分类链接',
        2 => '单页链接',
        3 => '自定义链接'
    ];

    // 打开方式
    protected $target = [
        '_self'  => '当前窗口',
        '_blank' => '新窗口'
    ];

	/**
     * 导航菜单列表
     */
	public function index()
	{  
        $navId = $this->request->param('nav_id', 0, 'intval');
        
        $navMenuList = NavMenuModel::where('nav_id', $navId)->order('order')->select()->each(function($item){
            $item->type = $this->type[$item->type];
        });
        
        $navMenuList = Tree::config(['name' => 'title'])->toList($navMenuList, 2);

        return Builder::table()
            ->addColumns([
                ['id', 'ID'],
                ['icon_title', '标题'],
                ['type', '类型'],
                ['target', '打开方式', 'select', $this->target],
                ['order', '排序', 'text'],
                ['status', '状态', 'switch']
            ])
            ->addTopButton('back', [
                'method' => 'href',
                'url'    => url('admin_nav/index')
            ])
            ->addTopButton('add', [
                'url' => url('add', ['nav_id' => $navId])
            ])
            ->addTopButton('delete')
            ->addRightButton('nav_menu', [
                'title' => '添加子菜单',
                'url'   => url('add', ['nav_id' => $navId, 'parent_id' => '__id__']),
                'class' => 'layui-btn layui-btn-warm layui-btn-xs'
            ])
            ->addRightButtons('edit,delete')
            ->setRightToolbar(['width' => '20%'])
            ->setTableData($navMenuList)
            ->fetch();
	}

    /**
     * 导航菜单添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();

            $validate = new NavMenuValidate;
            if (!$validate->check($params)) {
                $this->error($validate->getError());
            }

            $result = NavMenuModel::create($params);
            
            if ($result) {
                $this->success('添加成功', url('index', ['nav_id' => $params['nav_id']]));
            } else {
                $this->error('添加失败');
            }
        } else {

            return Builder::form()
                ->setPageTitle('导航菜单添加')
                ->addFormItems([
                    ['hidden', 'nav_id', $this->request->param('nav_id', 0)],
                    ['hidden', 'parent_id', $this->request->param('parent_id', 0)],
                    ['text', 'title', '标题'],
                    ['radio', 'type', '类型', '', $this->type, 1],
                    ['select', 'category_id', '分类', '', CategoryModel::column('id,name')],
                    ['select', 'page_id', '单页', '', PageModel::column('id,title')],
                    ['text', 'url', '链接'],
                    ['radio', 'target', '打开方式', '', $this->target, '_self'],
                    ['number', 'order', '排序', '', 99, 0, 99, 0],
                    ['switch', 'status', '状态']
                ])
                ->setTrigger([
                    ['type', 1, 'category_id'],
                    ['type', 2, 'page_id'],
                    ['type', 3, 'url']
                ])
                ->fetch();
        }   
    }

    /**
     * 导航菜单编辑
     */
    public function edit()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();

            $validate = new NavMenuValidate;
            if (!$validate->check($params)) {
                $this->error($validate->getError());
            }

            $result = NavMenuModel::where('id', $params['id'])->update($params);
            
            if ($result !== false) {
                $this->success('保存成功', url('index', ['nav_id' => $params['nav_id']]));
            } else {
                $this->error('保存失败');
            }
        } else {
            $id = $this->request->param('id', 0, 'intval');
            $navMenu = NavMenuModel::get($id);
            
            return Builder::form()
                ->setPageTitle('导航菜单编辑')
                ->addFormItems([
                    ['hidden', 'id'],
                    ['hidden', 'nav_id'],
                    ['hidden', 'parent_id'],
                    ['text', 'title', '标题'],
                    ['radio', 'type', '类型', '', $this->type],
                    ['select', 'category_id', '分类', '', CategoryModel::column('id,name')],
                    ['select', 'page_id', '单页', '', PageModel::column('id,title')],
                    ['text', 'url', '链接'],
                    ['radio', 'target', '打开方式', '', $this->target],
                    ['number', 'order', '排序', '', 99, 0, 99, 0],
                    ['switch', 'status', '状态']
                ])
                ->setFormData($navMenu)
                ->setTrigger([
                    ['type', 1, 'category_id'],
                    ['type', 2, 'page_id'],
                    ['type', 3, 'url']
                ])
                ->fetch();
        }   
    }
}