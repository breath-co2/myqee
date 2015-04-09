<?php

/**
 * 数据库缓存驱动器
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Driver
 * @package    Cache
 * @copyright  Copyright (c) 2008-2016 myqee.com
 * @license	   http://www.myqee.com/license.html
 */
class Driver_Cache_Driver_Database extends Cache_Driver
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
            $config = Core::config('cache/database.'.$config_name);
        }

        $this->database  = $config['database'];
        $this->tablename = $config['tablename'];

        if (!$this->tablename)
        {
            throw new Exception(__('Database cache configuration error'));
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
        $key_map = array();
        if (is_array($key))
        {
            $md5_key = array();
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
            $key_map[$this->prefix . $key] = $key;
            $key = $this->prefix . $key;

            $this->_handler->where('key', md5($key))->limit(1);
        }

        $rs = $this->_handler->select('key_string', 'value', 'number')->from($this->tablename)->and_where_open()->where('expire', 0)->or_where('expire', TIME,'>')->and_where_close()->get();

        if ($rs->count())
        {
            if (is_array($key))
            {
                $return = array();
                foreach ($rs as $data)
                {
                    $data_key = $key_map[$data['key_string']];
                    $return[$data_key] = $data['value'];

                    if (''===$data['value'])
                    {
                        $return[$data_key] = $data['number'];
                    }
                    else
                    {
                        $this->_de_format_data($return[$data_key]);
                    }
                }
            }
            else
            {
                $return = $rs->current();

                if (''===$return['value'])
                {
                    $return = $return['number'];
                }
                else
                {
                    $return = $return['value'];
                    $this->_de_format_data($return);
                }
            }

            if (IS_DEBUG)Core::debug()->info($key, 'database cache hit key');

            unset($rs);

            return $return;
        }
        else
        {
            if (IS_DEBUG)Core::debug()->warn($key, 'database cache mis key');
        }

        return false;
    }

    /**
     * 存数据
     *
     * @param string/array $key 支持多存
     * @param mixed $value Value 多存时此项可空
     * @param mixed $lifetime 有效期，默认3600，即1小时，0表示不限制
     * @return boolean
     */
    public function set($key, $value = null, $lifetime = 3600)
    {
        if (IS_DEBUG)Core::debug()->info($key, 'database cache set key');

        if ($lifetime>0)
        {
            $lifetime += TIME;
        }

        if (is_array($key))
        {
            foreach ($key as $k=>$v)
            {
                $k = $this->prefix . $k;

                if (is_numeric($v))
                {
                    $data = array
                    (
                        md5($k),
                        $k,
                        '',
                        $v,
                        $lifetime,
                    );
                }
                else
                {
                    $this->_format_data($value[$k]);

                    $data = array
                    (
                        md5($k),
                        $k,
                        $value[$k],
                        0,
                        $lifetime,
                    );
                }

                $this->_handler->values($data);
            }
        }
        else
        {
            $key = $this->prefix . $key;

            if (is_numeric($value))
            {
                # 对于数值型数据，存在number字段里
                $data = array
                (
                    md5($key),
                    $key,
                    '',
                    $value,
                    $lifetime,
                );
            }
            else
            {
                $this->_format_data($value);
                $data = array
                (
                    md5($key),
                    $key,
                    $value,
                    0,
                    $lifetime,
                );
            }

            $this->_handler->values($data);
        }

        $rs = $this->_handler->columns(array('key', 'key_string', 'value', 'number', 'expire'))->replace($this->tablename);

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
        if (IS_DEBUG)Core::debug()->info($key, 'database delete key');

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

    /**
     * 删除过期数据
     *
     */
    public function delete_expired()
    {
        try
        {
            $this->_handler->where('expire', 0, '>')->where('expire', TIME, '<=')->delete($this->tablename);
            return true;
        }
        catch (Exception $e)
        {
            Core::debug()->error($e->getMessage());
            return false;
        }
    }

    /**
     * 递减
     *
     * @param string $key
     * @param int $offset
     * @param int $lifetime 当递减失则时当作set使用
     */
    public function decrement($key, $offset = 1, $lifetime = 60)
    {
        return $this->increment($key, -$offset, $lifetime);
    }

    /**
     * 递增
     *
     * @param string $key
     * @param int $offset
     * @param int $lifetime 当递减失则时当作set使用
     */
    public function increment($key, $offset = 1, $lifetime = 60)
    {
        # 首先尝试递增
        $s = $this->_handler->value_increment('number', $offset)->where('key', md5($this->prefix.$key))->update($this->tablename, array('value'=>''));

        if (!$s)
        {
            # 没有更新到数据，尝试插入数据
            return $this->set($key, $offset, $lifetime);
        }

        return false;
    }
}