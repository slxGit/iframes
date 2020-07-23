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
 * @Last Modified Time: 2020-04-26 16:27:01
 */
 // ------------------------------------------------------------------------

// 应用公共文件

if (!function_exists('get_http_type')) {
    /**
     * 获取协议类型
     * @return string
     */
    function get_http_type()
    {
        return ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
    }
}

if (!function_exists('get_host_domain')) {
    /**
     * 获取主域名
     * @return string
     */
    function get_host_domain(){
        return get_http_type() . $_SERVER['SERVER_NAME'];
    }
}

if (!function_exists('get_file_name')) {
    /**
     * 获取文件名
     * @param int $id 附件ID
     * @return string
     */
    function get_file_name($id = 0)
    {
        return model('system/annex')->getFileName($id);
    }
}

if (!function_exists('get_file_path')) {
    /**
     * 获取附件路径
     * @param int $id 附件ID
     * @param int $type 类型：1-补全域名，0-直接返回数据库记录的地址
     * @return string
     */
    function get_file_path($id = 0, $type = 0)
    {
        return model('system/annex')->getFilePath($id, $type);
    }
}

if (!function_exists('get_thumb_path')) {
    /**
     * 获取图片缩略图路径
     * @param int $id 附件ID
     * @param string $size 缩略国尺寸,如：300x300
     * @param int $type 类型：1-补全域名，0-直接返回数据库记录的地址
     * @return string
     */
    function get_thumb_path($id = 0, $size = '', $type = 0)
    {

        return model('system/annex')->getThumbPath($id, $size, $type);
    }
}

if (!function_exists('delete_file')) {
    /**
     * 删除附件
     * @param string|array $ids 附件ids
     * @return string
     */
    function delete_file($ids = '')
    {
        return model('system/annex')->deleteFile($ids, $type);
    }
}

if (!function_exists('parse_attr')) {
    /**
     * 解析配置
     * @param string $value 配置值
     * @return array|string
     */
    function parse_attr($value = '') {
        $array = preg_split('/[,;\r\n]+/', trim($value, ",;\r\n"));
        if (strpos($value, ':')) {
            $value  = array();
            foreach ($array as $val) {
                list($k, $v) = explode(':', $val);
                $value[$k] = $v;
            }
        } else {
            $value = $array;
        }
        return $value;
    }
}

if (!function_exists('implode_attr')) {
    /**
     * 组合配置
     * @param array $array 配置值
     * @return string
     */
    function implode_attr($array = []) {
        $result = [];
        foreach ($array as $key => $value) {
            $result[] = $key . ':' . $value;
        }
        return empty($result) ? '' : implode(PHP_EOL, $result);
    }
}

if (!function_exists('hook')) { 
    /**
     * 监听钩子
     * @param string $name 钩子名称
     * @param mixed  $params 传入参数
     * @param bool   $once   只获取一个有效返回值
     */
    function hook($name = '', $params = null, $once = false) {
        return \think\facade\Hook::listen($name, $params, $once);
    }
}

if (!function_exists('get_plugin_class')) {
    /**
     * 获取插件类名
     * @param  string $name 插件名
     * @return string
     */
    function get_plugin_class($name)
    {
        return "plugins\\{$name}\\{$name}";
    }
}

if (!function_exists('plugin_url')) {
    /**
     * 生成插件操作链接
     * @param string $url 链接：插件名称/控制器/操作
     * @param array $param 参数
     * @param boolean $admin 是否需要后台登录验证
     * @return string
     */
    function plugin_url($url = '', $param = [], $admin = true)
    {
        $params = [];
        $url = explode('/', $url);

        if (isset($url[0])) {
            $params['_plugin'] = $url[0];
        }
        if (isset($url[1])) {
            $params['_controller'] = $url[1];
        }
        if (isset($url[2])) {
            $params['_action'] = $url[2];
        }

        // 合并参数
        $params = array_merge($params, $param);

        $module = $admin === true ? 'system' : 'portal';

        // 返回url地址
        return url($module . '/plugin/execute', $params);
    }
}

if (!function_exists('plugin_action')) {
    /**
     * 执行插件动作
     * 也可以用这种方式调用：plugin_action('插件名/控制器/动作', [参数1,参数2...])
     * @param string $name 插件名
     * @param string $controller 控制器
     * @param string $action 动作
     * @param mixed $params 参数
     * @return mixed
     */
    function plugin_action($name = '', $controller = '', $action = '', $params = [])
    {   
        if (strpos($name, '/')) {
            $params = is_array($controller) ? $controller : (array)$controller;
            list($name, $controller, $action) = explode('/', $name);
        }
        if (!is_array($params)) {
            $params = (array)$params;
        }
        $class = "plugins\\{$name}\\controller\\{$controller}Controller";
        $obj = new $class;
        return call_user_func_array([$obj, $action], $params);
    }
}

if (!function_exists('plugin_action_exist')) {
    /**
     * 检查插件控制器是否存在某操作
     * @param string $name 插件名
     * @param string $controller 控制器
     * @param string $action 动作
     * @return bool
     */
    function plugin_action_exist($name = '', $controller = '', $action = '')
    {
        if (strpos($name, '/')) {
            list($name, $controller, $action) = explode('/', $name);
        }
        return method_exists("plugins\\{$name}\\controller\\{$controller}Controller", $action);
    }
}

if (!function_exists('plugin_config')) {
    /**
     * 获取或设置某个插件配置参数
     * @param string $name 插件名.配置名
     * @param string $value 设置值
     * @return mixed
     */
    function plugin_config($name = '', $value = '')
    {
        if ($value === '') {
            // 获取插件配置
            if (strpos($name, '.')) {
                list($name, $item) = explode('.', $name);
                return model('system/plugin')->getConfig($name, $item);
            } else {
                return model('system/plugin')->getConfig($name);
            }
        } else {
            // 设置插件配置
            return model('system/plugin')->setConfig($name, $value);
        }
    }
}

if (!function_exists('parse_config')) {
    /**
     * 解析配置，返回默认值
     * @param array $configs 配置
     * @return array
     */
    function parse_config($configs = []) {
        $type = [
            'hidden'      => 2,
            'text'        => 4,
            'tags'        => 4,
            'file'        => 4,
            'date'        => 4,
            'plan'        => 4,
            'icon'        => 4,
            'rate'        => 4,
            'sort'        => 4,
            'bmap'        => 4,
            'image'       => 4,
            'range'       => 4,
            'files'       => 4,
            'plans'       => 4,
            'static'      => 4,
            'number'      => 4,
            'switch'      => 4,
            'images'      => 4,
            'archive'     => 4,
            'layedit'     => 4,
            'ueditor'     => 4,
            'meditor'     => 4,
            'archives'    => 4,
            'password'    => 4,
            'textarea'    => 4,
            'colorpicker' => 4,
            'select'      => 5,
            'xmselect'    => 5,
            'linkage'     => 5,
            'transfer'    => 5,
            'table'       => 5,
            'datatable'   => 5,
            'radio'       => 5,
            'checkbox'    => 5,
            'linkages'    => 6,
        ];
        $result = [];
        foreach ($configs as $item) {
            $configType = $item[0];
            if ($configType == 'group') {
                foreach ($item[1] as $option) {
                    foreach ($option as $group => $val) {
                        $configType = $val[0];
                        $result[$val[1]] = isset($val[$type[$configType]]) ? $val[$type[$configType]] : '';
                    }
                }
            } else {
                $result[$item[1]] = isset($item[$type[$configType]]) ? $item[$type[$configType]] : '';
            }
        }
        return $result;
    }
}