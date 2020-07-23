<?php

/**
 * 广告位置表 model
 */

namespace app\adv\model;

use app\common\model\BaseModel;

class PositionModel extends BaseModel
{
	protected $name = 'adv_position';

    public function getUpdatedAttr($value)
    {
    	return date('Y-m-d H:i', $value);
    }

    public static function categoryList(){
        $categoryList = self::field('id,name')->where('is_open',1)->order('id', 'desc')->select()->toArray();
        $categoryList = array_column($categoryList, 'name','id');
        return $categoryList;
    }

    public static function advPositionList($id,$adv_platform){
        $res = self::alias('a')
            ->field('a.id,name,adv_width,adv_height,c.file_path,title,link,d.file_path as video_path,type,carousel,content_url,starttime,endtime')
            ->leftJoin('adv_content b','a.id = b.position_id')
            ->leftJoin('sys_annex c','b.content_url = c.id')
            ->leftJoin('sys_annex d','b.conent_video = d.id')
            ->where(['a.id' => $id,'a.adv_platform' => $adv_platform,'is_open' => 1,'is_enabled' => 1])
            ->whereTime('starttime','<',time())
            ->whereTime('endtime ','>',time())
            ->select()->each(function($item){
                if ($item->type == 1) $type = '图片';
                if ($item->type == 2) $type = '轮播';
                if ($item->type == 3) $type = '视频';
                if (isset($type)) $item->type = $type;
            });
        return $res;
    }
}