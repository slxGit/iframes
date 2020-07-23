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
 * @Last Modified Time: 2020-03-28 22:56:19
 */
 // ------------------------------------------------------------------------

namespace app\api\controller;

use app\common\builder\Builder;
use app\api\model\ErrorLogModel;
use app\common\controller\AdminBaseController;

class AdminErrorLogController extends AdminBaseController
{
	/**
     * 错误日志列表
     */
    public function index()
    {
        return Builder::table()
            ->addColumns([
                ['id', 'ID'],
                ['message', '错误消息'],
                ['file', '文件位置'],
                ['line', '行数'],
                ['created', '日期']
            ])
            ->addRightButton('detail', [
            	'title'   => '详情',
                'class'   => 'layui-btn layui-btn-xs',
                'url'     => url('detail', ['id' => '__id__']),
                'method'  => 'href'
            ])
            ->setTableData(ErrorLogModel::order('id', 'desc')->paginate())
            ->fetch();
    }
  
    /**
     * 错误日志详情
     */
    public function detail()
    {
    	$id = $this->request->param('id', 0, 'intval');

		return Builder::form()
			->setPageTitle('错误日志详情')
			->addFormItems([
                ['text', 'id', 'ID', '', '', 'disabled'],
                ['textarea', 'message', '错误消息', '', '', 'disabled'],
                ['textarea', 'file', '文件位置', '', '', 'disabled'],
                ['text', 'line', '行数', '', '', 'disabled'],
                ['text', 'created', '日期', '', '', 'disabled']
			])
			->setFormData(ErrorLogModel::get($id))
			->delBtn('submit')
			->fetch();
    }
}