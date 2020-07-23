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
 * @Date:       2020-02-10 12:31:48
 *
 * @Last Modified By:   Aaron
 * @Last Modified Time: 2020-04-09 15:26:05
 */
 // ------------------------------------------------------------------------
 
namespace app\common\controller;

use app\system\model\ManagerModel;
use app\system\model\AuthRuleModel;
use app\system\model\AuthAccessModel;
use app\common\controller\BaseController;

class AdminBaseController extends BaseController
{
    /**
     * 管理员ID
     */
    protected $managerId;

    /**
     * 初始化
     */
    protected function initialize()
    {
        parent::initialize();

        $this->managerId = $this->checkLogin();

        $this->checkAccess();
    }

    /**
     * 检查是否登录，没有登录则跳转到登录页面
     */
    final protected function checkLogin()
    {
    	$managerId = session('manager_id', '', 'admin');

        if (!empty($managerId)) {
            return $managerId;
        } else {
            if ($this->request->isAjax()) {
                $this->error('您没有访问权限！');
            } else {
                $url = url('system/Access/login');
                $redirectJS = <<<EOF
                    <script type="text/javascript">
                            window.top.location.href = "$url";
                    </script>
EOF;
                echo $redirectJS;                
            }

        }
    }

    /**
     * 检查是否可以访问该路由
     * @return boolean
     */
    final protected function checkAccess()
    {
        $module = $this->request->module();
        $controller = \think\Loader::parseName($this->request->controller(), 0);
        $action = $this->request->action();
        if (!$this->checkAuth($module . '/' . $controller . '/' . $action)) {
            $this->error('您没有访问权限！', url('system/Main/index'));
        };
    }

    /**
     * 检查规则名称的访问权限
     * @param $route name 规则名称 模块名称/控制器名称/方法名称
     * @return boolean
     */
    final protected function checkAuth($name)
    {
        // 查询不到管理员ID
        if (empty($this->managerId)) {
            return false;
        }

        // 管理员为Admin用户
        if ($this->managerId == 1) {
            return true;
        }
  
        $name = strtolower($name);

        // 查询路由是否受权限控制
        $hasAuthRule = AuthRuleModel::where('name', $name)->count();
        if (!$hasAuthRule) {
            return true;
        }

        // 获取管理员拥有的所有角色ID
        $managerModel = new ManagerModel;
        $managerModel->id = $this->managerId;
        $roleIds = array_column($managerModel->roles()->where('status', 1)->select()->toArray(), 'id');

        // 没有角色
        if (empty($roleIds)) {
            return false;
        }

        // 角色为最高管理员
        if (in_array(1, $roleIds)) {
            return true;
        }

        // 查询拥有的角色下的所有规则
        $ruleNames = AuthAccessModel::where('role_id', 'in', $roleIds)->column('rule_name');

        if (in_array($name, $ruleNames)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取当前操作模型
     * @return object|\think\db\Query
     */
    final protected function getCurrModel()
    {
        $tableToken = input('post._t', '');
        $module = $this->request->module();
        $controller = parse_name($this->request->controller());

        $tableToken == '' && $this->error('缺少参数');
        !session('?'  .$tableToken) && $this->error('参数错误');

        $tableData = session($tableToken);
   
        $table = $tableData['table'];

        $table == '' && $this->error('缺少表名');

        if ($tableData['module'] != $module || $tableData['controller'] != $controller) {
            $this->error('非法操作');
        }

        $model = \think\Db::name($table);

        return $model;
    }

    /**
     * 启用
     */
    public function enable()
    {
        return $this->setStatus('enable');
    }

    /**
     * 禁用
     */
    public function disable()
    {
        return $this->setStatus('disable');
    }

    /**
     * 删除
     */
    public function delete()
    {
        return $this->setStatus('delete');
    }

    /**
     * 设置状态
     * @param string $type 操作类型：启用(enable)、禁用(disable)、删除(delete)
     */
    protected function setStatus($type = '')
    {
        $ids = $this->request->post('ids');
  
        !is_array($ids) && $ids = array_map('intval', explode(',', $ids));
  
        $field = $this->request->post('field', 'status');

        empty($ids) && $this->error('缺少主键');
        
        $model = $this->getCurrModel();

        $where = [
            ['id', 'in', $ids]
        ];

        $result = false;

        switch ($type) {
            // 启用
            case 'enable': 
                $result = $model->where($where)->setField($field, 1);
                break;
            // 禁用
            case 'disable': 
                $result = $model->where($where)->setField($field, 0);
                break;
            // 删除
            case 'delete': 
                $result = $model->where($where)->delete();
                break;

            default:
                $this->error('非法操作');
                break;
        }

        if ($result !== false) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }

    }

    /**
     * 快速编辑
     */
    public function quickEdit()
    {
        $id    = input('post.id', '');
        $field = input('post.name', '');
        $value = input('post.value', '');
        $type  = input('post.type', '');

        $id    == '' && $this->error('缺少主键值');
        $field == '' && $this->error('缺少字段名');

        $model = $this->getCurrModel();

        switch ($type) {
            case 'date':
                $value = strtotime($value);
                break;
        }

        $result = $model->where('id', $id)->setField($field, $value);

        if ($result !== false) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }
}