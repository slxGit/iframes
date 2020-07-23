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
 * @Last Modified Time: 2020-04-05 20:28:49
 */
 // ------------------------------------------------------------------------

namespace app\system\controller;

use app\common\builder\Builder;
use app\system\model\ConfigModel;
use app\system\validate\ConfigValidate;
use app\common\controller\AdminBaseController;

class ConfigController extends AdminBaseController
{
    /**
     * 配置设置
     */
    public function setting($group = 'base')
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $configs = ConfigModel::where('group', $group)->where('status', 1)->column('name,type');
            foreach ($configs as $name => $type) {
                if (!isset($post[$name])) {
                    switch ($type) {
                        case 'checkbox':
                            $post[$name] = '';
                            break;
                    }
                } else {
                    if (is_array($post[$name])) {
                        $post[$name] = implode(',', $post[$name]);
                    }
                }
                if (isset($post[$name])) {
                    ConfigModel::where('name', $name)->update(['value' => $post[$name]]);
                }
            }
            $this->success('保存成功');
        } else {
            $groups = config('config_group');
        
            $tabList = [];

            foreach ($groups as $key => $value) {
                $tabList[$key] = ['title' => $value, 'url' => url('setting', ['group' => $key])]; 
            }

            $builder = Builder::form()->setTabNav($tabList, $group);

            $configModel = new ConfigModel;
            $configs = $configModel::where('status', 1)
                ->where('group', $group)
                ->order('order', 'asc')
                ->select();

            foreach ($configs as &$v) {
                if ($v['options'] != '') {
                    $v['options'] = parse_attr($v['options']);
                }
                switch ($v['type']) {
                    case 'switch':
                        $v['text'] = 'ON|OFF';
                        break;
                }
            }

            return $builder->setFormItems($configs)->delBtn('back')->fetch();
        }
    }

    /**
     * 配置管理
     */
    public function index($group = 'base')
    {
        $map = [
            ['group', '=', $group]
        ];
        
        $params = $this->request->param();

        $mapOr = [];

        if (!empty($params['keyword'])) {
            $map1 = [
                ['title', 'like', '%' . $params['keyword'] . '%']
            ];
            $map2 = [
                ['name', 'like', '%' . $params['keyword'] . '%']
            ];
            $mapOr = [$map1, $map2];
        }

        $groups = config('config_group');
    
        $tabList = [];

        foreach ($groups as $key => $value) {
            $tabList[$key] = ['title' => $value, 'url' => url('index', ['group' => $key])]; 
        }

        $configList = ConfigModel::where($map)
            ->where(function($query) use ($mapOr){
                $query->whereOr($mapOr);
            })
            ->order('order', 'asc')
            ->paginate()
            ->each(function($item){
                $item->type = config('config_type')[$item->type];
            });

        return Builder::table()
            ->setTableName('sys_config')
            ->setTabNav($tabList, $group)
            ->setSearchUrl(url('index', ['group' => $group]))
            ->addColumns([
                ['name', '名称', 'text', '', ['sort' => true]],
                ['title', '标题', 'text', '', ['sort' => true]],
                ['type', '类型', '', '', ['sort' => true]],
                ['status', '状态', 'switch', '', ['sort' => true]],
                ['order', '排序', 'text']
            ])
            ->addSearchItem('text', 'keyword', '[:请输入名称/标题]')
            ->addTopButton('add', [
                'url' => url('add', ['group' => $group])
            ])
            ->addTopButtons('enable,disable,delete')
            ->addRightButtons('edit,delete')
            ->setTableData($configList)
            ->fetch();
    }

    /**
     * 新增配置项
     */    
    public function add($group = '')
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $validate = new ConfigValidate;
            if (!$validate->check($post)) {
                $this->error($validate->getError());
            }
            if (ConfigModel::create($post)) {
                $this->success('添加成功', url('index', ['group' => $group]));
            } else {
                $this->error('添加失败');
            }
        } else {
            return Builder::form()
                ->setPageTitle('配置新增')
                ->addFormItems([
                    ['radio', 'group', '配置分组', '', config('config_group'), $group],
                    ['select', 'type', '配置类型', '', config('config_type')],
                    ['text', 'title', '配置标题', '一般由中文组成，仅用于显示'],
                    ['text', 'name', '配置名称', "由英文字母和下划线组成，如<code>web_site_title</code>，调用方法：<code>config('web_site_title')</code>"],
                    ['array', 'value', '配置值', '该配置的具体内容'],
                    ['array', 'options', '配置项', '用于单选、多选、下拉等类型'],
                    ['text', 'tips', '配置说明', '该配置的具体说明'],
                    ['number', 'order', '排序', '', 99]
                ])
                ->fetch();
        }
    }

    /**
     * 编辑配置项
     */   
    public function edit($id)
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $validate = new ConfigValidate;
            if (!$validate->check($post)) {
                $this->error($validate->getError());
            }
            if (ConfigModel::where('id', $id)->update($post) !== false) {
                $this->success('保存成功', url('index', ['group' => $post['group']]));
            } else {
                $this->error('保存失败');
            }
        } else {
            $config = ConfigModel::get($id);
            return Builder::form()
                ->setPageTitle('配置新增')
                ->addFormItems([
                    ['hidden', 'id'],
                    ['radio', 'group', '配置分组', '', config('config_group')],
                    ['select', 'type', '配置类型', '', config('config_type')],
                    ['text', 'title', '配置标题', '一般由中文组成，仅用于显示'],
                    ['text', 'name', '配置名称', "由英文字母和下划线组成，如<code>web_site_title</code>，调用方法：<code>config('web_site_title')</code>"],
                    ['array', 'value', '配置值', '该配置的具体内容'],
                    ['array', 'options', '配置项', '用于单选、多选、下拉等类型'],
                    ['text', 'tips', '配置说明', '该配置的具体说明'],
                    ['number', 'order', '排序']
                ])
                ->setFormData($config)
                ->fetch(); 
        }
    }
}