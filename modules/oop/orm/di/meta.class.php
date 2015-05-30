<?php

/**
 * MyQEE ORM 处理默认类型字段DI控制器
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Module
 * @package    ORM
 * @copyright  Copyright (c) 2008-2016 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class OOP_ORM_DI_Meta extends OOP_ORM_DI
{
    /**
     * 数据所属组
     *
     * @var string
     */
    protected $meta_group = '';

    /**
     * 格式化默认的参数，会在每个类型的 `format()` 方法前执行
     */
    protected function check_config()
    {
        if (!isset($this->table_name) || !$this->table_name)
        {
            throw new InvalidArgumentException('orm '. $this->class_name .'::$'. $this->key .' config error, need table_name key.');
        }

        if (!isset($this->config['field_name']) || !$this->config['field_name'])
        {
            $this->config['field_name'] = $this->key;
        }

        parent::check_config();
    }

    public function format_config()
    {
        if (!array_key_exists('depth', $this->config))
        {
            $this->config['depth'] = 0;
        }
        else
        {
            $this->config['depth'] = (int)$this->config['depth'];
        }

        if (!array_key_exists('meta_group', $this->config))
        {
            $this->config['meta_group'] = '';
        }

        $this->meta_group = $this->config['meta_group'];
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
        $pk = $obj->pk(',');
        if (!$pk)
        {
            # 没有id是新创建的对象？不用去数据库中读取

            if ($this->config['depth'])
            {
                $tmp_data = array();
            }
            else
            {
                $tmp_data = null;
            }
        }
        else
        {
            $metadata =& $obj->__orm_callback('get_metadata', $this->table_name);
            $group    = $this->meta_group;
            $table    = $this->table_name;

            if (!isset($metadata[$table]) || !array_key_exists($group, $metadata[$table]))
            {
                # 加载数据
                $metadata[$table][$group] = $obj->finder()->load_metadata($obj, $table, $this->meta_group);
            }

            $tmp_data = OOP_ORM_DI_Meta::get_metadata_by_group_data($this->config, $metadata[$table][$group]);
        }

        $compiled_data[$this->key]     = $tmp_data;
        $compiled_raw_data[$this->key] = $tmp_data;

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
//            $data[$this->field_name] = $data;
        }

        if (!array_key_exists($this->key, $compiled_data) && $obj->pk())
        {
            # 没有构造过数据直接set，则先构造下
            $this->get_data($obj, $data, $compiled_data, $compiled_raw_data);
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

    /**
     * 是否元数据拟字段
     *
     * @return bool
     */
    public function is_metadata()
    {
        return true;
    }

    protected function get_metadata_by_group_data($config, $metadata)
    {
        if ($config['depth'] > 0)
        {
            $tmp_data = array();
            foreach ($metadata as $item)
            {
                if ($item['field_name'] !== $config['field_name'])continue;

                if (strpos($item['meta_index'], '.') !== false)
                {
                    // 构造一个多维数组
                    $tmp =& $tmp_data;
                    foreach(explode('.', $item['meta_index']) as $key)
                    {
                        $tmp =& self::_create_metadata_tree($key, $tmp);
                    }

                    $tmp = $item['meta_value'];

                    if (isset($config['format']) && $config['format'])
                    {
                        OOP_ORM_DI::_do_de_format_data($config['format'], $tmp);
                    }

                    unset($tmp);
                }
                else
                {
                    if (isset($config['format']) && $config['format'])
                    {
                        OOP_ORM_DI::_do_de_format_data($config['format'], $item['meta_value']);
                    }

                    $tmp_data[$item['meta_index']] = $item['meta_value'];
                }
            }

            return $tmp_data;
        }
        else
        {
            foreach ($metadata as $item)
            {
                if ($item['field_name'] === $config['field_name'])
                {
                    if (isset($config['format']) && $config['format'])
                    {
                        OOP_ORM_DI::_do_de_format_data($config['format'], $item['meta_value']);
                    }
                    return $item['meta_value'];
                }
            }

            return null;
        }
    }

    protected static function & _create_metadata_tree($key, & $metadata)
    {
        if (!is_array($metadata[$key]) || !array_key_exists($key, $metadata))$metadata[$key] = array();

        return $metadata[$key];
    }

    protected function format_field_value(OOP_ORM_Data $obj, & $data, $new_data, $format_type)
    {
        # 数据主键
        $id = $obj->pk(',');

        # 这个是对应主表的表名称，它和元数据的 table_name 字段是对应的
        $table = $obj->finder()->tablename();

        # 这个是metadata所在的表名称
        $my_table = $this->table_name;

        $meta_group = $this->meta_group;

        if (null === $new_data || '' === $new_data || array() === $new_data)
        {
            # 删除了

            # 读取已经加载的所有元数据
            $old_metadata = $obj->__orm_callback('get_metadata');

            if (isset($old_metadata[$my_table][$meta_group]))foreach ($old_metadata[$my_table][$meta_group] as $hash => $item)
            {
                # 遍历所有当前组的数据

                if ($item['table_name'] === $table && $item['field_name'] === $this->field_name)
                {
                    # 把当前字段的数据全部设置成 null 以便进行删除处理
                    $data[$my_table][$hash] = null;
                }
            }

            return;
        }

        if ($this->config['depth'])
        {
            # 处理多行数据

            # 读取已经加载的所有元数据
            $old_metadata = $obj->__orm_callback('get_metadata');

            $old_item_data = array();
            if (isset($old_metadata[$my_table][$meta_group]))foreach ($old_metadata[$my_table][$meta_group] as $hash => $item)
            {
                # 把数据整理到一个数组里
                if ($item['table_name'] === $table && $item['field_name'] === $this->field_name)
                {
                    # 把当前字段的数据全部设置成 null 以便进行删除处理
                    $old_item_data[$hash] = $item;
                }
            }

            foreach ($new_data as $k => $v)
            {
                $tmp = $this->get_meta_item($table, $id, $k, $v);

                # 将临时表中同一个hash的删除
                unset($old_item_data[$tmp['hash']]);

                if (array_key_exists($tmp['hash'], $old_item_data) && $tmp['meta_value'] == $old_item_data[$tmp['hash']]['meta_value'])
                {
                    # 相同的数据，则不处理
                    continue;
                }

                if ($tmp['meta_value'] === '')
                {
                    # 新设置的数据是空，则不处理
                    continue;
                }

                $data[$my_table][$tmp['hash']] = $tmp;
            }

            if ($old_item_data)foreach($old_item_data as $hash => $tmp)
            {
                # 把所有剩余的设置成 null 以便进行删除处理
                $data[$my_table][$tmp['hash']] = null;
            }
        }
        else
        {
            if ($format_type)
            {
                # 动态格式化
                if (is_array($new_data) || is_object($new_data))
                {
                    $new_data = serialize($new_data);
                }
            }
            elseif (isset($this->config['format']) && $this->config['format'])
            {
                # 格式化数据
                $this->_format_data($new_data);
            }

            $new_data = $this->get_meta_item($table, $id, 0, $new_data);

            $data[$my_table][$new_data['hash']] = $new_data;
        }
    }

    protected function get_meta_item($table, $id, $meta_index, $item)
    {
        $item = (string)$item;

        return array
        (
            'hash'       => $table .'_'. $id .'_'. $this->field_name .'_'. $meta_index,
            'table_name' => $table,
            'field_name' => $this->field_name,
            'theid'      => $id,
            'meta_index' => $meta_index,
            'meta_group' => $this->meta_group,
            'meta_time'  => time(),
            'meta_value' => $item,
        );
    }

    /**
     * 处理格式化数据
     *
     * 支持多维度数据格式化
     *
     * @param $data
     */
    protected function _format_data(& $data)
    {
        if ($this->config['depth'] > 0)
        {
            // 支持多纬度数据格式化
            $tmp   = $data;
            $depth = $this->config['depth'];

            for ($i = 1; $i <= $depth; $i++)
            {
                $tmp2 = array();

                if (!is_array($tmp))
                {
                    # 不是数组的话,需要出现里下,否则下面foreach出错
                    $this->_check_depth_data($tmp);
                }

                foreach ($tmp as $key => & $item)
                {
                    if ($i === $depth)
                    {
                        # 一个个的格式化
                        OOP_ORM_DI::_do_format_data($this->config['format'], $item);
                    }
                    else
                    {
                        # 还没有到最终节点(比如 depth = 3)
                        if (!is_array($item))
                        {
                            $item = (array)$item;
                        }

                        foreach ($item as $key2 => &$item2)
                        {
                            $tmp2[$key.'.'.$key2] =& $item2;
                        }
                    }
                }

                if ($i !== $depth)
                {
                    unset($tmp);
                    $tmp =& $tmp2;
                    unset($tmp2);
                }
            }

            $data = $tmp;
        }
        else
        {
            OOP_ORM_DI::_do_format_data($this->config['format'], $data);
        }
    }

    /**
     * 处理反序列化数据
     *
     * @param $data
     */
    protected function _de_format_data(& $data)
    {

    }

    /**
     * 检查多维度数据是否可以设置
     *
     *   将会把 $new_value 处理成一个可以用于 foreach 的数组或对象
     *
     * @param $new_value
     * @return bool
     */
    protected function _check_depth_data(& $new_value)
    {
        if (is_object($new_value))
        {
            if ($new_value instanceof Traversable || $new_value instanceof ArrayObject || $new_value instanceof ArrayIterator)
            {
                # 可以进行foreach,不需要处理
                return true;
            }
            elseif ($new_value instanceof stdClass)
            {
                $new_value = get_object_vars($new_value);
                return true;
            }
            elseif (method_exists($new_value, 'getArrayCopy') || method_exists($new_value, 'as_array'))
            {
                # 可以进行foreach,不需要处理
                return true;
            }
            elseif (isset($this->config['callback']['get_data']) && ($method = $this->config['callback']['get_data']) && method_exists($new_value, $method))
            {
                $new_value = (array)$new_value->$method();
                return true;
            }
            else
            {
                # 未知对象
                $new_value = get_object_vars($new_value);
                return true;
            }
        }
        elseif (is_bool($new_value) || null === $new_value)
        {
            $new_value = array();
        }
        else
        {
            $new_value = (array)$new_value;
        }

        return true;
    }
}