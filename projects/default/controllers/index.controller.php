<?php

/**
 * 首页控制器
 * @author jonwang
 *
 */
class Controller_Index extends Controller
{
    /**
     * 欢迎页
     */
    public function action_default()
    {
        $view = new View('welcome');
        $view->render();
    }

    /**
     * 用于检查配置是否成功
     */
    public function action_check_config()
    {
        echo '{"status":"1"}';
    }

    public function action_helloworld()
    {
        echo 'Hello World.<br>Time:'.(microtime(1)-START_TIME);
    }
}