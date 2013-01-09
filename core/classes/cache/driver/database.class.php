<?php

/**
 * 数据库缓存驱动器
 *
 * @author     jonwang(jonwang@myqee.com)
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2012 myqee.com
 * @license	   http://www.myqee.com/license.html
 */
class MyQEE_Cache_Driver_Database
{

    /**
     * 是否开启缓存
     *
     * @var boolean
     */
    const DATA_COMPRESS = true;

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

    /**
     * 是否压缩数据
     *
     * @var boolean
     */
    protected $_compress = false;

    public function __construct($config_name = 'default')
    {
        if ( is_array($config_name) )
        {
            $config = $config_name;
        }
        else
        {
            $config = Core::config('cache/database.'.$config_name);
        }

        $this->database = $config['database'];
        $this->tablename = $config['tablename'];

        if ( !$this->tablename )
        {
            throw new Exception('数据库缓存配置错误。');
        }

        if ( Cache_Driver_Database::DATA_COMPRESS && function_exists('gzcompress') )
        {
            $this->_compress = true;
        }

        $this->_handler = new Database(array('type'=>$config['type'],'connection'=>$config));
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
        if (IS_DEBUG)$key_bak = $key;

        if ( is_array($key) )
        {
            $this->_handler->in('key',array_map('md5', $key));
        }
        else
        {
            $this->_handler->where('key',md5($key))->limit(1);
        }

        $rs = $this->_handler->select('key','value','number')->from($this->tablename)->and_where_open()->where('expire',0)->or_where('expire', TIME,'>')->and_where_close()->get();

        if ( $rs->count() )
        {
            if ( is_array($key) )
            {
                $return = array();
                foreach ( $rs as $data )
                {
                    $return[$data['key']] = $data['value'];

                    if ( ''===$data['value'] )
                    {
                        $return[$data['key']] = $data['number'];
                    }
                    else
                    {
                        if($this->_compress)
                        {
                            //启用数据压缩
                            $return[$data['key']] = gzuncompress($data['value']);
                        }
                        $return[$data['key']] = @unserialize($return);
                    }
                }
            }
            else
            {
                $return = $rs->current();

                if ( ''===$return['value'] )
                {
                    $return = $return['number'];
                }
                else
                {
                    $return = $return['value'];
                    if($this->_compress)
                    {
                        //启用数据压缩
                        $return = gzuncompress($return);
                    }

                    $return = @unserialize($return);
                }
            }

            if (IS_DEBUG)Core::debug()->info($key_bak,'database cache hit key');

            return $return;
        }
        else
        {
            if (IS_DEBUG)Core::debug()->error($key_bak,'database cache mis key');
        }

        return false;
    }

    /**
     * 存数据
     *
     * @param string/array $key 支持多存
     * @param $data Value 多存时此项可空
     * @param $lifetime 有效期，默认3600，即1小时，0表示最大值30天（2592000）
     * @return boolean
     */
    public function set($key, $value = null, $lifetime = 3600)
    {
        if (IS_DEBUG)Core::debug()->info($key,'database cache set key');

        if ($lifetime>0)
        {
            $lifetime += TIME;
        }
        if ( is_array($key) )
        {
            foreach ($key as $k)
            {
                if (is_numeric($value[$k]))
                {
                    $data = array
                    (
                        md5($k),
                        $k,
                        '',
                        $value[$k],
                        $lifetime,
                    );
                }
                else
                {
                    if($this->_compress)
                    {
                        $value[$k] = gzcompress($value[$k],9);
                    }
                    $data = array
                    (
                        md5($k),
                        $k,
                        serialize($value[$k]),
                        null,
                        $lifetime,
                    );
                }

                $this->_handler->values($data);
            }
        }
        else
        {
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
                if($this->_compress)
                {
                    $value = gzcompress($value,9);
                }
                $data = array
                (
                    md5($key),
                    $key,
                    serialize($value),
                    null,
                    $lifetime,
                );
            }

            $this->_handler->values($data);
        }

        $rs = $this->_handler->columns(array('key','key_string','value','number','expire'))->replace($this->tablename);

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
        if (IS_DEBUG)Core::debug()->info($key,'database delete key');

        if ( true!==$key )
        {
            $this->_handler->where('key',$key);
        }
        elseif (is_array($key))
        {
            $this->_handler->in('key',$key);
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
            $this->_handler->where('expire',0,'>')->where('expire',TIME,'<=')->delete($this->tablename);
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
        $k = md5($key);

        # 首先尝试递增
        $s = $this->_handler->value_increment('number',$offset)->where('key',$k)->update($this->tablename,array('value'=>''));

        if (!$s)
        {
            # 没有更新到数据，尝试插入数据
            return $this->set($key,$offset,$lifetime);
        }

        return false;
    }
}