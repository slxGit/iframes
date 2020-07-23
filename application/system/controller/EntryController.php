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
 * @Last Modified Time: 2020-05-11 11:54:11
 */
 // ------------------------------------------------------------------------

namespace app\system\controller;

use utils\Tree;
use think\facade\Env;
use app\system\model\MenuModel;
use app\common\controller\AdminBaseController;

class EntryController extends AdminBaseController
{
	/**
     * 后台外层框架界面
     */
	public function index()
	{
		// 获取菜单列表
		$menuList = MenuModel::field('id,parent_id,name,module,controller,action,param,icon')
			->where('status', 1)->order(['order' => 'asc', 'id' => 'asc'])->select();
	
		// 删除没有访问权限的菜单
		foreach ($menuList as $k => $v) {
			if (!$this->checkAuth($v['module'] . '/' . $v['controller'] . '/' . $v['action'])) {
				unset($menuList[$k]);
			}
		}

		// 数据转为树状结构
		$menus = Tree::toLayer($menuList, 3, function ($menu) {
			$menu['url'] = url($menu['module'] . '/' . $menu['controller'] . '/' . $menu['action'], $menu['param']);

			return $menu;
 		});

		$this->assign('menus', $menus);

		return $this->fetch();
	}

	/**
     * 个人信息
     */
	public function profile()
	{
		if ($this->request->isPost()) {
			$params = $this->request->param();

    		$validate = new \app\system\validate\ManagerValidate;
	        if (!$validate->scene('edit')->check($params)) {
	            $this->error($validate->getError());
	        }

            if (empty($params['password'])) {
                unset($params['password']);
            } else {
                $params['password'] = md5($params['password']);
            }

            $managerModel = new \app\system\model\ManagerModel;
            $result = $managerModel->save($params, ['id' => $params['id']]);
            if ($result) {
	        	$this->success('保存成功');
            } else {
            	$this->error('保存失败');
            }

		} else {
			$manager = \app\system\model\ManagerModel::get($this->managerId);
			unset($manager->password);

			return \app\common\builder\Builder::form()
				->setPageTitle('个人设置')
				->addFormItems([
					['hidden', 'id'],
					['static', 'username', '用户名', '不可更改'],
					['password', 'password', '密码', '6-20位'],
					['text', 'nickname', '昵称'],
					['text', 'mobile', '手机号']
				])
				->setFormData($manager)
				->fetch();			
		}
	}

	/**
     * 清除缓存
     */
	public function clearCache()
	{
		$deleteDirs = ['cache', 'log', 'temp'];

		foreach ($deleteDirs as $dir) {
			$dirname = Env::get('runtime_path') . $dir;
			$this->deleteDir($dirname);
		}

		$this->success('清除成功');
	}

    /**
     * 删除目录
     * @method  deleteDir
     * @param   string  $dirname  目录名
     * @return  boolean
     */    
    protected function deleteDir($dirname)
    {
        if (!is_dir($dirname)) return false;
        if ($dh = opendir($dirname)) {
            while (($file = readdir($dh)) !== false) {
                if ($file == '.' || $file == '..') continue;
                $name = $dirname . DIRECTORY_SEPARATOR . $file;
                if (is_dir($name)) {
                    $this->deleteDir($name);
                } else {
			        if(is_file($name)) {
			        	unlink($name);
			        }
                }
            }
            closedir($dh);
            rmdir($dirname);
            return true;
        } else {
            return false;
        }
    }
}