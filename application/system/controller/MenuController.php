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
 * @Last Modified Time: 2020-04-14 10:35:55
 */
 // ------------------------------------------------------------------------

namespace app\system\controller;

use utils\Tree;
use app\common\builder\Builder;
use app\system\model\MenuModel;
use app\system\model\AuthRuleModel;
use app\system\validate\MenuValidate;
use app\common\controller\AdminBaseController;

class MenuController extends AdminBaseController
{
	/**
     * 菜单列表
     */
	public function index()
	{
		$menuList = MenuModel::field('id,parent_id,name,module,controller,action,icon')
			->order('order', 'asc')->select();
		
		$trees = Tree::toLayer($menuList, 4, function ($menu) {
			$menu['spread'] = true;
			$menu['title'] = '<i class="layui-icon ' . $menu['icon'] . '"></i> <b>' . $menu['name'] . '</b> &nbsp&nbsp<i class="layui-icon layui-icon-link"></i> ' . $menu['module'] . '/' . $menu['controller'] . '/' . $menu['action'];
			return $menu;
		});
		
		$this->assign('trees', json_encode($trees));

		return $this->fetch();
	}

	/**
     * 菜单添加
     */
    public function add()
    {
    	if ($this->request->isPost()) {
    		$params = $this->request->post();

    		$validate = new MenuValidate;
	        if (!$validate->check($params)) {
	            $this->error($validate->getError());
	        }

	        $menuModel = new MenuModel;
	        $result = $menuModel->save($params);
	        if ($result) {
	        	// 查询该路由是否存在于权限规则表中
	        	$name = $params['module'] . '/' . $params['controller'] . '/' . $params['action'];
	        	$hasAuthRule = AuthRuleModel::where('name', $name)->count();
	            if (!$hasAuthRule) {
	                $authRuleData = [
	                    'name'  => $name,
	                    'title' => $params['name'],
	                ];
	                $authRuleModel = new AuthRuleModel;
	                $authRuleModel->save($authRuleData);
	            }
	        	$this->success('添加成功', url('index'));
	        } else {
	        	$this->error('添加失败');
	        }

    	} else {
			$menuList = MenuModel::field('id,parent_id,name')->order('order', 'asc')->select();

            $menus = Tree::toLayer($menuList, 3, function($item){
                $item['value'] = $item['id'];
                unset($item['icon_name']);
                unset($item['parent_id']);
                unset($item['level']);
                unset($item['extremity']);
                unset($item['id']);
                return $item;
            });

            array_unshift($menus, ['name' => '作为一级分类', 'value' => 0]);

			return Builder::form()
				->setPageTitle('菜单添加')
				->addFormItems([
                    ['xmselect', 'parent_id', '上级', '', $menus, $this->request->param('id') ?: 0, '', ['tree' => ['show' => true, 'strict' => false, 'expandedKeys' => true], 'radio' => true, 'clickClose' => true, 'height' => 'auto', 'model' => ['label' => ['type' => 'text']]]],
					['text', 'name', '名称'],
					['text', 'module', '应用'],
					['text', 'controller', '控制器'],
					['text', 'action', '方法'],
					['text', 'param', '参数', '如：a=1&b=2'],
					['radio', 'status', '状态', '', [1 => '显示', 0 => '隐藏'], 1],
					['icon', 'icon', '图标'],
					['number', 'order', '排序', '', 99, '', 0, 99, 0]
				])
				->fetch();
    	}
    }

	/**
     * 菜单编辑
     */
    public function edit()
    {
    	if ($this->request->isPost()) {
    		$params = $this->request->post();

    		$validate = new MenuValidate;
	        if (!$validate->check($params)) {
	            $this->error($validate->getError());
	        }

	        $menuModel = new MenuModel;
	        $beforeMenu = $menuModel->get($params['id']);
	        $result = $menuModel->save($params, ['id' => $params['id']]);
	        if ($result) {
	        	// 查询该路由是否存在于权限规则表中
	        	$name = $params['module'] . '/' . $params['controller'] . '/' . $params['action'];
	        	$hasAuthRule = AuthRuleModel::where('name', $name)->count();
	        	if (!$hasAuthRule) {
	        		// 查询该菜单修改前的路由是否存在于权限规则表中
	        		$beforeName = $beforeMenu['module'] . '/' . $beforeMenu['controller'] . '/' . $beforeMenu['action'];
	        		$beforeHasAuthRule = AuthRuleModel::where('name', $beforeName)->count();
	        		if ($beforeHasAuthRule) {
	        			$authRuleModel = new AuthRuleModel;
	        			$authRuleModel->save(['title' => $params['name'], 'name' => $name], ['name' => $beforeName]);
	                } else {
	                	$authRuleModel = new AuthRuleModel;
	                	$authRuleModel->save(['title' => $params['name'], 'name' => $name]);
	                }
	        	} else {
	        		$authRuleModel = new AuthRuleModel;
	        		$authRuleModel->save(['title' => $params['name']], ['name' => $name]);
	        	}
	        	$this->success('保存成功', url('index'));
	        } else {
	        	$this->error('保存失败');
	        }
    	} else {
	    	$id = $this->request->param('id', 0, 'intval');

	    	$menu = MenuModel::get($id);

			$menuList = MenuModel::field('id,parent_id,name')->order('order', 'asc')->select();

            $menus = Tree::toLayer($menuList, 3, function($item){
                $item['value'] = $item['id'];
                unset($item['icon_name']);
                unset($item['parent_id']);
                unset($item['level']);
                unset($item['extremity']);
                unset($item['id']);
                return $item;
            });

			array_unshift($menus, ['name' => '作为一级分类', 'value' => 0]);
	
			return Builder::form()
				->setPageTitle('菜单编辑')
				->addFormItems([
					['hidden', 'id'],
                    ['xmselect', 'parent_id', '上级', '', $menus, '', '', ['tree' => ['show' => true, 'strict' => false, 'expandedKeys' => true], 'radio' => true, 'clickClose' => true, 'height' => 'auto', 'model' => ['label' => ['type' => 'text']]]],
					['text', 'name', '名称'],
					['text', 'module', '应用'],
					['text', 'controller', '控制器'],
					['text', 'action', '方法'],
					['text', 'param', '参数', '如：a=1&b=2'],
					['radio', 'status', '状态', '', [1 => '显示', 0 => '隐藏']],
					['icon', 'icon', '图标'],
					['number', 'order', '排序', '', 99, '', 0, 99, 0]
				])
				->setFormData($menu)
				->fetch();    		
    	}
    }

    /**
     * 菜单删除
     */
    public function delete()
    {
    	$id = $this->request->param('id', 0, 'intval');
        $childrenCount = MenuModel::where('parent_id', $id)->count();

        if ($childrenCount) {
            $this->error("该菜单下存在子菜单，无法删除！");
        }

        $menu = MenuModel::field('module, controller, action')->get($id);
        $result = MenuModel::destroy($id);

        if ($result) {
        	$name = $menu['module'] . '/' . $menu['controller'] . '/' . $menu['action'];
        	AuthRuleModel::destroy(function($query) use ($name){
			    $query->where('name', $name);
			});
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
}