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
//         echo __('Page Not Found');

//         Core::import_library('com.myqee.administration');

        /*

        $this->prefix = 'default';
        $this->prefix = 'myqee';

        $this->account = 'j0wj5l01xm';
        $this->key = 'h4m24m31z5l0lw5y2mi01kk4z1wlh1yj34h205m0';
        $this->host = 'auth.sinas3.com';
        $this->port = 443;
        $this->protocol = 'https';
         */
//         $d = new Storage(array('driver'=>'Swift', 'driver_config'=>'https://j0wj5l01xm:h4m24m31z5l0lw5y2mi01kk4z1wlh1yj34h205m0@auth.sinas3.com/myqee'));
//        $dd = $d->set('aaaaaaaaa1',array('a'=>1123,'ddd'=>3344));
//         $dd = $d->get('aaaaaaaaa1');
//         var_dump($dd);
//         exit;
//         echo '<pre>';
//         $config =  array
//         (
//             'type' => Database::TYPE_Mongo,
//             'connection' => array
//             (
//                 'hostname'   => '127.0.0.1',
//             	'database'   => 'test',
//             	'persistent' => false,
//             ),
//             'table_prefix' => '',
//             'charset'      => 'utf8',
//             'caching'      => false,
//             'profiling'    => true,
//         );

// //         $d = Database::instance($config)->from('admin_member')->group_by('id')->get()->as_array();
//         $d = Database::instance($config)->from('admin_member')->select_max('id as id1')->select_min('id as id2')->select_avg('id as id3')->select('title')->get()->as_array();
//         print_r($d);
//         exit;
        $view = new View('welcome');
        $view->render();
    }

    public function action_helloworld()
    {
        echo 'Hello World.<br>Time:'.(microtime(1)-START_TIME);
    }
}