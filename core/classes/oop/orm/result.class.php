<?php

/**
 * MyQEE ORM 核心类
 *
 * @author     jonwang(jonwang@myqee.com)
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Core_OOP_ORM_Result extends ArrayIterator
{

    /**
     * 唯一ID
     * @var string
     */
    protected $_orm_uniqid;

    protected static $_ORM_RESULTS = array();

    public function __construct(array $array = array())
    {
        $this->_orm_uniqid = uniqid('orm_result_');
        OOP_ORM_Result::$_ORM_RESULTS[$this->_orm_uniqid] = $array;
        foreach ( $array as $item )
        {
            if ( is_object($item) && $item instanceof OOP_ORM_Data )
            {
                $item->__orm_callback_ini_result($this);
            }
        }
        parent::__construct($array);
    }

    public function __destruct()
    {
        # 销毁寄存器
        unset(OOP_ORM_Result::$_ORM_RESULTS[$this->_orm_uniqid]);
    }

    /**
     * 获取随机ID
     */
    public function get_uniqid()
    {
        return $this->_orm_uniqid;
    }

    /**
     * 合并Result结果
     * @param OOP_ORM_Result $result
     * @return OOP_ORM_Result
     */
    public function megre(OOP_ORM_Result $result)
    {
        foreach ( $result as $item )
        {
            $this->append($item);
        }
        return $this;
    }

    public function append($value)
    {
        OOP_ORM_Result::$_ORM_RESULTS[$this->_orm_uniqid][] = $value;

        $r = parent::append($value);
        return $r;
    }

    /**
     * 返回数组
     *
     * @param string $key 返回以$key为键名的数组
     * @param string $value_key 返回$value_key键名的值
     */
    public function as_array($key=null,$value_key=null)
    {
        $data = parent::getArrayCopy();
        if ( $key || $value_key )
        {
            $result = array();
            foreach ($data as $item)
            {
                if ($key)
                {
                    if ($value_key)
                    {
                        $result[$item->$key] = $item->$value_key;
                    }
                    else
                    {
                        $result[$item->$key] = $item;
                    }
                }
                else
                {
                    if ($value_key)
                    {
                        $result[] = $item->$value_key;
                    }
                    else
                    {
                        $result[] = $item;
                    }
                }
            }
            return $result;
        }
        return $data;
    }

    /**
     * 将某一对象移出此ORM，不对数据库操作
     *
     * 也可直接unset($ormTest[3])这样移除指定指针的项目
     * @param $offset
     */
    public function remove($offset)
    {
        return $this->offsetUnset($offset);
    }

    /**
     * @return OOP_ORM_Result
     */
    public function end()
    {
        end($this);
        return $this;
    }

    /**
     * @return OOP_ORM_Result
     */
    public function prev()
    {
        prev($this);
        return $this;
    }

    /**
     * 获取一组对象
     *
     * @param string $id
     * @return array
     */
    public static function get_group_data($uniqid)
    {
        return OOP_ORM_Result::$_ORM_RESULTS[$uniqid];
    }
}