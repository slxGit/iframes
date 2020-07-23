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
 * @Date:       2020-02-18 16:02:56
 *
 * @Last Modified By:   Aaron
 * @Last Modified Time: 2020-02-21 12:01:52
 */
 // ------------------------------------------------------------------------

namespace app\api\exception;

use Exception;
use think\facade\Env;
use think\exception\Handle;

class ExceptionHandler extends Handle
{
	// 响应码
	protected $code;
    // 错误信息
	protected $message;

    public function render(Exception $e)
    {	
    	// 开启debug模式，异常交给父类异常处理
    	// if (Env::get('app_debug')) {
    	// 	return parent::render($e);
    	// } 
        
    	// 参数验证错误
    	if ($e instanceof \think\exception\ValidateException) {
    		$this->message = $e->getError();
    		$this->code = 400;

    	// HTTP异常
    	} elseif ($e instanceof \think\exception\HttpException) {
    		$this->message = $e->getMessage();
    		$this->code = $e->getStatusCode();

    	// 其他错误
    	} else {
    		$this->message = $e->getMessage();
    		$this->code = 500;
            $this->recordErrorLog($e);
    	}

        $data = [
            'code'    => $this->code,
            'message' => $this->message,
            'data'    => ''
        ];

       	return json($data);
    }

    /**
     * 将异常写入数据库
     * @param Exception $e
     */
    private function recordErrorLog(Exception $e)
    {
        $errorLogModel = new \app\api\model\ErrorLogModel;
        $errorLogModel->message = $e->getMessage();
        $errorLogModel->file = $e->getFile();
        $errorLogModel->line = $e->getLine();
        $errorLogModel->save();
    }
}