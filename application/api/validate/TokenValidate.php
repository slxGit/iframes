<?php

/**
 * KirinBDF
 *
 * A Kirin Base Development Framework Based on ThinkPHP & Layui
 *
 * @System:     KirinBDF
 * @Version:    Version 1.0
 * @Link:       http://www.kirinbdf.com/
 * @Copyright:  Copyright (c) 2017 - 2019, KirinBDF Team.
 *
 * @Author:  Aaron
 * @Email:   wo1425768@163.com
 * @Date:    2019-05-06 15:17:44
 *
 * @Last Modified By:   Aaron
 * @Last Modified Time: 2020-03-10 12:08:34
 */
 // ------------------------------------------------------------------------
namespace app\api\validate;

use app\common\validate\ApiBaseValidate;

class TokenValidate extends ApiBaseValidate
{
    protected $rule = [
        'appid'          => 'require',
        'nonce'          => 'require',
        'timestamp'      => 'require|number',
        'sign'           => 'require',
        'mobile'         => 'require|mobile',
        'smscode'        => 'require|checkSmscode',
        'code'           => 'require',
        'iv'             => 'require',
        'encrypted_data' => 'require'

    ];

    protected $message = [
        'appid.require'          => 'appid不正确',
        'nonce.require'          => 'nonce不正确',
        'timestamp.require'      => 'timestamp不正确',
        'timestamp.number'       => 'timestamp格式不正确',
        'sign.require'           => 'sign不正确',
        'mobile.require'         => 'mobile不正确',
        'mobile.mobile'          => 'mobile不正确',
        'smscode.require'        => 'smscode不正确',
        'smscode.checkSmscode'   => 'smscode不正确',
        'code.require'           => 'code不正确',
        'iv.require'             => 'iv不正确',
        'encrypted_data.require' => 'encrypted_data不正确',
    ];

    protected $scene = [
        // 手机短信
        'mobile' => ['appid', 'nonce', 'timestamp', 'sign', 'mobile', 'smscode'],
        // 微信小程序
        'wechatmini' => ['code', 'iv', 'encrypted_data']
    ];

    protected function checkSmscode($smscode, $rule, $data)
    {   
        if ($smscode != cache('smscode_' . $data['mobile'])) {
            return false;
        } else {
            return true;
        }
    }
}
	