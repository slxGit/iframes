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
 * @Last Modified Time: 2020-04-29 14:51:26
 */
 // ------------------------------------------------------------------------

namespace app\system\controller;

use think\Image;
use app\common\builder\Builder;
use app\system\model\AnnexModel;
use app\system\model\AnnexThumbModel;
use app\common\controller\AdminBaseController;

class AnnexController extends AdminBaseController
{
    /**
     * 附件列表
     */    
    public function index($group = 'images')
    {
        $listTab = [
            'images' => ['title' => '图片', 'url' => url('index', ['group' => 'images'])],
            'files'  => ['title' => '文件', 'url' => url('index', ['group' => 'files'])]
        ];

        $builder = Builder::table()->setTabNav($listTab, $group);

        $map = [];

        $fileName = $this->request->param('file_name', '');

        if ($fileName != '') {
            $map[] = ['file_name', 'like', '%' . $fileName . '%'];
        }

        switch ($group) {
            case 'images':
                $map[] = ['ext', 'in', ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'swf']];
                $builder = $builder->addColumns([
                    ['id', 'ID'],
                    ['file_path', '详情', 'image'],
                    ['file_name', '名称'],
                    ['size', '大小'],
                    ['storage_name', '存储'],
                    ['created', '上传时间']
                ]);
                break;
            
            case 'files':
                $map[] = ['ext', 'notin', ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'swf']];
                $builder = $builder->addColumns([
                    ['id', 'ID'],
                    ['file_name', '名称'],
                    ['size', '大小'],
                    ['storage_name', '存储'],
                    ['created', '上传时间']
                ]);
                break;
        }

        $annexList = AnnexModel::where($map)->order('id', 'desc')->paginate()->each(function($item){
            $item->file_path = $item->id;
        });

        return $builder->addSearchItems([
                ['text', 'file_name', '文件名'],
            ])
            ->addTopButton('delete')
            ->addRightButton('delete')
            ->setTableData($annexList)
            ->fetch();
    }

    /**
     * 文件删除
     */
    public function delete()
    {
        $ids = $this->request->param('ids');
        !is_array($ids) && $ids = array($ids);

        $annexs = AnnexModel::where('id', 'in', $ids)->column('id,file_path,storage');
        
        foreach ($annexs as $annex) {
            if ($annex['storage'] === 'local') {
                $path = $this->request->env('root_path') .  'public' . $annex['file_path'];
                is_file($path) && @unlink($path);
            } else {
                hook('delete_annex', ['id' => $annex['id']], true);
            }
        }
        $annexModel = new AnnexModel;
        $annexModel->deleteFile(array_keys($annexs));

        $this->success('删除成功');
    } 

    /**
     * 上传文件
     * @param string $dir 上传目录
     * @return json
     */
    public function upload($dir = '')
    {
        return $this->saveFile($dir);
    }

    /**
     * Ueditor上传文件
     * @param string $path 上传路径
     * @param string $size 限制大小 单位：KB
     * @param string $ext 限制文件后缀
     * @return json
     */
    public function ueditor()
    {
        $configFile = './static/builder/ueditor/php/config.json';
        $config = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents($configFile)), true);

        $action = $_GET['action'];
        
        switch ($action) {
            /* 获取配置信息 */
            case 'config':
                $result =  $config;
                break;

            /* 上传图片 */
            case 'uploadimage':
                return $this->saveFile('images', 'ueditor');
                break;

            /* 上传涂鸦 */
            case 'uploadscrawl':
                return $this->saveScrawl();
                break;

            /* 上传视频 */
            case 'uploadvideo':
                return $this->saveFile('videos', 'ueditor');
                break;

            /* 上传附件 */
            case 'uploadfile':
                return $this->saveFile('files', 'ueditor');
                break;

            /* 列出图片 */
            case 'listimage':
                return $this->actionList('listimage', $config);
                break;

            /* 列出附件 */
            case 'listfile':
                return $this->actionList('listfile', $config);
                break;

            default:
                $result = ['state' => '请求地址出错'];
                break;
        }

        /* 输出结果 */
        if (isset($_GET["callback"])) {
            if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
                return htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
            } else {
                return json(['state' => 'callback参数不合法']);
            }
        } else {
            return json($result);
        }
    }

    /**
     * layedit上传文件
     * @return json
     */
    public function layedit()
    {
        return $this->saveFile('images', 'layedit');
    }

    /**
     * 保存文件
     * @param string $dir 保存目录
     * @param string $from 来源
     * @return string|\think\response\Json
     */
    private function saveFile($dir = '', $from = '')
    {   
        // 文件大小限制
        $sizeLimit = $dir == 'images' ? config('upload_image_size') : config('upload_file_size');
        $sizeLimit = $sizeLimit * 1024;

        // 文件类型限制
        $extLimit = $dir == 'images' ? config('upload_image_ext') : config('upload_file_ext');
        if ($extLimit != '') $extLimit = explode(',', $extLimit);

        // 缩略图参数
        $thumb = $this->request->post('thumb', '');
 
        // 获取文件信息
        $file = $this->request->file('file');
        if ($file) {

            // 判断是否超出文件大小限制
            if ($sizeLimit > 0 && ($file->getInfo('size') > $sizeLimit)) {
                return $this->uploadError($from, '文件不能超过' . $sizeLimit/1024 . 'KB');
            }

            // 判断文件类型是否符合
            if (!empty($extLimit) && (!in_array(pathinfo($file->getInfo('name'), PATHINFO_EXTENSION), $extLimit))) {
                return $this->uploadError($from, '文件格式不支持');
            }

            // 附件上传钩子，用于第三方文件上传扩展
            if (config('upload_storage') != 'local') {
                $hookResult = hook('upload_annex', ['dir' => $dir, 'from' => $from], true);
                if ($hookResult !== false) {
                    return $hookResult;
                }
            }

            // 上传
            $info = $file->move(config('upload_path') . $dir);

            if ($info !== false) {

                $imgWidth = $imgHeight = '';

                if ($dir == 'images') {
                    $img = Image::open($info);
                    $imgWidth  = $img->width();
                    $imgHeight = $img->height();
                }

                // 文件路径
                $filePath = '/uploads/' . $dir . '/' . str_replace('\\', '/', $info->getSaveName());

                // 附件信息
                $annexInfo = [
                    'manager_id'   => $this->managerId,
                    'file_key'     => md5(uniqid()),
                    'file_path'    => $filePath,
                    'file_name'    => $file->getInfo('name'),
                    'mime'         => $file->getInfo('type'),
                    'size'         => $file->getInfo('size'),
                    'md5'          => $info->hash('md5'),
                    'sha1'         => $info->hash('sha1'),
                    'ext'          => strtolower($info->getExtension()),
                    'width'        => $imgWidth,
                    'height'       => $imgHeight,
                    'storage'      => 'local',
                    'storage_name' => '本地'
                ];

                $annex = AnnexModel::create($annexInfo);

                // 生成缩略图
                if ($thumb != '') {
                    // $thumb = $this->saveThumb($info, $info->getPathInfo()->getfileName(), $info->getFilename(), $thumbSize, $thumbType);
                    $this->saveThumb($annex, $thumb);
                }

                return $this->uploadSuccess($from, get_file_path($annex['id']), $annex['file_name'], $annex['id']);
            } else {
                return $this->uploadError($from, $file->getError());
            }
        } else {
            return $this->uploadError($from, '上传出错');
        }
    }

    /**
     * 保存缩略图
     * @param array $annex 文件信息
     * @param $thumb 缩略图参数

     */
    private function saveThumb($annex, $thumb)
    {
        if ($annex['storage'] === 'local') {
            foreach (explode(',', $thumb) as $v) {
                list($width, $height) = explode('x', $v);
                $phyPath = \think\facade\Env::get('root_path') . 'public' . $annex['file_path'];
                $info = [
                    'annex_id'  => $annex['id'],
                    'file_path' => $this->createThumb($phyPath, $v),
                    'width'     => $width,
                    'height'    => $height,
                    'storage'   => 'local'
                ];
                $thumbModel = new AnnexThumbModel;
                $thumbModel->isUpdate(false)->save($info);
            }
        } else {
            hook('create_thumb', ['id' => $annex->id, 'thumb' => $thumb], true);
        }   
    }

    /**
     * 创建缩略图
     * @param string $phyPath 文件物理路径
     * @param string $size 尺寸
     */
    private function createThumb($phyPath = '', $size = '')
    {
        list($width, $height) = explode('x', $size);

        $fileExt = pathinfo($phyPath, PATHINFO_EXTENSION);

        $fileDir = pathinfo($phyPath, PATHINFO_DIRNAME);

        $fileName = md5(microtime(true)) . '.' . $fileExt;

        \think\Image::open($phyPath)->thumb($width, $height)->save($fileDir . DIRECTORY_SEPARATOR . $fileName);

        $dir = substr($fileDir, strripos($fileDir, '/'));

        $filePath = '/uploads/images' . $dir . '/' . $fileName;
        
        return $filePath;
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
    private function uploadError($from, $msg)
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

    /**
     * 保存涂鸦(用于ueditor)
     * @return \think\response\Json
     */
    private function saveScrawl()
    {
        $file = $this->request->post('file');
        $fileContent = base64_decode($file);
        $fileName = md5($file) . '.png';
        $dir = config('upload_path') . 'images/' . date('Ymd', $this->request->time());

        // 物理路径
        $filePath = $dir . '/' . $fileName;

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (file_put_contents($filePath, $fileContent) === false) {
            return json(['state' => '涂鸦上传出错']);
        } else {
            $image = Image::open($filePath);

            // 附件信息
            $annexInfo = [
                'manager_id' => $this->managerId,
                'file_key'   => md5(uniqid()),
                'file_path'  => '/uploads/images/' . date('Ymd', $this->request->time()) . '/' . $fileName,
                'file_name'  => $fileName,
                'size'       => filesize($filePath),
                'ext'        => strtolower($image->type()),
                'mime'       => $image->mime(),
                'width'      => $image->width(),
                'height'     => $image->height(),
                'md5'        => md5_file($filePath),
                'sha1'       => sha1_file($filePath)
            ];
          
            AnnexModel::create($annexInfo);

            return json([
                "state" => "SUCCESS",         
                "url"   => $annexInfo['file_path'], 
                "title" => $fileName, 
            ]);
        }
    }

    /**
     * 显示文件列表(用于ueditor)
     * @param string $type 类型
     * @param $config
     * @return \think\response\Json
     */
    private function actionList($type, $config)
    {
        /* 判断类型 */
        switch ($type) {
            /* 列出附件 */
            case 'listfile':
                $allowFiles = $config['fileManagerAllowFiles'];
                $listSize = $config['fileManagerListSize'];
                $path = '/uploads/files/';
                break;
            /* 列出图片 */
            case 'listimage':
            default:
                $allowFiles = $config['imageManagerAllowFiles'];
                $listSize = $config['imageManagerListSize'];
                $path = 'uploads/images/';
        }

        $allowFiles = substr(str_replace(".", "|", join("", $allowFiles)), 1);

        /* 获取参数 */
        $size = isset($_GET['size']) ? htmlspecialchars($_GET['size']) : $listSize;
        $start = isset($_GET['start']) ? htmlspecialchars($_GET['start']) : 0;
        $end = $start + $size;

        /* 获取文件列表 */
        $path = $_SERVER['DOCUMENT_ROOT'] . (substr($path, 0, 1) == "/" ? "":"/") . $path;
        $files = $this->getfiles($path, $allowFiles);
        if (!count($files)) {
            return json_encode(array(
                "state" => "no match file",
                "list" => array(),
                "start" => $start,
                "total" => count($files)
            ));
        }

        /* 获取指定范围的列表 */
        $len = count($files);
        for ($i = min($end, $len) - 1, $list = array(); $i < $len && $i >= 0 && $i >= $start; $i--){
            $list[] = $files[$i];
        }

        /* 返回数据 */
        $result = json_encode(array(
            "state" => "SUCCESS",
            "list" => $list,
            "start" => $start,
            "total" => count($files)
        ));

        return $result;
    }

    /**
     * 遍历获取目录下的指定类型的文件(用于ueditor)
     * @param $path
     * @param array $files
     * @return array
     */
    private function getfiles($path, $allowFiles, &$files = array())
    {
        if (!is_dir($path)) return null;
        if(substr($path, strlen($path) - 1) != '/') $path .= '/';
        $handle = opendir($path);
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..') {
                $path2 = $path . $file;
                if (is_dir($path2)) {
                    $this->getfiles($path2, $allowFiles, $files);
                } else {
                    if (preg_match("/\.(".$allowFiles.")$/i", $file)) {
                        $files[] = array(
                            'url'=> substr($path2, strlen($_SERVER['DOCUMENT_ROOT'])),
                            'mtime'=> filemtime($path2)
                        );
                    }
                }
            }
        }
        return $files;
    }
}