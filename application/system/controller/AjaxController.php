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
 * @Last Modified Time: 2020-03-18 14:14:17
 */
 // ------------------------------------------------------------------------

namespace app\system\controller;

use app\common\controller\AdminBaseController;

class AjaxController extends AdminBaseController
{
	/**
     * 获取联动数据
     * @param string $token token
     * @param int $value 父级字段值
     * @return json
     */
    public function getLevelData()
    {
        $params = $this->request->param();
        if ($params['token'] == '') {
            $this->error('缺少token');
        }

        $tokenData = session($params['token']);

        $data = db($tokenData['table'])->where($tokenData['pid_field'], $params['value'])->column($tokenData['option_field'], $tokenData['key_field']);

        $this->success('请求成功', '', $data);
    }
}