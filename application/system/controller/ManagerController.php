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
 * @Last Modified Time: 2020-03-31 23:00:23
 */
 // ------------------------------------------------------------------------

namespace app\system\controller;

use app\common\builder\Builder;
use app\system\model\RoleModel;
use app\system\model\ManagerModel;
use app\system\model\RoleManagerModel;
use app\system\validate\ManagerValidate;
use app\common\controller\AdminBaseController;

class ManagerController extends AdminBaseController
{
	/**
     * 管理员列表
     */
	public function index()
	{
		$user = ManagerModel::get(1);
		
		$managerList = ManagerModel::paginate()->each(function($item){
			$item->roles_name = implode('，', array_column($item->roles->toArray(), 'name'));
		});

		return Builder::table()
			->addColumns([
				['id', 'ID'],
				['username', '用户名'],
				['roles_name', '角色'],
				['mobile', '手机号'],
				['last_login_ip', 'IP地址'],
				['last_login_time', '登录时间']
			])
			->setTableData($managerList)
			->addTopButtons('add')
			->addRightButtons('edit,delete')
			->replaceRightButton(['id' => 1], "<button class='layui-btn layui-btn-xs layui-btn-disabled' disabled>不可操作</button>")
			->fetch();
	}

	/**
     * 管理员添加
     */
	public function add()
	{
		if ($this->request->isPost()) {
			$params = $this->request->param();

    		$validate = new ManagerValidate;
	        if (!$validate->scene('add')->check($params)) {
	            $this->error($validate->getError());
	        }

	        $roleIds = $this->request->param('role_ids', []);
            if (in_array('1', $roleIds)) {
                if ($this->managerId != 1) {
                    $this->error("您没有权限创建超级管理员！");
                }
            }

	        if (!isset($params['status'])) {
	        	$params['status'] = 0;
	        }

            $params['password'] = md5($params['password']);

	        $managerModel = new ManagerModel;
	        $result = $managerModel->save($params);

	        if ($result) {
	        	if (!empty($roleIds)) {
		        	// 添加关联角色
	                $roleManagerData = [];
	                foreach ($roleIds as $roleId) {
	                    $roleManagerData[] = ['role_id' => $roleId, 'manager_id' => $managerModel->id];
	                }
		            $roleManagerModel = new RoleManagerModel;
		           	$roleManagerModel->saveAll($roleManagerData);
	        	}
	        	$this->success('添加成功', url('index'));
	        } else {
	        	$this->error('添加失败');
	        }
		} else {
	        // 获取角色列表
	        $roleList = RoleModel::where('status', 1)->column('id,name');

			return Builder::form()
				->setPageTitle('管理员添加')
				->addFormItems([
					['text', 'username', '用户名'],
					['password', 'password', '密码', '6-20位'],
					['text', 'nickname', '昵称'],
					['text', 'mobile', '手机号'],
					['checkbox', 'role_ids', '角色', '', $roleList],
					['switch', 'status', '状态', '', 1, '启用|禁用']
				])
				->fetch();
		}
	}

	/**
     * 管理员编辑
     */
    public function edit()
    {
		if ($this->request->isPost()) {
			$params = $this->request->param();

    		$validate = new ManagerValidate;
	        if (!$validate->scene('edit')->check($params)) {
	            $this->error($validate->getError());
	        }

	        if (!isset($params['status'])) {
	        	$params['status'] = 0;
	        }

            if (empty($params['password'])) {
                unset($params['password']);
            } else {
                $params['password'] = md5($params['password']);
            }

            $managerModel = new ManagerModel;
            $result = $managerModel->save($params, ['id' => $params['id']]);
            if ($result) {
            	$id = $params['id'];
	        	// 删除之前的关联角色
            	RoleManagerModel::destroy(function($query) use ($id){
				    $query->where('manager_id', $id);
				});
                // 添加新的关联角色
                $roleIds = $this->request->param('role_ids', []);
                if (!empty($roleIds)) {
	                $roleManagerData = [];
	                foreach ($roleIds as $roleId) {
	                    $roleManagerData[] = ['role_id' => $roleId, 'manager_id' => $id];
	                }
		            $roleManagerModel = new RoleManagerModel;
		           	$roleManagerModel->saveAll($roleManagerData);
                }
	        	$this->success('保存成功', url('index'));
            } else {
            	$this->error('保存失败');
            }

		} else {
			$id = $this->request->param('id', 0, 'intval');

			$manager = ManagerModel::get($id);
			unset($manager->password);

			// 获取拥有的角色ID
			$manager['role_ids'] = implode(',', array_column($manager->roles->toArray(), 'id'));

	        // 获取角色列表
	        $roleList = RoleModel::where('status', 1)->column('id,name');

			return Builder::form()
				->setPageTitle('管理员编辑')
				->addFormItems([
					['hidden', 'id'],
					['static', 'username', '用户名', '不可更改'],
					['password', 'password', '密码', '6-20位'],
					['text', 'nickname', '昵称'],
					['text', 'mobile', '手机号'],
					['checkbox', 'role_ids', '角色', '', $roleList],
					['switch', 'status', '状态', '', 1, '启用|禁用']
				])
				->setFormData($manager)
				->fetch();			
		}
    }

 	/**
     * 管理员删除
     */
    public function delete()
    {
    	$id = $this->request->param('ids', 0, 'intval');
        if ($id == 1) {
            $this->error("最高管理员不能被删除！");
        }

        $result = ManagerModel::destroy($id);

        if ($result) {
        	RoleManagerModel::destroy(function($query) use ($id){
			    $query->where('manager_id', $id);
			});
        	$this->success('删除成功');
        } else {
        	$this->error('删除失败');
        }
    }   
}