<?php
/**
 * 缓存驱动类
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Module
 * @package    Cache
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
abstract class Module_Cache_Driver
{
    protected $config = array();

    /**
     * 前缀
     *
     * @var string
     */
    protected $prefix = '';

    /**
     * 是否压缩数据
     *
     * @var boolean
     */
    protected $compress = 1;

    public function __construct($config = null)
    {
        if ($config)
        {
            $this->config = $config;
        }
    }

    /**
     * 设置前缀
     *
     * @param string $prefix
     * @return $this
     */
    public function set_prefix($prefix)
    {
        if ($prefix)
        {
            $this->prefix = trim($prefix, ' /_') . '_';
        }
        else
        {
            $prefix = '';
        }

        return $this;
    }

    /**
     * 获取前缀
     *
     * @return string
     */
    public function get_prefix()
    {
        return $this->prefix;
    }

    /**
     * 取得数据
     *
     * @param string/array $key
     * @return mixed
     */
    abstract public function get($key);

    /**
     * 存数据
     *
     * @param string/array $key 支持多存
     * @param $data Value 多存时此项可空
     * @param $lifetime 有效期，默认3600，即1小时，0表示最大值30天（2592000）
     * @return boolean
     */
    abstract public function set($key, $value = null, $lifetime = 3600);

    /**
     * 删除指定key的缓存，若$key===true则表示删除全部
     *
     * @param string $key
     */
    abstract public function delete($key);

    /**
     * 删除全部
     *
     * @return boolean
     */
    abstract public function delete_all();

    /**
     * 过期数据会自动清除
     *
     * @return boolean
     */
    abstract public function delete_expired();

    /**
     * 递减
     *
     * @param string $key
     * @param int $offset
     * @param int $lifetime 当递减失则时当作set使用
     * @return boolean
     */
    abstract public function decrement($key, $offset = 1, $lifetime = 60);

    /**
     * 递增
     *
     * @param string $key
     * @param int $offset
     * @param int $lifetime 当递减失则时当作set使用
     * @return boolean
     */
    abstract public function increment($key, $offset = 1, $lifetime = 60);

    protected function _de_format_data(&$data)
    {
        if (null===$data || is_bool($data))
        {
            # bool类型不处理
        }
        elseif (!is_numeric($data))
        {
            # 解压
            if (substr($data,0,14)=='::gzcompress::')
            {
                $data = @gzuncompress(substr($data, 14));
            }

            # 解序列化
            if (substr($data,0,13)=='::serialize::')
            {
                $data = @unserialize(substr($data, 13));
            }
        }
    }

    protected function _format_data(&$data)
    {
        if (!is_numeric($data) && !is_string($data))
        {
            # 序列化
            $data = '::serialize::' . serialize($data);

            # 压缩
            if ($this->compress)
            {
                $data = '::gzcompress::' . @gzcompress($data,9);
            }
        }
    }
}