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
 * @Date:       2020-02-18 19:27:20
 *
 * @Last Modified By:   Aaron
 * @Last Modified Time: 2020-03-24 16:10:00
 */
 // ------------------------------------------------------------------------

namespace app\common\validate;

use think\Validate;
use think\exception\ValidateException;

class ApiBaseValidate extends Validate
{
	/**
     * 数据自动验证
     * @access public
     * @param  array   $data  数据
     * @param  string  $scene 验证场景
     * @return void
     */
    public function goCheck($data = '', $scene = '')
    {   
        if ($data === '') {
            $data = request()->param();
        }
        
        if ($scene != '') {
            $this->scene($scene);
        }
        
        if (!$this->check($data)) {
            throw new ValidateException($this->getError());
        }
    }

}