<?php

/**
 * 持久存储类
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Module
 * @package    Storage
 * @copyright  Copyright (c) 2008-2016 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Module_Storage
{
    /**
     * 驱动类型为文件
     *
     * @var string
     */
    const DRIVE_FILE = 'File';

    /**
     * 驱动类型为Redis
     *
     * @var string
     */
    const DRIVE_REDIS = 'Redis';

    /**
     * 驱动类型为OpenStack Object Storage (Swift)
     *
     * @see http://www.openstack.org/software/openstack-storage/
     * @var string
     */
    const DRIVE_SWIFT = 'Swift';

    /**
     * 驱动类型为Database
     *
     * @var string
     */
    const DRIVE_DATABASE = 'Database';

    /**
     * 默认配置名
     *
     * @var string
     */
    const DEFAULT_CONFIG_NAME = 'default';


    protected static $instances = array();

    /**
     * 驱动对象
     *
     * @var Storage_Drive_File
     */
    protected $drive;

    /**
     * 配置
     *
     * @var array
     */
    protected $config;

    /**
     * 返回数据库实例化对象
     *
     * @param string $config_name 默认值为 Database::DEFAULT_CONFIG_NAME
     * @return Database
     */
    public static function instance($config_name = null)
    {
        if (null===$config_name)
        {
            $config_name = Storage::DEFAULT_CONFIG_NAME;
        }

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

    public function __construct($config_name = null)
    {
        if (null===$config_name)
        {
            $config_name = Storage::DEFAULT_CONFIG_NAME;
        }

        if (is_array($config_name))
        {
            $this->config = $config_name;
        }
        else
        {
            $this->config = Core::config('storage.' . $config_name);
        }

        if (!isset($this->config['drive']))
        {
            $this->config['drive'] = Storage::DRIVE_FILE;
        }

        $drive = 'Storage_Drive_' . $this->config['drive'];
        if (!class_exists($drive, true))
        {
            throw new Exception(__('The :type drive :drive does not exist', array(':type'=>'Storge',':drive'=>$this->config['drive'])));
        }

        $this->drive = new $drive($this->config['drive_config']);

        # 设置前缀
        if ($this->config['prefix'])
        {
            $this->drive->set_prefix($this->config['prefix']);
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

        return $this->drive->get($key);
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
        return $this->drive->set($key, $value);
    }

    /**
     * 是否存在关键字的数据
     *
     * @param string $key
     * @return boolean
     */
    public function exists($key)
    {
        return $this->drive->exists($key);
    }

    /**
     * 删除指定key的缓存数据
     *
     * @param string $key
     * @return boolean
     */
    public function delete($key)
    {
        return $this->drive->delete($key);
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
        return $this->drive->delete_all();
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
        return call_user_func_array(array($this->drive,$method) , $params);
    }

}