<?php

/**
 * MyQEE ORM 核心类
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Module
 * @package    ORM
 * @copyright  Copyright (c) 2008-2016 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Module_OOP_ORM_Result implements Iterator, ArrayAccess, Serializable, Countable, JsonSerializable
{
    /**
     * 当前对象唯一ID
     *
     * @var string
     */
    protected $id;

    /**
     * 当前指针位置
     *
     * @var int
     */
    protected $offset = 0;

    /**
     * 当前总数
     *
     * @var int
     */
    protected $count = 0;

    /**
     * 数据存放
     *
     * @var array
     */
    protected $data = array();

    /**
     * 资源
     *
     * @var Database_Result
     */
    protected $resource;

    /**
     * 参数
     *
     * @var array
     */
    protected $option = array();

    /**
     * 查询对象
     *
     * @var OOP_ORM
     */
    protected $finder;

    /**
     * 根据分组存放的数据
     *
     * @var array
     */
    protected static $GROUP_DATA = array();

    /**
     * 记录每个组的资源对象
     *
     * @var array
     */
    protected static $GROUP_RESOURCE = array();

    /**
     * 记录每个组的Finder
     *
     * @var array
     */
    protected static $GROUP_FINDER = array();

    /**
     * 记录每个组的设置
     *
     * @var array
     */
    protected static $GROUP_OPTION = array();

    /**
     * MyQEE ORM 字段返回对象
     *
     * @param mixed $data 数据
     * @param OOP_ORM $finder Finder对象
     */
    public function __construct($data, OOP_ORM $finder, $option = array())
    {
        if ($this->id)return;    // 禁止重复执行

        $this->id     = uniqid('orm_result_');
        $this->count  = count($data);

        if (!is_array($option))
        {
            $option = array();
        }

        if (!isset($option['count']))
        {
            $option['count'] = $this->count;
        }

        OOP_ORM_Result::$GROUP_DATA[$this->id]     = array();
        OOP_ORM_Result::$GROUP_FINDER[$this->id]   = $finder;
        OOP_ORM_Result::$GROUP_OPTION[$this->id]   = $option;
        OOP_ORM_Result::$GROUP_RESOURCE[$this->id] = null;

        if (is_array($data))
        {
            OOP_ORM_Result::$GROUP_DATA[$this->id] = $data;
        }
        elseif ($data instanceof Database_Result)
        {
            OOP_ORM_Result::$GROUP_RESOURCE[$this->id] = $data;
        }
        else
        {
            throw new Exception(__('Error type of resource.'));
        }

        # 指针引用，使用 $this->no_cached() 方法则会释放所有静态数据
        $this->data     =& OOP_ORM_Result::$GROUP_DATA[$this->id];
        $this->finder   =& OOP_ORM_Result::$GROUP_FINDER[$this->id];
        $this->option   =& OOP_ORM_Result::$GROUP_OPTION[$this->id];
        $this->resource =& OOP_ORM_Result::$GROUP_RESOURCE[$this->id];
    }

    public function __destruct()
    {
        OOP_ORM_Result::release($this->id);
    }

    /**
     * 获取当前组唯一ID
     *
     * @return string
     */
    public function id()
    {
        return $this->id;
    }

    public function offsetExists($index)
    {
        if ($this->is_get_all_resource())
        {
            return isset($this->data[$index]);
        }
        else
        {
            if (isset($this->data[$index]))return true;

            return $this->resource()->offsetExists($index);
        }
    }

    public function offsetGet($index)
    {
        if (isset($this->data[$index]))
        {
            return $this->data[$index];
        }
        elseif (!$this->resource())
        {
            return null;
        }

        return $this->get_offset_data($index);
    }

    public function offsetSet($index, $newval)
    {
        return false;
    }

    public function offsetUnset($index)
    {
        return false;
    }

    /**
     * 返回一个数组
     *
     * @return array
     */
    public function as_array()
    {
        if (!$this->is_get_all_resource())
        {

            $old_offset = $this->offset;

            # 全部遍历一次
            while($this->valid())
            {
                $this->current();
                $this->next();
            }

            $this->offset = $old_offset;


            if ($this->resource)
            {
                # 如果还存在则说明之前有跳过index的
                if ($old_offset > 0)
                {
                    $this->offset = 0;
                    for($i=0; $i<$old_offset; $i++)
                    {
                        $this->offsetGet($i);
                    }
                }

                # 释放资源
                $this->release_resource();
            }
        }

        return $this->data;
    }

    /**
     * 返回一个数组
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return $this->as_array();
    }

    /**
     * 返回当前数
     *
     * @return int
     */
    public function count()
    {
        return $this->count;
    }

    /**
     * 反序列化
     *
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $u      = unserialize($serialized);
        $finder = $u['finder'];

        $this->__construct(array(), $finder, $u['option']);
    }

    /**
     * 序列化对象
     *
     * @return string
     */
    public function serialize()
    {
        return serialize(array
        (
            'version' => '1.0',
            'finder'  => $this->finder,
            'option'  => $this->option,
        ));
    }

    /**
     * 输出调试信息
     *
     * for php5.6 var_dump debug
     *
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'data'     => $this->data,
            'resource' => $this->resource,
            'option'   => $this->option,
        ];
    }

    /**
     * 返回当前信息
     *
     * @return mixed
     */
    public function current()
    {
        return $this->offsetGet($this->offset);
    }

    /**
     * 指针移动到下一条
     */
    public function next()
    {
        $this->offset++;
    }

    /**
     * 指针移到上一条
     */
    public function prev()
    {
        if ($this->offset > 0)
        {
            $this->offset--;
        }
    }

    /**
     * 当前key
     *
     * @return int
     */
    public function key()
    {
        return $this->offset;
    }

    /**
     * 是否有效
     *
     * @return bool
     */
    public function valid()
    {
        return $this->offsetExists($this->offset);
    }

    /**
     * 重置指针
     */
    public function rewind()
    {
        $this->offset = 0;
    }

    /**
     * 获取当前的设置
     *
     * @return array
     */
    public function option()
    {
        return $this->option;
    }

    /**
     * 获取当前的Finder
     *
     * @return OOP_ORM_Finder_DB
     */
    public function finder()
    {
        return $this->finder;
    }

    /**
     * 获取当前的数据库资源
     *
     * @return Database_Result
     */
    public function resource()
    {
        return $this->resource;
    }

    /**
     * 标记为不缓存组信息
     *
     * 设置后，对应的组数据不可以通过 `OOP_ORM_Result::get_data_by_group_id();` 来获取
     *
     * @return $this
     */
    public function no_cached()
    {
        if ($this->id)
        {
            OOP_ORM_Result::release($this->id);
            $this->id = null;
        }

        return $this;
    }

    /**
     * 在使用 json_encode 这个对象时回调的方法
     *
     *      echo json_encode($this);
     *
     * @see http://cn.php.net/manual/zh/jsonserializable.jsonserialize.php
     * @return array
     */
    public function jsonSerialize()
    {
        $rs = array();
        foreach ($this as $item)
        {
            $rs[] = $item->as_array();
        }

        return $rs;
    }


    /**
     * 是否获取了所有资源
     *
     * @return bool
     */
    protected function is_get_all_resource()
    {
        if ($this->resource)
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    /**
     * 获取指定的对象，如果没有数据则尝试从资源中创建数据
     *
     * 无可用数据则返回false
     *
     * @param $group_id
     * @param $index
     * @return OOP_ORM_Data|null
     */
    protected function get_offset_data($index)
    {
        if (!isset($this->data[$index]))
        {
            if (!$this->resource || !$this->finder)
            {
                return null;
            }

            # 从数据库中获取
            $data = $this->resource->offsetGet($index);

            # 返回是null，则seek看是否对应指针数据不存在
            if (null === $data && false === $this->resource->seek($index))return null;

            # 使用获取的数据创建新对象
            $this->data[$index] = $this->finder->create($data, isset($this->option['is_field_key'])?$this->option['is_field_key']:true, $this->id);


            if ($this->option['count'] == count($this->data))
            {
                # 获取完所有数据后就可以直接释放资源，不用等到对象被消毁
                $this->release_resource();

                # 重新排序，避免排序错乱
                ksort($this->data, SORT_NUMERIC);
            }
        }

        return $this->data[$index];
    }

    /**
     * 是否资源
     *
     * @return $this
     */
    protected function release_resource()
    {
        $this->resource = null;

        if ($this->id)
        {
            OOP_ORM_Result::$GROUP_RESOURCE[$this->id] = null;
            unset(OOP_ORM_Result::$GROUP_RESOURCE[$this->id]);
        }

        return $this;
    }


    /**
     * 根据组ID获取指定的offset和数量的数据
     *
     * 返回 false 表示不存在对应的组
     *
     * @param $group_id
     * @param int $offset 起始位置
     * @param int $limit 返回数量，0表示返回全部
     * @return array 返回数组
     */
    public static function get_data_by_group_id($group_id, $offset = 0, $limit = 0)
    {
        if (!isset(OOP_ORM_Result::$GROUP_DATA[$group_id]) && !isset(OOP_ORM_Result::$GROUP_RESOURCE[$group_id]))return array();

        $max = OOP_ORM_Result::get_count_by_group_id($group_id);
        if ($limit > 0)
        {
            $max = min($max, $offset + $limit);
        }

        $rs = array();
        for($i = $offset; $i < $max; $i++)
        {
            $rs[$i] = OOP_ORM_Result::get_offset_data_by_group_id($group_id, $i);
        }

        return $rs;
    }


    /**
     * 返回指定组的数量
     *
     * @param $group_id
     * @return int
     */
    public static function get_count_by_group_id($group_id)
    {
        if (isset(OOP_ORM_Result::$GROUP_OPTION[$group_id]['count']))
        {
            return OOP_ORM_Result::$GROUP_OPTION[$group_id]['count'];
        }
        elseif (isset(OOP_ORM_Result::$GROUP_RESOURCE[$group_id]) && OOP_ORM_Result::$GROUP_RESOURCE[$group_id])
        {
            return OOP_ORM_Result::$GROUP_RESOURCE[$group_id]->count();
        }
        elseif (isset(OOP_ORM_Result::$GROUP_DATA[$group_id]))
        {
            return count(OOP_ORM_Result::$GROUP_DATA[$group_id]);
        }
        else
        {
            return 0;
        }
    }

    public static function append_data($group_id, $data)
    {
        OOP_ORM_Result::$GROUP_DATA[$group_id][] = $data;

        if (is_object($data) && $data instanceof OOP_ORM_Data)
        {
            $data->__orm_callback('add_group_id', $group_id);
        }
    }

    public static function remove_item($group_id, $obj)
    {
        if (!isset(OOP_ORM_Result::$GROUP_DATA[$group_id]))
        {
            return;
        }

        # 移除指定对象
        $key = array_search($obj, OOP_ORM_Result::$GROUP_DATA[$group_id], true);
        if (false !== $key)
        {
            unset(OOP_ORM_Result::$GROUP_DATA[$group_id][$key]);

            if (is_object($obj) && $obj instanceof OOP_ORM_Data)
            {
                $obj->__orm_callback('remove_group_id', $group_id);
            }
        }

        if (!OOP_ORM_Result::$GROUP_DATA[$group_id])
        {
            unset(OOP_ORM_Result::$GROUP_DATA[$group_id]);
        }
    }

    /**
     * 释放资源
     *
     * @param $group_id
     */
    public static function release($group_id)
    {
        if (OOP_ORM_Result::$GROUP_DATA[$group_id])
        {
            foreach(OOP_ORM_Result::$GROUP_DATA[$group_id] as $item)
            {
                if (is_object($item) && $item instanceof OOP_ORM_Data)
                {
                    $item->__orm_callback('remove_group_id', $group_id);
                }
            }
            unset($item);
        }

        # 销毁分组寄存器
        unset(
            OOP_ORM_Result::$GROUP_DATA[$group_id],
            OOP_ORM_Result::$GROUP_RESOURCE[$group_id],
            OOP_ORM_Result::$GROUP_FINDER[$group_id],
            OOP_ORM_Result::$GROUP_OPTION[$group_id]
        );
    }

    /**
     * 获取指定的对象，如果没有数据则尝试从资源中创建数据
     *
     * 无可用数据则返回false
     *
     * @param $group_id
     * @param $index
     * @return OOP_ORM_Data|null
     */
    protected static function get_offset_data_by_group_id($group_id, $index)
    {
        if (!isset(OOP_ORM_Result::$GROUP_DATA[$group_id][$index]))
        {
            /**
             * @var $resource Database_Result
             * @var $finder OOP_ORM_Finder_DB
             * @ver $option array
             */
            $resource = isset(OOP_ORM_Result::$GROUP_RESOURCE[$group_id]) ? OOP_ORM_Result::$GROUP_RESOURCE[$group_id] : null;
            $finder   = isset(OOP_ORM_Result::$GROUP_FINDER[$group_id]) ? OOP_ORM_Result::$GROUP_FINDER[$group_id] : null;
            $option   = isset(OOP_ORM_Result::$GROUP_OPTION[$group_id]) ? OOP_ORM_Result::$GROUP_OPTION[$group_id] : array();

            if (!$resource || !$finder)
            {
                return null;
            }

            # 从数据库中获取
            $data = $resource->offsetGet($index);

            # 返回是null，则seek看是否对应指针数据不存在
            if (null === $data && false === $resource->seek($index))return null;

            # 使用获取的数据创建新对象
            OOP_ORM_Result::$GROUP_DATA[$group_id][$index] = $finder->create($data, isset($option['is_field_key'])?$option['is_field_key']:true, $group_id);

            if (count(OOP_ORM_Result::$GROUP_DATA[$group_id]) == $option['count'])
            {
                # 获取完所有数据后就可以直接释放资源，不用等到对象被消毁
                OOP_ORM_Result::$GROUP_RESOURCE[$group_id] = null;
                unset(OOP_ORM_Result::$GROUP_RESOURCE[$group_id]);

                # 重新排序，避免排序错乱
                asort(OOP_ORM_Result::$GROUP_DATA, SORT_NUMERIC);
            }
        }

        return OOP_ORM_Result::$GROUP_DATA[$group_id][$index];
    }
}