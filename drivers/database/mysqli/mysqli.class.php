<?php

/**
 * 数据库MySQLI返回类
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Driver
 * @package    Database
 * @subpackage MySQLI
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Driver_Database_Driver_MySQLI extends Database_Driver
{
    /**
     * MySQL使用反引号标识符
     *
     * @var string
     */
    protected $_identifier = '`';

    /**
     * 记录当前连接所对应的数据库
     * @var array
     */
    protected static $_current_databases = array();

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
            $config['port'] = 3306;
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

        if (!$connection_id || !isset(Database_Driver_MySQLI::$_connection_instance[$connection_id]))
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

        # 切换表
        $this->select_db($this->config['connection']['database']);

        $last_check_connect_time = time();
    }

    /**
     * 获取当前连接
     *
     * @return mysqli
     */
    public function connection()
    {
        # 尝试连接数据库
        $this->connect();

        # 获取连接ID
        $connection_id = $this->connection_id();

        if ($connection_id && isset(Database_Driver_MySQLI::$_connection_instance[$connection_id]))
        {
            return Database_Driver_MySQLI::$_connection_instance[$connection_id];
        }
        else
        {
            throw new Exception('数据库连接异常');
        }
    }

    protected function _connect()
    {
        if ($this->_try_use_exists_connection())
        {
            return;
        }

        $database = $hostname = $port = $socket = $username = $password = $persistent = null;
        extract($this->config['connection']);

        # 错误服务器
        static $error_host = array();

        $last_error = null;
        while (true)
        {
            $hostname = $this->_get_rand_host($error_host);
            if (false===$hostname)
            {
                if(IS_DEBUG)Core::debug()->error($error_host, 'error_host');

                if ($last_error && $last_error instanceof Exception)throw $last_error;
                throw new Exception('connect mysqli server error.');
            }

            $_connection_id = $this->_get_connection_hash($hostname, $port, $username);
            Database_Driver_MySQLI::$_current_connection_id_to_hostname[$_connection_id] = $hostname.':'.$port;

            try
            {
                $time = microtime(true);

                $error_code = 0;
                $error_msg  = '';
                try
                {
                    if (empty($persistent))
                    {
                        $tmplink = mysqli_init();
                        mysqli_options($tmplink, MYSQLI_OPT_CONNECT_TIMEOUT, 3);
                        mysqli_real_connect($tmplink, $hostname, $username, $password, $database, $port, null, MYSQLI_CLIENT_COMPRESS);
                    }
                    else
                    {
                        $tmplink = new mysqli($hostname, $username, $password, $database, $port);
                    }
                }
                catch (Exception $e)
                {
                    $error_msg  = $e->getMessage();
                    $error_code = $e->getCode();
                    $tmplink    = false;
                }

                if (false===$tmplink)
                {
                    if (IS_DEBUG)throw $e;

                    if (!($error_msg && 2===$error_code && preg_match('#(Unknown database|Access denied for user)#i', $error_msg)))
                    {
                        $error_msg = 'connect mysqli server error.';
                    }
                    throw new Exception($error_msg, $error_code);
                }

                if (IS_DEBUG)Core::debug()->info('mysqli://'.$username.'@'.$hostname.':'.$port.'/'.$database.'/ connection time:' . (microtime(true) - $time));

                # 连接ID
                $this->_connection_ids[$this->_connection_type]                      = $_connection_id;
                # 设置实例化对象
                Database_Driver_MySQLI::$_connection_instance[$_connection_id]       = $tmplink;
                # 设置当前连接的数据库
                Database_Driver_MySQLI::$_current_databases[$_connection_id]         = $database;
                # 设置计数器
                Database_Driver_MySQLI::$_connection_instance_count[$_connection_id] = 1;

                unset($tmplink);

                break;
            }
            catch (Exception $e)
            {
                if (IS_DEBUG)
                {
                    Core::debug()->error($username.'@'.$hostname.':'.$port.'.Msg:'.strip_tags($e->getMessage(), '') .'.Code:'. $e->getCode(), 'connect mysqli server error');
                    $last_error = new Exception($e->getMessage(), $e->getCode());
                }
                else
                {
                    $last_error = new Exception('connect mysqli server error', $e->getCode());
                }

                if (2===$e->getCode() && preg_match('#(Unknown database|Access denied for user)#i', $e->getMessage(), $m))
                {
                    // 指定的库不存在，直接返回
                    throw new Exception(strtolower($m[1])=='unknown database'?__('The mysql database does not exist'):__('The mysql database account or password error'));
                }
                else
                {
                    if (!in_array($hostname, $error_host))
                    {
                        $error_host[] = $hostname;
                    }
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
        if (Database_Driver_MySQLI::$_connection_instance)
        {
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
                $_connection_id = $this->_get_connection_hash($host, $this->config['port'], $this->config['username']);

                if (isset(Database_Driver_MySQLI::$_connection_instance[$_connection_id]))
                {
                    $this->_connection_ids[$this->_connection_type] = $_connection_id;

                    # 计数器+1
                    Database_Driver_MySQLI::$_connection_instance_count[$_connection_id]++;

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * 检查连接是否可用
     *
     * 防止因长时间不链接而导致连接丢失的问题 MySQL server has gone away
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
            $connection    = Database_Driver_MySQLI::$_connection_instance[$connection_id];

            if ($connection)
            {
                $ping_status = mysqli_ping($connection);
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
                throw new Exception('connect mysqli server error');
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
            if ($connection_id && Database_Driver_MySQLI::$_connection_instance[$connection_id])
            {
                if (isset(Database_Driver_MySQLI::$_connection_instance_count[$connection_id]) && Database_Driver_MySQLI::$_connection_instance_count[$connection_id]>1)
                {
                    Database_Driver_MySQLI::$_connection_instance_count[$connection_id]--;
                }
                else
                {
                    $link = Database_Driver_MySQLI::$_connection_instance[$connection_id];

                    unset(Database_Driver_MySQLI::$_connection_instance[$connection_id]);
                    unset(Database_Driver_MySQLI::$_connection_instance_count[$connection_id]);
                    unset(Database_Driver_MySQLI::$_current_databases[$connection_id]);
                    unset(Database_Driver_MySQLI::$_current_charset[$connection_id]);
                    unset(Database_Driver_MySQLI::$_current_connection_id_to_hostname[$connection_id]);

                    try
                    {
                        mysqli_close($link);
                    }
                    catch(Exception $e)
                    {}
                    unset($link);

                    if(IS_DEBUG)Core::debug()->info('close '.$key.' mysqli '.Database_Driver_MySQLI::$_current_connection_id_to_hostname[$connection_id].' connection.');
                }
            }

            $this->_connection_ids[$key] = null;
        }
    }

    /**
     * 切换表
     *
     * @param string Database
     * @return void
     */
    public function select_db($database)
    {
        if (!$database)return;

        $connection_id = $this->connection_id();

        if (!$connection_id || !isset(Database_Driver_MySQLI::$_current_databases[$connection_id]) || $database!=Database_Driver_MySQLI::$_current_databases[$connection_id])
        {
            $connection = Database_Driver_MySQLI::$_connection_instance[$connection_id];

            if (!$connection)
            {
                $this->connect();
                $this->select_db($database);
                return;
            }

            if (!mysqli_select_db($connection, $database))
            {
                throw new Exception('选择数据表错误:' . mysqli_error($connection), mysqli_errno($connection));
            }

            if (IS_DEBUG)
            {
                $host = $this->_get_hostname_by_connection_hash($this->connection_id());
                Core::debug()->info(($host['username']?$host['username'].'@':'') . $host['hostname'] . ($host['port'] && $host['port']!='3306'?':'.$host['port']:'').'select to db:'.$database);
            }

            # 记录当前已选中的数据库
            Database_Driver_MySQLI::$_current_databases[$connection_id] = $database;
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
        $connection = Database_Driver_MySQLI::$_connection_instance[$connection_id];

        if (!$connection_id || !$connection)
        {
            $this->connect();
            return $this->set_charset($charset);
        }

        if (isset(Database_Driver_MySQLI::$_current_charset[$connection_id]) && $charset == Database_Driver_MySQLI::$_current_charset[$connection_id])
        {
            return true;
        }

        $status = mysqli_set_charset($connection, $charset);
        if (false===$status)
        {
            throw new Exception('Error:' . mysqli_error($connection), mysqli_errno($connection));
        }

        # 记录当前设置的编码
        Database_Driver_MySQLI::$_current_charset[$connection_id] = $charset;

        return true;
    }

    public function escape($value)
    {
        $connection = $this->connection();

        $this->_change_charset($value);

        if (($value = mysqli_real_escape_string($connection, $value)) === false)
        {
            throw new Exception('Error:' . mysqli_errno($connection), mysqli_error($connection));
        }

        return "'$value'";
    }

    /**
     * 查询
     *
     * $use_connection_type 默认不传为自动判断，可传true/false,若传字符串(只支持a-z0-9的字符串)，则可以切换到另外一个连接，比如传other,则可以连接到$this->_connection_other_id所对应的ID的连接
     *
     * @param string $sql 查询语句
     * @param string $as_object 是否返回对象
     * @param boolean $connection_type 是否使用主数据库，不设置则自动判断
     * @return Database_Driver_MySQLI_Result
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
            Core::debug()->info($sql, 'MySQL');

            static $is_sql_debug = null;

            if (null === $is_sql_debug) $is_sql_debug = (bool)Core::debug()->profiler('sql')->is_open();

            if ($is_sql_debug)
            {
                $host = $this->_get_hostname_by_connection_hash($this->connection_id());
                $benchmark = Core::debug()->profiler('sql')->start('Database', 'mysqli://' . ($host['username']?$host['username'].'@':'') . $host['hostname'] . ($host['port'] && $host['port'] != '3306' ? ':' . $host['port'] : ''));
            }
        }

        static $is_no_cache = null;
        if (null === $is_no_cache) $is_no_cache = (bool)Core::debug()->profiler('nocached')->is_open();
        //显示无缓存数据
        if ($is_no_cache && strtoupper(substr($sql, 0, 6)) == 'SELECT')
        {
            $sql = 'SELECT SQL_NO_CACHE' . substr($sql, 6);
        }

        // Execute the query
        if (($result = mysqli_query($connection, $sql)) === false)
        {
            if (isset($benchmark))
            {
                // This benchmark is worthless
                $benchmark->delete();
            }

            if (IS_DEBUG)
            {
                $err = 'Error:' . mysqli_error($connection) . '. SQL:' . $sql;
            }
            else
            {
                $err = mysqli_error($connection);
            }
            throw new Exception($err, mysqli_errno($connection));
        }

        if (isset($benchmark))
        {
            # 在线查看SQL情况
            if ($is_sql_debug)
            {
                $data = array();
                $data[0]['db']            = $host['hostname'] . '/' . $this->config['connection']['database'] . '/';
                $data[0]['select_type']   = '';
                $data[0]['table']         = '';
                $data[0]['key']           = '';
                $data[0]['key_len']       = '';
                $data[0]['Extra']         = '';
                $data[0]['query']         = '';
                $data[0]['type']          = '';
                $data[0]['id']            = '';
                $data[0]['row']           = count($result);
                $data[0]['ref']           = '';
                $data[0]['all rows']      = '';
                $data[0]['possible_keys'] = '';

                if (strtoupper(substr($sql,0,6))=='SELECT')
                {
                    $re = $connection->query('EXPLAIN ' . $sql);
                    $i = 0;
                    while (true == ($row = $re->fetch_array(MYSQLI_NUM)))
                    {
                        $data[$i]['select_type']      = (string)$row[1];
                        $data[$i]['table']            = (string)$row[2];
                        $data[$i]['key']              = (string)$row[5];
                        $data[$i]['key_len']          = (string)$row[6];
                        $data[$i]['Extra']            = (string)$row[9];
                        if ($i==0) $data[$i]['query'] = '';
                        $data[$i]['type']             = (string)$row[3];
                        $data[$i]['id']               = (string)$row[0];
                        $data[$i]['ref']              = (string)$row[7];
                        $data[$i]['all rows']         = (string)$row[8];
                        $data[$i]['possible_keys']    = (string)$row[4];
                        $i++;
                    }
                }

                $data[0]['query'] = $sql;
            }
            else
            {
                $data = null;
            }
            if(IS_DEBUG)Core::debug()->profiler('sql')->stop($data);
        }

        // Set the last query
        $this->last_query = $sql;

        if ($type === 'INSERT' || $type === 'REPLACE')
        {
            // Return a list of insert id and rows created
            return array
            (
                mysqli_insert_id($connection),
                mysqli_affected_rows($connection)
            );
        }
        elseif ($type === 'UPDATE' || $type === 'DELETE')
        {
            // Return the number of rows affected
            return mysqli_affected_rows($connection);
        }
        else
        {
            // Return an iterator of results
            return new Database_Driver_MySQLI_Result($result, $sql, $as_object, $this->config);
        }
    }
}