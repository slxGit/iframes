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
 * @Last Modified Time: 2020-02-15 12:47:02
 */
 // ------------------------------------------------------------------------

namespace app\member\controller;

use app\common\controller\MemberBaseController;

class EntryController extends MemberBaseController
{
	/**
     * 用户首页
     */
	public function index()
	{
		$name = session('user_name', '', 'member');
		$logoutUrl = url('member/Access/logout');
        return '<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none;font-size:20px} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px;} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:) </h1><p> 您好，' . $name .'<br/><a href="' . $logoutUrl . '">退出登录</a></p></div>';
	}

}