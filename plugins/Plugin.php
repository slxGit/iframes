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
 * @Date:       2020-02-10 12:31:48
 *
 * @Last Modified By:   Aaron
 * @Last Modified Time: 2020-04-01 15:05:49
 */
 // ------------------------------------------------------------------------

namespace plugins;

use think\Container;
use think\Exception;

/**
 * 插件抽象类
 */
abstract class Plugin
{
    /**
     * @var null 视图实例对象
     */
    protected $view = null;

    /**
     * @var string 插件路径
     */
    public $pluginPath = '';

    /**
     * @var string 插件配置文件
     */
    public $configFile = '';

    /**
     * @var string 错误信息
     */
    protected $error = '';

    /**
     * @var array 插件信息
     */
    public $info = [];

    /**
     * @var array 插件钩子
     */
    public $hooks = [];

    /**
     * @var array 触发器（用于配置项）
     */
    public $trigger = [];

    /**
     * 构造方法
     */
    public function __construct()
    {
        $this->view = Container::get('view');
        
        $this->pluginPath = config('plugin_path') . $this->getName() . '/';

        if (is_file($this->pluginPath . 'config.php')) {
            $this->configFile = $this->pluginPath . 'config.php';
        }
    }

    /**
     * 获取插件名称
     * @return string
     */
    final public function getName()
    {
        $class = get_class($this);
        return substr($class, strrpos($class, '\\') + 1);
    }

    /**
     * @return View
     */
    final public function getView()
    {
        return $this->view;
    }

    /**
     * 模板变量赋值
     * @param  mixed $name  变量名
     * @param  mixed $value 变量值
     * @return $this
     */
    final protected function assign($name, $value = '')
    {
        $this->view->assign($name, $value);
        return $this;
    }

    /**
     * 渲染内容输出
     * @param  string $content 内容
     * @param  array  $vars    模板输出变量
     * @param  array  $config  模板参数
     * @return mixed
     */
    final protected function display($content, $vars = [], $config = [])
    {
        return $this->view->display($content, $vars, $config);
    }

    /**
     * 解析和获取模板内容 用于输出
     * @param  string    $template 模板文件名或者内容
     * @param  array     $vars     模板输出变量
     * @param  array     $config     模板参数
     * @param  bool      $renderContent     是否渲染内容
     * @return string
     * @throws \Exception
     */
    final protected function fetch($template = '', $vars = [], $replace = [], $config = [], $renderContent = false)
    {
        if ($template != '') {
            $template = $this->pluginPath . 'view/' . ltrim($template, '/') . '.' . config('template.view_suffix');
          
            // 模板不存在 抛出异常
            if (!is_file($template)) {
                throw new Exception('模板不存在：' . $template);
            }

            return $this->view->fetch($template, $vars, $replace, $config, $renderContent);
        }
    }

    /**
     * 获取插件配置值，先从数据库获取，如果没有则从插件配置文件获取
     * @param string $name 插件名称
     * @return array|mixed
     */
    final public function getConfig($name = '')
    {
        if(empty($name)){
            $name = $this->getName();
        }

        $config = plugin_config($name);

        if (!$config) {
            if ($this->configFile != '') {
                $fileConfig = include $this->configFile;
            }

            if (isset($fileConfig) && !empty($fileConfig)) {
                $config = parse_config($fileConfig);
            }
        }
       
        return $config;
    }

    /**
     * 获取错误信息
     * @return string
     */
    final public function getError()
    {
        return $this->error;
    }

    /**
     * 必须实现安装方法
     * @return mixed
     */
    abstract public function install();

    /**
     * 必须实现卸载方法
     * @return mixed
     */
    abstract public function uninstall();
}