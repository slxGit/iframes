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
 * @Date:       2020-02-18 01:35:55
 *
 * @Last Modified By:   Aaron
 * @Last Modified Time: 2020-03-10 12:11:08
 */
 // ------------------------------------------------------------------------

namespace app\api\controller;

use think\Response;
use think\exception\HttpResponseException;

trait Send
{
    /**
     * API请求成功返回数据的快捷方法
     * @access protected
     * @param mixed  $data    返回的数据
     * @param mixed  $message 提示信息
     * @param array  $header  发送的 Header 信息
     * @return void
     * @throws HttpResponseException
     */
    protected static function sendSuccess($data = '', $message = '', array $header = [])
    {   
        $result = [
            'code'     => 200,
            'message'  => $message == '' ? 'OK' : (string)$message,
            'data'     => $data
        ];

        $response = Response::create($result, 'json')->header($header);

        throw new HttpResponseException($response);
    }

    /**
     * API请求失败返回数据的快捷方法
     * @access protected
     * @param int    $code    状态码
     * @param mixed  $message 提示信息
     * @param mixed  $data    返回的数据
     * @param array  $header  发送的 Header 信息
     * @return void
     * @throws HttpResponseException
     */
    protected static function sendError($code, $message = '', $data = '', array $header = [])
    {   
        $result = [
            'code'    => (int)$code,
            'message' => (string)$message,
            'data'    => $data
        ];

        $response = Response::create($result, 'json')->header($header);
        
        throw new HttpResponseException($response);
    }
}