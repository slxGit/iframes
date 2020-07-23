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
 * @Last Modified Time: 2020-04-10 09:50:15
 */
 // ------------------------------------------------------------------------

namespace app\member\controller;

use app\common\builder\Builder;
use app\member\model\UserModel;
use app\common\controller\AdminBaseController;

class AdminUserController extends AdminBaseController
{
	/**
     * 用户列表
     */
	public function index()
	{
        $params = $this->request->param();

        $map = [];

        if (!empty($params['nickname'])) {
            $map[] = ['nickname', '=', $params['nickname']]; 
        }

        if (!empty($params['mobile'])) {
            $map[] = ['mobile', 'like', $params['mobile'] . '%']; 
        }

        $userList = UserModel::where($map)->paginate()->each(function($item){
            if (!empty($item->qq_openid)) {
                $item->social = 'QQ';
            }
            if (!empty($item->wechat_openid)) {
                if (!empty($item->social)) {
                    $item->social += '|' . $item->social;
                } else {
                    $item->social = '微信';
                }
            }
            if (!empty($item->wechat_mini_openid)) {
                if (!empty($item->social)) {
                    $item->social += '|' . $item->social;
                } else {
                    $item->social = '微信小程序';
                }
            }
        });

        return Builder::table()
            ->addColumns([
                ['id', 'ID'],
                ['nickname', '昵称'],
                ['mobile', '手机号'],
                ['social', '社交账号'],
                ['last_login_ip', 'IP地址']
            ])
            ->addSearchItems([
                ['text', 'nickname', '昵称'],
                ['text', 'mobile', '手机号']
            ])
            ->setTableData($userList)
            ->fetch();
	}

}