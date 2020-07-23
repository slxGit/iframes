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
 * @Date:       2020-03-31 10:31:04
 *
 * @Last Modified By:   Aaron
 * @Last Modified Time: 2020-04-26 17:50:15
 */
 // ------------------------------------------------------------------------
 
namespace plugins\Qiniu;

use plugins\Plugin;
use Qiniu\Auth;
use Qiniu\Config;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;
use think\Image;
use think\facade\Env;
use app\system\model\AnnexModel;
use app\system\model\AnnexThumbModel;
require Env::get('root_path') . 'plugins/Qiniu/sdk/autoload.php';

/**
 * 七牛云存储插件
 * @package plugins\SystemInfo
 */
class Qiniu extends Plugin
{
	public $info = [
		'title'       => '七牛云存储',
		'author'      => '张津硕',
		'version'     => '1.0.0',
        'description' => "需将 <a href='/system/config/setting/group/upload'>文件存储</a> 设置为七牛云"
	];

	public $hooks = ['upload_annex', 'delete_annex', 'create_thumb'];

    /**
     * 检查配置参数
     */
    private function checkConfig()
    {
        $config = $this->getConfig();

        $errorMsg = '';
        if ($config['ak'] == '') {
            $errorMsg = '请设置七牛云AccessKey';
        } elseif ($config['sk'] == '') {
            $errorMsg = '请设置七牛云SecretKey';
        } elseif ($config['bucket'] == '') {
            $errorMsg = '请设置七牛云Bucket';
        } elseif ($config['domain'] == '') {
            $errorMsg = '请设置七牛云Domain';
        }
        if ($errorMsg != '') {
            return $this->uploadError($params['from'], $errorMsg);
        }
        $config['domain'] = rtrim($config['domain'], '/') . '/';
        return $config;
    }

	/**
     * 上传附件钩子
     */
	public function uploadAnnex($params = [])
	{
        $config = $this->checkConfig();

        // 获取文件信息
        $file = request()->file('file');

        $fileInfo = $file->getInfo();

        // 构建鉴权对象
		$auth = new Auth($config['ak'], $config['sk']);

		// 生成上传 Token
		$token = $auth->uploadToken($config['bucket']);

        // 要上传文件的本地路径
        $filePath = $fileInfo['tmp_name'];

        // 上传到七牛后保存的文件名
        $key = md5(uniqid());

        // 初始化 UploadManager 对象并进行文件的上传。
        $uploadMgr = new UploadManager();

        // 调用 UploadManager 的 putFile 方法进行文件的上传。
        list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);

        if ($err !== null) {
            return $this->uploadError($params['from'], $err);
        } else {
            $imgWidth = $imgHeight = '';

            if ($params['dir'] == 'images') {
                $img = Image::open($filePath);
                $imgWidth  = $img->width();
                $imgHeight = $img->height();
            }

            // 附件信息
            $annexInfo = [
                'manager_id'   => session('manager_id', '', 'admin'),
                'file_key'     => $key,
                'file_path'    => $config['domain'] . $key,
                'file_name'    => $fileInfo['name'],
                'mime'         => $fileInfo['type'],
                'size'         => $fileInfo['size'],
                'md5'          => $file->md5(),
                'sha1'         => $file->sha1(),
                'ext'          => strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION)),
                'width'        => $imgWidth,
                'height'       => $imgHeight,
                'storage'      => 'qiniu',
                'storage_name' => '七牛云'
            ];

            $annex = AnnexModel::create($annexInfo);

            return $this->uploadSuccess($params['from'], $annexInfo['file_path'], $annexInfo['file_name'], $annex['id']);
        }
	}

    /**
     * 创建缩略图
     */
    public function createThumb($params = [])
    {
        $config = $this->checkConfig();

        list($width, $height) = explode('x', $params['thumb']);
        $key = AnnexModel::where('id', $params['id'])->value('file_key');

        $info = [
            'annex_id'  => $params['id'],
            'file_path' => $config['domain'] . $key . '?imageMogr2/thumbnail/' . $width . 'x' . $height,
            'width'     => $width,
            'height'    => $height,
            'storage'   => 'qiniu'
        ];

        return AnnexThumbModel::create($info);
    }

    /**
     * 删除附件钩子
     */
    public function deleteAnnex($params = [])
    {
        $config = $this->checkConfig();
        $annex = AnnexModel::get($params['id']);

        $auth = new Auth($config['ak'], $config['sk']);
        $qiniuConfig = new Config();
        $bucketManager = new BucketManager($auth, $qiniuConfig);
        $bucketManager->delete($config['bucket'], $annex['file_key']);
    }

	public function install()
	{
		$storageOptions = db('sys_config')->where('name', 'upload_storage')->value('options');
		$options = parse_attr($storageOptions);
        if (!isset($options['qiniu'])) {
            $storageOptions .= PHP_EOL . 'qiniu:七牛云';
        }
		db('sys_config')->where('name', 'upload_storage')->update(['options' => $storageOptions]);
		return true;
	}

	public function uninstall()
	{
        $storageOptions = db('sys_config')->where('name', 'upload_storage')->value('options');
        $storageOptions = parse_attr($storageOptions);
		unset($storageOptions['qiniu']);
        $storageOptions = implode_attr($storageOptions);
		db('sys_config')->where('name', 'upload_storage')->update(['options' => $storageOptions, 'value' => 'local']);
		return true;
	}

    /**
     * 上传成功信息
     * @param string $from
     * @param string $filePath
     * @param string $fileName
     * @param string $fileId
     * @return string|\think\response\Json
     */
    private function uploadSuccess($from, $filePath = '', $fileName = '', $fileId = '')
    {   
        switch ($from) {
            case 'ueditor':
                return json([
                    'state' => 'SUCCESS',  
                    'url'   => $filePath,
                    'title' => $fileName,
                ]);
                break;

            case 'layedit':
                return json([
                    'code' => 0,  
                    'data' => [
                        'src'   => $filePath,
                        'title' => $fileName
                    ]
                ]);
                break;

            default:
                return json([
                    'code' => 1,
                    'msg'  => '上传成功',
                    'data' => [
                        'file_id'   => $fileId,
                        'file_name' => $fileName,
                        'file_path' => $filePath
                    ]
                ]);
        }
    }

    /**
     * 上传失败信息
     * @param string $from
     * @param string $errorMsg
     * @return string|\think\response\Json
     */
    private function uploadError($from = '', $msg)
    {
        switch ($from) {
            case 'ueditor':
                return json(['state' => $msg]);
                break;

            case 'layedit':
                return json([
                    'code' => 1,  
                    'msg' => $msg
                ]);
                break;

            default:
                return json([
                    'code' => 0,
                    'msg'  => $msg
                ]);
        }
    }
}