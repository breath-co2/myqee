<?php

/**
 * Memcache缓存驱动器
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Module
 * @package    Cache
 * @copyright  Copyright (c) 2008-2016 myqee.com
 * @license	   http://www.myqee.com/license.html
 */
class Driver_Cache_Driver_Memcache extends Cache_Driver
{

    /**
     * Memcache链接对象
     * @var array
     */
    protected static $memcaches = array();

    /**
     * 记录$memcaches对象被引用数
     * @var array
     */
    protected static $memcaches_num = array();

    /**
     * Memcache对象
     * @var Memcache
     */
    protected $_memcache;

    protected $servers = array();

    /**
     * 当前配置名
     * @var string
     */
    protected $config_name;

    protected static $_memcached_mode = null;

    /**
     * Memcache缓存驱动器
     * @param string $config_name 配置名或数组
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
            $this->servers = Core::config('cache/memcache.' . $config_name);
        }

        if (!is_array($this->servers))
        {
            throw new Exception(__('The memcache config :config does not exist', array(':config'=>$config_name)));
        }

        $this->config_name = $config_name;

        $this->_connect();

        # 增加自动关闭连接列队
        Core::add_close_connect_class('Cache_Driver_Memcache');
    }

    public function __destruct()
    {
        $this->close_connect();
    }

    /**
     * 连接memcache服务器
     */
    protected function _connect()
    {
        if ($this->_memcache)return;
        if (!$this->config_name)return;

        $config_name = $this->config_name;

        if (!isset(Cache_Driver_Memcache::$memcaches[$config_name]))
        {

            if (null === Cache_Driver_Memcache::$_memcached_mode)
            {
                if (function_exists('extension_loaded'))
                {
                    # 优先采用memcached扩展
                    if (extension_loaded('memcached'))
                    {
                        Cache_Driver_Memcache::$_memcached_mode = true;
                    }
                    elseif (extension_loaded('memcache'))
                    {
                        Cache_Driver_Memcache::$_memcached_mode = false;
                    }
                    else
                    {
                        throw new Exception(__('The system did not load memcached or memcache extension'));
                    }
                }
            }

            if (Cache_Driver_Memcache::$_memcached_mode)
            {
                $memcache = 'memcached';
            }
            else
            {
                $memcache = 'memcache';
            }

            Cache_Driver_Memcache::$memcaches[$config_name] = new $memcache();
            Cache_Driver_Memcache::$memcaches_num[$config_name] = 0;

            if (Cache_Driver_Memcache::$_memcached_mode)
            {
                Cache_Driver_Memcache::$memcaches[$config_name]->addServers($this->servers);
            }
            else
            {
                foreach ($this->servers as $server)
                {
                    $server += array('host' => '127.0.0.1', 'port' => 11211, 'persistent' => true);

                    Cache_Driver_Memcache::$memcaches[$config_name]->addServer($server['host'], $server['port'], (bool)$server['persistent'], $server['weight'], 1, 15, true, 'Cache_Driver_Memcache::failure_addserver');

                    if (IS_DEBUG)Core::debug()->info('add memcache server '.$server['host'].':'.$server['port']);
                }
            }
        }

        # 断开引用关系
        unset($this->_memcache);

        # 设置memcache
        $this->_memcache =& Cache_Driver_Memcache::$memcaches[$config_name];

        Cache_Driver_Memcache::$memcaches_num[$config_name]++;
    }

    /**
     * 关闭memcache连接
     */
    public function close_connect()
    {
        if ($this->config_name && $this->_memcache)
        {
            unset($this->_memcache);
            Cache_Driver_Memcache::$memcaches_num[$this->config_name]--;

            if (0 == Cache_Driver_Memcache::$memcaches_num[$this->config_name])
            {

                if (!Cache_Driver_Memcache::$_memcached_mode)
                {
                    @Cache_Driver_Memcache::$memcaches[$this->config_name]->close();
                }

                if (IS_DEBUG)Core::debug()->info('close memcache server.');

                Cache_Driver_Memcache::$memcaches[$this->config_name] = null;
                unset(Cache_Driver_Memcache::$memcaches[$this->config_name]);
                unset(Cache_Driver_Memcache::$memcaches_num[$this->config_name]);
            }
        }
    }

    /**
     * 取得memcache数据，支持批量取
     *
     * @param string/array $key
     * @return mixed
     */
    public function get($key)
    {
        # 尝试连接
        $this->_connect();

        $is_array_key = is_array($key);

        # 有前缀
        if ($this->prefix)
        {
            if ($is_array_key)
            {
                $key_map = array();
                foreach ($key as &$k)
                {
                    $key_map[$this->prefix . $k] = $k;
                    $k = $this->prefix . $k;
                }
            }
            else
            {
                $key = $this->prefix . $key;
            }
        }

        if (Cache_Driver_Memcache::$_memcached_mode && $is_array_key)
        {
            $return = $this->_memcache->getMulti($key);
        }
        else
        {
            $return = $this->_memcache->get($key);
        }

        # 移除前缀
        if ($is_array_key && $return && $this->prefix)
        {
            $new_rs = array();
            foreach ($return as $k=>$item)
            {
                $new_rs[$key_map[$k]] = $item;
            }
            $return = $new_rs;
        }

        if (false === $return)
        {
            if(IS_DEBUG)Core::debug()->warn($key, 'memcache mis key');
            return false;
        }
        else
        {
            if (IS_DEBUG)Core::debug()->info($key, 'memcache hit key');
        }

        return $return;
    }

    /**
     * 给memcache存数据
     *
     * @param string/array $key 支持多存
     * @param mixed $value Value 多存时此项可空
     * @param int $lifetime 有效期，默认3600，即1小时，0表示最大值30天（2592000）
     * @return boolean
     */
    public function set($key, $value = null, $lifetime = 3600)
    {
        $this->_connect();

        $is_array_key = is_array($key);

        # 加前缀
        if ($this->prefix)
        {
            if ($is_array_key)
            {
                $new_data = array();
                foreach ($key as $k=>$v)
                {
                    $new_data[$this->prefix . $k] = $v;
                }
                $key = $new_data;
                unset($new_data);
            }
            else
            {
                $key = $this->prefix . $key;
            }
        }

        if (Cache_Driver_Memcache::$_memcached_mode)
        {
            // memcached
            if ($is_array_key)
            {
                $rs = $this->_memcache->setMulti($key, $lifetime);
            }
            else
            {
                $rs = $this->_memcache->set($key, $value, $lifetime);
            }
        }
        else
        {
            // memcache
            if ($is_array_key)
            {
                $rs = true;
                foreach ($key as $k => $v)
                {
                    $s = $this->_memcache->set($k, $v, $this->_get_flag($v), $lifetime);

                    if (false===$s)
                    {
                        $rs = false;
                    }
                }
            }
            else
            {
                $rs = $this->_memcache->set($key, $value, $this->_get_flag($value), $lifetime);
            }
        }

        if (IS_DEBUG)
        {
            if (is_array($key))
            {
                Core::debug()->info(array_keys($key), 'memcache set key');
            }
            else
            {
                Core::debug()->info($key, 'memcache set key');
            }
        }

        return $rs;
    }

    /**
     * 删除指定key的缓存，若$key===true则表示删除全部
     *
     * @param string $key
     */
    public function delete($key)
    {
        $this->_connect();

        if (true===$key)
        {
            if (Cache_Driver_Memcache::$_memcached_mode)
            {
                $status = $this->_memcache->flush(1);
            }
            else
            {
                $status = $this->_memcache->flush();

                if ($status)
                {
                    // We must sleep after flushing, or overwriting will not work!
                    // @see http://php.net/manual/en/function.memcache-flush.php#81420
                    sleep(1);
                }
            }
        }
        else if (is_array($key))
        {
            # 加前缀
            if ($this->prefix)
            {
                foreach ($key as &$k)
                {
                    $k = $this->prefix . $k;
                }
            }

            if (Cache_Driver_Memcache::$_memcached_mode)
            {
                $status = $this->_memcache->deleteMulti($key);
            }
            else
            {
                # 循环的删除
                foreach ($key as $k)
                {
                    $this->_memcache->delete($k);
                }

                $status = true;
            }
        }
        else
        {
            $status = $this->_memcache->delete($this->prefix . $key);
        }

        if (IS_DEBUG)Core::debug()->info($key, 'memcache delete key');

        return $status;
    }

    /**
     * 删除全部
     */
    public function delete_all()
    {
        return $this->delete(true);
    }

    /**
     * 过期数据会自动清除
     *
     */
    public function delete_expired()
    {
        return true;
    }

    /**
     * 递减
     * 与原始decrement方法区别的是若memcache不存指定KEY时返回false，这个会自动递减
     *
     * @param string $key
     * @param int $offset
     * @param int $lifetime 当递减失则时当作set使用
     */
    public function decrement($key, $offset = 1, $lifetime = 60)
    {
        if ($this->_memcache->decrement($this->prefix . $key, $offset))
        {
            return true;
        }
        elseif ($this->get($key) === null && $this->set($key, $offset, $lifetime))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 递增
     * 与原始increment方法区别的是若memcache不存指定KEY时返回false，这个会自动递增
     *
     * @param string $key
     * @param int $offset
     * @param int $lifetime 当递减失则时当作set使用
     */
    public function increment($key, $offset = 1, $lifetime = 60)
    {
        if ($this->_memcache->increment($this->prefix . $key, $offset))
        {
            return true;
        }
        elseif (null===$this->get($key) && $this->set($key, $offset, $lifetime))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    protected function _get_flag($data)
    {
        if (is_int($data) || is_float($data))
        {
            return false;
        }
        else if (is_string($data) || is_array($data) || is_object($data))
        {
            return MEMCACHE_COMPRESSED;
        }
        else
        {
            return false;
        }
    }

    public function __call($method, $params)
    {
        $this->_connect();
        if (method_exists($this->_memcache, $method))
        {
            return call_user_func_array(array($this->_memcache, $method), $params);
        }
    }

    /**
     * 关闭所有memcache链接
     */
    public static function close_all_connect()
    {
        if (!Cache_Driver_Memcache::$_memcached_mode)
        {
            foreach (Cache_Driver_Memcache::$memcaches as $config_name=>$memcache)
            {
                try
                {
                    $memcache->close();
                }
                catch (Exception $e)
                {
                    Core::debug()->error('close memcache connect error:' . $e);
                }

                Cache_Driver_Memcache::$memcaches[$config_name] = null;
            }
        }

        # 重置全部数据
        Cache_Driver_Memcache::$memcaches     = array();
        Cache_Driver_Memcache::$memcaches_num = array();

        if (IS_DEBUG)Core::debug()->info('close all memcache server.');
    }

    public static function failure_addserver($host, $port, $udp, $info, $code)
    {
        if (IS_DEBUG)Core::debug()->warn('memcache server failover:' . ' host: ' . $host . ' port: ' . $port . ' udp: ' . $udp . ' info: ' . $info . ' code: ' . $code);
    }
}