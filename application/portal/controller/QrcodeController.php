<?php
namespace app\portal\controller;

use Endroid\QrCode\QrCode;
use app\common\controller\BaseController;

class QrcodeController extends BaseController
{   
	/**
     * 二维码资源
     * @return mixed
     */
    public function resource($data = '', $size = 300, $margin = 10, $encoding = 'UTF-8')
    {   
        $qrCode = new QrCode($data);

        // 设置二维码大小 px
        $qrCode->setSize($size);

        // 设置二维码内容相对于整张图片的外边距
        $qrCode->setMargin($margin);

        // 设置编码类型
        $qrCode->setEncoding($encoding);

        return response($qrCode->writeString(), 200)->contentType($qrCode->getContentType());
    }
}
