<?php

/**
 * Model基础核心类
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Core_Model
{

    /**
     * 记录错误对象
     *
     * @var Exception
     */
    protected $error;

    /**
     * 数据库配置
     *
     * @var string
     */
    protected $database = 'default';

    /**
     * 缓存配置
     *
     * @var string
     */
    protected $cache_config = 'default';

    /**
     * 用于自动缓存产生key的一些附加数据
     *
     * 可用于被get_cache_data()获取数据时产生key的依据
     *
     * @var array
     */
    protected $cache_key_data = array();

    /**
     * 最后使用的自动缓存产生keykey
     *
     * @var string
     */
    protected $cache_last_key = null;

    /**
     * 当前数据库对象
     *
     * @var Database
     */
    protected $_database_instance = null;

    /**
     * 清除缓慢模式
     *
     * @var boolean
     */
    private $_clear_cache_data_mode = false;

    public function __construct()
    {

    }

    /**
     * 数据库对象
     *
     * @return Database
     */
    protected function db()
    {
        if (null===$this->_database_instance)
        {
            $this->_database_instance = new Database($this->database);
        }

        return $this->_database_instance;
    }

    /**
     * 缓存对象
     *
     * @return Cache
     */
    protected function cache()
    {
        return Cache::instance($this->cache_config);
    }

    protected function get_cache_data()
    {
        $key = $this->_get_cache_key(new Exception(), 1);
        if (true===$this->_clear_cache_data_mode)
        {
            # 清除模式
            $this->cache()->delete($key);
            return 1;
        }
        else
        {
            return $this->cache()->get($key);
        }
    }

    protected function set_cache_data($data, $exp = 3600, $type = Cache::TYPE_MAX_AGE)
    {
        $key = $this->_get_cache_key(new Exception(), 1);
        return $this->cache()->set($key, $data ,$exp , $type);
    }

    /**
     * 清除指定方法和参数的缓存
     *
     * @param string $model_name
     * @param string $fun
     * @param mixed $arg1
     * @param mixed $arg2
     * @param mixed $arg3
     * @param mixed $arg3
     * @param mixed $arg...
     */
    public function clear_cache_data($fun,$arg1='',$arg2='')
    {
        $args = func_get_args();
        array_shift($args);
        $this->_clear_cache_data_mode = true;
        call_user_func_array( array($this,$fun) , $args);
        $this->_clear_cache_data_mode = false;
    }

    /**
     * 根据trace获取唯一key
     *
     * @param Exception $e
     * @param int $trace_offset 需要获取key的参照位置，>=0，默认0，通常都是0
     * @return string
     */
    protected function _get_cache_key(Exception $e, $trace_offset = 0)
    {
        $trace_offset = (int)$trace_offset;
        if ( !($trace_offset >= 0) )
        {
            $trace_offset = 0;
        }
        $trace = $e->getTrace();
        if ( $trace[$trace_offset]['args'] )
        {
            if ( $this->cache_key_data )
            {
                $trace[$trace_offset]['args'] = array_merge($this->cache_key_data , $trace[$trace_offset]['args'] );
            }

            foreach ( $trace[$trace_offset]['args'] as & $item )
            {
                if ( is_object($item) )
                {
                    # 如果是对象，则需要稍微处理下
                    if ( $item instanceof OOP_ORM_Data )
                    {
                        # ORM DB对象
                        $id_field = $item->id_field_name();
                        if ( $id_field && $item->$id_field )
                        {
                            # 依据唯一ID来设置数据
                            $item = '__ORM_DATA_CLASS:' . get_class($item) . '_ID:' . $item->$id_field;
                        }
                        else
                        {
                            $item = $item->get_all_field_data();
                        }
                        continue;
                    }
                }
            }
        }
        $data = array
        (
            $trace[$trace_offset]['class'],
            $trace[$trace_offset]['function'],
            $trace[$trace_offset]['type'],
            $trace[$trace_offset]['args']
        );

        $this->cache_last_key = 'Model_Cache_' . md5('autokey_' . serialize($data));

        return $this->cache_last_key;
    }

    /**
     * 设置错误
     *
     * @param string $message
     * @param int $no
     */
    protected function error($message, $no = 0)
    {
        $this->error = new Exception($message, $no);
        return $this;
    }

    /**
     * 获取错误信息
     *
     * @return Exception
     */
    public function get_error()
    {
        return $this->error;
    }
}