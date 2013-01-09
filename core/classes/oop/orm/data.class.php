<?php

/**
 * MyQEE ORM 核心类
 *
 * @author     jonwang(jonwang@myqee.com)
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2012 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Core_OOP_ORM_Data
{
    /**
     * ID 字段名
     * @var string
     */
    protected $_id_field_name;

    /**
     * 当前类的名称(全小写格式)
     * @var string
     */
    protected $_class_name;

    /**
     * 当前ORM的唯一ID
     * @var string
     */
    protected $_orm_result_uniqid;

    /**
     * 当前ORM基本名称
     * @var string
     */
    protected $_orm_name;

    /**
     * 当前ORM对象
     * @var ORM
     */
    protected $_orm_object;

    /**
     * 存放的对象数据,key和offset所对应
     *
     * @var array
     */
    protected $_offset_data = array();

    /**
     * 原始字段数据
     *
     * 在对象构造的时候会将每一个字段数据都设置进去，key和数据库字段对应而并非offset
     *
     * @var array
     */
    protected $_original_field_data = array();

    /**
     * 是否已创建完成
     *
     * 当$_orm_data_is_created=true时，修改对象值将被置于已修改对象
     * @var boolean
     */
    protected $_orm_data_is_created = false;

    /**
     * 数据是否修改过
     *
     * @var boolean
     */
    protected $_data_is_changed = false;

    /**
     * 记录已构造好的字段
     *
     * @example array('test'=>true,'test2'=>true,'field3'=>true);
     * @var array
     */
    protected $_created_offset = array();

    /**
     * 记录已设置过数据的字段
     *
     * @example array('test'=>true,'test2'=>true,'field3'=>true);
     * @var array
     */
    protected $_has_set_offset = array();

    /**
     * 递增或递减值信息
     *
     * key为字段，不是offset
     *
     * @var array
     */
    protected $_value_increment = array();

    /**
     * 字段设置
     *
     * @var array
     */
    protected static $_offset_setting = array();

    /**
     * 数据库字段和对象key的对应关系
     * array(
     *   'classname' => array(
     *   	'fieldname' => array(
     *   		'offset1',
     *   		'offset2',
     *   		...
     *   	),
     *   ),
     * )
     * @var array
     */
    protected static $_field_to_offset = array();

    /**
     * 记录所有ID字段
     *
     *   array(
     * 	     'classname' => 'id',
     *       'classname2' => 'mid',
     *   )
     *
     * @var array
     */
    protected static $_all_id_field_name = array();

    /**
     * 记录id数据缓存，在SHELL脚本中运行则不记录
     *
     *   array(
     * 	     'classname' => array(1=>...,2=>...),
     * 	     'classname2' => array(1=>...,2=>...),
     *   )
     *
     * @var array
     */
    protected static $_id_field_cache_data = array();

    /**
     * 已缓存ID总数
     *
     * @var int
     */
    protected static $_id_field_cache_data_num = 0;

    /**
     * 最大ID数据缓存数
     *
     * @var int
     */
    protected static $max_id_cache_num = 1000;

    /**
     * ORM数据构造
     *
     * @param array $array 构造时设置数据，通过此设置的数据被认为是以数据库字段field为键的数组
     */
    public function __construct($array = null)
    {
        # 对象名称
        $this->_class_name = strtolower(get_class($this));

        # 更新配置
        $this->_renew_orm_config();

        # 如果有数据，则设置数据
        if ( $array && is_array($array) ) $this->__orm_callback_ini_data_($array,true);

        # 标志ORM为已构造完成
        $this->_orm_data_is_created = true;
    }

    /**
     * 构造对象时清除字段
     *
     * 销毁变量以便可以统一采用__get()方法获取
     */
    protected function _renew_orm_config()
    {
        $class_name = $this->_class_name;
        # 获取当前对象所有变量
        $class_vars = get_class_vars($class_name);

        if ( !isset(OOP_ORM_Data::$_offset_setting[$class_name]) )
        {
            OOP_ORM_Data::$_offset_setting[$class_name] = array();
            OOP_ORM_Data::$_field_to_offset[$class_name] = array();
            $need_check_config = true;
        }
        else
        {
            $need_check_config = false;
        }

        # 清理key
        foreach ( $class_vars as $k => $v )
        {
            $kstr = strtolower($k[0]);
            if ( $kstr >= 'a' && $kstr <= 'z' )
            {
                if ( $need_check_config )
                {
                    OOP_ORM_Data::$_offset_setting[$class_name][$k] = $v;
                }
                unset($this->$k);
            }
        }

        # 需要更新配置
        if ( $need_check_config )
        {
            OOP_ORM_Parse::check_config(
                OOP_ORM_Data::$_offset_setting[$class_name]
                ,
                OOP_ORM_Data::$_field_to_offset[$class_name]
                ,
                OOP_ORM_Data::$_all_id_field_name[$class_name]
            );
        }

        # 当前ID字段
        $this->_id_field_name = OOP_ORM_Data::$_all_id_field_name[$class_name];
    }

    /**
     * 支持序列化对象 serialize($this)
     *
     */
    public function __sleep()
    {
        $this->__orm_sleep_data__ = array
        (
            'v' => '1.0',
            'd' => $this->get_field_data(),
        );

        $r = array('__orm_sleep_data__');

        # 获取外部设置的key，比如:$obj->test
        $r2 = array_keys(OOP_ORM_Parse::get_object_vars($this));

        # 合并数组并移除重复值
        return array_unique(array_merge($r,$r2));
    }

    /**
     * 反序列化 unserialize()
     */
    public function __wakeup()
    {
        if ( isset($this->__orm_sleep_data__['v'] ) && $this->__orm_sleep_data__['v'] == '1.0' )
        {
            # 将数据初始化设置进去
            $this->__construct( $this->__orm_sleep_data__['d'] );

            # 清除
            unset($this->__orm_sleep_data__);
        }
        else
        {
            $this->__construct();
        }
    }

    public function __call($method, $params)
    {
        # ORM 调用接口
        if ( substr($method, 0, 15) == '__orm_callback_' && method_exists($this, $method . '_') )
        {
            $count_params = count($params);
            $action_name = $method . '_';
            switch ( $count_params )
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
        return null;
    }

    /**
     * 销毁指定key的值
     *
     * @param string $key
     */
    public function __unset($key)
    {
        # 获取当前设置
        $config = $this->_get_offset_config($key);
        if ( ! $config )
        {
            return;
        }

        $offset_exist = isset($this->_offset_data[$key]);

        if ( $this->_orm_data_is_created )
        {
            # 对象已构造完毕
            if ( $offset_exist && $config['is_readonly'] )
            {
                if ( null!==$this->_offset_data[$key] && false!==$this->_offset_data[$key] )
                {
                    # 只读字段不允许设置
                    return false;
                }
            }
        }

        # 映射字段
        if ( isset($config['_parent_offset_name']) )
        {
            # 设置数据
            if ( false === $this->__unset($config['_parent_offset_name']) )
            {
                return false;
            }
            else
            {
                return true;
            }
        }

        unset($this->_has_set_offset[$key]);
        unset($this->_offset_data[$key]);
        unset($this->_created_offset[$key]);

        if ( isset($config['_all_sub_offset']) && is_array($config['_all_sub_offset']) )
        {
            foreach ($config['_all_sub_offset'] as $sub_key)
            {
                unset($this->_has_set_offset[$sub_key]);
                unset($this->_offset_data[$sub_key]);
                unset($this->_created_offset[$sub_key]);
            }
        }

        return true;
    }

    public function __isset($key)
    {
        return isset($this->_has_set_offset[$key]);
    }

    public function & __get($key)
    {
        if ( !isset($this->_created_offset[$key]) )
        {
            # 构造数据
            $this->_create_data($key);
        }

        return $this->_offset_data[$key];
    }

    public function __set($key , $value)
    {
        return $this->set_data($key, $value , true);
    }

    /**
     * 设置数据
     *
     * 键名是对象的键名，而并非数据库字段的键名，可以批量设置
     * $has_created=是否已构造好的数据。若已构造好的数据则无需再经过_create_data()方法处理，默认false
     *
     * 	 set_data( array('a'=>'1','b'=>2) );
     *   set_data( array('a'=>'1','b'=>2) , null , true );
     *   set_data( 'a , '1' );
     *   set_data( 'a , '1' , true );
     *
     * @param array/string $key
     * @param fixed $value 值
     * @param boolean $has_parsed 是否已构造好的数据，默认false
     * @return boolean 是否设置成功
     */
    public function set_data($key , $value , $has_parsed = false)
    {
        if ( is_array($key) )
        {
            foreach ( $key as $k => $v )
            {
                $this->set_data($k, $v , $has_parsed);
            }
            return true;
        }

        # 获取当前设置
        $config = $this->_get_offset_config($key);
        if ( !$config )
        {
            if ( preg_match('#^[a-z]#i', $key) || $key=='__orm_sleep_data__' )
            {
                # 没有相应的配置，直接快速设置后返回，其中__orm_sleep_data__为对象序列化时需要设置的key
                $this->$key = $value;
            }
            return;
        }

        # 当前节点是否已设置
        $offset_isset = isset($this->_offset_data[$key]);

        # 标记为已设置过的节点
        $this->_has_set_offset[$key] = true;

        if ( $this->_orm_data_is_created )
        {
            # 对象已构造完毕
            if ( $offset_isset && $config['is_readonly'] )
            {
                if ( null!==$this->_offset_data[$key] && false!==$this->_offset_data[$key] )
                {
                    # 只读字段不允许修改
                    return false;
                }
            }
        }

        # 映射字段
        if ( isset($config['_parent_offset_name']) )
        {
            $parent_offset = $config['_parent_offset_name'];

            # 父节点名
            if ( !$parent_offset || $parent_offset==$key )
            {
                return false;
            }

            $parent_data = $this->$parent_offset;

            # 设置子节点数据
            $is_changed = OOP_ORM_Parse::set_sub_offset_data( $parent_data , $value , explode('.',$config['_sub_key_name']) );

            # 将数据设置到父节点
            if ( false===$is_changed || false===$this->__set($parent_offset, $parent_data) )
            {
                # 若返回是false，则表示父类不允许设置（比如只读字段）
                return false;
            }
            else
            {
                # 父节点会推动所有子节点更新数据，所以这边直接返回true即可
                return true;
            }
        }
        elseif ( isset($config['field_name']) )
        {
            if ( $this->_orm_data_is_created )
            {
                $this->_data_is_changed = true;
            }
            else
            {
                # 记录到旧数据
                $this->_original_field_data[$config['field_name']] = $value;
            }
        }

        if ($has_parsed)
        {
            $this->_offset_data[$key] = $value;
            # 设为已构造
            $this->_created_offset[$key] = true;
        }
        else
        {
            # 反解格式化数据
            if ( isset($config['format']) )
            {
                $value = OOP_ORM_Parse::de_format_data($value, $config['format']);
            }

            if ( isset($this->_created_offset[$key]) )
            {
                # 已经构造了
                if ( is_object($this->_offset_data[$key]) )
                {
                    # 对象
                    if ( isset($config['object']['callback']['set_data']) )
                    {
                        # 配置有设置数据的接口
                        $call_set_data_function = $config['object']['callback']['set_data'];
                        $obj = $this->_offset_data[$key];
                        $obj->$call_set_data_function($value);
                    }
                    else
                    {
                        $this->_offset_data[$key] = $value;
                        # 清理掉以便可以重新构造
                        unset($this->_created_offset[$key]);
                    }
                }
                else
                {
                    # 已创建了且不是对象，则可以直接替换掉
                    $this->_offset_data[$key] = $value;
                }
            }
            else
            {
                # 设置了一个未构造的数据，且当前也没有构造数据，则直接替换掉
                $this->_offset_data[$key] = $value;
            }
        }

        # 推动子字段进行更新数据
        if ( isset($config['_all_sub_offset']) )
        {
            foreach ($config['_all_sub_offset'] as $sub_key)
            {
                # 子字段配置
                $sub_config = $this->_get_offset_config($sub_key);
                if (!$sub_config || !isset($sub_config['_sub_key_name']))continue;

                # 默认都需要清理子数据
                $need_reset = true;

                # 表示子字段已设置过
                $this->_has_set_offset[$sub_key] = true;

                if ( isset($this->_created_offset[$sub_key]) )
                {
                    if ( is_object($this->_offset_data[$sub_key]) )
                    {
                        # 已构造对象需要处理
                        $obj = $this->_offset_data[$sub_key];
                        if ( isset($config['object']['callback']['set_data']) )
                        {
                            $sub_data = OOP_ORM_Parse::get_sub_offset_data( $this->$key , explode('.',$sub_config['_sub_key_name']) );
                            $call_set_data_function = $config['object']['callback']['set_data'];
                            $obj->$call_set_data_function($sub_data);
                            # 调用过设置数据的的回调函数后就不需要再清理了
                            $need_reset = false;
                        }
                        else
                        {
                            # 没有设置回调函数
                            if ($obj instanceof OOP_ORM_Data || method_exists($obj, 'getArrayCopy') )
                            {
                                # 获取当前数据
                                $tmp_data = $obj->getArrayCopy();
                                # 获取父节点的子数据
                                $sub_data = OOP_ORM_Parse::get_sub_offset_data( $this->$key , explode('.',$sub_config['_sub_key_name']) );
                                if ( $tmp_data===$sub_data )
                                {
                                    # 未修改过，不需要清理
                                    $need_reset = false;
                                }
                            }
                            elseif ( method_exists($obj, '__toString') )
                            {
                                # 获取当前数据
                                $tmp_data = $obj->__toString();
                                # 获取父节点的子数据
                                $sub_data = OOP_ORM_Parse::get_sub_offset_data( $this->$key , explode('.',$sub_config['_sub_key_name']) );
                                if ( $tmp_data===$sub_data )
                                {
                                    # 未修改过，不需要清理
                                    $need_reset = false;
                                }
                            }
                        }
                    }
                }

                if ($need_reset)
                {
                    # 清理设置，在重新get的时候会重新构造数据
                    unset($this->_created_offset[$sub_key]);
                    unset($this->_offset_data[$sub_key]);
                }
            }
        }

        return true;
    }

    /**
     * 返回本对象json_encode()结果
     *
     * @return string
     */
    public function json_encode()
    {
        return json_encode($this->getArrayCopy());
    }

    /**
     * getArrayCopy别名
     *
     * 注意，此方法将返回以对象键名为key的数组，且不会根据设置格式化数据<br>
     * 若希望获取以数据库字段为key的数据，可使用get_field_data()方法
     *
     * @return array
     */
    public function as_array()
    {
        return $this->getArrayCopy();
    }

    /**
     * 将对象返回成数据，若数据为对象也会一并转换
     *
     */
    public function getArrayCopy()
    {
        $arr = array();
        foreach ( $this->_offset_data as $k=>$v)
        {
            if (is_object($v))
            {
                if ( method_exists($v,'getArrayCopy') )
                {
                    $arr[$k] = $v->getArrayCopy();
                }
                elseif ( method_exists($v, '__toString') )
                {
                    $arr[$k] = $v->__toString();
                }
                elseif ( $v instanceof stdClass )
                {
                    $arr[$k] = (array)stdClass;
                }
                else
                {
                    $arr[$k] = get_object_vars($v);
                }
            }
            else
            {
                $arr[$k] = $v;
            }
        }

        # 获取public的数据
        $k = array_keys(OOP_ORM_Parse::get_object_vars($this));
        if ($k)foreach ($k as $v)
        {
            $arr[$v] = $this->$v;
        }
        return $arr;
    }

    /**
     * 返回当前对象ID字段名
     *
     * @return string
     */
    public function id_field_name()
    {
        return $this->_id_field_name;
    }

    /**
     * 获取指定key的配置
     *
     * @param string $index
     * @return array/null
     */
    protected function _get_offset_config($index)
    {

        if ( isset(OOP_ORM_Data::$_offset_setting[$this->_class_name][$index]) )
        {
            return OOP_ORM_Data::$_offset_setting[$this->_class_name][$index];
        }
        else
        {
            return null;
        }
    }

    /**
     * 根据字段名获取所有offset键名
     *
     * @param string $field_name
     * @return array
     */
    protected function _get_offset_name_by_field($field_name)
    {
        return (array)OOP_ORM_Data::$_field_to_offset[$this->_class_name][$field_name];
    }

    /**
     * 指定offset递增
     *
     * 通过这个方法改变值后，构造SQL时会是`field_name`=`field_name`+1，而不是`field_name`=2这样，可解决并发问题
     *
     * @param string $offset
     * @param ing $value
     * @return $this
     */
    public function value_increment($offset, $value=1)
    {
        $this->$offset = $this->$offset + $value;

        $config = $this->_get_offset_config($offset);
        if ( ! $config ) continue;

        # 虚拟字段
        if ( $config['is_virtual_field'] ) continue;
        if ( ! isset($config['field_name']) ) continue;
        $field_name = $config['field_name'];

        $this->_value_increment[$field_name] = $value;

        return $this;
    }

    /**
     * 指定offset递减
     *
     * 与increment_value相反
     *
     * @param string $offset
     * @param ing $value
     * @return $this
     */
    public function value_decrement($offset, $value=1)
    {
        return $this->value_increment($offset,-$value);
    }

    /**
     * 更新数据
     *
     * @return int $status 作用的行数
     */
    public function update()
    {
        $data = $this->get_changed_data();
        if ( !$data ) return 0;

        $orm = $this->orm();
        if ( !method_exists($orm, 'update') )
        {
            throw new Exception('当前ORM对象' . get_class($orm) . '不支持update方法');
        }
        $id_field = $this->get_id_field_name();
        if ( $id_field )
        {
            $offset = current( $this->_get_offset_name_by_field($id_field) );
            $where[$id_field] = $this->$offset;
        }
        else
        {
            throw new Exception('ORM:' . get_class($this) . '不存在ID字段，无法使用ORM系统自带的update方法更新。');
        }

        # 递增或递减数据处理
        if ( $this->_value_increment && method_exists($orm->driver(), 'value_increment') ) foreach ( $this->_value_increment as $field=>$value )
        {
            # 如果存在递增或递减的数据
            if ( isset($data[$field]) )
            {
                $orm->driver()->value_increment($field,$value);
                unset($data[$field]);
            }
        }

        $status = $orm->update($data, $where);

        $this->_clear_changed_value_setting();

        return $status;
    }

    /**
     * 插入数据
     *
     * @return array(插入ID,作用行数)
     */
    public function insert()
    {
        $orm = $this->orm();
        if ( !method_exists($orm, 'insert') )
        {
            throw new Exception('当前ORM对象' . get_class($orm) . '不支持insert方法');
        }
        $status = $orm->insert( $this->get_changed_data() );
        if ( !$status ) return array(0, 0);
        if ( $status[0] > 0 )
        {
            $id_field = $this->get_id_field_name();
            $offset = current( $this->_get_offset_name_by_field($id_field) );
            $this->$offset = $status[0];
        }

        $this->_clear_changed_value_setting();

        return $status;
    }

    /**
     * 删除对象数据
     * @return integer 操作行数
     * @throws Exception
     */
    public function delete()
    {
        $orm = $this->orm();
        $status = 0;
        $id_field = $this->get_id_field_name();
        if ( $id_field )
        {
            $where[$id_field] = $this->$id_field;
        }
        else
        {
            throw new Exception('ORM:' . get_class($orm) . '不存在ID字段，无法使用ORM系统自带的delete方法删除。');
        }
        $status = $orm->delete($where);

        $this->_remove_id_cache_data();

        return $status;
    }

    /**
     * 获取以数据库字段名为键名的数组数据
     *
     * 会排除掉虚拟数据，此数据可直接用户数据库的update
     *
     * @param $field_name 字段名，如果不传则获取所有filed_data数据
     * @return array
     */
    public function get_field_data($field_name=null)
    {
        # 获取全部字段数据
        $data = array();

        foreach ( array_keys($this->_has_set_offset) as $offset )
        {
            $this->_renew_offset_changed_value( $data , $offset , true);
        }

        # 其它没有构造过的数据，合并到老数据里
        $data = array_merge( $this->_original_field_data , $data );

        if (null===$field_name)
        {
            return $data;
        }
        elseif ($field_name && isset($data[$field_name]))
        {
            return $data[$field_name];
        }
        else
        {
            return null;
        }
    }

    /**
     * 获取修改的数据
     *
     * 注意，返回的数组的键名是字段的键名，而并不是对象的键名
     *
     * @return array
     */
    public function get_changed_data()
    {
        $data = array();

        foreach ( array_keys($this->_has_set_offset) as $offset )
        {
            $this->_renew_offset_changed_value( $data , $offset , false );
        }

        return $data;
    }

    /**
     * 更新指定$offset的修改数据并设置到$data相应的key中
     *
     * @param string $offset
     * @param array $data
     * @param boolean $is_all_data 是否取得完整数据
     * @param boolean $renew_original_field_data 是否仅更新原始数据
     */
    protected function _renew_offset_changed_value( & $data , $offset , $is_all_data = false , $renew_original_field_data = false )
    {
        $config = $this->_get_offset_config($offset);
        if ( !$config ) return;

        # 虚拟字段
        if ( $config['is_virtual_field'] ) return;
        if ( !isset($config['field_name']) ) return;

        $field_name = $config['field_name'];

        if ( isset($data[$field_name]) )
        {
            # 已经有数据了
            return;
        }

        if ( !$renew_original_field_data && isset($config['read_only']) && $config['read_only'] )
        {
            # 只读数据
            if ( $is_all_data )
            {
                $data[$field_name] = $this->_original_field_data[$field_name];
            }
            else
            {
                return;
            }
        }
        $tmp_data = $this->_offset_data[$offset];

        # 只有是对象的数据才处理
        if ( is_object($tmp_data) )
        {
            # 调用是否修改过数据的回调方法
            if ( true==$is_all_data )
            {
                $is_change = true;
            }
            elseif ( isset($config['object']['callback']['is_change']) )
            {
                $run = $config['object']['callback']['is_change'];
                $is_change = $tmp_data->$run();
            }
            elseif ( $tmp_data instanceof OOP_ORM_Data )
            {
                $is_change = $tmp_data->__orm_callback_data_is_change_();
            }
            else
            {
                $is_change  = true;
            }

            # 没有修改
            if ( !$is_change ) return;

            $tmp_data = OOP_ORM_Parse::get_object_field_data($tmp_data, isset($config['object']['callback']['get_data']) ? $config['object']['callback']['get_data'] : null);
            if ( false === $tmp_data )
            {
                return;
            }
        }

        if ( isset($config['_all_sub_offset']) )
        {
            # 含有子节点数据
            foreach ($config['_all_sub_offset'] as $sub_offset)
            {
                $sub_config = $this->_get_offset_config($sub_offset);
                if ( !$sub_config || !isset($sub_config['_sub_key_name']) )continue;

                if ( isset($this->_offset_data[$sub_offset]) )
                {
                    $sub_data = $this->_offset_data[$sub_offset];
                    if ( is_object($sub_data) )
                    {
                        # 数据是一个对象
                        $sub_data = OOP_ORM_Parse::get_object_field_data($sub_data, isset($sub_config['object']['callback']['get_data']) ? $sub_config['object']['callback']['get_data'] : null);
                    }
                    # 将子节点数据合并进来
                    OOP_ORM_Parse::set_sub_offset_data( $tmp_data , $sub_data , explode('.',$sub_config['_sub_key_name']) );
                }
            }
        }

        # 序列化数据
        if ( isset($config['format']) )
        {
            $tmp_data = OOP_ORM_Parse::format_data($tmp_data, $config['format']);
        }

        if ( $renew_original_field_data )
        {
            $this->_original_field_data[$field_name] = $tmp_data;
        }
        elseif ( true==$is_all_data || $tmp_data!==$this->_original_field_data[$field_name] )
        {
            # 如果需要完整数据，或者数据发生修改
            $data[$field_name] = $tmp_data;
        }
    }

    /**
     * 获取ID字段名称
     */
    public function get_id_field_name()
    {
        return $this->_id_field_name;
    }

    /**
     * 获取原始字段数据
     *
     * @param string $field_name 不传就返回全部
     * @return fixed
     */
    public function get_original_field_data($field_name=null)
    {
        if (null!==$field_name)
        {
            return $this->_original_field_data[$field_name];
        }
        else
        {
            return $this->_original_field_data;
        }
    }

    /**
     * 获取当前ORM
     *
     * @return OOP_ORM_Finder_DB
     * @return OOP_ORM_Finder_Cache
     * @return OOP_ORM_Finder_HttpClient
     */
    public function orm()
    {
        if ( ! $this->_orm_object )
        {
            if ( ! $this->_orm_name )
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
                        $classname = $tmpobj;
                    }
                    if ( preg_match('#^ORM_([a-z0-9_]+)_Data$#i', $classname, $m) )
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
            if ( ! $this->_orm_name )
            {
                throw new Exception('$this->_orm_name未定义');
            }
            $orm_class_name = 'ORM_' . $this->_orm_name . '_Finder';

            if ( ! class_exists($orm_class_name, true) )
            {
                throw new Exception('指定的ORM对象“' . $orm_class_name . '”不存在');
            }
            $this->_orm_object = new $orm_class_name();
        }
        return $this->_orm_object;
    }

    /**
     * 获取当前数据的组的所有数据
     *
     * @return array
     */
    public function get_group_data()
    {
        if ( !$this->_orm_result_uniqid ) return array($this);

        $data = OOP_ORM_Result::get_group_data($this->_orm_result_uniqid);
        if ($data)
        {
            return $data;
        }
        else
        {
            return array($this);
        }
    }

    /**
     * 删除指定OFFSET的ORM的缓存（如果有）
     *
     * @param string $index
     * @return boolean
     */
    public function delete_orm_cache( $index )
    {
        # 获取配置
        $config = $this->_get_offset_config($index);

        # 不存在指定的配置
        if ( ! $config ) return false;

        # 无缓存设置
        if ( !isset($config['orm']['cache']))return true;

        $c_config = $config['orm'];
        foreach ( $c_config['mapping'] as & $item )
        {
            $item = $this->$item;
        }
        return OOP_ORM_Parse::delete_orm_cache_data( $this, $index , $c_config );
    }

    /**
     * 删除指定的offset的缓存（如果有）
     *
     * 如果有ORM cache的设置，会自动调用$this->delete_orm_cache()方法
     *
     * @param string $index
     * @return boolean
     */
    public function delete_offset_cache( $index )
    {
        # 获取配置
        $config = $this->_get_offset_config($index);

        # 不存在指定的配置
        if (!$config) return false;

        # 如果有ORM cache，则清楚ORM cache
        if (isset($config['orm']['cache']))$this->delete_orm_cache($index);

        # 无缓存设置
        if (!isset($config['cache']))return true;

        return OOP_ORM_Parse::delete_offset_cache_data( $this, $index, $config['cache'] );
    }

    /**
     * 清理修改数据记录，以便再次执行$this->update()方法
     */
    protected function _clear_changed_value_setting()
    {
        $this->_data_is_changed = false;
        $this->_value_increment = array();

        # 处理原始数据
        $data = array();
        foreach ( array_keys($this->_has_set_offset) as $offset )
        {
            $this->_renew_offset_changed_value( $data , $offset , false , true );
        }

        # 清除数据缓存
        $this->_remove_id_cache_data();
    }

    /**
     * 创建实例化数据
     *
     * @param string $key
     */
    protected function _create_data($index)
    {
        # 标记为已创建
        $this->_created_offset[$index] = true;

        $config = $this->_get_offset_config($index);
        if (!$config)return false;

        # 是否已经设置过数据
        $offset_isset = isset($this->_offset_data[$index]);

        if ($offset_isset)
        {
            $offset_data = $data = $this->_offset_data[$index];
            if ( is_object($data) && $data instanceof stdClass )
            {
                # 将stdClass对象转成array类型
                $data = (array)$data;
            }
        }
        else
        {
            if ( isset($config['cache']) )
            {
                # 数据缓存
                $data = OOP_ORM_Parse::get_offset_cache_data($this , $index , $config['cache']);
                if (false!==$data)
                {
                    $this->_offset_data[$index] = $data;
                    return true;
                }
            }
            else
            {
                $data = false;
            }

            if ( null===$data || false===$data )
            {
                if ( isset($config['_parent_offset_name']) )
                {
                    # 映射字段
                    $parent_offset = $config['_parent_offset_name'];
                    $data = OOP_ORM_Parse::get_sub_offset_data($this->$parent_offset , explode('.',$config['_sub_key_name']) ,$index );
                }
                elseif ( isset($config['orm']) )
                {
                    # 获取数据
                    $data = OOP_ORM_Parse::get_orm_data_by_config($this,$config['orm'], $index);
                }
                elseif ( isset($config['data']) )
                {
                    # 处理mapping
                    if ( isset($config['data']['mapping']) )
                    {
                        foreach ( $config['data']['mapping'] as $k => $v )
                        {
                            $config['data']['where'][$v] = $this->$k;
                        }
                    }

                    $data = false;
                    # 处理缓存
                    if ( isset($config['data']['cache']) )
                    {
                        $data = OOP_ORM_Parse::get_cache_data($index, @$config['data']['where'], $config['data']['cache']);
                    }
                    if ( false === $data )
                    {
                        # 获取数据
                        $data = OOP_ORM_Parse::get_data($config['data'] , $this);

                        # 缓存数据
                        if ( isset($config['data']['cache']) )
                        {
                            OOP_ORM_Parse::set_cache_data($index, @$config['data']['where'], $config['data']['cache'], $data);
                        }
                    }
                }
                else
                {
                    $data = false;
                }
            }
        }

        // 需要返回OBJECT对象
        if ( isset($config['object']) )
        {
            $object_name = $config['object']['name'];
            if ( !class_exists($object_name, true) )
            {
                throw new Exception('指定的对象:' . $object_name . '不存在！');
            }
            if ( isset($config['object']['callback']['set_data']) )
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

        if ( !$offset_isset || (isset($offset_data) && $data!==$offset_data) )
        {
            $this->_offset_data[$index] = $data;
        }

        # 设置缓存
        if ( false!==$data && isset($config['cache']) )
        {
            OOP_ORM_Parse::set_offset_cache_data($this , $index , $config['cache'] , $data);
        }

        return true;
    }

    /**
     * 清除ID数据缓存
     *
     * @return $this
     */
    protected function _remove_id_cache_data()
    {
        if (IS_CLI)return $this;

        $id_field_name = $this->id_field_name();
        $id = $this->$id_field_name;

        if ( $id && isset(OOP_ORM_Data::$_id_field_cache_data[$this->_class_name][$id]) )
        {
            unset(OOP_ORM_Data::$_id_field_cache_data[$this->_class_name][$id]);
            OOP_ORM_Data::$_id_field_cache_data_num -= 1;
        }

        return $this;
    }

    /**
     * 用于给ORM回调设置ORM对象
     */
    protected function __orm_callback_set_orm_(OOP_ORM $orm)
    {
        $this->_orm_object = $orm;
    }

    /**
     * 用于给ORM回调设置数据
     * @param array $data 待设置的数据
     * @param boolean $is_field_key 待设置的数据的key是否数据库的字段，true是，false则为offset
     */
    protected function __orm_callback_ini_data_($data = null, $is_field_key = false)
    {
        if ( !is_array($data) ) return false;

        # 记录当前orm是否构造完成
        $created = $this->_orm_data_is_created;

        # 将orm设置为未构造状态，因为set_data时会根据构造状态执行不同的操作
        $this->_orm_data_is_created = false;

        # 如果是字段数据，则整理为offset数据
        if ( $is_field_key )
        {
            foreach ($data as $key=>$value)
            {
                # 获取当前字段的首个offset
                $offset = current($this->_get_offset_name_by_field($key));
                if (!$offset)
                {
                    $offset = $key;
                }
                $this->set_data($offset, $value , false);
            }
        }
        else
        {
            # 设置数据
            $this->set_data($data, null , false);
        }

        # 还原状态
        $this->_orm_data_is_created = $created;

        # ID数据缓存,用于重复ID主键数据查询时直接返回
        if (IS_CLI && $is_field_key)
        {
            $id_field_name = $this->id_field_name();
            $id = $data[$id_field_name];

            if ( OOP_ORM_Data::$_id_field_cache_data_num > OOP_ORM_Data::$max_id_cache_num )
            {
                // 超过最大cache数则清除掉
                OOP_ORM_Data::$_id_field_cache_data = array();
            }

            if ( $id && !isset(OOP_ORM_Data::$_id_field_cache_data[$this->_class_name][$id]) )
            {
                OOP_ORM_Data::$_id_field_cache_data[$this->_class_name][$id] = $data;
                OOP_ORM_Data::$_id_field_cache_data_num += 1;
            }
        }
    }

    /**
     * 用于给ORM回调初始化数据
     */
    protected function __orm_callback_ini_result_(OOP_ORM_Result $result)
    {
        $this->_orm_result_uniqid = $result->get_uniqid();
    }

    /**
     * 用于给ORM回调判断是否修改过数据
     *
     * @return boolean
     */
    protected function __orm_callback_data_is_change_()
    {
        if ( $this->_data_is_changed ) return true;
        $data = array();
        foreach ( array_keys($this->_has_set_offset) as $offset )
        {
            $this->_renew_offset_changed_value( $data , $offset , false );
            if ( $data )return true;
        }
        return false;
    }

    /**
     * 返回ID查询数据的缓存内容
     *
     * @param string $class_name
     * @param int/array $id
     * @return array
     */
    public static function id_field_cache_data( $class_name , $id )
    {
        if (IS_CLI)return null;

        $class_name = strtolower($class_name);
        if (is_array($id))
        {
            $tmp = array();
            foreach ($id as $tmpid)
            {
                if ( isset(OOP_ORM_Data::$_id_field_cache_data[$class_name][$tmpid]) )
                {
                    $tmp[$tmpid] = OOP_ORM_Data::$_id_field_cache_data[$class_name][$tmpid];
                }
            }
            return $tmp;
        }
        else if ( isset(OOP_ORM_Data::$_id_field_cache_data[$class_name][$id]) )
        {
            return OOP_ORM_Data::$_id_field_cache_data[$class_name][$id];
        }
        else
        {
            return null;
        }
    }
}