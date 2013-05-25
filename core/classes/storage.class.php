<?php

/**
 * 持久存储类
 *
 * @author     jonwang(jonwang@myqee.com)
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license	   http://www.myqee.com/license.html
 */
class Core_Storage
{
    /**
     * 驱动类型为文件
     *
     * @var string
     */
    const DRIVER_FILE = 'File';

    /**
     * 驱动类型为Redis
     *
     * @var string
     */
    const DRIVER_REDIS = 'Redis';

    /**
     * 驱动类型为OpenStack Object Storage (Swift)
     *
     * @see http://www.openstack.org/software/openstack-storage/
     * @var string
     */
    const DRIVER_SWIFT = 'Swift';

    /**
     * 驱动类型为Database
     *
     * @var string
     */
    const DRIVER_DATABASE = 'Database';


    protected static $instances = array();

    /**
     * 驱动对象
     *
     * @var Storage_Driver_File
     */
    protected $driver;

    /**
     * 配置
     *
     * @var array
     */
    protected $config;

    /**
     * 返回数据库实例化对象
     *
     * 支持 `Database::instance('mysqli://root:123456@127.0.0.1/myqee/');` 的方式
     *
     * @param string $config_name
     * @return Database
     */
    public static function instance($config_name = 'default')
    {
        if (is_string($config_name))
        {
            $i_name = $config_name;
        }
        else
        {
            $i_name = '.config_' . md5(serialize($config_name));
        }

        if (!isset(Storage::$instances[$i_name]))
        {
            Storage::$instances[$i_name] = new Storage($config_name);
        }

        return Storage::$instances[$i_name];
    }

    public function __construct($config_name = 'default')
    {
        if (is_array($config_name))
        {
            $this->config = $config_name;
        }
        else
        {
            $this->config = Core::config('storage.' . $config_name);
        }

        if (!isset($this->config['driver']))
        {
            $this->config['driver'] = Storage::DRIVER_FILE;
        }

        $driver = 'Storage_Driver_' . $this->config['driver'];
        if (!class_exists($driver, true))
        {
            throw new Exception(__('The :type driver :driver does not exist', array(':type'=>'Storge',':driver'=>$this->config['driver'])));
        }

        $this->driver = new $driver($this->config['driver_config']);

        # 设置前缀
        if ($this->config['prefix'])
        {
            $this->driver->set_prefix($this->config['prefix']);
        }
    }

    /**
     * 获取指定KEY的缓存数据
     *
     *     $strone->get('a');
     *     $strone->get('a','b','c');
     *     $strone->get(array('a','b','c'));
     *
     * @param string $key 指定key
     * @return mixed
     * @return false 返回失败
     */
    public function get($key)
    {
        $columns = func_get_args();
        if (count($columns)>1)
        {
            $key = $columns;
        }

        if (null===$key)
        {
            return null;
        }

        return $this->driver->get($key);
    }

    /**
     * 保存内容
     *
     * @param $key string
     * @param $value fixed
     * @return boolean
     */
    public function set($key, $value)
    {
        return $this->driver->set($key, $value);
    }

    /**
     * 是否存在关键字的数据
     *
     * @param string $key
     * @return boolean
     */
    public function exists($key)
    {
        return $this->driver->exists($key);
    }

    /**
     * 删除指定key的缓存数据
     *
     * @param string $key
     * @return boolean
     */
    public function delete($key)
    {
        return $this->driver->delete($key);
    }

    /**
     * 删除全部缓存
     *
     * delete_all()的别名
     *
     * @return boolean
     */
    public function clean()
    {
        return $this->delete_all();
    }

    /**
     * 删除全部缓存
     *
     * @return boolean
     */
    public function delete_all()
    {
        return $this->driver->delete_all();
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function __set($key, $value)
    {
        return $this->set($key, $value);
    }

    public function __unset($key)
    {
        return $this->delete($key);
    }

    public function __call($method, $params)
    {
        return call_user_func_array(array($this->driver,$method) , $params);
    }

}