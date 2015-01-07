<?php

/**
 * MyQEE ORM 核心类
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Module
 * @package    ORM
 * @copyright  Copyright (c) 2008-2015 myqee.com
 * @license    http://www.myqee.com/license.html
 */
abstract class Module_OOP_ORM
{
    /**
     * ORM版本
     *
     * @var int
     */
    const VERSION = '4.0';

    /**
     * 一对一，One-to-one
     *
     * @var string
     */
    const PARAM_TYPE_O2O = 'o2o';

    /**
     * 一对多，One-to-many
     *
     * @var string
     */
    const PARAM_TYPE_O2M = 'o2m';

    /**
     * 返回类型为返回ORM对象
     *
     * 在4.0版本后将弃用，使用 PARAM_MAPPING_O2F 代替
     *
     * @deprecated
     * @var string
     */
    const PARAM_RETURN_FINDER = 'finder';

    /**
     * 返回类型为返回单条数据
     *
     * 在4.0版本后将弃用，使用 PARAM_MAPPING_O2O 代替
     *
     * @deprecated
     * @var string
     */
    const PARAM_RETURN_SINGLE = 'single';

    /**
     * 返回类型为返回一组数据
     *
     * 在4.0版本后将弃用，使用 PARAM_MAPPING_O2M 代替
     *
     * @deprecated 在4.0版本后将弃用
     * @var string
     */
    const PARAM_RETURN_GROUP = 'group';

    /**
     * 定义ORM的名称，不定义则采用系统默认规则
     *
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

    /**
     * 定义ORM返回的Index名称，不定义则采用系统默认规则
     *
     * @var string
     */
    protected $_orm_name_index;

    /**
     * 复合主键多数据分隔符
     *
     * @var string
     */
    protected $_composite_pk_value_delimiter = ',';

    protected $_auto_where = array();

    protected static $orm_name_for_class = array();

    public function __construct()
    {
        # 检查$this->orm_name变量
        if (!$this->_orm_name)
        {
            $my_name = strtolower(get_class($this));
            if (isset(OOP_ORM::$orm_name_for_class[$my_name]))
            {
                $this->_orm_name = OOP_ORM::$orm_name_for_class[$my_name];
            }
            else
            {
                $tmp_obj = $this;
                while ($tmp_obj)
                {
                    if (is_object($tmp_obj))
                    {
                        $class_name = get_class($tmp_obj);
                    }
                    else
                    {
                        $class_name = (string)$tmp_obj;
                    }

                    if (preg_match('#^(?:Library_[a-z0-9]+_[a-z0-9]+_)?ORM_([a-z0-9_]+)_Finder$#i', $class_name, $m))
                    {
                        OOP_ORM::$orm_name_for_class[$my_name] = $this->_orm_name = $m[1];
                        break;
                    }
                    else
                    {
                        $tmp_obj = get_parent_class($tmp_obj);
                    }
                }
                unset($tmp_obj);
            }
        }
    }

    /**
     * 支持序列化对象 serialize($this)
     */
    public function __sleep()
    {
        return array('_orm_name', 'database', 'tablename');
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
        elseif($type=='index' && $this->_orm_name_index)
        {
            return $this->_orm_name_index;
        }
        elseif ($this->_orm_name)
        {
            return 'ORM_'. $this->_orm_name .'_'. ucfirst($type);
        }
        else
        {
            switch ($type)
            {
                case 'data':
                    return 'oop_orm_data';
                case 'result':
                    return 'oop_orm_result';
                case 'index':
                    return 'oop_orm_index';
                case 'finder':
                    return 'oop_orm_finder_db';
                default:
                    throw new Exception('不支持的ORM类型：'. $type);
                    break;
            }
        }
    }

    /**
     * 创建一条数据
     *
     * 如果 `$data` 是数据库获取的内容，`$is_field_key` 请设置成 true
     *
     * `$is_field_key` 详细说明：
     * `$is_field_key` 是所传进来的数据的key是对象的键名还是字段的键名,true:对应字段的键名，false:对应对象的键名
     * 差别在于：
     * 由于ORM DATA的键名可以和数据库的字段名称不一样，所以在设置数据的时候需要指定是哪个数据，如果键名和数据库的字段名完全一样，这样的话则没有区别
     *
     * @param array $data 数据
     * @param boolean $is_field_key 数据的键名是否数据库字段，默认false
     * @param string $group_id 分组ID，可不传
     * @param array $delay_data_setting 延迟读取参数
     * @throws Exception
     */
    public function create(array $data = array(), $is_field_key = false, $group_id = null, array $delay_data_setting = array())
    {
        $orm_data_name = $this->get_orm_name('data');

        /**
         * @var $orm OOP_ORM_Data
         */
        $orm = OOP_ORM_Data::create_instance($orm_data_name, $data, $this, $is_field_key, $delay_data_setting);


        if ($group_id)
        {
            # 设置组ID
            $orm->__orm_callback('add_group_id', $group_id);
        }

        return $orm;
    }

    /**
     * 当前驱动
     *
     * @return Database|HttpClient
     */
    abstract public function driver();

    /**
     * 但会对象
     *
     * @return OOP_ORM_Result
     */
    abstract public function find();

    /**
     * 获取一个对象
     *
     * @return OOP_ORM_Data
     */
    public function find_single()
    {
        return $this->find()->current();
    }

    /**
     * 返回ORM数据对象
     *
     * @param $array
     * @param array $opt 参数
     * @return OOP_ORM_Result
     */
    public function create_group_data($rs, array $opt = array())
    {
        $result_name = $this->get_orm_name('result');

        if (!class_exists($result_name, false))
        {
            if (IS_DEBUG)
            {
                static $no_result = array();

                if (!isset($no_result[$result_name]))
                {
                    Core::debug()->warn($result_name .' 对象不存在，将使用默认的 OOP_ORM_Result 对象');
                    $no_result[$result_name] = true;
                }
            }

            $result_name = 'OOP_ORM_Result';
        }

        return new $result_name($rs, $this, $opt);
    }

    /**
     * 设置自动添加条件
     *
     * @param array $auto_where
     * @return $this
     */
    public function set_auto_where(array $auto_where)
    {
        $this->_auto_where = $auto_where;

        return $this;
    }

    /**
     * 清除自动添加条件
     *
     * @return $this
     */
    public function clear_auto_where()
    {
        $this->_auto_where = array();

        return $this;
    }

    /**
     * 获取当前ORM的主键
     *
     * 如果是单个组件，则返回字符串，如果是复合主键，则返回数值
     *
     * @return array
     */
    public function get_pk_name()
    {
        return OOP_ORM_DI::get_pk_name_by_class_name(strtolower($this->get_orm_name('data')));
    }

    /**
     * 根据ID获取对象
     *
     * @param $id int 对象ID
     * @param $use_master boolean 是否使用主数据
     * @return OOP_ORM_Data
     */
    public function get_by_id($id, $use_master = false)
    {
        $id_fields = $this->get_pk_name();
        if ($id_fields)
        {
            $this->driver()->where(array_combine(array_values($id_fields), is_array($id) ? $id : explode($this->_composite_pk_value_delimiter, $id)));
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
     * @return OOP_ORM_Result
     */
    public function get_by_ids(array $ids, $use_master = false)
    {
        $id_fields = $this->get_pk_name();

        if ($id_fields)
        {
            if (1 === count($id_fields))
            {
                $this->driver()->in(current($id_fields), $ids);
            }
            else
            {
                # 多字段主键查询
                # 将构造出类似这样的WHERE条件  WHERE (`id1`=1 AND `id2`=1) OR (`id1`=2 AND `id2`=2) OR (`id1`=3 AND `id2`=3)
                $id_fields = array_values($id_fields);

                foreach($ids as $id)
                {
                    $this->driver()->or_where_open()->where(array_combine($id_fields, is_array($id) ? $id : explode($this->_composite_pk_value_delimiter, $id)))->or_where_close();
                }
            }

            return $this->find(null, $use_master);
        }
        else
        {
            throw new Exception(__('Can\'t found pk.'));
        }
    }

    /**
     * 返回最后查询语句
     *
     * @return string
     */
    public function last_query()
    {
        return $this->driver()->last_query();
    }
}
