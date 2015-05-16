<?php

/**
 * MyQEE ORM 类型基础类
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Module
 * @package    ORM
 * @copyright  Copyright (c) 2008-2016 myqee.com
 * @license    http://www.myqee.com/license.html
 */
abstract class Module_OOP_ORM_DI
{
    /**
     * 当前类名称
     *
     * @var string
     */
    protected $class_name;

    /**
     * 当前key的名称
     *
     * @var string
     */
    protected $key;

    /**
     * 对应数据的表名称
     *
     * @var string
     */
    protected $table_name;

    /**
     * 对应数据的字段名
     *
     * @var string
     */
    protected $field_name;

    /**
     * 当前配置
     *
     * @var array
     */
    protected $config;
    
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
     * 记录所有metadata的key
     *
     * @var array
     */
    protected static $META_GROUP_OF_KEY = array();

    /**
     * 记录所有metadata的key所在的表名称
     *
     * @var array
     */
    protected static $META_TABLE_OF_KEY = array();


    public function __construct($class_name, $key, $table_name, $config)
    {
        $this->class_name = $class_name;
        $this->key        = $key;
        $this->config     = $config;
        $this->table_name = $table_name;

        $this->check_config();
        $this->format_config();
    }

    /**
     * 格式化默认的参数，会在每个类型的 `format()` 方法前执行
     */
    protected function check_config()
    {
        if (!is_array($this->config))
        {
            $this->config = array();
        }

        if (isset($this->config['field']) && $this->config['field'])
        {
            $this->field_name = $this->config['field_name'] = $this->config['field'];

            unset($this->config['field']);
        }
        elseif (isset($this->config['field_name']) && $this->config['field_name'])
        {
            $this->field_name = $this->config['field_name'];
        }
        else
        {
            # 标记为虚拟字段
            $this->config['is_virtual'] = true;
        }


        # 是否只读字段
        if (isset($this->config['is_readonly']))
        {
            $this->config['is_readonly'] = (bool)$this->config['is_readonly'];
        }
        else
        {
            $this->config['is_readonly'] = false;
        }

        # 兼容旧版本主键设置
        if (isset($this->config['is_id_field']))
        {
            $this->config['pk'] = (bool)$this->config['is_id_field'];
            unset($this->config['is_id_field']);
        }

        # 处理缓存配置
        if (isset($this->config['cache']))
        {
            if (is_array($this->config['cache']))
            {
                # 数组不处理
                if (!isset($this->config['cache']['config']))
                {
                    $this->config['cache']['config'] = null;
                }
            }
            elseif (is_string($this->config['cache']) && false !== strpos(',', $this->config['cache']))
            {
                $opt = explode(',', $this->config['cache']);
                $this->config['cache'] = array
                (
                    'config'  => $opt[0],
                    'expired' => $opt[1],
                );
                if (isset($opt[2]))
                {
                    $this->config['cache']['expire_type'] = $opt[3];
                }
            }
            else
            {
                $this->config['cache'] = array
                (
                    'config'  => null,
                    'expired' => (int)$this->config['cache'],
                );
            }
        }
    }

    /**
     * 格式化配置
     *
     * @return mixed
     */
    abstract protected function format_config();

    /**
     * 获取当前类型的数据
     *
     * @param OOP_ORM_Data $obj
     * @param $data
     * @param $compiled_data
     * @return mixed
     */
    abstract public function & get_data(OOP_ORM_Data $obj, & $data, & $compiled_data, & $compiled_raw_data);

    abstract public function set_data(OOP_ORM_Data $obj, & $data, & $compiled_data, & $compiled_raw_data, $new_value, $has_compiled);

    /**
     * 是否设置过对象
     *
     * @param OOP_ORM_Data $obj
     * @param $data
     * @param $compiled_data
     * @return bool
     */
    public function is_set(OOP_ORM_Data $obj, & $data, & $compiled_data)
    {
        if (isset($data[$this->key]) || isset($compiled_data[$this->key]))
        {
            # isset 比 array_key_exists 快，所以先用 isset 判断
            return true;
        }
        elseif (array_key_exists($this->key, $data) || array_key_exists($this->key, $compiled_data))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function un_set(OOP_ORM_Data $obj, & $data, & $compiled_data)
    {
        if ($this->config['is_readonly'] && $this->is_set($obj, $data, $compiled_data))
        {
            # 只读字段不允许unset
            return false;
        }

        unset($compiled_data[$this->key]);

        return true;
    }

    /**
     * 当前表名称
     *
     * @return string
     */
    public function table_name()
    {
        return $this->table_name;
    }

    /**
     * 获取当前字段名
     *
     * @return string|null
     */
    public function field_name()
    {
        return $this->field_name;
    }

    /**
     * 是否自读字段
     *
     * @return bool
     */
    public function is_readonly()
    {
        if ($this->config['is_readonly'])
        {
            # 只读字段
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 是否主键
     *
     * @return bool
     */
    public function is_pk()
    {
        return $this->config['pk']?true:false;
    }

    /**
     * 是否修改过内容
     *
     * @return bool
     */
    public function check_data_is_change(OOP_ORM_Data $obj, $only_check_field_data, $current_data, $current_compiled_data, $current_raw_compiled_data)
    {
        if ($current_data === $current_compiled_data || $current_raw_compiled_data === $current_compiled_data)
        {
            # 数据相同
            return false;
        }
        elseif (!$this->is_metadata() && !$this->field_name() && null === $current_compiled_data)
        {
            # 没有字段名，数据也是空
            return false;
        }
        elseif (is_object($current_compiled_data))
        {
            if ($current_compiled_data !== $current_raw_compiled_data)
            {
                # 不是同一个对象
                return true;
            }
            elseif ($current_compiled_data instanceof OOP_ORM_Data)
            {
                # ORM 数据
                return $current_compiled_data->is_changed($only_check_field_data);
            }
            elseif ($current_compiled_data == $current_raw_compiled_data)
            {
                # 同一个类型
                if (isset($this->config['callback']['is_change']) && ($method = $this->config['callback']['is_change']) && method_exists($current_compiled_data, $method))
                {
                    # 回调是否修改过
                    return $current_compiled_data->$method();
                }
                elseif (isset($this->config['callback']['get_object_id']) && ($method = $this->config['callback']['get_object_id']) && method_exists($current_compiled_data, $method))
                {
                    # 回调对象ID
                    return (($id = $current_compiled_data->$method()) && $id === $current_raw_compiled_data->$method()) ? false : true;
                }
                elseif (isset($this->config['callback']['get_data']) && ($method = $this->config['callback']['get_data']) && method_exists($current_compiled_data, $method))
                {
                    # 回调获取数据
                    return $current_compiled_data->$method() !== $current_raw_compiled_data->$method() ? true : false;
                }
                elseif ($current_compiled_data instanceof stdClass || $current_compiled_data instanceof ArrayObject || $current_compiled_data instanceof ArrayIterator)
                {
                    # 通过数组进行对比
                    return (array)$current_compiled_data !== (array)$current_raw_compiled_data ? true: false;
                }
                elseif (method_exists($current_compiled_data, '__toString'))
                {
                    # 回调获取数据
                    return $current_compiled_data->__toString() !== $current_raw_compiled_data->__toString() ? true : false;
                }
                elseif (method_exists($current_compiled_data, 'getArrayCopy'))
                {
                    # 回调获取数据
                    return $current_compiled_data->getArrayCopy() !== $current_raw_compiled_data->getArrayCopy() ? true : false;
                }
                else
                {
                    # 序列化成文本进行对比
                    return serialize($current_compiled_data) !== serialize($current_raw_compiled_data)? true : false;
                }
            }
            else
            {
                # 对象发生修改
                return true;
            }
        }
        else
        {
            # 使用旧数据进行对比
            return $current_compiled_data === $current_raw_compiled_data ? false : true;
        }
    }

    /**
     * 刷新数据
     *
     * @param array $data 传入刷新的数据
     * @param array $current_compiled_data 当前已经构造的数据
     * @param bool $runtime_format 动态格式化，传入 true 则会对是 array 或对象的数据 serialize 处理
     * @return bool
     */
    public function refresh_field_data(OOP_ORM_Data $obj, & $data, $current_compiled_data, $runtime_format = false)
    {
        if (!$this->field_name)
        {
            return false;
        }

        if (is_object($current_compiled_data))
        {
            if (isset($this->config['callback']['get_data']) && ($method = $this->config['callback']['get_data']) && method_exists($current_compiled_data, $method))
            {
                # 回调获取数据
                $new_data = $current_compiled_data->$method();
            }
            elseif ($current_compiled_data instanceof OOP_ORM_Data)
            {
                if (isset($this->config['bind']) && $this->config['bind'])
                {
                    $new_data = $current_compiled_data->get_data_by_field_name($this->config['bind'], true);
                }
                else
                {
                    $new_data = $current_compiled_data->pk();
                }
            }
            elseif ($current_compiled_data instanceof stdClass || $current_compiled_data instanceof ArrayObject || $current_compiled_data instanceof ArrayIterator)
            {
                $new_data = (array)$current_compiled_data;
            }
            elseif (method_exists($current_compiled_data, '__toString'))
            {
                $new_data = (string)$current_compiled_data;
            }
            elseif (method_exists($current_compiled_data, 'getArrayCopy'))
            {
                $new_data = $current_compiled_data->getArrayCopy();
            }
            else
            {
                $new_data = serialize($current_compiled_data);
            }
        }
        else
        {
            $new_data = $current_compiled_data;
        }

        if ($runtime_format)
        {
            # 动态格式化
            if (is_array($new_data) || is_object($new_data))
            {
                $new_data = serialize($new_data);
            }
        }
        else
        {
            if (isset($this->config['format']) && $this->config['format'])
            {
                # 格式化
                OOP_ORM_DI::_format_data($this->config['format'], $new_data);
            }
        }

        if (null === $new_data && !isset($data[$this->table_name][$this->key]) && isset($this->config['is_temp_instance']) && $this->config['is_temp_instance'])
        {
            # 对于这种情况应该认为不存在此字段
        }
        else
        {
            $this->format_field_value($obj, $data, $new_data);
        }

        return true;
    }

    /**
     * 是否虚拟字段
     *
     * @return bool
     */
    public function is_virtual()
    {
        return (false === $this->is_metadata() && isset($this->config['is_virtual']) && $this->config['is_virtual']) ? true : false;
    }

    /**
     * 是否元数据拟字段
     *
     * @return bool
     */
    public function is_metadata()
    {
        return false;
    }

    /**
     * 获取当前配置
     *
     * @return array
     */
    public function config()
    {
        return $this->config;
    }

    /**
     * 删除指定的offset的缓存（如果有）
     *
     * @param OOP_ORM_Data $obj
     * @return bool
     */
    public function delete_cache(OOP_ORM_Data $obj)
    {
        # 无缓存设置
        if (!isset($this->config['cache']))return true;

        /**
         * @var $cache Cache
         */
        list($cache, $key) = $this->get_cache_instance_and_key($obj);

        # 清除缓存
        return $cache->delete($key);
    }

    /**
     * 获取缓存数据
     *
     * @param OOP_ORM_Data $obj
     * @param $orm_config
     * @return array array($cache, $cache_key, $data)
     */
    protected function get_cache_data(OOP_ORM_Data $obj)
    {
        $config = $this->config();

        if (isset($config['cache']))
        {
            list($cache, $cache_key) = $this->get_cache_instance_and_key($obj);

            /**
             * @var $cache Cache
             */
            $data = $cache->get($cache_key);

            if ($data && IS_DEBUG)
            {
                Core::debug()->info($cache_key, 'cache_key');
                Core::debug()->info('found cache '. get_class($obj) .'{PK:'. $obj->pk() .'}->'. $this->key . ($data instanceof OOP_ORM_Data?'. item pk is '. $data->pk() : ''));
            }

            return array($cache, $cache_key, $data);
        }
        else
        {
            return array(null, null, null);
        }
    }

    /**
     * 获取缓存实例化对象和当前key
     *
     * @param OOP_ORM_Data $obj
     * @return array array($cache, $cache_key)
     */
    protected function get_cache_instance_and_key(OOP_ORM_Data $obj)
    {
        $opt = $this->cache_key_option($obj);
        $key = 'orm_cache.'. $this->class_name .'->'. $this->key .','. md5(json_encode($opt));

        if (IS_DEBUG)
        {
            Core::debug()->info($opt, $key);
        }

        return array
        (
            new Cache($this->config['cache']['config']),
            $key,
        );
    }

    /**
     * 返回用于生成cache的key的option
     *
     * @param OOP_ORM_Data $obj
     * @return array
     */
    protected function cache_key_option(OOP_ORM_Data $obj)
    {
        return $this->config();
    }

    protected function format_field_value(OOP_ORM_Data $obj, & $data, $new_data)
    {
        $data[$this->table_name][$this->field_name] = $new_data;
    }

    public static function parse_offset($class_name, $class_vars = null, $expand_key = null, $tablename = null, $meta_tablename = null)
    {
        if (isset(OOP_ORM_DI::$OFFSET_DI[$class_name]))
        {
            return;
        }

        $pk = array();

        # 获取当前对象所有变量
        $config = array();

        if (null === $class_vars)
        {
            /**
             * @var $obj OOP_ORM_Data
             */
            $obj            = new $class_name();
            $class_vars     = get_object_vars($obj);
            $expand_key     = $obj->__orm_callback('get_expand_key');
            $tablename      = $obj->finder()->tablename();
            $meta_tablename = $obj->finder()->tablename_meta();
            unset($obj);
        }

        if (!$class_vars)return;

        foreach($class_vars as $key => $field_config)
        {
            if ($key[0] === '_')continue;

            $type = 'Default';

            if (true === $field_config || null === $field_config)
            {
                $type = 'Default';
            }
            elseif (is_string($field_config))
            {
                if ($field_config === '@expand')
                {
                    # 支持使用快速扩展字段

                    continue;
                }
                elseif (preg_match('#^@meta(?:\@([a-z0-9_\-]+))?$#', $field_config, $m))
                {
                    # 支持快速定义元数据
                    $field_config = array
                    (
                        'type'       => 'meta',
                        'field_name' => $key,
                        'table_name' => isset($m[1])?$m[1]: $meta_tablename,
                    );
                }
                elseif(preg_match('#^(xml|json|http|https)://(.*)$#', $field_config))
                {
                    $type = 'Resource';
                }
                elseif ((false !== strpos($field_config, '[') && preg_match('#^([a-z0-9_]+)\[(.*)\]$#i', $field_config, $m)) || false!==strpos($field_config, '.'))
                {
                    $type = 'Virtual';
                }
            }

            if (is_array($field_config))
            {
                if (isset($field_config['orm']))
                {
                    $type = 'ORM';
                }
                elseif (isset($field_config['function']))
                {
                    $type = 'Function';
                }
                elseif (isset($field_config['is_virtual']))
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

                        case 'meta':
                            $type = 'Meta';

                            # 更新元数据数据表
                            if (isset($field_config['table_name']))
                            {
                                $tablename = $field_config['table_name'];
                            }

                            # 记录分组
                            OOP_ORM_DI::$META_GROUP_OF_KEY[$class_name][$key] = isset($field_config['meta_group']) ? (string)$field_config['meta_group'] : '';
                            OOP_ORM_DI::$META_TABLE_OF_KEY[$class_name][$key] = $tablename;
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

            /**
             * @var $tmp OOP_ORM_DI_Default
             */
            $tmp = new $type_name($class_name, $key, $tablename, $field_config);

            # 判断是否主键
            if ($tmp->is_pk())
            {
                $pk[$tmp->field_name()] = $key;
            }

            $config[$key] = $tmp;

            unset($tmp);
        }

        if ($expand_key)
        {
            $config['.$expand_key'] = $expand_key;
        }


        OOP_ORM_DI::$OFFSET_DI[$class_name] = $config;
        OOP_ORM_DI::$CLASS_PK[$class_name]  = $pk;
    }

    /**
     * 获取处理字段DI控制对象
     *
     * @param $class_name
     * @param $key
     * @return OOP_ORM_DI
     */
    public static function get_class_di($class_name, $key)
    {
        if (isset(OOP_ORM_DI::$OFFSET_DI[$class_name][$key]))
        {
            return OOP_ORM_DI::$OFFSET_DI[$class_name][$key];
        }
        elseif (OOP_ORM_DI::$OFFSET_DI[$class_name]['.$expand_key'])
        {
            # 自动扩展key

            if (!isset(OOP_ORM_DI::$OFFSET_DI[$class_name]['.expand_field'][$key]))
            {
                # 创建一个虚拟对象
                OOP_ORM_DI::$OFFSET_DI[$class_name]['.expand_field'][$key] = new OOP_ORM_DI_Virtual($class_name, $key, null, OOP_ORM_DI::$OFFSET_DI[$class_name]['.$expand_key'].'.'. $key);
            }

            return OOP_ORM_DI::$OFFSET_DI[$class_name]['.expand_field'][$key];
        }
        else
        {
            if (!isset(OOP_ORM_DI::$OFFSET_DI[$class_name]['.undefined'][$key]))
            {
                # 创建一个虚拟对象
                OOP_ORM_DI::$OFFSET_DI[$class_name]['.undefined'][$key] = new OOP_ORM_DI_Default($class_name, $key, null, array('is_virtual' => true));
            }

            return OOP_ORM_DI::$OFFSET_DI[$class_name]['.undefined'][$key];
        }
    }

    /**
     * 返回指定对象主键
     *
     * 如果没有主键则返回空数组 `array()`
     *
     * @param $class_name
     * @return array
     */
    public static function get_pk_name_by_class_name($class_name)
    {
        if (!isset(OOP_ORM_DI::$CLASS_PK[$class_name]))
        {
            OOP_ORM_DI::parse_offset($class_name);
        }

        return OOP_ORM_DI::$CLASS_PK[$class_name];
    }

    /**
     * 根据字段名获取key名
     *
     * @param $class_name
     * @param $field_name
     * @return null|string
     */
    public static function get_key_by_field_name($class_name, $field_name)
    {
        if (null === $field_name)return null;

        if (isset(OOP_ORM_DI::$OFFSET_DI[$class_name]))
        {
            if (isset(OOP_ORM_DI::$OFFSET_DI[$class_name][$field_name]) && $tmp_di = OOP_ORM_DI::$OFFSET_DI[$class_name][$field_name])
            {
                /**
                 * @var $tmp_di OOP_ORM_DI_Default
                 */
                if ($field_name === $tmp_di->field_name())
                {
                    # field_name 和 key 相同
                    return $field_name;
                }
            }

            # 遍历
            foreach(OOP_ORM_DI::$OFFSET_DI[$class_name] as $tmp_di)
            {
                if ($field_name == $tmp_di->field_name())
                {
                    # field_name 和 key 相同
                    return $field_name;
                }
            }
        }

        return null;
    }

    /**
     * 获取指定类名称所有元数据的组对照关系
     *
     *      OOP_ORM_DI::get_meta_group_of_key($class);
     *
     * 返回内容类似
     *
     *      array
     *      (
     *          //键      所属分组
     *          'key1' => '',
     *          'key2' => '',
     *          'key3' => 'lazy_load',
     *      );
     *
     *
     * @param string $class_name 对象名称
     * @return array
     */
    public static function get_meta_group_of_key($class_name)
    {
        return isset(OOP_ORM_DI::$META_GROUP_OF_KEY[$class_name]) ? OOP_ORM_DI::$META_GROUP_OF_KEY[$class_name] : array();
    }


    /**
     * 获取指定类名称所有元数据的表对照关系
     *
     *      OOP_ORM_DI::get_meta_table_of_key($class);
     *
     * 返回内容类似
     *
     *      array
     *      (
     *          //键      表
     *          'key1' => 'data',
     *          'key2' => 'data',
     *          'key3' => 'data_02',
     *      );
     *
     *
     * @param string $class_name 对象名称
     * @return array
     */
    public static function get_meta_table_of_key($class_name)
    {
        return isset(OOP_ORM_DI::$META_TABLE_OF_KEY[$class_name]) ? OOP_ORM_DI::$META_TABLE_OF_KEY[$class_name] : array();
    }

    /**
     * 获取对象数据
     *
     * @param $obj
     * @return array
     */
    public static function get_object_vars($obj)
    {
        return get_object_vars($obj);
    }


    /**
     * 格式化数据
     *
     * @param $key
     * @param $format_config
     * @param $compiled_data
     */
    protected static function _format_data($format_config, &$tmp_data)
    {
        foreach((array)$format_config as $v)
        {
            try
            {
                if (is_array($v))
                {
                    $fun = 'OOP_ORM_DI::_format_action_'. array_shift($v);
                    array_unshift($v, $tmp_data);
                    $tmp_data = call_user_func_array($fun, $v);
                }
                else
                {
                    $fun = '_format_action_'. $v;
                    $tmp_data = OOP_ORM_DI::$fun($tmp_data);
                }
            }
            catch (Exception $e)
            {
                Core::debug()->error($v, 'ORM格式化方法不存在');
            }
        }
    }

    /**
     * 解开数据
     *
     * @param $key
     * @param $format_config
     * @param $compiled_data
     */
    protected static function _de_format_data($format_config, &$tmp_data)
    {
        foreach(array_reverse((array)$format_config) as $v)
        {
            try
            {
                if (is_array($v))
                {
                    $fun = 'OOP_ORM_DI::_de_format_action_'. array_shift($v);
                    array_unshift($v, $tmp_data);
                    $tmp_data = call_user_func_array($fun, $v);
                }
                else
                {
                    $fun = '_de_format_action_'. $v;
                    $tmp_data = OOP_ORM_DI::$fun($tmp_data);
                }
            }
            catch (Exception $e)
            {
                Core::debug()->error($v, 'ORM解析方法不存在');
            }
        }
    }


    protected static function _format_action_serialize($data)
    {
        return serialize($data);
    }

    protected static function _de_format_action_serialize($data)
    {
        try
        {
            return @unserialize((string)$data);
        }
        catch (Exception $e)
        {
            if (IS_DEBUG)Core::debug()->error($data, 'deformat orm serialize error');
            return false;
        }
    }

    protected static function _format_action_json($data)
    {
        return json_encode($data);
    }

    protected static function _de_format_action_json($data)
    {
        try
        {
            return json_decode((string)$data, true);
        }
        catch (Exception $e)
        {
            if (IS_DEBUG)Core::debug()->error($data, 'deformat orm json error');
            return false;
        }
    }

    protected static function _format_action_gzcompress($data)
    {
        return gzcompress($data, 9);
    }

    protected static function _de_format_action_gzcompress($data)
    {
        try
        {
            return gzuncompress($data);
        }
        catch (Exception $e)
        {
            if (IS_DEBUG)Core::debug()->error($data, 'deformat orm gzcompress error');
            return false;
        }
    }

    /**
     * 拼接字符串
     *
     * @param mixed $data
     * @param string $exp_str 默认,分割
     * @param boolean $is_add_both_str 是否在两段加上$exp_str
     *
     * @return string
     */
    protected static function _format_action_implode($data, $exp_str = ',', $is_add_both_str = false)
    {
        $v = (string)implode($exp_str, $data);
        if ($v === '')
        {
            return '';
        }
        else
        {
            if ($is_add_both_str)
            {
                return $exp_str . $v . $exp_str;
            }
            else
            {
                return $v;
            }
        }
    }

    /**
     * 分割字符串
     *
     * @param mixed $data
     * @param string $exp_str 默认,分割
     * @param boolean $is_add_both_str 是否在两段加上$exp_str
     *
     * @return array
     */
    protected static function _de_format_action_implode($data, $exp_str = ',', $is_add_both_str = false)
    {
        $data = (string)$data;
        if ($is_add_both_str)
        {
            $data = trim($data, $exp_str);
        }
        if (empty($data))
        {
            return array();
        }
        return explode($exp_str, $data);
    }

    /**
     * 格式化成XML字符串
     *
     * @use Text::to_xml
     * @param array $array 数组
     * @param string $tab 缩进字符，默认 tab 符
     * @param string $crlf 换行符，默认window换行符
     * @param string $attribute_key XML的attributes所在key，默认 `@attributes`
     * @param string $xml_header_string XML第一行声明的字符串
     * @return string
     */
    protected static function _format_action_xml($data, $tab = "\t", $crlf = CRLF, $attribute_key = '@attributes', $xml_header_string = null)
    {
        return Arr::to_xml($data, $tab, $crlf, $attribute_key, $xml_header_string);
    }

    /**
     * 从XML格式化成数组
     *
     * @use Text::xml_to_array
     * @param string|SimpleXMLElement $xml_string XML字符串，支持http的XML路径，接受 SimpleXMLElement 对象
     * @param string $attribute_key attributes所使用的key，默认 @attributes，设置成 true 则和内容自动合并
     * @param int $max_recursion_depth 解析最高层次，默认25
     * @param int|array $url_xml_setting 如果传入的 `$xml_string` 是URL，则允许缓存的时间或者是缓存配置的array，默认不缓存
     * @return array | false 失败则返回false
     */
    protected static function _de_format_action_xml($data, $attribute_key = null, $max_recursion_depth = null, $url_xml_setting = null)
    {
        return Text::xml_to_array($data, $attribute_key, $max_recursion_depth, $url_xml_setting);
    }

    /**
     * 检验数据类型
     *
     * 目前支持处理整形和浮点型数据转换
     *
     * @see http://www.metsky.com/archives/84.html MySQL数据类型及范围用法一览表
     * @param $field_type
     * @param $compiled_data
     */
    protected function _check_field_type($field_type, & $data)
    {
        if (preg_match('#^(Int|TinyInt|Bit|SmallInt|MediumInt|BigInt)(?:\(([0-9]+)\))?((:?,| )Unsigned)?$#i', $field_type, $m))
        {
            $type = strtolower($m[1]);
            if ($m[3] && strtolower($m[3]) === 'unsigned')
            {
                $type_num = array
                (
                    'int'       => array(0, (1<<32)-1),
                    'bit'       => array(0, 255),
                    'tinyint'   => array(0, 255),
                    'smallint'  => array(0, 65535),
                    'mediumint' => array(0, (1<<24)-1),
                    'bigint'    => array(0, pow(2, 64)-1),
                );
            }
            else
            {
                $type_num = array
                (
                    'int'       => array(-1<<31, (1<<31)-1),
                    'bit'       => array(-128, 127),
                    'tinyint'   => array(0, 255),
                    'smallint'  => array(-32768, 32767),
                    'mediumint' => array(-1<<23, (1<<23)-1),
                    'bigint'    => array(-pow(2, 63), pow(2, 63)-1),
                );
            }

            $data = (int)$data;
            $num = $type_num[$type];

            if ($data < $num[0])
            {
                $data = $num[0];
            }
            elseif ($data > $num[1])
            {
                $data = $num[1];
            }
        }
        elseif (preg_match('#^(Float|Double|Decimal)#i', $field_type))
        {
            # 转换成浮点型
            $data = (float)$data;
        }
        elseif (preg_match('#^(bool|boolean)$#i', $field_type))
        {
            $data = (bool)$data;
        }
    }
}