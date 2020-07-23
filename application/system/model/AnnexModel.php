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
 * @Date:       2020-02-10 19:41:57
 *
 * @Last Modified By:   Aaron
 * @Last Modified Time: 2020-04-26 17:29:29
 */
 // ------------------------------------------------------------------------

namespace app\system\model;

use app\common\model\BaseModel;
use app\system\model\AnnexThumbModel;

class AnnexModel extends BaseModel
{
	protected $name = 'sys_annex';

    /**
     * 根据附件ID获取KEY
     * @param string|array $ids 附件ID
     * @return string key
     */
    public function getFileKey($ids = '')
    {
        if (is_string($ids) && strpos($ids, ',')) {
            $ids = explode(',', $ids);
        }
        if (is_array($ids)) {
            $keys = [];
            $list = $this->where('id', 'in', $ids)->field('id,file_key')->select();
            foreach ($list as $v) {
                $keys[$v['id']] = $v['file_key'];
            }
            return $keys;
        } else {
            return $this->where('id', $ids)->value('file_key');
        }
    }

    /**
     * 根据附件ID获取名称
     * @param int $id 附件ID
     * @return string 名称
     */
    public function getFileName($id = 0)
    {
        return $this->where('id', $id)->value('file_name');
    }

    /**
     * 根据附件ID获取路径
     * @param int $id 附件ID
     * @param int $type 类型：1-补全域名，0-直接返回数据库记录的地址
     */
    public function getFilePath($id = 0, $type = 0)
    {
        $info = $this->where('id', $id)->field('file_path,storage')->find();
        if ($info) {
            if ($info['storage'] == 'local') {
                return ($type == 1 ? get_host_domain() : '') . $info['file_path'];
            } else {
                return $info['fle_path'];
            }
        } else {
            return null;
        }
    }

    /**
     * 根据附件ID获取缩略图路径
     * @param int $id 附件ID
     * @param string $size 缩略国尺寸,如：300x300
     * @param int $type 类型：1-补全域名，0-直接返回数据库记录的地址
     */
    public function getThumbPath($id = 0, $size = '', $type = 0)
    {
        list($width, $height) = explode('x', $size);
        $thumb = AnnexThumbModel::where('annex_id', $id)->where('width', $width)->where('height', $height)->field('file_path,storage')->find();
        if ($thumb) {
            if ($thumb['storage'] == 'local') {
                return ($type == 1 ? get_host_domain() : '') . $thumb['file_path'];
            } else {
                return $thumb['file_path'];
            }
        } else {
            return null;
        }
    }

    /**
     * 根据id删除附件
     * @param string|array $ids 附件id
     */
    public function deleteFile($ids = '')
    {
        $result = self::destroy($ids);
        if ($result) {
            $thumbs = AnnexThumbModel::where('annex_id', 'in', $ids)->field('id,file_path,storage')->select();
            foreach ($thumbs as $thumb) {
                AnnexThumbModel::destroy($thumb['id']);
                if ($thumb['storage'] == 'local') {
                    $filePath = request()->env('root_path') .  'public' . $thumb['file_path'];
                    is_file($filePath) && @unlink($filePath);
                }
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * 转换字节格式
     * @param   int  $byte 字节数
     * @return  string
     */
    public function getSizeAttr($byte)
    {
        $KB = 1024;       
        $MB = 1024 * $KB;
        $GB = 1024 * $MB;
        $TB = 1024 * $GB;

        if ($byte < $KB) {
            return $byte . 'B';
        } elseif ($byte < $MB) {
            return round($byte / $KB, 2) . ' KB';
        } elseif ($byte < $GB) {
            return round($byte / $MB, 2) . ' MB';
        } elseif ($byte < $TB) {
            return round($byte / $GB, 2) . ' GB';
        } else {
            return round($byte / $TB, 2) . ' TB';
        }
    }

    /**
     * 创建时间格式
     * @return  string
     */
    public function getCreatedAttr($value)
    {
        return date('Y-m-d H:i', $value);
    }
}