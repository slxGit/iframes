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
 * @Last Modified Time: 2020-04-01 14:55:43
 */
 // ------------------------------------------------------------------------

namespace app\common\controller;

use think\Exception;
use app\common\controller\BaseController;

class PluginBaseController extends BaseController
{
    protected $plugin;

    /**
     * 初始化
     */
    protected function initialize()
    {
        parent::initialize();

        $this->getPlugin();

        $this->view = $this->plugin->getView();
    }

    /**
     * 获取插件类的实例化对象
     */
    protected function getPlugin()
    {
        if (is_null($this->plugin)) {
            $pluginName = $this->request->param('_plugin');
            $pluginClass = get_plugin_class($pluginName);
            $this->plugin = new $pluginClass;
        }
        return $this->plugin;
    }

    /**
     * 获取插件名称
     * @return string
     */
    protected function getPluginName()
    {
        return $this->plugin->getName();
    }

    /**
     * 模板变量赋值
     * @param mixed $name 要显示的模板变量
     * @param mixed $value 变量的值
     * @return void
     */
    protected function assign($name, $value = '')
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
    protected function display($content = '', $vars = [], $config = [])
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
    protected function fetch($template = '', $vars = [], $replace = [], $config = [], $renderContent = false)
    {
        $template = $this->parseTemplate($template);

        // 模板不存在 抛出异常
        if (!is_file($template)) {
            throw new Exception('template not exists:' . $template);
        }

        return $this->view->fetch($template, $vars, $replace, $config, $renderContent);
    }

    /**
     * 自动定位模板文件
     * @access private
     * @param string $template 模板文件规则
     * @return string
     */
    private function parseTemplate($template)
    {
        // 分析模板文件规则
        $viewEngineConfig = config('template.');

        $depr = $viewEngineConfig['view_depr'];

        $path = $this->plugin->pluginPath . 'view/';

        $controller = $this->request->param('_controller');
        $action     = $this->request->param('_action');

        if (0 !== strpos($template, '/')) {
            $template   = str_replace(['/', ':'], $depr, $template);
            $controller = \think\Loader::parseName($controller);
            if ($controller) {
                if ('' == $template) {
                    // 如果模板文件名为空 按照默认规则定位
                    $template = str_replace('.', DIRECTORY_SEPARATOR, $controller) . $depr . $action;
                } elseif (false === strpos($template, $depr)) {
                    $template = str_replace('.', DIRECTORY_SEPARATOR, $controller) . $depr . $template;
                }
            }
        } else {
            $template = str_replace(['/', ':'], $depr, substr($template, 1));
        }

        return $path . ltrim($template, '/') . '.' . ltrim($viewEngineConfig['view_suffix'], '.');
    }

    /**
     * 设置验证失败后是否抛出异常
     * @access protected
     * @param  bool $fail 是否抛出异常
     * @return $this
     */
    protected function validateFailException($fail = true)
    {
        $this->failException = $fail;

        return $this;
    }

    /**
     * 验证数据
     * @access protected
     * @param  array        $data     数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * @param  array        $message  提示信息
     * @param  bool         $batch    是否批量验证
     * @param  mixed        $callback 回调方法（闭包）
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate($data, $validate, $message = [], $batch = false, $callback = null)
    {
        if (is_array($validate)) {
            $v = $this->app->validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                list($validate, $scene) = explode('.', $validate);
            }
            $v = $this->app->validate('\\plugins\\' . $this->plugin->getName() . '\\validate\\' . $validate . 'Validate');
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        if (is_array($message)) {
            $v->message($message);
        }

        if ($callback && is_callable($callback)) {
            call_user_func_array($callback, [$v, &$data]);
        }

        if (!$v->check($data)) {
            if ($this->failException) {
                throw new ValidateException($v->getError());
            }
            return $v->getError();
        }

        return true;
    }
}