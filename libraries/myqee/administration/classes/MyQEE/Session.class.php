<?php

/**
 * MyQEE Session 类
 *
 * @author     jonwang(jonwang@myqee.com)
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2012 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class MyQEE_Session
{

    /**
     * @var Session
     */
    protected static $instance;

    // Protected key names (cannot be set by the user)
    protected static $protect = array('SID' => 1, '_flash_session_' => 1);

    public static $config;

    protected static $flash;

    /**
     * Session驱动
     * @var Session_Driver_Default
     */
    protected $driver;

    /**
     * @var ORM_Admin_Member_Data
     */
    protected static $member;

    /**
     * @return Session
     */
    public static function instance()
    {
        if ( Session::$instance == null )
        {
            // Create a new instance
            new Session();
        }
        return Session::$instance;
    }

    /**
     * On first session instance creation, sets up the driver and creates session.
     */
    public function __construct($vars = null)
    {
        // This part only needs to be run once
        if ( Session::$instance === null )
        {
            // Load config
            Session::$config = Core::config('session');

            if ( ! isset(Session::$config['name']) || ! preg_match('#^(?=.*[a-z])[a-z0-9_]++$#iD', Session::$config['name']) )
            {
                // Name the session, this will also be the name of the cookie
                Session::$config['name'] = 'PHPSESSINID';
            }

            if ( isset(Session::$config['driver']) && class_exists('Session_Driver_' . Session::$config['driver'], true) )
            {
                $driver_name = 'Session_Driver_' . Session::$config['driver'];
                if ( isset(Session::$config['driver_config']) )
                {
                    $this->driver = new $driver_name(Session::$config['driver_config']);
                }
                else
                {
                    $this->driver = new $driver_name();
                }
            }
            else
            {
                $this->driver = new Session_Driver_Default();
            }

            if ( $vars )
            {
                // Set the new data
                $this->set($vars);
            }

            if ( !isset($_SESSION['_flash_session_']) )
            {
                $_SESSION['_flash_session_'] = array();
            }
            Session::$flash = & $_SESSION['_flash_session_'];

            # 清理Flash Session
            $this->expire_flash();

            $_SESSION['SID'] = $this->driver->session_id();

            if ( !isset($_SESSION['_last_actived_time_']) || TIME-600>$_SESSION['_last_actived_time_'] )
            {
                # 更新最后活动时间 10分钟更新一次
                $_SESSION['_last_actived_time_'] = TIME;
            }

            # 确保关闭前执行保存
            Core::register_shutdown_function(array('Session', 'write_close'));

            Session::$instance = $this;

            if ( null===Session::$member && isset($_SESSION['member_id']) && $_SESSION['member_id']>0 )
            {
                $orm_member = new ORM_Admin_Member_Finder();
                Session::$member = $orm_member->where('id',$_SESSION['member_id'])->find(null,true)->current();
            }
        }
    }



    /**
     * 开启SESSION
     * @return Session
     */
    public function start()
    {
        return $this;
    }

    /**
     * Get the session id.
     *
     * @return  string
     */
    public function id()
    {
        return $_SESSION['SID'];
    }

    /**
     * 銷毀當前Session
     *
     * @return  void
     */
    public function destroy()
    {
        $_SESSION = array();
        Session::$member = null;
        $this->driver->destroy();
    }

    /**
     * 设置用户
     *
     * @param ORM_Admin_Member_Data $member
     * @return Session
     */
    public function set_member(ORM_Admin_Member_Data $member)
    {
        Session::$member = $member;
        if ( $member->id>0 )
        {
            # 设置用户数据
            $_SESSION['member_id'] = $member->id;
            $_SESSION['member_password'] = $member->password;
        }
        else
        {
            # 游客数据则清空
            unset($_SESSION['member_id']);
        }

        return $this;
    }

    /**
     * 返回当前用户id
     *
     * @return int
     */
    public function member_id()
    {
        return $_SESSION['member_id'];
    }

    /**
     * 获取用户对象
     *
     * @return ORM_Admin_Member_Data
     */
    public function member()
    {
        if ( null===Session::$member )
        {
            # 创建一个空的用户对象
            Session::$member = new ORM_Admin_Member_Data();
        }
        return Session::$member;
    }

    /**
     * 最后活动时间
     *
     * @return int
     */
    public function last_actived_time()
    {
        return $_SESSION['_last_actived_time_'];
    }

    /**
     * 此方法用于保存session数据
     *
     * 只执行一次，系统在关闭前会执行
     *
     * @return  void
     */
    public static function write_close()
    {
        if ( null === Session::$instance )
        {
            return false;
        }
        static $run = null;
        if ( $run === null )
        {
            $run = true;

            if ( ! $_SESSION['_flash_session_'] )
            {
                unset($_SESSION['_flash_session_']);
            }

            if ( Session::$member && $_SESSION['member_id'] )
            {
                # 设置用户数据
                $_SESSION['member_id'] = Session::$member->id;
                $_SESSION['member_password'] = Session::$member->password;
            }

            Session::$instance->driver->write_close();
        }
    }

    /**
     * 设置一个session数据
     *
     * @param   string|array  key, or array of values
     * @param   mixed		 value (if keys is not an array)
     * @return  void
     */
    public function set($keys, $val = false)
    {
        if ( empty($keys) ) return false;

        if ( ! is_array($keys) )
        {
            $keys = array($keys => $val);
        }

        foreach ( $keys as $key => $val )
        {
            if ( isset(Session::$protect[$key]) ) continue;

            // Set the key
            $_SESSION[$key] = $val;
        }
    }

    /**
     * 设置一个Session闪存数据
     *
     * @param   string|array  key, or array of values
     * @param   mixed		 value (if keys is not an array)
     * @return  void
     */
    public function set_flash($keys, $val = false)
    {
        if ( empty($keys) ) return false;

        if ( !is_array($keys) )
        {
            $keys = array($keys => $val);
        }

        foreach ( $keys as $key => $val )
        {
            if ( $key == false ) continue;

            Session::$flash[$key] = 'new';
            $this->set($key, $val);
        }
    }

    /**
     * Freshen one, multiple or all flash variables.
     *
     * @param   string  variable key(s)
     * @return  void
     */
    public function keep_flash($keys = null)
    {
        $keys = ($keys === null) ? array_keys(Session::$flash) : func_get_args();

        foreach ( $keys as $key )
        {
            if ( isset(Session::$flash[$key]) )
            {
                Session::$flash[$key] = 'new';
            }
        }
    }

    /**
     * Expires old flash data and removes it from the session.
     *
     * @return  void
     */
    protected function expire_flash()
    {
        if ( !empty(Session::$flash) )
        {
            foreach ( Session::$flash as $key => $state )
            {
                if ( $state === 'old' )
                {
                    // Flash has expired
                    unset(Session::$flash[$key], $_SESSION[$key]);
                }
                else
                {
                    // Flash will expire
                    Session::$flash[$key] = 'old';
                }
            }
        }
    }

    /**
     * Get a variable. Access to sub-arrays is supported with key.subkey.
     *
     * @param   string  variable key
     * @param   mixed   default value returned if variable does not exist
     * @return  mixed   Variable data if key specified, otherwise array containing all session data.
     */
    public function get($key = false, $default = false)
    {
        if ( empty($key) ) return $_SESSION;

        $result = isset($_SESSION[$key]) ? $_SESSION[$key] : Core::key_string($_SESSION, $key);

        return ($result === null) ? $default : $result;
    }

    /**
     * Get a variable, and delete it.
     *
     * @param   string  variable key
     * @param   mixed   default value returned if variable does not exist
     * @return  mixed
     */
    public function get_once($key, $default = false)
    {
        $return = $this->get($key, $default);
        $this->delete($key);

        return $return;
    }

    /**
     * Delete one or more variables.
     *
     * @param   string  variable key(s)
     * @return  void
     */
    public function delete()
    {
        $args = func_get_args();

        foreach ( $args as $key )
        {
            if ( isset(Session::$protect[$key]) ) continue;

            // Unset the key
            unset($_SESSION[$key]);
        }
    }

    public static function session_name()
    {
        return Session::$config['name'];
    }
}