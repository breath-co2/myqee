<?php

/**
 * MyQEE Session 缓存驱动器
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Module
 * @package    Session
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Module_Session_Driver_Default
{

    public function __construct()
    {
        static $run = null;
        if (null===$run)
        {
            $run = true;

            if (function_exists('ini_set'))
            {
                @ini_set('session.gc_probability', (int)Session::$config['gc_probability']);
                @ini_set('session.gc_divisor'    , Session::$config['gc_divisor']?Session::$config['gc_divisor']:100);
                @ini_set('session.gc_maxlifetime', (Session::$config['expiration']==0)?2592000:Session::$config['expiration']);

                // session保存接口
                if (isset(Session::$config['save_handler']) && Session::$config['save_handler'])
                {
                    @ini_set('session.save_handler', Session::$config['save_handler']);
                }
            }

            // session 保存目录
            if (isset(Session::$config['save_path']) && Session::$config['save_path'])
            {
                session_save_path(Session::$config['save_path']);
            }
        }

        $this->create();
    }

    /**
     * 创建Session
     *
     * @return  void
     */
    public function create()
    {
        session_name(Session::$config['name']);

        $this->destroy();

        $cookie_config = Core::config('cookie');

        # 这里对IP+非80端口的需要特殊处理下，经试验，当这种情况下，设置session id的cookie的话会失败
        if (preg_match('#^([0-9]+.[0-9]+.[0-9]+.[0-9]+):[0-9]+$#', $cookie_config['domain'], $m))
        {
            # IP:PORT 方式
            $cookie_config['domain'] = $m[1];
        }

        $s_name = session_name();

        switch(Session::$config['type'])
        {
            case 'auto':
                $sid = HttpIO::REQUEST($s_name);
                break;
            case 'url':
                $sid = HttpIO::GET($s_name);
                break;
            default;
                $sid = HttpIO::COOKIE($s_name);
                break;
        }

        if (!$sid || !Session::check_session_id($sid))
        {
            # 如果检验的Session ID不合法，则重新生成一个
            session_id(Session::create_session_id());
        }
        else
        {
            session_id($sid);
        }

        # Session ID 通过uri传递
        if (Session::$config['type']=='url')
        {
            @ini_set('session.use_only_cookies', 0);
        }
        else
        {
            session_set_cookie_params(Session::$config['httponly']?0:(int)Session::$config['expiration'], $cookie_config['path'], $cookie_config['domain'], $cookie_config['secure'], Session::$config['httponly']);
        }

        session_start();
    }

    /**
     * 获取SESSION ID
     */
    public function session_id()
    {
        return session_id();
    }

    /**
     * 回收当前Session
     *
     * @return  void
     */
    public function destroy()
    {
        if (session_id()!=='')
        {
            $name = session_name();

            session_destroy();

            $_SESSION = array();

            Core::cookie()->delete($name, '/');
        }
    }

    /**
     * 保存Session数据
     *
     * @return  void
     */
    public function write_close()
    {
        session_write_close();
    }
}