<?php
/**
 * 数据库核心类
 *
 * @author     jonwang(jonwang@myqee.com)
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2012 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class MyQEE_Database extends Database_QueryBuilder
{
    /**
     * MySQL驱动类型
     *
     * @var string
     */
    const TYPE_MySQL = 'MySQL';

    /**
     * MySQLI驱动类型
     *
     * @var string
     */
    const TYPE_MySQLI = 'MySQLI';

    /**
     * Mongo驱动类型
     *
     * @var string
     */
    const TYPE_Mongo  = 'Mongo';

    /**
     * SQLite驱动类型
     *
     * @var string
     */
    const TYPE_SQLite  = 'SQLite';

    /**
     * @var array Database instances
     */
    protected static $instances = array();

    /**
     * 当前配置
     *
     * @var array
     */
    protected $config;

    /**
     * 当前驱动
     *
     * @var Database_Driver_MySQLI
     */
    protected $driver;

    /**
     * 数据库连接对象
     *
     * @var mysqli
     */
    protected $_connection;

    /**
     * 是否自动使用主数据库
     *
     * @var boolean
     */
    protected $is_auto_use_master = false;

    /**
     * 记录慢查询
     *
     *   array
     *   (
     *       //    执行时的时间    耗时(单位毫秒)   查询语句
     *       array(1351691389,   1200          ,'select * from test;'),
     *   )
     *
     * @var array
     */
    protected static $slow_querys = array();

    /**
     * 返回数据库实例化对象
     *
     * @param string $config_name
     * @return Database
     */
    public static function instance($config_name = 'default')
    {
        if ( is_string($config_name) )
        {
            $i_name = $config_name;
        }
        else
        {
            $i_name = '.config_'.md5(serialize($config_name));
        }

        if ( !isset(Database::$instances[$i_name]) )
        {
            Database::$instances[$i_name] = new Database($config_name);
        }
        return Database::$instances[$i_name];
    }

    /**
     * Sets the initial columns to select from.
     *
     * @param   array  column list
     * @return  void
     */
    public function __construct($config_name = 'default')
    {
        if ( is_array($config_name) )
        {
            $this->config = $config_name;
        }
        else
        {
            $this->config = Core::config('database.' . $config_name);
        }
        $this->config['charset'] = strtoupper($this->config['charset']);
        if ( !isset($this->config['auto_change_charset']) )
        {
            $this->config['auto_change_charset'] = false;
        }
        if ( $this->config['auto_change_charset'] )
        {
            if ( isset($this->config['data_charset']) )
            {
                $this->config['data_charset'] = strtoupper($this->config['data_charset']);
            }
            else
            {
                $this->config['data_charset'] = $this->config['charset'];
            }
        }

        $driver = $this->config['type'];
        if ( ! $driver )
        {
            $driver = 'MySQL';
        }
        $driver = 'Database_Driver_' . $driver;
        if ( ! class_exists($driver, true) )
        {
            throw new Exception('Database Driver:' . $driver . ' not found.');
        }

        if (!isset($this->config['connection']))
        {
            throw new Exception('Database connection not set.');
        }

        if ( is_string($this->config['connection']) )
        {
            $this->config['connection'] = Database::parse_dsn($this->config['connection']);
        }

        # 当前驱动
        $this->driver = new $driver($this->config);

        parent::__construct();

        # 增加自动关闭连接列队
        Core::add_close_connect_class('Database');
    }

    public function __destruct()
    {
        $this->close_connect();
    }

    /**
     * 获取驱动引擎对象
     *
     * @return Database_Driver_MySQLI
     */
    public function driver()
    {
        return $this->driver;
    }

    /**
     * 关闭连接
     */
    public function close_connect()
    {
        $this->driver->close_connect();
    }

    /**
     * 执行SQL查询
     *
     * @param string $sql
     * @param boolean $as_object 返回对象名称 默认false，即返回数组
     * @param boolean $use_master 是否使用主数据库，不设置则自动判断,对更新的SQL无效
     * @return Database_Driver_MySQLI_Result
     */
    public function query($sql, $as_object = false, $use_master = null)
    {
        if ( null === $use_master && true === $this->is_auto_use_master )
        {
            $use_master = true;
        }

        static $slow_query_mtime = null;
        if ( null===$slow_query_mtime )
        {
            if (IS_CLI)
            {
                $slow_query_mtime = false;
            }
            else
            {
                $slow_query_mtime = (int)Core::config('core.slow_query_mtime');
            }
        }

        if ($slow_query_mtime)$stime = microtime(1);

        $rs = $this->driver->query($sql, $as_object, $use_master);

        if ( $slow_query_mtime>0 )
        {
            $etime = microtime(1);
            $time = 1000*($etime-$stime);
            if ( $time>$slow_query_mtime )
            {
                // 记录慢查询
                Database::$slow_querys[] = array
                (
                    (int)$stime,
                    $time,
                    $this->driver->last_query(),            // 不用$sql是因为比如MongoDB这样的会在driver里再处理
                );
            }
        }

        return $rs;
    }

    /**
     * 返回当前表前缀
     *
     * @return  string
     */
    public function table_prefix()
    {
        return $this->config['table_prefix'];
    }

    /**
     * 解析为SQL语句
     *
     * @see QueryBuilder::compile()
     * @param string $type select,insert,update,delect,replace
     * @param boolean $use_master 当$type=select此参数有效，设置true则使用主数据库，设置false则使用从数据库，不设置则使用默认
     * @return  string
     */
    public function compile($type = 'select', $use_master=null)
    {
        if ( $type=='select' && null === $use_master && true === $this->is_auto_use_master )
        {
            $use_master = true;
        }
        # 先连接数据库，因为在compile时需要用到mysql_real_escape_string,mysqli_real_escape_string方法
        $this->driver->connect($use_master);

        # 获取查询SQL
        $sql = $this->driver->compile($this->_builder, $type);

        # 重置QueryBulider
        $this->reset();

        return $sql;
    }

    /**
     * 获取数据
     *
     * @param boolean $as_object 返回对象名称 默认false，即返回数组
     * @param boolean $use_master 是否使用主数据库，不设置则自动判断
     * @return Database_Driver_MySQLI_Result
     */
    public function get($as_object = false, $use_master = null)
    {
        return $this->query($this->compile('select',$use_master), $as_object, $use_master);
    }

    /**
     * 最后查询的SQL语句
     *
     * @return string
     */
    public function last_query()
    {
        return $this->driver->last_query();
    }

    /**
     * 更新数据
     *
     * @param string $table
     * @param array $value
     * @param array $where
     * @return int 作用的行数
     */
    public function update($table = null, $value = null, $where = null)
    {
        if ( $table )
        {
            $this->table($table);
        }
        if ( $value )
        {
            $this->set($value);
        }
        if ( $where )
        {
            $this->where($where);
        }
        $sql = $this->compile('update');

        return $this->query($sql, false, true);
    }

    /**
     * 插入数据
     *
     * @param string $table
     * @param array $value
     * @param Database_Result
     * @return array(插入ID,作用行数)
     */
    public function insert($table = null, $value = null)
    {
        if ( $table )
        {
            $this->table($table);
        }
        if ( $value )
        {
            $this->columns(array_keys($value));
            $this->values($value);
        }
        $sql = $this->compile('insert');

        return $this->query($sql , false , true);
    }

    /**
     * 删除数据
     *
     * @param string $table 表名称
     * @param array $where 条件
     * @return integer 操作行数
     */
    public function delete($table = null, $where = null)
    {
        if ( $table )
        {
            $this->table($table);
        }
        if ( $where )
        {
            $this->where($where);
        }
        $sql = $this->compile('delete');

        return $this->query($sql , false , true);
    }

    /**
     * 统计指定条件的数量
     *
     * @param   mixed	table name string or array(query, alias)
     * @return  integer
     */
    public function count_records($table = null, $where = null)
    {
        if ( $table )
        {
            $this->from($table);
        }
        if ( $where )
        {
            $this->where($where);
        }
        $this->select($this->expr_value('COUNT(1) AS `total_row_count`'));

        return (int)$this->query($this->compile('select'), false)->get('total_row_count');
    }

    /**
     * 替换数据 replace into
     *
     * @param string $table
     * @param array $value
     * @param array $where
     * @param Database_Result
     */
    public function replace($table = null, $value = null, $where = null)
    {
        return $this->merge($table, $value, $where);
    }

    /**
     * 替换数据 replace into
     *
     * @param string $table
     * @param array $value
     * @param array $where
     * @param Database_Result
     */
    public function merge($table = null, $value = null, $where = null)
    {
        if ( $table )
        {
            $this->table($table);
        }
        if ( $value )
        {
            $this->columns(array_keys($value));
            $this->values($value);
        }
        if ( $where )
        {
            $this->where($where);
        }
        $sql = $this->compile('replace');
        return $this->query($sql, false , true);
    }

    /**
     * 获取事务对象
     *
     * @return Database_Transaction 事务对象
     */
    public function transaction()
    {
        return $this->driver->transaction();
    }

    /**
     * 设置是否一直在主数据库上查询
     *
     * 这样设置后，select会一直停留在主数据库上，直到$this->auto_use_master(false)后才会自动判断
     * @param boolean $autoUseMaster
     * @return Database
     */
    public function auto_use_master($autoUseMaster = true)
    {
        $this->is_auto_use_master = (boolean)$autoUseMaster;
        return $this;
    }

    /**
     * 是否一直用主数据库查询
     *
     * @return boolean
     */
    public function is_auto_use_master()
    {
        return $this->is_auto_use_master;
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
    public function create_database( $database, $charset = null, $collate=null )
    {
        if ( method_exists($this->driver, 'create_database') )
        {
            return $this->driver->create_database($database,$charset,$collate);
        }
        else
        {
            return false;
        }
    }

    /**
     * 解析DSN路径格式
     *
     * @param  string  DSN string
     * @return array
     */
    public static function parse_dsn($dsn)
    {

        $db = array
        (
            'type'       => false,
            'username'   => false,
            'password'   => false,
            'hostname'   => false,
            'port'       => false,
            'persistent' => false,
            'database'   => false,
        );

        // Get the protocol and arguments
        list ( $db['type'], $connection ) = explode('://', $dsn, 2);

        if ( $connection[0] === '/' )
        {
            // Strip leading slash
            $db['database'] = substr($connection, 1);
        }
        else
        {
            $connection = parse_url('http://' . $connection);

            if ( isset($connection['user']) )
            {
                $db['username'] = $connection['user'];
            }

            if ( isset($connection['pass']) )
            {
                $db['password'] = $connection['pass'];
            }

            if ( isset($connection['port']) )
            {
                $db['port'] = $connection['port'];
            }

            if ( isset($connection['host']) )
            {
                if ( $connection['host'] === 'unix(' )
                {
                    list ( $db['persistent'], $connection['path'] ) = explode(')', $connection['path'], 2);
                }
                else
                {
                    $db['hostname'] = $connection['host'];
                }
            }

            if ( isset($connection['path']) && $connection['path'] )
            {
                // Strip leading slash
                $db['database'] = substr($connection['path'], 1);
            }
        }

        return $db;
    }

    /**
     * 关闭全部数据库链接
     */
    public static function close_all_connect()
    {
        if (!Database::$instances || !is_array(Database::$instances)) return;

        foreach ( Database::$instances as $database )
        {
            if ( $database instanceof Database )
            {
                $database->close_connect();
            }
        }

        // 执行保存慢查询方法
        Database::save_slow_query();
    }

    /**
     * 记录慢查询
     *
     * @return boolean
     */
    protected static function save_slow_query()
    {
        if (!Database::$slow_querys)return true;

        // 记录URL信息
        $data = "\n".str_pad(HttpIO::METHOD,4,' ') .' '. date('H:i:s',TIME) .' - '. str_pad((int)(1000*(microtime(1)-START_TIME)),6,' ',STR_PAD_LEFT) . ' - '. str_pad(HttpIO::IP,15) .' '.$_SERVER["SCRIPT_URI"] .(''!==$_SERVER["QUERY_STRING"]?'?'.$_SERVER["QUERY_STRING"]:'') . (HttpIO::METHOD=='POST'?'   POST:'.json_encode(HttpIO::POST()):'') ."\n";
        foreach (Database::$slow_querys as $item)
        {
            $data .= '     ' . date('H:i:s',$item[0]).' - '.str_pad((int)$item[1],6,' ',STR_PAD_LEFT) . ' - ' . $item[2] . "\n";
        }

        // 写入文件
        File::create_file(DIR_LOG.'slow_query/'.date('Y/m_d',TIME),$data,FILE_APPEND);
    }
}
