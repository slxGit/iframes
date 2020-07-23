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
 * @Last Modified Time: 2020-04-01 09:35:00
 */
 // ------------------------------------------------------------------------

namespace app\system\model;

use app\common\model\BaseModel;

class PluginModel extends BaseModel
{
	protected $name = 'sys_plugin';

    /**
     * 获取插件配置
     * @param string $name 插件名称
     * @param string $item 指定返回的插件配置项
     * @return array|mixed
     */
    public function getConfig($name = '', $item = '')
    {
        $config = $this->where('name', $name)->value('config');
        if (!$config) {
            return [];
        }

        $config = json_decode($config, true);

        if (!empty($item)) {
            $items = explode(',', $item);
            if (count($items) == 1) {
                return isset($config[$item]) ? $config[$item] : '';
            } else {
	            $result = [];
	            foreach ($items as $item) {
	                $result[$item] = isset($config[$item]) ? $config[$item] : '';
	            }
	            return $result;
            }
        } else {
        	return $config;
        }
    }

    /**
     * 设置插件配置
     * @param string $name 插件名.配置名
     * @param string|array $value 配置值
     * @return bool
     */
    public function setConfig($name = '', $value = '')
    {
        $item = '';
        if (strpos($name, '.')) {
            list($name, $item) = explode('.', $name);
        }

        $config = $this->where('name', $name)->value('config');
        if (!$config) {
            return false;
        }

        $config = json_decode($config, true);
        
        if ($item === '') {
            // 批量更新
            if (!is_array($value) || empty($value)) {
                return false;
            }

            $config = array_merge($config, $value);
        } else {
            // 更新单个值
            $config[$item] = $value;
        }

        $result = $this->where('name', $name)->setField('config', json_encode($config));

        if ($result === false) {
            return false;
        } else {
        	return true;
        }
    }

    public function setConfigAttr($value)
    {
        return !empty($value) ? json_encode($value) : '';
    }
}