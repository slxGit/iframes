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
 * @Date:       2020-02-11 19:14:11
 *
 * @Last Modified By:   Aaron
 * @Last Modified Time: 2020-03-07 18:46:05
 */
 // ------------------------------------------------------------------------

namespace app\cms\model;

use app\common\model\BaseModel;

class ArticleModel extends BaseModel
{
	protected $name = 'cms_article';

	// 关联分类模型
	public function category()
	{
		return $this->belongsTo('CategoryModel', 'category_id', 'id');
	}

	// 关联标签模型
	public function tags()
    {
        return $this->belongsToMany('TagModel', '\\app\\cms\\model\\ArticleTagModel', 'tag_id', 'article_id');
    }

    public function getUpdatedAttr($value)
    {
    	return date('Y-m-d H:i', $value);
    }

    public function getPublishedTimeAttr($value)
    {
    	return $value ? date('Y-m-d H:i', $value) : '';
    }
}