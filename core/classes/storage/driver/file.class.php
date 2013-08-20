<?php
/**
 * 文件处理驱动
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Core
 * @package    Classes
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Core_Storage_Driver_File extends Storage_Driver
{
    /**
     * storage目录
     *
     * @var string
     */
    protected $dir;

    /**
     * 存储
     *
     * @var string
     */
    protected $storage = 'default';

    public function __construct($config_name = null)
    {
        if (is_array($config_name))
        {
            $config = $config_name;
        }
        else
        {
            $config = (array)Core::config('storage/file.'.$config_name);
        }

        if ($config['storage'])
        {
            $this->storage  = $config['storage'];
        }

        $this->dir = DIR_DATA . 'storage' . DS;
    }

    /**
     * 取得数据，支持批量取
     *
     * @param string/array $key
     * @return mixed
     */
    public function get($key)
    {
        if (is_array($key))
        {
            # 支持多取
            $data = array();
            foreach ($key as $k=>$v)
            {
                $data[$k] = $this->get((string)$v);
            }
            return $data;
        }

        $filename = $this->get_filename_by_key($key);

        if (file_exists($filename))
        {
            $data = @file_get_contents($filename);

            $this->_de_format_data($data);
            return $data;
        }
        else
        {
            return null;
        }
    }

    /**
     * 存数据
     *
     * @param string/array $key 支持多存
     * @param $data Value 多存时此项可空
     * @param $lifetime 有效期，默认3600，即1小时，0表示最大值30天（2592000）
     * @return boolean
     */
    public function set($key, $value = null)
    {
        if (is_array($key))
        {
            # 支持多存
            $i=0;
            foreach ($key as $k=>$v)
            {
                if ($this->set((string)$k, $v))
                {
                    $i++;
                }
            }

            return $i==count($key)?true:false;
        }

        $filename = $this->get_filename_by_key($key);

        $this->_format_data($value);

        return File::create_file($filename, $value, null, null, $this->storage);
    }

    /**
     * 删除指定key的缓存，若$key===true则表示删除全部
     *
     * @param string $key
     */
    public function delete($key)
    {
        if (true===$key)
        {
            # 删除全部
            return File::remove_dir($this->dir . $this->prefix, $this->storage);
        }

        if (is_array($key))
        {
            # 支持多取
            $data = array();
            $i=0;
            foreach ($key as $k=>$v)
            {
                if ($this->delete((string)$v))
                {
                    $i++;
                }
            }
            return $i==count($key)?true:false;
        }

        $filename = $this->get_filename_by_key($key);

        if (!file_exists($filename))
        {
            return true;
        }

        return File::unlink($filename, $this->storage);
    }

    /**
     * 删除全部
     */
    public function delete_all()
    {
        return $this->delete(true);
    }

    /**
     * 根据KEY获取文件路径
     *
     * @param string $key
     */
    protected function get_filename_by_key($key)
    {
        return $this->dir . $this->prefix . 'storage_' . substr(preg_replace('#[^a-z0-9_\-]*#i','',$key), 0, 50) . '_' . md5($key);
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
            $this->prefix = trim($prefix, ' /_') . '/';
        }
        else
        {
            $prefix = '';
        }

        return $this;
    }
}