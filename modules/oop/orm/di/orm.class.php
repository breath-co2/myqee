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
            if (OOP_ORM::PARAM_RETURN_GROUP === $this->config['orm']['return'])
            {
                $this->config['type'] = OOP_ORM::PARAM_TYPE_O2M;
            }
            if (OOP_ORM::PARAM_RETURN_FINDER === $this->config['orm']['return'])
            {
                $this->config['type'] = OOP_ORM::PARAM_TYPE_O2F;
            }
            else
            {
                $this->config['type'] = OOP_ORM::PARAM_TYPE_O2O;
            }

            if (isset($this->config['orm']['cache']))
            {
                $this->config['cache'] = $this->config['orm']['cache'];
                if (isset($this->config['cache']['driver_config']))
                {
                    $this->config['cache']['config'] = $this->config['cache']['driver_config'];
                    unset($this->config['cache']['driver_config']);
                }
            }

            $this->config['orm'] = $this->config['orm']['name'];
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
    public function & get_data(OOP_ORM_Data $obj, & $data, & $compiled_data, & $compiled_raw_data, & $delay_setting)
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
        $orm_config = $this->config;
        $orm_obj    = ORM($orm_config['orm']);
        $rs         = null;

        switch ($orm_config['type'])
        {
            case OOP_ORM::PARAM_TYPE_O2O:

                $st = array
                (
                    'type'             => 'orm',
                    'class_name'       => $this->class_name,
                    'key'              => $this->key,
                    'config'           => $this->config(),
                    'parent_group_ids' => $obj->__orm_callback('get_parent_group_ids'),
                );

                $data = array();
                foreach($this->config['mapping'] as $k0 => $k)
                {
                    $data[$k0] = $obj->$k;
                }

                # 获取字段名
                if (isset($this->config['bind']) && $bind = $this->config['bind'])
                {
                    $data[$bind] = $obj->get_data_by_field_name($this->field_name, true);
                }

                $rs = $orm_obj->create($data, true, null, $st);

                if ($group_ids = $obj->__orm_callback('get_group_ids'))
                {
                    $rs->__orm_callback('add_parent_group_id', $group_ids);
                }

                break;
            case OOP_ORM::PARAM_TYPE_O2M:

                foreach($this->config['mapping'] as $k0 => $k)
                {
                    $orm_obj->where($k0, $obj->$k);
                }

                foreach($this->config['where'] as $k0 => $k)
                {
                    $orm_obj->where($k0, $k);
                }

                $rs = $orm_obj->find();
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
    public static function get_delay_data(OOP_ORM_Data $obj, $delay_setting = null)
    {
        if (null === $delay_setting)
        {
            $delay_setting = $obj->__orm_callback('get_delay_setting');
        }

        if (null === $delay_setting)return false;

        $orm_config = $delay_setting['config'];

        if (!$orm_config['mapping'] && !$orm_config['where'] && !isset($orm_config['bind']))
        {
            if (IS_DEBUG)
            {
                Core::debug()->warn($delay_setting, 'error delay_setting');
                throw new Exception($delay_setting['class_name'] .'->'. $delay_setting['key'] .' 获取延迟数据设置异常，缺失mapping或where条件');
            }
            return false;
        }

        $get_batch_data = true;

        if (!$delay_setting['parent_group_ids'])
        {
            $get_batch_data = false;
        }
        elseif ($orm_config['type'] !== OOP_ORM::PARAM_TYPE_O2O)
        {
            $get_batch_data = false;
        }
        elseif (isset($orm_config['order_by']))
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

        if ($get_batch_data)
        {
            # 批量获取数据
            return OOP_ORM_DI_ORM::_get_batch_delay_data($obj, $delay_setting);
        }
        else
        {
            return OOP_ORM_DI_ORM::_get_single_delay_data($obj, $delay_setting);
        }
    }

    /**
     * 批量获取延迟数据
     *
     * @param OOP_ORM_Data $obj
     * @param $delay_setting
     * @return bool
     */
    protected static function _get_batch_delay_data(OOP_ORM_Data $obj, $delay_setting)
    {
        $key        = $delay_setting['key'];
        $mapping    = $delay_setting['config']['mapping'];
        $where      = $delay_setting['config']['where'];
        $bind       = isset($delay_setting['config']['bind']) ? $delay_setting['config']['bind'] : null;
        $bind_field = isset($delay_setting['config']['field_name']) ? $delay_setting['config']['field_name'] : null;


        $cache_fns = array();
        # 尝试批量获取，提高语句查询性能
        $group_data = array();
        if($delay_setting['parent_group_ids'])
        {
            $find   = 1;        // 用来判断使得要跳出while循环
            $offset = 0;        // 起始位置
            $limit  = 100;      // 单组批量获取数，这样可以避免某些组数量特别多时导致异常问题

            while ($find)
            {
                $found_count = 0;
                foreach ($delay_setting['parent_group_ids'] as $group_id)
                {
                    $tmp_group_data = OOP_ORM_Result::get_data_by_group_id($group_id, $offset, $limit);
                    $found_count += count($tmp_group_data);

                    foreach($tmp_group_data as $item)
                    {
                        /**
                         * @var $tmp OOP_ORM_Data
                         */
                        $tmp = $item->$key;

                        if ($tmp===$obj)
                        {
                            # 找到了对象自己，则标记为不用再继续寻找
                            $find = 0;
                        }

                        if ($tmp && ($tmp===$obj || $tmp->__orm_callback('get_delay_setting')))
                        {
                            $tmp_delay_setting = $tmp->__orm_callback('get_delay_setting');

                            if (isset($tmp_delay_setting['config']['cache']))
                            {
                                // 缓存配置
                                list($cache, $cache_key, $data) = OOP_ORM_DI_ORM::_get_cache_data($tmp, $tmp_delay_setting);

                                if ($data)
                                {
                                    $item->$key->__orm_callback('set_delay_data', $data);

                                    continue;
                                }

                                $tmp_fn = array($cache, $cache_key, $tmp_delay_setting['config']['cache']);
                            }
                            else
                            {
                                $tmp_fn = null;
                            }

                            $cache_fns[]  = $tmp_fn;
                            $group_data[] = $item;
                        }
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

        if (!$group_data)
        {
            # 没有获取到任何内容
            return true;
        }

        $tmp = array();
        foreach($group_data as $item)
        {
            foreach($mapping as $k => $v)
            {
                /**
                 * @var $item OOP_ORM_Data
                 */
                $tmp[$k][] = $item->$v;
            }

            foreach($where as $k => $v)
            {
                $tmp[$k][] = $v;
            }

            if ($bind)
            {
                $tmp[$bind][] = $item->get_data_by_field_name($bind_field, true);
            }
        }

        # 对数据进行去重处理
        $tmp   = array_map('array_unique', $tmp);
        $db    = $obj->finder()->driver();
        $table = $obj->table_name();

        if (1 === count($tmp))
        {
            # 只有1个条件
            $c_key = key($tmp);               //当前字段名
            $in    = current($tmp);           //获取where条件

            if (isset($mapping[$c_key]))
            {
                $m_key = $mapping[$c_key];    //对象字段名
            }
            else
            {
                $m_key = null;
            }

            # 查询条件
            $rs = array();
            foreach ($db->from($table)->in($c_key, $in)->get() as $item)
            {
                $k = $item[$c_key];
                if (!isset($rs[$k]))$rs[$k] = $item;
            }

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
                     */
                    $cache->set($cache_key, $rs[$k], $cache_config['expire'], isset($cache_config['expire_type'])?$cache_config['expire_type']:null);
                }

                $item->$key->__orm_callback('set_delay_data', $rs[$k]);
            }
        }
        else
        {
            foreach($tmp as $k => $v)
            {
                $db->group_by($k);
                $db->in($k, $v);
            }

            # 组织数据
            $rs = array();
            foreach ($db->from($table)->get() as $item)
            {
                $k = '';

                foreach($mapping as $m_k => $v)
                {
                    $k .= ','. $item[$m_k];
                }

                foreach($where as $m_value)
                {
                    $k .= ','. $m_value;
                }

                if ($bind)
                {
                    $k .= ','. $item[$bind];
                }

                if (!isset($rs[$k]))$rs[$k] = $item;
            }

            foreach($group_data as $i => $item)
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
                    $rs[$k] = array();
                }

                if ($cache_fns[$i])
                {
                    # 先处理缓存
                    list($cache, $cache_key, $cache_config) = $cache_fns[$i];
                    /**
                     * @var $cache Cache
                     */
                    $cache->set($cache_key, $rs[$k], $cache_config['expire'], isset($cache_config['expire_type'])?$cache_config['expire_type']:null);
                }

                $item->$key->__orm_callback('set_delay_data', $rs[$k]);
            }
        }

        return true;
    }


    /**
     * 获取单独数据
     *
     * @param OOP_ORM_Data $obj
     * @param $delay_setting
     * @return bool
     */
    protected static function _get_single_delay_data(OOP_ORM_Data $obj, $delay_setting)
    {
        if (isset($delay_setting['config']['cache']))
        {
            list($cache, $cache_key, $data) = OOP_ORM_DI_ORM::_get_cache_data($obj, $delay_setting);
        }
        else
        {
            $data = null;
        }

        if (!$data)
        {
            $db         = $obj->finder()->driver();
            $table      = $obj->table_name();
            $orm_config = $delay_setting['config'];
            $mapping    = $delay_setting['config']['mapping'];
            $where      = $delay_setting['config']['where'];
            $bind       = isset($delay_setting['config']['bind']) ? $delay_setting['config']['bind'] : null;

            # WHERE
            if ($where)
            {
                $db->where($where);
            }

            if ($mapping)
            {
                foreach($mapping as $k => $v)
                {
                    $db->where($k, $obj->$k);
                }
            }

            if ($bind)
            {
                $db->where($bind, $obj->get_data_by_field_name($bind, true));
            }

            if (isset($orm_config['order_by']) && $orm_config['order_by'])
            {
                foreach($orm_config['order_by'] as $k => $v)
                {
                    $db->order_by($k, $v);
                }
            }

            # OFFSET
            if (isset($orm_config['offset']) && $orm_config['offset'] > 0)
            {
                $db->offset($orm_config['offset']);
            }

            # GROUP BY
            if (isset($orm_config['group_by']) && $orm_config['group_by'])
            {
                foreach ($orm_config['group_by'] as $item)
                {
                    $db->group_by($item);
                }
            }

            # ORDER BY
            if (isset($orm_config['order_by']) && $orm_config['order_by'])
            {
                foreach ($orm_config['order_by'] as $key => $item)
                {
                    $db->order_by($key, $item);
                }
            }

            # IN
            if (isset($orm_config['in']) && $orm_config['in'])
            {
                foreach ($orm_config['in'] as $key => $item)
                {
                    $db->in($key, $item);
                }
            }

            # LIMIT
            if (isset($orm_config['limit']) && $orm_config['limit'])
            {
                $db->limit($orm_config['limit']);
            }

            # LIKE
            if (isset($orm_config['like']) && $orm_config['like'])
            {
                $db->like($orm_config['like']);
            }

            # HAVING
            if (isset($orm_config['having']) && $orm_config['having'])
            {
                $db->having($orm_config['having']);
            }

            # 支持扩展所有的方法
            if (isset($orm_config['other']) && $orm_config['other'])
            {
                foreach ($orm_config['other'] as $argv)
                {
                    $k = array_pop($argv);
                    call_user_func_array(array($db, $k), $argv);
                }
            }

            $data = $db->from($table)->get_single();

            if ($data && isset($cache) && isset($cache_key))
            {
                /**
                 * @var $cache Cache
                 */
                $cache->set($cache_key, $data, $orm_config['cache']['expire'], isset($orm_config['cache']['expire_type'])?$orm_config['cache']['expire_type']:null);
            }
        }

        $obj->__orm_callback('set_delay_data', $data);

        return true;
    }

    /**
     * 获取缓存数据
     *
     * @param OOP_ORM_Data $obj
     * @param $delay_setting
     * @return array array($cache, $cache_key, $data)
     */
    protected static function _get_cache_data(OOP_ORM_Data $obj, $delay_setting)
    {
        $orm_config = $delay_setting['config'];
        if (isset($orm_config['cache']))
        {
            # 有缓存配置
            $orm_config_copy = $orm_config;

            if (is_array($orm_config_copy['mapping']))foreach($orm_config_copy['mapping'] as $key => $value)
            {
                $orm_config_copy['mapping'][$key] = $obj->$key;
            }
            unset($orm_config_copy['cache']);

            # 根据配置生成一个key
            asort($orm_config_copy);

            $cache_key = '_orm_cache_'. md5(var_export($orm_config_copy, true));

            # 缓存对象
            $cache = Cache::instance(isset($orm_config['config']) ? $orm_config['config'] : null);

            $data = $cache->get($cache_key);

            if (IS_DEBUG)
            {
                Core::debug()->info($data, 'orm '. $delay_setting['class_name'] .'->'. $delay_setting['key'] .' found by cache');
            }

            return array($cache, $cache_key, $data);
        }
        else
        {
            return array(null, null, null);
        }
    }
}