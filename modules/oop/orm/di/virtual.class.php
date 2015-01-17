<?php

/**
 * MyQEE ORM 处理虚拟类型字段DI控制器
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Module
 * @package    ORM
 * @copyright  Copyright (c) 2008-2015 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class OOP_ORM_DI_Virtual extends OOP_ORM_DI
{
    /**
     * 格式化默认的参数，会在每个类型的 `format()` 方法前执行
     */
    protected function check_config()
    {
        if (is_string($this->config))
        {
            if (false!==strpos($this->config, '[') && preg_match('#^([a-z0-9_]+)\[(.*)\]$#i', $this->config))
            {
                # 支持 test[abc][def] 格式
                $this->config = str_replace(array('][', ']', '['), array('.', '', ''), $this->config);
            }

            if (false!==strpos($this->config, '.'))
            {
                # 支持 test.abc.def 格式
                $fields = explode('.', $this->config);
                $parent = array_shift($fields);
                $this->config = array
                (
                    'is_virtual' => true,
                    'parent_offset'    => $parent,
                    'sub_offsets'      => $fields,
                );
            }
            else
            {
                throw new Exception('class: '. $this->class_name. ', key: '.$this->key .' 设置的类型不支持');
            }
        }
        elseif (is_array($this->config))
        {
            unset($this->config['field_name'], $this->config['pk']);

            $this->config['is_virtual'] = true;
            if (!$this->config['parent_offset'] || !$this->config['sub_offsets'])
            {
                throw new Exception('class: '. $this->class_name. ', key: '.$this->key .' 设置参数缺失');
            }
        }
        else
        {
            throw new Exception('class: '. $this->class_name. ', key: '.$this->key .' 不支持的类型');
        }
    }

    protected function format_config()
    {

    }

    /**
     * 构造数据
     *
     * @param OOP_ORM_Data $obj
     * @param $data
     * @param $compiled_data
     * @return bool
     * @throws Exception
     */
    public function & get_data(OOP_ORM_Data $obj, & $data, & $compiled_data, & $compiled_raw_data)
    {
        $parent_offset = $this->config['parent_offset'];
        $sub_offsets   = $this->config['sub_offsets'];

        $tmp_data =& $obj->$parent_offset;
        foreach ($sub_offsets as $key)
        {
            if (null===$tmp_data)
            {
                throw new Exception("Illegal string offset '$key' of class {$this->class_name}, key:{$this->key}");
            }

            if (is_object($tmp_data))
            {
                if (isset($tmp_data->$key))
                {
                    $tmp =& $tmp_data->$key;
                }
                else
                {
                    $tmp = null;
                }
            }
            elseif (is_array($tmp_data))
            {
                if (isset($tmp_data[$key]))
                {
                    $tmp =& $tmp_data[$key];
                }
                else
                {
                    $tmp = null;
                }
            }
            else
            {
                throw new Exception("Illegal string offset '$key' of class {$this->class_name}, key:{$this->key}");
            }

            unset($tmp_data);
            $tmp_data =& $tmp;
            unset($tmp);
        }

        # 移除指针
        unset($compiled_data[$this->key]);
        # 重新赋值
        $compiled_data[$this->key] =& $tmp_data;
        # 记录一个副本用于判断是否修改
        $compiled_raw_data[$this->key] = $tmp_data;
        # 回调
        $obj->__orm_callback('set_virtual_field_update', $parent_offset, $this->key);

        return $compiled_data[$this->key];
    }

    public function set_data(OOP_ORM_Data $obj, & $data, & $compiled_data, & $compiled_raw_data, $new_value, $has_compiled)
    {
        if ($has_compiled && $this->is_readonly())
        {
            # 只读字段
            return false;
        }

        $parent_offset = $this->config['parent_offset'];
        $sub_offsets   = $this->config['sub_offsets'];
        $last_offset   = array_pop($sub_offsets);

        $tmp_data =& $obj->$parent_offset;

        if ($last_offset)foreach ($sub_offsets as $key)
        {
            if (is_object($tmp_data))
            {
                if (!isset($tmp_data->$key))
                {
                    $tmp_data->$key = array();
                }

                $tmp =& $tmp_data->$key;
            }
            elseif (is_array($tmp_data))
            {
                if (!isset($tmp_data[$key]))
                {
                    # 如果没有对应的key，则给数组增加一个
                    $tmp_data[$key] = array();
                }
                $tmp =& $tmp_data[$key];
            }
            else
            {
                throw new Exception("Illegal string offset '$key' of class {$this->class_name}, key:{$this->key}");
            }

            unset($tmp_data);
            $tmp_data =& $tmp;
            unset($tmp);
        }

        if (isset($compiled_data[$this->key]))
        {
            unset($compiled_data[$this->key]);
        }

        if (is_object($tmp_data))
        {
            $tmp_data->$last_offset = $new_value;

            return true;
        }
        elseif (is_array($tmp_data))
        {
            $tmp_data[$last_offset] = $new_value;

            return true;
        }
        else
        {
            throw new Exception("Illegal string offset '$last_offset' of class {$this->class_name}, key:{$this->key}");
        }
    }

    public function is_set(OOP_ORM_Data $obj, & $data, & $compiled_data)
    {
        $parent_offset = $this->config['parent_offset'];
        $sub_offsets   = $this->config['sub_offsets'];
        $last_offset   = array_pop($sub_offsets);

        $tmp_data =& $obj->$parent_offset;

        if ($last_offset)foreach ($sub_offsets as $key)
        {
            if (is_object($tmp_data))
            {
                if (!isset($tmp_data->$key))
                {
                    return false;
                }

                $tmp =& $tmp_data->$key;
            }
            elseif (is_array($tmp_data))
            {
                if (!isset($tmp_data[$key]))
                {
                    return false;
                }
                $tmp =& $tmp_data[$key];
            }
            else
            {
                return false;
            }

            unset($tmp_data);
            $tmp_data =& $tmp;
            unset($tmp);
        }

        if (is_object($tmp_data))
        {
            return isset($tmp_data->$last_offset);
        }
        elseif (is_array($tmp_data))
        {
            return isset($tmp_data[$last_offset]);
        }
        else
        {
            return false;
        }
    }

    public function un_set(OOP_ORM_Data $obj, & $data, & $compiled_data)
    {
        if ($this->is_readonly())
        {
            # 只读字段
            return false;
        }

        if (isset($compiled_data[$this->key]))
        {
            unset($compiled_data[$this->key]);
        }

        $parent_offset = $this->config['parent_offset'];
        $sub_offsets   = $this->config['sub_offsets'];
        $last_offset   = array_pop($sub_offsets);

        $tmp_data =& $obj->$parent_offset;

        if ($last_offset)foreach ($sub_offsets as $key)
        {
            if (is_object($tmp_data))
            {
                if (!isset($tmp_data->$key))
                {
                    return true;
                }

                $tmp =& $tmp_data->$key;
            }
            elseif (is_array($tmp_data))
            {
                if (!isset($tmp_data[$key]))
                {
                    return true;
                }
                $tmp =& $tmp_data[$key];
            }
            else
            {
                return true;
            }

            unset($tmp_data);
            $tmp_data =& $tmp;
            unset($tmp);
        }

        if (is_object($tmp_data))
        {
            unset($tmp_data->$last_offset);
            return true;
        }
        elseif (is_array($tmp_data))
        {
            unset($tmp_data[$last_offset]);
            return true;
        }
        else
        {
            return true;
        }
    }

    /**
     * 获取虚拟字段的父字段名
     *
     * @return string
     */
    public function get_parent_offset_name()
    {
        return $this->config['parent_offset'];
    }
}