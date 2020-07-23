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
 * @Last Modified Time: 2020-04-04 23:28:36
 */
 // ------------------------------------------------------------------------

namespace app\common\behavior;

use think\facade\App;
use app\system\model\ConfigModel;

/**
 * 初始化配置信息行为
 * 将系统配置信息合并到本地配置
 * @package app\common\behavior
 */
class ConfigBehavior
{
    /**
     * 执行行为 run方法是Behavior唯一的接口
     * @access public
     * @return void
     */
    public function run()
    {
        // 路由检测
        $dispatch = App::routeCheck()->init()->getDispatch();
        if (is_array($dispatch)) {
            // 获取当前模块名称
            $module = isset($dispatch[0]) ? $dispatch[0] : '';
            // 获取当前控制器名称
            $controller = isset($dispatch[1]) ? $dispatch[1] : '';

            if ($module == 'api' || strpos($controller, 'api_') === 0) {
                // 设置API异常处理handle类
                config('exception_handle', '\\app\\api\\exception\\ExceptionHandler');
            }
        }

    	// 读取系统配置
        $configModel = new ConfigModel();
        $configs = $configModel->getItems();

        // 设置配置信息
        config($configs, 'app');
    }
}
