<?php

/**
 * MyQEE ORM 处理默认类型字段DI控制器
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Module
 * @package    ORM
 * @copyright  Copyright (c) 2008-2015 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class OOP_ORM_DI_Default extends OOP_ORM_DI
{
    /**
     * 格式化默认的参数，会在每个类型的 `format()` 方法前执行
     */
    protected function check_config()
    {
        if (true===$this->config || 1===$this->config)
        {
            # 对于id=true||1的字段，默认为主键
            $this->config = array
            (
                'pk'          => true,
                'is_readonly' => true,
                'field_name'  => $this->key,
                'is_virtual'  => false,
            );
        }
        elseif (null===$this->config)
        {
            $this->config = array
            (
                'field_name' => $this->key,
                'is_virtual' => false,
            );
        }

        parent::check_config();
    }

    public function format_config()
    {
        if (!$this->config['is_virtual'] && !isset($this->config['field_name']))
        {
            $this->field_name = $this->config['field_name'] = $this->key;
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
    public function & get_data(OOP_ORM_Data $obj, & $data, & $compiled_data, & $compiled_raw_data)
    {
        if ($this->field_name && isset($data[$this->field_name]))
        {
            $tmp_data = $data[$this->field_name];

            # 处理格式化数据
            if (isset($this->config['format']))
            {
                OOP_ORM_DI::_de_format_data($this->config['format'], $tmp_data);
            }
            elseif (isset($this->config['is_temp_instance']) && $this->config['is_temp_instance'])
            {
                # 虚拟对象，尝试解析数据
                if (is_string($tmp_data))
                {
                    if (in_array(substr($tmp_data,0 ,1), array('{', '[')))
                    {
                        # 尝试用 json 解析
                        $tmp_data2 = @json_decode($tmp_data, true);
                        if (null!==$tmp_data2)
                        {
                            $tmp_data = $tmp_data2;
                            $this->config['format'] = 'json';       // json处理
                        }
                    }
                    elseif (in_array(substr($tmp_data, 0, 2), array('a:', 'o:', 's:', 'i:', 'f:')))
                    {
                        # 尝试用反序列化解析
                        try
                        {
                            $tmp_data2 = unserialize($tmp_data);
                            $tmp_data = $tmp_data2;
                            $this->config['format'] = 'serialize';  // 序列化处理
                        }
                        catch(Exception $e)
                        {
                            # 解析失败则忽略操作
                        }
                    }
                }
            }

            # 处理数据类型
            if (isset($this->config['field_type']))
            {
                OOP_ORM_DI::_check_field_type($this->config['field_type'], $tmp_data);
            }

            $compiled_data[$this->key]     = $tmp_data;
            $compiled_raw_data[$this->key] = $tmp_data;     // 保存一个数据备份，用于检查是否修改
        }
        else
        {
            $compiled_data[$this->key] = null;
        }

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
    public function set_data(OOP_ORM_Data $obj, & $data, & $compiled_data, & $compiled_raw_data, $new_value, $has_compiled)
    {
        if ($has_compiled)
        {
            if ($this->is_readonly())
            {
                # 只读字段
                return false;
            }
        }
        elseif ($this->field_name)
        {
            # 未构造完
            $data[$this->field_name] = $data;
        }

        # 处理数据类型
        if (isset($this->config['field_type']))
        {
            OOP_ORM_DI::_check_field_type($this->config['field_type'], $new_value);
        }

        $compiled_data[$this->key] = $new_value;

        if (!$has_compiled)
        {
            $compiled_raw_data[$this->key] = $compiled_data[$this->key];
        }

        return true;
    }
}