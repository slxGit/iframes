<?php
namespace app\portal\controller;

use app\common\controller\BaseController;

class PluginController extends BaseController
{   
	/**
     * 执行插件方法
     * @return mixed
     */
    public function execute()
    {
        $plugin     = input('param._plugin');
        $controller = input('param._controller');
        $action     = input('param._action');
        $params     = $this->request->param();

        if (empty($plugin) || empty($controller) || empty($action)) {
            $this->error('没有指定插件名称、控制器名称或操作名称');
        }
        
        if (!plugin_action_exist($plugin, $controller, $action)) {
            $this->error("找不到方法：{$plugin}/{$controller}/{$action}");
        }

        return plugin_action($plugin, $controller, $action, $params);
    }
}
