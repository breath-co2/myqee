<?php

/**
 * 数据库存储驱动器
 *
 * @author     jonwang(jonwang@myqee.com)
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license	   http://www.myqee.com/license.html
 */
class Core_Storage_Driver_Database extends Storage_Driver
{
    /**
     * 数据库配置
     *
     * @var string
     */
    protected $database;

    /**
     * 缓存表名称
     *
     * @var string
     */
    protected $tablename;

    /**
     * 数据库对象
     *
     * @var Database
     */
    protected $_handler;

    public function __construct($config_name = 'default')
    {
        if (is_array($config_name))
        {
            $config = $config_name;
        }
        else
        {
            $config = Core::config('storage/database.'.$config_name);
        }

        $this->database  = $config['database'];
        $this->tablename = $config['tablename'];

        if (!$this->tablename)
        {
            throw new Exception(__('Database storage configuration error'));
        }

        $this->_handler = new Database(array('type'=>$config['type'], 'connection'=>$config));
    }

    public function __destruct()
    {
        $this->_handler->close_connect();
    }

    /**
     * 取得数据，支持批量取
     * @param string/array $key
     * @return mixed
     */
    public function get($key)
    {
        if (is_array($key))
        {
            $md5_key = array();
            $key_map = array();
            foreach ($key as &$k)
            {
                $key_map[$this->prefix . $k] = $k;
                $k = $this->prefix . $k;
                $md5_key[] = md5($k);
            }

            $this->_handler->in('key', $md5_key);
        }
        else
        {
            $key = $this->prefix . $key;

            $this->_handler->where('key', md5($key))->limit(1);
        }

        $rs = $this->_handler->select('key_string', 'value')->from($this->tablename)->get();

        if ($rs->count())
        {
            if (is_array($key))
            {
                $return = array();
                foreach ($rs as $data)
                {
                    $data_key = $key_map[$data['key_string']];
                    $return[$data_key] = $data['value'];
                    $this->_de_format_data($return[$data_key]);
                }
            }
            else
            {
                $return = $rs->current();
                $return = $return['value'];
                $this->_de_format_data($return);
            }

            if (IS_DEBUG)Core::debug()->info($key, 'database storage hit key');

            unset($rs);

            return $return;
        }
        else
        {
            if (IS_DEBUG)Core::debug()->error($key, 'database storage mis key');
        }

        return false;
    }

    /**
     * 存数据
     *
     * @param string/array $key 支持多存
     * @param $data Value 多存时此项可空
     * @param $lifetime 有效期，默认3600，即1小时，0表示不限制
     * @return boolean
     */
    public function set($key, $value = null)
    {
        if (IS_DEBUG)Core::debug()->info($key, 'database storage set key');

        if (is_array($key))
        {
            foreach ($key as $k=>$v)
            {
                $k = $this->prefix . $k;

                $this->_format_data($value[$k]);

                $data = array
                (
                    md5($k),
                    $k,
                    $value[$k],
                );

                $this->_handler->values($data);
            }
        }
        else
        {
            $key = $this->prefix . $key;

            $this->_format_data($value);
            $data = array
            (
                md5($key),
                $key,
                $value,
            );

            $this->_handler->values($data);
        }

        $rs = $this->_handler->columns(array('key', 'key_string', 'value'))->replace($this->tablename);

        if ($rs[0])
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 删除指定key的缓存，若$key===true则表示删除全部
     *
     * @param string $key
     */
    public function delete($key)
    {
        if (IS_DEBUG)Core::debug()->info($key, 'database storage delete key');

        if (is_array($key))
        {
            $new_keys = array();
            foreach ($key as $k)
            {
                $k = $this->prefix . $k;
                $new_keys[] = md5($k);
            }

            $this->_handler->in('key', $new_keys);
        }
        elseif (true!==$key)
        {
            $key = $this->prefix . $key;
            $this->_handler->where('key', $key);
        }

        try
        {
            $this->_handler->delete($this->tablename);
            return true;
        }
        catch (Exception $e)
        {
            Core::debug()->error($e->getMessage());
            return false;
        }
    }

    /**
     * 删除全部
     */
    public function delete_all()
    {
        return $this->delete(true);
    }
}