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
 * @Last Modified Time: 2020-03-22 22:52:06
 */
 // ------------------------------------------------------------------------

namespace app\api\controller;

use app\common\builder\Builder;
use app\api\model\AppModel;
use app\api\validate\AppValidate;
use app\common\controller\AdminBaseController;

class AdminAppController extends AdminBaseController
{
	/**
     * 应用管理
     */
    public function index()
    {
        return Builder::table()
            ->addColumns([
                ['id', 'ID'],
                ['name', '应用名称'],
                ['appid', 'appid'],
                ['appsecret', 'appsecret']
            ])
            ->addTopButton('add')
            ->addRightButtons('edit,delete')
            ->setTableData(AppModel::paginate())
            ->fetch();
    }

	/**
     * 应用添加
     */
    public function add()
    {
		if ($this->request->isPost()) {
			$params = $this->request->param();

    		$validate = new AppValidate;
	        if (!$validate->check($params)) {
	            $this->error($validate->getError());
	        }

	        if (!isset($params['status'])) {
	        	$params['status'] = 0;
	        }

	        $result = AppModel::create($params);

	        if ($result) {
	        	$this->success('添加成功', url('index'));
	        } else {
	        	$this->error('添加失败');
	        }
		} else {
			return Builder::form()
				->setPageTitle('应用添加')
				->addFormItems([
					['text', 'name', '应用名称'],
					['text', 'appid', 'appid'],
					['text', 'appsecret', 'appsecret'],
					['switch', 'status', '状态'],
					['textarea', 'remark', '备注']
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

    		$validate = new AppValidate;
	        if (!$validate->check($params)) {
	            $this->error($validate->getError());
	        }

	        if (!isset($params['status'])) {
	        	$params['status'] = 0;
	        }

            $result = AppModel::where('id', $params['id'])->update($params);
            if ($result) {
	        	$this->success('保存成功', url('index'));
            } else {
            	$this->error('保存失败');
            }

		} else {
			$id = $this->request->param('id', 0, 'intval');

			return Builder::form()
				->setPageTitle('应用编辑')
				->addFormItems([
					['hidden', 'id'],
					['text', 'name', '应用名称'],
					['text', 'appid', 'appid'],
					['text', 'appsecret', 'appsecret'],
					['switch', 'status', '状态'],
					['textarea', 'remark', '备注']
				])
				->setFormData(AppModel::get($id))
				->fetch();			
		}
    }
}