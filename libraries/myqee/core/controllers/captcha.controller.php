<?php

class Controller_Captcha extends Controller
{
    /**
     * 输出缩略图
     */
    public function action_default($type='')
    {
        $width = 80;
        $height = 30;
        if ($type && preg_match('#^([0-9]+)x([0-9]+)\.png$#i', $type,$m))
        {
            if ($m[1]>0)$width = $m[1];
            if ($m[1]>0)$height = $m[2];
        }
        Captcha::render(array('width'=>$width,'height'=>$height));
    }
}