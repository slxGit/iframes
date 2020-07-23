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
 * @Date:       2020-02-18 01:09:45
 *
 * @Last Modified By:   Aaron
 * @Last Modified Time: 2020-03-10 12:03:40
 */
 // ------------------------------------------------------------------------

namespace app\api\controller;

use app\api\controller\Send;

class ErrorController
{
    use Send;

    /**
     * 空方法
     */
    public function _empty()
    {
        $this->sendError(404, 'Not Found');
    }
}