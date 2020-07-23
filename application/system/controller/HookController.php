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
 * @Last Modified Time: 2020-04-01 16:46:11
 */
 // ------------------------------------------------------------------------

namespace app\system\controller;

use app\common\builder\Builder;
use app\system\model\HookModel;
use app\system\model\HookPluginModel;
use app\system\validate\HookValidate;
use app\common\controller\AdminBaseController;

class HookController extends AdminBaseController
{
	/**
     * 钩子列表
     */
	public function index()
	{
		$map = [];

		$params = $this->request->param();

		if (!empty($params['name'])) {
			$map[] = ['name', 'like', '%' . $params['name'] . '%'];
		}

		$hookList = HookModel::where($map)->paginate();

		return Builder::table()
			->setTableName('sys_hook')
			->addColumns([
				['name', '名称', '', '', ['sort' => true]],
				['description', '描述'],
				['system', '系统钩子', 'judge'],
				['status', '状态', 'switch']
			])
			->addSearchItem('text', 'name', '[:请输入钩子名称]')
			->addTopButtons('add,enable,disable,delete')
			->addRightButtons('edit,delete')
			->setTableData($hookList)
			->fetch();
	}

	/**
     * 钩子添加
     */	
	public function add()
	{
        if ($this->request->isPost()) {
        	$post = $this->request->post();

        	$validate = new HookValidate;
	        if (!$validate->check($post)) {
	            $this->error($validate->getError());
	        }

            if (HookModel::create($post)) {
                $this->success('添加成功', url('index'));
            } else {
                $this->error('添加失败');
            }

        } else {
			return Builder::form()
				->setPageTitle('钩子新增')
				->addFormItems([
					['text', 'name', '钩子名称', '由字母和下划线组成，如：<code>app_init</code>'],
					['text', 'description', '钩子描述'],
					['hidden', 'system', 1]
				])
				->fetch();        	
        }
	}

	/**
     * 钩子编辑
     */	
	public function edit()
	{
        if ($this->request->isPost()) {
        	$post = $this->request->post();

        	$validate = new HookValidate;
	        if (!$validate->check($post)) {
	            $this->error($validate->getError());
	        }

            if (HookModel::update($post)) {
                if ($post['sort'] != '') {
                    HookPluginModel::sort($post['name'], $post['sort']);
                }
                $this->success('保存成功', url('index'));
            } else {
                $this->error('保存失败');
            }

        } else {
        	$id = $this->request->param('id', 0, 'intval');

        	$hook = HookModel::get($id);

	        // 获取该钩子的所有插件
	        $plugins = HookPluginModel::where('hook', $hook['name'])->order('order')->column('plugin,plugin');
	 
			return Builder::form()
				->setPageTitle('钩子编辑')
				->addFormItems([
					['hidden', 'id'],
					['text', 'name', '钩子名称', '由字母和下划线组成，如：<code>app_init</code>'],
					['text', 'description', '钩子描述'],
					['sort', 'sort', '插件排序', '', $plugins]
				])
				->setFormData($hook)
				->fetch();        	
        }
	}

    /**
     * 快速编辑（启用/禁用）
     * @param string $status 状态
     * @return mixed
     */
    public function quickEdit()
    {
        $id = $this->request->post('id');
        $status = $this->request->post('value');

        $hookName = HookModel::where('id', $id)->value('name');
        $result = HookPluginModel::where('hook', $hookName)->setField('status', $status);
        if ($result === false) {
            $this->error('操作失败');
        }

        return parent::quickEdit();
    }

    /**
     * 启用
     */
    public function enable()
    {
        return $this->setStatus('enable');
    }

    /**
     * 禁用
     */
    public function disable()
    {
    	return $this->setStatus('disable');
    }

    /**
     * 删除
     */
    public function delete()
    {
        $ids = $this->request->post('ids');
     	$ids = (array)$ids;

        $map = [
            ['id', 'in', $ids],
            ['system', '=', 1],
        ];
        if (HookModel::where($map)->find()) {
            $this->error('禁止删除系统钩子');
        }
        return $this->setStatus('delete');
    }

    /**
     * 设置状态
     * @param string $type 类型
     */
    protected function setStatus($type = '')
    {
        $ids = $this->request->post('ids');
   		$ids = (array)$ids;
   		
        foreach ($ids as $id) {
            $hookName = HookModel::where('id', $id)->value('name');
            $status = $type == 'enable' ? 1 : 0;
            $result = HookPluginModel::where('hook', $hookName)->setField('status', $status);
            if ($result === false) {
            	$this->error('操作失败');
            }
        }

        return parent::setStatus($type);
    }
}