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
 * @Last Modified Time: 2020-03-07 15:45:31
 */
 // ------------------------------------------------------------------------

namespace app\cms\model;

use app\common\model\BaseModel;

class TagModel extends BaseModel
{
	protected $name = 'cms_tag';

	// 关联关系模型
	public function articletags()
    {
        return $this->hasMany('ArticleTagModel', 'tag_id', 'id');
    }
}