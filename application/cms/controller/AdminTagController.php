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
 * @Last Modified Time: 2020-03-30 15:42:26
 */
 // ------------------------------------------------------------------------

namespace app\cms\controller;

use app\common\builder\Builder;
use app\cms\model\TagModel;
use app\cms\model\ArticleTagModel;
use app\cms\validate\TagValidate;
use app\common\controller\AdminBaseController;

class AdminTagController extends AdminBaseController
{
	/**
     * 标签列表
     */
	public function index()
	{  
        $params = $this->request->param();

        $map = [];

        if (!empty($params['name'])) {
            $map[] = ['name', 'like', '%'. $params['name'] . '%'];
        }

        // 获取标签列表
        $tagList = TagModel::where($map)->paginate();

        return Builder::table()
            ->addColumns([
                ['id', 'ID'],
                ['name', '名称', 'text'],
                ['description', '描述', 'text'],
                ['status', '状态', 'switch']
            ])
            ->addSearchItem('text', 'name', '名称')
            ->addTopButtons('delete')
            ->addRightButtons('edit,delete')
            ->setTableData($tagList)
            ->fetch();
	}

    /**
     * 标签编辑
     */
    public function edit()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();

            $validate = new TagValidate;
            if (!$validate->check($params)) {
                $this->error($validate->getError());
            }

            $result = TagModel::where('id', $params['id'])->update($params);
            
            if ($result !== false) {
                $this->success('保存成功', url('index'));
            } else {
                $this->error('保存失败');
            }
        } else {
            $id = $this->request->param('id', 0, 'intval');

            $tag = TagModel::get($id);

            return Builder::form()
                ->setPageTitle('标签编辑')
                ->addFormItems([
                    ['hidden', 'id'],
                    ['text', 'name', '名称'],
                    ['switch', 'status', '状态'],
                    ['textarea', 'description', '描述']
                ])
                ->setFormData($tag)
                ->fetch();
        }   
    }

    /**
     * 标签删除
     */
    public function delete()
    {
        $ids = $this->request->param('ids');

        $ids = is_array($ids) ? $ids : [$ids];

        foreach ($ids as $id) {
            $tag = TagModel::get($id, 'articletags');
            $tag->together('articletags')->delete();
        }

        $this->success('删除成功');
    }
}