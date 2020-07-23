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
 * @Date:       2020-03-31 13:48:36
 *
 * @Last Modified By:   Aaron
 * @Last Modified Time: 2020-04-04 19:58:58
 */
 // ------------------------------------------------------------------------

/**
 * 插件配置信息
 */
return [
    ['text', 'ak', 'AccessKey', '登录七牛平台，访问 <a class="text-info" href="https://portal.qiniu.com/user/key" target="_blank">https://portal.qiniu.com/user/key</a> 查看'],
    ['text', 'sk', 'SecretKey', '登录七牛平台，访问 <a class="text-info" href="https://portal.qiniu.com/user/key" target="_blank">https://portal.qiniu.com/user/key</a> 查看'],
    ['text', 'bucket', 'Bucket', '上传的空间名'],
    ['text', 'domain', 'Domain', '空间绑定的域名，以 <code>http://</code> 开头', 'http://']
];