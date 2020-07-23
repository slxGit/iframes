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
 * @Last Modified Time: 2020-04-14 15:07:45
 */
 // ------------------------------------------------------------------------

namespace app\api\controller;

use app\api\model\AppModel;
use app\member\model\UserModel;
use app\api\controller\Send;
use app\api\controller\Oauth;
use app\api\validate\TokenValidate;
use app\common\controller\BaseController;

class TokenController extends BaseController
{   
    use Send;

    // 请求时间差限制
    protected $timeDif = 300;

    /**
     * 初始化
     */
    protected function initialize()
    {
        parent::initialize();
    }

    /**
     * 获取应用密钥
     */
    private function getAppsecret($appid = '')
    {
        return AppModel::where('appid', $appid)->value('appsecret');
    }

    /**
     * 手机验证码获取token
     * @param string $appid 应用ID
     * @param string $nonce 随机数
     * @param int $timestamp 时间戳
     * @param string $sign 签名
     * @param string $mobile 手机号
     * @param string $smscode 验证码
     */
    public function mobile()
    {
        $params = $this->request->post();

        $validate = new TokenValidate;
        $validate->goCheck($params, 'mobile');
        
        $this->checkParams($params);

        cache('smscode_' . $params['mobile'], NULL);

        $userModel = new UserModel;
        $user = $userModel->where('mobile', $params['mobile'])->find();

        if (!$user) {
            $result = $userModel->register([
                'mobile'   => $params['mobile'],
                'nickname' => $this->createNickname()
            ]);
            if ($result) {
                $user = $userModel->find($userModel->id);
            } else {
                $this->sendError(500, '注册用户失败');
            }
        }

        if ($user->getData('status') == 0) {
            $this->sendError(403, '用户已禁用');
        }

        $userModel->afterLogin($user->id);

        $clientInfo = [
            'user_id'  => $user->id,
            'mobile'   => $user->mobile,
            'nickname' => $user->nickname
        ];

        $accessToken = $this->setAccessToken($clientInfo);

        $this->sendSuccess($accessToken);
    }

    /**
     * 微信小程序获取token
     * @param string $code 用户登录凭证
     * @param string $iv 用户信息加密算法的初始向量
     * @param string $encrypted_data 用户信息加密数据
     */
    public function wechatmini()
    {
        $params = $this->request->post();

        $validate = new TokenValidate;
        $validate->goCheck($params, 'wechatmini');

        // 获取seesionKey
        $sessionKey = $this->getWechatMiniSessionKey($params['code']);

        // 获取解密数据(用户信息)
        $userInfo = $this->getWechatMiniDecryptedData($sessionKey, $params['encrypted_data'], $params['iv']);

        $userModel = new UserModel;
        $user = $userModel->where('wechat_mini_openid', $userInfo['openId'])->find();

        if (!$user) {
            $result = $userModel->register([
                'wechat_mini_openid' => $userInfo['openId'],
                'nickname'           => $userInfo['nickName'],
                'avatar'             => $userInfo['avatarUrl'],
                'sex'                => $userInfo['gender']
            ]);
            if ($result) {
                $user = $userModel->find($userModel->id);
            } else {
                $this->sendError(500, '注册用户失败');
            }
        }

        if ($user->getData('status') == 0) {
            $this->sendError(403, '用户已禁用');
        }

        $userModel->afterLogin($user->id);

        $clientInfo = [
            'user_id' => $user->id,
            'mobile'   => $user->mobile,
            'nickname' => $user->nickname
        ];

        $accessToken = $this->setAccessToken($clientInfo);

        $this->sendSuccess($accessToken);
    }

    /**
     * 参数检测
     */
    protected function checkParams($params = [])
    {   
        // 时间戳校验
        if (abs($params['timestamp'] - time()) > $this->timeDif) {
            $this->sendError(400, '请求时间戳和服务器时间戳异常');
        }

        // appid检测
        $appid = AppModel::where('appid', $params['appid'])->find();
        if (!$appid) {
            $this->sendError(400, 'appid不正确');
        } elseif ($appid->status == 0) {
            $this->sendError(400, 'appid被禁用');
        }

        // 签名检测
        if (Oauth::sign($params, $this->getAppsecret($params['appid'])) !== $params['sign']) {
            $this->sendError(400, 'sign不正确');
        }
    }

    /**
     * 设置AccessToken
     * @param $clientInfo 用户信息
     * @return int
     */
    protected function setAccessToken($clientInfo)
    {
        // 生成令牌
        $accessToken = $this->buildAccessToken();

        // 返回给客户端的AccessToken信息
        $accessTokenInfo = [
            'access_token'  => $accessToken,
            'expires_time'  => time() + Oauth::$expires
        ];

        // 存储AccessToken
        $this->saveAccessToken($accessToken, $clientInfo);

        return $accessTokenInfo;
    }

    /**
     * 生成AccessToken令牌
     * @return string
     */
    protected function buildAccessToken()
    {
        return sha1(md5(uniqid(md5(microtime(true)), true)));
    }

    /**
     * 存储AccessToken
     * @param $accessToken
     * @param $accessTokenValue
     */
    protected function saveAccessToken($accessToken, $accessTokenValue)
    {
        cache(Oauth::$accessTokenPrefix . $accessToken, $accessTokenValue, Oauth::$expires);
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

    // 获取微信小程序的session_key
    protected function getWechatMiniSessionKey($code)
    {
        $params = [
            'appid'      => config('wechat_mini_appid'),
            'secret'     => config('wechat_mini_appsecret'),
            'js_code'    => $code,
            'grant_type' => 'authorization_code'
        ];

        // 拼接请求地址
        $requestUrl = 'https://api.weixin.qq.com/sns/jscode2session?' . http_build_query($params);

        // 发送请求
        $result = json_decode(file_get_contents($requestUrl), true);

        // 请求失败
        if (!isset($result['session_key'])) {
            $this->sendError($result['errcode'], $result['errmsg']);
        }

        return $result['session_key'];
    }

    /**
     * 获取微信小程序的解密数据
     * @return array
     */
    protected function getWechatMiniDecryptedData($sessionKey, $encryptedData, $iv)
    {
        // 解密工具实例
        $pc = new \app\api\utils\WXBizDataCrypt(config('wechat_mini_appid'), $sessionKey);

        // 进行解密
        $code = $pc->decryptData($encryptedData, $iv, $data);

        // 解密失败
        if ($code !== 0) {
            $this->sendError($code, '微信小程序数据解密失败');
        }

        $data = json_decode($data, true);

        return $data;
    }
}
