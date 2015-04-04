<?php

/**
 * MyQEE ORM 处理Function类型字段DI控制器
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Module
 * @package    ORM
 * @copyright  Copyright (c) 2008-2016 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class OOP_ORM_DI_Function extends OOP_ORM_DI
{
    public function format_config()
    {
        # 兼容V2
        if (isset($this->config['function']))
        {
            $this->config['func'] = $this->config['function'];
            unset($this->config['function']);
        }
        if (isset($this->config['arguments']))
        {
            $this->config['args'] = $this->config['arguments'];
            unset($this->config['arguments']);
        }

        if (!isset($this->config['func']))
        {
            throw new Exception("orm {$this->class_name}->{$this->key} func config error.");
        }
        $this->config['func'] = (array)$this->config['func'];

        if (!isset($this->config['args']))
        {
            $this->config['args'] = array();
        }
        else
        {
            $this->config['args'] = (array)$this->config['args'];
        }

        # 实时执行，true则每次都会回调function，false则只有第一次调用function方法，下次再访问则获取直接将当前值返回
        $this->config['runtime'] = isset($this->config['runtime']) ? (bool)$this->config['runtime'] : false;
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
        $config = $this->config();
        $fun    = $config['func'];

        $args   = $config['args'];
        foreach ($args as & $item)
        {
            if ($item === '$this')
            {
                $item = $obj;
            }
            elseif (is_string($item) && substr($item, 0, 7) === '$this->')
            {
                $key  = substr($item, 7);
                $item = $obj->$key;
            }
        }

        if (count($fun) === 1)
        {
            $tmp_data = call_user_func_array($fun[0], $args);
        }
        else
        {
            if ($fun[0] === '$this')
            {
                $fun[0]   = $obj;
                $tmp_data = $obj->__orm_callback('get_by_function_di', $fun, $args);
            }
            else
            {
                $tmp_data = call_user_func_array($fun, $args);
            }
        }

        if (false === $config['runtime'])
        {
            # 非实时运行
            $compiled_data[$this->key]     = $tmp_data;
            $compiled_raw_data[$this->key] = $tmp_data;
        }

        return $tmp_data;
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
        return false;
    }
}