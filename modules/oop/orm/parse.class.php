<?php

/**
 * ORM 字段解析器
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Module
 * @package    ORM
 * @copyright  Copyright (c) 2008-2016 myqee.com
 * @license    http://www.myqee.com/license.html
 */
abstract class Module_OOP_ORM_Parse
{

    public function __construct()
    {
        throw new Exception('OOP_ORM_Parse 对象不需实例化');
    }

    /**
     * 检查并规范ORM字段配置
     *
     */
    public static function check_config(&$setting, &$field_to_offset, &$id_field)
    {
        if ( !is_array($setting) )
        {
            return;
        }

        foreach ( $setting as $key => &$field_config )
        {
            if ( !is_array($field_config) )
            {
                if ( null === $field_config ) $field_config = $key;
                $field_config = array('field_name' => $field_config);
            }

            OOP_ORM_Parse::_check_fieldname($key, $field_config, $field_to_offset);
            OOP_ORM_Parse::_check_orm($key, $field_config);
            OOP_ORM_Parse::_check_data($key, $field_config);
            OOP_ORM_Parse::_check_object($key, $field_config);

            if ( !$id_field )
            {
                OOP_ORM_Parse::_check_id_field($key, $field_config, $id_field);
            }
        }
        unset($field_config);

        # 整理数据子集
        foreach ( $setting as $key => $config )
        {
            if ( isset($config['_parent_offset_name']) )
            {
                $setting[$config['_parent_offset_name']]['_all_sub_offset'][] = $key;
            }
        }
    }

    /**
     * 检查字段名
     *
     * @param string $key
     * @param array $field_config
     */
    protected static function _check_fieldname($key, &$field_config, &$field_to_offset)
    {
        # 是否只读字段
        if ( isset($field_config['is_readonly']) )
        {
            $field_config['is_readonly'] = (bool)$field_config['is_readonly'];
        }

        # 是否虚拟字段
        if ( isset($field_config['is_virtual_field']) && $field_config['is_virtual_field'] )
        {
            unset($field_config['field_name']);
            return;
        }
        elseif ( !isset($field_config['field_name']) )
        {
            $field_config['field_name'] = $key;
        }

        $field_config['is_virtual_field'] = false;

        if ( false!==strpos($field_config['field_name'], '[') && preg_match('#^([a-z0-9_]+)\[(.*)\]$#i', $field_config['field_name'], $m) )
        {
            # 支持 test[abc][def] 格式
            unset($field_config['field_name'], $field_config['data'], $field_config['orm']);
            $field_config['_parent_offset_name'] = $m[1];
            $field_config['_sub_key_name'] = str_replace('][', '.', $m[2]);
        }
        elseif ( false!==strpos($field_config['field_name'] , '.') )
        {
            # 支持 test.abc.def 格式
            unset($field_config['field_name'], $field_config['data'], $field_config['orm']);
            $m = explode('.', $field_config['field_name'],2);
            $field_config['_parent_offset_name'] = $m[0];
            $field_config['_sub_key_name'] = $m[1];
        }
        else
        {
            $field_to_offset[$field_config['field_name']][] = $key;
        }
    }

    /**
     * 检查是否ID字段
     *
     * @param string $key
     * @param array $field_config
     */
    protected static function _check_id_field($key, &$field_config, &$id_field)
    {
        if ( isset($field_config['is_id_field']) && true === $field_config['is_id_field'] )
        {
            $id_field = $field_config['field_name'];
        }
    }

    /**
     * 检查ORM配置
     *
     * @param string $key
     * @param array $field_config
     */
    protected static function _check_orm($key, &$field_config)
    {
        if ( !isset($field_config['orm']) )
        {
            return;
        }
        if ( $field_config['orm'] && ! is_array($field_config['orm']) )
        {
            $field_config['orm'] = array('name' => (string)$field_config['orm']['name']);
        }
        elseif ( !isset($field_config['orm']['name']) )
        {
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
    protected static function _check_data($key, &$field_config)
    {
        if ( !isset($field_config['data']) )
        {
            return;
        }
        if ( !is_array($field_config['data']) )
        {
            Core::debug()->error($field_config,'ORM字段:'.$key.'配置错误，data属性应该为数组');
            unset($field_config['data']);
            return;
        }
        if ( !isset($field_config['data']['drive']) )
        {
            Core::debug()->error($field_config,'ORM字段:'.$key.'配置错误，data属性缺少drive');
            unset($field_config['data']);
            return;
        }
        if ( isset($field_config['data']['mapping']) && ! is_array($field_config['data']['mapping']) )
        {
            Core::debug()->error($field_config,'ORM字段:'.$key.'配置错误，data属性的mapping应该为数组');
            unset($field_config['data']['mapping']);
            return;
        }

        $run = '_parse_' . $field_config['data']['drive'];
        if ( method_exists('OOP_ORM_Parse', $run) )
        {
            # 处理数据解析
            $field_config = OOP_ORM_Parse::$run($field_config);
        }
    }

    protected static function _check_object($key, &$field_config)
    {
        if ( !isset($field_config['object']) )
        {
            return;
        }
        if ( is_string($field_config['object']) )
        {
            $field_config['object'] = array('name' => $field_config['object']);
        }
        elseif ( ! is_array($field_config['object']) )
        {
            Core::debug()->error('ORM字段配置错误，object属性应为数组');
            unset($field_config['object']);
            return;
        }
        if ( !isset($field_config['object']['name']) )
        {
            Core::debug()->error('ORM字段配置错误，object属性的name必须设置');
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
    protected static function _parse_database($config)
    {
        if ( !isset($config['database']) )
        {
            $config['database'] = 'default';
        }

        if ( !isset($config['tablename']) )
        {
            throw new Exception('配置错误，缺少表名称.');
        }

        return $config;
    }

    /**
     * 格式化数据
     *
     * @param mixed $data
     * @param array $config
     */
    public static function format_data($data, $config)
    {
        foreach ( $config as $v )
        {
            try
            {
                if ( is_array($v) )
                {
                    $fun = 'OOP_ORM_Parse::_format_action_' . array_shift($v);
                    array_unshift($v,$data);
                    $data = call_user_func_array($fun, $v);
                }
                else
                {
                    $fun = '_format_action_' . $v;
                    $data = OOP_ORM_Parse::$fun($data);
                }
            }
            catch ( Exception $e )
            {
                Core::debug()->error('ORM数据解析失败，方法：' . $fun . '。');
            }
        }
        return $data;
    }

    /**
     * 反解数据
     *
     * @param mixed $data
     * @param array $config
     */
    public static function de_format_data($data, $config)
    {
        $config = array_reverse($config);
        foreach ( $config as $v )
        {
            try
            {
                if ( is_array($v) )
                {
                    $fun = 'OOP_ORM_Parse::_de_format_action_' . array_shift($v);
                    array_unshift($v,$data);
                    $data = call_user_func_array($fun, $v);
                }
                else
                {
                    $fun = '_de_format_action_' . $v;
                    $data = OOP_ORM_Parse::$fun($data);
                }
            }
            catch ( Exception $e )
            {
                Core::debug()->error('ORM数据解析失败，方法：' . $fun . '。');
            }
        }

        return $data;
    }

    protected static function _format_action_serialize($data)
    {
        return serialize($data);
    }

    protected static function _format_action_json($data)
    {
        return json_encode($data);
    }

    protected static function _format_action_gzcompress($data)
    {
        return gzcompress($data, 9);
    }

    /**
     * 拼接字符串
     *
     * @param fixed $data
     * @param string $exp_str 默认,分割
     * @param boolean $is_add_both_str 是否在两段加上$exp_str
     *
     * @return string
     */
    protected static function _format_action_implode($data , $exp_str=',' , $is_add_both_str = false )
    {
        $v = (string)implode($exp_str, $data);
        if ( $v==='' )
        {
            return '';
        }
        else
        {
            if ( $is_add_both_str )
            {
                return $exp_str.$v.$exp_str;
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
     * @param fixed $data
     * @param string $exp_str 默认,分割
     * @param boolean $is_add_both_str 是否在两段加上$exp_str
     *
     * @return array
     */
    protected static function _de_format_action_implode($data , $exp_str=',' , $is_add_both_str = false )
    {
        $data = (string)$data;
        if ( $is_add_both_str )
        {
            $data = trim( $data , $exp_str );
        }
        if ( empty($data) )
        {
            return array();
        }
        return explode($exp_str,$data);
    }

    protected static function _de_format_action_serialize($data)
    {
        try
        {
            return @unserialize((string)$data);
        }
        catch (Exception $e)
        {
            return false;
        }
    }

    protected static function _de_format_action_json($data)
    {
        try
        {
            return json_decode((string)$data, true);
        }
        catch (Exception $e)
        {
            return false;
        }
    }

    protected static function _de_format_action_gzcompress($data)
    {
        try
        {
            return gzuncompress($data);
        }
        catch (Exception $e)
        {
            return false;
        }
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
     * 根据配置获取数据
     *
     * @param array $config
     */
    public static function get_data($config , $obj)
    {
        # 处理mapping
        if ( isset($config['mapping']) )
        {
            foreach ($config['mapping'] as $k=>$v)
            {
                $config['where'][$k] = $obj->$v;
            }
        }

        $fun = '_get_data_'.$config['drive'];

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
        $data = Database::instance($config['database'])->from($config['tablename']);

        if ( isset($config['where']) && $config['where'] )
        {
            $data->where($config['where']);
        }
        if ( isset($config['limit']) && $config['limit'] )
        {
            $data->limit($config['limit']);
        }
        if ( isset($config['offset']) && $config['offset'] )
        {
            $data->offset($config['offset']);
        }
        if ( isset($config['order_by']) && $config['order_by'] )
        {
            foreach ( $config['order_by'] as $k => $v )
            {
                $data->order_by($k, $v == 'DESC' ? 'DESC' : 'ASC');
            }
        }
        $result = $data->get();
        if ( isset($config['result']) && $config['result'] = 'single' )
        {
            $result = $result->current();
        }
        else
        {
            if ( isset($config['as_array']) )
            {
                $result = $result->as_array($config['as_array'][0] , $config['as_array'][1]);
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
        $callfun = $config['function'];
        $args = $config['arguments'];

        if (!is_array($args))
        {
            $args = array();
        }

        if (is_array($callfun))
        {
            # 对$this做特殊处理
            if (is_string($callfun[0]) && strtolower($callfun[0])=='$this')
            {
                $callfun[0] = $obj;
            }
            return call_user_func_array($callfun, $args);
        }
        else
        {
            return call_user_func($callfun, $args);
        }
    }

    /**
     * 获取HttpClient配置类型的数据
     *
     * @param array $config
     * @return array
     */
    protected static function _get_data_httpget($config,$obj)
    {
        if (isset($config['method']) && strtolower($config['method'])=='post' )
        {
            $method = 'post';
        }
        else
        {
            $method = 'get';
        }
        if ( isset($config['type']) )
        {
            $http_client = HttpClient::factory($config['type']);
        }
        else
        {
            $http_client = HttpClient::factory();
        }

        if ($method == 'post')
        {
            if (isset($config['timeout']))
            {
                return $http_client->post( $config['url'] , $config['arguments'] , $config['timeout'] );
            }
            else
            {
                return $http_client->post( $config['url'] , $config['arguments'] );
            }
        }
        else
        {
            $config['url'] .= (strpos($config['url'],'?')?'&':'?') . http_build_query($config['arguments'],'','&');
            if (isset($config['timeout']))
            {
                return $http_client->get( $config['url'] , $config['timeout'] );
            }
            else
            {
                return $http_client->get( $config['url'] );
            }
        }
    }

    /**
     * 获取对象的字段数据
     * @param fixed $obj
     * @param string $callback_fun
     * @return fixed 返回字段数据
     */
    public static function get_object_field_data($obj, $callback_fun = null)
    {
        if ( $callback_fun )
        {
            $data = $obj->$callback_fun();
        }
        elseif ( $obj instanceof OOP_ORM_Data )
        {
            $data = $obj->get_field_data();
        }
        elseif ( $obj instanceof stdClass )
        {
            $data = (array)$obj;
        }
        elseif ( $obj instanceof OOP_ORM )
        {
            # OOP_ORM对象无法作用于实际数据
            return false;
        }
        elseif ( method_exists($obj, 'getArrayCopy') )
        {
            $data = $obj->getArrayCopy();
        }
        elseif ( method_exists($obj, '__toString') )
        {
            $data = $obj->__toString();
        }
        else
        {
            # 对于未知且没有设置回调方法的对象，返回get_object_vars()结果
            return get_object_vars($obj);
        }
        return $data;
    }

    public static function & key_array( &$arr, $key )
    {
        if ( strlen($key) )
        {
            $kk = explode('.', $key);
            $k = array_shift($kk);
            $newkey = implode('.', $kk);
            return OOP_ORM_Parse::key_array($arr[$k], $newkey);
        }
        else
        {
            return $arr;
        }
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
        if ( isset( $orm_config['cache'] ) )
        {
            # 设置了缓存
            $c_config = $orm_config;
            foreach ( $c_config['mapping'] as & $item )
            {
                $item = $data_obj->$item;
            }
            $data = OOP_ORM_Parse::get_orm_cache_data($data_obj , $index , $c_config);

            $orm_name = 'ORM_' . $orm_config['name'] . '_Data';
            if ( $data && is_object($data) && $data instanceof $orm_name )
            {
                return $data;
            }

            $can_try_get_gourps_data = false;
        }
        else
        {
            # 是否可以批量取对象集里的数据
            $can_try_get_gourps_data = true;
        }

        # 映射ORM对象
        $obj_name = 'ORM_' . $orm_config['name'] . '_Finder';
        if ( ! class_exists($obj_name, true) )
        {
            throw new Exception('指定的ORM:' . $orm_config['name'] . '不存在！');
        }
        $obj = new $obj_name();

        # 支持OFFSET
        if ( isset($orm_config['offset']) )
        {
            $obj->offset($orm_config['offset']);
            if ( $orm_config['offset'] > 0 )
            {
                $can_try_get_gourps_data = false; //有OFFSET的话，不能同时取多条数据
            }
        }

        # 支持GROUP BY
        if ( isset($orm_config['group_by']) )
        {
            foreach ($orm_config['group_by'] as $item)
            {
                $obj->group_by($item);
            }
            $can_try_get_gourps_data = false;
        }

        # 支持ORDER BY
        if ( isset($orm_config['order_by']) )
        {
            foreach ($orm_config['order_by'] as $key=>$item)
            {
                $obj->order_by($key , $item);
            }
            $can_try_get_gourps_data = false;
        }

        # 支持IN
        if ( isset($orm_config['in']) )
        {
            foreach ($orm_config['in'] as $key=>$item)
            {
                $obj->in($key , $item);
            }
            $can_try_get_gourps_data = false;
        }

        # 获取ORM单条数据
        if ( $can_try_get_gourps_data && $orm_config['return'] == OOP_ORM::PARAM_RETURN_SINGLE )
        {
            # 对于单条数据，可以尝试批量获取，以优化SQL性能
            $data = OOP_ORM_Parse::_get_orm_group_data($obj, $data_obj, $orm_config , $index);
            if ( true!==$data )
            {
                # 如果!==true，则直接返回
                return $data;
            }
        }

        $orm_where = array();
        # 处理mapping
        if ( $orm_config['mapping'] )
        {
            foreach ( $orm_config['mapping'] as $k => $v )
            {
                $orm_where[$k] = $data_obj->$v;
            }
        }
        if ( isset($orm_config['where']) )
        {
            $orm_where = $orm_config['where'] + $orm_where;
        }

        if ( $orm_where )
        {
            # 设置autowhere
            $obj->set_auto_where($orm_where);
        }

        if ( $orm_config['limit'] )
        {
            $obj->limit($orm_config['limit']);
        }

        if ( $orm_config['return'] == OOP_ORM::PARAM_RETURN_SINGLE )
        {
            # 获取单条数据
            $data = $obj->find()->current();
        }
        elseif ( $orm_config['return'] == OOP_ORM::PARAM_RETURN_GROUP )
        {
            # 获取一组数据
            $data = $data = $obj->find();
        }
        else
        {
            # 当前ORM对象
            $data = $obj;
        }

        if ( isset($orm_config['cache']) )
        {
            # 设置缓存
            OOP_ORM_Parse::set_orm_cache_data($data_obj , $index , $c_config , $data);
        }

        return $data;
    }

    protected static function _get_orm_group_data(OOP_ORM $obj, OOP_ORM_Data $data_obj, $orm_config , $index)
    {
        $group_data = $data_obj->get_group_data();
        if ( $group_data )
        {
            foreach ( $orm_config['mapping'] as $k => $v )
            {
                $tmpdata = array(); //记录临时数据
                foreach ( $group_data as $i => $item )
                {
                    # 当前字段需要符合的条件
                    $iswhere = true;
                    if ( isset($orm_config['where']) )
                    {
                        foreach ( $orm_config['where'] as $wk => $wv )
                        {
                            if ( $item->$wk != $wv )
                            {
                                $iswhere = false;
                            }
                        }
                    }
                    if ( $iswhere )
                    {
                        $tmpdata[] = $item->$v;
                    }
                }
                $tmpdata = array_unique($tmpdata);
                if ( $tmpdata )
                {
                    $obj->in($k, $tmpdata);
                }
            }

            if ( $tmpdata )
            {
                # 如果有临时数据，则表明可以进行批量获取
                if ( isset($orm_config['where']) ) foreach ( $orm_config['where'] as $k => $v )
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
            foreach ( $all_data as $item )
            {
                $tmpkey = '';
                foreach ( $orm_config['mapping'] as $k => $v )
                {
                    $tmpkey .= '|' . $k . '_' . $item->$k;
                }
                $tmp[$tmpkey] = $item;
            }

            # 将获取来的数据赋值给各个对象
            foreach ( $group_data as $item )
            {
                $tmpkey = '';
                foreach ( $orm_config['mapping'] as $k => $v )
                {
                    $tmpkey .= '|' . $k . '_' . $item->$v;
                }
                if ( $item===$data_obj )
                {
                    # 当前对象
                    $data = isset($tmp[$tmpkey]) ? $tmp[$tmpkey] : null;
                }
                else
                {
                    $item->$index = isset($tmp[$tmpkey]) ? $tmp[$tmpkey] : null;
                }
            }

            if ( isset($data) )
            {
                # 已经获取到当前对象，直接返回
                return $data;
            }
        }

        return true;
    }

    /**
     * 根据offset及配额获取缓存数据（如果有）
     *
     * @param string $index
     * @param array $config
     * @return fiexd
     */
    public static function get_offset_cache_data( OOP_ORM_Data $data_obj , $index , $cache_config )
    {
        # 获取一个key
        $key = OOP_ORM_Parse::get_offset_cache_key($data_obj , $index);

        return Cache::instance($cache_config['drive_config'])->get($key);
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
    public static function set_offset_cache_data( OOP_ORM_Data $data_obj , $index , $cache_config , $data )
    {
        # 获取一个key
        $key = OOP_ORM_Parse::get_offset_cache_key($data_obj, $index);

        return Cache::instance($cache_config['drive_config'])->set($key , $data , isset($cache_config['expire'])?$cache_config['expire']:3600 , $cache_config['expire_type'] );
    }

    /**
     * 根据offset及配额获取缓存数据（如果有）
     *
     * @param string $index
     * @param array $config
     * @return fiexd
     */
    public static function delete_offset_cache_data( OOP_ORM_Data $data_obj , $index , $cache_config )
    {
        # 获取一个key
        $key = OOP_ORM_Parse::get_offset_cache_key($data_obj , $index);

        # 清除缓存
        return Cache::instance($cache_config['drive_config'])->delete($key);
    }

    /**
     * 根据index获取一个唯一的缓存key
     * @param string $index
     * @return string
     */
    public static function get_offset_cache_key( OOP_ORM_Data $data_obj , $index )
    {
        $id_field_name = $data_obj->id_field_name();
        $id_value = $data_obj->$id_field_name;
        return '_ORM_OFFSET_CACHE_' . get_class($data_obj) . '_' . $id_value . '_' . $index;
    }


    /**
     * 根据offset及配额获取缓存数据（如果有）
     *
     * @param string $index
     * @param array $config
     * @return fiexd
     */
    public static function get_orm_cache_data( OOP_ORM_Data $data_obj , $index , $config )
    {
        # 获取一个key
        $key = OOP_ORM_Parse::get_orm_cache_key($data_obj , $index , $config);

        return Cache::instance($config['cache']['drive_config'])->get($key);
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
    public static function set_orm_cache_data( OOP_ORM_Data $data_obj , $index , $config , $data )
    {
        # 获取一个key
        $key = OOP_ORM_Parse::get_orm_cache_key($data_obj , $index , $config);

        return Cache::instance($config['cache']['drive_config'])->set($key , $data , isset($config['cache']['expire'])?$config['cache']['expire']:3600 , $config['cache']['expire_type'] );
    }

    /**
     * 根据offset及配额获取缓存数据（如果有）
     *
     * @param string $index
     * @param array $config
     * @return fiexd
     */
    public static function delete_orm_cache_data( OOP_ORM_Data $data_obj ,$index , $config )
    {
        # 获取一个key
        $key = OOP_ORM_Parse::get_orm_cache_key($data_obj ,$index , $config);

        # 清除缓存
        return Cache::instance($config['cache']['drive_config'])->delete($key);
    }

    /**
     * 根据index获取一个唯一的缓存key
     * @param string $index
     * @return string
     */
    public static function get_orm_cache_key( OOP_ORM_Data $data_obj , $index , $config )
    {
        return '_ORM_CACHE_' . get_class($data_obj) . '_' . $index .'_'. md5(serialize($config));
    }

    /**
     * 设置根节点数据
     *
     * @param array $sub_field
     * @param fixed $data
     * @param fixed $value
     * @return boolean 数据是否修改
     */
    public static function set_sub_offset_data( &$data , $value , array $sub_field )
    {
        $first_field = array_shift($sub_field);

        if ($sub_field)
        {
            if ( is_object($data) )
            {
                return OOP_ORM_Parse::set_sub_offset_data( $data->$first_field , $value , $sub_field );
            }
            else
            {
                if ( !is_array($data) )
                {
                    $data = array();
                }
                return OOP_ORM_Parse::set_sub_offset_data( $data[$first_field] , $value , $sub_field );
            }
        }
        else
        {
            if ( is_object($data) )
            {
                $old_data = $data->$first_field;
                $data->$first_field = $value;
                $status = $old_data===$data->$first_field?false:true;
            }
            else
            {
                if ( !is_array($data) )
                {
                    $data = array();
                }
                $status = $value===$data[$first_field]?false:true;
                $data[$first_field] = $value;
            }
            return $status;
        }
    }

    /**
     * 获取一个子节点数据
     *
     * @param array/object $data 数组或对象
     * @param array $sub_field 子节点
     */
    public static function get_sub_offset_data($data , array $sub_field,$index=null)
    {
        if ( !is_array($data) && !is_object($data) ) return null;

        foreach ( $sub_field as $key )
        {
            if ( is_object($data) )
            {
                if ( isset($data->$key) )
                {
                    $data = $data->$key;
                }
                else
                {
                    return null;
                }
            }
            else
            {
                if ( isset($data[$key]) )
                {
                    $data = $data[$key];
                }
                else
                {
                    return null;
                }
            }
        }


        return $data;
    }

    public static function get_object_vars($obj)
    {
        return get_object_vars($obj);
    }
}