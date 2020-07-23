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
 * @Last Modified Time: 2020-02-14 16:31:56
 */
 // ------------------------------------------------------------------------

namespace app\system\controller;

use app\system\model\ManagerModel;
use app\common\controller\BaseController;

class AccessController extends BaseController
{
    /**
     * 初始化
     */
    protected function initialize()
    {
    	parent::initialize();
    }

    /**
     * 默认转向登录
     */ 
    public function index()
    {
        $this->redirect(url('login'));
    }

    /**
     * 检查是否登录，已登录则跳转到后台首页
     */
    protected function checkLogin()
    {
        if (!empty(session('manager_id', '', 'admin'))) {
            $this->redirect(url('system/Entry/index'));
        }
    }

    /**
     * 后台登录界面
     */
	public function login()
	{
		$this->checkLogin();

		return $this->fetch();
	}

    /**
     * 后台登录提交
     */    
    public function doLogin()
    {   
        $params = $this->request->param();

        $validate = $this->app->validate('Login');
        if (!$validate->check($params)) {
            $this->error($validate->getError());
        }
        $manager = ManagerModel::where('username', $params['username'])
            ->where('password', md5($params['password']))
            ->find();

        if (empty($manager)) {
            $this->error('用户名或密码错误');
        } elseif ($manager->getData('status') == 0) {
            $this->error('用户已禁用，请联系管理员!');
        } else {
            session('manager_id', $manager->id, 'admin');
            session('manager_name', $manager->username, 'admin');
            $manager->last_login_ip = $this->request->ip(0, true);
            $manager->last_login_time = time();
            $manager->save();
            $this->success('登录成功', url('system/Entry/index'));
        }
    }

    /**
     * 退出登录
     */
    public function logout()
    {        
        session('manager_id', null, 'admin');
        session('manager_name', null, 'admin');
        $this->redirect(url('system/Access/login'));
    }
}