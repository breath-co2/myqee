<?php
/**
 * 验证码输出控制器
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   MyQEE
 * @package    Core
 * @subpackage Controller
 * @copyright  Copyright (c) 2008-2016 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Core_Controller_Captcha extends Controller
{
    public $allow_suffix = 'png';

    /**
     * 输出缩略图
     */
    public function action_default($type = '')
    {
        $width  = 80;
        $height = 30;

        if ($type && preg_match('#^([0-9]+)x([0-9]+)$#i', $type, $m))
        {
            if ($m[1]>0)$width  = $m[1];
            if ($m[1]>0)$height = $m[2];
        }

        Captcha::render(array('width'=>$width, 'height'=>$height));
    }
}