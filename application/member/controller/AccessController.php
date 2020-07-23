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
 * @Last Modified Time: 2020-04-05 21:58:29
 */
 // ------------------------------------------------------------------------

namespace app\member\controller;

use app\member\model\UserModel;
use app\system\model\ConfigModel;
use app\member\validate\LoginValidate;
use app\common\controller\BaseController;

class AccessController extends BaseController
{
    /**
     * 初始化
     */
    protected function initialize()
    {
    	parent::initialize();
    }

    /**
     * 默认转向登录
     */ 
    public function index()
    {
        $this->redirect(url('login'));
    }

    /**
     * 检查是否登录，已登录则跳转到首页
     */
    protected function checkLogin()
    {
        if (!empty(session('user_id', '', 'member'))) {
            $this->redirect(url('member/Entry/index'));
        }
    }

    /**
     * 登录界面
     */
	public function login()
	{
		$this->checkLogin();

        $configModel = new ConfigModel;
        $thirdConnect = $configModel->getItems('qq_login_status,wechat_login_status');
        $this->assign('third_connect', $thirdConnect);

		return $this->fetch();
	}

    /**
     * 第三方登录界面
     */    
    public function connect($type = '')
    {
        switch ($type) {
            case 'qq':
                $oauth = \thirdconnect\Oauth::qq();
                $oauth->login();
                break;
            
            case 'wechat':
                $oauth = \thirdconnect\Oauth::wechat();
                $oauth->login();
                break;
        }
    }

    /**
     * 第三方登录回调
     */  
    public function callback($type = '')
    {
        switch ($type) {
            case 'qq':
                $configModel = new ConfigModel;
                $configs = $configModel->getItems('qq_login_appid,qq_login_appkey');
                $oauth = \thirdconnect\Oauth::qq([
                    'APPID' => $configs['qq_login_appid'],
                    'APPSECRET' => $configs['qq_login_appkey'],
                    'CALLBACK'  => request()->domain(). '/member/access/callback?type=qq'
                ]);
                $openid = $oauth->getOpenid();
                $userModel = new UserModel;
                $user = $userModel->where('qq_openid', $openid)->find();
                if (!$user) {
                    $info = $oauth->getUserinfo();
                    $result = $userModel->register([
                        'qq_openid'   => $info['openid'],
                        'nickname'    => $info['nickname'],
                        'sex'         => $info['gender'],
                        'avatar'      => $info['avatar']
                    ]);
                    if ($result) {
                        $this->doLogin($userModel->find($userModel->id));
                    } else {
                        $this->error('注册用户失败');
                    }
                } else {
                    $this->doLogin($user);
                }
                break;
            
            case 'wechat':
                $configModel = new ConfigModel;
                $configs = $configModel->getItems('wechat_login_appid,wechat_login_appsecret');
                $oauth = \thirdconnect\Oauth::wechat([
                    'APPID' => $configs['wechat_login_appid'],
                    'APPSECRET' => $configs['wechat_login_appsecret'],
                    'CALLBACK'  => request()->domain(). '/member/access/callback?type=wechat'
                ]);
                $openid = $oauth->getOpenid();
                $userModel = new UserModel;
                $user = $userModel->where('wechat_openid', $openid)->find();
                if (!$user) {
                    $info = $oauth->getUserinfo();
                    $result = $userModel->register([
                        'wechat_openid' => $info['openid'],
                        'nickname'      => $info['nickname'],
                        'sex'           => $info['gender'],
                        'avatar'        => $info['avatar']
                    ]);
                    if ($result) {
                        $this->doLogin($userModel->find($userModel->id));
                    } else {
                        $this->error('注册用户失败');
                    }
                } else {
                    $this->doLogin($user);
                }
                break;
        }
    }

    /**
     * 手机短信登录提交
     */    
    public function smsLoginPost()
    {   
        $params = $this->request->param();
        $validate = new loginValidate;
        if (!$validate->scene('sms_login')->check($params)) {
            $this->error($validate->getError());
        }

        cache('smscode_' . $params['mobile'], NULL);

        $userModel = new UserModel;
        $user = $userModel->where('mobile', $params['mobile'])->find();

        if (!$user) {
            $result = $userModel->register([
                'mobile'   => $params['mobile'],
                'nickname' => $this->createNickname()
            ]);
            if ($result) {
                $this->doLogin($userModel->find($userModel->id));
            } else {
                $this->error('注册用户失败');
            }
        } else {
            $this->doLogin($user);
        }
    }

    /**
     * 发送短信验证码
     */
    public function sendSmscode()
    {
        $params = $this->request->param();
        
        // 短信配置参数
        $configModel = new ConfigModel;
        $field = 'ali_sms_accesskeyid,ali_sms_accesskeysecret,ali_sms_signname,ali_sms_templatecode';
        $params = array_merge($configModel->getItems($field), $params);

        $validate = new loginValidate;
        if (!$validate->scene('send_smscode')->check($params)) {
            $this->error($validate->getError());
        }

        $mobile = $params['mobile'];

        if(!empty(cache('smscode_cooling_' . $mobile))){
            $this->error('您的短信发送过于频繁，请稍后再试');
        }

        $code = rand(100000, 999999);

        $client  = new \Flc\Dysms\Client([
            'accessKeyId' => $params['ali_sms_accesskeyid'], 
            'accessKeySecret' => $params['ali_sms_accesskeysecret']
        ]);
        $sendSms = new \Flc\Dysms\Request\SendSms;
        $sendSms->setPhoneNumbers($mobile);
        $sendSms->setSignName($params['ali_sms_signname']);
        $sendSms->setTemplateCode($params['ali_sms_templatecode']);
        $sendSms->setTemplateParam(['code' => $code]);
        $result = $client->execute($sendSms);

        if ($result->Code === 'OK') {
            // 设置验证码的有效期
            cache('smscode_' . $mobile, $code, 300);
            // 设置可以再次发送验证码的冷却期
            cache('smscode_cooling_' . $mobile, 1, 60);
            $this->success("短信验证码已发送至+86{$mobile}");
        } else {
            $this->error($result->Message);
        }
    }

    // 登录行为
    protected function doLogin($user)
    {
        if ($user->getData('status') == 0) {
            $this->error('用户已禁用');
        }
        session('user_id', $user['id'], 'member');
        session('user_name', $user['nickname'], 'member');
        session('user_avatar', $user['avatar'], 'member');
        $userModel = new UserModel;
        $userModel->afterLogin($user['id']);
        $this->success('登录成功', url('cms/category/index'));
    }

    /**
     * 随机生成昵称
     */
    protected function createNickname($length = 10)
    {
        $pattern = '1234567890abcdefghijklmnopqrstuvwxyz';
        $range = strlen($pattern) - 1;
        $nickname = '';
        for ($i = 0; $i < $length; $i++) {
            $nickname .= $pattern{mt_rand(0, $range)}; 
        }
        return $nickname;
    }

    /**
     * 退出登录
     */
    public function logout()
    {        
        session('user_id', null, 'member');
        session('user_name', null, 'member');
        session('user_avatar', null, 'member');
        $this->redirect(url('cms/category/index'));
    }
}