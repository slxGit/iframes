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
 * @Date:       2020-02-10 19:41:57
 *
 * @Last Modified By:   Aaron
 * @Last Modified Time: 2020-04-21 16:47:29
 */
 // ------------------------------------------------------------------------

namespace app\system\model;

use app\common\model\BaseModel;
use app\system\model\HookModel;

class HookPluginModel extends BaseModel
{
	protected $name = 'sys_hook_plugin';

    /**
     * 添加钩子和对应的插件
     * @param array $hooks 钩子
     * @param string $pluginName 插件名称
     */
    public static function addHooks($hooks = [], $pluginName = '')
    {
        if (!empty($hooks) && is_array($hooks)) {
            $data = [];
            foreach ($hooks as $name => $description) {
                if (is_numeric($name)) {
                    $name = $description;
                }
                $data[] = [
                    'hook'   => $name,
                    'plugin' => $pluginName
                ];
            }
            return self::insertAll($data);
        }
        return true;
    }

    /**
     * 删除钩子
     * @param string $pluginName 钩子名称
     */
    public static function deleteHooks($pluginName = '')
    {
        if (!empty($pluginName)) {
            // 查询插件用到的钩子
            $allHooks = self::where('plugin', $pluginName)->column('hook');
            if (!empty($allHooks)) {
                // 查询同时用于其他插件的钩子
                $excludeHooks = self::where('plugin', '<>', $pluginName)->where('hook', 'in', $allHooks)->column('hook');
                // 获取要删除的钩子名称(差集)
                $hooks = array_diff($allHooks, $excludeHooks);
                if (!empty($hooks)) {
                    HookModel::where('name', 'in', $hooks)->where('system', 0)->delete();
                }
            }
            if (self::where('plugin', $pluginName)->delete() === false) {
                return false;
            }
        }
        return true;
    }

    /**
     * 钩子插件排序
     * @param string $hook 钩子
     * @param string $plugins 插件名
     * @return bool
     */
    public static function sort($hook = '', $plugins = '')
    {
        if ($hook != '' && $plugins != '') {
            $plugins = is_array($plugins) ? $plugins : explode(',', $plugins);
            foreach ($plugins as $k => $plugin) {
                $map = [
                    'hook'   => $hook,
                    'plugin' => $plugin
                ];
                self::where($map)->setField('order', $k + 1);
            }
        }
        return true;
    }
}