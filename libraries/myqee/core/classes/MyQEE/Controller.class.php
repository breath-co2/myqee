<?php

/**
 * MyQEE 控制器核心类
 *
 * @author     jonwang(jonwang@myqee.com)
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2012 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class MyQEE_Controller
{

    /**
     * 请求参数
     * @var array
     */
    public $arguments;

    /**
     * 控制器
     * @var string
     */
    public $controller;

    /**
     * 方法
     * @var string
     */
    public $action;

    public function __construct()
    {

    }

    /**
     * 用于给系统调用设置对象变量
     * @param array $data
     */
    public function _callback_set_vars($data)
    {
        # 将路由信息传入到控制器变量中
        foreach ( $data as $key => $value )
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
     * Session对象
     * @return Session
     */
    protected static function session()
    {
        return Session::instance();
    }

    /**
     * 执行新控制
     * 若采用非内部调用，则会等同于浏览器的URI执行，请注意防止陷入死循环
     *
     * @param string $uri 新的uri
     * @param boolean $print 是否直接输出
     * @param boolean $use_route 是否在路由中寻找，默认：是
     * @param boolean $is_internal 是否内部调用，默认：是
     * @return string 返回新控制器执行的html
     */
    protected static function execute($uri, $print = true, $use_route = true, $is_internal = true)
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
    protected static function redirect($url, $code = 302)
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

    /**
     * 显示信息
     *
     * @param string $message
     * @param int $code
     */
    public function show_message( $msg , $code=0 , $data = array() )
    {
        $out = array
        (
            'code' => $code,
            'msg'  => (string)$msg,
            'data' => $data,
        );

        if ( IS_SYSTEM_MODE )
        {
            // 系统内部调用模式

            echo $msg;

            exit;
        }
        if ( HttpIO::IS_AJAX )
        {
            @header('Content-Type:application/json');
            echo json_encode($out);

            exit;
        }
        else
        {
            View::factory((Core::$is_admin_url?'admin/':'').'message',$out)->render(true);
        }

        if ( method_exists($this,'after') )
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
    public function show_success( $message = 'success' , $data = array() )
    {
        $this->show_message($message,1,$data);
    }

    public function show_error( $message = 'error' , $data = array() )
    {
        $this->show_message($message,-1,$data);
    }
}