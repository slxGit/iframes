<?php

/**
 * 广告后台管理 广告位置管理
 */
namespace app\adv\controller;

use app\adv\model\ContentModel;
use app\adv\model\PositionModel;
use app\adv\validate\ContentValidate;
use app\adv\validate\PositionValidate;
use app\common\builder\Builder;
use app\common\controller\AdminBaseController;

class AdminAdvPositionController extends AdminBaseController
{
    /**
     * 属性
     */    
    protected $adv_platform = [
        1 => '网页',
        2 => '小程序',
        3 => 'H5'
    ];

	/**
     * 广告位置列表
     */
	public function index()
	{  
        $params = $this->request->param();

        // 查询条件
        $map = [];

        // 位置(查询条件)
        if (!empty($params['name'])) {
            $map[] = ['name', 'like', '%' . $params['name'] . '%'];
        }

        // 投放平台(查询条件)
        if (!empty($params['adv_platform'])) {
            $map[] = ['adv_platform', '=', $params['adv_platform']];
        }

        // 广告内容查询
        $advContentList = PositionModel:: where($map)
            ->order('updated', 'desc')
            ->paginate()->each(function($item){
                if ($item->adv_platform == 1) $adv_platform = '网页';
                if ($item->adv_platform == 2) $adv_platform = '小程序';
                if ($item->adv_platform == 3) $adv_platform = 'H5';
                if (isset($adv_platform)) $item->adv_platform = $adv_platform;
            });
        return Builder::table()
            ->addColumns([
                ['id', 'ID', '', '', ['width' => '6%']],
                ['name', '位置', '', '', ['width' => '10%']],
                ['adv_platform','投放平台','','',['width' => '10%']],
                ['adv_width', '宽度', '', '', ['width' => '10%']],
                ['adv_height', '高度', '', '', ['width' => '10%']],
                ['remark', '备注', '', '', ['width' => '10%']],
                ['is_open', '是否开启', 'switch', '', ['width' => '10%']],
                ['updated', '更新时间', '', '', ['width' => '15%']],
            ])
            ->addTopButtons('add')
            ->addTopButton('publish', [
                'title'    => '关闭',
                'method'   => 'ajax',
                'url'      => url('is_open'),
                'param'    => [
                    'ids'  => '__id__'
                ],
                'complete' => 'refresh',
                'class'    => 'layui-btn layui-btn-sm'
            ])
            ->addTopButton('delete')
            ->addSearchItems([
                ['text', 'name', '位置'],
                ['xmselect', 'adv_platform', '投放平台', $this->adv_platform, [], 3]
            ])
            ->addRightButtons('edit,delete')
            ->setRightToolbar(['width' => '15%'])
            ->setTableData($advContentList)
            ->fetch();
	}

    /**
     * 广告位置添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();
            $validate = new PositionValidate();
            if (!$validate->check($params)) {
                $this->error($validate->getError());
            }
            $positionModel = new PositionModel();
            $result = $positionModel->save($params);
            if ($result) {
                $this->success('添加成功', url('index'));
            } else {
                $this->error('添加失败');
            }
        } else {
            return Builder::form()
                ->setPageTitle('位置添加')
                ->addFormItems([
                    ['text', 'name', '位置', ''],
                    ['radio', 'adv_platform', '投放平台', '', $this->adv_platform],
                    ['text', 'adv_width', '宽度'],
                    ['text', 'adv_height', '高度'],
                    ['textarea', 'remark', '备注'],
                    ['switch', 'is_open', '是否显示','',1],

                ])
                ->fetch();
        }
    }

    /**
     * 广告位置编辑
     */
    public function edit()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();

            $validate = new PositionValidate();
            if (!$validate->check($params)) {
                $this->error($validate->getError());
            } 
            $positionModel = new PositionModel();
            $result = $positionModel->save($params, ['id' => $params['id']]);

            if ($result) {
                $this->success('保存成功', url('index'));
            } else {
                $this->error('保存失败');
            }
        } else {
            $id = $this->request->param('id', 0, 'intval');
            $article = PositionModel::get($id);
            return Builder::form()
                ->setPageTitle('位置编辑')
                ->addFormItems([
                    ['hidden', 'id'],
                    ['text', 'name', '位置', ''],
                    ['radio', 'adv_platform', '投放平台', '', $this->adv_platform],
                    ['text', 'adv_width', '宽度'],
                    ['text', 'adv_height', '高度'],
                    ['textarea', 'remark', '备注'],
                    ['switch', 'is_open', '是否显示',''],
                ])
                ->setFormData($article)
                ->fetch();
        }        
    }

    /**
     * 广告位置删除
     */
    public function delete()
    {
        $ids = $this->request->param('ids');

        $ids = is_array($ids) ? $ids : [$ids];


        // 删除广告位置
        $result = PositionModel::destroy($ids);

        if ($result) {
            // 相关广告内容也就被关闭
            ContentModel::where('position_id','in',$ids)->delete();
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    /**
     * 禁用
     */    
    public function is_open($ids = '')
    {
        $result = PositionModel::where('id', 'in', $ids)->update(['is_open' => 0, 'updated' => time()]);
        if ($result !== false) {
            // 相关广告内容也就被关闭
            ContentModel::where('position_id','in',$ids)->update(['is_enabled' => 0, 'updated' => time()]);
            $this->success('关闭成功');
        } else {
            $this->error('关闭失败');
        }
    }

    /**
     * 快捷编辑
     */
    public function quickEdit()
    {
        $params = $this->request->param();
        switch ($params['name']) {
            case 'is_open':
                if ($params['value'] == 1) {
                    $result = PositionModel::where('id', $params['id'])->update(['is_open' => 1, 'updated' => time()]);
                } else {
                    $result = PositionModel::where('id', $params['id'])->update(['is_open' => 0, 'updated' => time()]);
                }
                break;
        }

        if ($result !== false) {
            // 相关广告内容也就被关闭
            ContentModel::where('position_id',$params['id'])->update(['is_enabled' => 0, 'updated' => time()]);
            $this->success('保存成功');
        } else {
            $this->error('保存失败');
        }
    }


}