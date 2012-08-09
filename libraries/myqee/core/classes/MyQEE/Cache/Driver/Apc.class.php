<?php

/**
 * Apc缓存驱动器
 *
 * @author     jonwang(jonwang@myqee.com)
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2012 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class MyQEE_Cache_Driver_Apc
{

    public function __construct()
    {
        if ( !extension_loaded('apc') )
        {
            throw new Exception(__('php APC extension is not available.'));
        }
    }

    /**
     * 取得数据
     *
     * @param string/array $key
     * @return mixed
     */
    public function get($key)
    {
        $success = false;

        $return = apc_fetch($key, $success);

        if ( $success === false )
        {
            if (IS_DEBUG)Core::debug()->error($key,'cpc cache mis key');
            return false;
        }
        else
        {
            if ( is_array($key) )
            {
                foreach ( $return as &$item )
                {
                    Cache_Driver_Apc::_de_format_data($item);
                }
            }
            else
            {
                    Cache_Driver_Apc::_de_format_data($return);
            }

            if (IS_DEBUG)Core::debug()->info($key,'apc cache hit key=');
        }

        return $return;
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
        if (IS_DEBUG)Core::debug()->info($key,'apc cache set key');

        if ( is_array($key) )
        {
            $return = true;
            foreach ( $key as $k => &$v )
            {
                Cache_Driver_Apc::_format_data($v);
                $s = apc_store($k, $v, $lifetime);
                if ( false === $s )
                {
                    $return = false;
                }
            }

            return $return;
        }
        else
        {
            Cache_Driver_Apc::_format_data($value);
            return apc_store($key, $value, $lifetime);
        }
    }

    /**
     * 删除指定key的缓存，若$key===true则表示删除全部
     *
     * @param string $key
     */
    public function delete($key)
    {
        if (IS_DEBUG)Core::debug()->info($key,'apc cache delete key');

        if ( $key === true )
        {
            return $this->delete_all();
        }

        return apc_delete($key);
    }

    /**
     * 删除全部
     */
    public function delete_all()
    {
		return apc_clear_cache('user');
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
     * 与原始decrement方法区别的是若不存指定KEY时返回false，这个会自动递减
     *
     * @param string $key
     * @param int $offset
     * @param int $lifetime 当递减失则时当作set使用
     */
    public function decrement($key, $offset = 1, $lifetime = 60)
    {
        if ( apc_dec($key, $offset) )
        {
            return true;
        }
        elseif ( false==apc_exists($key) && $this->set($key, $offset, $lifetime) )
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
     * 与原始increment方法区别的是若不存指定KEY时返回false，这个会自动递增
     *
     * @param string $key
     * @param int $offset
     * @param int $lifetime 当递减失则时当作set使用
     */
    public function increment($key, $offset = 1, $lifetime = 60)
    {
        if ( apc_inc($key, $offset) )
        {
            return true;
        }
        elseif ( false==apc_exists($key) && $this->set($key, $offset, $lifetime) )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    protected static function _de_format_data( &$data )
    {
        if ( null===$data || is_bool($data) )
        {
            # bool类型不处理
        }
        elseif ( !is_numeric($data) )
        {
            $data = @unserialize($data);
        }
    }

    protected static function _format_data( &$data )
    {
        if ( !is_numeric($data) )
        {
            $data = serialize($data);
        }
    }
}
