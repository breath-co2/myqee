<?php

class Controller_Demo extends Controller_Admin
{
    public function action_default()
    {
        $view = new View('admin/demo');
        $view->render();
    }

    public function action_test($menu=0)
    {
        echo '测试菜单';
        echo '<br>参数='.$menu;
    }

    public function action_test3()
    {
        echo '测试菜单3';
    }

    public function action_use()
    {
        $view = new View('admin/demo_use');
        $view->render();
    }
}