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
        if ($this->config['orm'] && !is_array($this->config['orm']))
        {
            $this->config['orm'] = array
            (
                'name' => (string)$this->config['orm']['name']
            );
        }
        elseif(!isset($this->config['orm']['name']))
        {
            if (IS_DEBUG)Core::debug()->error($this->config, 'ORM字段:'.$this->key.'配置错误，data属性应该为数组');
            unset($this->config['orm']);
            return;
        }

        # 设置为ORM时，data, object, format 配置将无效
        unset($this->config['data'], $this->config['object'], $this->config['format'], $this->config['field_name']);

        # 标记为虚拟字段
        $this->config['is_virtual'] = true;
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
        $compiled_data[$this->key] = $this->get_orm_data($obj);

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

        if ($orm_config['name'])
        {
            # 映射ORM对象
            $obj_name = 'ORM_' . $orm_config['name'] . '_Finder';
            if (!class_exists($obj_name, true))
            {
                throw new Exception('指定的ORM:'. $orm_config['name'] .'不存在！');
            }

            /**
             * @var $orm_obj OOP_ORM_Finder_DB
             */
            $orm_obj = new $obj_name();
        }
        else
        {
            $orm_obj = new OOP_ORM_Finder_DB($this->config['table']);
        }

        $st = array
        (
            'type'             => 'orm',
            'class_name'       => $this->class_name,
            'key'              => $this->key,
            'config'           => $this->config(),
            'parent_group_ids' => $obj->__orm_callback('get_parent_group_ids'),
        );

        $data = array();
        if(is_array($this->config['mapping']))foreach($this->config['mapping'] as $k0 => $k)
        {
            $data[$k0] = $obj->$k;
        }

        $rs = null;
        switch ($orm_config['type'])
        {
            case OOP_ORM::PARAM_TYPE_O2O:
                $rs = $orm_obj->create($data, false);
                $rs->__orm_callback('set_delay_setting', $st);
                if ($group_ids = $obj->__orm_callback('get_group_ids'))
                {
                    $rs->__orm_callback('add_parent_group_id', $group_ids);
                }
                break;
            case OOP_ORM::PARAM_TYPE_O2M:
                $rs = $orm_obj->create_group_data(array());
                break;
        }

        return $rs;

        # 兼容老版本
        if (OOP_ORM::PARAM_RETURN_SINGLE == $orm_config['return'])
        {
            return $orm_obj->create($data, false);
        }
        elseif ($orm_config['return'] == OOP_ORM::PARAM_RETURN_GROUP)
        {
            # 获取一组数据
            return $orm_obj->create_group_data(array());
        }
        else
        {
            return $orm_obj;
        }
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
     * 根据offset及配额设置缓存数据
     *
     * @param string $index
     * @param array $config
     * @return mixed
     */
    protected function set_cache_data(Cache $cache_obj, $cache_key, $data)
    {
        # 设置缓存类型
        if (isset($this->config['cache']['expire']))
        {
            $expire = $this->config['cache']['expire'];
            $type   = Cache::TYPE_ADV_AGE;
        }
        elseif (isset($this->config['cache']['max_age']))
        {
            $expire = $this->config['cache']['expire'];
            $type   = Cache::TYPE_ADV_AGE;
        }
        elseif (isset($this->config['cache']['max_hit']))
        {
            $expire = $this->config['cache']['max_hit'];
            $type   = Cache::TYPE_ADV_HIT;
        }
        elseif (isset($this->config['cache']['renew_age']))
        {
            $expire = $this->config['cache']['renew_age'];
            $type   = Cache::TYPE_MAX_AGE;
        }
        elseif (isset($this->config['cache']['renew_hit']))
        {
            $expire = $this->config['cache']['renew_hit'];
            $type   = Cache::TYPE_MAX_HIT;
        }
        else
        {
            $expire = 3600;
            $type   = Cache::TYPE_ADV_AGE;
        }

        return $cache_obj->set($cache_key, $data, $expire, $type);
    }

    /**
     * 根据index获取一个唯一的缓存key
     *
     * @param string $index
     * @return string
     */
    protected function get_cache_key()
    {
        return strtoupper('_ORM_CACHE_'. $this->class_name .'_'. $this->key);
    }


    /**
     * 获取延迟数据
     *
     * @return bool
     */
    public static function get_delay_data(OOP_ORM_Data $obj, $delay_setting = null)
    {
        if (null===$delay_setting)
        {
            $delay_setting = $obj->__orm_callback('get_delay_setting');
        }
        if (null===$delay_setting)return false;

        $orm_config = $delay_setting['config'];
        $mapping    = $orm_config['mapping'];
        $where      = $orm_config['where'];

        if (!$mapping && !$where)
        {
            if (IS_DEBUG)throw new Exception('当前对象"'. get_class($obj) .'"获取延迟数据设置异常');
            return false;
        }

        $get_batch_data = true;

        if (!$delay_setting['parent_group_ids'])
        {
            $get_batch_data = false;
        }
        elseif ($orm_config['type']!==OOP_ORM::PARAM_TYPE_O2O)
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
        $key     = $delay_setting['key'];
        $mapping = $delay_setting['config']['mapping'];
        $table   = $delay_setting['config']['table'];

        # 尝试批量获取，提高语句查询性能
        $data = array();
        if($delay_setting['parent_group_ids'])foreach ($delay_setting['parent_group_ids'] as $group_id)
        {
            foreach(OOP_ORM_Result::get_data_by_group_id($group_id) as $item)
            {
                /**
                 * @var $tmp OOP_ORM_Data
                 */
                $tmp = $item->$key;
                if ($tmp && ($tmp===$obj || $tmp->__orm_callback('get_delay_setting')))
                {
                    $data[] = $item;
                }
            }
        }
        if (!$data)
        {
            return false;
        }

        $tmp = array();
        foreach($mapping as $k => $v)
        {
            foreach($data as $item)
            {
                /**
                 * @var $item OOP_ORM_Data
                 */
                $tmp[$k][] = $item->$v;
            }

            # 去重
            $tmp[$k] = array_unique($tmp[$k]);
        }

        $db = Database::instance();

        if (1===count($mapping))
        {
            # 只有1个map
            $skey  = key($tmp);               //当前对象字段名
            $pkey  = $mapping[$skey];         //服对象字段名
            $in    = current($tmp);           //获取where条件

            if ($delay_setting['config']['where'] && is_array($delay_setting['config']['where']))
            {
                # where条件
                $db->where($delay_setting['config']['where']);
            }

            $rs = array();
            foreach ($db->from($table)->in($skey, $in)->get() as $item)
            {
                $tkey = $item[$skey];
                if (!isset($rs[$tkey]))$rs[$tkey] = $item;
            }

            foreach($data as $item)
            {
                $k = $item->$pkey;
                if (isset($rs[$k]))
                {
                    $item->$key->__orm_callback('set_delay_data', $rs[$k]);
                }
            }
        }
        else
        {
            if ($delay_setting['config']['where'] && is_array($delay_setting['config']['where']))
            {
                # where条件
                $db->where($delay_setting['config']['where']);
            }

            foreach($tmp as $k => $v)
            {
                $db->group_by($k);
                $db->in($k, $v);
            }

            # 组织数据
            $rs = array();
            foreach ($db->from($table)->get() as $item)
            {
                $tkey = '';
                foreach($mapping as $k => $v)
                {
                    $tkey .= ','. $item[$k];
                }
                if (!isset($rs[$tkey]))$rs[$tkey] = $item;
            }

            foreach($data as $item)
            {
                $k = '';
                foreach($mapping as $pkey)
                {
                    $k .= ','. $item->$pkey;
                }

                if (isset($rs[$k]))
                {
                    $item->$key->__orm_callback('set_delay_data', $rs[$k]);
                }
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
        $db         = Database::instance();
        $orm_config = $delay_setting['config'];
        $table      = $orm_config['table'];
        $mapping    = $orm_config['mapping'];

        # WHERE
        if ($orm_config['where'] && is_array($orm_config['where']))
        {
            $db->where($orm_config['where']);
        }

        if ($mapping)
        {
            foreach($mapping as $k => $v)
            {
                $db->where($k, $obj->$k);
            }
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

        $obj->__orm_callback('set_delay_data', $db->from($table)->get_single());

        return true;
    }
}