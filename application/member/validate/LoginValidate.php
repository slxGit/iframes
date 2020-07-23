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
 * @Last Modified Time: 2020-04-05 21:57:43
 */
 // ------------------------------------------------------------------------
namespace app\member\validate;

use think\Validate;

class LoginValidate extends Validate
{
    protected $rule = [
        'mobile'                  => 'require|mobile',
        'smscode'                 => 'require|checkSmscode',
        'ali_sms_accesskeyid'     => 'require',
        'ali_sms_accesskeysecret' => 'require',
        'ali_sms_signname'        => 'require',
        'ali_sms_templatecode'    => 'require'
    ];
    protected $message = [
        'mobile.require'                  => '请输入手机号',
        'mobile.mobile'                   => '手机号码不正确',
        'smscode.require'                 => '请输入验证码',
        'smscode.checkSmscode'            => '验证码不正确',
        'ali_sms_accesskeyid.require'     => '请配置参数：AccessKeyId',
        'ali_sms_accesskeysecret.require' => '请配置参数：AccessKeySecret',
        'ali_sms_signname.require'        => '请配置参数：SignName',
        'ali_sms_templatecode.require'    => '请配置参数：TemplateCode'
    ];

    protected $scene = [
        // 发送短信验证码
        'send_smscode' => ['ali_sms_accesskeyid', 'ali_sms_accesskeysecret', 'ali_sms_signname', 'ali_sms_templatecode', 'mobile'],
        // 手机短信登录
        'sms_login'    => ['mobile', 'smscode']
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
	