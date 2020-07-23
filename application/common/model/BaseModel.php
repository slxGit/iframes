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
 * @Date:       2020-02-10 19:42:47
 *
 * @Last Modified By:   Aaron
 * @Last Modified Time: 2020-02-12 22:29:45
 */
 // ------------------------------------------------------------------------

namespace app\common\model;

use think\Model;

class BaseModel extends Model
{
	// 创建时间字段
    protected $createTime = 'created';

    // 更新时间字段
    protected $updateTime = 'updated';

    // 是否需要自动写入时间戳 如果设置为字符串 则表示时间字段的类型
    protected $autoWriteTimestamp = true;
}