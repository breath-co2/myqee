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
class Module_OOP_ORM_Data
{
    /**
     * 自定义ORM对象基础名称
     *
     * @var string
     */
    protected $_orm_name;

    /**
     * 当前类的名称(全小写格式)
     *
     * @var string
     */
    protected $_class_name;

    /**
     * 定义自动展开的key
     *
     * 有一种场景非常适合使用此参数：
     * 当一个表的内容基本上是序列化存在一个字段里时，可以指定这个字段名称，这样当程序设置某些未定义的参数时都将存放到这个字段的key里
     *
     * @var string
     */
    protected $_expand_key;

    /**
     * 数据库数据
     *
     * @var array
     */
    protected $_data = array();

    /**
     * 记录解析过的数据
     *
     * @var array
     */
    protected $_compiled_data = array();

    /**
     * 是否已创建完成
     *
     * 当$_orm_data_is_created=true时，修改对象值将被置于已修改对象
     * @var boolean
     */
    protected $_orm_data_is_created = false;

    /**
     * 当前对象是否存入在寄存器中
     *
     * @var bool
     */
    protected $_is_register_instance = false;

    /**
     * 当字段更新时更新虚拟字段对应
     *
     * @var array
     */
    protected $_update_virtual_field = array();

    /**
     * 记录已经被unset掉的key
     *
     * @var array
     */
    protected $_unset_keys = array();

    /**
     * 标记对象是否临时化对象
     *
     * @var bool
     */
    protected $_is_temp_instance = false;

    /**
     * 记录处理字段的DI控制器
     *
     * @var array
     */
    protected $_temp_offset_di = array();

    /**
     * 记录当前对象所在组ID
     *
     * @var array
     */
    protected $_group_ids = array();

    /**
     * 记录当前对象父类所在组ID
     *
     * @var array
     */
    protected $_parent_group_ids = array();

    /**
     * 记录延迟获取数据设置
     *
     * @var array
     */
    protected $_delay_setting = null;

    /**
     * 记录字段配置处理DI控制器
     *
     * @var array
     */
    protected static $OFFSET_DI = array();

    /**
     * 对象主键
     *
     * @var array
     */
    protected static $CLASS_PK = array();

    /**
     * 根据ID实例化的对象
     *
     * @var array
     */
    protected static $INSTANCE_BY_PK = array();

    /**
     * 所有实例化对象的数量
     *
     * @var array
     */
    protected static $ALL_INSTANCE_COUNT = 0;

    /**
     * 释放后新增加实例化对象的数量
     *
     * @var array
     */
    protected static $NEW_INSTANCE_COUNT = 0;

    /**
     * 新增实例化对象超过这个数量则释放对象数
     *
     * @var int
     */
    protected static $RELEASE_INSTANCE_COUNT = 100;

    /**
     * 所有已释放对象数
     *
     * @var int
     */
    protected static $RELEASED_COUNT = 0;


    /**
     * ORM数据构造
     *
     * @param array $array 构造时设置数据，通过此设置的数据被认为是以数据库字段field为键的数组
     */
    public function __construct($array = null)
    {
        # 更新配置
        $this->_init();

        # 如果有数据，则设置数据
        if ($array && is_array($array))$this->__orm_callback_ini_data($array, true);

        # 标志ORM为已构造完成
        $this->_orm_data_is_created = true;
    }

    function __destruct()
    {
        if ($this->_is_register_instance)
        {
            # 释放数增加
            OOP_ORM_Data::$RELEASED_COUNT++;
        }

        if ($this->_group_ids && $this->_compiled_data)foreach ($this->_group_ids as $group_id => $b)
        {
            # 清理干净
            $this->__orm_callback_remove_group_id($group_id);
        }
    }

    /**
     * 构造对象时清除字段
     *
     * 销毁变量以便可以统一采用__get()方法获取
     */
    protected function _init()
    {
        # 对象名称
        $this->_class_name = strtolower(get_class($this));

        if (substr($this->_class_name, -12)=='oop_orm_data')
        {
            # 临时对象
            $this->_is_temp_instance = true;
        }

        # 获取当前对象所有变量
        $class_vars = OOP_ORM::get_object_vars($this);

        if ($class_vars)foreach($class_vars as $key => $value)
        {
            unset($this->$key);
        }

        if (!isset(OOP_ORM_Data::$OFFSET_DI[$this->_class_name]))
        {
            # 更新字段配置
            OOP_ORM_Data::parse_field_by_class_name($this->_class_name, $class_vars, $this->_expand_key);
        }
    }

    /**
     * 销毁指定key的值
     *
     * @param string $key
     */
    public function __unset($key)
    {
        $rs = $this->_get_offset_di($key)->un_set($this, $this->_data, $this->_compiled_data);

        if ($rs)
        {
            $this->_unset_keys[$key] = 1;
        }

        return $rs;
    }

    public function __isset($key)
    {
        if (isset($this->_unset_keys[$key]))return false;

        return $this->_get_offset_di($key)->is_set($this, $this->_data, $this->_compiled_data);

        /*
        if (isset($this->_data[$key]))return true;
        if (isset($this->_unset_key[$key]))return false;

        $field_config = $this->get_offset_config($key);
        if (isset($field_config['parent_field']))
        {
            # 判断映射字段
            $tmp    = null;
            $parent = $this;
            $count  = count($field_config['parent_field']);
            $i      = 0;
            foreach($field_config['parent_field'] as $item)
            {
                $i++;
                unset($tmp);
                if (is_array($parent))
                {
                    if (!isset($parent[$item]))
                    {
                        return false;
                    }
                    elseif ($i==$count)
                    {
                        return true;
                    }
                    else
                    {
                        $tmp = $parent[$item];
                    }
                }
                elseif (is_object($parent))
                {
                    if (!isset($parent->$item))
                    {
                        return false;
                    }
                    elseif ($i==$count)
                    {
                        return true;
                    }
                    else
                    {
                        $tmp = $parent->$item;
                    }
                }
                else
                {
                    return false;
                }
                unset($parent);
                $parent = $tmp;
            }

            return false;
        }
        else
        {
            return false;
        }

        */
    }

    public function & __get($key)
    {
        if (isset($this->_compiled_data[$key]))return $this->_compiled_data[$key];

        # 已经被unset
        if (isset($this->_unset_keys[$key]))return null;

//        if ($key=='blog_id'||$key=='hits')return $this->_get_field_parse_obj($key);
        return $this->_get_offset_di($key)->get_data($this, $this->_data, $this->_compiled_data, $this->_delay_setting);

        /*

        if (isset($this->_compiled_data[$key]))return $this->_compiled_data[$key];

        $config = $this->get_offset_config($key);
        if (!$config)
        {
            # 没有配置过的key
            if (isset($this->_no_config_data[$key]))
            {
                return $this->_no_config_data[$key];
            }
            else
            {
                return null;
            }
        }

        if (isset($config['parent_field']))
        {
            # 映射字段
            $tmp    = null;
            $parent =& $this;
            foreach($config['parent_field'] as $item)
            {
                unset($tmp);
                if (is_array($parent))
                {
                    if (!isset($parent[$item]))
                    {
                        $parent[$item] = null;
                    }
                    $tmp =& $parent[$item];
                }
                elseif (is_object($parent))
                {
                    $tmp =& $parent->$item;
                }
                else
                {
                    $tmp = null;
                    break;
                }
                unset($parent);
                $parent =& $tmp;
            }

            return $tmp;
        }
        else
        {
            if (isset($config['cache']))
            {
                # 从缓存中获取
                $data = OOP_ORM_Parse::get_offset_cache_data($this, $key, $config['cache']);
                if (false!==$data)
                {
                    $this->_compiled_data[$key] = $data;
                    return $this->_compiled_data[$key];
                }
            }


            if (isset($config['orm']))
            {
                # 获取ORM对象
                $data = OOP_ORM_Parse::get_orm_data_by_config($this, $config['orm'], $key);
            }
            elseif (isset($config['data']))
            {
                # 处理mapping
                if (isset($config['data']['mapping']))
                {
                    foreach ($config['data']['mapping'] as $k => $v)
                    {
                        $config['data']['where'][$v] = $this->$k;
                    }
                }

                $data = false;
                # 处理缓存
                if (isset($config['data']['cache']))
                {
                    $data = OOP_ORM_Parse::get_cache_data($key, @$config['data']['where'], $config['data']['cache']);
                }
                if (false===$data)
                {
                    # 获取数据
                    $data = OOP_ORM_Parse::get_data($config['data'], $this);

                    # 缓存数据
                    if (isset($config['data']['cache']))
                    {
                        OOP_ORM_Parse::set_cache_data($key, @$config['data']['where'], $config['data']['cache'], $data);
                    }
                }
            }
            else
            {
                $data = false;
            }


            // 需要返回OBJECT对象
            if (isset($config['object']))
            {
                $object_name = $config['object']['name'];
                if (!class_exists($object_name, true))
                {
                    throw new Exception('指定的对象:'. $object_name .'不存在！');
                }

                if (isset($config['object']['callback']['set_data']))
                {
                    $obj = new $object_name();
                    $call_set_data_function = $config['object']['callback']['set_data'];
                    $obj->$call_set_data_function($data);
                }
                else
                {
                    $obj = new $object_name($data);
                }
                unset($data); //释放引用关系
                $data = $obj;
            }

            if (!$offset_isset || (isset($offset_data) && $data!==$offset_data))
            {
                $this->_offset_data[$key] = $data;
            }

            # 设置缓存
            if (false!==$data && isset($config['cache']))
            {
                OOP_ORM_Parse::set_offset_cache_data($this, $key, $config['cache'], $data);
            }

            return true;
        }

        */
    }

    public function __set($key, $value)
    {
        $rs = $this->_get_offset_di($key)->set_data($this, $this->_data, $this->_compiled_data, $value, true);

        if ($rs)
        {
            if (isset($this->_unset_keys[$key]))
            {
                unset($this->_unset_keys[$key]);
            }

            if (isset($this->_update_virtual_field[$key]))
            {
                # 当更新字段后，如果有虚拟字段则移除虚拟字段的compiled数据，以便再次获取时可以更新数据
                foreach($this->_update_virtual_field[$key] as $item=>$value)
                {
                    unset($this->_compiled_data[$item]);
                }

                unset($this->_update_virtual_field[$key]);
            }
        }

        return $rs;
    }

    /**
     * 支持序列化对象 serialize($this)
     */
    public function __sleep()
    {
        $this->__orm_sleep_data__ = array
        (
            'v' => '2.0',
            'd' => $this->get_field_data(),
        );

        $r = array('__orm_sleep_data__');

        # 获取外部设置的key，比如:$obj->test
        $r2 = array_keys(OOP_ORM_Parse::get_object_vars($this));

        # 合并数组并移除重复值
        return array_unique(array_merge($r, $r2));
    }

    /**
     * 反序列化 unserialize()
     */
    public function __wakeup()
    {
        if (isset($this->__orm_sleep_data__['v']))
        {
            switch ($this->__orm_sleep_data__['v'])
            {
                case '1.0':
                    # 兼容老版本数据
                    $this->__construct($this->__orm_sleep_data__['d']);

                    # 清除
                    unset($this->__orm_sleep_data__);
                    break;
                case '2.0':

                    break;
                default:
                    $this->__construct();
                    break;
            }
        }
        else
        {
            $this->__construct();
        }
    }

    /**
     * 当var_dump这个对象时返回的内容，PHP 5.6 起支持
     *
     * @link http://php.net/manual/zh/language.oop5.magic.php#object.debuginfo
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'data'          => $this->_data,
            'compiled_data' => $this->_compiled_data,
        ];
    }

    /**
     * 更新数据
     *
     * @return int $status 作用的行数
     */
    public function update()
    {
        $changed_data = $this->get_changed_data();
    }

    /**
     * 插入数据
     *
     * @return array array(插入ID,作用行数)
     */
    public function insert()
    {

    }

    /**
     * 删除对象数据
     *
     * @return integer 操作行数
     * @throws Exception
     */
    public function delete()
    {

    }

    /**
     * 获取修改的数据
     *
     * !!! 注意，返回的数组的键名是字段的键名，而并不是对象的键名
     * 如果有多表，其它表的字段则会存在 _other_table_field key中
     *
     * @return array
     */
    public function get_changed_data()
    {
        $data = array();

        foreach($this->_compiled_data as $key=>$value)
        {
            /**
             * @var $value OOP_ORM_Field
             */
            if ($value->is_change())
            {
                $parse_obj         = $this->_get_offset_di($key);
                $field_name        = $parse_obj->get_field_name();
                if ($field_name)
                {
                    $data[$field_name] = $value->get_data();
                }
                else
                {
                    # 虚拟字段
                }
            }
        }

        return $data;
    }

    /**
     * 是否修改过数据
     *
     * @var bool
     */
    public function is_change()
    {
        foreach($this->_compiled_data as $value)
        {

        }

        return false;
    }

    /**
     * 获取当前字段配置
     *
     * 如果 `$key = null` 则返回全部配置
     *
     * @param null $key
     * @return array|null
     */
    public function get_offset_config($key = null)
    {
        if (null===$key)
        {
            return OOP_ORM_Parse::$CONFIG[$this->_class_name];
        }
        else
        {
            if (!isset(OOP_ORM_Parse::$CONFIG[$this->_class_name]))
            {
                return null;
            }
            else
            {
                return OOP_ORM_Parse::$CONFIG[$this->_class_name][$key];
            }
        }
    }

    /**
     * 返回当前对象主键值
     *
     * 如果存在多个主键，默认用,分开
     *
     * @param string $glue 多个字段主键之间分隔符，false则不合并，返回数组
     * @return string|array|false|null
     */
    public function pk($glue = ',')
    {
        if ($pk = $this->pk_key_name())
        {
            $key_data = array();
            foreach((array)$pk as $key)
            {
                if (null===$this->$key)return null;     // 如果有一个key为设置，则返回null
                $key_data[] = $this->$key;
            }

            if ($glue)
            {
                return implode((string)$glue, $key_data);
            }
            else
            {
                return $key_data;
            }
        }
        else
        {
            return false;
        }
    }

    /**
     * 获取当前ORM的主键键名
     *
     * 如果是单个组件，则返回字符串，如果是复合主键，则返回数值
     *
     * @return string|array|null
     */
    public function pk_key_name()
    {
        return OOP_ORM_Data::get_pk_by_class_name($this->_class_name);
    }

    /**
     * 获取当前数据的组的所有数据
     *
     * @return array
     */
//    public function get_group_data()
//    {
//        $arr = array();
//        foreach($this->_group_ids as $group_id => $t)
//        {
//            if ($resource = OOP_ORM_Result::get_resource_by_group_id($group_id))
//            {
//                # 有资源对象
//                $arr = array_merge($arr, $resource->as_array());
//            }
//            else
//            {
//
//            }
//        }
//    }

    /**
     * 验证参数是否有效
     *
     * @param null $key
     * @return bool
     */
    public function valid($key = null)
    {
        if (null===$key)
        {
            # 批量验证
        }
    }

    /**
     * 获取处理字段di控制反转对象
     *
     * @param $class_name
     * @param $key
     * @return OOP_ORM_DI
     */
    protected function _get_offset_di($key)
    {
        if ($this->_is_temp_instance)
        {
            # 临时ORM对象
            if (!isset($this->_temp_offset_di[$key]))
            {
                $config = array
                (
                    'is_temp_instance' => true,
                );

                if (isset($this->_data[$key]))
                {
                    $config['field_name'] = $key;
                }
                elseif (null!==$this->_delay_setting)
                {
                    $config['may_be_delay'] = true;
                }
                else
                {
                    $config['is_virtual_field'] = true;
                }

                $this->_temp_offset_di[$key] = new OOP_ORM_DI_Default($this->_class_name, $key, $config);
            }

            return $this->_temp_offset_di[$key];
        }
        else
        {
            return OOP_ORM_Data::get_offset_di($this->_class_name, $key);
        }
    }

    /**
     * 用于系统回调
     *
     * @param $key
     * @param null $arg1
     * @param null $arg2
     */
    public function & __orm_callback($method, $arg1 = null, $arg2 = null)
    {
        $params       = func_get_args();
        array_shift($params);

        $count_params = count($params);
        $action_name  = '__orm_callback_'. $method;
        if (method_exists($this, $action_name))
        {
            switch ($count_params)
            {
                case 0 :
                    return $this->$action_name();
                    break;
                case 1 :
                    return $this->$action_name($params[0]);
                    break;
                case 2 :
                    return $this->$action_name($params[0], $params[1]);
                    break;
                case 3 :
                    return $this->$action_name($params[0], $params[1], $params[2]);
                    break;
                case 4 :
                    return $this->$action_name($params[0], $params[1], $params[2], $params[3]);
                    break;
                default :
                    return call_user_func_array(array($this, $action_name), $params);
                    break;
            }
        }

        throw new Exception('method '. $action_name .' does not exits.');
    }

    public static function parse_field_by_class_name($class_name, $class_vars = null, $expand_key = null)
    {
        $pk = null;

        # 获取当前对象所有变量
        $config = array();

        if (null===$class_vars)
        {
            /**
             * @var $obj OOP_ORM_Data
             */
            $obj = new $class_name();
            $class_vars = OOP_ORM::get_object_vars($obj);
            $expand_key = $obj->__orm_callback('get_expand_key');
            unset($obj);
        }

        if (!$class_vars)return;

        foreach($class_vars as $key => $field_config)
        {
            if ($key[0]=='_')continue;

            $type = 'Default';

            if (true===$field_config || null===$field_config)
            {
                $type = 'Default';
            }
            elseif (is_string($field_config))
            {
                if(preg_match('#^(xml|json)://(.*)$#', $field_config))
                {
                    $type = 'Resource';
                }
                elseif ((false!==strpos($field_config, '[') && preg_match('#^([a-z0-9_]+)\[(.*)\]$#i', $field_config, $m)) || false!==strpos($field_config, '.'))
                {
                    $type = 'Virtual';
                }
            }
            elseif (is_array($field_config))
            {
                if (isset($field_config['orm']))
                {
                    $type = 'ORM';
                }
                elseif (isset($field_config['data']))
                {
                    $type = 'Data';
                }
                elseif (isset($field_config['object']))
                {
                    $type = 'Object';
                }
                elseif (isset($field_config['is_virtual_field']))
                {
                    $type = 'Virtual';
                }
                elseif (isset($field_config['resource']))
                {
                    $type = 'Resource';
                }
                elseif (isset($field_config['type']) && $field_config['type'])
                {
                    switch ($field_config['type'])
                    {
                        case OOP_ORM::PARAM_TYPE_O2O:
                        case OOP_ORM::PARAM_TYPE_O2M:
                            $type = 'ORM';
                            break;
                        default;
                            break;
                    }
                }


                # 设置扩展key
                if (isset($field_config['is_expand']) && $field_config['is_expand'])
                {
                    if (!$expand_key)
                    {
                        $expand_key = $key;
                    }
                    elseif(IS_DEBUG)
                    {
                        Core::debug()->error($key, $class_name .'设置了多个$expand_key');
                    }
                }
            }

            $type_name = 'OOP_ORM_DI_'. $type;

            $config[$key] = new $type_name($class_name, $key, $field_config);

            if ($config[$key]->is_pk())
            {
                # 判断是否主键
                if ($pk)
                {
                    $pk   = (array)$pk;
                    $pk[] = $key;
                }
                else
                {
                    $pk = $key;
                }
            }
        }

        if ($expand_key)
        {
            $config['.$expand_key'] = $expand_key;
        }


        OOP_ORM_Data::$OFFSET_DI[$class_name] = $config;
        OOP_ORM_Data::$CLASS_PK[$class_name]  = $pk;
    }

    /**
     * 获取处理字段DI控制对象
     *
     * @param $class_name
     * @param $key
     * @return OOP_ORM_DI
     */
    public static function get_offset_di($class_name, $key)
    {
        if (isset(OOP_ORM_Data::$OFFSET_DI[$class_name][$key]))
        {
            return OOP_ORM_Data::$OFFSET_DI[$class_name][$key];
        }
        elseif (OOP_ORM_Data::$OFFSET_DI[$class_name]['.$expand_key'])
        {
            # 自动扩展key

            if (!isset(OOP_ORM_Data::$OFFSET_DI[$class_name]['.expand_field'][$key]))
            {
                # 创建一个虚拟对象
                OOP_ORM_Data::$OFFSET_DI[$class_name]['.expand_field'][$key] = new OOP_ORM_DI_Virtual($class_name, $key, OOP_ORM_Data::$OFFSET_DI[$class_name]['.$expand_key'].'.'. $key);
            }

            return OOP_ORM_Data::$OFFSET_DI[$class_name]['.expand_field'][$key];
        }
        else
        {
            if (!isset(OOP_ORM_Data::$OFFSET_DI[$class_name]['.undefined'][$key]))
            {
                # 创建一个虚拟对象
                OOP_ORM_Data::$OFFSET_DI[$class_name]['.undefined'][$key] = new OOP_ORM_DI_Default($class_name, $key, array('is_virtual_field' => true));
            }

            return OOP_ORM_Data::$OFFSET_DI[$class_name]['.undefined'][$key];
        }
    }

    public static function get_pk_by_class_name($class_name)
    {
        if (!isset(OOP_ORM_Data::$CLASS_PK[$class_name]))
        {
            OOP_ORM_Data::parse_field_by_class_name($class_name);
        }

        return OOP_ORM_Data::$CLASS_PK[$class_name];
    }

    /**
     * 获取一个根据主键唯一的实例化对象
     *
     * @param $orm_data_name
     * @param $data
     * @param bool $is_field_key
     * @return OOP_ORM_Data
     */
    public static function create_instance($orm_data_name, $data, $is_field_key = false)
    {
        if (isset(OOP_ORM_Data::$INSTANCE_BY_PK[$orm_data_name]) && OOP_ORM_Data::$INSTANCE_BY_PK[$orm_data_name])
        {
            # 复用寄存器中的对象

            /**
             * 获取一个临时对象
             *
             * @var $tmp_obj OOP_ORM_Data
             */
            $tmp_obj = current(OOP_ORM_Data::$INSTANCE_BY_PK[$orm_data_name]);

            # 获取主键
            $pk_name = $tmp_obj->pk_key_name();
            if (is_array($pk_name))
            {
                $tmp_id = array();

                if ($is_field_key)
                {
                    # 字段名
                    foreach($pk_name as $key)
                    {
                        $tmp_id[] = $data[$key];
                    }
                    $pk = implode(',', $tmp_id);
                }
                else
                {

                }
            }
            elseif ($pk_name)
            {
                $pk = $data[$pk_name];
            }
            else
            {
                $pk = null;
            }

            if ($pk && isset(OOP_ORM_Data::$INSTANCE_BY_PK[$orm_data_name][$pk]))
            {
                /**
                 * 获取唯一主键的对象
                 *
                 * @var $orm OOP_ORM_Data
                 */
                $orm = OOP_ORM_Data::$INSTANCE_BY_PK[$orm_data_name][$pk];

                # 更新ORM数据
                $orm->__orm_callback('renew_data', $data, $is_field_key);

                return $orm;
            }
        }


        /**
         * 实例化一个新的对象
         *
         * @var $orm OOP_ORM_Data
         */
        $orm = new $orm_data_name();

        # 用ORM调用接口设置参数
        $orm->__orm_callback('ini_data', $data, $is_field_key);

        if ($pk = $orm->pk())
        {
            if (OOP_ORM_Data::$NEW_INSTANCE_COUNT >= OOP_ORM_Data::$RELEASE_INSTANCE_COUNT)
            {
                $old_released_count = OOP_ORM_Data::$RELEASED_COUNT;

                foreach(OOP_ORM_Data::$INSTANCE_BY_PK as $item)
                {
                    foreach($item as $o)
                    {
                        /**
                         * @var $o OOP_ORM_Data
                         */
                        $o->__orm_callback('try_release');
                    }
                }
                unset($item);
                unset($o);

                $released_count = OOP_ORM_Data::$RELEASED_COUNT - $old_released_count;

                # 重置计数器
                OOP_ORM_Data::$NEW_INSTANCE_COUNT  = 0;
                OOP_ORM_Data::$ALL_INSTANCE_COUNT -= $released_count;

                if (IS_DEBUG && $released_count)
                {
                    Core::debug()->info("auto release $released_count orm.");
                }
            }

            OOP_ORM_Data::register_instance($orm, $orm_data_name, $pk);
        }

        return $orm;
    }

    /**
     * 获取当前ORM
     *
     * @return OOP_ORM_Finder_DB
     */
    public function orm()
    {
        if (!$this->_orm_name)
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
                    $class_name = $tmp_obj;
                }

                if (preg_match('#^(?:Library_[a-z0-9]+_[a-z0-9]+_)?ORM_([a-z0-9_]+)_Data$#i', $class_name, $m))
                {
                    $this->_orm_name = $m[1];
                    break;
                }
                else
                {
                    $tmp_obj = get_parent_class($tmp_obj);
                }
            }
            unset($tmp_obj);
        }

        if (!$this->_orm_name)
        {
            throw new Exception(get_class($this).'->_orm_name 未定义，请先定义');
        }

        $orm_class_name = 'ORM_'. $this->_orm_name .'_Finder';

        if (!class_exists($orm_class_name, true))
        {
            throw new Exception('指定的ORM对象“'. $orm_class_name .'”不存在');
        }

        return new $orm_class_name();
    }

    /**
     * 将对象注册导寄存器里
     *
     * @param OOP_ORM_Data $orm
     * @param null $orm_data_name
     * @param null $pk
     * @return bool
     */
    protected static function register_instance(OOP_ORM_Data $orm, $orm_data_name = null, $pk = null)
    {
        if (!$orm_data_name)$orm_data_name = strtolower(get_class($orm));
        if (!$pk)
        {
            if (!($pk = $orm->pk()))
            {
                return false;
            }
        }

        # 标记为已注册对象
        $orm->_is_register_instance = true;

        # 存入寄存器
        OOP_ORM_Data::$INSTANCE_BY_PK[$orm_data_name][$pk] = $orm;
        OOP_ORM_Data::$ALL_INSTANCE_COUNT++;
        OOP_ORM_Data::$NEW_INSTANCE_COUNT++;

        return true;
    }

    /**
     * 用于给ORM回调设置数据
     *
     * @param array $data 待设置的数据
     * @param boolean $is_field_key 待设置的数据的key是否数据库的字段，true是，false则为offset
     * @param bool $clean 是否清理老数据
     * @return bool
     */
    protected function __orm_callback_ini_data($data = null, $is_field_key = false, $clean = true)
    {
        if (!is_array($data))return false;

        if (!$is_field_key)
        {
            # 如果不是以字段名为key的数据
        }

        if ($clean)
        {
            $this->_data = $data;
        }
        else
        {
            $this->_data = array_merge($this->_data, $data);
        }

        if (!$this->_orm_data_is_created)
        {
            # 未创建
            if ($clean)
            {
                $this->_raw_data = $data;
            }
            else
            {
                $this->_raw_data = array_merge($this->_raw_data, $data);
            }
        }

        return true;
    }

    protected function & __orm_callback_get_data($key)
    {
        return $this->_data[$key];
    }

    /**
     * 设置当字段更新时可更新虚拟字段
     *
     * @param $key
     * @param $sub_key
     */
    protected function __orm_callback_set_virtual_field_update($key, $sub_key)
    {
        if (!isset($this->_update_virtual_field[$key][$sub_key]))
        {
            $this->_update_virtual_field[$key][$sub_key] = 1;
        }
    }

    /**
     * 获取当前设置的扩展key
     *
     * @return string
     */
    protected function __orm_callback_get_expand_key()
    {
        return $this->_expand_key;
    }

    /**
     * 设置组ID
     */
    protected function __orm_callback_add_group_id($group_id)
    {
        if (!isset($this->_group_ids[$group_id]))
        {
            $this->_group_ids[$group_id] = 1;

            foreach($this->_compiled_data as $item)
            {
                if (is_object($item) && $item instanceof OOP_ORM_Data)
                {
                    $item->__orm_callback_add_parent_group_id($group_id);
                }
            }
        }

        return true;
    }

    /**
     * 释放组
     *
     * @param string $group_id 释放的组ID
     * @return string
     */
    protected function __orm_callback_remove_group_id($group_id)
    {
        foreach($this->_compiled_data as $item)
        {
            if (is_object($item) && $item instanceof OOP_ORM_Data)
            {
                $item->__orm_callback_remove_parent_group_id($group_id);
            }
        }

        if ($group_id)
        {
            unset($this->_group_ids[$group_id]);
        }
    }

    protected function __orm_callback_add_parent_group_id($group_id)
    {
        $this->_parent_group_ids[$group_id] = 1;

        if ($this->_delay_setting)
        {
            # 更新父组ID
            $this->_delay_setting['parent_group_ids'] = $this->__orm_callback_get_parent_group_ids();
        }
    }


    protected function __orm_callback_remove_parent_group_id($group_id)
    {
        unset($this->_parent_group_ids[$group_id]);

        if ($this->_delay_setting)
        {
            # 更新父组ID
            $this->_delay_setting['parent_group_ids'] = $this->__orm_callback_get_parent_group_ids();
        }
    }

    /**
     * 获取组ID
     *
     * @return array
     */
    protected function __orm_callback_get_group_ids()
    {
        return $this->_group_ids ? array_keys($this->_group_ids) : array();
    }

    /**
     * 获取父组ID
     *
     * @return array
     */
    protected function __orm_callback_get_parent_group_ids()
    {
        return $this->_parent_group_ids ? array_keys($this->_parent_group_ids) : array();
    }

    /**
     * 尝试释放对象
     *
     * @param string $group_id 释放的组ID
     * @return string
     */
    protected function __orm_callback_try_release()
    {
        if (!$this->_group_ids && $pk = $this->pk())
        {
            # 没有任何组，则在 OOP_ORM_Data::$INSTANCE_BY_PK 中释放
            unset(OOP_ORM_Data::$INSTANCE_BY_PK[$this->_class_name][$pk]);
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 设置为延迟获取数据
     *
     * @return string
     */
    protected function __orm_callback_set_delay_setting(array $setting)
    {
        $this->_delay_setting = $setting;
    }

    protected function __orm_callback_get_delay_setting()
    {
        return $this->_delay_setting;
    }

    /**
     * 设置延迟获取的数据
     *
     * @param $data
     * @return bool
     */
    protected function __orm_callback_set_delay_data($data)
    {
        $this->_delay_setting = null;

        if ($data && is_array($data))
        {
            $this->_data = array_merge($this->_data, $data);
        }

        return true;
    }
}