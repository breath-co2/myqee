<?php
/**
 * 存储驱动
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Core
 * @package    Classes
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
abstract class Core_Storage_Driver
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
    protected $compress = 0;

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
     * @return boolean
     */
    abstract public function set($key, $value = null);

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