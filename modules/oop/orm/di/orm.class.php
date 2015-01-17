<?php

/**
 * MyQEE ORM 处理ORM类型字段DI控制器
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Module
 * @package    ORM
 * @copyright  Copyright (c) 2008-2015 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class OOP_ORM_DI_ORM extends OOP_ORM_DI
{
    /**
     * 格式化配置
     *
     * @throws Exception
     */
    public function format_config()
    {
        if (isset($this->config['name']) && is_string($this->config['name']))
        {
            $this->config['orm'] = $this->config['name'];
            unset($this->config['name']);
        }

        if (isset($this->config['orm']['return']))
        {
            # 兼容2.0版本的配置
            OOP_ORM_DI_ORM::_check_for_v2_config($this->config);
        }

        if (!$this->config['orm'])
        {
            Core::debug()->info($this->key, 'orm key');
            Core::debug()->warn($this->config, 'orm config');

            throw new Exception('Unknown ORM');
        }

        # 设置为ORM时，data, object, format 配置将无效
        unset($this->config['data'], $this->config['object'], $this->config['format']);

        if (isset($this->config['field']))
        {
            $this->config['field_name'] = $this->config['field'];
            unset($this->config['field']);
        }

        if (!isset($this->config['field_name']))
        {
            # 标记为虚拟字段
            $this->config['is_virtual'] = true;

            # 没有字段也无法绑定
            unset($this->config['bind']);
        }

        # 统一设置
        if (!isset($this->config['mapping']) || !is_array($this->config['mapping']))
        {
            $this->config['mapping'] = array();
        }

        if (!isset($this->config['where']) || !is_array($this->config['where']))
        {
            $this->config['where'] = array();
        }
    }

    /**
     * 构造数据
     *
     * @param OOP_ORM_Data $obj
     * @param $data
     * @param $compiled_data
     * @return bool
     */
    public function & get_data(OOP_ORM_Data $obj, & $data, & $compiled_data, & $compiled_raw_data)
    {
        $compiled_data[$this->key] = $compiled_raw_data[$this->key] = $this->get_orm_data($obj);

        return $compiled_data[$this->key];
//
//
//        # 从缓存中获取
//        if (isset($this->config['cache']))
//        {
//            # 获取一个key
//            $cache_key = $this->get_cache_key();
//            $cache_obj = new Cache($this->config['cache']['config']);
//            $tmp_data  = $cache_obj->get($cache_key);
//
//            if ($tmp_data && is_object($tmp_data) && $tmp_data instanceof OOP_ORM_Data && $tmp_data->__orm_callback('check_sleep_data'))
//            {
//                $compiled_data[$this->key] = $tmp_data;
//                return true;
//            }
//        }
//
//        $compiled_data[$this->key] = $this->get_orm_data($obj);
//
//        if (isset($this->config['cache']))
//        {
//            # 设置缓存
//            $this->set_cache_data($cache_obj, $cache_key, $compiled_data[$this->key]);
//        }
//
//        return $compiled_data[$this->key];
    }

    /**
     * 获取当前类型的数据
     *
     * @param OOP_ORM_Data $obj
     * @return bool|mixed|null|OOP_ORM_Finder_DB|OOP_ORM_Result
     * @throws Exception
     */
    protected function get_orm_data(OOP_ORM_Data $obj)
    {
        $orm_config = $this->config();
        $finder     = ORM($orm_config['orm']);
        $rs         = null;

        switch ($orm_config['type'])
        {
            case OOP_ORM::PARAM_TYPE_O2O:

                $rs = $this->get_o2o_data($finder, $obj);
//
//                $st = array
//                (
//                    'type'             => 'orm',
//                    'class_name'       => $this->class_name,
//                    'key'              => $this->key,
//                    'config'           => $this->config(),
//                    'parent_group_ids' => $obj->__orm_callback('get_parent_group_ids'),
//                );
//
//                $data = array();
//                foreach($this->config['mapping'] as $k0 => $k)
//                {
//                    $data[$k0] = $obj->$k;
//                }
//
//                # 获取字段名
//                if (isset($this->config['bind']) && $bind = $this->config['bind'])
//                {
//                    $data[$bind] = $obj->get_data_by_field_name($this->field_name, true);
//                }
//
//                $rs = $orm_obj->create($data, true, null, $st);

//
//                if ($group_ids = $obj->__orm_callback('get_group_ids'))
//                {
//                    $rs->__orm_callback('add_parent_group_id', $group_ids);
//                }


                break;
            case OOP_ORM::PARAM_TYPE_O2F:
                $rs = $finder;

                break;
            case OOP_ORM::PARAM_TYPE_O2M:
                $db = $finder->driver();

                OOP_ORM_DI_ORM::_set_query_info($obj, $db, $orm_config);

                $rs = $finder->find();

                break;
        }

        return $rs;
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
        if ($has_compiled && $this->config['is_readonly'])
        {
            # 只读字段
            return false;
        }

        if ($has_compiled && $new_value instanceof OOP_ORM_Data && isset($compiled_raw_data[$this->key]) && ($c = $compiled_raw_data[$this->key]) && $c instanceof OOP_ORM_Data)
        {
            if (($pk = $c->pk()) && $pk === $new_value->pk())
            {
                # 主键相同，则不需要更新
                return true;
            }
        }

        # 增加父对象组ID
        if ($new_value instanceof OOP_ORM_Data && ($group_ids = $obj->__orm_callback('get_group_ids')))
        {
            $new_value->__orm_callback('add_parent_group_id', $group_ids);
        }

        $compiled_data[$this->key] = $new_value;

        if (!$has_compiled)
        {
            $compiled_raw_data[$this->key] = $compiled_data[$this->key];
        }

        return true;
    }


    /**
     * 获取延迟数据
     *
     * @return bool
     */
    protected function get_o2o_data(OOP_ORM $finder, OOP_ORM_Data $obj)
    {
        $orm_config = $this->config();

        if (!$orm_config['mapping'] && !$orm_config['where'] && !isset($orm_config['bind']))
        {
            if (IS_DEBUG)
            {
                Core::debug()->warn($orm_config, 'error orm setting');
                throw new Exception($this->class_name .'->'. $this->key .' 获取延迟数据设置异常，缺失mapping或where或bind条件');
            }

            return null;
        }

        if ($obj->__orm_callback('get_group_ids') && OOP_ORM_DI_ORM::_check_can_get_batch($orm_config))
        {
            # 批量获取数据
            return $this->_get_o2o_data_batch_type($finder, $obj);
        }
        else
        {
            return $this->_get_o2o_data_single_type($finder, $obj);
        }
    }

    /**
     * 批量获取数据
     *
     * @param OOP_ORM $finder
     * @param OOP_ORM_Data $obj
     * @return null|OOP_ORM_Data
     */
    protected function _get_o2o_data_batch_type(OOP_ORM $finder, OOP_ORM_Data $obj)
    {
        $config     = $this->config();
        $mapping    = $config['mapping'];
        $where      = $config['where'];
        $bind       = isset($config['bind']) ? $config['bind'] : null;
        $bind_field = $this->field_name;
        $group      = array();

        if($group_ids = $obj->__orm_callback('get_group_ids'))
        {
            $obj_class = get_class($obj);
            $next      = true;               // 用来判断使得要跳出while循环
            $offset    = 0;                  // 起始位置
            $limit     = 100;                // 单组批量获取数，这样可以避免某些组数量特别多时导致异常问题

            while ($next)
            {
                $found_count = 0;
                foreach ($group_ids as $group_id)
                {
                    $tmp_group_data = OOP_ORM_Result::get_data_by_group_id($group_id, $offset, $limit);
                    $found_count   += count($tmp_group_data);

                    foreach($tmp_group_data as $item)
                    {
                        if (get_class($item) !== $obj_class)
                        {
                            # 不是相同类型的对象
                            continue;
                        }

                        if ($item === $obj)
                        {
                            # 同一个对象
                            $next = false;
                        }

                        if ($item->__orm_callback('is_compiled', $this->key))
                        {
                            # 已经构造过的数据忽略
                            continue;
                        }

                        if ($found_count > $limit)
                        {
                            $next = false;
                        }

                        $group[] = $item;

                        /**
                         * @var $batch_where OOP_ORM_Data
                         */
//                        $tmp = $item->$key;
//
//                        if ($tmp===$obj)
//                        {
//                            # 找到了对象自己，则标记为不用再继续寻找
//                            $find = 0;
//                        }
//
//                        if ($tmp && ($tmp===$obj || $tmp->__orm_callback('get_delay_setting')))
//                        {
//                            $tmp_delay_setting = $tmp->__orm_callback('get_delay_setting');
//
//                            if (isset($tmp_delay_setting['config']['cache']))
//                            {
//                                // 缓存配置
//                                list($cache, $cache_key, $data) = OOP_ORM_DI_ORM::_get_cache_data($tmp, $tmp_delay_setting);
//
//                                if ($data)
//                                {
//                                    continue;
//                                }
//
//                                $tmp_fn = array($cache, $cache_key, $tmp_delay_setting['config']['cache']);
//                            }
//                            else
//                            {
//                                $tmp_fn = null;
//                            }
//
//                            $cache_fns[]  = $tmp_fn;
//                            $group_data[] = $item;
//                        }
                    }
                }

                if (!$found_count)
                {
                    # 没有可用的返回数
                    break;
                }

                $offset += $limit;
            }
        }

        if (!$group)
        {
            # 没有获取到任何组，则采样单个获取的方法
            return $this->_get_o2o_data_single_type($finder, $obj);
        }

        $batch_where = array();

        foreach($group as $item)
        {
            /**
             * @var $item OOP_ORM_Data
             */
            foreach($mapping as $k => $v)
            {
                $batch_where[$k][] = $item->$v;
            }

            foreach($where as $k => $v)
            {
                $batch_where[$k][] = $v;
            }

            if ($bind)
            {
                $batch_where[$bind][] = $item->get_data_by_field_name($bind_field, true);
            }
        }


        # 对数据进行去重处理
        $batch_where = array_map('array_unique', $batch_where);

        $return = null;

        if (1 === count($batch_where))
        {
            # 只有1个条件

            $c_key = key($batch_where);         //当前字段名
            $in    = current($batch_where);     //获取where条件
            $rs    = array();
            $g     = $finder->in($c_key, $in)->find();
            $obj->__orm_callback('set_key_batch_orm_group', $g);

            foreach ($g as $item)
            {
                $k = $item->get_data_by_field_name($c_key, true);

                $item->__orm_callback('add_parent_group_id', $group_ids);

                $rs[$k] = $item;
            }

            foreach ($group as $item)
            {
                if ($mapping)
                {
                    $v = current($mapping);
                    $k = $item->$v;
                }
                elseif ($where)
                {
                    $k = current($where);
                }
                elseif ($bind)
                {
                    $k = $item->get_data_by_field_name($bind_field, true);
                }
                else
                {
                    $k = null;
                }

                if (!isset($rs[$k]))
                {
                    $rs[$k] = null;
                }

                if ($item === $obj)
                {
                    $return = $rs[$k];
                }
                else
                {
                    # 通过回调设置ORM批量获取的数据
                    $item->__orm_callback('set_batch_orm_data', $this->key, $rs[$k]);
                }
            }


            /*
            foreach($group_data as $i => $item)
            {
                if ($m_key)
                {
                    $k = $item->$m_key;
                }
                elseif ($bind)
                {
                    $k = $item->get_data_by_field_name($bind_field, true);
                }
                elseif ($where)
                {
                    $k = current($where);
                }
                else
                {
                    continue;
                }

                if (!isset($rs[$k]))
                {
                    # 有可能数据库中没有对应的数据
                    $rs[$k] = array();
                }

                if ($cache_fns[$i])
                {
                    # 先处理缓存
                    list($cache, $cache_key, $cache_config) = $cache_fns[$i];
                    /**
                     * @var $cache Cache
                     /
                    $cache->set($cache_key, $rs[$k], $cache_config['expire'], isset($cache_config['expire_type'])?$cache_config['expire_type'] : null);
                }
            }
            */

        }
        else
        {
            foreach($batch_where as $k => $v)
            {
                $finder->in($k, $v);
                $finder->group_by($k);
            }

            # 组织数据
            $rs = array();
            foreach ($finder->find() as $item)
            {
                /**
                 * @var $item OOP_ORM_Data
                 */
                $k = '';

                foreach($mapping as $m_k => $v)
                {
                    $k .= ','. $item->$m_k;
                }

                foreach($where as $m_value)
                {
                    $k .= ','. $m_value;
                }

                if ($bind)
                {
                    $k .= ','. $item->get_data_by_field_name($bind, true);
                }

                if (!isset($rs[$k]))$rs[$k] = $item;
            }

            foreach($group as $i => $item)
            {
                $k = '';
                foreach($mapping as $m_key)
                {
                    $k .= ','. $item->$m_key;
                }

                foreach($where as $m_value)
                {
                    $k .= ','. $m_value;
                }

                if ($bind)
                {
                    $k .= ','. $item->get_data_by_field_name($bind_field, true);
                }

                if (!isset($rs[$k]))
                {
                    # 有可能数据库中没有对应的数据
                    $rs[$k] = null;
                }

                if ($item === $obj)
                {
                    $return = $rs[$k];
                }
                else
                {
                    # 通过回调设置ORM批量获取的数据
                    $item->__orm_callback('set_batch_orm_data', $this->key, $rs[$k]);
                }


//                if ($cache_fns[$i])
//                {
//                    # 先处理缓存
//                    list($cache, $cache_key, $cache_config) = $cache_fns[$i];
//                    /**
//                     * @var $cache Cache
//                     */
//                    $cache->set($cache_key, $rs[$k], $cache_config['expire'], isset($cache_config['expire_type'])?$cache_config['expire_type'] : null);
//                }
            }

        }

        return $return;
    }


    /**
     * 获取单独数据
     *
     * @param OOP_ORM $finder
     * @param OOP_ORM_Data $obj
     * @return null|OOP_ORM_Data
     */
    protected function _get_o2o_data_single_type(OOP_ORM $finder, OOP_ORM_Data $obj)
    {
        $config = $this->config();

        if (isset($config['cache']))
        {
            list($cache, $cache_key, $data) = $this->_get_cache_data($obj);
        }
        else
        {
            $data = null;
        }

        if (!$data)
        {
            OOP_ORM_DI_ORM::_set_query_info($obj, $finder, $config);

            $data = $finder->find()->current();

            if ($data && isset($cache) && isset($cache_key))
            {
                /**
                 * @var $cache Cache
                 */
                $cache->set($cache_key, $data, $config['cache']['expire'], isset($config['cache']['expire_type']) ? $config['cache']['expire_type'] : null);
            }
        }

        return $data;
    }

    /**
     * 获取缓存数据
     *
     * @param OOP_ORM_Data $obj
     * @param $orm_config
     * @return array array($cache, $cache_key, $data)
     */
    protected function _get_cache_data(OOP_ORM_Data $obj)
    {
        $config = $this->config();

        if (isset($config['cache']))
        {
            # 有缓存配置
            $orm_config_copy = $config;

            if (is_array($orm_config_copy['mapping']))foreach($orm_config_copy['mapping'] as $key => $value)
            {
                $orm_config_copy['mapping'][$key] = $obj->$key;
            }
            unset($orm_config_copy['cache']);

            # 根据配置生成一个key
            asort($orm_config_copy);

            $cache_key = '_orm_cache_'. md5(var_export($orm_config_copy, true));

            # 缓存对象
            $cache = Cache::instance(isset($config['cache']['config']) ? $config['cache']['config'] : null);

            $data = $cache->get($cache_key);

            if (IS_DEBUG)
            {
                Core::debug()->info('orm '. get_class($obj) .'->'. $this->key .' found by cache, item pk is '. $data->pk());
            }

            return array($cache, $cache_key, $data);
        }
        else
        {
            return array(null, null, null);
        }
    }

    /**
     * 针对V2版本的修正参数
     */
    private static function _check_for_v2_config(& $config)
    {
        switch ($config['orm']['return'])
        {

            case OOP_ORM::PARAM_RETURN_GROUP:
                $config['type'] = OOP_ORM::PARAM_TYPE_O2M;
                break;
            case OOP_ORM::PARAM_RETURN_FINDER:
                $config['type'] = OOP_ORM::PARAM_TYPE_O2F;
                break;
            default:
                $config['type'] = OOP_ORM::PARAM_TYPE_O2O;
                break;
        }

        if (isset($config['orm']['cache']))
        {
            $config['cache'] = $config['orm']['cache'];
            if (isset($config['cache']['driver_config']))
            {
                $config['cache']['config'] = $config['cache']['driver_config'];
                unset($config['cache']['driver_config']);
            }
        }

        $config['orm'] = $config['orm']['name'];
    }

    protected static function _check_can_get_batch($orm_config)
    {
        $get_batch_data = true;

        if (isset($orm_config['order_by']))
        {
            $get_batch_data = false;
        }
        elseif (isset($orm_config['offset']) && $orm_config['offset'] > 0)
        {
            # OFFSET
            $get_batch_data = false;
        }
        elseif (isset($orm_config['group_by']))
        {
            # GROUP BY
            $get_batch_data = false;
        }
        elseif (isset($orm_config['order_by']))
        {
            # ORDER BY
            $get_batch_data = false;
        }
        elseif (isset($orm_config['in']))
        {
            # IN
            $get_batch_data = false;
        }
        elseif ($orm_config['limit'])
        {
            # LIMIT
            $get_batch_data = false;
        }
        elseif ($orm_config['like'])
        {
            # LIKE
            $get_batch_data = false;
        }
        elseif ($orm_config['having'])
        {
            # HAVING
            $get_batch_data = false;
        }
        elseif ($orm_config['other'])
        {
            # 其它任意
            $get_batch_data = false;
        }

        return $get_batch_data;
    }

    protected static function _set_query_info(OOP_ORM_Data $obj, OOP_ORM $finder, $orm_config)
    {
        # WHERE
        if ($orm_config['where'])
        {
            $finder->where($orm_config['where']);
        }

        # MAPPING
        if ($orm_config['mapping'])
        {
            foreach($orm_config['mapping'] as $k => $v)
            {
                $finder->where($k, $obj->$v);
            }
        }

        # 绑定数据
        if (isset($orm_config['bind']) && $orm_config['bind'])
        {
            $finder->where($orm_config['bind'], $obj->get_data_by_field_name($orm_config['bind'], true));
        }

        if (isset($orm_config['order_by']) && $orm_config['order_by'])
        {
            foreach($orm_config['order_by'] as $k => $v)
            {
                $finder->order_by($k, $v);
            }
        }

        # OFFSET
        if (isset($orm_config['offset']) && $orm_config['offset'] > 0)
        {
            $finder->offset($orm_config['offset']);
        }

        # GROUP BY
        if (isset($orm_config['group_by']) && $orm_config['group_by'])
        {
            foreach ($orm_config['group_by'] as $item)
            {
                $finder->group_by($item);
            }
        }

        # ORDER BY
        if (isset($orm_config['order_by']) && $orm_config['order_by'])
        {
            foreach ($orm_config['order_by'] as $key => $item)
            {
                $finder->order_by($key, $item);
            }
        }

        # IN
        if (isset($orm_config['in']) && $orm_config['in'])
        {
            foreach ($orm_config['in'] as $key => $item)
            {
                $finder->in($key, $item);
            }
        }

        # LIMIT
        if (isset($orm_config['limit']) && $orm_config['limit'])
        {
            $finder->limit($orm_config['limit']);
        }

        # LIKE
        if (isset($orm_config['like']) && $orm_config['like'])
        {
            $finder->like($orm_config['like']);
        }

        # HAVING
        if (isset($orm_config['having']) && $orm_config['having'])
        {
            $finder->having($orm_config['having']);
        }

        # 支持扩展所有的方法
        if (isset($orm_config['other']) && $orm_config['other'])
        {
            foreach ($orm_config['other'] as $argv)
            {
                $k = array_pop($argv);
                call_user_func_array(array($finder, $k), $argv);
            }
        }
    }
}