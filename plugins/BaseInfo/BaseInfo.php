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
 * @Date:       2020-03-31 10:31:04
 *
 * @Last Modified By:   Aaron
 * @Last Modified Time: 2020-04-01 16:19:24
 */
 // ------------------------------------------------------------------------
 
namespace plugins\BaseInfo;

use plugins\Plugin;

/**
 * 基本信息插件
 * @package plugins\SystemInfo
 */
class BaseInfo extends Plugin
{
	public $info = [
		'title'       => '基本信息',
		'author'      => '张津硕',
		'version'     => '1.0.0',
        'description' => '在后台首页显示基本信息'
	];

	public $hooks = [
		'admin_index' => '后台首页'
	];

	/**
     * 后台首页钩子
     */
	public function adminIndex()
	{
		$this->assign('config', $this->getConfig());
		echo $this->fetch('info');
	}

	public function install()
	{
		return true;
	}

	public function uninstall()
	{
		return true;
	}
}