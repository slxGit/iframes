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
 * @Last Modified Time: 2020-04-29 11:26:31
 */
 // ------------------------------------------------------------------------

namespace app\common\builder\form;

use app\common\builder\Builder;

/**
 * 表单构建器
 */
class FormBuilder extends Builder
{
    /**
     * @var string 模板路径
     */
    private $template = '';

    /**
     * @var string public目录
     */
    private $publicPath = '';

    /**
     * @var bool 是否组合分组
     */
    private $isGroup = false;

    /**
     * @var array 模板变量
     */
    public $vars = [
        // 页面标题
        'page_title'      => '',
        // 页面tab导航
        'tab_nav'         => [],
        // 表单项目
        'form_items'      => [],    
        // 表单数据
        'form_data'       => [],
        // 需要触发的表单项
        'field_triggers'  => [],
        // 需要隐藏的表单项
        'field_hide'      => [],
        // 表单提交方式
        'submit_method'   => '',
        // 表单提交地址
        'submit_url'      => '',
        // 表单提交确认
        'submit_confirm'  => '',
        // 是否ajax提交
        'submit_ajax'     => true,
        // 按钮标题
        'btn_title'       => [],
        // 要删除的按钮
        'btn_del'         => [],
        // 额外按钮
        'btn_extra'       => [],
        // 需要加载的js
        'js_files'        => [],
        // 需要加载的css
        'css_files'       => [],
        // 需要引入的js
        'js_list'         => [],
        // 需要引入的css
        'css_list'        => [],
        // 需要引入的js代码
        'js_code'         => '',
        // 需要引入的css代码
        'css_code'        => ''
    ];

    /**
     * 初始化
     */
    public function initialize()
    {
        $baseFile = $this->request->baseFile();
        $this->publicPath = substr($baseFile, 0, strripos($baseFile, '/') + 1);
        $this->template = __DIR__ . '/layout.html';
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
     * 设置按钮标题
     * @param string|array $btn 按钮名 submit-提交,back-返回
     * @param string $title 按钮标题
     * @return $this
     */
    public function setBtnTitle($btn = '', $title = '')
    {
        if (!empty($btn)) {
            if (is_array($btn)) {
                $this->vars['btn_title'] = $btn;
            } else {
                $this->vars['btn_title'][trim($btn)] = trim($title);
            }
        }
        return $this;
    }

    /**
     * 设置表单提交方式
     * @param string $method 提交方式
     * @return $this
     */
    public function setSubmitMethod($submitMethod = '')
    {
        if ($submitMethod != '') {
            $this->vars['submit_method'] = $submitMethod;
        }

        return $this;
    }

    /**
     * 设置表单提交地址
     * @param string $url 提交地址
     * @return $this
     */
    public function setSubmitUrl($url = '')
    {
        if ($url != '') {
            $this->vars['submit_url'] = $url;
        }

        return $this;
    }

    /**
     * 设置ajax方式提交
     * @param bool $isAjax false-关闭 ture-开启
     * @return $this
     */
    public function isAjax($isAjax = true)
    {
        $this->vars['submit_ajax'] = $isAjax;
        return $this;
    }

    /**
     * 设置触发
     * @param string $trigger 需要触发的表单项名，目前只支持select/radio
     * @param string $values 触发的值
     * @param string $show 触发后要显示的表单项名
     * @return $this
     */
    public function setTrigger($trigger = '', $values = '', $show = '')
    {
        if (!empty($trigger)) {
            if (is_array($trigger)) {
                foreach ($trigger as $v) {
                    $this->vars['field_hide'][] = $v[2];
                    $this->vars['field_triggers'][$v[0]][] = [(string)$v[1], $v[2]];
                }
            } else{
                $this->vars['field_hide'][] = $show;
                $this->vars['field_triggers'][$trigger][] = [(string)$values, $show];
            }
        }

        return $this;
    }

    /**
     * 设置多个触发
     * @param array $triggers 触发数组
     * @return $this
     */
    public function setTriggers($triggers = [])
    {
        if (!empty($triggers)) {
            $this->setTrigger($triggers);
        }
        
        return $this;
    }

    /**
     * 设置提交表单时显示确认框
     * @return $this
     */
    public function submitConfirm()
    {
        $this->vars['submit_confirm'] = true;

        return $this;
    }

    /**
     * 添加底部额外按钮
     * @param string $btn 按钮内容 <button type="button">默认</button>
     * @return $this
     */
    public function addBtn($btn = '')
    {
        if ($btn != '') {
            $this->vars['btn_extra'][] = $btn;
        }

        return $this;
    }

    /**
     * 删除按钮
     * @param mixed $btn 要隐藏的按钮，如：['submit']，其中submit-确认按钮,back-返回按钮
     * @return $this
     */
    public function delBtn($btn = [])
    {
        if (!empty($btn)) {
            $this->vars['btn_del'] = is_array($btn) ? $btn : explode(',', $btn);
        }

        return $this;
    }

    /**
     * 添加隐藏表单项
     * @param string $name 表单项名
     * @param string $default 默认值
     * @return mixed
     */
    public function addHidden($name = '', $default = '')
    {
        $item = [
            'type'  => 'hidden',
            'name'  => $name,
            'value' => $default
        ];

        if ($this->isGroup) {
            return $item;
        }

        $this->vars['form_items'][] = $item;

        return $this;
    }

    /**
     * 添加静态文本
     * @param string $name 静态表单项名
     * @param string $title 标题
     * @param string $tips 提示
     * @param string $default 默认值
     * @param array  $grid 格栅参数
     * @param string $hidden 需要提交的值
     * @return mixed
     */
    public function addStatic($name = '', $title = '', $tips = '', $default = '', $grid = [], $hidden = '')
    {
        $item = [
            'type'   => 'static',
            'name'   => $name,
            'title'  => $title,
            'tips'   => $tips,
            'value'  => $default,
            'grid'   => $this->parseGrid($grid),
            'hidden' => $hidden
        ];

        if ($this->isGroup) {
            return $item;
        }

        $this->vars['form_items'][] = $item;

        return $this;
    }

    /**
     * 添加单行文本框
     * @param string $name 表单项名
     * @param string $title 标题
     * @param string $tips 提示
     * @param string $default 默认值
     * @param array  $grid 格栅参数
     * @param string $verify 验证规则
     * @param string $attr 属性
     * @return mixed
     */
    public function addText($name = '', $title = '', $tips = '', $default = '', $grid = [], $verify = '', $attr = '')
    {
        if (preg_match('/(.*)\[:(.*)\]/', $title, $matches)) {
            $title       = $matches[1];
            $placeholder = $matches[2];
        }

        $item = [
            'type'        => 'text',
            'name'        => $name,
            'title'       => $title,
            'tips'        => $tips,
            'value'       => $default,
            'grid'        => $this->parseGrid($grid), 
            'verify'      => $verify,
            'attr'        => $attr,
            'placeholder' => isset($placeholder) ? $placeholder : '请输入' . $title
        ];

        if ($this->isGroup) {
            return $item;
        }

        $this->vars['form_items'][] = $item;

        return $this;
    }

    /**
     * 添加密码框
     * @param string $name 表单项名
     * @param string $title 标题
     * @param string $tips 提示
     * @param string $default 默认值
     * @param array  $grid 格栅参数
     * @param string $verify 验证规则
     * @param string $attr 属性
     * @return mixed
     */
    public function addPassword($name = '', $title = '', $tips = '', $default = '', $grid = [], $verify = '', $attr = '')
    {
        if (preg_match('/(.*)\[:(.*)\]/', $title, $matches)) {
            $title       = $matches[1];
            $placeholder = $matches[2];
        }

        $item = [
            'type'        => 'password',
            'name'        => $name,
            'title'       => $title,
            'tips'        => $tips,
            'value'       => $default,
            'grid'        => $this->parseGrid($grid),
            'verify'      => $verify,
            'attr'        => $attr,
            'placeholder' => isset($placeholder) ? $placeholder : '请输入' . $title,
        ];

        if ($this->isGroup) {
            return $item;
        }

        $this->vars['form_items'][] = $item;

        return $this;
    }

    /**
     * 添加数字表单项
     * @param string $name 表单项名
     * @param string $title 标题
     * @param string $tips 提示
     * @param string $default 默认值
     * @param string $min 最小值
     * @param string $max 最大值
     * @param string $prec 小数精确度
     * @param string $step 数字间隔
     * @param array  $grid 格栅参数
     * @param string $verify 验证规则
     * @param string $attr 属性
     * @return mixed
     */
    public function addNumber($name = '', $title = '', $tips = '', $default = '', $grid = [], $min = '', $max = '', $prec = '', $step = '', $verify = '', $attr = '')
    {
        if (preg_match('/(.*)\[:(.*)\]/', $title, $matches)) {
            $title       = $matches[1];
            $placeholder = $matches[2];
        }

        $item = [
            'type'        => 'number',
            'name'        => $name,
            'title'       => $title,
            'tips'        => $tips,
            'value'       => $default,
            'grid'        => $this->parseGrid($grid),
            'min'         => $min,
            'max'         => $max,
            'prec'        => $prec,
            'step'        => $step,
            'verify'      => $verify,
            'attr'        => $attr,
            'placeholder' => isset($placeholder) ? $placeholder : '请输入' . $title
        ];

        if ($this->isGroup) {
            return $item;
        }

        $this->vars['form_items'][] = $item;

        return $this;
    }

    /**
     * 添加多行文本框
     * @param string $name 表单项名
     * @param string $title 标题
     * @param string $tips 提示
     * @param string $default 默认值
     * @param array  $grid 格栅参数
     * @param string $verify 验证规则
     * @param string $attr 属性
     * @return mixed
     */
    public function addTextarea($name = '', $title = '', $tips = '', $default = '', $grid = [], $verify = '', $attr = '')
    {
        if (preg_match('/(.*)\[:(.*)\]/', $title, $matches)) {
            $title       = $matches[1];
            $placeholder = $matches[2];
        }

        $item = [
            'type'        => 'textarea',
            'name'        => $name,
            'title'       => $title,
            'tips'        => $tips,
            'value'       => $default,
            'grid'        => $this->parseGrid($grid),
            'verify'      => $verify,
            'attr'        => $attr,
            'placeholder' => isset($placeholder) ? $placeholder : '请输入' . $title
        ];

        if ($this->isGroup) {
            return $item;
        }

        $this->vars['form_items'][] = $item;

        return $this;
    }

    /**
     * 添加数组（返回值调用函数parse_attr）
     * @param string $name 表单项名
     * @param string $title 标题
     * @param string $tips 提示
     * @param string $default 默认值
     * @param array  $grid 格栅参数
     * @param string $verify 验证规则
     * @param string $attr 属性
     * @return mixed
     */
    public function addArray($name = '', $title = '', $tips = '', $default = '', $grid = [], $verify = '', $attr = '')
    {
        return $this->addTextarea($name, $title, $tips, $default, $grid, $verify, $attr);
    }

    /**
     * 添加下拉菜单
     * @param string $name 下拉菜单名
     * @param string $title 标题
     * @param string $tips 提示
     * @param array  $options 选项
     * @param string $default 默认值
     * @param array  $grid 格栅参数
     * @param string $verify 验证规则
     * @param string $attr 属性
     * @return mixed
     */
    public function addSelect($name = '', $title = '', $tips = '', $options = [], $default = '', $grid = [], $verify = '', $attr = '')
    {
        if (preg_match('/(.*)\[:(.*)\]/', $title, $matches)) {
            $title       = $matches[1];
            $placeholder = $matches[2];
        }

        $item = [
            'type'        => 'select',
            'name'        => $name,
            'title'       => $title,
            'tips'        => $tips,
            'options'     => $options == '' ? [] : $options,
            'value'       => $default,
            'grid'        => $this->parseGrid($grid),
            'verify'      => $verify,
            'attr'        => $attr,
            'placeholder' => isset($placeholder) ? $placeholder : '请选择一项',
        ];

        if ($this->isGroup) {
            return $item;
        }

        $this->vars['form_items'][] = $item;
        
        return $this;
    }

    /**
     * 添加xm-select
     * @param string $name 下拉菜单名
     * @param string $title 标题
     * @param string $tips 提示
     * @param array  $options 选项
     * @param string $default 默认值
     * @param array  $grid 格栅参数
     * @param array  $configs 配置参数
     * @return mixed
     */
    public function addXmselect($name = '', $title = '', $tips = '', $options = [], $default = '', $grid = [], $configs = [])
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
            'tips'        => $tips,
            'options'     => json_encode($options),
            'value'       => is_array($default) ? implode(',', $default) : $default,
            'grid'        => $this->parseGrid($grid),
            'configs'     => json_encode($configs),
            'placeholder' => isset($placeholder) ? $placeholder : '请选择' . $title
        ];

        if ($this->isGroup) {
            return $item;
        }

        $this->vars['form_items'][] = $item;
        
        return $this;
    }

    /**
     * 添加普通联动表单项
     * @param string $name 表单项名
     * @param string $title 表单项标题
     * @param string $tips 表单项提示说明
     * @param array  $options 表单项
     * @param string $default 默认值
     * @param array  $grid 格栅参数
     * @param string $url 数据请求地址
     * @param string $nextItemNames 下一级下拉框的表单名
     * @param string $param 指定请求参数的key名称
     * @param string $verify 验证规则
     * @param string $attr 属性
     * @return mixed
     */
    public function addLinkage($name = '', $title = '', $tips = '', $options = [], $default = '', $grid = [], $url = '', $nextItemNames = '', $param = '', $verify = '', $attr = '')
    {
        if (preg_match('/(.*)\[:(.*)\]/', $title, $matches)) {
            $title       = $matches[1];
            $placeholder = $matches[2];
        }
    
        $item = [
            'type'            => 'linkage',
            'name'            => $name,
            'title'           => $title,
            'tips'            => $tips,
            'options'         => $options,
            'value'           => $default,
            'grid'            => $this->parseGrid($grid),
            'url'             => $url,
            'next_item_names' => $nextItemNames,
            'param'           => $param == '' ? $name : $param,
            'verify'          => $verify,
            'attr'            => $attr,
            'placeholder'     => isset($placeholder) ? $placeholder : '请选择一项',
        ];

        if ($this->isGroup) {
            return $item;
        }

        $this->vars['form_items'][] = $item;

        return $this;
    }

    /**
     * 添加快速多级联动
     * @param string $name 表单项名
     * @param string $title 标题
     * @param string $tips 提示
     * @param string $table 表名
     * @param int $level 级别
     * @param string $default 默认值
     * @param array  $grid 格栅参数
     * @param array|string $fields 字段名，默认为id,name,pid
     * @param bool $lastValue 只 传入/获取 最后一个选项值
     * @param array  $grid 格栅参数
     * @return mixed
     */
    public function addLinkages($name = '', $title = '', $tips = '', $table = '', $level = '', $default = '', $grid = [], $fields = [], $lastValue = true)
    {
    	if ($level == '') $level = 3;

        if ($level > 3) $this->error('目前最多只支持3级联动');

        // 唯一标识的字段名（主键）
        $keyField = 'id';
        // 显示名称的字段名
        $optionField = 'name';
        // 父级唯一标识的字段名
        $pidField = 'pid';

        if (!empty($fields)) {
            if (!is_array($fields)) {
                $fields = explode(',', $fields);
            }
            $keyField    = isset($fields[0]) ? $fields[0] : $keyField;
            $optionField = isset($fields[1]) ? $fields[1] : $optionField;
            $pidField    = isset($fields[2]) ? $fields[2] : $pidField;
        }

        // 创建Token
        $token = sha1(md5(uniqid(md5(microtime(true)), true)));
        session($token, ['table' => $table, 'key_field' => $keyField, 'option_field' => $optionField, 'pid_field' => $pidField]);

        // 获取一级数据
        $levelOne = $this->getLinkagesData($table, 0, $keyField, $optionField, $pidField);
        
        // 默认选中的联动数据
        $levelKey = $levelData = [];

        if ($lastValue === false) {
            if (!empty($default)) {
                if (is_array($default)) {
                    $default = end($default);
                } else {
                    $default = explode(',', $default);
                    $default = end($default);
                }
            }
        }

        // 如果有默认值
        if (!empty($default)) {
            $levelKeyData = $this->getLinkagesDataReverse($table, $default, $keyField, $optionField, $pidField);
            $levelKey = array_reverse($levelKeyData['key']);
            array_shift($levelKey);
            array_push($levelKey, $default);
            $levelData = array_reverse($levelKeyData['data']);
            if (count($levelKey) < $level) {
                $levelData[] = $this->getLinkagesData($table, end($levelKey), $keyField, $optionField, $pidField);
            }
        }   

        $item = [
            'type'         => 'linkages',
            'name'         => $name,
            'title'        => $title,
            'tips'         => $tips,
            'table'        => $table,
            'level'        => $level,
            'key_field'    => $keyField,
            'option_field' => $optionField,
            'pid_field'    => $pidField,
            'token'        => $token,
            'level_one'    => $levelOne,
            'level_key'    => $levelKey,
            'level_data'   => $levelData,
            'last_value'   => $lastValue,
            'grid'         => $this->parseGrid($grid)
        ];
 
        if ($this->isGroup) {
            return $item;
        }

        $this->vars['form_items'][] = $item;

        return $this;
    }


    /**
     * 获取联动数据
     * @param string $table 表名
     * @param int $pid 父级ID
     * @param string $pidField 父级ID的字段名
     * @param string $keyField 唯一标识的字段名（主键）
     * @param string $optionField 显示名称的字段名
     */
    protected function getLinkagesData($table = '', $pid = 0, $keyField = 'id', $optionField = 'name', $pidField = 'pid')
    {
        return db($table)->where($pidField, $pid)->column($optionField, $keyField);
    }

    /**
     * 反向获取联动数据
     * @param string $table 表名
     * @param string $key 主键值
     * @param string $keyField 主键字段名
     * @param string $pidField pid字段名
     * @param string $optionField 显示名称的字段名
     */
    protected function getLinkagesDataReverse($table = '', $key = '', $keyField = 'id', $optionField = 'name', $pidField = 'pid')
    {
        $result = $levelKey = $levelData = [];

        $levelPid = db($table)->where($keyField, $key)->value($pidField);
        $levelKey[] = $levelPid;
        $levelData[] = $this->getLinkagesData($table, $levelPid, $keyField, $optionField, $pidField);

        if ($levelPid != 0) {
            $data = $this->getLinkagesDataReverse($table, $levelPid, $keyField, $optionField, $pidField);
            $levelKey = array_merge($levelKey, $data['key']);
            $levelData = array_merge($levelData, $data['data']);
        }

        $result['key'] = $levelKey;
        $result['data'] = $levelData;

        return $result;
    }

    /**
     * 添加开关
     * @param string $name 表单项名
     * @param string $title 标题
     * @param string $tips 提示
     * @param string $default 默认值
     * @param array  $grid 格栅参数
     * @param string $text 开关两种状态的文本
     * @param string $attr 属性
     * @return mixed
     */
    public function addSwitch($name = '', $title = '', $tips = '', $default = '', $grid = [], $text = '', $attr = '')
    {
        $item = [
            'type'  => 'switch',
            'name'  => $name,
            'title' => $title,
            'tips'  => $tips,
            'value' => $default,
            'grid'  => $this->parseGrid($grid),
            'text'  => $text ?: 'ON|OFF',
            'attr'  => $attr
        ];

        if ($this->isGroup) {
            return $item;
        }

        $this->vars['form_items'][] = $item;

        return $this;
    }

    /**
     * 添加单选
     * @param string $name 单选名
     * @param string $title 单选标题
     * @param string $tips 提示
     * @param array  $options 单选数据
     * @param string $default 默认值
     * @param array  $grid 格栅参数
     * @param string $verify 验证规则
     * @param string $attr 属性
     * @return mixed
     */
    public function addRadio($name = '', $title = '', $tips = '', $options = [], $default = '', $grid = [], $verify = '', $attr = '')
    {
        $verify == 'required' && $verify = 'radio_required';

        $item = [
            'type'    => 'radio',
            'name'    => $name,
            'title'   => $title,
            'tips'    => $tips,
            'options' => $options == '' ? [] : $options,
            'value'   => $default,
            'grid'    => $this->parseGrid($grid),
            'verify'  => $verify,
            'attr'    => $attr
        ];

        if ($this->isGroup) {
            return $item;
        }

        $this->vars['form_items'][] = $item;

        return $this;
    }

    /**
     * 添加复选框
     * @param string $name 复选框名
     * @param string $title 复选框标题
     * @param string $tips 提示
     * @param array  $options 复选框数据
     * @param string $default 默认值
     * @param array  $grid 格栅参数
     * @param string $attr 属性
     * @return mixed
     */
    public function addCheckbox($name = '', $title = '', $tips = '', $options = [], $default = '', $grid = [], $attr = '')
    {
        $item = [
            'type'    => 'checkbox',
            'name'    => $name,
            'title'   => $title,
            'tips'    => $tips,
            'options' => $options == '' ? [] : $options,
            'value'   => $default,
            'grid'    => $this->parseGrid($grid),
            'attr'    => $attr
        ];

        if ($this->isGroup) {
            return $item;
        }

        $this->vars['form_items'][] = $item;

        return $this;
    }

    /**
     * 添加标签
     * @param string $name 表单项名
     * @param string $title 标题
     * @param string $tips 提示
     * @param string $default 默认值
     * @param array  $grid 格栅参数
     * @return mixed
     */
    public function addTags($name = '', $title = '', $tips = '', $default = '', $grid = [])
    {
        $item = [
            'type'  => 'tags',
            'name'  => $name,
            'title' => $title,
            'tips'  => $tips,
            'value' => is_array($default) ? implode(',', $default) : $default,
            'grid'  => $this->parseGrid($grid)
        ];

        if ($this->isGroup) {
            return $item;
        }

        $this->vars['form_items'][] = $item;

        return $this;
    }

    /**
     * 添加单文件上传
     * @param string $name 表单项名
     * @param string $title 标题
     * @param string $tips 提示
     * @param string $default 默认值
     * @param array  $grid 格栅参数
     * @param string $url 上传接口地址
     * @param string $size 文件大小，单位为kb，0为不限制
     * @param string $ext 文件后缀
     * @return mixed
     */
    public function addFile($name = '', $title = '', $tips = '', $default = '', $grid = [], $url = '', $size = '', $exts = '')
    {
        if (preg_match('/(.*)\[:(.*)\]/', $title, $matches)) {
            $title       = $matches[1];
            $placeholder = $matches[2];
        }

        $size = $size != '' ? $size : config('upload_file_size');
        $exts  = $exts != '' ? $exts : config('upload_file_ext');

        if (is_array($exts)) {
            $exts = implode('|', $exts);
        } else {
            $exts = str_replace(',', '|', $exts);
        }
        
        empty($url) && $url = url('system/annex/upload', ['dir' => 'files']);

        $item = [
            'type'        => 'file',
            'name'        => $name,
            'title'       => $title,
            'tips'        => $tips,
            'value'       => $default,
            'grid'        => $this->parseGrid($grid),
            'url'         => $url,
            'size'        => $size,
            'exts'        => $exts,
            'placeholder' => isset($placeholder) ? $placeholder : '上传文件'
        ];

        if ($this->isGroup) {
            return $item;
        }

        $this->vars['form_items'][] = $item;

        return $this;
    }

    /**
     * 添加多文件上传
     * @param string $name 表单项名
     * @param string $title 标题
     * @param string $tips 提示
     * @param string $default 默认值
     * @param array  $grid 格栅参数
     * @param string $url 上传接口地址
     * @param string $size 文件大小，单位为kb，0为不限制
     * @param string $exts 文件后缀
     * @return mixed
     */
    public function addFiles($name = '', $title = '', $tips = '', $default = '', $grid = [], $url = '', $size = '', $exts = '')
    {
        if (preg_match('/(.*)\[:(.*)\]/', $title, $matches)) {
            $title       = $matches[1];
            $placeholder = $matches[2];
        }

        if (is_array($default)) {
            $default = implode(',', $default);
        }

        $size = $size != '' ? $size : config('upload_file_size');
        $exts  = $exts != '' ? $exts : config('upload_file_ext');

        if (is_array($exts)) {
            $exts = implode('|', $exts);
        } else {
            $exts = str_replace(',', '|', $exts);
        }

        empty($url) && $url = url('system/annex/upload', ['dir' => 'files']);

        $item = [
            'type'        => 'files',
            'name'        => $name,
            'title'       => $title,
            'tips'        => $tips,
            'value'       => $default,
            'grid'        => $this->parseGrid($grid),
            'url'         => $url,
            'size'        => $size,
            'exts'        => $exts,
            'placeholder' => isset($placeholder) ? $placeholder : '上传文件'
        ];

        if ($this->isGroup) {
            return $item;
        }

        $this->vars['form_items'][] = $item;

        return $this;
    }

    /**
     * 添加单文件展示
     * @param string $name 表单项名
     * @param string $title 标题
     * @param string $tips 提示
     * @param string $default 默认值
     * @param array  $grid 格栅参数
     * @return mixed
     */
    public function addArchive($name = '', $title = '', $tips = '', $default = '', $grid = [])
    {
        $item = [
            'type'  => 'archive',
            'name'  => $name,
            'title' => $title,
            'tips'  => $tips,
            'value' => $default,
            'grid'  => $this->parseGrid($grid)
        ];

        if ($this->isGroup) {
            return $item;
        }

        $this->vars['form_items'][] = $item;

        return $this;
    }

    /**
     * 添加多文件展示
     * @param string $name 表单项名
     * @param string $title 标题
     * @param string $tips 提示
     * @param string $default 默认值
     * @param array  $grid 格栅参数
     * @return mixed
     */
    public function addArchives($name = '', $title = '', $tips = '', $default = '', $grid = [])
    {
        if (is_array($default)) {
            $default = implode(',', $default);
        }
        
        $item = [
            'type'  => 'archives',
            'name'  => $name,
            'title' => $title,
            'tips'  => $tips,
            'value' => $default,
            'grid'  => $this->parseGrid($grid)
        ];

        if ($this->isGroup) {
            return $item;
        }

        $this->vars['form_items'][] = $item;

        return $this;
    }

    /**
     * 添加单图片上传
     * @param string $name 表单项名
     * @param string $title 标题
     * @param string $tips 提示
     * @param string $default 默认值
     * @param array  $grid 格栅参数
     * @param array $thumb 缩略图参数 array:['size' => ['30x30','100x100']] string:'30x30,100x100'
     * @param string $url 上传接口地址
     * @param string $size 图片大小，单位为kb，0为不限制
     * @param string $ext 图片后缀
     * @return mixed
     */
    public function addImage($name = '', $title = '', $tips = '', $default = '', $grid = [], $thumb = '', $url = '', $size = '', $exts = '')
    {
        if (preg_match('/(.*)\[:(.*)\]/', $title, $matches)) {
            $title       = $matches[1];
            $placeholder = $matches[2];
        }

        $size = $size != '' ? $size : config('upload_image_size');
        $exts  = $exts != '' ? $exts : config('upload_image_ext');

        if (is_array($exts)) {
            $exts = implode('|', $exts);
        } else {
            $exts = str_replace(',', '|', $exts);
        }

        if (!empty($thumb)) {
            if (is_array($thumb)) {
                $thumb = implode($thumb, ',');
            }
        }

        empty($url) && $url = url('system/annex/upload', ['dir' => 'images']);

        $item = [
            'type'        => 'image',
            'name'        => $name,
            'title'       => $title,
            'tips'        => $tips,
            'value'       => $default,
            'grid'        => $this->parseGrid($grid),
            'thumb'       => $thumb,
            'url'         => $url,
            'size'        => $size,
            'exts'        => $exts,
            'placeholder' => isset($placeholder) ? $placeholder : '上传图片'
        ];

        if ($this->isGroup) {
            return $item;
        }

        $this->vars['form_items'][] = $item;

        return $this;
    }

    /**
     * 添加多图片上传
     * @param string $name 表单项名
     * @param string $title 标题
     * @param string $tips 提示
     * @param string $default 默认值
     * @param array  $grid 格栅参数
     * @param array $thumb 缩略图参数 array:['size' => ['30x30','100x100']] string:'30x30,100x100'
     * @param string $url 上传接口地址
     * @param string $size 图片大小，单位为kb，0为不限制
     * @param string $exts 图片后缀
     * @return mixed
     */
    public function addImages($name = '', $title = '', $tips = '', $default = '', $grid = [], $thumb = '', $url = '', $size = '', $exts = '')
    {
        if (preg_match('/(.*)\[:(.*)\]/', $title, $matches)) {
            $title       = $matches[1];
            $placeholder = $matches[2];
        }

        if (is_array($default)) {
            $default = implode(',', $default);
        }

        $size = $size != '' ? $size : config('upload_image_size');
        $exts  = $exts != '' ? $exts : config('upload_image_ext');

        if (is_array($exts)) {
            $exts = implode('|', $exts);
        } else {
            $exts = str_replace(',', '|', $exts);
        }

        if (!empty($thumb)) {
            if (is_array($thumb)) {
                $thumb = implode($thumb, ',');
            }
        }

        empty($url) && $url = url('system/annex/upload', ['dir' => 'images']);

        $item = [
            'type'        => 'images',
            'name'        => $name,
            'title'       => $title,
            'tips'        => $tips,
            'value'       => $default,
            'grid'        => $this->parseGrid($grid),
            'thumb'       => $thumb,
            'url'         => $url,
            'size'        => $size,
            'exts'        => $exts,
            'placeholder' => isset($placeholder) ? $placeholder : '上传图片'
        ];

        if ($this->isGroup) {
            return $item;
        }

        $this->vars['form_items'][] = $item;

        return $this;
    }

    /**
     * 添加单图片展示
     * @param string $name 表单项名
     * @param string $title 标题
     * @param string $tips 提示
     * @param string $default 默认值
     * @param array  $grid 格栅参数
     * @return mixed
     */
    public function addPlan($name = '', $title = '', $tips = '', $default = '', $grid = [])
    {        
        $item = [
            'type'  => 'plan',
            'name'  => $name,
            'title' => $title,
            'tips'  => $tips,
            'value' => $default,
            'grid'  => $this->parseGrid($grid)
        ];

        if ($this->isGroup) {
            return $item;
        }

        $this->vars['form_items'][] = $item;

        return $this;
    }

    /**
     * 添加多图片展示
     * @param string $name 表单项名
     * @param string $title 标题
     * @param string $tips 提示
     * @param string $default 默认值
     * @param array  $grid 格栅参数
     * @return mixed
     */
    public function addPlans($name = '', $title = '', $tips = '', $default = '', $grid = [])
    {
        if (is_array($default)) {
            $default = implode(',', $default);
        }
        
        $item = [
            'type'  => 'plans',
            'name'  => $name,
            'title' => $title,
            'tips'  => $tips,
            'value' => $default,
            'grid'  => $this->parseGrid($grid)
        ];

        if ($this->isGroup) {
            return $item;
        }

        $this->vars['form_items'][] = $item;

        return $this;
    }

    /**
     * 添加日期
     * @param string $name 表单项名
     * @param string $title 标题
     * @param string $tips 提示
     * @param string $default 默认值
     * @param array  $grid 格栅参数
     * @param string $configs 配置参数
     * @param string $verify 验证规则
     * @param string $attr 属性
     * @return mixed
     */
    public function addDate($name = '', $title = '', $tips = '', $default = '', $grid = [], $configs = [], $verify = '', $attr = '')
    {
        if (preg_match('/(.*)\[:(.*)\]/', $title, $matches)) {
            $title       = $matches[1];
            $placeholder = $matches[2];
        }

        $item = [
            'type'        => 'date',
            'name'        => $name,
            'title'       => $title,
            'tips'        => $tips,
            'value'       => $default,
            'grid'        => $this->parseGrid($grid),
            'configs'     => json_encode($configs),
            'verify'      => $verify,
            'attr'        => $attr,
            'placeholder' => isset($placeholder) ? $placeholder : '请选择' . $title
        ];

        if ($this->isGroup) {
            return $item;
        }

        $this->vars['form_items'][] = $item;

        return $this;
    }

    /**
     * 添加Layui编辑器
     * @param string $name 表单项名
     * @param string $title 标题
     * @param string $tips 提示
     * @param string $default 默认值
     * @param array  $grid 格栅参数
     * @param string $configs 配置参数
     * @return mixed
     */
    public function addLayedit($name = '', $title = '', $tips = '', $default = '', $grid = [], $configs = [])
    {
        if (preg_match('/(.*)\[:(.*)\]/', $title, $matches)) {
            $title       = $matches[1];
            $placeholder = $matches[2];
        }

        $item = [
            'type'        => 'layedit',
            'name'        => $name,
            'title'       => $title,
            'tips'        => $tips,
            'value'       => $default,
            'grid'        => $this->parseGrid($grid),
            'configs'     => json_encode($configs),
            'placeholder' => isset($placeholder) ? $placeholder : '请输入' . $title
        ];

        if ($this->isGroup) {
            return $item;
        }

        $this->vars['form_items'][] = $item;

        return $this;
    }

    /**
     * 添加百度编辑器
     * @param string $name 表单项名
     * @param string $title 标题
     * @param string $tips 提示
     * @param string $default 默认值
     * @param array  $grid 格栅参数
     * @return mixed
     */
    public function addUeditor($name = '', $title = '', $tips = '', $default = '', $grid = [])
    {
        if (preg_match('/(.*)\[:(.*)\]/', $title, $matches)) {
            $title       = $matches[1];
            $placeholder = $matches[2];
        }

        $item = [
            'type'        => 'ueditor',
            'name'        => $name,
            'title'       => $title,
            'tips'        => $tips,
            'value'       => $default,
            'grid'        => $this->parseGrid($grid),
            'placeholder' => isset($placeholder) ? $placeholder : '请输入' . $title
        ];

        if ($this->isGroup) {
            return $item;
        }

        $this->vars['form_items'][] = $item;

        return $this;
    }

    /**
     * 添加百度地图
     * @param string $name 表单项名
     * @param string $title 标题
     * @param string $tips 提示
     * @param string $default 默认坐标
     * @param string $address 默认地址
     * @param array  $grid 格栅参数
     * @param string $level 地图显示级别
     * @param string $ak 百度APPKEY
     * @return mixed
     */
    public function addBmap($name = '', $title = '', $tips = '', $default = '', $address = '', $grid = [], $level = '', $ak = '')
    {   
        if ($ak == '') {
            $ak = config('bmap_ak');
        }
        $item = [
            'type'    => 'bmap',
            'name'    => $name,
            'title'   => $title,
            'tips'    => $tips,
            'value'   => $default,
            'address' => $address,
            'grid'    => $this->parseGrid($grid),
            'level'   => $level == '' ? 12 : $level,
            'ak'      => $ak
        ];

        if ($this->isGroup) {
            return $item;
        }

        $this->vars['form_items'][] = $item;

        return $this;
    }

    /**
     * 添加表格
     * @param string $name 表单项名
     * @param string $title 标题
     * @param string $tips 提示
     * @param array  $columns 列参数
     * @param string $default 默认值
     * @param array  $grid 格栅参数
     * @param array  $configs 配置
     * @return mixed
     */
    public function addTable($name = '', $title = '', $tips = '', $columns = [], $default = '', $grid = [], $configs = [])
    {
        $cols = [];
        foreach ($columns as $k => $v) {
            if (is_array($v)) {
                $col = [
                    'field' => $v[0],
                    'title' => $v[1]
                ];
                if (isset($v[2])) {
                    $col = array_merge($col, $v[2]);
                }
                $cols[] = $col;
            } else {
                $col = [
                    'field' => $k,
                    'title' => $v
                ];
                $cols[] = $col;
            }
        }
        if (empty($title)) {
            $cols = [$cols];
        } else {
            $titleCols = [[
                'align'   => 'center',
                'title'   => '<span class="table-title">' . $title . '</span>',
                'colspan' => count($cols) === 1 ? 2 : count($cols)
            ]];
            $cols = [$titleCols, $cols];
        }

        $item = [
            'type'    => 'table',
            'name'    => $name,
            'tips'    => $tips,
            'cols'    => json_encode($cols),
            'value'   => $default == '' ? '[]' : json_encode($default),
            'grid'    => $this->parseGrid($grid),
            'configs' => json_encode($configs)
        ];

        if ($this->isGroup) {
            return $item;
        }

        $this->vars['form_items'][] = $item;

        return $this;
    }

    /**
     * 添加数据表格
     * @param string $name 表单项名
     * @param string $title 标题
     * @param string $tips 提示
     * @param array  $columns 列参数
     * @param string $default 默认值
     * @param array  $grid 格栅参数
     * @return mixed
     */
    public function addDatatable($name = '', $title = '', $tips = '', $columns = [], $default = '', $grid = [])
    {
        $cols = [];

        foreach ($columns as $k => &$v) {
            if (is_array($v)) {
                $type = $v['type'];

                switch ($type) {
                    case 'text':
                        $templet = "<div>{{data_table_templet(`text`, d, `{$k}`)}}</div>";
                        break;
                    case 'select':
                        $options = json_encode($v['options'], JSON_UNESCAPED_UNICODE);
                        $templet = "<div>{{# var options = {$options}}}{{data_table_templet(`select`, d, `{$k}`, options)}}</div>";
                        break;
                }

                $column = [
                    'field' => $k,
                    'title' => $v['title'],
                    'templet' => $templet
                ];

                if (!empty($v['configs'])) {
                    $column = array_merge($column, $v['configs']);
                }

                $cols[] = $column;
            } else {
                $type = 'text';
                $cols[] = [
                    'field' => $k,
                    'title' => $v,
                    'templet' => "<div>{{data_table_templet(`text`, d, `{$k}`)}}</div>"
                ];                
            }
        }

        if (!empty($cols)) {
            $cols[] = [
                'fixed'   => 'right',
                'title'   => '操作', 
                'align'   => 'center',
                'toolbar' => '#data-table-right-buttons'
            ];
        }

        if (empty($title)) {
            $cols = [$cols];
        } else {
            $titleCols = [[
                'align'   => 'center',
                'title'   => '<span class="table-title">' . $title . '</span>',
                'colspan' => count($cols) === 1 ? 2 : count($cols)
            ]];
            $cols = [$titleCols, $cols];
        }
   
        $item = [
            'type'  => 'datatable',
            'name'  => $name,
            'tips'  => $tips,
            'cols'  => json_encode($cols),
            'value' => $default == '' ? '[]' : json_encode($default),
            'grid'  => $this->parseGrid($grid)
        ];

        if ($this->isGroup) {
            return $item;
        }

        $this->vars['form_items'][] = $item;

        return $this;
    }

    /**
     * 添加按钮
     * @param string $id 表单项名,也是按钮id
     * @param string $title 标题
     * @param string $btnAttr 按钮属性
     * @param array  $grid 格栅参数
     * @param string $ele_type 按钮类型,a/button
     * @return mixed
     */
    public function addButton($name = '', $title = '', $btnAttr = [], $grid = [], $eleType = 'a')
    {
        $item = [
            'type'     => 'button',
            'name'     => $name,
            'title'    => $title,
            'btn_attr' => $btnAttr,
            'grid'     => $this->parseGrid($grid),
            'ele_type' => $eleType
        ];

        if ($this->isGroup) {
            return $item;
        }

        $this->vars['form_items'][] = $item;

        return $this;
    }

    /**
     * 添加图标选择器
     * @param string $name 表单项名
     * @param string $title 标题
     * @param string $tips 提示
     * @param string $default 默认值
     * @param array  $grid 格栅参数
     * @return mixed
     */
    public function addIcon($name = '', $title = '', $tips = '', $default = '', $grid = [])
    {
        if (preg_match('/(.*)\[:(.*)\]/', $title, $matches)) {
            $title       = $matches[1];
            $placeholder = $matches[2];
        }

        $item = [
            'type'        => 'icon',
            'name'        => $name,
            'title'       => $title,
            'tips'        => $tips,
            'value'       => $default,
            'grid'        => $this->parseGrid($grid),
            'placeholder' => isset($placeholder) ? $placeholder : '请选择图标'
        ];

        if ($this->isGroup) {
            return $item;
        }

        $this->vars['form_items'][] = $item;

        return $this;
    }

    /**
     * 添加取色器
     * @param string $name 表单项名
     * @param string $title 标题
     * @param string $tips 提示
     * @param string $default 默认值
     * @param array  $grid 格栅参数
     * @param string $mode 模式：默认为rgba(含透明度)，也可以是rgb
     * @param string $verify 验证规则
     * @param string $attr 属性
     * @return mixed
     */
    public function addColorpicker($name = '', $title = '', $tips = '', $default = '', $grid = [], $configs = [], $verify = '', $attr = '')
    {
        if (preg_match('/(.*)\[:(.*)\]/', $title, $matches)) {
            $title       = $matches[1];
            $placeholder = $matches[2];
        }
        
        $item = [
            'type'        => 'colorpicker',
            'name'        => $name,
            'title'       => $title,
            'tips'        => $tips,
            'value'       => $default,
            'grid'        => $this->parseGrid($grid),
            'configs'     => json_encode($configs),
            'verify'      => $verify,
            'attr'        => $attr,
            'placeholder' => isset($placeholder) ? $placeholder : '请选择颜色'
        ];

        if ($this->isGroup) {
            return $item;
        }

        $this->vars['form_items'][] = $item;
        
        return $this;
    }

    /**
     * 添加评分
     * @param string $name 表单项名
     * @param string $title 标题
     * @param string $tips 提示
     * @param string $default 默认值
     * @param array  $grid 格栅参数
     * @param array  $configs 配置参数
     * @return mixed
     */
    public function addRate($name = '', $title = '', $tips = '', $default = '', $grid = [], $configs = [])
    {
        $item = [
            'type'    => 'rate',
            'name'    => $name,
            'title'   => $title,
            'tips'    => $tips,
            'value'   => $default,
            'grid'    => $this->parseGrid($grid),
            'configs' => json_encode($configs)
        ];

        if ($this->isGroup) {
            return $item;
        }

        $this->vars['form_items'][] = $item;

        return $this;
    }

    /**
     * 添加范围
     * @param string $name 表单项名
     * @param string $title 标题
     * @param string $tips 提示
     * @param string $default 默认值
     * @param array  $grid 格栅参数
     * @param array  $configs 配置参数
     * @return mixed
     */
    public function addRange($name = '', $title = '', $tips = '', $default = '', $grid = [], $configs = [])
    {
        $item = [
            'type'    => 'range',
            'name'    => $name,
            'title'   => $title,
            'tips'    => $tips,
            'value'   => $default,
            'grid'    => $this->parseGrid($grid),
            'configs' => json_encode($configs)
        ];

        if ($this->isGroup) {
            return $item;
        }

        $this->vars['form_items'][] = $item;

        return $this;
    }

    /**
     * 添加拖拽排序
     * @param string $name 表单项名
     * @param string $title 标题
     * @param string $tips 提示
     * @param array  $value 值
     * @param array  $grid 格栅参数
     * @return mixed
     */
    public function addSort($name = '', $title = '', $tips = '', $value = [], $grid = [])
    {
        $item = [
            'type'  => 'sort',
            'name'  => $name,
            'title' => $title,
            'tips'  => $tips,
            'value' => $value,
            'grid'  => $this->parseGrid($grid)
        ];

        if ($this->isGroup) {
            return $item;
        }

        $this->vars['form_items'][] = $item;

        return $this;
    }

    /**
     * 添加穿梭框
     * @param string $name 表单项名
     * @param string $title 标题
     * @param string $tips 提示
     * @param array  $options 选项
     * @param string $default 默认值
     * @param array  $grid 格栅参数
     * @param array  $configs 配置参数
     * @return mixed
     */
    public function addTransfer($name = '', $title = '', $tips = '', $options = [], $default = '', $grid = [], $configs = [])
    {
        if (!empty($options)) {
            if (count($options) == count($options, 1)) {
                $optionsData = [];
                foreach ($options as $k => $v) {
                    $optionsData[] = [
                        'title' => $v,
                        'value' => $k
                    ];
                }
                $options = $optionsData;
            } 
        }

        $item = [   
            'type'    => 'transfer',
            'name'    => $name,
            'title'   => $title,
            'tips'    => $tips,
            'value'   => is_array($default) ? implode(',', $default) : $default,
            'grid'    => $this->parseGrid($grid),
            'options' => json_encode($options),
            'configs' => json_encode($configs)
        ];

        if ($this->isGroup) {
            return $item;
        }

        $this->vars['form_items'][] = $item;
        
        return $this;
    }

    /**
     * 添加分组
     * @param array $groups 分组数据
     * @return mixed
     */
    public function addGroup($groups = [])
    {
        if (is_array($groups) && !empty($groups)) {
            $this->isGroup = true;
            foreach ($groups as &$group) {
                foreach ($group as $key => $item) {
                    $type = array_shift($item);
                    $method = 'add'. ucfirst($type);
                    $group[$key] = call_user_func_array([$this, $method], $item);
                }
            }
            $this->isGroup = false;
        }

        $item = [
            'type'    => 'group',
            'options' => $groups
        ];

        $this->vars['form_items'][] = $item;

        return $this;
    }

    /**
     * 解析栅格参数
	 * xs-超小屏幕,手机<768px sm-小屏幕,平板≥768px md-桌面中等屏幕,桌面≥992px lg-桌面大型屏幕,桌面≥1200px
     * @return string
     */
    protected function parseGrid($param = '')
    {
    	if (empty($param)) {
    		return 'layui-col-md12';
    	} else {
	    	if (is_string($param) && strpos($param, ',') !== false) {
	    		$param = explode(',', $param);
	    	}
	    	if (is_array($param)) {
	    		$grids = ['xs', 'sm', 'md', 'lg'];
	    		$result = '';
	    		foreach ($param as $key => $value) {
	    			$result .= 'layui-col-' . $grids[$key] . $value . ' ';
	    		}
	    		return $result;
	    	} else {
	    		return 'layui-col-md' . $param;
	    	}
    	}
    }

    /**
     * 添加表单项
     * 第一个参数传表单项类型，其余参数与各自方法中的参数一致
     * @param string $type 表单项类型
     * @return $this
     */
    public function addFormItem($type = '')
    {
        if ($type != '') {
            $args = func_get_args();
            array_shift($args);
            $method = 'add'. ucfirst($type);
            call_user_func_array([$this, $method], $args);
        }

        return $this;
    }

    /**
     * 一次性添加多个表单标签项
     * @param array $items 表单项
     * @return $this
     */
    public function addFormItems($items = [])
    {
        if (!empty($items)) {
            foreach ($items as $item) {
                call_user_func_array([$this, 'addFormItem'], $item);
            }
        }

        return $this;
    }

    /**
     * 直接设置表单项数据
     * @param array $items 表单项数据
     * @return $this
     */
    public function setFormItems($items = [])
    {
        if ($items instanceof \think\model\Collection) {
            $items = $items->toArray();
        } elseif ($items instanceof \think\paginator) {
            $items = $items->toArray()['data'];
        }

        if (!empty($items)) {
            foreach ($items as $item) {
                switch ($item['type']) {
                    case 'group':
                        foreach ($item['options'] as $options) {
                            foreach ($options as $option) {
                                $this->loadMinify($option['type']);
                            }
                        }
                        break;
                    default:
                        $this->loadMinify($item['type']);
                        break;
                }
            }
            $this->vars['form_items'] = array_merge($this->vars['form_items'], $items);
        }   

        return $this;
    }

    /**
     * 设置表单数据
     * @param array $formData 表单数据
     * @return $this
     */
    public function setFormData($formData = [])
    {
        if (!empty($formData)) {
            $this->vars['form_data'] = $formData;
        }
        return $this;
    }

    /**
     * 设置表单项的值
     */
    private function setFormValue()
    {
        if ($this->vars['form_data']) {
            foreach ($this->vars['form_items'] as &$item) {
                // 判断是否为分组
                if ($item['type'] == 'group') {
                    foreach ($item['options'] as &$group) {
                        foreach ($group as $key => $value) {
                            switch ($value['type']) {
                                case 'tags':
                                case 'transfer':
                                case 'xmselect':
                                case 'images':
                                case 'plans':
                                case 'files':
                                case 'archives':
                                    if (isset($this->vars['form_data'][$value['name']])) {
                                        if (is_array($this->vars['form_data'][$value['name']])) {
                                            $group[$key]['value'] = implode(',', $this->vars['form_data'][$value['name']]);
                                        } else {
                                            $group[$key]['value'] = $this->vars['form_data'][$value['name']];
                                        }
                                    }
                                    break;
                                case 'table':
                                case 'datatable':
                                    if (isset($this->vars['form_data'][$value['name']])) {
                                        $group[$key]['value'] = json_encode($this->vars['form_data'][$value['name']]);
                                    }
                                    break;
                                case 'linkages':
                                    if (isset($this->vars['form_data'][$value['name']])) {
                                        $levelKeyData = $this->getLinkagesDataReverse($group[$key]['table'], $this->vars['form_data'][$value['name']], $value['key_field'], $value['option_field'], $value['pid_field']);
                                        $levelKey = array_reverse($levelKeyData['key']);
                                        array_shift($levelKey);
                                        array_push($levelKey, $this->vars['form_data'][$value['name']]);
                                        $levelData = array_reverse($levelKeyData['data']);
                                        $group[$key]['level_key'] = $levelKey;
                                        $group[$key]['level_data'] = $levelData;
                                        $group[$key]['value'] = $this->vars['form_data'][$value['name']];
                                    }
                                    break;
                                case 'bmap':
                                    if (isset($this->vars['form_data'][$value['name'] . '_address'])) {
                                        $group[$key]['address'] = $this->vars['form_data'][$value['name'] . '_address'] ?: '';
                                    }
                                default:
                                    if (isset($this->vars['form_data'][$value['name']])) {
                                        $group[$key]['value'] = $this->vars['form_data'][$value['name']];
                                    }
                                    break;
                            }
                            // 静态文本且设置了需要提交的值
                            if ($value['type'] == 'static' && $value['hidden'] === true) {
                                if (isset($this->vars['form_data'][$value['name']])) {
                                    $group[$key]['hidden'] = $this->vars['form_data'][$value['name']];
                                } else {
                                    $group[$key]['hidden'] = $value['value'];
                                }
                            }
                        }
                    }
                } else {
                    switch ($item['type']) {
                        case 'tags':
                        case 'transfer':
                        case 'xmselect':
                        case 'images':
                        case 'plans':
                        case 'files':
                        case 'archives':
                            if (isset($this->vars['form_data'][$item['name']])) {
                                if (is_array($this->vars['form_data'][$item['name']])) {
                                    $item['value'] = implode(',', $this->vars['form_data'][$item['name']]);
                                } else {
                                    $item['value'] = $this->vars['form_data'][$item['name']];
                                }
                            }
                            break;
                        case 'table':
                        case 'datatable':
                            if (isset($this->vars['form_data'][$item['name']])) {
                                $item['value'] = json_encode($this->vars['form_data'][$item['name']]);
                            }
                            break;
                        case 'linkages':
                            if (isset($this->vars['form_data'][$item['name']])) {
                                $levelKeyData = $this->getLinkagesDataReverse($item['table'], $this->vars['form_data'][$item['name']], $item['key_field'], $item['option_field'], $item['pid_field']);
                                $levelKey = array_reverse($levelKeyData['key']);
                                array_shift($levelKey);
                                array_push($levelKey, $this->vars['form_data'][$item['name']]);
                                $levelData = array_reverse($levelKeyData['data']);
                                $item['level_key'] = $levelKey;
                                $item['level_data'] = $levelData;
                                $item['value'] = $this->vars['form_data'][$item['name']];
                            }
                            break;
                        case 'bmap':
                            if (isset($this->vars['form_data'][$item['name'] . '_address'])) {
                                $item['address'] = $this->vars['form_data'][$item['name'] . '_address'] ?: '';
                            }
                        default:
                            if (isset($this->vars['form_data'][$item['name']])) {
                                $item['value'] = $this->vars['form_data'][$item['name']];
                            }
                            break;
                    }
                    // 静态文本且设置了需要提交的值
                    if ($item['type'] == 'static' && $item['hidden'] === true) {
                        if (isset($this->vars['form_data'][$item['name']])) {
                            $item['hidden'] = $this->vars['form_data'][$item['name']];
                        } else {
                            $item['hidden'] = $item['value'];
                        }
                    }
                }
            }
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
                case 'images':
                    $this->vars['js_files'][]  = '/static/builder/jquery-ui/jquery-ui.min.js';
                    break;        
                case 'xmselect':
                    $this->vars['js_files'][]  = '/static/builder/xm-select/dist/xm-select.js';
                    break;             
                case 'sort':
                    $this->vars['css_files'][] = '/static/builder/jquery-nestable/jquery.nestable.css';
                    $this->vars['js_files'][]  = '/static/builder/jquery-nestable/jquery.nestable.js';
                    break;
                case 'icon':
                    $this->vars['js_files'][]  = '/static/builder/iconpicker/iconPicker.js';
                    break;
                case 'tags':
                    $this->vars['css_files'][]  = '/static/builder/jquery-tagsinput/jquery.tagsinput.min.css';
                    $this->vars['js_files'][]  = '/static/builder/jquery-tagsinput/jquery.tagsinput.min.js';
                    break;
                case 'ueditor':
                    $this->vars['js_files'][]  = '/static/builder/ueditor/ueditor.config.js';
                    $this->vars['js_files'][] = '/static/builder/ueditor/ueditor.all.min.js';
                    break;
            }
        } else {
            foreach ($this->vars['form_items'] as $item) {
                // 判断是否为分组
                if ($item['type'] == 'group') {
                    foreach ($item['options'] as &$group) {
                        foreach ($group as $key => $value) {
                            if ($group[$key]['type'] != '') {
                                $this->loadMinify($group[$key]['type']);
                            }
                        }
                    }
                } else {
                    if ($item['type'] != '') {
                        $this->loadMinify($item['type']);
                    }                   
                }
            }
        }
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
                $filesPath = explode(',', $filesPath);
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

        // 设置表单项的值
        $this->setFormValue();

        // 处理不同表单类型加载不同js和css
        $this->loadMinify();

        // 处理隐藏字段
        if (!empty($this->vars['field_hide'])) {
            $this->vars['field_hide'] = array_unique($this->vars['field_hide']);
        }

        // 处理js和css合并的参数
        if (!empty($this->vars['css_files'])) {
            $this->vars['css_files'] = array_unique($this->vars['css_files']);
        }
        if (!empty($this->vars['js_files'])) {
            $this->_vars['js_files'] = array_unique($this->vars['js_files']);
        }
 
        // 处理额外按钮
        $this->vars['btn_extra'] = implode(' ', $this->vars['btn_extra']);
       
        return parent::fetch($this->template, $this->vars, $config);
    }

}