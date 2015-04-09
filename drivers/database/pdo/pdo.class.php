<?php

/**
 * 数据库PDO驱动
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Driver
 * @package    Database
 * @subpackage PDO
 * @copyright  Copyright (c) 2008-2016 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Driver_Database_Driver_PDO extends Database_Driver
{
    /**
     * 记录PDO驱动类型
     *
     * @var string
     */
    protected $_pdo_Driver_type;

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

    protected static $_pdo_types = array
    (
        'mysql',
        'cubrid',
        'sybase',       //MS SQL Server (PDO)
        'firebird',
        'ibm',
        'informix',
        'oci',
        'odbc',
        'pgsql',
        'sqlite',
        'sqlite2',
        'sqlsrv',
        '4d',
    );

    function __construct(array $config)
    {
        static $c = null;
        if (null===$c)
        {
            $c = version_compare(PHP_VERSION, '5.4', '<');
        }
        if ($c)
        {
            # php5.4以下有注入漏洞，所以本系统不允许使用PDO
            throw new Exception(__('If use pdo Driver, need php 5.4+, because less php 5.4 has the SQL injection vulnerability.'));
        }

        if (is_array($config['connection']['hostname']))
        {
            $pdo = current($config['connection']['hostname']);
            if (is_array($pdo))
            {
                $pdo = current($pdo);
            }
        }
        else
        {
            $pdo = $config['connection']['hostname'];
        }

        list($type, $host) = explode(':', $pdo, 2);

        if (isset($config['pdo_type']) && $config['pdo_type'])
        {
            $type = strtolower($config['pdo_type']);
        }
        else
        {
            if (!$type)
            {
                throw new Exception(__('pdo config connection hostname error. need pdo driver type'));
            }

            if ($type=='uri')
            {
                // uri:file:///path/to/dsnfile
                $config['connection']['hostname'] = $pdo = file_get_contents($host);
                list($type, $host) = explode(':', $pdo, 2);

                if (!$type)
                {
                    throw new Exception(__('pdo :host error. need pdo driver type'), array(':host', $host));
                }
            }

            $type = strtolower($type);
        }

        if (!in_array($type, Database_Driver_PDO::$_pdo_types))
        {
            throw new Exception(__('Unknown driver type: :type'), array(':type'=>$type));
        }
        $this->_pdo_Driver_type = $type;

        # driver option
        if (!isset($config['options']) || !is_array($config['options']))
        {
            $config['options'] = array();
        }

        if (isset($config['persistent']) && $config['persistent'])
        {
            $config['options'][PDO::ATTR_PERSISTENT] = true;
        }

        parse_str(str_replace(';', '&', $host), $ot);

        $default_port = null;

        switch ($type)
        {
            case 'ibm':
            case 'odbc':
                $db_name = 'DATABASE';
                break;
            case 'informix':
                $db_name = 'database';
                break;
            case 'sqlsrv':
                $db_name = 'Database';
                $default_port = 1433;
                break;
            case 'mysql':
                $db_name = 'dbname';
                $this->_identifier = '`';
                if ($config['charset'])
                {
                    $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES '{$config['charset']}';";
                }

                $default_port = 3306;
                break;
            default:
                $db_name = 'dbname';
                break;
        }

        if (!isset($ot[$db_name]))
        {
            if ($config['connection']['database'])
            {
                $ot[$db_name] = $config['connection']['database'];

                # 重新还原
                $config['connection']['hostname'] = $type .':'. http_build_query($ot, '', ';');
            }
        }
        else
        {
            $config['connection']['database'] = $ot[$db_name];
        }

        # 默认端口
        if ($default_port)
        {
            if (!isset($config['connection']['port']) || !$config['connection']['port'])
            {
                $config['connection']['port'] = isset($ot['port']) && $ot['port'] ? (int)$ot['port'] : $default_port;
            }
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

        if (!$connection_id || !isset(Database_Driver_PDO::$_connection_instance[$connection_id]))
        {
            $this->_connect();
        }

        # 设置编码
        $this->set_charset($this->config['charset']);

        # 切换表
        $this->select_db($this->config['connection']['database']);
    }

    /**
     * 获取当前连接
     *
     * @return pdo
     */
    public function connection()
    {
        # 尝试连接数据库
        $this->connect();

        # 获取连接ID
        $connection_id = $this->connection_id();

        if ($connection_id && isset(Database_Driver_PDO::$_connection_instance[$connection_id]))
        {
            return Database_Driver_PDO::$_connection_instance[$connection_id];
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

        if (!$this->config['connection']['hostname'])
        {
            throw new Exception('pdo配置缺少dsn或hostname参数');
        }

        $database = $port = $socket = $username = $password = $persistent = $options = null;
        extract($this->config['connection']);

        if (!$options)
        {
            $options = array();
        }
        # 报错类型
        $options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;

        # 错误服务器
        static $error_host = array();

        $last_error = null;
        while (true)
        {
            $dsn = $this->_get_rand_host($error_host);
            if (false===$dsn)
            {
                if(IS_DEBUG)Core::debug()->warn($error_host, 'error_host');

                if ($last_error && $last_error instanceof Exception)throw $last_error;
                throw new Exception('connect pdo server error.');
            }

            $_connection_id = $this->_get_connection_hash($dsn, $port, $username);
            Database_Driver_PDO::$_current_connection_id_to_hostname[$_connection_id] = $dsn;

            try
            {
                $time = microtime(true);

                $error_code = 0;
                $error_msg  = '';

                try
                {
                    $tmp_link = new pdo($dsn, $username, $password, $options);
                }
                catch (Exception $e)
                {
                    $error_msg  = $e->getMessage();
                    $error_code = $e->getCode();
                    $tmp_link   = false;
                }

                if (false===$tmp_link)
                {
                    if (IS_DEBUG && isset($e) && $e instanceof Exception)throw $e;

                    if (!($error_msg && 2===$error_code && preg_match('#(Unknown database|Access denied for user)#i', $error_msg)))
                    {
                        $error_msg = 'connect pdo server error.';
                    }
                    throw new Exception($error_msg, $error_code);
                }

                if (IS_DEBUG)Core::debug()->info('pdo '.$dsn.' connection time:' . (microtime(true) - $time));

                # 连接ID
                $this->_connection_ids[$this->_connection_type]                   = $_connection_id;
                # 设置实例化对象
                Database_Driver_PDO::$_connection_instance[$_connection_id]       = $tmp_link;
                # 设置当前连接的数据库
                Database_Driver_PDO::$_current_databases[$_connection_id]         = $database;
                # 设置计数器
                Database_Driver_PDO::$_connection_instance_count[$_connection_id] = 1;

                unset($tmp_link);

                break;
            }
            catch (Exception $e)
            {
                if (IS_DEBUG)
                {
                    Core::debug()->error('pdo '.$dsn.'.Msg:'.strip_tags($e->getMessage(), '') .'.Code:'. $e->getCode(), 'connect pdo error');
                    $last_error = new Exception($e->getMessage(), $e->getCode());
                }
                else
                {
                    $last_error = new Exception('connect pdo error', $e->getCode());
                }

                if (2===$e->getCode() && preg_match('#(Unknown database|Access denied for user)#i', $e->getMessage(), $m))
                {
                    // 指定的库不存在，直接返回
                    throw new Exception(strtolower($m[1])=='unknown database'?__('The mysql database does not exist'):__('The mysql database account or password error'));
                }
                else
                {
                    if (!in_array($dsn, $error_host))
                    {
                        $error_host[] = $dsn;
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
        if (Database_Driver_PDO::$_connection_instance)
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
                $_connection_id = $this->_get_connection_hash($host, $this->config['connection']['port'], $this->config['connection']['username']);

                if (isset(Database_Driver_PDO::$_connection_instance[$_connection_id]))
                {
                    $this->_connection_ids[$this->_connection_type] = $_connection_id;

                    # 计数器+1
                    Database_Driver_PDO::$_connection_instance_count[$_connection_id]++;

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * 关闭链接
     */
    public function close_connect()
    {
        if ($this->_connection_ids)foreach ($this->_connection_ids as $key=>$connection_id)
        {
            if ($connection_id && Database_Driver_PDO::$_connection_instance[$connection_id])
            {
                if (isset(Database_Driver_PDO::$_connection_instance_count[$connection_id]) && Database_Driver_PDO::$_connection_instance_count[$connection_id]>1)
                {
                    Database_Driver_PDO::$_connection_instance_count[$connection_id]--;
                }
                else
                {
                    $id = Database_Driver_PDO::$_current_connection_id_to_hostname[$connection_id];
                    Database_Driver_PDO::$_connection_instance[$connection_id] = null;

                    unset(Database_Driver_PDO::$_connection_instance[$connection_id]);
                    unset(Database_Driver_PDO::$_connection_instance_count[$connection_id]);
                    unset(Database_Driver_PDO::$_current_databases[$connection_id]);
                    unset(Database_Driver_PDO::$_current_charset[$connection_id]);
                    unset(Database_Driver_PDO::$_current_connection_id_to_hostname[$connection_id]);

                    if(IS_DEBUG)Core::debug()->info('close '. $key .' pdo '. $id .' connection.');
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

        if (!$connection_id || !isset(Database_Driver_PDO::$_current_databases[$connection_id]) || $database!=Database_Driver_PDO::$_current_databases[$connection_id])
        {
            /**
             * @var $connection pdo
             */
            $connection = Database_Driver_PDO::$_connection_instance[$connection_id];

            if (!$connection)
            {
                $this->connect();
                $this->select_db($database);
                return;
            }

            try
            {
                # 注意，手动切换数据库只能使得一次查询生效，再次查询还是原来的库
                $connection->exec("USE `$database`;");
            }
            catch (Exception $e)
            {
                if (IS_DEBUG)throw $e;

                throw new Exception('pdo选择数据库'.$database.'失败');
            }

            if (IS_DEBUG)
            {
                $host = $this->_get_hostname_by_connection_hash($this->connection_id());
                Core::debug()->info('pdo '. ($host['username']?$host['username'].'@':'') . $host['host'] .' select to db:'. $database);
            }
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
        /**
         * @var $connection pdo
         */
        $connection = Database_Driver_PDO::$_connection_instance[$connection_id];

        if (!$connection_id || !$connection)
        {
            $this->connect();
            return $this->set_charset($charset);
        }

        if (isset(Database_Driver_PDO::$_current_charset[$connection_id]) && $charset == Database_Driver_PDO::$_current_charset[$connection_id])
        {
            return true;
        }

        try
        {
            $connection->exec('SET NAMES '. $charset);
            $status = true;
        }
        catch (Exception $e)
        {
            $status = false;
        }

        if (!$status && isset($e))
        {
            throw new Exception('Error:'. $e->getMessage(), $e->getCode());
        }

        # 记录当前设置的编码
        Database_Driver_PDO::$_current_charset[$connection_id] = $charset;

        return true;
    }

    /**
     * 执行构造语法执行
     *
     * @param $builder
     * @return Database_Driver_PDO_Result
     */
    public function execute($statement, array $input_parameters = array(), $as_object = null, $connection_type = null)
    {
        $statement = trim($statement);
        $type      = $this->_get_query_type($statement, $connection_type);

        # 设置连接类型
        $this->_set_connection_type($connection_type);

        # 连接数据库
        $connection = $this->connection();

        # 记录调试
        if(IS_DEBUG)
        {
            Core::debug()->info($statement, 'PDO Statement');

            static $is_sql_debug = null;
            if (null === $is_sql_debug)
            {
                $is_sql_debug = (bool)Core::debug()->profiler('sql')->is_open();
            }
            if ($is_sql_debug)
            {
                $host      = $this->_get_hostname_by_connection_hash($this->connection_id());
                $benchmark = Core::debug()->profiler('sql')->start('Database', 'pbo ' . ($host['username']?$host['username'].'@':'') . $host['hostname']);
            }
        }

        try
        {
            $sth = $connection->prepare($statement);
            if (false === $sth)
            {
                throw new Exception('pdo prepare sql error.');
            }

            if (false===$sth->execute($input_parameters))
            {
                throw new Exception('pdo execute error.');
            }
        }
        catch (Exception $e)
        {
            $sth = false;
        }

        # 关闭调试记录
        if (IS_DEBUG && isset($benchmark))
        {
            Core::debug()->profiler('sql')->stop();
        }

        if (false === $sth)
        {
            if (isset($e))
            {
                if (IS_DEBUG)
                {
                    throw $e;
                }
                else
                {
                    throw new Exception($e->getMessage(), $e->getCode());
                }
            }
            else
            {
                throw new Exception('Error: pdo prepare sql error.');
            }
        }

        $this->last_query = $sth->queryString;


        if ($type === 'INSERT' || $type === 'REPLACE')
        {
            return array
            (
                $connection->lastInsertId(),
                $sth->rowCount(),
            );
        }
        elseif ($type === 'UPDATE' || $type === 'DELETE')
        {
            return $sth->rowCount();
        }
        else
        {
            return new Database_Driver_PDO_Result($sth, $this->last_query, $as_object, $this->config);
        }
    }

    /**
     * 查询
     *
     * $use_connection_type 默认不传为自动判断，可传true/false,若传字符串(只支持a-z0-9的字符串)，则可以切换到另外一个连接，比如传other,则可以连接到$this->_connection_other_id所对应的ID的连接
     *
     * @param string $sql 查询语句
     * @param string $as_object 是否返回对象
     * @param boolean $use_connection_type 是否使用主数据库，不设置则自动判断
     * @return Database_Driver_PDO_Result
     */
    public function query($sql, $as_object = null, $connection_type = null)
    {
        return $this->execute($sql, array(), $as_object, $connection_type);
    }

    public function escape($value, $parameter_type = PDO::PARAM_STR)
    {
        $connection = $this->connection();

        $this->_change_charset($value);

        if (false===($value = $connection->quote($value, $parameter_type)))
        {
            throw new Exception('Error: pdo quote error, string: '. $value);
        }

        return $value;
    }
}