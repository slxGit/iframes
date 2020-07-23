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
 * @Last Modified Time: 2020-04-03 22:15:57
 */
 // ------------------------------------------------------------------------

namespace app\system\controller;

use think\Db;
use utils\Sql;
use app\common\builder\Builder;
use app\system\model\HookModel;
use app\system\model\PluginModel;
use app\system\model\HookPluginModel;
use app\common\controller\AdminBaseController;

class PluginController extends AdminBaseController
{
	/**
     * 插件列表
     */
	public function index()
	{
        // 关键词
        $keyword = $this->request->param('keyword', '');

        // 状态 1：未安装 2：已启用 3：已禁用 4：已损坏
        $status = $this->request->param('status', '');

        // 获取插件目录下的所有插件目录
        $dirs = array_map('basename', glob(config('plugin_path') . '*', GLOB_ONLYDIR));

        if ($dirs === false || !file_exists(config('plugin_path'))) {
            $this->error('插件目录不可读或者不存在');
        }

        // 获取数据库插件(已安装)
        $plugins = PluginModel::order('order asc,id desc')->column(true, 'name');

        // 获取未安装的插件
        foreach ($dirs as $plugin) {
            if (!isset($plugins[$plugin])) {
                $plugins[$plugin]['name'] = $plugin;

                // 获取插件类名
                $class = get_plugin_class($plugin);
                
                // 插件类不存在则跳过实例化
                if (!class_exists($class)) {
                    // 插件的入口文件不存在！
                    $plugins[$plugin]['status'] = '-2';
                    continue;
                }

                // 实例化插件
                $obj = new $class;

                // 插件插件信息缺失
                if (!isset($obj->info) || empty($obj->info)) {
                    // 插件信息缺失！
                    $plugins[$plugin]['status'] = '-3';
                    continue;
                }

                // 插件插件信息不完整
                if (!$this->checkInfo($obj->info)) {
                    $plugins[$plugin]['status'] = '-4';
                    continue;
                }

                // 插件未安装
                $plugins[$plugin] = array_merge($plugins[$plugin], $obj->info);
                $plugins[$plugin]['status'] = '-1';
            }
        }
   
        // 数量统计
        $total = [
            // 所有插件数量
            'all' => count($plugins),
            // 已禁用数量
            '0'   => 0,
            // 已启用数量
            '1'   => 0,
            // 未安装数量
            '-1'   => 0,
            // 已损坏数量
            '-2'   => 0
        ];

        // 过滤查询结果和统计数量
        foreach ($plugins as $key => $value) {
            // 统计数量
            if (in_array($value['status'], ['-2', '-3', '-4'])) {
                $total['-2']++;
            } else {
                $total[(string)$value['status']]++;
            }

            // 过滤查询
            if ($status != '') {
                if ($status == '-2') {
                    if (!in_array($value['status'], ['-2', '-3', '-4'])) {
                        unset($plugins[$key]);
                        continue;
                    }
                } elseif ($value['status'] != $status) {
                    unset($plugins[$key]);
                    continue;
                }
            }

            if ($keyword != '') {
                if (stristr($value['name'], $keyword) === false && (!isset($value['title']) || stristr($value['title'], $keyword) === false) && (!isset($value['author']) || stristr($value['author'], $keyword) === false)) {
                    unset($plugins[$key]);
                    continue;
                }
            }
        }
        
        // 处理状态和显示按钮
        $replaceRightButtons = [];
        foreach ($plugins as &$plugin) {
            $plugin['status'] = (string)$plugin['status'];
            switch ($plugin['status']) {
                case '-4': 
                    // 插件信息不完整
                    $plugin['title'] = '插件信息不完整';
                    break;
                case '-3':
                    // 插件信息缺失
                    $plugin['title'] = '插件信息缺失';
                    break;
                case '-2': 
                    // 入口文件不存在
                    $plugin['title'] = '入口文件不存在';
                    break;
            }
            // 插件设置状态状态

            // 处理右侧按钮
            if (in_array($plugin['status'], ['-2', '-3', '-4'])) {
                $buttons = "<button class='layui-btn layui-btn-xs layui-btn-disabled' disabled>不可操作</button>";
            } else {
                switch ($plugin['status']) {
                    case '-1':
                        $buttons = ['install'];
                        break;
                    
                    case '0':
                        $buttons[] = 'enable';
                        $buttons[] = 'uninstall';
                        if (isset($plugin['config']) && $plugin['config'] != '') {
                            $buttons[] = 'config';
                        }
                        if (isset($plugin['has_admin']) && $plugin['has_admin'] === 1) {
                            $buttons[] = 'manage';
                        }
                        break;

                    case '1':
                        $buttons[] = 'disable';
                        $buttons[] = 'uninstall';
                        if (isset($plugin['config']) && $plugin['config'] != '') {
                            $buttons[] = 'config';
                        }
                        if (isset($plugin['has_admin']) && $plugin['has_admin'] === 1) {
                            $buttons[] = 'manage';
                        }
                        break;
                }
            }
            $replaceRightButtons[] = [['name' => $plugin['name']], $buttons];

        }

        return Builder::table()
            ->addColumns([
                ['title', '名称'],
                ['version', '版本', '', '无版本号'],
                ['author', '作者'],
                ['description', '简介', '', '暂无简介']
            ])
            ->addSearchItem('text', 'keyword', '[:请输入标识/名称/作者]')
            ->addTopButton('all', [
                'title' => '全部（' . $total['all'] . '）',
                'url'   => url('index'),
                'class' => 'layui-btn layui-btn-primary layui-btn-sm'
            ])
            ->addTopButton('alenable', [
                'title' => '已启用（' . $total['1'] . '）',
                'url'   => url('index', ['status' => '1'])
            ])
            ->addTopButton('aldisable', [
                'title' => '已禁用（' . $total['0'] . '）',
                'url'   => url('index', ['status' => '0']),
                'class' => 'layui-btn layui-btn-warm layui-btn-sm'
            ])
            ->addTopButton('notinstall', [
                'title' => '未安装（' . $total['-1'] . '）',
                'url'   => url('index', ['status' => '-1']),
                'class' => 'layui-btn layui-btn-normal layui-btn-sm'
            ])
            ->addTopButton('aldamage', [
                'title' => '已损坏（' . $total['-2'] . '）',
                'url'   => url('index', ['status' => '-2']),
                'class' => 'layui-btn layui-btn-danger layui-btn-sm'
            ])
            ->addRightButton('install', [
                'title'    => '安装',
                'method'   => 'ajax',
                'param'    => [
                    'name' => '__name__'
                ],
                'confirm'  => true,
                'complete' => 'refresh'
            ])
            ->addRightButton('enable', [
                'confirm'  => true
            ])
            ->addRightButton('disable', [
                'confirm'  => true
            ])
            ->addRightButton('uninstall', [
                'title'    => '卸载',
                'method'   => 'ajax',
                'param'    => [
                    'name' => '__name__'
                ],
                'confirm'  => true,
                'complete' => 'refresh',
                'class'    => 'layui-btn layui-btn-danger layui-btn-xs'
            ])
            ->addRightButton('config', [
                'title'    => '设置',
                'url'      => url('config', ['name' => '__name__']),
                'class'    => 'layui-btn layui-btn-normal layui-btn-xs'
            ])
            ->addRightButton('manage', [
                'title'    => '管理',
                'url'      => url('manage', ['name' => '__name__']),
                'class'    => 'layui-btn layui-btn-primary layui-btn-xs'
            ])
            ->replaceRightButtons($replaceRightButtons)
            ->setTableData($plugins)
            ->fetch();

	}

    /**
     * 安装插件
     * @param string $name 插件标识
     */
    public function install($name = '')
    {
        set_time_limit(0)||ini_set("max_execution_time", 0);

        $plugName = trim($name);
        $plugName == '' && $this->error('插件不存在！');

        $pluginClass = get_plugin_class($plugName);

        if (!class_exists($pluginClass)) {
            $this->error('插件不存在！');
        }

        // 实例化插件
        $plugin = new $pluginClass;
        
        // 插件预安装
        if(!$plugin->install()) {
            $this->error('插件预安装失败：' . $plugin->getError());
        }

        // 执行安装插件sql文件
        $sqlFile = realpath(config('plugin_path') . $name . '/install.sql');
        if (file_exists($sqlFile)) {
            $content = Sql::getSqlFromFile($sqlFile);
            if (!empty($content)) {
                foreach ($content as $value) {
                    Db::execute($value);
                }
            }
        }

        // 插件配置信息
        $pluginInfo = $plugin->info;
        $pluginInfo['name'] = $name;

        // 验证插件信息
        $result = $this->validate($pluginInfo, 'Plugin');

        // 验证失败 输出错误信息
        if($result !== true) $this->error($result);

        // 并入插件配置值
        $pluginInfo['config'] = $plugin->getConfig();

        // 将插件信息写入数据库
        if (PluginModel::create($pluginInfo)) {
            // 添加钩子
            if (isset($plugin->hooks) && !empty($plugin->hooks)) {
                if (!HookModel::addHooks($plugin->hooks, $name)) {
                    $this->error('安装插件钩子时出现错误，请重新安装');
                }
            }
            $this->success('插件安装成功');
        } else {
            $this->error('插件安装失败');
        }
    }

    /**
     * 卸载插件
     * @param string $name 插件标识
     */
    public function uninstall($name = '')
    {
        set_time_limit(0)||ini_set("max_execution_time", 0);

        $pluginName = trim($name);
        $pluginName == '' && $this->error('插件不存在！');

        $pluginClass = get_plugin_class($pluginName);
        if (!class_exists($pluginClass)) {
            $this->error('插件不存在！');
        }

        // 实例化插件
        $plugin = new $pluginClass;

        // 插件预卸载
        if(!$plugin->uninstall()) {
            $this->error('插件预卸载失败：' . $plugin->getError());
        }

        // 卸载插件自带钩子
        if (HookModel::deleteHooks($pluginName) === false) {
            $this->error('卸载插件钩子时出现错误，请重新卸载');
        }

        // 执行卸载插件sql文件
        $sqlFile = realpath(config('plugin_path') . $pluginName . '/uninstall.sql');
        if (file_exists($sqlFile)) {
            $content = Sql::getSqlFromFile($sqlFile, true);
            if (!empty($content)) {
                Db::execute($content);
            }
        }

        // 删除插件信息
        if (PluginModel::where('name', $pluginName)->delete()) {
            $this->success('插件卸载成功');
        } else {
            $this->error('插件卸载失败');
        }
    }

    /**
     * 插件参数设置
     * @param string $name 插件名称
     */
    public function config($name = '')
    {
        // 更新配置
        if ($this->request->isPost()) {
            $post = $this->request->post();

            $dbConfig = PluginModel::where('name', $name)->value('config');
            $dbConfig = json_decode($dbConfig, true);

            $diffConfigKeys = array_diff(array_keys($dbConfig), array_keys($post));
            if (!empty($diffConfigKeys)) {
                foreach ($diffConfigKeys as $value) {
                    $post[$value] = '';
                }
            }

            $result = PluginModel::where('name', $name)->update(['config' => json_encode($post)]);

            if ($result !== false) {
                $this->success('保存成功', url('index'));
            } else {
                $this->error('保存失败');
            }
        } else {
            $pluginClass = get_plugin_class($name);

            // 实例化插件
            $plugin = new $pluginClass;
            $trigger = isset($plugin->trigger) ? $plugin->trigger : [];

            // 插件配置值
            $config = PluginModel::where('name', $name)->value('config');
        
            $config = json_decode($config, true);

            // 插件配置项
            $configItems = include config('plugin_path') . $name . '/config.php';

            return Builder::form()
                ->setPageTitle('插件设置')
                ->addFormItems($configItems)
                ->setFormData($config)
                ->setTrigger($trigger)
                ->fetch();            
        }
    }

    /**
     * 插件管理
     * @param string $name 插件名
     */
    public function manage($name = '')
    {
        if (plugin_action_exist($name, 'Admin', 'index')) {
            $this->redirect(plugin_url($name . '/Admin/index'));
        } else {
            $this->error('管理页面不存在');
        }
    }

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

    /**
     * 设置状态
     * @param string $type 状态类型:enable/disable
     */
    public function setStatus($type = '')
    {
        $ids = $this->request->post('ids');
        empty($ids) && $this->error('缺少主键');

        $status = $type === 'enable' ? 1 : 0;

        $plugin = PluginModel::where('id', '=', $ids)->value('name');

        if ($plugin) {
            HookPluginModel::where('plugin', $plugin)->setField('status', $status);
        }

        $result = PluginModel::where('id', '=', $ids)->setField('status', $status);

        if ($result !== false) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }

    /**
     * 禁用插件/禁用插件数据
     */
    public function disable()
    {
        $this->setStatus('disable');
    }

    /**
     * 启用插件/启用插件数据
     */
    public function enable()
    {
        $this->setStatus('enable');
    }

    /**
     * 检查插件插件信息是否完整
     * @param string $info 插件插件信息
     * @return bool
     */
    private function checkInfo($info = '')
    {
        $items = ['title', 'author', 'version'];
        foreach ($items as $item) {
            if (!isset($info[$item]) || $info[$item] == '') {
                return false;
            }
        }
        return true;
    }
}