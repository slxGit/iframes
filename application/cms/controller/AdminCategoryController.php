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
 * @Last Modified Time: 2020-04-13 00:14:36
 */
 // ------------------------------------------------------------------------

namespace app\cms\controller;

use utils\Tree;
use app\common\builder\Builder;
use app\cms\model\CategoryModel;
use app\cms\validate\CategoryValidate;
use app\common\controller\AdminBaseController;

class AdminCategoryController extends AdminBaseController
{
	/**
     * 分类列表
     */
	public function index()
	{  
        // 获取分类列表
        $categoryList = CategoryModel::field('id,parent_id,name,order,status,created')
            ->order('order', 'asc')->select();

        $categoryList = Tree::toList($categoryList, 2);

        return Builder::table()
            ->addColumns([
                ['id', 'ID'],
                ['icon_name', '名称'],
                ['order', '排序', 'text'],
                ['status', '状态', 'switch'],
                ['created', '创建时间']
            ])
            ->addTopButtons('add')
            ->addRightButton('add', [
                'title' => '添加子分类',
                'url'   => url('add', ['id' => '__id__']),
                'class' => 'layui-btn layui-btn-warm layui-btn-xs'
            ])
            ->addRightButtons('edit,delete')
            ->setRightToolbar(['width' => '20%'])
            ->setTableData($categoryList)
            ->fetch();
	}

    /**
     * 分类添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();

            $validate = new CategoryValidate;
            if (!$validate->check($params)) {
                $this->error($validate->getError());
            }

            $result = CategoryModel::create($params);
            
            if ($result) {
                $this->success('添加成功', url('index'));
            } else {
                $this->error('添加失败');
            }
        } else {
            $categoryList = CategoryModel::field('id,parent_id,name')->order('order', 'asc')->select();

            $categories = Tree::toLayer($categoryList, 3, function($item){
                $item['value'] = $item['id'];
                unset($item['icon_name']);
                unset($item['parent_id']);
                unset($item['level']);
                unset($item['extremity']);
                unset($item['id']);
                return $item;
            });

            array_unshift($categories, ['name' => '作为一级分类', 'value' => 0]);

            return Builder::form()
                ->setPageTitle('分类添加')
                ->addFormItems([
                    ['xmselect', 'parent_id', '上级', '', $categories, $this->request->param('id') ?: 0, '', ['tree' => ['show' => true, 'strict' => false, 'expandedKeys' => true], 'radio' => true, 'clickClose' => true, 'height' => 'auto', 'model' => ['label' => ['type' => 'text']]]],
                    ['text', 'name', '名称'],
                    ['textarea', 'description', '描述'],
                    ['text', 'seo_title', 'seo标题'],
                    ['tags', 'seo_keywords', 'seo关键字'],
                    ['textarea', 'seo_description', 'seo描述'],
                    ['number', 'order', '排序', '', 99, 0, 99, 0],
                    ['switch', 'status', '状态', '', 1]
                ])
                ->fetch();
        }
    }

    /**
     * 分类编辑
     */
    public function edit()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();

            $validate = new CategoryValidate;
            if (!$validate->check($params)) {
                $this->error($validate->getError());
            }

            $result = CategoryModel::where('id', $params['id'])->update($params);
            
            if ($result !== false) {
                $this->success('保存成功', url('index'));
            } else {
                $this->error('保存失败');
            }
        } else {
            $id = $this->request->param('id', 0, 'intval');

            $category = CategoryModel::get($id);

            $categoryList = CategoryModel::field('id,parent_id,name')->order('order', 'asc')->select();

            $categories = Tree::toLayer($categoryList, 3, function($item){
                $item['value'] = $item['id'];
                unset($item['icon_name']);
                unset($item['parent_id']);
                unset($item['level']);
                unset($item['extremity']);
                unset($item['id']);
                return $item;
            });

            array_unshift($categories, ['name' => '作为一级分类', 'value' => 0]);

            return Builder::form()
                ->setPageTitle('分类编辑')
                ->addFormItems([
                    ['hidden', 'id'],
                    ['xmselect', 'parent_id', '上级', '', $categories, '', '', ['tree' => ['show' => true, 'strict' => false, 'expandedKeys' => true], 'radio' => true, 'clickClose' => true, 'height' => 'auto', 'model' => ['label' => ['type' => 'text']]]],
                    ['text', 'name', '名称'],
                    ['textarea', 'description', '描述'],
                    ['text', 'seo_title', 'seo标题'],
                    ['tags', 'seo_keywords', 'seo关键字'],
                    ['textarea', 'seo_description', 'seo描述'],
                    ['number', 'order', '排序', '', 99, 0, 99, 0],
                    ['switch', 'status', '状态', '', 1]
                ])
                ->setFormData($category)
                ->fetch();
        }        
    }

    /**
     * 分类删除
     */
    public function delete()
    {
        $id = $this->request->param('ids', 0, 'intval');

        // 是否存在子分类
        $childrenCount = CategoryModel::where('parent_id', $id)->count();
        if ($childrenCount) {
            $this->error("该分类下存在子分类，无法删除！");
        }

        // 分类下是否存在文章
        $articleCount = \app\cms\model\ArticleModel::where('category_id', $id)->count();
        if ($articleCount) {
            $this->error("该分类下存在文章，无法删除！");
        }

        $result = CategoryModel::destroy($id);

        if ($result) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
}