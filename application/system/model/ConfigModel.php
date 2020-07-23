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
 * @Last Modified Time: 2020-04-05 14:47:22
 */
 // ------------------------------------------------------------------------

namespace app\system\model;

use app\common\model\BaseModel;

class ConfigModel extends BaseModel
{
	protected $name = 'sys_config';

	/**
     * 获取配置项参数
     * @param string|array $names 配置项name
     * @param string $group 配置项分组
     * @return json
     */
	public function getItems($names = '', $group = '')
	{
		$map = [
			['status', '=', 1]
		];

		if (!empty($names)) {
			if (!is_array($names)) {
				$names = array_map('trim', explode(',', $names));
			}
			$map[] = ['name', 'in', $names];
		}

		if ($group != '') {
			$map[] = ['group', '=', $group];
		}


		$configs = $this->where($map)->column('type,value', 'name');
		
		$result = [];

		foreach ($configs as $config) {
            switch ($config['type']) {
                case 'array':
                    $result[$config['name']] = parse_attr($config['value']);
                    break;
                default:
                    $result[$config['name']] = $config['value'];
                    break;
            }
		}

		return $result;
	}
}