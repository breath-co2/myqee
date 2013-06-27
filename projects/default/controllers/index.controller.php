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

        if (isset($_GET['show']) && $_GET['show']=='time')
        {
            register_shutdown_function('Controller_Index::runtime');
        }
    }

    public static function runtime()
    {

        echo '<br><pre>';
        echo microtime(1)-START_TIME;

        echo '<br>get_included_files => ';
        print_r(get_included_files());
    }

    public function action_helloworld()
    {
        echo 'Hello World.<br>Time:'.(microtime(1)-START_TIME);
    }
}