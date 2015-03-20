<?php

/**
 * 数据库SQLite返回类
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Driver
 * @package    Database
 * @subpackage SQLite
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Driver_Database_Driver_SQLite extends Database_Driver
{
    /**
     * MySQL使用反引号标识符
     *
     * @var string
     */
    protected $_identifier = '`';

    /**
     * 记录当前数据库所对应的页面编码
     *
     * @var array
     */
    protected static $_current_charset = array();

    /**
     * 链接寄存器
     *
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
     * 记录connection id所对应的DB
     *
     * @var array
     */
    protected static $_current_connection_id_to_db = array();

    protected $_connection_type = 'master';

    function __construct(array $config)
    {
        if (!isset($config['db']))
        {
            throw new Exception(__("sqlite config error,can't found \$config['db'] value."));
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
        $connection_id = $this->connection_id();

        if (!$connection_id || !isset(Database_Driver_SQLite::$_connection_instance[$connection_id]))
        {
            $this->_connect();
        }

        # 设置编码
        $this->set_charset($this->config['charset']);
    }

    /**
     * 获取当前连接
     *
     * @return sqlite
     */
    public function connection()
    {
        # 尝试连接数据库
        $this->connect();

        # 获取连接ID
        $connection_id = $this->connection_id();

        if ($connection_id && isset(Database_Driver_SQLite::$_connection_instance[$connection_id]))
        {
            return Database_Driver_SQLite::$_connection_instance[$connection_id];
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

        $db = $persistent = null;

        extract($this->config['connection']);

        # 错误服务器
        static $error_host = array();

        $last_error = null;

        for ($i=1; $i<=2; $i++)
        {
            # 尝试重连
            try
            {
                $_connection_id = $this->_get_connection_hash($db);
                Database_Driver_SQLite::$_current_connection_id_to_db[$_connection_id] = Core::debug_path($db);

                $time = microtime(true);
                try
                {
                    if ($persistent)
                    {
                        $tmplink = sqlite_popen($db);
                    }
                    else
                    {
                        $tmplink = sqlite_open($db);
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
                    if (IS_DEBUG)
                    {
                        throw $e;
                    }
                    else
                    {
                        $error_msg = 'open sqlite error.';
                    }

                    throw new Exception($error_msg, $error_code);
                }

                if (IS_DEBUG)Core::debug()->info('sqlite '.Core::debug_path($db).' connection time:' . (microtime(true) - $time));

                # 连接ID
                $this->_connection_ids[$this->_connection_type] = $_connection_id;
                # 设置实例化对象
                Database_Driver_SQLite::$_connection_instance[$_connection_id]       = $tmplink;
                # 设置计数器
                Database_Driver_SQLite::$_connection_instance_count[$_connection_id] = 1;

                unset($tmplink);

                break;
            }
            catch (Exception $e)
            {
                if (IS_DEBUG)
                {
                    Core::debug()->error($db,'open sqlite:'.$db.' error.');
                    $last_error = new Exception($e->getMessage(), $e->getCode());
                }
                else
                {
                    $last_error = new Exception('open sqlite error.', $e->getCode());
                }

                if ($i==2)
                {
                    throw $last_error;
                }

                # 3毫秒后重新连接
                usleep(3000);
            }
        }
    }

    protected function _try_use_exists_connection()
    {
        # 检查下是否已经有连接连上去了
        if (Database_Driver_SQLite::$_connection_instance)
        {
            $_connection_id = $this->_get_connection_hash($this->config['connection']['db']);

            if (isset(Database_Driver_SQLite::$_connection_instance[$_connection_id]))
            {
                $this->_connection_ids[$this->_connection_type] = $_connection_id;

                # 计数器+1
                Database_Driver_SQLite::$_connection_instance_count[$_connection_id]++;
                return true;
            }
        }

        return false;
    }

    /**
     * 获取链接唯一hash
     *
     * @param string $file
     * @return string
     */
    protected function _get_connection_hash($file)
    {
        $hash = sha1(get_class($this).$file);
        Database_Driver::$_hash_to_hostname[$hash] = Core::debug_path($file);

        return $hash;
    }

    /**
     * 关闭链接
     */
    public function close_connect()
    {
        if ($this->_connection_ids)foreach ($this->_connection_ids as $key=>$connection_id)
        {
            if ($connection_id && Database_Driver_SQLite::$_connection_instance[$connection_id])
            {
                if (isset(Database_Driver_SQLite::$_connection_instance_count[$connection_id]) && Database_Driver_SQLite::$_connection_instance_count[$connection_id]>1)
                {
                    Database_Driver_SQLite::$_connection_instance_count[$connection_id]--;
                }
                else
                {
                    $link = Database_Driver_SQLite::$_connection_instance[$connection_id];
                    $id   = Database_Driver_SQLite::$_current_connection_id_to_db[$connection_id];

                    unset(Database_Driver_SQLite::$_connection_instance[$connection_id]);
                    unset(Database_Driver_SQLite::$_current_charset[$connection_id]);
                    unset(Database_Driver_SQLite::$_current_connection_id_to_db[$connection_id]);

                    try
                    {
                        sqlite_close($link);
                    }
                    catch(Exception $e)
                    {}
                    unset($link);

                    if(IS_DEBUG)Core::debug()->info('close '. $key .' sqlite '. $id .' connection.');
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
        if (!$charset)return;

        $connection_id = $this->connection_id();
        $connection = Database_Driver_SQLite::$_connection_instance[$connection_id];

        if (!$connection_id || !$connection)
        {
            $this->connect();
            return $this->set_charset($charset);
        }

        if (isset(Database_Driver_SQLite::$_current_charset[$connection_id]) && $charset==Database_Driver_SQLite::$_current_charset[$connection_id])
        {
            return true;
        }

        $status = (bool)sqlite_query('SET NAMES ' . $this->quote($charset), $connection);
        if ($status === false)
        {
            throw new Exception('Error:' . sqlite_error_string($connection), sqlite_last_error($connection));
        }

        # 记录当前设置的编码
        Database_Driver_SQLite::$_current_charset[$connection_id] = $charset;
    }

    public function escape($value)
    {
        $value = sqlite_escape_string($value);
        return "'$value'";
    }

    /**
     * 查询
     *
     * $use_connection_type 默认不传为自动判断，可传true/false,若传字符串(只支持a-z0-9的字符串)，则可以切换到另外一个连接，比如传other,则可以连接到$this->_connection_other_id所对应的ID的连接
     *
     * @param string $sql 查询语句
     * @param string $as_object 是否返回对象
     * @return Database_Driver_SQLite_Result
     */
    public function query($sql, $as_object = null, $use_master = null)
    {
        $sql = trim($sql);

        if (preg_match('#^([a-z]+)(:? |\n|\r)#i',$sql,$m))
        {
            $type = strtoupper($m[1]);
        }

        # 连接数据库
        $connection = $this->connection();

        # 记录调试
        if(IS_DEBUG)
        {
            Core::debug()->info($sql, 'SQLite');

            static $is_sql_debug = null;

            if (null === $is_sql_debug) $is_sql_debug = (bool)Core::debug()->profiler('sql')->is_open();

            if ($is_sql_debug)
            {
                $db = $this->_get_hostname_by_connection_hash($this->connection_id());
                $benchmark = Core::debug()->profiler('sql')->start('Database', 'sqlite://'.$db);
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
        if (($result = sqlite_query($sql, $connection)) === false)
        {
            if (isset($benchmark))
            {
                // This benchmark is worthless
                $benchmark->delete();
            }

            if (IS_DEBUG)
            {
                $err = 'Error:' . sqlite_error_string($connection) . '. SQL:' . $sql;
            }
            else
            {
                $err = sqlite_error_string($connection);
            }
            throw new Exception($err, sqlite_last_error($connection));
        }

        if (isset($benchmark))
        {
            # 在线查看SQL情况
            if ($is_sql_debug)
            {
                $data = array();
                $data[0]['db']            = $db;
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
                    $re = sqlite_query('EXPLAIN ' . $sql, $connection);
                    $i = 0;
                    while (true == ($row = sqlite_fetch_array($re , SQLITE_NUM)))
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
            Core::debug()->profiler('sql')->stop($data);
        }

        // Set the last query
        $this->last_query = $sql;

        if ($type === 'INSERT' || $type === 'REPLACE')
        {
            // Return a list of insert id and rows created
            return array
            (
                sqlite_last_insert_rowid($connection),
                sqlite_changes($connection)
            );
        }
        elseif ($type === 'UPDATE' || $type === 'DELETE')
        {
            // Return the number of rows affected
            return sqlite_changes($connection);
        }
        else
        {
            // Return an iterator of results
            return new Database_Driver_SQLite_Result($result, $sql, $as_object ,$this->config);
        }
    }
}