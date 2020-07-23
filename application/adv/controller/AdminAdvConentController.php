<?php

/**
 * 广告后台管理 广告内容管理
 */
namespace app\adv\controller;

use app\adv\model\ContentModel;
use app\adv\model\PositionModel;
use app\adv\validate\ContentValidate;
use app\common\builder\Builder;
use app\common\controller\AdminBaseController;

class AdminAdvConentController extends AdminBaseController
{
    /**
     * 属性
     */    
    protected $type = [
        1 => '图片',
        2 => '轮播',
        3 => '视频',
    ];

	/**
     * 广告内容列表
     */
	public function index()
	{  
        $params = $this->request->param();

        // 查询条件
        $map = [];

        // 标题(查询条件)
        if (!empty($params['title'])) {
            $map[] = ['title', 'like', '%' . $params['title'] . '%'];
        }

        // 分类(查询条件)
        if (!empty($params['type'])) {
            $map[] = ['type', '=', $params['type']];
        }

        // 广告内容查询
        $advContentList = ContentModel::with('position')
            ->field('type,position.name,content_url,title,link,is_enabled,starttime,endtime')
            ->where($map)
            ->order('updated', 'desc')
            ->paginate()->each(function($item){
                if ($item->type == 1) $type = '图片';
                if ($item->type == 2) $type = '轮播';
                if ($item->type == 3) $type = '视频';
                if (isset($type)) $item->type = $type;
                if (strtotime($item->endtime) <= time()){
                    $item->static = '已结束';
                }else if (strtotime($item->starttime) >= time()){
                    $item->static = '未开始';
                }else{
                    $item->static = '正常';
                }
            });
        return Builder::table()
            ->addColumns([
                ['id', 'ID', '', '', ['width' => '6%']],
                ['type', '广告类型', '', '', ['width' => '10%']],
                ['name','位置','','',['width' => '20%']],
                ['content_url', '图片', 'image', '', '', ['width' => '10%']],
                ['title', '标题', '', '', ['width' => '15%']],
                ['is_enabled', '是否显示', 'switch', '', ['width' => '10%']],
                ['static', '状态', '', '', ['width' => '15%']],
                ['starttime', '开始时间', '', '', ['width' => '15%']],
                ['endtime', '结束时间', '', '', ['width' => '15%']]
            ])
            ->addTopButtons('add')
            ->addTopButton('publish', [
                'title'    => '禁用',
                'method'   => 'ajax',
                'url'      => url('disable'),
                'param'    => [
                    'ids'  => '__id__'
                ],
                'complete' => 'refresh',
                'class'    => 'layui-btn layui-btn-sm'
            ])
            ->addTopButton('delete')
            ->addSearchItems([
                ['text', 'title', '标题'],
                ['xmselect', 'type', '广告类型', $this->type, [], 4]
            ])
            ->addRightButtons('edit,delete')
            ->setRightToolbar(['width' => '15%'])
            ->setTableData($advContentList)
            ->fetch();
	}

    /**
     * 广告内容添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();

            $validate = new ContentValidate();
            if (!$validate->check($params)) {
                $this->error($validate->getError());
            }
            $this->time_validate_params($params);
            $contentModel = new ContentModel();
            $result = $contentModel->save($params);
            if ($result) {
                $this->success('添加成功', url('index'));
            } else {
                $this->error('添加失败');
            }
        } else {
            $categoryList = PositionModel::categoryList();
            return Builder::form()
                ->setPageTitle('广告添加')
                ->addFormItems([
                    ['select', 'position_id', '类型', '', $categoryList],
                    ['text', 'title', '标题'],
                    ['radio', 'type', '类型', '', $this->type],
                    ['text', 'carousel', '轮播图位置选择（1-5）', ''],
                    ['image', 'content_url', '图片', '', '', '', '100x100'],
                    ['file', 'content_video', '视频','','',''],
                    ['text', 'link', '跳转链接'],
                    ['text', 'link_name', '投放人名字'],
                    ['text', 'link_email', '投放人邮箱'],
                    ['text', 'link_phone', '投放人手机号  '],
                    ['date', 'starttime', '开始时间'],
                    ['date', 'endtime', '结束时间'],
                    ['switch', 'is_enabled', '是否显示','',1],

                ])
                // radio的触发器
                ->setTriggers([
                    ['type', 2, 'carousel'],
                    ['type', 3, 'content_video'],
                ])
                ->fetch();
        }
    }

    /**
     * 广告内容编辑
     */
    public function edit()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();
            $validate = new ContentValidate();
            if (!$validate->check($params)) {
                $this->error($validate->getError());
            }
            $this->time_validate_params($params,$params['id']);
            $articleModel = new ContentModel();
            $result = $articleModel->save($params, ['id' => $params['id']]);

            if ($result) {
                $this->success('保存成功', url('index'));
            } else {
                $this->error('保存失败');
            }
        } else {
            $id = $this->request->param('id', 0, 'intval');
            $article = ContentModel::get($id);
            $categoryList = PositionModel::categoryList();
            return Builder::form()
                ->setPageTitle('广告内容编辑')
                ->addFormItems([
                    ['hidden', 'id'],
                    ['select', 'position_id', '类型', '', $categoryList],
                    ['text', 'title', '标题'],
                    ['radio', 'type', '类型', '', $this->type],
                    ['text', 'carousel', '轮播图位置选择（1-5）', ''],
                    ['image', 'content_url', '图片', '', '', '', '100x100'],
                    ['file', 'content_video', '视频','','','','','avi,mp3,mp4'],
                    ['text', 'link', '跳转链接'],
                    ['text', 'link_name', '投放人名字'],
                    ['text', 'link_email', '投放人邮箱'],
                    ['text', 'link_phone', '投放人手机号  '],
                    ['date', 'starttime', '开始时间'],
                    ['date', 'endtime', '结束时间'],
                    ['switch', 'is_enabled', '是否显示'],
                ])
                // radio的触发器
                ->setTriggers([
                    ['type', 2, 'carousel'],
                    ['type', 3, 'content_video'],
                ])
                ->setFormData($article)
                ->fetch();
        }        
    }

    /**
     * 广告内容删除
     */
    public function delete()
    {
        $ids = $this->request->param('ids');

        $ids = is_array($ids) ? $ids : [$ids];


        // 删除广告内容
        $result = ContentModel::destroy($ids);

        if ($result) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    /**
     * 禁用
     */    
    public function disable($ids = '')
    {
        $result = ContentModel::where('id', 'in', $ids)->update(['is_enabled' => 0, 'updated' => time()]);
        if ($result !== false) {
            $this->success('禁用成功');
        } else {
            $this->error('禁用成功');
        }
    }

    /**
     * 快捷编辑
     */
    public function quickEdit()
    {
        $params = $this->request->param();
 
        switch ($params['name']) {
            case 'is_enabled':
                if ($params['value'] == 1) {
                    ContentModel::is_position_exist($params['id']) ? true : $this->error('位置未查询到！');
                    $result = ContentModel::where('id', $params['id'])->update(['is_enabled' => 1, 'updated' => time()]);
                } else {
                    $result = ContentModel::where('id', $params['id'])->update(['is_enabled' => 0, 'updated' => time()]);
                }
                break;

        }

        if ($result !== false) {
            $this->success('保存成功');
        } else {
            $this->error('保存失败');
        }
    }

    /**
     * 时间校验
     * @param $params
     */
    public function time_validate_params($params,$id = 0){
        $time = date('Y-m-d',time());
        if(strtotime($params['starttime']) < strtotime($time)) $this->error('开始时间不能小于当前时间');

        if($params['starttime'] >= $params['endtime']) $this->error('开始时间不能大于结束时间');

        $contentModel = new ContentModel();
        // 验证指定位置轮播图 是否存在
        if ($params['type'] == 2){
            if ($params['carousel'] > 5 || $params['carousel'] <= 0) $this->error('轮播添加失败请输入（1-5）');

            $contentModel->is_position_carousel($params['position_id'],$params['carousel'],$params['starttime'],$id) ? true : $this->error('当前轮播广告已存在且未过期添加失败') ;
        }
    }
}