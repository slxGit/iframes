<?php


namespace app\adv\controller;

use app\adv\model\ContentModel;
use app\adv\model\PositionModel;
use app\adv\validate\PositionByIdValidate;
use app\adv\validate\PositionValidate;
use app\api\controller\Send;
use app\common\controller\AdvBaseController;

class AdvShowController extends AdvBaseController
{
    use Send;
	public function advPositionShow()
	{
        $params = $this->request->param();
        $validate = new PositionByIdValidate();
        if (!$validate->check($params)) {
            $this->error($validate->getError());
        }
        $res = PositionModel::advPositionList($params['position_id'],$params['adv_platform']);

        if (empty($res)){
            $this->sendError('404','暂无广告');
        }
        $this->sendSuccess($res);
	}
}