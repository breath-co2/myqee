<?php

/**
 * Apc缓存驱动器
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Driver
 * @package    Cache
 * @copyright  Copyright (c) 2008-2016 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Driver_Cache_Driver_Apc extends Cache_Driver
{
    public function __construct()
    {
        if (function_exists('extension_loaded') && !extension_loaded('apc'))
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
        $is_array_key = is_array($key);

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

        $success = false;

        $return = apc_fetch($key, $success);

        if (false===$success)
        {
            if (IS_DEBUG)Core::debug()->warn($key, 'apc cache mis key');
            return false;
        }
        else
        {
            if ($is_array_key)
            {
                if ($this->prefix)
                {
                    # 有前缀，移除前缀
                    $new_rs = array();
                    foreach ($return as $k=>$item)
                    {
                        $this->_de_format_data($item);
                        $new_rs[$key_map[$k]] = $item;
                    }
                    $return = $new_rs;
                }
                else
                {
                    foreach ($return as &$item)
                    {
                        $this->_de_format_data($item);
                    }
                }
            }
            else
            {
                $this->_de_format_data($return);
            }

            if (IS_DEBUG)Core::debug()->info($key, 'apc cache hit key');
        }

        return $return;
    }

    /**
     * 存数据
     *
     * @param string/array $key 支持多存
     * @param mixed $value Value 多存时此项可空
     * @param int $lifetime 有效期，默认3600，即1小时，0表示最大值30天（2592000）
     * @return boolean
     */
    public function set($key, $value = null, $lifetime = 3600)
    {
        if ($this->prefix)
        {
            if (is_array($key))
            {
                $new_key = array();
                foreach ($key as $k=>$v)
                {
                    $new_key[$this->prefix . $k] = $v;
                }
                $key = $new_key;
            }
            else
            {
                $key = $this->prefix . $key;
            }
        }

        if (IS_DEBUG)Core::debug()->info($key, 'apc cache set key');

        if (is_array($key))
        {
            $return = true;
            foreach ($key as $k => &$v)
            {
                $this->_format_data($v);
                $s = apc_store($k, $v, $lifetime);
                if (false === $s)
                {
                    $return = false;
                }
            }

            return $return;
        }
        else
        {
            $this->_format_data($value);
            return apc_store($key, $value, $lifetime);
        }
    }

    /**
     * 删除指定key的缓存，若$key===true则表示删除全部
     *
     * @param string $key
     * @return boolean
     */
    public function delete($key)
    {
        if (true===$key)
        {
            return $this->delete_all();
        }
        else
        {
            if ($this->prefix)
            {
                if (is_array($key))
                {
                    foreach ($key as &$k)
                    {
                        $k = $this->prefix . $k;
                    }
                }
                else
                {
                    $key = $this->prefix . $key;
                }
            }

            $status = apc_delete($key);
        }

        if (IS_DEBUG)Core::debug()->info($key, 'apc cache delete key');

        return $status;
    }

    /**
     * 删除全部
     *
     * @return boolean
     */
    public function delete_all()
    {
        if (IS_DEBUG)Core::debug()->info('apc cache delete all cache');

		return apc_clear_cache('user');
    }


    /**
     * 过期数据会自动清除
     *
     * @return boolean
     */
    public function delete_expired()
    {
        return true;
    }

    /**
     * 递减
     *
     * 与原始decrement方法区别的是若不存指定KEY时返回false，这个会自动递减
     *
     * @param string $key
     * @param int $offset
     * @param int $lifetime 当递减失则时当作set使用
     * @return boolean
     */
    public function decrement($key, $offset = 1, $lifetime = 60)
    {
        if ( apc_dec($this->prefix . $key, $offset) )
        {
            return true;
        }
        elseif ( false==apc_exists($this->prefix . $key) && $this->set($key, $offset, $lifetime) )
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
     *
     * 与原始increment方法区别的是若不存指定KEY时返回false，这个会自动递增
     *
     * @param string $key
     * @param int $offset
     * @param int $lifetime 当递减失则时当作set使用
     * @return boolean
     */
    public function increment($key, $offset = 1, $lifetime = 60)
    {
        if (apc_inc($this->prefix . $key, $offset))
        {
            return true;
        }
        elseif (false==apc_exists($this->prefix . $key) && $this->set($key, $offset, $lifetime))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}
