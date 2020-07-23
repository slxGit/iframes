<?php

/**
 * 广告内容表 model
 */
namespace app\adv\model;

use app\common\model\BaseModel;
use think\db\Where;

class ContentModel extends BaseModel
{
	protected $name = 'adv_content';

	// 关联分类模型
	public function position()
	{
		return $this->belongsTo('PositionModel', 'position_id', 'id')->setEagerlyType(0);
	}

	// 时间戳转换
    public function getStarttimeAttr($value)
    {
        return date('Y-m-d H:i', $value);
    }

    public function getEndtimeAttr($value)
    {
        return date('Y-m-d H:i', $value);
    }

    public function setStarttimeAttr($value)
    {
        return $value ? strtotime($value) : '';
    }

    public function setEndtimeAttr($value)
    {
        return $value ? strtotime($value) : '';
    }


    public function is_position_carousel($position_id,$carousel,$startime,$id){
	    $where = new Where();
	    $where['position_id'] = $position_id;
        $where['carousel']    = $carousel;
        // 如果是修改传id 不查询当前id
	    if ($id != 0){
            $where['id'] =  array('neq',$id);
        }

        $res = self::where($where)->whereTime('endtime','>=' , strtotime($startime))
            ->count();
        if ($res != 0){
           return false;
        }
	    return true;
    }

    public static function is_position_exist($content_id)
    {
        $position_id =  self::where('id',$content_id)->field('position_id')->find();
        $res = PositionModel::where(['id' => $position_id['position_id'] , 'is_open' => 1])->count();
        if ($res != 0){
            return true;
        }
        return false;
    }


}