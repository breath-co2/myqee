<?php
/**
 * 首页控制器
 * @author 呼吸二氧化碳 <jonwang@myqee.com>
 *
 */
class Controller_Check_Config extends Controller
{
    /**
     * 用于检查配置是否成功
     */
    public function action_index()
    {
        echo '{"status":"1"}';
    }
}