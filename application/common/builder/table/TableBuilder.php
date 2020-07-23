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
 * @Last Modified Time: 2020-04-26 15:16:52
 */
 // ------------------------------------------------------------------------

namespace app\common\builder\table;

use app\common\builder\Builder;

/**
 * 表格构建器
 */
class TableBuilder extends Builder
{
    /**
     * @var string 当前模型名称
     */
    private $module = '';

    /**
     * @var string 当前控制器名称
     */
    private $controller = '';

    /**
     * @var string 当前操作名称
     */
    private $action = '';

    /**
     * @var string 数据表名
     */
    private $tableName = '';

	/**
     * @var string 模板路径
     */
    private $template = '';

    /**
     * @var string public目录
     */
    private $publicPath = '';

    /**
     * @var string 左侧复选框
     */
    private $leftColumnCheckbox = false;

	/**
     * @var array 模板变量
     */
    public $vars = [
        // 页面标题
        'page_title'                  => '',
        // 页面tab导航
        'tab_nav'                     => [],
        // 表格列集合
        'table_columns'               => [],    
        // 表格数据
        'table_data'                  => [],
        // 表格配置              
        'table_configs'               => [],
        // 分页数据
        'pages'                       => '',
        // 表格搜索项
        'search_items'                => [],
        // 搜索栏的跳转地址
        'search_url'                  => '',
        // 快捷修改的地址
        'quick_edit_url'              => '',
        // 表格工具按钮
        'tool_buttons'                => [],
        // 表格顶部按钮
        'top_buttons'                 => [],
        // 表格右侧按钮
        'right_buttons'               => [],
        // 表格右侧工具栏
        'right_toolbar'               => [],
        // 要替换的右侧按钮
        'replace_right_buttons'       => [],       
        // 需要加载的js
        'js_files'                    => [],
        // 需要加载的css
        'css_files'                   => [],
        // 需要引入的js
        'js_list'                     => [],
        // 需要引入的css
        'css_list'                    => [],
        // 需要引入的js代码
        'js_code'                     => '',
        // 需要引入的css代码
        'css_code'                    => ''
    ];

    /**
     * 初始化
     */
    public function initialize()
    {
        $this->module     = $this->request->module();
        $this->controller = parse_name($this->request->controller());
        $this->action     = $this->request->action();
        $this->setTableName(); 
        $baseFile = $this->request->baseFile();
        $this->publicPath = substr($baseFile, 0, strripos($baseFile, '/') + 1);
        $this->template = __DIR__ . '/layout.html';
        $this->vars['search_url'] = $this->getDefaultUrl();
    }

    /**
     * 设置数据库表名
     * @param string $name 表名
     * @return $this
     */
    public function setTableName($name = '')
    {
        if ($name == '') {
            $controller = $this->controller;
            if (strpos($controller, 'admin_') === 0) {
                $controller = substr($controller, 6);
            }
            $this->tableName = $this->module . '_' . $controller;
        } else {
            $this->tableName = $name;
        }
    
        return $this;
    }

    /**
     * 设置表格配置
     * @param array $configs 配置项
     * @return $this
     */
    public function setTableConfigs($configs = [])
    {
        if (!empty($configs)) {
            $this->vars['table_configs'] = $configs;
        }

        return $this;
    }

    /**
     * 设置页面标题
     * @param string $title 页面标题
     * @return $this
     */
    public function setPageTitle($title = '')
    {
        if ($title != '') {
            $this->vars['page_title'] = trim($title);
        }

        return $this;
    }

    /**
     * 设置Tab按钮列表
     * @param array $tabList Tab列表 如：['tab1' => ['title' => '标题', 'url' => 'https://www.baidu.com']]
     * @param string $currTab 当前tab名
     * @return $this
     */
    public function setTabNav($tabList = [], $currTab = '')
    {
        if (!empty($tabList)) {
            $this->vars['tab_nav'] = [
                'tab_list' => $tabList,
                'curr_tab' => $currTab,
            ];
        }

        return $this;
    }

    /**
     * 设置搜索地址
     * @param string $url 搜索地址
     * @return $this
     */
    public function setSearchUrl($url = '')
    {
        if ($url != '') {
            $this->vars['search_url'] = $url;
        }

        return $this;
    }

    /**
     * 设置快捷修改的地址
     * @param string $url 修改地址
     * @return $this
     */
    public function setQuickEditUrl($url = '')
    {
        if ($url != '') {
            $this->vars['quick_edit_url'] = $url;
        }

        return $this;
    }

    /**
     * 设置右侧操作栏
     * @param array $configs 配置参数
     * @return $this
     */
    public function setRightToolbar($configs = [])
    {
        if (!empty($configs)) {
            $this->vars['right_toolbar'] = $configs;
        }

        return $this;
    }

    /**
     * 设置分页
     * @param string $pages 分页数据
     * @return $this
     */
    public function setPages($pages = '')
    {
        if ($pages !== '') {
            $this->vars['pages'] = $pages;
        }
        return $this;
    }

    /**
     * 引入css文件
     * @param string $filesPath 文件路径，多个用逗号隔开,所有文件均位于static目录下。
     * @return $this
     */
    public function setCssList($filesPath = '')
    {
        if ($filesPath != '') {
            $this->loadFile('css', $filesPath);
        }

        return $this;
    }

    /**
     * 引入js文件
     * @param string $filesPath 文件路径，多个用逗号隔开,所有文件均位于static目录下。
     * @return $this
     */
    public function setJsList($filesPath = '')
    {
        if ($filesPath != '') {
            $this->loadFile('js', $filesPath);
        }

        return $this;
    }

    /**
     * 引入css或js文件
     * @param string $type 类型：css/js
     * @param mixed $filesPath 文件路径，多个用逗号隔开,所有文件均位于static目录下。
     */
    private function loadFile($type = '', $filesPath = '')
    {
        if ($filesPath != '') {
            if (!is_array($filesPath)) {
                $filesPath = array_map('trim', explode(',', $filesPath));
            }

            foreach ($filesPath as $path) {
                $this->vars[$type . '_list'][] = $this->publicPath . 'static/'. $path . '.' . $type;
            }
        }
    }

    /**
     * 设置CSS代码
     * @param string $cssCode 额外CSS代码
     * @return $this
     */
    public function setCssCode($cssCode = '')
    {
        if ($cssCode != '') {
            $this->vars['css_code'] = $cssCode;
        }

        return $this;
    }

    /**
     * 设置JS代码
     * @param string $jsCode 额外JS代码
     * @return $this
     */
    public function setJsCode($jsCode = '')
    {
        if ($jsCode != '') {
            $this->vars['js_code'] = $jsCode;
        }

        return $this;
    }

    /**
     * 获取默认url
     * @param string $type 按钮类型：add/enable/disable/delete
     * @param array $params 参数
     * @return string
     */
    private function getDefaultUrl($type = '', $params = [])
    {
        if ($type == '') {
            $type = $this->action;
        }

        $url = $this->module . '/' . $this->controller . '/' . $type;

        if (!empty($params)) {
            $params = array_filter($params, function($v) {
                return $v !== '';
            });
        }

        return url($url, $params);
    }

    /**
     * 添加单行文本框搜索项
     * @param string $name 表单项名
     * @param string $title 标题
     * @param string $default 默认值
     * @param int $md 宽度比例 最大12
     * @return mixed
     */
    public function addSearchText($name = '', $title = '', $md = 3)
    {
        if (preg_match('/(.*)\[:(.*)\]/', $title, $matches)) {
            $title       = $matches[1];
            $placeholder = $matches[2];
        }

        $item = [
            'type'        => 'text',
            'name'        => $name,
            'title'       => $title,
            'placeholder' => isset($placeholder) ? $placeholder : '请输入'. $title,
            'md'          => $md
        ];

        $this->vars['search_items'][] = $item;

        return $this;
    }

    /**
     * 添加下拉菜单搜索项
     * @param string $name 下拉菜单名
     * @param string $title 标题
     * @param array $options 选项
     * @param int $md 宽度比例 最大12
     * @return mixed
     */
    public function addSearchSelect($name = '', $title = '', $options = [], $md = 3)
    {
        if (preg_match('/(.*)\[:(.*)\]/', $title, $matches)) {
            $title       = $matches[1];
            $placeholder = $matches[2];
        }

        $item = [
            'type'        => 'select',
            'name'        => $name,
            'title'       => $title,
            'options'     => $options == '' ? [] : $options,
            'placeholder' => isset($placeholder) ? $placeholder : '请选择或搜索',
            'md'          => $md
        ];

        $this->vars['search_items'][] = $item;
        
        return $this;
    }

    /**
     * 添加xm下拉菜单搜索项
     * @param string $name 下拉菜单名
     * @param string $title 标题
     * @param array $options 选项参数
     * @param array $configs 配置参数
     * @param int $md 宽度比例 最大12
     * @return mixed
     */
    public function addSearchXmselect($name = '', $title = '', $options = [], $configs = [], $md = 3)
    {
        if (preg_match('/(.*)\[:(.*)\]/', $title, $matches)) {
            $title       = $matches[1];
            $placeholder = $matches[2];
        }

        if (!empty($options)) {
            if (count($options) == count($options, 1)) {
                $optionsData = [];
                foreach ($options as $k => $v) {
                    $optionsData[] = [
                        'name' => $v,
                        'value' => $k
                    ];
                }
                $options = $optionsData;
            }
        }

        $item = [
            'type'        => 'xmselect',
            'name'        => $name,
            'title'       => $title,
            'options'     => json_encode($options),
            'configs'     => json_encode($configs),
            'placeholder' => isset($placeholder) ? $placeholder : '请选择' . $title,
            'md'          => $md
        ];

        $this->vars['search_items'][] = $item;
        
        return $this;
    }

    /**
     * 添加日期搜索项
     * @param string $name 表单项名
     * @param string $title 标题
     * @param string $configs 配置参数
     * @param int $md 宽度比例 最大12
     * @return mixed
     */
    public function addSearchDate($name = '', $title = '', $configs = [], $md = 3)
    {
        if (preg_match('/(.*)\[:(.*)\]/', $title, $matches)) {
            $title       = $matches[1];
            $placeholder = $matches[2];
        }

        $item = [
            'type'        => 'date',
            'name'        => $name,
            'title'       => $title,
            'configs'     => json_encode($configs),
            'placeholder' => isset($placeholder) ? $placeholder : '请选择日期',
            'md'          => $md
        ];

        $this->vars['search_items'][] = $item;
        
        return $this;
    }

    /**
     * 添加搜索项
     * 第一个参数传搜索项类型，其余参数与各自方法中的参数一致
     * @param string $type 表单项类型
     * @return $this
     */
    public function addSearchItem($type = '')
    {
        if ($type != '') {
            $args = func_get_args();
            array_shift($args);
            $method = 'addSearch'. ucfirst($type);
            call_user_func_array([$this, $method], $args);
        }

        return $this;
    }

    /**
     * 一次性添加多个搜索标签项
     * @param array $items 表单项
     * @return $this
     */
    public function addSearchItems($items = [])
    {
        if (!empty($items)) {
            foreach ($items as $item) {
                call_user_func_array([$this, 'addSearchItem'], $item);
            }
        }

        return $this;
    }

    /**
     * 创建表名Token
     * @return bool|string
     */
    private function createTableToken()
    {
        $data = [
            'table'      => $this->tableName,
            'module'     => $this->module,
            'controller' => $this->controller,
            'action'     => $this->action,
        ];

        $tableToken = substr(sha1($this->module . '-' . $this->controller . '-' . $this->action . '-' . $this->tableName), 0, 8);

        session($tableToken, $data);

        return $tableToken;
    }

    /**
     * 添加一个顶部按钮
     * @param string $type 按钮类型：add/enable/disable/back/delete
     * @param array $attribute 按钮属性 title:标题 class:类 url:请求地址 confirm:是否确认
     *        method:href-跳转页面,open-框架内新打开页面,open_title-页面标题（配合open使用）,back-返回上一页
     *        ajax-接口请求,param-请求参数,complete-请求成功的事件refresh/delete
     * @return $this
     */
    public function addTopButton($type = '', $attribute = [])
    {
        $tableToken = $this->createTableToken();

        switch ($type) {
            // 新增按钮
            case 'add':
                // 默认属性
                $btnAttribute = [
                    'title'   => '新增',
                    'class'   => 'layui-btn layui-btn-normal layui-btn-sm',
                    'url'     => $this->getDefaultUrl($type),
                    'method'  => 'href'
                ];
                break;

            // 启用按钮
            case 'enable':
                // 默认属性
                $btnAttribute   = [
                    'title'     => '启用',
                    'class'     => 'layui-btn layui-btn-sm',
                    'url'       => $this->getDefaultUrl($type),
                    'method'    => 'ajax',
                    'param'     => [
                        'ids'   => '__id__',
                        '_t'    => $tableToken
                    ],
                    'complete'  => 'refresh'
                ];
                break;

            // 禁用按钮
            case 'disable':
                // 默认属性
                $btnAttribute   = [
                    'title'     => '禁用',
                    'class'     => 'layui-btn layui-btn-warm layui-btn-sm',
                    'url'       => $this->getDefaultUrl($type),
                    'method'    => 'ajax',
                    'param'     => [
                        'ids'   => '__id__',
                        '_t'    => $tableToken
                    ],
                    'complete'  => 'refresh'
                ];
                break;

            // 删除按钮(不可恢复)
            case 'delete':
                // 默认属性
                $btnAttribute  = [
                    'title'    => '删除',
                    'class'    => 'layui-btn layui-btn-danger layui-btn-sm',
                    'url'      => $this->getDefaultUrl($type),
                    'confirm'  => true,
                    'method'   => 'ajax',
                    'param'    => [
                        'ids'  => '__id__',
                        '_t'   => $tableToken
                    ],
                    'complete' => 'delete'

                ];
                break;

            // 返回按钮
            case 'back':
                // 默认属性
                $btnAttribute = [
                    'title'   => '返回',
                    'class'   => 'layui-btn layui-btn-primary layui-btn-sm',
                    'url'     => '',
                    'method'  => 'back'
                ];
                break;

            // 自定义按钮
            default:
                // 默认属性
                $btnAttribute = [
                    'title'   => '自定义按钮',
                    'class'   => 'layui-btn layui-btn-sm',
                    'url'     => $this->getDefaultUrl($type),
                    'method'  => 'href'
                ];
                break;
        }

        // 自定义字段名
        if (isset($attribute['field'])) {
            $btnAttribute['param']['field'] = $attribute['field'];
            unset($attribute['field']);
        }

        // 合并自定义属性
        if ($attribute && is_array($attribute)) {
            $btnAttribute = array_merge($btnAttribute, $attribute);
        }

        // 将传递参数转为JSON格式
        if (isset($btnAttribute['param'])) {
            $btnAttribute['param'] = json_encode($btnAttribute['param']);
            if(preg_match('/__(.*?)__/', $btnAttribute['param'])) {
                $this->leftColumnCheckbox = true;
            }
        }

        $this->vars['top_buttons'][] = $btnAttribute;

        return $this;
    }

    /**
     * 一次性添加多个顶部按钮
     * @param array|string $buttons 按钮类型
     * @return $this
     */
    public function addTopButtons($buttons = [])
    {
        if (!empty($buttons)) {
            $buttons = is_array($buttons) ? $buttons : array_map('trim', explode(',', $buttons));
            foreach ($buttons as $key => $value) {
                if (is_numeric($key)) {
                    $this->addTopButton($value);
                } else {
                    $this->addTopButton($key, $value);
                }
            }
        }

        return $this;
    }

    /**
     * 添加一个右侧按钮
     * @param string $type 按钮类型：edit/enable/disable/delete
     * @param array $attribute 按钮属性 title:标题 class:类 url:请求地址 confirm:是否确认 
     *        method:href-跳转页面,open-框架内新打开页面,open_title-页面标题（配合open使用）
     *        ajax-接口请求,param-请求参数,complete-请求成功的事件refresh/delete
     * @return $this
     */
    public function addRightButton($type = '', $attribute = [])
    {
        $tableToken = $this->createTableToken();

        switch ($type) {
            // 编辑按钮
            case 'edit':
                // 默认属性
                $btnAttribute = [
                    'type'    => 'edit',
                    'title'   => '编辑',
                    'class'   => 'layui-btn layui-btn-normal layui-btn-xs',
                    'url'     => $this->getDefaultUrl($type, ['id' => '__id__']),
                    'method'  => 'href'
                ];
                break;

            // 启用按钮
            case 'enable':
                // 默认属性
                $btnAttribute   = [
                    'type'      => 'enable',
                    'title'     => '启用',
                    'class'     => 'layui-btn layui-btn-xs',
                    'url'       => $this->getDefaultUrl($type),
                    'method'    => 'ajax',
                    'param'     => [
                        'ids'   => '__id__',
                        '_t'    => $tableToken
                    ],
                    'complete'  => 'refresh'
                ];
                break;

            // 禁用按钮
            case 'disable':
                // 默认属性 
                $btnAttribute   = [
                    'type'      => 'disable',
                    'title'     => '禁用',
                    'class'     => 'layui-btn layui-btn-warm layui-btn-xs',
                    'url'       => $this->getDefaultUrl($type),
                    'method'    => 'ajax',
                    'param'     => [
                        'ids'   => '__id__',
                        '_t'    => $tableToken
                    ],
                    'complete'  => 'refresh'
                ];
                break;

            // 删除按钮
            case 'delete':
                // 默认属性
                $btnAttribute  = [
                    'type'     => 'delete',
                    'title'    => '删除',
                    'class'    => 'layui-btn layui-btn-danger layui-btn-xs',
                    'url'      => $this->getDefaultUrl($type),
                    'confirm'  => true,
                    'method'   => 'ajax',
                    'param'    => [
                        'ids'  => '__id__',
                        '_t'   => $tableToken
                    ],
                    'complete' => 'delete'
                ];
                break;

            // 自定义按钮
            default:
                // 默认属性
                $btnAttribute = [
                    'type'    => $type,
                    'title'   => '自定义按钮',
                    'class'   => 'layui-btn layui-btn-xs',
                    'url'     => $this->getDefaultUrl($type),
                    'method'  => 'href'
                ];
                break;
        }

        // 自定义字段名
        if (isset($attribute['field'])) {
            $btnAttribute['param']['field'] = $attribute['field'];
            unset($attribute['field']);
        }

        // 合并自定义属性
        if ($attribute && is_array($attribute)) {
            $btnAttribute = array_merge($btnAttribute, $attribute);
        }

        // 将传递参数转为JSON格式
        if (isset($btnAttribute['param'])) {
            $btnAttribute['param'] = json_encode($btnAttribute['param']);
        }

        $this->vars['right_buttons'][] = $btnAttribute;

        return $this;
    }

    /**
     * 一次性添加多个右侧按钮
     * @param array|string $buttons 按钮类型
     * @return $this
     */
    public function addRightButtons($buttons = [])
    {
        if (!empty($buttons)) {
            $buttons = is_array($buttons) ? $buttons : array_map('trim', explode(',', $buttons));
            foreach ($buttons as $key => $value) {
                if (is_numeric($key)) {
                    $this->addRightButton($value);
                } else {
                    $this->addRightButton($key, $value);
                }
            }
        }
        
        return $this;
    }

    /**
     * 替换右侧按钮
     * @param array $condition 条件，格式为：['字段名' => ['字段值1,字段值2']]
     * @param string|array $content 要替换的内容:'<button>按钮</button>' | ['enable','delete']
     * @param null $target 要替换的目标按钮
     * @return $this
     */
    public function replaceRightButton($condition = [], $content = '')
    {
        foreach ($condition as &$v) {
            if (!is_array($v)) {
                $v = [$v];
            }
        }

        $this->vars['replace_right_buttons'][] = [
            'condition' => $condition,
            'content'   => $content            
        ];

        return $this;
    }

    /**
     * 批量替换右侧按钮
     * @param array $buttons 替换的条件与内容
     * @return $this
     */
    public function replaceRightButtons($buttons)
    {   
        if (!empty($buttons)) {
            foreach ($buttons as $value) {
                $this->replaceRightButton($value[0], $value[1]);
            }
        }
        
        return $this;
    }

    /**
     * 添加一个工具按钮
     * @param string $type 按钮类型：filter/print/exports
     * @return $this
     */
    public function addToolButton($type = '')
    {
        $this->vars['tool_buttons'][] = $type;

        return $this;
    }

    /**
     * 一次性添加多个工具按钮
     * @param array|string $buttons 按钮类型
     * @return $this
     */
    public function addToolButtons($buttons = [])
    {
        if (!empty($buttons)) {
            $buttons = is_array($buttons) ? $buttons : array_map('trim', explode(',', $buttons));
            foreach ($buttons as $value) {
                $this->addToolButton($value);
            }
        }
        
        return $this;
    }

    /**
     * 添加普通列
     * @param string $name 字段名称
     * @param string $title 列标题
     * @param string $default 默认值
     * @param array $configs 配置参数
     * @return $this
     */
    public function addCommon($name = '', $title = '', $default = '', $configs = [])
    {
        $column = [
            'type'  => 'common',
            'value' => $default,
            'cols'  => [
                'field' => $name,
                'title' => $title
            ]
        ];
      
        if (!empty($configs)) {
            $column['cols'] = array_merge($column['cols'], $configs);
        }

        $this->vars['table_columns'][] = $column;

        return $this;
    }

    /**
     * 添加序列
     * @param string $name 字段名称
     * @param string $title 列标题
     * @param array $configs 配置参数
     * @return $this
     */
    public function addIndex($name = '', $title = '', $configs = [])
    {
        $column = [
            'type'  => 'index',
            'cols'  => [
                'field'   => $name,
                'title'   => $title,
                'templet' => "<div>{{templet_render(`index`, d)}}</div>"
            ]
        ];

        if (!empty($configs)) {
            $column['cols'] = array_merge($column['cols'], $configs);
        }

        $this->vars['table_columns'][] = $column;

        return $this;
    }

    /**
     * 添加文本列
     * @param string $name 字段名称
     * @param string $title 列标题
     * @param string $default 默认值
     * @param array $configs 配置参数
     * @return $this
     */
    public function addText($name = '', $title = '', $default = '', $configs = [])
    {
        $params = json_encode(['_t' => $this->createTableToken()]);

        $column = [
            'type'  => 'text',
            'value' => $default,
            'cols'  => [
                'field' => $name,
                'title' => $title,
                'edit'  => 'text',
                'templet' => "<div>{{# var params = `{$params}`}}{{templet_render(`text`, d, `{$name}`, [], params)}}</div>"
            ]
        ];
      
        if (!empty($configs)) {
            $column['cols'] = array_merge($column['cols'], $configs);
        }

        $this->vars['table_columns'][] = $column;

        return $this;
    }

    /**
     * 添加标签
     * @param string $name 字段名称
     * @param string $title 列标题
     * @param string $default 默认值
     * @param array $configs 配置参数
     * @return $this
     */
    public function addTag($name = '', $title = '', $default = '', $class = '', $configs = [])
    {
        $params = json_encode(['class' => $class]);

        if (!is_array($default) && !empty($default)) {
            $default = explode(',', $default);
        }

        $column = [
            'type'  => 'tag',
            'value' => $default,
            'cols'  => [
                'field' => $name,
                'title' => $title,
                'templet' => "<div>{{# var params = {$params}}}{{templet_render(`tag`, d, `{$name}`, [], params)}}</div>"
            ]
        ];
        
        if (!empty($configs)) {
            $column['cols'] = array_merge($column['cols'], $configs);
        }

        $this->vars['table_columns'][] = $column;

        return $this;
    }

    /**
     * 添加链接
     * @param string $name 字段名称
     * @param string $title 列标题
     * @param string $default 默认值
     * @param string $method 打开方式 href-跳转页面,open-框架内新打开页面
     * @param array $configs 配置参数
     * @return $this
     */
    public function addLink($name = '', $title = '', $default = '', $method = '', $configs = [])
    {
        if ($method == '') $method = 'open';

        $column = [
            'type'  => $method,
            'value' => is_array($default) ? $default : explode(',', $default),
            'cols'  => [
                'field' => $name,
                'title' => $title,
                'templet' => "<div>{{templet_render(`{$method}`, d, `{$name}`)}}</div>"
            ]
        ];
      
        if (!empty($configs)) {
            $column['cols'] = array_merge($column['cols'], $configs);
        }

        $this->vars['table_columns'][] = $column;

        return $this;
    }

    /**
     * 添加图片
     * @param string $name 字段名称
     * @param string $title 列标题
     * @param string $default 默认值
     * @param int $valueType 赋值类型 1-值为附件ID 2-值为链接地址
     * @param array $configs 配置参数
     * @return $this
     */
    public function addImage($name = '', $title = '', $default = '', $valueType = '', $configs = [])
    {
        $valueType == '' && $valueType = 1;

        if ($valueType == 1) {
            if ($default != '') {
                $default = get_file_path($default);
                if (!$default) {
                    $default = '/static/admin/img/default.png';
                }
            }
        }

        $column = [
            'type'        => 'image',
            'value'       => $default,
            'cols'        => [
                'field'   => $name,
                'title'   => $title,
                'templet' => "<div>{{templet_render(`image`, d, `{$name}`)}}</div>"
            ],
            'value_type'  => $valueType
        ];
        
        if (!empty($configs)) {
            $column['cols'] = array_merge($column['cols'], $configs);
        }

        $this->vars['table_columns'][] = $column;

        return $this;
    }

    /**
     * 添加下载
     * @param string $name 字段名称
     * @param string $title 列标题
     * @param string $default 默认值
     * @param array $configs 配置参数
     * @return $this
     */
    public function addDownload($name = '', $title = '', $default = '', $configs = [])
    {
        $column = [
            'type'    => 'download',
            'value'   => is_array($default) ? $default : explode(',', $default),
            'cols'    => [
                'field'   => $name,
                'title'   => $title,
                'templet' => "<div>{{templet_render(`download`, d, `{$name}`)}}</div>"
            ]
        ];
      
        if (!empty($configs)) {
            $column['cols'] = array_merge($column['cols'], $configs);
        }

        $this->vars['table_columns'][] = $column;

        return $this;
    }

    /**
     * 添加开关
     * @param string $name 字段名称
     * @param string $title 列标题
     * @param string $default 默认值
     * @param array $configs 配置参数
     * @return $this
     */
    public function addSwitch($name = '', $title = '', $default = '', $configs = [])
    {
        $params = json_encode(['_t' => $this->createTableToken()]);

        $column = [
            'type'    => 'switch',
            'value'   => $default,
            'cols'    => [
                'field'   => $name,
                'title'   => $title,
                'templet' => "<div>{{# var params = `{$params}`}}{{templet_render(`switch`, d, `{$name}`, [], params)}}</div>"
            ]
        ];
      
        if (!empty($configs)) {
            $column['cols'] = array_merge($column['cols'], $configs);
        }

        $this->vars['table_columns'][] = $column;

        return $this;
    }

    /**
     * 添加复选框
     * @param string $name 字段名称
     * @param string $title 列标题
     * @param string $default 默认值
     * @param array $configs 配置参数
     * @return $this
     */
    public function addCheckbox($name = '', $title = '', $default = '', $configs = [])
    {
        $params = json_encode(['_t' => $this->createTableToken()]);

        $column = [
            'type'    => 'checkbox',
            'value'   => $default,
            'cols'    => [
                'field'   => $name,
                'title'   => $title,
                'templet' => "<div>{{# var params = `{$params}`}}{{templet_render(`checkbox`, d, `{$name}`, [], params)}}</div>"
            ]
        ];
      
        if (!empty($configs)) {
            $column['cols'] = array_merge($column['cols'], $configs);
        }

        $this->vars['table_columns'][] = $column;

        return $this;
    }

    /**
     * 添加下拉菜单
     * @param string $name 字段名称
     * @param string $title 列标题
     * @param string $default 默认值
     * @param array $configs 配置参数
     * @return $this
     */
    public function addSelect($name = '', $title = '', $options = [], $default = '', $configs = [])
    {
        $options = json_encode($options, JSON_UNESCAPED_UNICODE);

        $params = json_encode(['_t' => $this->createTableToken()]);

        $column = [
            'type'    => 'select',
            'value'   => $default,
            'cols'    => [
                'field'   => $name,
                'title'   => $title,
                'templet' => "<div>{{# var options = {$options};var params = `{$params}`}}{{templet_render(`select`, d, `{$name}`, options, params)}}</div>"
            ]
        ];
      
        if (!empty($configs)) {
            $column['cols'] = array_merge($column['cols'], $configs);
        }

        $this->vars['table_columns'][] = $column;

        return $this;
    }

    /**
     * 添加xm下拉菜单
     * @param string $name 字段名称
     * @param string $title 列标题
     * @param string $default 默认值
     * @param array $params 额外参数
     * @param array $configs 配置参数
     * @return $this
     */
    public function addXmselect($name = '', $title = '', $options = [], $default = '', $params = [], $configs = [])
    {
        if (!empty($options)) {
            if (count($options) == count($options, 1)) {
                $optionsData = [];
                foreach ($options as $k => $v) {
                    $optionsData[] = [
                        'name' => $v,
                        'value' => $k
                    ];
                }
                $options = $optionsData;
            }
        }

        $options = json_encode($options, JSON_UNESCAPED_UNICODE);

        empty($params) && $params = [];

        $params['_t'] = $this->createTableToken();

        $params = urlencode(json_encode($params, JSON_UNESCAPED_UNICODE));

        $column = [
            'type'    => 'xmselect',
            'value'   => is_array($default) ? $default : explode(',', $default),
            'cols'    => [
                'field'   => $name,
                'title'   => $title,
                'templet' => "<div>{{# var options = `{$options}`; var params = `{$params}`}}{{templet_render(`xmselect`, d, `{$name}`, options, params)}}</div>"
            ]
        ];
   
        if (!empty($configs)) {
            $column['cols'] = array_merge($column['cols'], $configs);
        }

        $this->vars['table_columns'][] = $column;

        return $this;
    }

    /**
     * 添加日期列
     * @param string $name 字段名称
     * @param string $title 列标题
     * @param string $default 默认值
     * @param array $params 额外参数
     * @param array $configs 配置参数
     * @return $this
     */
    public function addDate($name = '', $title = '', $default = '', $params = [], $configs = [])
    {
        empty($params) && $params = [];

        $params['_t'] = $this->createTableToken();

        $params = json_encode($params, JSON_UNESCAPED_UNICODE);
       
        $column = [
            'type'  => 'date',
            'value' => $default,
            'cols'  => [
                'field' => $name,
                'title' => $title,
                'templet' => "<div>{{# var params = `{$params}`}}{{templet_render(`date`, d, `{$name}`, [], params)}}</div>"
            ]
        ];

        if (!empty($configs)) {
            $column['cols'] = array_merge($column['cols'], $configs);
        }

        $this->vars['table_columns'][] = $column;

        return $this;
    }

    /**
     * 添加判断列
     * @param string $name 字段名称
     * @param string $title 列标题
     * @param string $default 默认值
     * @param array $configs 配置参数
     * @return $this
     */
    public function addJudge($name = '', $title = '', $default = '', $configs = [])
    {
        $column = [
            'type'  => 'judge',
            'value' => $default,
            'cols'  => [
                'field' => $name,
                'title' => $title,
                'templet' => "<div>{{templet_render(`judge`, d, `{$name}`)}}</div>"
            ]
        ];
      
        if (!empty($configs)) {
            $column['cols'] = array_merge($column['cols'], $configs);
        }

        $this->vars['table_columns'][] = $column;

        return $this;
    }

    /**
     * 添加一列
     * @param string $name 字段名称
     * @param string $title 列标题
     * @param string $type 模板类型
     * @return $this
     */
    public function addColumn($name = '', $title = '', $type = '')
    {  
        if ($type == '') $type = 'common';

        $args = func_get_args();
        unset($args[2]);
        $method = 'add'. ucfirst($type);
        call_user_func_array([$this, $method], $args);

        return $this;
    }

    /**
     * 一次性添加多列
     * @param array $columns 数据列
     * @return $this
     */
    public function addColumns($columns = [])
    {
        if (!empty($columns)) {
            foreach ($columns as $column) {
                call_user_func_array([$this, 'addColumn'], $column);
            }
        }

        return $this;
    }

    /**
     * 设置表格数据列表
     * @param array|object $tableData 表格数据
     * @return $this
     */
    public function setTableData($tableData = null)
    {
        if ($tableData !== null) {
            // 转为数组后的表格数据
            $this->vars['table_data'] = $this->toArray($tableData);
            if ($tableData instanceof \think\paginator) {
                // 添加URL参数
                $tableData->appends(request()->param());
                // 设置分页
                $this->setPages($tableData->render());
            }
        }

        return $this;
    }

    /**
     * 将表格数据转换为纯数组
     * @param array|object $tableData 数据
     * @return array
     */
    private function toArray($tableData)
    {
        if ($tableData instanceof \think\paginator) {
            return $tableData->toArray()['data'];
        } elseif ($tableData instanceof \think\model\Collection) {
            return $tableData->toArray();
        } else {
            return $tableData;
        }
    }

    /**
     * 根据表单项类型，加载不同js和css文件，并合并
     * @param string $type 表单项类型
     */
    private function loadMinify($type = '')
    {
        if ($type != '') {
            switch ($type) {    
                case 'xmselect':
                    $this->vars['js_files'][]  = '/static/builder/xm-select/dist/xm-select.js';
                    break;             
            }
        } else {
            foreach ($this->vars['table_columns'] as $column) {
                if ($column['type'] != '') {
                    $this->loadMinify($column['type']);
                }
            }
            foreach ($this->vars['search_items'] as $item) {
                if ($item['type'] != '') {
                    $this->loadMinify($item['type']);
                }
            }
        }
    }

    /**
     * 编译表格数据tableData的值
     */
    private function compileTableData()
    {
        foreach ($this->vars['table_data'] as $key => &$data) {
            // 编译单元格数据类型
            if ($this->vars['table_columns']) {
                foreach ($this->vars['table_columns'] as $column) {
                    switch ($column['type']) {
                        case 'image':
                            if (isset($data[$column['cols']['field']])) {
                                if ($column['value_type'] == 1) {
                                    if ($data[$column['cols']['field']] != '') {
                                        $path = get_file_path($data[$column['cols']['field']]);
                                        !$path && $path = '/static/admin/img/default.png';
                                        $data[$column['cols']['field']] = $path;
                                    }
                                }
                            } else {
                                $data[$column['cols']['field']] = empty($column['value']) ? '' : $column['value'];
                            }
                            break;
                        case 'tag':
                        case 'href':
                        case 'open':
                        case 'download':
                        case 'xmselect':
                            if (isset($data[$column['cols']['field']])) {
                                if (!is_array($data[$column['cols']['field']])) {
                                    $data[$column['cols']['field']] = explode(',', $data[$column['cols']['field']]);
                                }
                            } else {
                                $data[$column['cols']['field']] = empty($column['value']) ? '' : $column['value'];
                            }
                            break;
                        default:
                            if (!isset($data[$column['cols']['field']])) {
                                $data[$column['cols']['field']] = empty($column['value']) ? '' : $column['value'];
                            }
                            break;
                    }
                }
            }
        }
    }

    /**
     * 编译表格相关参数
     */
    private function compileTable()
    {   
        // 编译表格数据tableData的值
        $this->compileTableData();

        // 处理不同表单类型加载不同js和css
        $this->loadMinify();

        // 添加表格列集合左侧复选框
        if ($this->leftColumnCheckbox) { 
            array_unshift($this->vars['table_columns'], ['cols' => ['type' => 'checkbox', 'fixed' => 'left']]);
        }

        // 添加表格列集合右侧操作栏
        if (!empty($this->vars['right_buttons'])) {
            
            $rightToolbar = [
                'fixed'   => 'right',
                'title'   => '操作', 
                'toolbar' => '#toolbar-right-buttons',
                'align'   => 'center'
            ];

            if (!empty($this->vars['right_toolbar'])) {
                $rightToolbar = array_merge($rightToolbar, $this->vars['right_toolbar']);
            }

            $this->vars['table_columns'][] = ['cols' => $rightToolbar];
        }

        $this->vars['table_columns'] = [array_column($this->vars['table_columns'], 'cols')];
        $this->vars['table_data'] = array_values($this->vars['table_data']);
        $this->vars['tool_buttons'] = $this->vars['tool_buttons'];

        // 处理js和css合并的参数
        if (!empty($this->vars['css_files'])) {
            $this->vars['css_files'] = array_unique($this->vars['css_files']);
        }
        if (!empty($this->vars['js_files'])) {
            $this->vars['js_files'] = array_unique($this->vars['js_files']);
        }
    }

    /**
     * 模板变量赋值
     * @access public
     * @param  mixed $name  变量名
     * @param  mixed $value 变量值
     * @return $this
     */
    public function assign($name, $value = '')
    {
        if (is_array($name)) {
            $this->vars = array_merge($this->vars, $name);
        } else {
            $this->vars[$name] = $value;
        }

        return $this;
    }

    /**
     * 加载模板输出
     * @access protected
     * @param  string $template 模板文件名
     * @param  array  $vars     模板输出变量
     * @param  array  $config   模板参数
     * @return mixed
     */
	public function fetch($template = '', $vars = [], $config = [])
    {
        if ($template != '') {
            $this->template = $template;
        }

        if (!empty($vars)) {
            $this->vars = array_merge($this->vars, $vars);
        }

        $_config = [
            'taglib_begin' => '{',
            'taglib_end' => '}'
        ];

        $config = array_merge($_config, $config);

        // 编译表格数据
        $this->compileTable();

        return parent::fetch($this->template, $this->vars, $config);
    }
}