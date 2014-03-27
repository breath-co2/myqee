<?php

/**
 * 控制器核心类
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Core_Controller
{

    /**
     * 页面请求的参数
     *
     * @var array
     */
    public $arguments;

    /**
     * 当前访问的控制器
     *
     * @var string
     */
    public $controller;

    /**
     * 当前调用的方法
     *
     * @var string
     */
    public $action;

    /**
     * 当前请求的URI
     *
     * @var string
     */
    public $uri;

    /**
     * 当前请求的目录部分
     *
     * @var string
     */
    public $directory;

    /**
     * 当前请求的页面的后缀
     *
     * @var string
     */
    public $suffix;

    /**
     * 当前控制器信息ID
     *
     * 例如访问地址为 http://localhost/123/test/ 控制器为_id.controller.php ,方法为test，则$this->ids=array(123)，系统会在初始化控制器时进行设置
     *
     * @var array
     */
    public $ids = array();

    /**
     * 当前控制器允许的后缀名
     *
     * 例如 `$allow_suffix = css|js` 表示允许css和js两个后缀的请求，也可单独为action设置，比如
     *
     * ```
     * $allow_suffix = array
     * (
     *     'default' => 'js',
     *     'test'    => 'css',
     * );
     * ```
     * 表示 `action_default` 允许js后缀的请求，`action_test` 允许css后缀的请求
     *
     * @var string
     */
    public $allow_suffix = '';

    protected static $message_view = 'show_message';

    /**
     * 控制器实例化化对象
     *
     * @var array
     */
    public static $controllers = array();

    public function __construct()
    {
        $this->ids = 1;
    }

    /**
     * 用于给系统调用设置对象变量
     *
     * @param array $data
     */
    public function _callback_set_vars($data)
    {
        # 将路由信息传入到控制器变量中
        foreach ($data as $key => $value)
        {
            $this->$key = $value;
        }
    }

    /**
     * 用于给系统调用获取控制器变量
     */
    public function _callback_get_vars()
    {
        return get_object_vars($this);
    }

    /**
     * 返回Session对象
     *
     * @return Session
     */
    public function session()
    {
        return Session::instance();
    }

    /**
     * 显示信息
     *
     * @param string $message
     * @param int $code
     */
    public function show_message($msg, $code=0, $data = array())
    {
        $out = array
        (
            'code' => $code,
            'msg'  => (string)$msg,
            'data' => $data,
        );

        if (IS_SYSTEM_MODE)
        {
            // 系统内部调用模式

            echo $msg;

            exit;
        }

        if (HttpIO::IS_AJAX)
        {
            @header('Content-Type:application/json');

            if (defined('JSON_UNESCAPED_UNICODE'))
            {
                echo json_encode($out, JSON_UNESCAPED_UNICODE);
            }
            else
            {
                echo json_encode($out);
            }

            exit;
        }
        else
        {
            View::factory((IS_ADMIN_MODE?'admin/':'') .'message', $out)->render(true);
        }

        if (method_exists($this, 'after'))
        {
            $this->after();
        }

        exit;
    }

    /**
     * 输出成功信息
     *
     * @param string $message
     */
    public function show_success($message = 'success', $data = array())
    {
        $this->show_message($message, 1, $data);
    }

    public function show_error($message = 'error', $data = array())
    {
        $this->show_message($message, -1, $data);
    }


    /**
     * 执行新控制
     *
     * 若采用非内部调用，则会等同于浏览器的URI执行，请注意防止陷入死循环
     *
     * @param string $uri 新的uri
     * @param boolean $print 是否直接输出
     * @param boolean $use_route 是否在路由中寻找，默认：是
     * @param boolean $is_internal 是否内部调用，默认：是
     * @return string 返回新控制器执行的html
     */
    public static function execute($uri, $print = true, $use_route = true, $is_internal = true)
    {
        return HttpIO::execute($uri, $print, $use_route, $is_internal);
    }

    /**
     * 页面跳转
     *
     * @param   string   redirect location
     * @param   integer  status code: 301, 302, etc
     * @return  void
     * @uses    Core_url::site
     * @uses    HttpIO::send_headers
     */
    public static function redirect($url, $code = 302)
    {
        HttpIO::redirect($url, $code);
    }

    /**
     * 页面输出header缓存
     *
     * 0表示不缓存
     *
     * @param int $time 缓存时间，单位秒
     */
    public static function header_cache($time = 86400)
    {
        HttpIO::set_cache_header($time);
    }

}