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
 * @Date:       2020-02-15 19:39:23
 *
 * @Last Modified By:   Aaron
 * @Last Modified Time: 2020-04-01 10:51:54
 */
 // ------------------------------------------------------------------------

namespace app\common\behavior;

use \think\facade\Hook;
use app\system\model\HookModel;
use app\system\model\PluginModel;
use app\system\model\HookPluginModel;

/**
 * 注册钩子行为
 * @package app\common\behavior
 */
class HookBehavior
{
    /**
     * 执行行为 run方法是Behavior唯一的接口
     * @access public
     * @return void
     */
    public function run()
    {
        // 获取所有钩子
        $hooks = HookModel::where('status', 1)->column('name');
      
        // 获取所有插件
        $plugins = PluginModel::where('status', 1)->column('name');

        // 获取钩子对应的插件
        $hookPlugins = HookPluginModel::where('status', 1)->field('hook,plugin')->order('order', 'asc')->select();
     
        if (!$hookPlugins->isEmpty()) {
            foreach ($hookPlugins as $value) {
                if (in_array($value['hook'], $hooks) && in_array($value['plugin'], $plugins)) {
                    Hook::add($value['hook'], get_plugin_class($value['plugin']));
                }
            }
        }
    }
}
