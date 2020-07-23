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
 * @Last Modified Time: 2020-03-23 18:41:21
 */
 // ------------------------------------------------------------------------

namespace app\api\controller;

use think\facade\Request;
use app\api\controller\Send;
use app\api\exception\AuthenticateException;

class Oauth
{
	use Send;

    // accessToken过期时间
    public static $expires = 60*60*24*7;

    // accessToken前缀
    public static $accessTokenPrefix = 'accessToken_';

    /**
     * 认证授权
     * @param Request $request
     * @return mixed
     */
    public static function authenticate()
    {      
        return self::certification(self::getAccessToken());
    }

    /**
     * 从头部中获取accessToken
     * @param Request $request
     * @return string
     */
    public static function getAccessToken()
    {   
        //获取头部信息
        $authorization = Request::header('Authorization');

        //获取请求中的Authorization字段,值形式为Bearer AccessToken
        if (!$authorization) {
            self::sendError(401, '请求header未携带Authorization信息');
        }

        try {
        	list($type, $accessToken) = explode(' ', $authorization);
        } catch (\Exception $e) {
        	self::sendError(401, 'Authorization信息不正确');
    	}

        if ($type !== 'Bearer') {
            self::sendError(401, '接口认证方式需为Bearer');
        }

        if (!$accessToken) {
            self::sendError(401, '尝试获取的Authorization信息不存在');
        }

        return $accessToken;        
    }

    /**
     * 验证权限
     * @param string $accessToken
     * @return mixed
     */
    protected static function certification($accessToken)
    {
        $clientInfo = cache(self::$accessTokenPrefix . $accessToken);

        if (!$clientInfo) {
            self::sendError(401, 'access_token不正确或已过期');
        }

        return $clientInfo;
    }

    /**
     * 检测当前方法是否需要认证授权
     * @param array|string $whiteList 白名单
     * @return boolean
     */
    public static function needAuth($whiteList)
    {   
        $whiteList = is_array($whiteList) ? $whiteList : explode(',', $whiteList);

        // 白名单为空
        if (empty($whiteList)) {
            return true;
        }

        $whiteList = array_map('trim', array_map('strtolower', $whiteList));

        // 当前方法是否存在于白名单中
        if (in_array(Request::action(), $whiteList) || in_array('*', $whiteList)) {
            return false;
        }

        return true;
    }

    /**
     * 数据签名
     */
    public static function sign($data = [], $appsecret = '')
    {   
        // 版本号不参与签名(restful模式)
        unset($data['version']);

        // sign参数不参与签名
        unset($data['sign']);

        // 参数的值为空不参与签名
        $data = array_filter($data, function($var) {
            return !($var === '');
        });

        // 参数名ASCII码从小到大排序
        ksort($data);

        // 在最后拼接上应用密钥
        $data['key'] = $appsecret;

        // 使用URL键值对的格式进行MD5运算,再将得到的字符串转换为大写
        return strtoupper(md5(urldecode(http_build_query($data))));
    }
}