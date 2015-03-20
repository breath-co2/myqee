<?php

/**
 * 数据返回对象核心类
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   MyQEE
 * @package    Module
 * @subpackage Database
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
abstract class Module_Database_Result implements Countable, Iterator, SeekableIterator, ArrayAccess
{
    /**
     * 当前配置
     *
     * @var array
     */
    protected $_config;

    /**
     * 查询语句
     *
     * @var string
     */
    protected $_query;

    /**
     * 返回内容的指针
     *
     * @var mixed
     */
    protected $_result;

    /**
     * 返回的内容
     *
     * @var array
     */
    protected $_data = array();

    /**
     * 返回总行数
     *
     * @var int
     */
    protected $_total_rows;

    /**
     * 当前行数
     *
     * @var int
     */
    protected $_current_row = 0;

    /**
     * 内部指针所在行
     *
     * @var int
     */
    protected $_internal_row = 0;

    protected $_as_object = null;

    /**
     * 标记是否指针模式
     *
     * @var bool
     */
    protected $_cursor_mode = false;

    /**
     * 数据是否需要转换编码
     *
     * @var boolean
     */
    protected $_charset_need_change = false;

    /**
     * 指定是二进制数据的key，在自动转码时会或略相应的字段
     * @var array
     */
    protected $_charset_is_bin_field = array();

    /**
     * Sets the total number of rows and stores the result locally.
     *
     * @param mixed $result query result
     * @param string $sql SQL query
     * @param $sql
     * @param $as_object
     * @param $config
     * @return void
     */
    public function __construct($result, $sql, $as_object ,$config)
    {
        $this->_result = $result;
        $this->_query  = $sql;
        $this->_config = $config;

        if (is_object($as_object))
        {
            $as_object = get_class($as_object);
        }

        $this->_as_object = $as_object;

        if (isset($this->_config['auto_change_charset']) && $this->_config['auto_change_charset'] && $this->_config['charset'] !='UTF8')
        {
            $this->_charset_need_change = true;
        }
        else
        {
            $this->_charset_need_change = false;
        }
    }

    public function __call($m, $v)
    {
        if (method_exists($this->_result, $m))
        {
            return call_user_func_array(array($this->_result, $m), $v);
        }
        else
        {
            throw new Exception('method not found in '. get_class($this));
        }
    }

    /**
     * Result destruction cleans up all open result sets.
     *
     * @return  void
     */
    public function __destruct()
    {
        $this->release_resource();
    }

    /**
     * 释放资源
     *
     * @return mixed
     */
    abstract protected function release_resource();

    /**
     * 统计当前查询返回数据
     *
     * @return int
     */
    abstract protected function total_count();

    /**
     * 返回当前行数据
     */
    abstract protected function fetch_assoc();

    /**
     * 返回当前返回对象
     */
    public function result()
    {
        return $this->_result;
    }

    /**
     * 标记为指针移动模式
     *
     * 用于处理较大数据集返回，不至于出现内存超过限制的问题
     *
     * [!!] TODO 数据库驱动是PDO时请注意，由于PDO的限制，只能指针按顺序向下移动，否则数据返回异常
     *
     * @return $this
     */
    public function cursor_mode()
    {
        $this->_cursor_mode = true;

        return $this;
    }

    /**
     * 获取当前行数据
     *
     * @see Iterator::current()
     */
    public function current()
    {
        if ($this->_current_row !== $this->_internal_row && !$this->seek($this->_current_row))return false;

        $this->_internal_row ++;

        if ($this->_data && array_key_exists($this->_current_row, $this->_data))
        {
            return $this->_data[$this->_current_row];
        }

        $data = $this->fetch_assoc();

        if ($this->_charset_need_change)
        {
            $this->_change_data_charset($data);
        }

        if (true === $this->_as_object)
        {
            # 返回默认对象
            $data = new stdClass($data);
        }
        elseif (is_string($this->_as_object))
        {
            # 返回指定对象
            $data = new $this->_as_object($data);
        }

        if (!$this->_cursor_mode)
        {
            $this->_data[$this->_current_row] = $data;

            if ($this->count() == count($this->_data))
            {
                # 释放资源
                $this->release_resource();
            }
        }

        return $data;
    }

    /**
     * Return all of the rows in the result as an array.
     *
     * // Indexed array of all rows
     * $rows = $result->as_array();
     *
     * // Associative array of rows by "id"
     * $rows = $result->as_array('id');
     *
     * // Associative array of rows, "id" => "name"
     * $rows = $result->as_array('id', 'name');
     *
     * @param  string $key column for associative keys
     * @param  string $value column for values
     * @return array
     */
    public function as_array($key = null, $value = null)
    {
        $rs = array();

        if (null===$key && null===$value)
        {
            foreach ($this as $row)
            {
                $rs[] = $row;
            }
        }
        elseif (null===$key)
        {
            if ($this->_as_object)
            {
                foreach ($this as $row)
                {
                    $rs[] = $row->$value;
                }
            }
            else
            {
                foreach ($this as $row)
                {
                    $rs[] = $row[$value];
                }
            }
        }
        elseif (null===$value)
        {
            if ($this->_as_object)
            {
                foreach ($this as $row)
                {
                    $rs[$row->$key] = $row;
                }
            }
            else
            {
                foreach ($this as $row)
                {
                    $rs[$row[$key]] = $row;
                }
            }
        }
        else
        {
            if ($this->_as_object)
            {
                foreach ($this as $row)
                {
                    $rs[$row->$key] = $row->$value;
                }
            }
            else
            {
                foreach ($this as $row)
                {
                    $rs[$row[$key]] = $row[$value];
                }
            }
        }

        $this->rewind();

        return $rs;
    }

    public function getArrayCopy()
    {
        return $this->as_array();
    }

    /**
     * Return the named column from the current row.
     *
     * // Get the "id" value
     * $id = $result->get('id');
     *
     * @param  string $name column to get
     * @param  mixed  $default default value if the column does not exist
     * @return mixed
     */
    public function get($name, $default = null)
    {
        $row = $this->current();

        if ($this->_as_object)
        {
            if (isset($row->$name))return $row->$name;
        }
        else
        {
            if (isset($row[$name]))return $row[$name];
        }

        return $default;
    }

    /**
     * Implements [Countable::count], returns the total number of rows.
     *
     * echo count($result);
     *
     * @return  integer
     */
    public function count()
    {
        if (null===$this->_total_rows)
        {
            $this->_total_rows = $this->total_count();
        }
        return $this->_total_rows;
    }

    /**
     * Implements [ArrayAccess::offsetExists], determines if row exists.
     *
     *      if (isset($result[10]))
     *      {
     *          // Row 10 exists
     *      }
     *
     * @return  boolean
     */
    public function offsetExists($offset)
    {
        return ($offset >= 0 && $offset < $this->count());
    }

    /**
     * Implements [ArrayAccess::offsetGet], gets a given row.
     *
     *      $row = $result[10];
     *
     * @return  mixed
     */
    public function offsetGet($offset)
    {
        if ($this->_data && array_key_exists($offset, $this->_data))return $this->_data[$offset];

        if (!$this->seek($offset)) return null;

        if (!$offset != $this->_current_row)
        {
            $old_current         = $this->_current_row;
            $old_internal        = $this->_internal_row;
            $this->_current_row  = $offset;
            $this->_internal_row = $offset;
        }

        $rs = $this->current();

        if (isset($old_current) && isset($old_internal))
        {
            $this->_current_row  = $old_current;
            $this->_internal_row = $old_internal;
        }

        return $rs;
    }

    /**
     * Implements [ArrayAccess::offsetSet], throws an error.
     *
     * [!!] You cannot modify a database result.
     *
     * @return  void
     * @throws  Exception
     */
    final public function offsetSet($offset, $value)
    {
        throw new Exception('Database results are read-only');
    }

    /**
     * Implements [ArrayAccess::offsetUnset], throws an error.
     *
     * [!!] You cannot modify a database result.
     *
     * @return  void
     * @throws  Exception
     */
    final public function offsetUnset($offset)
    {
        throw new Exception('Database results are read-only');
    }

    /**
     * Implements [Iterator::key], returns the current row number.
     *
     *      echo key($result);
     *
     * @return  integer
     */
    public function key()
    {
        return $this->_current_row;
    }

    /**
     * Implements [Iterator::next], moves to the next row.
     *
     *      next($result);
     *
     * @return  $this
     */
    public function next()
    {
        ++$this->_current_row;

        return $this;
    }

    /**
     * Implements [Iterator::prev], moves to the previous row.
     *
     *      prev($result);
     *
     * @return  $this
     */
    public function prev()
    {
        --$this->_current_row;

        return $this;
    }

    /**
     * Implements [Iterator::rewind], sets the current row to zero.
     *
     *      rewind($result);
     *
     * @return  $this
     */
    public function rewind()
    {
        $this->_current_row = 0;

        return $this;
    }

    /**
     * Implements [Iterator::valid], checks if the current row exists.
     *
     * [!!] This method is only used internally.
     *
     * @return  boolean
     */
    public function valid()
    {
        return $this->offsetExists($this->_current_row);
    }

    public function fetch_array()
    {
        $data = $this->current();
        $this->next();

        return $data;
    }

    /**
     * 对数组或字符串进行编码转换
     *
     * @param array/string $data
     */
    protected function _change_data_charset(& $data)
    {
        if (is_array($data))
        {
            foreach ($data as $key=> & $item)
            {
                if ($this->_charset_is_bin_field && isset($this->_charset_is_bin_field[$key]))
                {
                    continue;
                }
                $this->_change_data_charset($item);
            }
        }
        else
        {
            if (IS_MBSTRING)
            {
                $data = mb_convert_encoding($data, 'UTF-8', $this->_config['data_charset']);
            }
            else
            {
                $data = iconv($this->_config['data_charset'], 'UTF-8//IGNORE', $data);
            }
        }
    }

    /**
     * 设置指定的key是二进制数据
     *
     * 此方法必须在as_array或current等前面执行
     * 当启用自动编码转换后，获取的数据会自动转码，通过此设置后可以避免对应的字段被转码
     *
     *     $this->is_bin('key1');
     *     $this->is_bin('key1' , 'key2');
     *
     * @param string $key
     * @return $this
     */
    public function is_bin($key)
    {
        $keys = func_get_args();
        foreach ($keys as $key)
        {
            $this->_charset_is_bin_field[$key] = true;
        }
        return $this;
    }
}