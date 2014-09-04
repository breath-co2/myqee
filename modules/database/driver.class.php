<?php

/**
 * 数据库驱动核心类
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   MyQEE
 * @package    Module
 * @subpackage Database
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
abstract class Module_Database_Driver
{
    /**
     * 当前连接类型 master|slaver
     *
     * @var string
     */
    protected $_connection_type = 'slaver';

    /**
     * 当前连接的所有的ID
     *
     *    array(
     *    	'master' => 'abcdef...',
     *    	'slaver' => 'defdef...',
     *    )
     *
     * @var array
     */
    protected $_connection_ids = array
    (
        'master' => null,
        'slaver' => null,
    );

    /**
     * 最后查询SQL语句
     *
     * @var string
     */
    protected $last_query = '';

    /**
     * 当前配置
     * @var array
     */
    protected $config;

    /**
     * 字符串引用符号
     *
     * @var string
     */
    protected $_identifier = '"';

    protected $_as_table = array();

    /**
     * 记录事务
     * array(
     * '连接ID'=>'父事务ID',
     * '连接ID'=>'父事务ID',
     * ...
     * )
     * @var array
     */
    protected static $transactions = array();

    /**
     * 记录hash对应的host数据
     * @var array
     */
    protected static $_hash_to_hostname = array();

    public function __construct($config)
    {
        $this->config = $config;
        if ( !is_array($this->config['connection']['hostname']) )
        {
            # 主从链接采用同一个内存地址
            $this->_connection_ids['master'] =& $this->_connection_ids['slaver'];
        }
    }

    public function __destruct()
    {
        $this->close_connect();
    }

    /**
     * 构建SQL语句
     */
    abstract public function compile($builder, $type = 'select');

    /**
     * 查询
     * @param string $sql 查询语句
     * @param string $as_object 是否返回对象
     * @param boolean $use_master 是否使用主数据库，不设置则自动判断
     * @return Database_Driver_MySQLI_Result
     */
    abstract public function query($sql, $as_object = null, $use_master = null);

    /**
     * 连接数据库
     *
     * $use_connection_type 默认不传为自动判断，可传true/false,若传字符串(只支持a-z0-9的字符串)，则可以切换到另外一个连接，比如传other,则可以连接到$this->_connection_other_id所对应的ID的连接
     *
     * @param boolean $use_connection_type 是否使用主数据库
     */
    abstract public function connect($use_connection_type = null);

    /**
     * 关闭链接
     */
    abstract public function close_connect();

    /**
     * Sanitize a string by escaping characters that could cause an SQL
     * injection attack.
     *
     * $value = $db->escape('any string');
     *
     * @param   string   value to quote
     * @return  string
     */
    abstract public function escape($value);

    abstract public function quote_table($value);

    /**
     * Quote a value for an SQL query.
     *
     * $db->quote(null);   // 'NULL'
     * $db->quote(10);     // 10
     * $db->quote('fred'); // 'fred'
     *
     * Objects passed to this function will be converted to strings.
     * [Database_Expression] objects will use the value of the expression.
     * [Database_Query] objects will be compiled and converted to a sub-query.
     * All other objects will be converted using the `__toString` method.
     *
     * @param   mixed   any value to quote
     * @return  string
     * @uses    Database::escape
     */
    abstract public function quote($value);

    /**
     * 获取当前连接
     *
     * @return mysqli
     * @return mysql
     */
    abstract public function connection();

    /**
     * 获取当前连接的唯一ID
     *
     * @return string
     */
    public function connection_id()
    {
        return $this->_connection_ids[$this->_connection_type];
    }

    /**
     * 获取事务对象
     *
     * @return Database_Transaction
     */
    public function transaction()
    {
        $tr_name = 'Database_Driver_'.$this->config['type'].'_Transaction';

        if (!class_exists($tr_name, true))
        {
            throw new Exception(__('the transaction of :driver not exist.', array(':driver'=>$this->config['type'])));
        }

        return new $tr_name($this);
    }

    /**
     * 最后查询的SQL语句
     *
     * @return string
     */
    public function last_query()
    {
        return $this->last_query;
    }

    /**
     * 获取一个随机HOST
     *
     * @param array $exclude_hosts 排除的HOST
     * @param string $type 配置类型
     */
    protected function _get_rand_host($exclude_hosts = array() , $type = null)
    {
        if (!$type)$type = $this->_connection_type;
        $hostname = $this->config['connection']['hostname'];

        if (!is_array($hostname))
        {
            if ( in_array($hostname, $exclude_hosts) )
            {
                return false;
            }

            if ($exclude_hosts && $type!='master' && in_array($hostname, $exclude_hosts))
            {
                # 如果相应的slave都已不可获取，则改由获取master
                return $this->_get_rand_host($exclude_hosts, 'master');
            }

            return $hostname;
        }

        $hostconfig = $hostname[$type];

        if (is_array($hostconfig))
        {
            if ($exclude_hosts)
            {
                $hostconfig = array_diff($hostconfig, $exclude_hosts);
            }

            $hostconfig = array_values($hostconfig);
            $count = count($hostconfig);

            if ($count==0)
            {
                if ($type!='master')
                {
                    return $this->_get_rand_host($exclude_hosts,'master');
                }
                else
                {
                    return false;
                }
            }

            # 获取一个随机链接
            $rand_id = mt_rand(0, $count - 1);

            return $hostconfig[$rand_id];
        }
        else
        {
            if (in_array($hostconfig, $exclude_hosts))
            {
                return false;
            }

            return $hostconfig;
        }
    }

    /**
     * 获取链接唯一hash
     *
     * @param string $hostname
     * @param int $port
     * @param string $username
     * @return string
     */
    protected function _get_connection_hash($hostname, $port, $username)
    {
        $hash = sha1(get_class($this) .'_'. $hostname .'_'. $port .'_'. $username);

        Database_Driver::$_hash_to_hostname[$hash] = array
        (
            'hostname' => $hostname,
            'port'     => $port,
            'username' => $username,
        );

        return $hash;
    }

    /**
     * 根据数据库连接唯一hash获取数据信息
     *
     * @param string $has
     * @return array array('hostname'=>'','port'=>'','username'=>'')
     */
    protected static function _get_hostname_by_connection_hash($hash)
    {
        return Database_Driver::$_hash_to_hostname[$hash];
    }

    /**
     * 切换编码
     *
     * @param string $value
     */
    protected function _change_charset(&$value)
    {
        if ($this->config['auto_change_charset'] && $this->config['charset']!='UTF8')
        {
            # 转换编码编码
            if (IS_MBSTRING)
            {
                $value = (string)mb_convert_encoding($value, $this->config['data_charset'],'UTF-8');
            }
            else
            {
                $value = (string)iconv('UTF-8', $this->config['data_charset'].'//IGNORE', $value);
            }
        }

        return $value;
    }

    /**
     * 设置连接类型
     *
     *    $use_connection_type 默认不传为自动判断，可传true/false,若传字符串(只支持a-z0-9的字符串)，则可以切换到另外一个连接，比如传other,则可以连接到$this->_connection_other_id所对应的ID的连接
     *
     * @param boolean|string $use_connection_type
     */
    protected function _set_connection_type($use_connection_type)
    {
        if (true===$use_connection_type)
        {
            $use_connection_type = 'master';
        }
        elseif (false===$use_connection_type)
        {
            $use_connection_type = 'slaver';
        }
        elseif (!$use_connection_type)
        {
            return;
        }

        $this->_connection_type = $use_connection_type;
    }

}