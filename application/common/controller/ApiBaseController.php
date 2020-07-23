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
 * @Last Modified Time: 2020-04-14 15:44:22
 */
 // ------------------------------------------------------------------------
 
namespace app\common\controller;

use app\api\controller\Send;
use app\api\controller\Oauth;
use app\common\controller\BaseController;

class ApiBaseController extends BaseController
{
    use Send;

    // 用户ID
    protected $userId;

    // 用户信息
    protected $clientInfo;

	// 白名单
	protected $whiteList = [];

    /**
     * 初始化
     */
    protected function initialize()
    {
        parent::initialize();

		if (Oauth::needAuth($this->whiteList)) {
    		$this->clientInfo = Oauth::authenticate();
            $this->userId = $this->clientInfo['user_id'];
		}
    }

   	/**
	 * 空方法
	 */
	public function _empty()
    {
        $this->sendError(404, 'Not Found');
    }
}