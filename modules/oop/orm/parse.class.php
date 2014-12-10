<?php

/**
 * ORM 字段解析器
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Module
 * @package    ORM
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
abstract class Module_OOP_ORM_Parse
{
    /**
     * 记录ORM对象的配置
     *
     * @var array
     */
    public static $CONFIG = array();

    /**
     * 主键设置
     *
     * @var array
     */
    public static $PK = array();

    /**
     * 虚拟字段
     *
     * @var array
     */
    public static $VIRTUAL_FIELD = array();

    /**
     * 子字段
     *
     * @var array
     */
    public static $SUB_FIELD = array();

    public function __construct()
    {
        throw new Exception('OOP_ORM_Parse 对象不需实例化');
    }

    /**
     * 解析ORM的字段配置
     *
     * @param $class_name
     */
    public static function config($class_name)
    {
        # 获取当前对象所有变量
        $config     = array();
        $class_vars = get_class_vars($class_name);

        foreach($class_vars as $key => $value)
        {
            if ($key[0]=='_')continue;

            if (true===$value)
            {
                $field_config = array
                (
                    'field_name'       => $key,
                    'is_virtual' => false,
                );

                if ($key=='id')
                {
                    # 对于id=true的字段，默认为主键
                    if (isset(OOP_ORM_Parse::$PK[$class_name]))
                    {
                        OOP_ORM_Parse::$PK[$class_name] = (array)OOP_ORM_Parse::$PK[$class_name];
                        OOP_ORM_Parse::$PK[$class_name][] = $key;
                    }
                    else
                    {
                        OOP_ORM_Parse::$PK[$class_name] = $key;
                    }

                    $field_config['is_pk']       = true;
                    $field_config['is_readonly'] = true;
                }
            }
            elseif (null===$value)
            {
                $field_config = array
                (
                    'field_name'       => $key,
                    'is_virtual' => false,
                );
            }
            elseif (is_string($value))
            {
                $field_config = array();

                if (false!==strpos($value, '[') && preg_match('#^([a-z0-9_]+)\[(.*)\]$#i', $value, $m))
                {
                    # 支持 test[abc][def] 格式
                    $field_config['is_virtual'] = true;
                    $field_config['parent_field']     = explode('][', $m[2]);
                    array_unshift($field_config['parent_field'], $m[1]);
                }
                elseif (false!==strpos($value, '.'))
                {
                    $field_config['is_virtual'] = true;
                    $field_config['parent_field']     = explode('.', $value);
                }
                else
                {
                    $field_config = array
                    (
                        'field_name' => $value,
                    );

                    if ($value==$key)
                    {
                        $field_config['is_virtual'] = false;
                    }
                    else
                    {
                        $field_config['is_virtual'] = true;
                    }
                }
            }
            elseif (is_array($value))
            {
                $field_config = $value;

                if (isset($field_config['is_readonly']))
                {
                    $field_config['is_readonly'] = (bool)$field_config['is_readonly'];
                }

                if ((isset($field_config['is_pk']) && true===$field_config['is_pk']) || (isset($field_config['is_id_field']) && true===$field_config['is_id_field']))
                {
                    # 主键字段
                    if (isset(OOP_ORM_Parse::$PK[$class_name]))
                    {
                        OOP_ORM_Parse::$PK[$class_name] = (array)OOP_ORM_Parse::$PK[$class_name];
                        OOP_ORM_Parse::$PK[$class_name][] = $key;
                    }
                    else
                    {
                        OOP_ORM_Parse::$PK[$class_name] = $key;
                    }
                }

                if (isset($field_config['orm']))
                {
                    OOP_ORM_Parse::_parse_orm($key, $field_config);
                }
                elseif (isset($field_config['data']))
                {
                    OOP_ORM_Parse::_parse_data($key, $field_config);
                }
                elseif (isset($field_config['object']))
                {
                    OOP_ORM_Parse::_parse_object($key, $field_config);
                }
                elseif (isset($field_config['format']))
                {
                    $field_config['format'] = (array)$field_config['format'];
                }
            }
            else
            {
                continue;
            }

            # 记录虚拟字段
            if ($field_config['is_virtual'])
            {
                OOP_ORM_Parse::$VIRTUAL_FIELD[$class_name][$key] = $key;
            }

            if (isset($field_config['parent_field']) && $field_config['parent_field'])
            {
                # 记录子字段
                OOP_ORM_Parse::$SUB_FIELD[$class_name][$key] = $key;
            }

            $config[$key] = $field_config;
        }

        OOP_ORM_Parse::$CONFIG[$class_name]    = $config;
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
     * 检查ORM配置
     *
     * @param string $key
     * @param array $field_config
     */
    protected static function _parse_orm($key, & $field_config)
    {
        if ($field_config['orm'] && !is_array($field_config['orm']))
        {
            $field_config['orm'] = array
            (
                'name' => (string)$field_config['orm']['name']
            );
        }
        elseif(!isset($field_config['orm']['name']))
        {
            if (IS_DEBUG)Core::debug()->error($field_config, 'ORM字段:'.$key.'配置错误，data属性应该为数组');
            unset($field_config['orm']);
            return;
        }

        # 设置为ORM时，data,object,format配置将无效
        unset($field_config['data'], $field_config['object'], $field_config['format']);
    }


    /**
     * 检查获取数据配置
     *
     * @param string $key
     * @param array $field_config
     * @throws Exception
     */
    protected static function _parse_data($key, &$field_config)
    {
        if (!isset($field_config['data']))
        {
            return;
        }

        if (!is_array($field_config['data']))
        {
            if (IS_DEBUG)Core::debug()->error($field_config, 'ORM字段:'.$key.'配置错误，data属性应该为数组');
            unset($field_config['data']);
            return;
        }

        if (!isset($field_config['data']['driver']))
        {
            if (IS_DEBUG)Core::debug()->error($field_config, 'ORM字段:'.$key.'配置错误，data属性缺少driver');
            unset($field_config['data']);
            return;
        }

        if (isset($field_config['data']['mapping']) && !is_array($field_config['data']['mapping']))
        {
            if (IS_DEBUG)Core::debug()->error($field_config, 'ORM字段:'.$key.'配置错误，data属性的mapping应该为数组');
            unset($field_config['data']['mapping']);
            return;
        }

        $run = 'parse_driver_'. $field_config['data']['driver'];
        if (method_exists('OOP_ORM_Parse', $run))
        {
            # 处理数据解析
            $field_config = OOP_ORM_Parse::$run($field_config);
        }
    }

    protected static function _parse_object($key, &$field_config)
    {
        if (!isset($field_config['object']))
        {
            return;
        }
        if (is_string($field_config['object']))
        {
            $field_config['object'] = array
            (
                'name' => $field_config['object']
            );
        }
        elseif (!is_array($field_config['object']))
        {
            if (IS_DEBUG)Core::debug()->error('ORM字段:'.$key.'配置错误，object属性应为数组');
            unset($field_config['object']);
            return;
        }

        if (!isset($field_config['object']['name']))
        {
            if (IS_DEBUG)Core::debug()->error('ORM字段:'.$key.'配置错误，object属性的name必须设置');
            unset($field_config['object']);
            return;
        }
    }

    /**
     * 解析配置
     *
     * @param array $field_config
     * @throws Exception
     * @return array $field_config
     */
    protected static function _parse_driver_database($config)
    {
        if (!isset($config['database']))
        {
            $config['database'] = 'default';
        }

        if (!isset($config['tablename']))
        {
            throw new Exception('配置错误，缺少表名称.');
        }

        return $config;
    }


    /**
     * 根据字段ORM配置获取ORM数据
     *
     * @param OOP_ORM_Data $data_obj
     * @param array $orm_config
     * @throws Exception
     * @return mixed
     */
    public static function get_orm_data_by_config(OOP_ORM_Data $data_obj, $orm_config, $index)
    {
        if (isset($orm_config['cache']))
        {
            # 设置了缓存
            $c_config = $orm_config;
            foreach ($c_config['mapping'] as & $item)
            {
                $item = $data_obj->$item;
            }
            $data = OOP_ORM_Parse::get_orm_cache_data($data_obj, $index, $c_config);

            $orm_name = 'ORM_'. $orm_config['name'] .'_Data';
            if ($data && is_object($data) && $data instanceof $orm_name)
            {
                return $data;
            }

            $can_try_get_groups_data = false;
        }
        else
        {
            # 是否可以批量取对象集里的数据
            $can_try_get_groups_data = true;
        }

        # 映射ORM对象
        $obj_name = 'ORM_'. $orm_config['name'] .'_Finder';
        if (!class_exists($obj_name, true))
        {
            throw new Exception('指定的ORM:'. $orm_config['name'] .'不存在！');
        }
        /**
         * @var $obj OOP_ORM_Finder_DB
         */
        $obj = new $obj_name();

        # 支持OFFSET
        if (isset($orm_config['offset']))
        {
            $obj->offset($orm_config['offset']);
            if ($orm_config['offset'] > 0)
            {
                $can_try_get_groups_data = false; //有OFFSET的话，不能同时取多条数据
            }
        }

        # 支持GROUP BY
        if (isset($orm_config['group_by']))
        {
            foreach ($orm_config['group_by'] as $item)
            {
                $obj->group_by($item);
            }
            $can_try_get_groups_data = false;
        }

        # 支持ORDER BY
        if ( isset($orm_config['order_by']) )
        {
            foreach ($orm_config['order_by'] as $key=>$item)
            {
                $obj->order_by($key, $item);
            }
            $can_try_get_groups_data = false;
        }

        # 支持IN
        if ( isset($orm_config['in']) )
        {
            foreach ($orm_config['in'] as $key=>$item)
            {
                $obj->in($key, $item);
            }
            $can_try_get_groups_data = false;
        }

        # 获取ORM单条数据
        if ($can_try_get_groups_data && $orm_config['return'] == OOP_ORM::PARAM_RETURN_SINGLE)
        {
            # 对于单条数据，可以尝试批量获取，以优化SQL性能
            $data = OOP_ORM_Parse::_get_orm_group_data($obj, $data_obj, $orm_config, $index);
            if (true!==$data)
            {
                # 如果!==true，则直接返回
                return $data;
            }
        }

        $orm_where = array();
        # 处理mapping
        if ($orm_config['mapping'])
        {
            foreach ($orm_config['mapping'] as $k => $v)
            {
                $orm_where[$k] = $data_obj->$v;
            }
        }

        if (isset($orm_config['where']))
        {
            $orm_where = $orm_config['where'] + $orm_where;
        }

        if ($orm_where)
        {
            # 设置 auto where
            $obj->set_auto_where($orm_where);
        }

        if ($orm_config['limit'])
        {
            $obj->limit($orm_config['limit']);
        }

        if ($orm_config['return'] == OOP_ORM::PARAM_RETURN_SINGLE)
        {
            # 获取单条数据
            $data = $obj->find()->current();
        }
        elseif ($orm_config['return'] == OOP_ORM::PARAM_RETURN_GROUP)
        {
            # 获取一组数据
            $data = $data = $obj->find();
        }
        else
        {
            # 当前ORM对象
            $data = $obj;
        }

        if (isset($orm_config['cache']))
        {
            # 设置缓存
            OOP_ORM_Parse::set_orm_cache_data($data_obj, $index, $c_config, $data);
        }

        return $data;
    }

    /**
     * 根据offset及配额获取缓存数据（如果有）
     *
     * @param string $index
     * @param array $config
     * @return mixed
     */
    public static function get_orm_cache_data(OOP_ORM_Data $data_obj, $key, $config)
    {
        # 获取一个key
        $cache_key = OOP_ORM_Parse::get_orm_cache_key($data_obj, $key, $config);

        $cache = new Cache($config['cache']['driver_config']);

        return $cache->get($cache_key);
    }

    /**
     * 根据index获取一个唯一的缓存key
     *
     * @param string $index
     * @return string
     */
    public static function get_orm_cache_key(OOP_ORM_Data $data_obj , $key , $config)
    {
        return '_ORM_CACHE_'. get_class($data_obj) .'_'. $key .'_'. md5(serialize($config));
    }


    /**
     * 根据offset及配额设置缓存数据
     *
     * @param string $index
     * @param array $config
     * @param data
     *
     * @return boolean
     */
    public static function set_orm_cache_data(OOP_ORM_Data $data_obj, $key, $config, $data)
    {
        # 获取一个key
        $cache_key = OOP_ORM_Parse::get_orm_cache_key($data_obj, $key, $config);

        $cache = new Cache($config['cache']['driver_config']);

        return $cache->set($cache_key, $data, isset($config['cache']['expire'])?$config['cache']['expire']:3600, $config['cache']['expire_type']);
    }


    protected static function _get_orm_group_data(OOP_ORM $obj, OOP_ORM_Data $data_obj, $orm_config, $index)
    {
        $group_data = $data_obj->get_group_data();
        if ($group_data)
        {
            foreach ($orm_config['mapping'] as $k => $v)
            {
                $tmpdata = array(); //记录临时数据
                foreach ($group_data as $item)
                {
                    # 当前字段需要符合的条件
                    $iswhere = true;
                    if (isset($orm_config['where']))
                    {
                        foreach ($orm_config['where'] as $wk => $wv)
                        {
                            if ($item->$wk != $wv)
                            {
                                $iswhere = false;
                            }
                        }
                    }
                    if ($iswhere)
                    {
                        $tmpdata[] = $item->$v;
                    }
                }

                $tmpdata = array_unique($tmpdata);
                if ($tmpdata)
                {
                    $obj->in($k, $tmpdata);
                }
            }

            if ($tmpdata)
            {
                # 如果有临时数据，则表明可以进行批量获取
                if (isset($orm_config['where']))foreach ($orm_config['where'] as $k => $v)
                {
                    # 额外的一些条件
                    $obj->where($k, $v);
                }
                $all_data = $obj->find();
            }
            else
            {
                $all_data = array();
            }

            # 整理data，将数据放在一个以mapping条件组成的key数组里
            $tmp = array();
            foreach ($all_data as $item)
            {
                $tmpkey = '';
                foreach ($orm_config['mapping'] as $k => $v)
                {
                    $tmpkey .= '|' . $k . '_' . $item->$k;
                }
                $tmp[$tmpkey] = $item;
            }

            # 将获取来的数据赋值给各个对象
            foreach ($group_data as $item)
            {
                $tmpkey = '';
                foreach ($orm_config['mapping'] as $k => $v)
                {
                    $tmpkey .= '|' . $k . '_' . $item->$v;
                }
                if ($item===$data_obj)
                {
                    # 当前对象
                    $data = isset($tmp[$tmpkey]) ? $tmp[$tmpkey] : null;
                }
                else
                {
                    $item->$index = isset($tmp[$tmpkey]) ? $tmp[$tmpkey] : null;
                }
            }

            if (isset($data))
            {
                # 已经获取到当前对象，直接返回
                return $data;
            }
        }

        return true;
    }

    /**
     * TODO 获取缓存数据
     */
    public static function get_cache_data($key, $where, $config)
    {
        return false;
    }

    /**
     * TODO 设置缓存数据
     */
    public static function set_cache_data($key, $where, $config, $data)
    {
        return false;
    }

    /**
     * 根据offset及配额获取缓存数据（如果有）
     *
     * @param string $index
     * @param array $config
     * @return fiexd
     */
    public static function get_offset_cache_data(OOP_ORM_Data $data_obj, $key, $cache_config)
    {
        # 获取一个key
        $cache_key = OOP_ORM_Parse::get_offset_cache_key($data_obj, $key);

        $cache = new Cache($cache_config['driver_config']);

        return $cache->get($cache_key);
    }


    /**
     * 根据offset及配额设置缓存数据
     *
     * @param string $index
     * @param array $config
     * @param data
     *
     * @return boolean
     */
    public static function set_offset_cache_data(OOP_ORM_Data $data_obj, $key, $cache_config, $data)
    {
        # 获取一个key
        $cache_key = OOP_ORM_Parse::get_offset_cache_key($data_obj, $key);

        $cache = new Cache($cache_config['driver_config']);

        return $cache->set($cache_key, $data, isset($cache_config['expire'])?$cache_config['expire']:3600, $cache_config['expire_type']);
    }


    /**
     * 根据index获取一个唯一的缓存key
     *
     * @param string $index
     * @return string
     */
    public static function get_offset_cache_key(OOP_ORM_Data $data_obj, $key)
    {
        $id_field_name = $data_obj->id_field_name();
        $id_value      = $data_obj->$id_field_name;

        return '_ORM_OFFSET_CACHE_'. get_class($data_obj) .'_'. $id_value .'_'. $key;
    }


    /**
     * 根据配置获取数据
     *
     * @param array $config
     */
    public static function get_data($config, $obj)
    {
        # 处理mapping
        if (isset($config['mapping']))
        {
            foreach ($config['mapping'] as $k=>$v)
            {
                $config['where'][$k] = $obj->$v;
            }
        }

        $fun = '_get_data_'.$config['driver'];

        return OOP_ORM_Parse::$fun($config, $obj);
    }

    /**
     * 获取数据库配置类型的数据
     *
     * @param array $config
     * @return array
     */
    protected static function _get_data_database($config, $obj)
    {
        $data = new Database($config['database']);
        $data = $data->from($config['tablename']);

        if (isset($config['where']) && $config['where'])
        {
            $data->where($config['where']);
        }
        if (isset($config['limit']) && $config['limit'])
        {
            $data->limit($config['limit']);
        }
        if (isset($config['offset']) && $config['offset'])
        {
            $data->offset($config['offset']);
        }
        if (isset($config['order_by']) && $config['order_by'])
        {
            foreach ($config['order_by'] as $k => $v)
            {
                $data->order_by($k, $v == 'DESC' ? 'DESC' : 'ASC');
            }
        }

        $result = $data->get();
        if (isset($config['result']) && $config['result'] = 'single')
        {
            $result = $result->current();
        }
        else
        {
            if (isset($config['as_array']))
            {
                $result = $result->as_array($config['as_array'][0], $config['as_array'][1]);
            }
            else
            {
                $result = $result->as_array();
            }
        }

        return $result;
    }

    /**
     * 获取function配置类型的数据
     *
     * @param array $config
     * @return array
     */
    protected static function _get_data_function($config, $obj)
    {
        $call_fun = $config['function'];
        $args     = $config['arguments'];

        if (!is_array($args))
        {
            $args = array();
        }

        if (is_array($call_fun))
        {
            # 对$this做特殊处理
            if (is_string($call_fun[0]) && strtolower($call_fun[0])=='$this')
            {
                $call_fun[0] = $obj;
            }
            return call_user_func_array($call_fun, $args);
        }
        else
        {
            return call_user_func($call_fun, $args);
        }
    }

    /**
     * 获取HttpClient配置类型的数据
     *
     * @param array $config
     * @return array
     */
    protected static function _get_data_httpget($config, $obj)
    {
        if (isset($config['type']))
        {
            $http_client = HttpClient::factory($config['type']);
        }
        else
        {
            $http_client = HttpClient::factory();
        }

        if (isset($config['method']) && strtolower($config['method'])=='post')
        {
            if (isset($config['timeout']))
            {
                return $http_client->post($config['url'], $config['arguments'], $config['timeout']);
            }
            else
            {
                return $http_client->post($config['url'], $config['arguments']);
            }
        }
        else
        {
            $config['url'] .= (strpos($config['url'], '?')?'&':'?') . http_build_query($config['arguments'], '', '&');
            if (isset($config['timeout']))
            {
                return $http_client->get($config['url'], $config['timeout']);
            }
            else
            {
                return $http_client->get($config['url']);
            }
        }
    }
}