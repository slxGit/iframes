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
 * @Last Modified Time: 2020-03-29 11:42:06
 */
 // ------------------------------------------------------------------------

namespace app\cms\controller;

use app\common\builder\Builder;
use app\cms\model\SliderModel;
use app\cms\validate\SliderValidate;
use app\common\controller\AdminBaseController;

class AdminSliderController extends AdminBaseController
{
	/**
     * 滚动图片列表
     */
	public function index()
	{  
        $sliderList = SliderModel::order('order', 'asc')->paginate();

        return Builder::table()
            ->addColumns([
                ['id', 'ID'],
                ['image', '图片', 'image'],
                ['title', '标题', 'text'],
                ['url', '链接', 'link'],
                ['order', '排序', 'text'],
                ['status', '状态', 'switch']
            ])
            ->addTopButtons('add,enable,disable,delete')
            ->addRightButtons('edit,delete')
            ->setTableData($sliderList)
            ->fetch();
	}

    /**
     * 滚动图片添加
     */
    public function add()
    {  
        if ($this->request->isPost()) {
            $params = $this->request->post();

            $validate = new SliderValidate;
            if (!$validate->check($params)) {
                $this->error($validate->getError());
            }

            $result = SliderModel::create($params);
            
            if ($result) {
                $this->success('添加成功', url('index'));
            } else {
                $this->error('添加失败');
            }
        } else {
            return Builder::form()
                ->setPageTitle('滚动图片添加')
                ->addFormItems([
                    ['text', 'title', '标题'],
                    ['image', 'image', '图片'],
                    ['text', 'url', '链接'],
                    ['number', 'order', '排序', '', 99, 0, 99, 0],
                    ['switch', 'status', '状态', '', 1]
                ])
                ->fetch();
        }
    }

    /**
     * 滚动图片编辑
     */
    public function edit()
    {  
        if ($this->request->isPost()) {
            $params = $this->request->post();

            $validate = new SliderValidate;
            if (!$validate->check($params)) {
                $this->error($validate->getError());
            }

            $sliderModel = new SliderModel;
            $result = $sliderModel->save($params, ['id' => $params['id']]);
            
            if ($result) {
                $this->success('保存成功', url('index'));
            } else {
                $this->error('保存失败');
            }
        } else {
            $id = $this->request->param('id', 0, 'intval');

            $slider = SliderModel::get($id);

            return Builder::form()
                ->setPageTitle('滚动图片编辑')
                ->addFormItems([
                    ['hidden', 'id'],
                    ['text', 'title', '标题'],
                    ['image', 'image', '图片'],
                    ['text', 'url', '链接'],
                    ['number', 'order', '排序', '', '', 0, 99, 0],
                    ['switch', 'status', '状态']
                ])
                ->setFormData($slider)
                ->fetch();
        }
    }
}