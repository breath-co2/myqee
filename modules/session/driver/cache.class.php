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
class Module_Session_Driver_Cache
{

    /**
     * 存储SESSION ID
     * @var string
     */
    protected static $Session_ID;

    protected static $OLD_SESSION_MD5;

    protected $cache_config = 'default';

    protected $session_name;

    /**
     * Session前缀
     *
     * @var string
     */
    protected $prefix = 'session_';

    public function __construct($cache_config = null)
    {
        if ($cache_config)
        {
            $this->cache_config = $cache_config;
            if (is_array($this->cache_config) && isset($this->cache_config['prefix']))
            {
                $this->prefix = $this->cache_config['prefix'];
            }
        }

        $this->session_name = Session::session_name();

        # 设置前缀
        if ($this->driver()->get_prefix()=='')
        {
            $this->driver()->set_prefix($this->prefix);
        }

        $this->create();
    }

    /**
     * 获取当前驱动
     *
     * @return Cache
     */
    public function driver()
    {
        return Cache::instance($this->cache_config);
    }

    /**
     * Create a new session.
     *
     * @return  void
     */
    public function create()
    {
        $cookie_config = Core::config('cookie');

        $_SESSION = array();

        switch(Session::$config['type'])
        {
            case 'auto':
                $sid = HttpIO::REQUEST($this->session_name);
                break;
            case 'url':
                $sid = HttpIO::GET($this->session_name);
                break;
            default;
                $sid = HttpIO::COOKIE($this->session_name);
                break;
        }

        if (!$sid || !Session::check_session_id($sid))
        {
            $sid = Session::create_session_id();

            if (Session::$config['type']!='url')
            {
                # 将session存入cookie
                Core::cookie()->set($this->session_name, $sid, !Session::$config['httponly'] && Session::$config['expiration']>0?Session::$config['expiration']:null, $cookie_config['path'], $cookie_config['domain'], $cookie_config['secure'], Session::$config['httponly']);
            }
        }

        # 添加URL处理自动追加SESSION ID参数
        if (Session::$config['type']=='url')
        {
            Core::add_url_args(Session::$config['name'], $sid);
        }

        # 调试模式设置Session模式，避免开启缓存模式时获取不到Session
        if (IS_DEBUG)$this->driver()->session_mode(true);

        $_SESSION = $this->driver()->get($sid);

        if (IS_DEBUG)$this->driver()->session_mode(false);

        if (!is_array($_SESSION))
        {
            $_SESSION = array();
        }

        # 将获取的值序列化MD5值
        Session_Driver_Cache::$OLD_SESSION_MD5 = md5(serialize($_SESSION));

        # 当前session id
        Session_Driver_Cache::$Session_ID = $sid;
    }

    /**
     * 获取session_id
     */
    public function session_id()
    {
        return Session_Driver_Cache::$Session_ID;
    }

    /**
     * Destroys the current session.
     *
     * @return  void
     */
    public function destroy()
    {
        $sid = Core::cookie()->get($this->session_name);
        if ($sid)
        {
            $this->driver()->delete($sid);
        }

        $_SESSION = array();

        Core::cookie()->delete($this->session_name, '/');
    }

    /**
     * 保存Session数据
     *
     * @return  void
     */
    public function write_close()
    {
        if (md5(serialize($_SESSION)) != Session_Driver_Cache::$OLD_SESSION_MD5)
        {
            if (!$_SESSION || $_SESSION===array())
            {
                # 清除session数据
                $this->driver()->delete($this->session_id());
            }
            else
            {
                # 如果确实修改则保存
                $this->driver()->set($this->session_id(), $_SESSION, Session::$config['expiration']>0?Session::$config['expiration']:2592000);
            }
        }
    }
}