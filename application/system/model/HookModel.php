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
 * @Last Modified Time: 2020-03-31 16:37:15
 */
 // ------------------------------------------------------------------------

namespace app\system\model;

use app\common\model\BaseModel;
use app\system\model\HookPluginModel;

class HookModel extends BaseModel
{
	protected $name = 'sys_hook';

    /**
     * 添加钩子
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
                    $description = '';
                }
                if (self::where('name', $name)->find()) {
                    continue;
                }
                $data[] = [
                    'name'        => $name,
                    'description' => $description
                ];
            }
            if (!empty($data) && self::insertAll($data) === false) {
                return false;
            } else {
            	HookPluginModel::addHooks($hooks, $pluginName);
            }
        }
        return true;
    }

    /**
     * 删除钩子
     * @param string $pluginName 插件名称
     */
    public static function deleteHooks($pluginName = '')
    {
        return HookPluginModel::deleteHooks($pluginName);
    }
}