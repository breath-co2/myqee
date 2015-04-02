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
abstract class Module_OOP_ORM
{

    /**
     * ORM版本
     * @var int
     */
    const VERSION = '2.3.1';

    /**
     * 返回类型为返回ORM对象
     * @var string
     */
    const PARAM_RETURN_FINDER = 'finder';

    /**
     * 返回类型为返回单条数据
     * @var string
     */
    const PARAM_RETURN_SINGLE = 'single';

    /**
     * 返回类型为返回一组数据
     * @var string
     */
    const PARAM_RETURN_GROUP = 'group';

    /**
     * 定义ORM的名称，不定义则采用系统默认规则
     * @var string
     */
    protected $_orm_name;

    /**
     * 定义ORM返回的Data名称，不定义则采用系统默认规则
     *
     * @var string
     */
    protected $_orm_name_data;

    /**
     * 定义ORM返回的Result名称，不定义则采用系统默认规则
     *
     * @var string
     */
    protected $_orm_name_result;

    protected $_auto_where = array();

    /**
     * 最后查询条件
     *
     * @var string
     */
    protected $last_query = '';

    /**
     * 当前对象ID字段
     *
     * 请使用$this->id_field_name()方法获取此值
     *
     * @var string
     */
    protected $_id_field = false;

//    protected static $ALL_ORM_DATA = array();

    public function __construct($data = null)
    {
        # 检查$this->orm_name变量
        if ( !isset($this->_orm_name) )
        {
            $tmpobj = $this;
            while ( $tmpobj )
            {
                if ( is_object($tmpobj) )
                {
                    $classname = get_class($tmpobj);
                }
                else
                {
                    $classname = (string)$tmpobj;
                }
                if ( preg_match('#^ORM_([a-z0-9_]+)_Finder$#i', $classname, $m) )
                {
                    $this->_orm_name = $m[1];
                    break;
                }
                else
                {
                    $tmpobj = get_parent_class($tmpobj);
                }
            }
            unset($tmpobj);
        }
        if ( !$this->_orm_name )
        {
            throw new Exception('请先定义$this->_orm_name变量');
        }
    }

    /**
     * 获取对象名称
     * @param string $type
     */
    public function get_orm_name($type)
    {
        $type = strtolower($type);
        if ($type=='data' && $this->_orm_name_data)
        {
            return $this->_orm_name_data;
        }
        elseif($type=='result' && $this->_orm_name_result)
        {
            return $this->_orm_name_result;
        }
        return 'ORM_' . $this->_orm_name . '_' . ucfirst($type);
    }

    /**
     * 创建一条数据
     * $is_field_key 是所传进来的数据的key是对象的键名还是字段的键名,true=字段的键名，false=对象的键名
     * 差别在于：
     * 由于ORM DATA的键名可以和数据库的字段名称不一样，所以在设置数据的时候需要指定是哪个数据，如果键名和数据库的字段名完全一样，这样的话则没有区别
     *
     * @param array $data 数据
     * @param boolean $is_field_key 数据的键名是否数据库字段，默认false
     * @throws Exception
     */
    public function create($data = null, $is_field_key = false)
    {
        $orm_data_name = $this->get_orm_name('data');
        if ( !$orm_data_name )
        {
            throw new Exception(get_class($this) . ' 没有定义data返回对象');
        }
        $orm = new $orm_data_name();
        # 用ORM调用接口设置参数
        $orm->__orm_callback_set_orm($this);
        $orm->__orm_callback_ini_data($data, $is_field_key);

        return $orm;
    }

    abstract public function drive();

    abstract public function find();

    /**
     * 返回ORM数据对象
     *
     * @param $array
     * @param boolean $is_field_key 数据的键名是否数据库字段，默认false
     * @return OOP_ORM_Result
     */
    public function create_group_data($array, $is_field_key = false)
    {
        $data = array();
        if ( is_array($array) ) foreach ( $array as $k => $v )
        {
            $data[$k] = $this->create($v, $is_field_key);
        }
        $result_name = $this->get_orm_name('result');
        if ( !class_exists($result_name, false) )
        {
            if (IS_DEBUG)
            {
                static $no_result = array();

                if ( !isset($no_result[$result_name]) )
                {
                    Core::debug()->info('指定的' . $result_name . '对象不存在。');
                    $no_result[$result_name] = true;
                }
            }

            $result_name = 'OOP_ORM_Result';
        }

        return new $result_name($data);
    }

    /**
     * 设置自动添加条件
     *
     * @param array $auto_where
     */
    public function set_auto_where(array $auto_where)
    {
        $this->_auto_where = $auto_where;
    }

    /**
     * 清除自动添加条件
     */
    public function clear_auto_where()
    {
        $this->_auto_where = array();
    }

    public function id_field_name()
    {
        if ( false===$this->_id_field )
        {
            # 获取ID字段名
            $tmpdata = $this->create();
            $this->_id_field = $tmpdata->id_field_name();
            unset($tmpdata);
        }
        return $this->_id_field;
    }

    /**
     * 根据ID获取对象
     *
     * @param $id int 对象ID
     * @param $use_master boolean 是否使用主数据
     */
    public function get_by_id($id, $use_master = false)
    {
        $idfield = $this->id_field_name();
        if ($idfield)
        {
            $this->drive()->where($idfield, $id);
            return $this->find(null, $use_master)->current();
        }
        else
        {
            throw new Exception('ORM设置不存在ID字段，无法使用此方法');
        }
    }

    /**
     * 根据IDs获取对象
     *
     * @return OOP_ORM_Result
     * @param $use_master boolean 是否使用主数据
     */
    public function get_by_ids($ids, $use_master = false)
    {
        $idfield = $this->id_field_name();

        if ($idfield)
        {
            $this->drive()->in($idfield, $ids);

            return $this->find(null, $use_master);
        }
        else
        {
            throw new Exception('ORM设置不存在ID字段，无法使用此方法');
        }
    }

    /**
     * 返回最后查询语句
     *
     * @return string
     */
    public function last_query()
    {
        return $this->last_query;
    }
}
