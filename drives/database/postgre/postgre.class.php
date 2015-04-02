<?php

/**
 * 数据库Postgre返回类
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Drive
 * @package    Database
 * @subpackage Postgre
 * @copyright  Copyright (c) 2008-2016 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Drive_Database_Drive_Postgre extends Database_Drive
{
    /**
     * 使用反引号标识符
     *
     * @var string
     */
    protected $_identifier = '`';

    /**
     * 记录当前数据库所对应的页面编码
     * @var array
     */
    protected static $_current_charset = array();

    /**
     * 链接寄存器
     * @var array
     */
    protected static $_connection_instance = array();

    /**
     * 链接寄存器使用数
     *
     * @var array
     */
    protected static $_connection_instance_count = array();

    /**
     * 记录connection id所对应的hostname
     *
     * @var array
     */
    protected static $_current_connection_id_to_hostname = array();

    function __construct(array $config)
    {
        if (!isset($config['port']) || !$config['port']>0)
        {
            $config['port'] = 5432;
        }

        parent::__construct($config);
    }

    /**
     * 连接数据库
     *
     * $use_connection_type 默认不传为自动判断，可传true/false,若传字符串(只支持a-z0-9的字符串)，则可以切换到另外一个连接，比如传other,则可以连接到$this->_connection_other_id所对应的ID的连接
     *
     * @param boolean $use_connection_type 是否使用主数据库
     */
    public function connect($use_connection_type = null)
    {
        if (null!==$use_connection_type)
        {
            $this->_set_connection_type($use_connection_type);
        }

        $connection_id = $this->connection_id();

        # 最后检查连接时间
        static $last_check_connect_time = 0;

        if (!$connection_id || !isset(Database_Drive_Postgre::$_connection_instance[$connection_id]))
        {
            $this->_connect();
        }

        # 如果有当前连接，检查连接
        if ($last_check_connect_time>0 && time()-$last_check_connect_time>=5)
        {
            # 5秒后检查一次连接状态
            $this->_check_connect();
        }

        # 设置编码
        $this->set_charset($this->config['charset']);

        $last_check_connect_time = time();
    }

    /**
     * 获取当前连接
     *
     * @return pg_connect
     */
    public function connection()
    {
        # 尝试连接数据库
        $this->connect();

        # 获取连接ID
        $connection_id = $this->connection_id();

        if ($connection_id && isset(Database_Drive_Postgre::$_connection_instance[$connection_id]))
        {
            return Database_Drive_Postgre::$_connection_instance[$connection_id];
        }
        else
        {
            throw new Exception('Postgre数据库连接异常');
        }
    }

    protected function _connect()
    {
        if ($this->_try_use_exists_connection())
        {
            return;
        }

        $database = $hostname = $port = $socket = $username = $password = $persistent = $schema = null;
        extract($this->config['connection']);

        # 错误服务器
        static $error_host = array();

        $last_error = null;
        while (true)
        {
            $hostname = $this->_get_rand_host($error_host);
            if (false===$hostname)
            {
                if(IS_DEBUG)Core::debug()->warn($error_host, 'error_host');

                if ($last_error && $last_error instanceof Exception)throw $last_error;
                throw new Exception('connect postgre server error.');
            }

            $_connection_id = $this->_get_connection_hash($hostname.'/'.$database, $port, $username);
            Database_Drive_Postgre::$_current_connection_id_to_hostname[$_connection_id] = $hostname.':'.$port;

            try
            {
                $dsn = Database_Drive_Postgre::_get_dsn($database, $hostname, $port, $socket, $username, $password, $persistent, $this->config['connection']);

                $time = microtime(true);

                try
                {
                    if ($persistent)
                    {
                        # 持久连接
                        $tmplink = pg_pconnect($dsn);
                        if ($tmplink && pg_connection_status($tmplink) === PGSQL_CONNECTION_BAD && false===pg_ping($tmplink))
                        {
                            throw new Exception('postgre pconnect server error.');
                        }
                    }
                    else
                    {
                        $tmplink = pg_connect($dsn);
                    }
                }
                catch (Exception $e)
                {
                    $tmplink = false;
                }

                if (false===$tmplink)
                {
                    if (IS_DEBUG)throw $e;

                    throw new Exception('connect postgre server error.');
                }

                if ($schema)
                {
                    @pg_query($tmplink, 'SET search_path TO '.$schema.',public');
                }

                Core::debug()->info('postgre://'.$username.'@'.$hostname.':'.$port.'/'.$database.'/ connection time:' . (microtime(true) - $time));

                # 连接ID
                $this->_connection_ids[$this->_connection_type]                       = $_connection_id;
                # 设置实例化对象
                Database_Drive_Postgre::$_connection_instance[$_connection_id]       = $tmplink;
                # 设置计数器
                Database_Drive_Postgre::$_connection_instance_count[$_connection_id] = 1;

                unset($tmplink);

                break;
            }
            catch (Exception $e)
            {
                if (IS_DEBUG)
                {
                    Core::debug()->warn($username.'@'.$hostname.':'.$port.'.Msg:'.strip_tags($e->getMessage(),'').'.Code:'.$e->getCode(), 'connect postgre server error');
                    $last_error = new Exception($e->getMessage(), $e->getCode());
                }
                else
                {
                    $last_error = new Exception('connect postgre server error', $e->getCode());
                }

                if (!in_array($hostname, $error_host))
                {
                    $error_host[] = $hostname;
                }
            }
        }
    }

    /**
     * @return bool
     * @throws Exception
     */
    protected function _try_use_exists_connection()
    {
        # 检查下是否已经有连接连上去了
        if (Database_Drive_Postgre::$_connection_instance)
        {
            $database = $this->config['connection']['database'];
            $hostname = $this->config['connection']['hostname'];
            if (is_array($hostname))
            {
                $host_config = $hostname[$this->_connection_type];
                if (!$host_config)
                {
                    throw new Exception('指定的数据库连接主从配置中('.$this->_connection_type.')不存在，请检查配置');
                }

                if (!is_array($host_config))
                {
                    $host_config = array($host_config);
                }
            }
            else
            {
                $host_config = array
                (
                    $hostname
                );
            }

            # 先检查是否已经有相同的连接连上了数据库
            foreach ($host_config as $host)
            {
                $_connection_id = $this->_get_connection_hash($host.'/'.$database, $this->config['connection']['port'], $this->config['connection']['username']);

                if (isset(Database_Drive_Postgre::$_connection_instance[$_connection_id]))
                {
                    $this->_connection_ids[$this->_connection_type] = $_connection_id;

                    # 计数器+1
                    Database_Drive_Postgre::$_connection_instance_count[$_connection_id]++;

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * 检查连接是否可用
     *
     * 防止因长时间不链接而导致连接丢失的问题 server has gone away
     *
     * @throws Exception
     */
    protected function _check_connect()
    {
        # 5秒检测1次
        static $error_num = 0;

        try
        {
            $connection_id = $this->connection_id();
            $connection = Database_Drive_Postgre::$_connection_instance[$connection_id];

            if ($connection)
            {
                $ping_status = pg_ping($connection);
            }
            else
            {
                $ping_status = false;
            }
        }
        catch (Exception $e)
        {
            $error_num++;
            $ping_status = false;
        }

        if (!$ping_status)
        {
            if ($error_num<5)
            {
                $this->close_connect();
                # 等待3毫秒
                usleep(3000);

                # 再次尝试连接
                $this->connect();
                $error_num = 0;
            }
            else
            {
                throw new Exception('connect postgre server error');
            }
        }

    }

    /**
     * 关闭链接
     */
    public function close_connect()
{
        if ($this->_connection_ids)foreach ($this->_connection_ids as $key=>$connection_id)
        {
            if ($connection_id && Database_Drive_Postgre::$_connection_instance[$connection_id])
            {
                if (isset(Database_Drive_Postgre::$_connection_instance_count[$connection_id]) && Database_Drive_Postgre::$_connection_instance_count[$connection_id]>1)
                {
                    Database_Drive_Postgre::$_connection_instance_count[$connection_id]--;
                }
                else
                {
                    $link = Database_Drive_Postgre::$_connection_instance[$connection_id];
                    $id   = Database_Drive_Postgre::$_current_connection_id_to_hostname[$connection_id];

                    unset(Database_Drive_Postgre::$_connection_instance[$connection_id]);
                    unset(Database_Drive_Postgre::$_connection_instance_count[$connection_id]);
                    unset(Database_Drive_Postgre::$_current_charset[$connection_id]);
                    unset(Database_Drive_Postgre::$_current_connection_id_to_hostname[$connection_id]);

                    try
                    {
                        pg_close($link);
                    }
                    catch(Exception $e)
                    {}
                    unset($link);

                    if(IS_DEBUG)Core::debug()->info('close '. $key .' postgre '. $id .' connection.');
                }
            }

            $this->_connection_ids[$key] = null;
        }
    }

    /**
     * 设置编码
     *
     * @param string $charset
     * @throws Exception
     * @return void|boolean
     */
    public function set_charset($charset)
    {
        if (!$charset)return false;

        $connection_id = $this->connection_id();
        $connection = Database_Drive_Postgre::$_connection_instance[$connection_id];

        if (!$connection_id || !$connection)
        {
            $this->connect();
            return $this->set_charset($charset);
        }

        if (isset(Database_Drive_Postgre::$_current_charset[$connection_id]) && $charset == Database_Drive_Postgre::$_current_charset[$connection_id])
        {
            return true;
        }

        if (pg_set_client_encoding($connection, $charset) === 0)
        {
            throw new Exception('Error:' . pg_last_error($connection));
        }

        # 记录当前设置的编码
        Database_Drive_Postgre::$_current_charset[$connection_id] = $charset;

        return true;
    }


    public function escape($value)
    {
        $this->_change_charset($value);

        if (is_array($value))
        {
            foreach ($value as $key => $val)
            {
                $value[$key] = $this->escape($val);
            }
            return $value;
        }
        elseif (is_string($value) || (is_object($value) && method_exists($value, '__toString')))
        {
            return "'". pg_escape_string($value) ."'";
        }
        elseif (is_bool($value))
        {
            return ($value === false) ? 0 : 1;
        }
        elseif ($value === null)
        {
            return 'NULL';
        }

        return $value;
    }


    /**
     * 查询
     *
     * $use_connection_type 默认不传为自动判断，可传true/false,若传字符串(只支持a-z0-9的字符串)，则可以切换到另外一个连接，比如传other,则可以连接到$this->_connection_other_id所对应的ID的连接
     *
     * @param string $sql 查询语句
     * @param string $as_object 是否返回对象
     * @param boolean $connection_type 是否使用主数据库，不设置则自动判断
     * @return Database_Drive_Postgre_Result
     */
    public function query($sql, $as_object = null, $connection_type = null)
    {
        $sql  = trim($sql);
        $type = $this->_get_query_type($sql, $connection_type);

        # 设置连接类型
        $this->_set_connection_type($connection_type);

        # 连接数据库
        $connection = $this->connection();

        # 记录调试
        if(IS_DEBUG)
        {
            Core::debug()->info($sql, 'Postgre');

            static $is_sql_debug = null;

            if (null === $is_sql_debug) $is_sql_debug = (bool)Core::debug()->profiler('sql')->is_open();

            if ($is_sql_debug)
            {
                $host = $this->_get_hostname_by_connection_hash($this->connection_id());
                $benchmark = Core::debug()->profiler('sql')->start('Database', 'postgre://' . ($host['username']?$host['username'].'@':'') . $host['hostname'] . ($host['port'] && $host['port'] != '3306' ? ':' . $host['port'] : '') . $host['database']);
            }
        }

        // Execute the query
        if (($result = pg_query($connection, $sql)) === false)
        {
            if (isset($benchmark))
            {
                // This benchmark is worthless
                $benchmark->delete();
            }

            if (IS_DEBUG)
            {
                $err = 'Error:' . pg_last_error($connection) . '. PostgreSQL:' . $sql;
            }
            else
            {
                $err = pg_last_error($connection);
            }
            throw new Exception($err);
        }

        if (isset($benchmark))
        {
            Core::debug()->profiler('sql')->stop();
        }

        // Set the last query
        $this->last_query = $sql;

        if ($type === 'INSERT' || $type === 'REPLACE')
        {
            // Return a list of insert id and rows created
            return array
            (
                $this->_insert_id($result),
                pg_affected_rows($connection)
            );
        }
        elseif ($type === 'UPDATE' || $type === 'DELETE')
        {
            // Return the number of rows affected
            return pg_affected_rows($connection);
        }
        else
        {
            // Return an iterator of results
            return new Database_Drive_Postgre_Result($result, $sql, $as_object ,$this->config);
        }
    }


    /**
     * 获取最后插入的ID
     *
     * @return int
     */
    protected function _insert_id($result)
    {
        $connection = $this->connection();

        $v = pg_version($connection);
        $v = isset($v['server']) ? $v['server'] : 0; // 'server' key is only available since PosgreSQL 7.4

        $table	= (func_num_args() > 0) ? func_get_arg(0) : null;
        $column	= (func_num_args() > 1) ? func_get_arg(1) : null;

        if ($table === null && $v >= '8.1')
        {
            $sql = 'SELECT LASTVAL() AS ins_id';
        }
        elseif ($table !== null)
        {
            if ($column !== null && $v >= '8.0')
            {
                $sql   = 'SELECT pg_get_serial_sequence(\''.$table."', '".$column."') AS seq";
                $query = pg_query($sql);
                $query = pg_fetch_array($query);
                $seq   = $query['seq'];
            }
            else
            {
                // seq_name passed in table parameter
                $seq = $table;
            }

            $sql = 'SELECT CURRVAL(\''.$seq."') AS ins_id";
        }
        else
        {
            return pg_last_oid($result);
        }

        $query = $this->query($sql);
        $query = pg_fetch_array($query);

        return (int)$query['ins_id'];
    }




    protected function _get_dsn($database, $hostname, $port, $socket, $username, $password, $persistent, $config)
    {
        $dsn = '';

        if (false!==strpos($hostname, '/'))
        {
            // If UNIX sockets are used, we shouldn't set a port
            $port = '';
        }

        $hostname === '' OR $dsn = 'host='. $hostname .' ';


        if (!empty($port) && ctype_digit($port))
        {
            $dsn .= 'port='. $port .' ';
        }

        if ($username!=='')
        {
            $dsn .= 'user='. $username .' ';

            /* An empty password is valid!
             *
            * $db['password'] = NULL must be done in order to ignore it.
            */
            $password === null OR $dsn .= "password='" . $password ."' ";
        }

        $database === '' OR $dsn .= 'dbname='. $database .' ';

        /* We don't have these options as elements in our standard configuration
         * array, but they might be set by parse_url() if the configuration was
        * provided via string. Example:
        *
        * postgre://username:password@localhost:5432/database?connect_timeout=5&sslmode=1
        */
        foreach (array('connect_timeout', 'options', 'sslmode', 'service') as $key)
        {
            if (isset($config[$key]) && is_string($config[$key]) && $config[$key] !== '')
            {
                $dsn .= $key."='". $config[$key] ."' ";
            }
        }

        $dsn = rtrim($dsn);

        return $dsn;
    }
}