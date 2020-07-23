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
 * @Last Modified Time: 2020-03-07 15:38:13
 */
 // ------------------------------------------------------------------------

namespace app\cms\model;

use think\model\Pivot;

class ArticleTagModel extends Pivot
{
	protected $name = 'cms_article_tag';

	// 创建时间字段
    protected $createTime = 'created';

    // 更新时间字段
    protected $updateTime = 'updated';

    // 是否需要自动写入时间戳 如果设置为字符串 则表示时间字段的类型
    protected $autoWriteTimestamp = true;
}