<?php

/**
 * MyQEE ORM 类型基础类
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Module
 * @package    ORM
 * @copyright  Copyright (c) 2008-2015 myqee.com
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

    public function __construct($class_name, $key, $config)
    {
        $this->class_name = $class_name;
        $this->key        = $key;
        $this->config     = $config;

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

        if (isset($this->config['field_name']) && $this->config['field_name'])
        {
            $this->field_name = $this->config['field_name'];
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
            elseif (is_string($this->config['cache']) && false!==strpos(',', $this->config['cache']))
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

    abstract protected function format_config();

    /**
     * 获取当前类型的数据
     *
     * @param OOP_ORM_Data $obj
     * @param $data
     * @param $compiled_data
     * @return mixed
     */
    abstract public function & get_data(OOP_ORM_Data $obj, & $data, & $compiled_data, & $delay_setting);

    abstract public function set_data(OOP_ORM_Data $obj, & $data, & $compiled_data, $new_value, $has_compiled = false);

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
            # 自读字段不允许unset
            return false;
        }

        unset($data[$this->key]);
        unset($compiled_data[$this->key]);

        return true;
    }

    /**
     * 获取当前字段名
     *
     * @return string
     */
    public function get_field_name()
    {
        return $this->config['field_name'];
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
    public function is_change()
    {

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
     * 格式化数据
     *
     * @param $key
     * @param $format_config
     * @param $compiled_data
     */
    protected static function _format_data($key, $format_config, &$compiled_data)
    {
        $tmp_data = $compiled_data[$key];

        foreach((array)$format_config as $v)
        {
            try
            {
                if (is_array($v))
                {
                    $fun = 'OOP_ORM_DI::_format_action_' . array_shift($v);
                    array_unshift($v, $tmp_data);
                    $tmp_data = call_user_func_array($fun, $v);
                }
                else
                {
                    $fun = '_format_action_' . $v;
                    $tmp_data = OOP_ORM_DI::$fun($tmp_data);
                }
            }
            catch (Exception $e)
            {
                Core::debug()->error($v, 'ORM格式化方法不存在');
            }
        }

        $compiled_data[$key] = $tmp_data;
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
        $format_config = array_reverse((array)$format_config);

        foreach($format_config as $v)
        {
            try
            {
                if (is_array($v))
                {
                    $fun = 'OOP_ORM_DI::_de_format_action_' . array_shift($v);
                    array_unshift($v, $tmp_data);
                    $tmp_data = call_user_func_array($fun, $v);
                }
                else
                {
                    $fun = '_de_format_action_' . $v;
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
        if ($v==='')
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

    protected static function _format_action_xml($data)
    {
        return Arr::to_xml($data);
    }

    protected static function _de_format_action_xml($data, $attribute_key = null, $max_recursion_depth = null, $url_xml_setting = null)
    {
        return Text::xml_to_array($data, $attribute_key, $max_recursion_depth, $url_xml_setting);
    }

    /**
     * 获取一个缓存对象
     *
     * @param $config
     * @return Cache
     */
    protected static function _get_cache_instance($config)
    {
        return new Cache($config['config']);
    }

    /**
     * 根据option获取一个唯一的缓存key
     *
     * @param array $option
     * @return string
     */
    protected function _get_cache_key(array $option)
    {
        $str = '_orm_cache';
        foreach($option as $k=>$v)
        {
            $str .= "&$k=$v";
        }
        return $str;
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
            if ($m[3] && strtolower($m[3])=='unsigned')
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

            if ($data<$num[0])
            {
                $data = $num[0];
            }
            elseif ($data>$num[1])
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