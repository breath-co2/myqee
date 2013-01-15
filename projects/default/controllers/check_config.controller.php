<?php
/**
 * 首页控制器
 * @author jonwang
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