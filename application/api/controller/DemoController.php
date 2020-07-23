<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2020-07-13
 * Time: 10:41
 */

namespace app\api\controller;


use app\common\controller\ApiBaseController;
use app\common\controller\BaseController;

class DemoController extends ApiBaseController
{

    protected $whiteList = ['index'];
    public function index(){
        return 11;
    }
}