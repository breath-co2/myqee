<?php

/**
 * Redis Storage驱动器
 *
 * @author     jonwang(jonwang@myqee.com)
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Core_Storage_Driver_Redis extends Storage_Driver
{

    /**
     * Redis链接对象
     *
     * @var array
     */
    protected static $redis = array();

    /**
     * 记录$redis对象被引用数
     * @var array
     */
    protected static $redis_num = array();

    /**
     * Redis对象
     *
     * @var Redis
     */
    protected $_redis;

    protected $servers = array();

    /**
     * 当前配置名
     *
     * @var string
     */
    protected $config_name;

    /**
     * Redis缓存驱动器
     *
     * @param $config_name 配置名或数组
     */
    public function __construct($config_name = 'default')
    {
        if (is_array($config_name))
        {
            $this->servers = $config_name;
            $config_name = md5(serialize($config_name));
        }
        else
        {
            $this->servers = Core::config('storage/redis.' . $config_name);
        }

        if (!is_array($this->servers))
        {
            throw new Exception(__('The storage redis config :config does not exist', array(':config'=>$config_name)));
        }
        $this->config_name = $config_name;

        $this->_connect();

        # 增加自动关闭连接列队
        Core::add_close_connect_class('Storage_Driver_Redis');
    }

    public function __destruct()
    {
        $this->close_connect();
    }

    /**
     * 连接服务器
     */
    protected function _connect()
    {
        if ($this->_redis)return;
        if (!$this->config_name)return;

        $config_name = $this->config_name;

        if (!isset(Storage_Driver_Redis::$redis[$config_name]))
        {
            $class = 'Redis';
            Storage_Driver_Redis::$redis[$config_name] = new $class();
            Storage_Driver_Redis::$redis_num[$config_name] = 0;

            foreach ($this->servers as $server)
            {
                $server += array
                (
                    'host'       => '127.0.0.1',
                    'port'       => 6379,
                    'persistent' => true,
                    'timeout'    => 2,
                );

                if ($server['persistent'])
                {
                    $action = 'pconnect';
                }
                else
                {
                    $action = 'connect';
                }

                try
                {
                    $time   = microtime(1);
                    $status = Storage_Driver_Redis::$redis[$config_name]->$action($server['host'], $server['port'],$server['timeout']);
                    $time   = microtime(1)-$time;
                }
                catch (Exception $e)
                {
                    $status = false;
                }

                if ($status)
                {
                    if (IS_DEBUG)Core::debug()->info('connect storage redis server '.$server['host'].':'.$server['port'] . ' time:'.$time);
                    break;
                }
                else
                {
                    if (IS_DEBUG)Core::debug()->error('error connect storage redis server '.$server['host'].':'.$server['port'] . ' time:'.$time);
                }
            }
        }

        # 断开引用关系
        unset($this->_redis);

        # 设置对象
        $this->_redis = & Storage_Driver_Redis::$redis[$config_name];

        Storage_Driver_Redis::$redis_num[$config_name]++;
    }

    /**
     * 关闭连接
     */
    public function close_connect()
    {
        if ( $this->config_name && $this->_redis )
        {
            unset($this->_redis);
            Storage_Driver_Redis::$redis_num[$this->config_name]--;

            if ( 0 == Storage_Driver_Redis::$redis_num[$this->config_name] )
            {
                @Storage_Driver_Redis::$redis[$this->config_name]->close();

                if (IS_DEBUG)Core::debug()->info('close storage redis server.');

                Storage_Driver_Redis::$redis[$this->config_name] = null;
                unset(Storage_Driver_Redis::$redis[$this->config_name]);
                unset(Storage_Driver_Redis::$redis_num[$this->config_name]);
            }
        }
    }

    /**
     * 取得数据，支持批量取
     *
     * @param string/array $key
     * @return mixed
     */
    public function get($key)
    {
        $this->_connect();

        $time = microtime(1);
        if (is_array($key))
        {
            # redis多取
            if ($this->prefix)
            {
                foreach ($key as &$k)
                {
                    $k = $this->prefix . $k;
                }
            }

            $return = $this->_redis->mget($key);

            foreach ( $return as &$item )
            {
                $this->_de_format_data($item);
            }
        }
        else
        {
            $return = $this->_redis->get($key);

            $this->_de_format_data($return);
        }
        $time = microtime(1) - $time;

        if (false===$return)
        {
            Core::debug()->error($key,'storage redis mis key');
            Core::debug()->info($time,'use time');

            return false;
        }
        else
        {
            Core::debug()->info($key,'storage redis hit key');
            Core::debug()->info($time,'use time');
        }

        return $return;
    }

    /**
     * 存数据，支持多存
     *
     * @param string/array $key
     * @param $data Value 多存时此项可空
     * @return boolean
     */
    public function set($key, $value = null)
    {
        $this->_connect();
        Core::debug()->info($key,'storage redis set key');

        if ( is_array($key) )
        {
            foreach ($key as & $item)
            {
                $this->_format_data($item);
            }
            return $this->_redis->mset($key);
        }
        else
        {
            $this->_format_data($value);
            return $this->_redis->set($key, $value);
        }
    }

    /**
     * 删除指定key的缓存，若$key===true则表示删除全部
     *
     * @param string $key
     */
    public function delete($key)
    {
        $this->_connect();
        if ($key === true)
        {

            return $this->_redis->flushAll();
        }
        else
        {
            $keys = func_get_args();
            return $this->_redis->delete($keys);
        }
    }

    /**
     * 删除全部
     */
    public function delete_all()
    {
        return $this->delete(true);
    }

    public function __call($method, $params)
    {
        $this->_connect();

        if (method_exists($this->_redis, $method))
        {
            return call_user_func_array(array($this->_redis,$method), $params);
        }
    }

    /**
     * 关闭所有链接
     */
    public static function close_all_connect()
    {
        foreach (Storage_Driver_Redis::$redis as $config_name=>$obj)
        {
            try
            {
                $obj->close();
            }
            catch (Exception $e)
            {
                Core::debug()->error('close redis storage connect error:'.$e);
            }

            Storage_Driver_Redis::$redis[$config_name] = null;
        }

        # 重置全部数据
        Storage_Driver_Redis::$redis = array();
        Storage_Driver_Redis::$redis_num = array();

        if (IS_DEBUG)Core::debug()->info('close all storage redis server.');
    }
}
