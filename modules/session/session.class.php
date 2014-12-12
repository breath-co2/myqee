<?php

/**
 * Session核心类
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Module
 * @package    Session
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Module_Session
{

    /**
     * @var Session
     */
    protected static $instance = null;

    protected static $protect = array('SID' => 1, '_flash_session_' => 1);

    public static $config;

    protected static $flash;

    /**
     * Session驱动
     *
     * @var Session_Driver_Default
     */
    protected static $driver;

    /**
     * @var Member
     */
    protected static $member;

    /**
     * @return Session
     */
    public static function instance()
    {
        if (null===Session::$instance)
        {
            // Create a new instance
            new Session();
        }

        return Session::$instance;
    }

    public function __construct($vars = null)
    {
        // This part only needs to be run once
        if (null===Session::$instance)
        {
            // Load config
            Session::$config = Core::config('session');

            if (!isset(Session::$config['check_string']) || !Session::$config['check_string'])
            {
                Session::$config['check_string'] = '&$@de23#$%@.d3l-3=!#1';
            }

            if (!isset(Session::$config['name']) || !preg_match('#^(?=.*[a-z])[a-z0-9_]++$#iD', Session::$config['name']))
            {
                // Name the session, this will also be the name of the cookie
                Session::$config['name'] = 'PHPSESSINID';
            }

            if (IS_DEBUG)
            {
                $time = microtime(1);
                $is_debug = (bool)Core::debug()->profiler()->is_open();
                if ($is_debug)
                {
                    Core::debug()->profiler()->start('Core', 'Session StartTime');
                }
            }

            if (isset(Session::$config['driver']) && class_exists('Session_Driver_' . Session::$config['driver'], true))
            {
                $driver_name = 'Session_Driver_' . Session::$config['driver'];

                if (isset(Session::$config['driver_config']))
                {
                    Session::$driver = new $driver_name(Session::$config['driver_config']);
                }
                else
                {
                    Session::$driver = new $driver_name();
                }
            }
            else
            {
                Session::$driver = new Session_Driver_Default();
            }

            if (!isset(Session::$config['type']) || Session::$config['type']!='url')
            {
                Session::$config['type'] = 'cookie';
            }

            if (IS_DEBUG)
            {
                if ($is_debug)
                {
                    Core::debug()->profiler()->stop();
                }

                # 输出Session启动使用时间
                Core::debug()->info(microtime(1)-$time, 'Session start use time');
            }

            if ($vars)
            {
                $this->set($vars);
            }

            if (!isset($_SESSION['_flash_session_']))
            {
                $_SESSION['_flash_session_'] = array();
            }
            Session::$flash = & $_SESSION['_flash_session_'];

            # 清理Flash Session
            $this->expire_flash();

            $_SESSION['SID'] = Session::$driver->session_id();

            # 确保关闭前执行保存
            Core::register_shutdown_function(array('Session', 'write_close'));

            Session::$instance = $this;

            # 加载用户数据
            Session::load_member_data();
        }
    }


    /**
     * 开启SESSION
     *
     * @return Session
     */
    public function start()
    {
        return $this;
    }

    /**
     * 获取SESSION ID
     *
     * @return  string
     */
    public function id()
    {
        return $_SESSION['SID'];
    }

    /**
     * 获取驱动对象
     *
     * @return Session_Driver_Default
     */
    public function driver()
    {
        return Session::$driver;
    }

    /**
     * 销毁当前Session
     *
     * @return  void
     */
    public function destroy()
    {
        $_SESSION = array();
        Session::$driver->destroy();
        Session::$member   = null;
        Session::$driver   = null;
        Session::$instance = null;
    }

    /**
     * 设置用户
     *
     * @param Member $member
     * @return Session
     */
    public function set_member(Member $member)
    {
        Session::$member = $member;
        if ($member->id>0)
        {
            # 设置用户数据
            $member_data        = $member->get_all_field_data();
            $_SESSION['member'] = $member_data;
        }
        else
        {
            # 游客数据则清空
            unset($_SESSION['member']);
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
        return $_SESSION['member']['id'];
    }

    /**
     * 获取用户对象
     *
     * @return Member
     */
    public function member()
    {
        if (null===Session::$member)
        {
            # 创建一个空的用户对象
            Session::$member = new Member();
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
        if (!isset($_SESSION['_last_actived_time_']))
        {
            $_SESSION['_last_actived_time_'] = TIME;
        }
        return $_SESSION['_last_actived_time_'];
    }


    /**
     * 设置SESSION数据
     *
     *     Session::instance()->set('key','value');
     *
     *     Session::instance()->set(array('key'=>'value','k2'=>'v2'));
     *
     * @param   string | array $val  key, or array of values
     * @param   mixed $val value (if keys is not an array)
     * @return  void
     */
    public function set($keys, $val = false)
    {
        if (empty($keys))return false;

        if (!is_array($keys))
        {
            $keys = array($keys => $val);
        }

        foreach ($keys as $key => $val)
        {
            if (isset(Session::$protect[$key])) continue;

            // Set the key
            $_SESSION[$key] = $val;
        }
    }

    /**
     * 设置一个闪存SESSION数据，在下次请求的时候会获取后自动销毁
     *
     * @param   string | array $keys key, or array of values
     * @param   mixed        $val value (if keys is not an array)
     * @return  void
     */
    public function set_flash($keys, $val = false)
    {
        if (empty($keys)) return false;

        if (!is_array($keys))
        {
            $keys = array($keys => $val);
        }

        foreach ($keys as $key => $val)
        {
            if (false==$key)continue;

            Session::$flash[$key] = 'new';
            $this->set($key, $val);
        }
    }

    /**
     * 保持闪存SESSION数据不销毁
     *
     * @param   string  variable key(s)
     * @return  void
     */
    public function keep_flash($keys = null)
    {
        $keys = (null===$keys) ? array_keys(Session::$flash) : func_get_args();

        foreach ($keys as $key)
        {
            if (isset(Session::$flash[$key]))
            {
                Session::$flash[$key] = 'new';
            }
        }
    }

    /**
     * 标记闪存SESSION数据为过期
     *
     * @return  void
     */
    public function expire_flash()
    {
        if (!empty(Session::$flash))
        {
            foreach (Session::$flash as $key => $state)
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
     * 获取一个SESSION数据
     *
     *     Session::instance()->get('key');
     *
     *     Session::instance()->get('key','default value');
     *
     * @param   string $key variable key
     * @param   mixed  $default default value returned if variable does not exist
     * @return  mixed   Variable data if key specified, otherwise array containing all session data.
     */
    public function get($key = false, $default = false)
    {
        if (empty($key))return $_SESSION;

        $result = isset($_SESSION[$key]) ? $_SESSION[$key] : Core::key_string($_SESSION, $key);

        return (null===$result) ? $default : $result;
    }

    /**
     * 获取后删除相应KEY的SESSION数据
     *
     * @param   string $key variable key
     * @param   mixed  $default default value returned if variable does not exist
     * @return  mixed
     */
    public function get_once($key, $default = false)
    {
        $return = $this->get($key, $default);
        $this->delete($key);

        return $return;
    }

    /**
     * 删除指定key的SESSION数据
     *
     *     Session::instance()->delete('key');
     *
     *     //删除key1和key2的数据
     *     Session::instance()->delete('key1','key2');
     *
     * @param   string $key1 variable key(s)
     * @return  void
     */
    public function delete($key1=null, $key2=null)
    {
        $args = func_get_args();

        foreach ($args as $key)
        {
            if (isset(Session::$protect[$key])) continue;

            unset($_SESSION[$key]);
        }
    }

    /**
     * 此方法用于保存session数据
     *
     * 系统在关闭前会执行
     *
     * @return void
     */
    public static function write_close()
    {
        if (Session::$instance)
        {
            if (!$_SESSION['_flash_session_'])
            {
                unset($_SESSION['_flash_session_']);
            }

            if (!isset($_SESSION['_last_actived_time_']) || TIME - 300 > $_SESSION['_last_actived_time_'])
            {
                # 更新最后活动时间 10分钟更新一次
                $_SESSION['_last_actived_time_'] = TIME;
            }

            Session::write_member_data();

            Session::$driver->write_close();

            Session::$instance = null;
        }
    }

    /**
     * 获取SESSION名称
     */
    public static function session_name()
    {
        return Session::$config['name'];
    }

    /**
     * 生成一个新的Session ID
     *
     * @return string 返回一个32长度的session id
     */
    public static function create_session_id()
    {
        # 获取一个唯一字符
        $mt_str = substr(md5(microtime(1).'d2^2**(fgGs@.d3l-' . mt_rand(1, 9999999) . HttpIO::IP), 2, 28);

        # 校验位
        $mt_str .= substr(md5('doe9%32' . $mt_str . Session::$config['check_string']), 8, 4);

        return $mt_str;
    }

    /**
     * 检查当前Session ID是否合法
     *
     * @param string $sid
     * @return boolean
     */
    public static function check_session_id($sid)
    {
        if (strlen($sid)!=32)return false;
        if (!preg_match('/^[a-fA-F\d]{32}$/', $sid))return false;

        $mt_str = substr($sid, 0, 28);
        $check_str = substr($sid, -4);

        if (substr(md5('doe9%32' . $mt_str . Session::$config['check_string']), 8, 4) === $check_str)
        {
            return true;
        }
        else
        {
            return false;
        }
    }


    /**
     * Session在加载时读取用户数据
     */
    protected static function load_member_data()
    {
        if (null===Session::$member && isset($_SESSION['member']))
        {
            Session::$member = new Member($_SESSION['member']);
        }
    }

    /**
     * Session在关闭时写入用户session数据
     */
    protected static function write_member_data()
    {
        if (Session::$member && Session::$member->id>0)
        {
            # 设置用户数据
            $member_data = Session::$member->get_all_field_data();

            $_SESSION['member'] = $member_data;
        }
    }
}

