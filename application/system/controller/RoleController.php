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
 * @Last Modified Time: 2020-05-25 09:54:27
 */
 // ------------------------------------------------------------------------

namespace app\system\controller;

use utils\Tree;
use app\common\builder\Builder;
use app\system\model\MenuModel;
use app\system\model\RoleModel;
use app\system\model\AuthAccessModel;
use app\system\model\RoleManagerModel;
use app\system\validate\RoleValidate;
use app\common\controller\AdminBaseController;

class RoleController extends AdminBaseController
{
	/**
     * 角色列表
     */
	public function index()
	{
		$roleList = RoleModel::order('order', 'asc')->paginate();

		return Builder::table()
			->setTableName('sys_role')
			->addColumns([
				['id', 'ID'],
				['name', '角色名称'],
				['remark', '备注'],
				['status', '状态', 'switch']
			])
			->setTableData($roleList)
			->addTopButtons('add')
			->addRightButton('authorize', [
				'title' => '权限设置',
				'class' => 'layui-btn layui-btn-warm layui-btn-xs',
				'url' => url('authorize', ['id' => '__id__']),
				'method' => 'href'
			])
			->addRightButtons('edit,delete')
			->replaceRightButton(['id' => 1], "<button class='layui-btn layui-btn-xs layui-btn-disabled' disabled>不可操作</button>")
			->fetch();
	}

	/**
     * 角色添加
     */
    public function add()
    {
    	if ($this->request->isPost()) {
    		$params = $this->request->param();

    		$validate = new RoleValidate;
	        if (!$validate->check($params)) {
	            $this->error($validate->getError());
	        }

	        $result = RoleModel::create($params);
	        if ($result) {
	        	$this->success('添加成功', url('index'));
	        } else {
	        	$this->error('添加失败');
	        }
    	} else {
    		return Builder::form()
				->setPageTitle('角色添加')
				->addFormItems([
					['text', 'name', '名称'],
					['textarea', 'remark', '备注'],
					['radio', 'status', '状态', '', [1 => '开启', 0 => '禁用'], 1],
					['number', 'order', '排序', '', 99, '', 0, 99, 0]
				])
				->fetch();
    	}
    }

    /**
     * 权限设置
     */
    public function authorize()
    {
    	if ($this->request->isPost()) {
    		// 获取角色ID
    		$id = $this->request->post('id', 0, 'intval');
	        if ($id == 1) {
	            $this->error("超级管理员角色不能被编辑！");
	        }
	        // 删除角色下的所有权限
	        AuthAccessModel::destroy(function($query) use ($id){
			    $query->where('role_id', $id);
			});
			// 获取选中的菜单ID
			$menuIds = $this->request->post('menu_ids');
			if (!empty($menuIds)) {
				// 查询选中的菜单数据
				$menuList = MenuModel::where('id', 'in', $menuIds)->field('module,controller,action')->select();
				if (!$menuList->isEmpty()) {
					$authAccessData = [];
		            foreach ($menuList as $menu) {
		                $ruleName = $menu['module'] . '/' . $menu['controller'] . '/' . $menu['action'];
		                $authAccessData[] = ['role_id' => $id, 'rule_name' => $ruleName];
		            }
		            $authAccessModel = new AuthAccessModel;
		           	$authAccessModel->saveAll($authAccessData);
				}
			}
			$this->success('保存成功');
    	} else {
	    	$id = $this->request->param('id', 0, 'intval');

	    	// 获取当前拥有的权限
        	$ruleNames = AuthAccessModel::where('role_id', $id)->column('rule_name');

			$menuList = MenuModel::field('id,parent_id,name,module,controller,action,icon')
				->order('order', 'asc')->select();

			$trees = Tree::toLayer($menuList, 4, function ($menu) use ($ruleNames) {
				$menu['spread'] = true;
				$menu['title'] = '<i class="layui-icon ' . $menu['icon'] . '"></i> ' . $menu['name'] . ' (' . $menu['module'] . '/' . $menu['controller'] . '/' . $menu['action'] . ')';
				$url = $menu['module'] . '/' . $menu['controller'] . '/' . $menu['action'];
				if (in_array($url, $ruleNames) && $menu['extremity'] === true) {
					$menu['checked'] = true;
				}
				return $menu;
			});
		
			$this->assign([
				'id' => $id,
				'trees' => json_encode($trees)
			]);

			return $this->fetch();    		
    	}
    }

    /**
     * 角色编辑
     */
    public function edit()
    {
    	if ($this->request->isPost()) {
    		$params = $this->request->param();

    		$validate = new RoleValidate;
	        if (!$validate->check($params)) {
	            $this->error($validate->getError());
	        }

	        if ($params['id'] == 1) {
	            $this->error("超级管理员角色不能被修改！");
	        }

	        $result = RoleModel::where('id', $params['id'])->update($params);
	        if ($result !== false) {
	        	$this->success('保存成功', url('index'));
	        } else {
	        	$this->error('保存失败');
	        }
    	} else {
    		$id = $this->request->param('id', 0, 'intval');

    		$role = RoleModel::get($id);

    		return Builder::form()
				->setPageTitle('角色编辑')
				->addFormItems([
					['hidden', 'id'],
					['text', 'name', '名称'],
					['textarea', 'remark', '备注'],
					['radio', 'status', '状态', '', [1 => '开启', 0 => '禁用']],
					['number', 'order', '排序', '', 99, '', 0, 99, 0]
				])
				->setFormData($role->getData())
				->fetch();
    	}
    }

    /**
     * 角色删除
     */
    public function delete()
    {
    	$id = $this->request->param('ids', 0, 'intval');

    	if ($id == 1) {
            $this->error("超级管理员角色不能被删除！");
        }

        $result = RoleModel::destroy($id);

        if ($result) {
        	RoleManagerModel::destroy(function($query) use ($id){
			    $query->where('role_id', $id);
			});
       		// 删除角色下的所有权限
	        AuthAccessModel::destroy(function($query) use ($id){
			    $query->where('role_id', $id);
			});
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

}