<?php

/**
 * MyQEE ORM 处理资源类型字段DI控制器
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Module
 * @package    ORM
 * @copyright  Copyright (c) 2008-2015 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class OOP_ORM_DI_Resource extends OOP_ORM_DI
{
    public function check_config()
    {
        if (is_string($this->config))
        {
            if (preg_match('#^(xml|json)://(.*)$#', $this->config, $m))
            {
                $this->config = array
                (
                    'type'     => $m[1],
                    'resource' => 'http://'. $m[2],
                );
            }
        }

        parent::check_config();
    }

    public function format_config()
    {
        if (isset($this->config['json']))
        {
            $this->config['resource'] = $this->config['json'];
            $this->config['type']     = 'json';
        }
        elseif(isset($this->config['xml']))
        {
            $this->config['resource'] = $this->config['xml'];
            $this->config['type']     = 'xml';
        }

        if ($this->config['type']=='json')
        {
            $this->config['format'][] = 'json';
        }
        elseif ($this->config['type']=='xml')
        {
            $this->config['format'][] = 'xml';
        }

        if (isset($this->config['api']))
        {
            $this->config['resource'] = $this->config['api'];
            unset($this->config['api']);
        }
    }


    /**
     * 获取当前类型的数据
     *
     * @param OOP_ORM_Data $obj
     * @param $data
     * @param $compiled_data
     * @return mixed
     */
    public function & get_data(OOP_ORM_Data $obj, & $data, & $compiled_data, & $delay_setting)
    {
        # 处理URL变量
        $url = $this->config['resource'];
        if (preg_match_all('#{{([a-z0-9_]+)}}#i', $url, $m))foreach($m[1] as $v)
        {
            $url = str_replace('{{'. $v .'}}', $obj->$v, $url);
        }

        # 获取缓存
        if (isset($this->config['cache']))
        {
            $option   = array
            (
                'url' => $url,
            );
            $cache    = OOP_ORM_DI::_get_cache_instance($this->config['cache']);
            $key      = OOP_ORM_DI::_get_cache_key($option);
            $tmp_data = $cache->get($key);
        }
        else
        {
            $tmp_data = null;
        }

        # 获取内容
        if (false!==$tmp_data)
        {
            $tmp_data = HttpClient::factory()->get($url)->data();
            if (false===$tmp_data)return false;

            # 处理数据类型
            if (isset($this->config['field_type']))
            {
                OOP_ORM_DI::_check_field_type($this->config['field_type'], $tmp_data);
            }
        }

        # 设置缓存
        if (isset($this->config['cache']))
        {
            $cache->set($key, $tmp_data, isset($this->config['cache']['expired'])?$this->config['cache']['expired']:3600, isset($this->config['cache']['expire_type'])?$this->config['cache']['expire_type']:null);
        }

        # 处理格式化数据
        if (isset($this->config['format']))
        {
            OOP_ORM_DI::_de_format_data($this->config['format'], $tmp_data);
        }

        $compiled_data[$this->key] = $tmp_data;

        return $compiled_data[$this->key];
    }


    /**
     * 设置数据
     *
     * @param OOP_ORM_Data $obj
     * @param $data
     * @param $compiled_data
     * @param $new_value
     * @param bool $has_compiled
     * @return bool
     */
    public function set_data(OOP_ORM_Data $obj, & $data, & $compiled_data, $new_value, $has_compiled = false)
    {
        return false;
    }
}