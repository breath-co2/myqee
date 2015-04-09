<?php

/**
 * 数据库驱动核心类
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   MyQEE
 * @package    Module
 * @subpackage Database
 * @copyright  Copyright (c) 2008-2016 myqee.com
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

    /**
     * 默认端口
     *
     * @var int
     */
    protected $_default_port = null;

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

    public function __construct(array $config)
    {
        $this->config = $config;
        if (!is_array($this->config['connection']['hostname']))
        {
            # 主从链接采用同一个内存地址
            $this->_connection_ids['master'] =& $this->_connection_ids['slaver'];
        }

        if ($this->_default_port && (!isset($this->config['connection']['port']) || !$this->config['connection']['port']>0))
        {
            $this->config['connection']['port'] = $this->_default_port;
        }
    }

    public function __destruct()
    {
        $this->close_connect();
    }

    /**
     * 执行构造语法执行
     *
     * @param string $statement
     * @param array $input_parameters
     * @param null|bool|string $as_object
     * @param null|bool|string $connection_type
     * @return Database_Driver_MySQLI_Result
     */
    public function execute($statement, array $input_parameters, $as_object = null, $connection_type = null)
    {
        $num_parameters = array();
        foreach($input_parameters as $key => $value)
        {
            if (is_int($key))
            {
                $num_parameters[$key] = $value;
            }
            else
            {
                $statement = str_replace($key, $this->quote($value), $statement);
            }
        }

        if ($num_parameters)
        {
            # 用 ? 分割开
            $statement_arr = explode('?', $statement);

            # 填补缺失的key，例如 $num_parameters = array(0=>'a', 2=>'b'); 缺失了 1
            foreach($statement_arr as $key => $value)
            {
                if (!isset($statement_arr[$key]))$statement_arr[$key] = '?';
            }

            foreach($num_parameters as $key => $value)
            {
                $statement_arr[$key] = $this->quote($value) . $statement_arr[$key];
            }

            # 拼接
            $statement = implode('', $statement_arr);
        }

        return $this->query($statement, $as_object, $connection_type);
    }

    /**
     * 构建SQL语句
     */
    public function compile($builder, $type = 'select')
    {
        if ($type == 'select')
        {
            return $this->_compile_select($builder);
        }
        else if ($type == 'insert')
        {
            return $this->_compile_insert($builder);
        }
        elseif ($type == 'replace')
        {
            return $this->_compile_insert($builder, 'REPLACE');
        }
        elseif ($type == 'update')
        {
            return $this->_compile_update($builder);
        }
        elseif ($type == 'delete')
        {
            return $this->_compile_delete($builder);
        }
        else
        {
            return $this->_compile_select($builder);
        }
    }

    /**
     * 查询
     * @param string $sql 查询语句
     * @param bool|string $as_object 是否返回对象
     * @param bool|string $use_master 是否使用主数据库，不设置则自动判断
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
     * @param  string $value  value to quote
     * @return string
     */
    abstract public function escape($value);

    /**
     * Quote a database table name and adds the table prefix if needed.
     *
     * $table = $db->quote_table($table);
     *
     * @param   mixed  $value table name or array(table, alias)
     * @param   bool  $auto_as_table
     * @return  string
     * @uses    Database::_quote_identifier
     * @uses    Database::table_prefix
     */
    public function quote_table($value, $auto_as_table = false)
    {
        // Assign the table by reference from the value
        if (is_array($value))
        {
            $table = & $value[0];
        }
        else
        {
            $table = & $value;
        }

        if ($this->config['table_prefix'] && is_string($table) && strpos($table, '.') === false)
        {
            if (stripos($table, ' AS ')!==false)
            {
                $table = $this->config['table_prefix'] . $table;
            }
            else
            {
                $table = $this->config['table_prefix'] . $table . ($auto_as_table?' AS '.$table:'');
            }
        }

        return $this->_quote_identifier($value);
    }

    /**
     * Quote a value for an SQL query.
     *
     * $db->quote(null);   // NULL
     * $db->quote(10);     // 10
     * $db->quote('fred'); // 'fred'
     *
     * Objects passed to this function will be converted to strings.
     * [Database_Expression] objects will use the value of the expression.
     * [Database_Query] objects will be compiled and converted to a sub-query.
     * All other objects will be converted using the `__toString` method.
     *
     * @param   mixed  $value any value to quote
     * @return  string
     * @uses    Database::escape
     */
    public function quote($value)
    {
        if ($value === null)
        {
            return 'NULL';
        }
        elseif ($value === true)
        {
            return "'1'";
        }
        elseif ($value === false)
        {
            return "'0'";
        }
        elseif (is_object($value))
        {
            if ($value instanceof Database)
            {
                // Create a sub-query
                return '('. $value->compile() .')';
            }
            elseif ($value instanceof Database_Expression)
            {
                // Use a raw expression
                return $value->value();
            }
            elseif ($value instanceof ArrayObject || $value instanceof ArrayIterator || $value instanceof stdClass)
            {
                return '('. implode(', ', array_map(array($this, __FUNCTION__), (array)$value)) .')';
            }
            else
            {
                // Convert the object to a string
                return $this->quote((string)$value);
            }
        }
        elseif (is_array($value))
        {
            return '(' . implode(', ', array_map(array($this, __FUNCTION__), $value)) . ')';
        }
        elseif (is_int($value))
        {
            return "'". $value ."'";
        }
        elseif (is_float($value))
        {
            // Convert to non-locale aware float to prevent possible commas
            return sprintf('%F', $value);
        }

        return $this->escape($value);
    }

    /**
     * 获取当前连接
     *
     * @return mysqli
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
        $tr_name = 'Database_Driver_'. $this->config['type'] .'_Transaction';

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
     * 创建一个数据库
     *
     * @param string $database
     * @param string $charset 编码，不传则使用数据库连接配置相同到编码
     * @param string $collate 整理格式
     * @return boolean
     * @throws Exception
     */
    public function create_database($database, $charset = null, $collate = null)
    {
        $config = $this->config;
        $this->config['connection']['database'] = null;
        if (!$charset)
        {
            $charset = $this->config['charset'];
        }
        $sql = 'CREATE DATABASE ' . $this->_quote_identifier($database) .' DEFAULT CHARACTER SET '. $charset;
        if ($collate)
        {
            $sql .= ' COLLATE '. $collate;
        }
        try
        {
            $result = $this->query($sql, null, true)->result();
            $this->config = $config;
            return $result;
        }
        catch (Exception $e)
        {
            $this->config = $config;
            throw $e;
        }
    }

    /**
     * 获取一个随机HOST
     *
     * @param array $exclude_hosts 排除的HOST
     * @param string $type 配置类型
     */
    protected function _get_rand_host($exclude_hosts = array(), $type = null)
    {
        if (!$type)$type = $this->_connection_type;
        $hostname = $this->config['connection']['hostname'];

        if (!is_array($hostname))
        {
            if (in_array($hostname, $exclude_hosts))
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

        $host_config = $hostname[$type];

        if (is_array($host_config))
        {
            if ($exclude_hosts)
            {
                $host_config = array_diff($host_config, $exclude_hosts);
            }

            $host_config = array_values($host_config);
            $count = count($host_config);

            if ($count==0)
            {
                if ($type!='master')
                {
                    return $this->_get_rand_host($exclude_hosts, 'master');
                }
                else
                {
                    return false;
                }
            }

            # 获取一个随机链接
            $rand_id = mt_rand(0, $count - 1);

            return $host_config[$rand_id];
        }
        else
        {
            if (in_array($host_config, $exclude_hosts))
            {
                return false;
            }

            return $host_config;
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
            'host'     => $hostname,
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
    protected function _change_charset(& $value)
    {
        if ($this->config['auto_change_charset'] && $this->config['charset']!='UTF8')
        {
            # 转换编码编码
            if (IS_MBSTRING)
            {
                $value = mb_convert_encoding((string)$value, $this->config['data_charset'], 'UTF-8');
            }
            else
            {
                $value = iconv('UTF-8', $this->config['data_charset'] .'//IGNORE', (string)$value);
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

    protected function _get_query_type($sql, & $connection_type)
    {
        if (preg_match('#^([a-z]+)(:? |\n|\r)#i', $sql, $m))
        {
            $type = strtoupper($m[1]);
        }
        else
        {
            $type = null;
        }

        $typeArr = array
        (
            'SELECT',
            'SHOW',     //显示表
            'EXPLAIN',  //分析
            'DESCRIBE', //显示结结构
            'INSERT',
            'REPLACE',
            'UPDATE',
            'DELETE',
        );

        if (!in_array($type, $typeArr))
        {
            $type = 'MASTER';
        }

        $slaverType = array('SELECT', 'SHOW', 'EXPLAIN');
        if ($type!='MASTER' && in_array($type, $slaverType))
        {
            if (true === $connection_type)
            {
                $connection_type = 'master';
            }
            else if (is_string($connection_type))
            {
                if (!preg_match('#^[a-z0-9_]+$#i', $connection_type))$connection_type = 'master';
            }
            else
            {
                $connection_type = 'slaver';
            }
        }
        else
        {
            $connection_type = 'master';
        }

        return $type;
    }

    protected function _quote_identifier($column)
    {
        if (is_array($column))
        {
            list($column, $alias) = $column;
        }

        if (is_object($column))
        {
            if ($column instanceof Database)
            {
                // Create a sub-query
                $column = '('. $column->compile() .')';
            }
            elseif ($column instanceof Database_Expression)
            {
                // Use a raw expression
                $column = $column->value();
            }
            else
            {
                // Convert the object to a string
                $column = $this->_quote_identifier((string)$column);
            }
        }
        else
        {
            # 转换为字符串
            $column = trim((string)$column);

            if (preg_match('#^(.*) AS (.*)$#i', $column, $m))
            {
                $column = $m[1];
                $alias  = $m[2];
            }

            if ($column === '*')
            {
                return $column;
            }
            elseif (strpos($column, '"') !== false)
            {
                // Quote the column in FUNC("column") identifiers
                $column = preg_replace('/"(.+?)"/e', '$this->_quote_identifier("$1")', $column);
            }
            elseif (strpos($column, '.') !== false)
            {
                $parts = explode('.', $column);

                $prefix = $this->config['table_prefix'];
                if ($prefix)
                {
                    // Get the offset of the table name, 2nd-to-last part
                    $offset = count($parts) - 2;

                    if (!$this->_as_table || !in_array($parts[$offset], $this->_as_table))
                    {
                        $parts[$offset] = $prefix . $parts[$offset];
                    }
                }

                foreach ($parts as & $part)
                {
                    if ($part !== '*')
                    {
                        // Quote each of the parts
                        $this->_change_charset($part);
                        $part = $this->_identifier . str_replace(array($this->_identifier, '\\'), '', $part) . $this->_identifier;
                    }
                }

                $column = implode('.', $parts);
            }
            else
            {
                $this->_change_charset($column);
                $column = $this->_identifier . str_replace(array($this->_identifier, '\\'), '', $column) . $this->_identifier;
            }
        }

        if (isset($alias))
        {
            $this->_change_charset($alias);
            $column .= ' AS ' . $this->_identifier . str_replace(array($this->_identifier, '\\'), '', $alias) . $this->_identifier;
        }

        return $column;
    }

    protected function _compile_select($builder)
    {
        $quote_ident = array($this, '_quote_identifier');

        $quote_table = array($this, 'quote_table');

        $query = 'SELECT ';

        if ($builder['distinct'])
        {
            if (true===$builder['distinct'])
            {
                $query .= 'DISTINCT ';
            }
            else
            {
                $builder['select_adv'][] = array
                (
                    $builder['distinct'],
                    'distinct',
                );
            }
        }

        $this->_init_as_table($builder);
        $this->_format_select_adv($builder);
        $this->_format_group_concat($builder);

        if (empty($builder['select']))
        {
            $query .= '*';
        }
        else
        {
            $query .= implode(', ', array_unique(array_map($quote_ident, $builder['select'])));
        }

        if (!empty($builder['from']))
        {
            // Set tables to select from
            $query .= ' FROM ' . implode(', ', array_unique(array_map($quote_table, $builder['from'], array(true))));
        }

        if (!empty($builder['index']))
        {
            foreach ($builder['index'] as $item)
            {
                $query .= ' '. strtoupper($item[1]) .' INDEX('. $this->_quote_identifier($item[0]) .')';
            }
        }

        if (!empty($builder['join']))
        {
            // Add tables to join
            $query .= ' '. $this->_compile_join($builder['join']);
        }

        if (!empty($builder['where']))
        {
            // Add selection conditions
            $query .= ' WHERE '. $this->_compile_conditions($builder['where']);
        }

        if (!empty($builder['group_by']))
        {
            // Add sorting
            $query .= ' GROUP BY '. implode(', ', array_map($quote_ident, $builder['group_by']));
        }

        if (!empty($builder['having']))
        {
            // Add filtering conditions
            $query .= ' HAVING '. $this->_compile_conditions($builder['having']);
        }

        if (!empty($builder['order_by']))
        {
            // Add sorting
            $query .= ' '. $this->_compile_order_by($builder['order_by']);
        }
        elseif ($builder['where'])
        {
            # 如果查询中有in查询，采用自动排序方式
            $in_query = null;
            foreach ($builder['where'] as $item)
            {
                if (isset($item['AND']) && $item['AND'][1] == 'in')
                {
                    if (count($item['AND'][1]) > 1)
                    {
                        # 大于1项才需要排序
                        $in_query = $item['AND'];
                    }
                    break;
                }
            }
            if ($in_query)
            {
                $query .= ' ORDER BY FIELD('. $this->_quote_identifier($in_query[0]) .', '. implode(', ', $this->quote($in_query[2])) .')';
            }
        }

        if ($builder['limit'] !== null)
        {
            // Add limiting
            $query .= ' LIMIT '. $builder['limit'];
        }

        if ($builder['offset'] !== null)
        {
            // Add offsets
            $query .= ' OFFSET '. $builder['offset'];
        }

        return $query;
    }

    /**
     * Compile the SQL query and return it.
     *
     * @return  string
     */
    protected function _compile_insert($builder, $type = 'INSERT')
    {
        if ($type != 'REPLACE')
        {
            $type = 'INSERT';
        }
        // Start an insertion query
        $query = $type . ' INTO ' . $this->quote_table($builder['table'], false);

        // Add the column names
        $query .= ' (' . implode(', ', array_map(array($this, '_quote_identifier'), $builder['columns'])) .') ';

        if (is_array($builder['values']))
        {
            // Callback for quoting values
            $quote = array($this, 'quote');

            $groups = array();
            foreach ($builder['values'] as $group)
            {
                $groups[] = '('. implode(', ', array_map($quote, $group)) .')';
            }

            // Add the values
            $query .= 'VALUES '. implode(', ', $groups);
        }
        else
        {
            // Add the sub-query
            $query .= (string)$builder['values'];
        }

        if ($type == 'REPLACE')
        {
            //where
            if (!empty($builder['where']))
            {
                // Add selection conditions
                $query .= ' WHERE '. $this->_compile_conditions($builder['where']);
            }
        }

        return $query;
    }

    protected function _compile_update($builder)
    {
        // Start an update query
        $query = 'UPDATE '. $this->quote_table($builder['table'], false);

        // Add the columns to update
        $query .= ' SET '. $this->_compile_set($builder['set']);

        if (!empty($builder['where']))
        {
            // Add selection conditions
            $query .= ' WHERE '. $this->_compile_conditions($builder['where']);
        }

        if (!empty($builder['order_by']))
        {
            // Add sorting
            $query .= ' '. $this->_compile_order_by($builder['order_by']);
        }

        if ($builder['limit'] !== null)
        {
            // Add limiting
            $query .= ' LIMIT '. $builder['limit'];
        }

        if ($builder['offset'] !== null)
        {
            // Add offsets
            $query .= ' OFFSET '. $builder['offset'];
        }

        return $query;
    }

    protected function _compile_delete($builder)
    {
        // Start an update query
        $query = 'DELETE FROM'. $this->quote_table($builder['table'], false);

        if (!empty($builder['where']))
        {
            $this->_init_as_table($builder);

            // Add selection conditions
            $query .= ' WHERE '. $this->_compile_conditions($builder['where']);
        }

        return $query;
    }

    /**
     * Compiles an array of ORDER BY statements into an SQL partial.
     *
     * @param   array  $columns sorting columns
     * @return  string
     */
    protected function _compile_order_by(array $columns)
    {
        $sort = array();
        foreach ($columns as $group)
        {
            list ($column, $direction) = $group;

            if (!empty($direction))
            {
                // Make the direction uppercase
                $direction = ' '. strtoupper($direction);
            }

            $sort[] = $this->_quote_identifier($column) . $direction;
        }

        return 'ORDER BY '. implode(', ', $sort);
    }

    /**
     * Compiles an array of conditions into an SQL partial. Used for WHERE
     * and HAVING.
     *
     * @param   array  $conditions condition statements
     * @return  string
     */
    protected function _compile_conditions(array $conditions)
    {
        $last_condition = null;

        $sql = '';
        foreach ($conditions as $group)
        {
            // Process groups of conditions
            foreach ($group as $logic => $condition)
            {
                if ($condition === '(')
                {
                    if (!empty($sql) && $last_condition !== '(')
                    {
                        // Include logic operator
                        $sql .= ' ' . $logic . ' ';
                    }

                    $sql .= '(';
                }
                elseif ($condition === ')')
                {
                    $sql .= ')';
                }
                else
                {
                    if (!empty($sql) && $last_condition !== '(')
                    {
                        // Add the logic operator
                        $sql .= ' '. $logic .' ';
                    }

                    // Split the condition
                    list ($column, $op, $value) = $condition;

                    if ($value === null)
                    {
                        if ($op === '=')
                        {
                            // Convert "val = NULL" to "val IS NULL"
                            $op = 'IS';
                        }
                        elseif ($op === '!=' || $op === '<>')
                        {
                            // Convert "val != NULL" to "valu IS NOT NULL"
                            $op = 'IS NOT';
                        }
                    }

                    // Database operators are always uppercase
                    $op = strtoupper($op);

                    if (is_array($value) && count($value)<=1)
                    {
                        # 将in条件下只有1条数据的改为where方式
                        if ($op == 'IN')
                        {
                            $op = '=';
                            $value = current($value);
                        }
                        elseif ($op == 'NOT IN')
                        {
                            $op = '!=';
                            $value = current($value);
                        }
                    }

                    if ($op === 'BETWEEN' && is_array($value))
                    {
                        // BETWEEN always has exactly two arguments
                        list ($min, $max) = $value;

                        // Quote the min and max value
                        $value = $this->quote($min) .' AND '. $this->quote($max);
                    }
                    elseif ($op == 'MOD')
                    {
                        $value = $this->quote($value[0]) .' '. strtoupper($value[2]) .' '. $this->quote($value[1]);
                    }
                    else
                    {
                        if (is_array($value))
                        {
                            if ($op=='=')
                            {
                                $op = 'IN';
                            }
                            elseif ($op=='!=')
                            {
                                $op = 'NOT IN';
                            }
                        }

                        $value = $this->quote($value);
                    }

                    // Append the statement to the query
                    $sql .= $this->_quote_identifier($column) .' '. $op .' '. $value;
                }

                $last_condition = $condition;
            }
        }

        return $sql;
    }

    /**
     * Compiles an array of JOIN statements into an SQL partial.
     *
     * @param   array  $joins join statements
     * @return  string
     */
    protected function _compile_join(array $joins)
    {
        $statements = array();

        foreach ($joins as $join)
        {
            $statements[] = $this->_compile_join_on($join);
        }

        return implode(' ', $statements);
    }

    protected function _compile_join_on($join)
    {
        if ($join['type'])
        {
            $sql = strtoupper($join['type']) .' JOIN';
        }
        else
        {
            $sql = 'JOIN';
        }

        // Quote the table name that is being joined
        $sql .= ' '. $this->quote_table($join['table'],true) .' ON ';

        $conditions = array();
        foreach ($join['on'] as $condition)
        {
            // Split the condition
            list ($c1, $op, $c2) = $condition;

            if ($op)
            {
                // Make the operator uppercase and spaced
                $op = ' ' . strtoupper($op);
            }

            // Quote each of the identifiers used for the condition
            $conditions[] = $this->_quote_identifier($c1) . $op .' '. $this->_quote_identifier($c2);
        }

        // Concat the conditions "... AND ..."
        $sql .= '('. implode(' AND ', $conditions) .')';

        return $sql;
    }

    /**
     * Compiles an array of set values into an SQL partial. Used for UPDATE.
     *
     * @param   array  $values updated values
     * @return  string
     */
    protected function _compile_set(array $values)
    {
        $set = array();
        foreach ($values as $group)
        {
            // Split the set
            list ($column, $value , $op) = $group;

            if ($op=='+' || $op=='-')
            {
                $w_type = $op;
            }
            else
            {
                $w_type = '';
            }

            $column = $this->_quote_identifier($column);

            if ($w_type)
            {
                $set[$column] = $column .' = '. $column .' '. $w_type .' '. $this->quote($value);
            }
            else
            {
                $set[$column] = $column .' = '. $this->quote($value);
            }
        }

        return implode(', ', $set);
    }


    /**
     * 初始化所有的as_table
     */
    protected function _init_as_table($builder)
    {
        $this->_as_table = array();

        if ($builder['from'])
        {
            foreach ($builder['from'] as $item)
            {
                $this->_do_init_as_table($item);
            }
        }

        if ($builder['join'])
        {
            foreach ($builder['join'] as $item)
            {
                $this->_do_init_as_table($item['table']);
            }
        }
    }

    protected function _do_init_as_table($value)
    {
        if (is_array($value))
        {
            list ($value, $alias) = $value;
        }
        elseif (is_object($value))
        {
            if ($value instanceof Database)
            {
                $value = $value->compile();
            }
            elseif ($value instanceof Database_Expression)
            {
                $value = $value->value();
            }
            else
            {
                $value = (string)$value;
            }
        }
        $value = trim($value);

        if (preg_match('#^(.*) AS ([a-z0-9`_]+)$#i', $value , $m))
        {
            $alias = $m[2];
        }
        elseif ($this->config['table_prefix'] && strpos($value, '.') === false)
        {
            $alias = $value;
        }
        else
        {
            $alias = null;
        }

        if ($alias)
        {
            $this->_as_table[] = $alias;
        }
    }

    /**
     * 格式化高级查询参数到select里
     */
    protected function _format_select_adv(&$builder)
    {
        if ($builder['select_adv'])foreach ($builder['select_adv'] as $item)
        {
            if (!is_array($item))continue;

            if (is_array($item[0]))
            {
                $column = $item[0][0];
                $alias  = $item[0][1];
            }
            else if (preg_match('#^(.*) AS (.*)$#i', $item[0], $m))
            {
                $column = $this->_quote_identifier($m[1]);
                $alias  = $m[2];
            }
            else
            {
                $column = $this->_quote_identifier($item[0]);
                $alias = $item[0];
            }

            // 其它参数
            $args_str = '';
            if (($count_item=count($item))>2)
            {
                for($i=2; $i<$count_item; $i++)
                {
                    $args_str .= ','. $this->_quote_identifier($item[$i]);
                }
            }

            $builder['select'][] = array
            (
                Database::expr_value(strtoupper($item[1]) .'('. $this->_quote_identifier($column.$args_str) .')'),
                $alias,
            );
        }
    }

    /**
     * 解析 GROUP_CONCAT
     *
     * @param array $arr
     * @return string
     */
    protected function _format_group_concat(&$builder)
    {
        if ($builder['group_concat'])foreach($builder['group_concat'] as $item)
        {
            if (is_array($item[0]))
            {
                $column = $item[0][0];
                $alias  = $item[0][1];
            }
            else if (preg_match('#^(.*) AS (.*)$#i', $item[0] , $m))
            {
                $column = $this->_quote_identifier($m[1]);
                $alias  = $m[2];
            }
            else
            {
                $column = $this->_quote_identifier($item[0]);
                $alias  = $item[0];
            }

            $str = 'GROUP_CONCAT(';

            if (isset($item[3]) && $item[3])
            {
                $str .= 'DISTINCT ';
            }
            $str .= $column;

            if (isset($item[1]) && $item[1])
            {
                $str .= ' ORDER BY '. $column .' '. (strtoupper($item[1])=='DESC'?'DESC':'ASC');
            }

            if ($item[2])
            {
                $str .= ' SEPARATOR '. $this->_quote_identifier($item[2]);
            }

            $str .= ')';

            $builder['select'][] = array
            (
                Database::expr_value($str),
                $alias,
            );
        }
    }
}