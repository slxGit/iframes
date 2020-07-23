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
 * @Date:       2020-02-10 12:31:48
 *
 * @Last Modified By:   Aaron
 * @Last Modified Time: 2020-02-14 16:28:04
 */
 // ------------------------------------------------------------------------

namespace app\common\controller;

use app\common\controller\BaseController;

class MemberBaseController extends BaseController
{
    /**
     * 用户ID
     */
    protected $userId;

    /**
     * 初始化
     */
    protected function initialize()
    {
        parent::initialize();

        $this->userId = $this->checkLogin();
    }

    /**
     * 检查是否登录，没有登录则跳转到登录页面
     */
    protected function checkLogin()
    {
        $userId = session('user_id', '', 'member');
        
        if (!empty($userId)) {
            return $userId;
        } else {
            $this->redirect(url('member/Access/login'));
        }
    }
}