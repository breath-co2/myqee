<?php

/**
 * 数据库Mongo驱动
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Driver
 * @package    Database
 * @subpackage Mongo
 * @copyright  Copyright (c) 2008-2015 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Driver_Database_Driver_Mongo extends Database_Driver
{
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
     * DB链接寄存器
     *
     * @var array
     */
    protected static $_connection_instance_db = array();

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
            $config['port'] = 27017;
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

        if (!$connection_id || !isset(Database_Driver_Mongo::$_connection_instance[$connection_id]))
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
     * @return MongoDB
     */
    public function connection()
    {
        # 尝试连接数据库
        $this->connect();

        # 获取连接ID
        $connection_id = $this->connection_id();

        if ($connection_id && isset(Database_Driver_Mongo::$_connection_instance_db[$connection_id]))
        {
            return Database_Driver_Mongo::$_connection_instance_db[$connection_id];
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

        $database = $hostname = $port = $username = $password = $persistent = $readpreference = null;
        extract($this->config['connection']);

        # 错误服务器
        static $error_host = array();

        $last_error = null;
        while (true)
        {
            $hostname = $this->_get_rand_host($error_host);
            if (false===$hostname)
            {
                Core::debug()->error($error_host, 'error_host');

                if ($last_error && $last_error instanceof Exception)throw $last_error;
                throw new Exception(__('connect mongodb server error.'));
            }

            $_connection_id = $this->_get_connection_hash($hostname, $port, $username);
            Database_Driver_Mongo::$_current_connection_id_to_hostname[$_connection_id] = $hostname.':'.$port;

            try
            {
                $time = microtime(true);

                $options = array
                (
                    'slaveOkay' => true,        //在从数据库可以查询，避免出现 Cannot run command count(): not master 的错误
                );

                // 长连接设计
                if ($persistent)
                {
                    $options['persist'] = is_string($persistent)?$persistent:'x';
                }

                static $check = null;

                if (null===$check)
                {
                    $check = true;

                    if (!class_exists('MongoClient', false))
                    {
                        if (class_exists('Mongo', false))
                        {
                            throw new Exception(__('your mongoclient version is too low.'));
                        }
                        else
                        {
                            throw new Exception(__('You do not have to install mongodb extension,see http://php.net/manual/zh/mongo.installation.php'));
                        }
                    }
                }

                $error_code = 0;
                try
                {
                    if ($username)
                    {
                        $tmplink = new MongoClient("mongodb://{$username}:{$password}@{$hostname}:{$port}/", $options);
                    }
                    else
                    {
                        $tmplink = new MongoClient("mongodb://{$hostname}:{$port}/", $options);
                    }
                }
                catch (Exception $e)
                {
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
                        $error_msg = 'connect mongodb server error.';
                    }
                    throw new Exception($error_msg, $error_code);
                }

                if (null!==$readpreference)
                {
                    $tmplink->setReadPreference($readpreference);
                }

                Core::debug()->info('MongoDB '.($username?$username.'@':'').$hostname.':'.$port.' connection time:' . (microtime(true) - $time));

                # 连接ID
                $this->_connection_ids[$this->_connection_type] = $_connection_id;
                Database_Driver_Mongo::$_connection_instance[$_connection_id] = $tmplink;

                unset($tmplink);

                break;
            }
            catch (Exception $e)
            {
                if (IS_DEBUG)
                {
                    Core::debug()->error(($username?$username.'@':'').$hostname.':'.$port, 'connect mongodb server error');
                    $last_error = new Exception($e->getMessage(), $e->getCode());
                }
                else
                {
                    $last_error = new Exception('connect mongodb server error', $e->getCode());
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
        if (Database_Driver_Mongo::$_connection_instance)
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

                if (isset(Database_Driver_Mongo::$_connection_instance[$_connection_id]))
                {
                    $this->_connection_ids[$this->_connection_type] = $_connection_id;

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
            if ($connection_id && Database_Driver_Mongo::$_connection_instance[$connection_id])
            {
                # 销毁对象
                Database_Driver_Mongo::$_connection_instance[$connection_id]    = null;
                Database_Driver_Mongo::$_connection_instance_db[$connection_id] = null;

                unset(Database_Driver_Mongo::$_connection_instance[$connection_id]);
                unset(Database_Driver_Mongo::$_connection_instance_db[$connection_id]);
                unset(Database_Driver_Mongo::$_current_databases[$connection_id]);
                unset(Database_Driver_Mongo::$_current_charset[$connection_id]);
                unset(Database_Driver_Mongo::$_current_connection_id_to_hostname[$connection_id]);

                if (IS_DEBUG)Core::debug()->info('close '.$key.' mongo '.Database_Driver_Mongo::$_current_connection_id_to_hostname[$connection_id].' connection.');
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

        if (!$connection_id || !isset(Database_Driver_Mongo::$_current_databases[$connection_id]) || $database!=Database_Driver_Mongo::$_current_databases[$connection_id])
        {
            if (!Database_Driver_Mongo::$_connection_instance[$connection_id])
            {
                $this->connect();
                $this->select_db($database);
                return;
            }

            $connection = Database_Driver_Mongo::$_connection_instance[$connection_id]->selectDB($database);
            if (!$connection)
            {
                throw new Exception('选择Mongo数据表错误');
            }
            else
            {
                Database_Driver_Mongo::$_connection_instance_db[$connection_id] = $connection;
            }

            if (IS_DEBUG)
            {
                Core::debug()->log('mongodb change to database:'. $database);
            }

            # 记录当前已选中的数据库
            Database_Driver_Mongo::$_current_databases[$connection_id] = $database;
        }
    }

    public function compile($builder, $type = 'select')
    {
        $where = array();
        if (!empty($builder['where']))
        {
            $where = $this->_compile_conditions($builder['where']);
        }

        if ($type=='insert')
        {
            $sql = array
            (
                'type'    => 'insert',
                'table'   => $builder['table'],
                'options' => array
                (
                    'safe' => true,
                ),
            );

            if (count($builder['values'])>1)
            {
                # 批量插入
                $sql['type'] = 'batchinsert';

                $data = array();
                foreach ($builder['columns'] as $field)
                {
                    foreach ($builder['values'] as $k=>$v)
                    {
                        $data[$k][$field] = $builder['values'][$k][$field];
                    }
                }
                $sql['data'] = $data;
            }
            else
            {
                # 单条插入
                $data = array();
                foreach ($builder['columns'] as $field)
                {
                    $data[$field] = $builder['values'][0][$field];
                }
                $sql['data'] = $data;
            }
        }
        elseif ($type == 'update')
        {
            $sql = array
            (
                'type'    => 'update',
                'table'   => $builder['table'],
                'where'   => $where,
                'options' => array
                (
                    'multiple' => true,
                    'safe'     => true,
                ),
            );
            foreach ($builder['set'] as $item)
            {
                if ($item[2]=='+')
                {
                    $op = '$inc';
                }
                elseif  ($item[2]=='-')
                {
                    $item[1] = - $item[1];
                    $op = '$inc';
                }
                else
                {
                    $op = '$set';
                }
                $sql['data'][$op][$item[0]] = $item[1];
            }
        }
        elseif ($type == 'delete')
        {
            $sql = array
            (
                'type'    => 'remove',
                'table'   => $builder['table'],
                'where'   => $where,
            );
        }
        else
        {
            $sql = array
            (
                'type'  => $type,
                'table' => $builder['from'][0],
                'where' => $where,
                'limit' => $builder['limit'],
                'skip'  => $builder['offset'],
            );

            if ($builder['distinct'])
            {
                $sql['distinct'] = $builder['distinct'];
            }

            // 查询
            if ($builder['select'])
            {
                $s = array();
                foreach ($builder['select'] as $item)
                {
                    if (is_string($item))
                    {
                        $item = trim($item);
                        if (preg_match('#^(.*) as (.*)$#i', $item , $m))
                        {
                            $s[$m[1]] = $m[2];
                            $sql['select_as'][$m[1]] = $m[2];
                        }
                        else
                        {
                            $s[$item] = 1;
                        }
                    }
                    elseif (is_object($item))
                    {
                        if ($item instanceof Database_Expression)
                        {
                            $v = $item->value();
                            if ($v==='COUNT(1) AS `total_row_count`')
                            {
                                $sql['total_count'] = true;
                            }
                            else
                            {
                                $s[$v] = 1;
                            }
                        }
                        elseif (method_exists($item, '__toString'))
                        {
                            $s[(string)$item] = 1;
                        }
                    }
                }

                $sql['select'] = $s;
            }

            // 排序
            if ($builder['order_by'])
            {
                foreach ($builder['order_by'] as $item)
                {
                    $sql['sort'][$item[0]] = $item[1]=='DESC'?-1:1;
                }
            }

            // group by
            if ($builder['group_by'])
            {
                $sql['group_by'] = $builder['group_by'];
            }

            // 高级查询条件
            if ($builder['select_adv'])
            {
                $sql['select_adv'] = $builder['select_adv'];

                // 分组统计
                if (!$builder['group_by'])
                {
                    $sql['group_by'] = array('0');
                }
            }

            if ($builder['group_concat'])
            {
                $sql['group_concat'] = $builder['group_concat'];

                // 分组统计
                if (!$builder['group_by'])
                {
                    $sql['group_by'] = array('0');
                }
            }

        }

        return $sql;
    }

    public function set_charset($charset)
    {

    }

    public function escape($value)
    {
        return $value;
    }

    public function quote_table($value)
    {
        return $value;
    }

    /**
     * MongoDB 不需要处理
     *
     * @param mixed $value
     * @return mixed|string
     */
    public function quote($value)
    {
        return $value;
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

    }

    /**
     * 执行查询
     *
     * 目前支持插入、修改、保存（类似mysql的replace）查询
     *
     * $use_connection_type 默认不传为自动判断，可传true/false,若传字符串(只支持a-z0-9的字符串)，则可以切换到另外一个连接，比如传other,则可以连接到$this->_connection_other_id所对应的ID的连接
     *
     * @param array $options
     * @param string $as_object 是否返回对象
     * @param boolean $use_master 是否使用主数据库，不设置则自动判断
     * @return Database_Driver_Mongo_Result
     */
    public function query($options, $as_object = null, $connection_type = null)
    {
        if (IS_DEBUG)Core::debug()->log($options);

        if (is_string($options))
        {
            # 设置连接类型
            $this->_set_connection_type($connection_type);

            # 必需数组
            if (!is_array($as_object))$as_object = array();

            # 执行字符串式查询语句
            return $this->connection()->execute($options, $as_object);
        }

        $type = $this->_get_query_type($options, $connection_type);

        # 设置连接类型
        $this->_set_connection_type($connection_type);

        # 连接数据库
        $connection = $this->connection();

        if (!$options['table'])
        {
            throw new Exception('查询条件中缺少Collection');
        }

        $tablename = $this->config['table_prefix'] . $options['table'];

        if(IS_DEBUG)
        {
            static $is_sql_debug = null;

            if (null === $is_sql_debug) $is_sql_debug = (bool)Core::debug()->profiler('sql')->is_open();

            if ($is_sql_debug)
            {
                $host = $this->_get_hostname_by_connection_hash($this->connection_id());
                $benchmark = Core::debug()->profiler('sql')->start('Database', 'mongodb://'.($host['username']?$host['username'].'@':'') . $host['hostname'] . ($host['port'] && $host['port'] != '27017' ? ':' . $host['port'] : ''));
            }
        }

        $explain = null;

        try
        {
            switch ($type)
            {
                case 'SELECT':

                    if ($options['group_by'])
                    {
                        $alias_key = array();

                        $select = $options['select'];
                        # group by
                        $group_opt = array();
                        if (1===count($options['group_by']))
                        {
                            $k = current($options['group_by']);
                            $group_opt['_id'] = '$'.$k;
                            if (!isset($select[$k]))$select[$k] = 1;
                        }
                        else
                        {
                            $group_opt['_id'] = array();
                            foreach ($options['group_by'] as $item)
                            {
                                if (false!==strpos($item, '.'))
                                {
                                    $key      = str_replace('.', '->', $item);
                                    $group_opt['_id'][$key] = '$'.$item;
                                    $alias_key[$key] = $item;
                                }
                                else
                                {
                                    $group_opt['_id'][$item] = '$'.$item;
                                }

                                if (!isset($select[$item]))$select[$item] = 1;
                            }
                        }

                        $last_query = 'db.'.$tablename.'.aggregate(';
                        $ops = array();
                        if ($options['where'])
                        {
                            $last_query .= '{$match:'.json_encode($options['where']).'}';
                            $ops[] = array
                            (
                                '$match' => $options['where']
                            );
                        }

                        $group_opt['_count'] = array('$sum'=>1);
                        if ($select)
                        {
                            foreach ($select as $k=>$v)
                            {
                                if (1===$v || true===$v)
                                {
                                    if (false!==strpos($k, '.'))
                                    {
                                        $key             = str_replace('.', '->', $k);
                                        $group_opt[$key] = array('$first'=>'$'.$k);
                                        $alias_key[$key] = $k;
                                    }
                                    else
                                    {
                                        $group_opt[$k] = array('$first'=>'$'.$k);
                                    }
                                }
                                else
                                {
                                    if (false!==strpos($v, '.'))
                                    {
                                        $key             = str_replace('.', '->', $k);
                                        $group_opt[$key] = array('$first'=>'$'.$k);
                                        $alias_key[$key] = $k;
                                    }
                                    else
                                    {
                                        $group_opt[$v] = array('$first'=>'$'.$k);
                                    }
                                }
                            }
                        }

                        // 处理高级查询条件
                        if ($options['select_adv'])foreach ($options['select_adv'] as $item)
                        {
                            if (!is_array($item))continue;

                            if (is_array($item[0]))
                            {
                                $column = $item[0][0];
                                $alias  = $item[0][1];
                            }
                            else if (preg_match('#^(.*) AS (.*)$#i', $item[0] , $m))
                            {
                                $column = $m[1];
                                $alias  = $m[2];
                            }
                            else
                            {
                                $column = $alias = $item[0];
                            }

                            if (false!==strpos($alias, '.'))
                            {
                                $arr               = explode('.', $alias);
                                $alias             = implode('->', $arr);
                                $alias_key[$alias] = implode('.', $arr);
                                unset($arr);
                            }

                            switch ($item[1])
                            {
                                case 'max':
                                case 'min':
                                case 'avg':
                                case 'first':
                                case 'last':
                                    $group_opt[$alias] = array
                                    (
                                        '$'.$item[1] => '$'.$column,
                                    );
                                    break;
                                case 'addToSet':
                                case 'concat':
                                    $group_opt[$alias] = array
                                    (
                                        '$addToSet' => '$'.$column,
                                    );
                                    break;
                                case 'sum':
                                    $group_opt[$alias] = array
                                    (
                                        '$sum' => isset($item[2])?$item[2]:'$'.$column,
                                    );
                                    break;
                            }
                        }

                        if ($options['group_concat'])foreach($options['group_concat'] as $item)
                        {
                            if (is_array($item[0]))
                            {
                                $column = $item[0][0];
                                $alias  = $item[0][1];
                            }
                            else if (preg_match('#^(.*) AS (.*)$#i', $item[0] , $m))
                            {
                                $column = $m[1];
                                $alias  = $m[2];
                            }
                            else
                            {
                                $column = $alias = $item[0];
                            }

                            if (false!==strpos($alias, '.'))
                            {
                                $arr               = explode('.', $alias);
                                $alias             = implode('->', $arr);
                                $alias_key[$alias] = implode('.', $arr);
                                unset($arr);
                            }

                            if (isset($item[3]) && $item[3])
                            {
                                $fun = '$addToSet';
                            }
                            else
                            {
                                $fun = '$push';
                            }

                            $group_opt[$alias] = array
                            (
                                $fun => '$' . $column,
                            );

                            if (isset($item[1]) && $item[1])
                            {
                                $group_opt[$alias] = array
                                (
                                    '$sort' => array
                                    (
                                        $column => strtoupper($item[1])=='DESC'?-1:1,
                                    )
                                );
                            }
                        }

                        if ($options['distinct'])
                        {
                            # 唯一值

                            # 需要先把相应的数据$addToSet到一起
                            $group_opt['_distinct_'.$options['distinct']] = array
                            (
                                '$addToSet' => '$' . $options['distinct'],
                            );

                            $ops[] = array
                            (
                                '$group' => $group_opt,
                            );

                            $last_query .= ', {$group:'.json_encode($group_opt).'}';


                            $ops[] = array
                            (
                                '$unwind' => '$_distinct_'.$options['distinct']
                            );
                            $last_query .= ', {$unwind:"$_distinct_'.$options['distinct'].'"}';

                            $group_distinct = array();

                            # 将原来的group的数据重新加进来
                            foreach($group_opt as $k=>$v)
                            {
                                # 临时统计的忽略
                                if ($k=='_distinct_'.$options['distinct'])continue;

                                if ($k=='_id')
                                {
                                    $group_distinct[$k] = '$'.$k;
                                }
                                else
                                {
                                    $group_distinct[$k] = array('$first'=>'$'.$k);
                                }
                            }
                            $group_distinct[$options['distinct']] = array
                            (
                                '$sum' => 1
                            );

                            $ops[] = array
                            (
                                '$group' => $group_distinct
                            );
                            $last_query .= ', {$group:'. json_encode($group_distinct) .'}';
                        }
                        else
                        {
                            $ops[] = array
                            (
                                '$group' => $group_opt,
                            );

                            $last_query .= ', {$group:'.json_encode($group_opt).'}';
                        }

                        if (isset($options['sort']) && $options['sort'])
                        {
                            $ops[]['$sort'] = $options['sort'];
                            $last_query .= ', {$sort:'.json_encode($options['sort']).'}';
                        }

                        if (isset($options['skip']) && $options['skip']>0)
                        {
                            $ops[]['$skip'] = $options['skip'];
                            $last_query .= ', {$skip:'.$options['skip'].'}';
                        }

                        if (isset($options['limit']) && $options['limit']>0)
                        {
                            $ops[]['$limit'] = $options['limit'];
                            $last_query .= ', {$limit:'.$options['limit'].'}';
                        }

                        $last_query .= ')';

                        $result = $connection->selectCollection($tablename)->aggregate($ops);

                        // 兼容不同版本的aggregate返回
                        if ($result && ($result['ok']==1 || !isset($result['errmsg'])))
                        {
                            if ($result['ok']==1 && is_array($result['result']))$result = $result['result'];

                            if ($alias_key)foreach ($result as &$item)
                            {
                                // 处理 _ID 字段
                                if (is_array($item['_id']))foreach ($item['_id'] as $k=>$v)
                                {
                                    if (false!==strpos($k, '->'))
                                    {
                                        $item['_id'][str_replace('->', '.', $k)] = $v;
                                        unset($item['_id'][$k]);
                                    }
                                }

                                // 处理 select 的字段
                                foreach($alias_key as $k => $v)
                                {
                                    $item[$v] = $item[$k];
                                    unset($item[$k]);
                                }
                            }

                            if ($options['total_count'])
                            {
                                foreach ($result as &$item)
                                {
                                    $item['total_count'] = $item['_count'];
                                }
                            }
                            $count = count($result);

                            $rs = new Database_Driver_Mongo_Result(new ArrayIterator($result), $options, $as_object, $this->config);
                        }
                        else
                        {
                            throw new Exception($result['errmsg'].'.query: '.$last_query);
                        }
                    }
                    else if ($options['distinct'])
                    {
                        # 查询唯一值
                        $result = $connection->command(
                            array
                            (
                                'distinct' => $tablename,
                                'key'      => $options['distinct'] ,
                                'query'    => $options['where']
                            )
                        );

                        $last_query = 'db.'.$tablename.'.distinct('.$options['distinct'].', '.json_encode($options['where']).')';

                        if(IS_DEBUG && $is_sql_debug)
                        {
                            $count = count($result['values']);
                        }

                        if ($result && $result['ok']==1)
                        {
                            $rs = new Database_Driver_Mongo_Result(new ArrayIterator($result['values']), $options, $as_object, $this->config);
                        }
                        else
                        {
                            throw new Exception($result['errmsg']);
                        }
                    }
                    else
                    {
                        $last_query = 'db.'.$tablename.'.find(';
                        $last_query .= $options['where']?json_encode($options['where']):'{}';
                        $last_query .= $options['select']?', '.json_encode($options['select']):'';
                        $last_query .= ')';

                        $result = $connection->selectCollection($tablename)->find($options['where'], (array)$options['select']);

                        if(IS_DEBUG && $is_sql_debug)
                        {
                            $explain = $result->explain();
                            $count   = $result->count();
                        }

                        if ($options['total_count'])
                        {
                            $last_query .= '.count()';
                            $result = $result->count();
                            # 仅统计count
                            $rs = new Database_Driver_Mongo_Result(new ArrayIterator(array(array('total_row_count'=>$result))), $options, $as_object, $this->config);
                        }
                        else
                        {
                            if ($options['sort'])
                            {
                                $last_query .= '.sort('.json_encode($options['sort']).')';
                                $result = $result->sort($options['sort']);
                            }

                            if ($options['skip'])
                            {
                                $last_query .= '.skip('.json_encode($options['skip']).')';
                                $result = $result->skip($options['skip']);
                            }

                            if ($options['limit'])
                            {
                                $last_query .= '.limit('.json_encode($options['limit']).')';
                                $result = $result->limit($options['limit']);
                            }

                            $rs = new Database_Driver_Mongo_Result($result, $options, $as_object, $this->config);
                        }
                    }

                    break;
                case 'UPDATE':
                    $result = $connection->selectCollection($tablename)->update($options['where'], $options['data'], $options['options']);
                    $count = $rs = $result['n'];
                    $last_query = 'db.'.$tablename.'.update('.json_encode($options['where']).','.json_encode($options['data']).')';
                    break;
                case 'SAVE':
                case 'INSERT':
                case 'BATCHINSERT':
                    $fun = strtolower($type);
                    $result = $connection->selectCollection($tablename)->$fun($options['data'], $options['options']);

                    if ($type=='BATCHINSERT')
                    {
                        $count = count($options['data']);
                        # 批量插入
                        $rs = array
                        (
                            '',
                            $count,
                        );
                    }
                    elseif (isset($result['data']['_id']) && $result['data']['_id'] instanceof MongoId)
                    {
                        $count = 1;
                        $rs = array
                        (
                            (string)$result['data']['_id'] ,
                            1 ,
                        );
                    }
                    else
                    {
                        $count = 0;
                        $rs = array
                        (
                            '',
                            0,
                        );
                    }

                    if ($type=='BATCHINSERT')
                    {
                        $last_query = '';
                        foreach ($options['data'] as $d)
                        {
                            $last_query .= 'db.'.$tablename.'.insert('.json_encode($d).');'."\n";
                        }
                        $last_query = trim($last_query);
                    }
                    else
                    {
                        $last_query = 'db.'.$tablename.'.'.$fun.'('.json_encode($options['data']).')';
                    }
                    break;
                case 'REMOVE':
                    $result = $connection->selectCollection($tablename)->remove($options['where']);
                    $rs = $result['n'];

                    $last_query = 'db.'.$tablename.'.remove('.json_encode($options['where']).')';
                    break;
                default:
                    throw new Exception('不支持的操作类型');
            }
        }
        catch (Exception $e)
        {
            if(IS_DEBUG && isset($benchmark))
            {
                Core::debug()->profiler('sql')->stop();
            }

            throw $e;
        }

        $this->last_query = $last_query;

        # 记录调试
        if(IS_DEBUG)
        {
            Core::debug()->info($last_query,'MongoDB');

            if (isset($benchmark))
            {
                if ($is_sql_debug)
                {
                    $data = array();
                    $data[0]['db']              = $host['hostname'] . '/' . $this->config['connection']['database'] . '/';
                    $data[0]['cursor']          = '';
                    $data[0]['nscanned']        = '';
                    $data[0]['nscannedObjects'] = '';
                    $data[0]['n']               = '';
                    $data[0]['millis']          = '';
                    $data[0]['row']             = $count;
                    $data[0]['query']           = '';
                    $data[0]['nYields']         = '';
                    $data[0]['nChunkSkips']     = '';
                    $data[0]['isMultiKey']      = '';
                    $data[0]['indexOnly']       = '';
                    $data[0]['indexBounds']     = '';

                    if ($explain)
                    {
                        foreach ($explain as $k=>$v)
                        {
                            $data[0][$k] = $v;
                        }
                    }

                    $data[0]['query'] = $last_query;
                }
                else
                {
                    $data = null;
                }

                Core::debug()->profiler('sql')->stop($data);
            }
        }

        return $rs;
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
        // mongodb 不需要手动创建，可自动创建
    }

    protected function _compile_set_data($op, $value)
    {
        $op = strtolower($op);
        $op_arr = array
        (
            '>'  => 'gt',
            '>=' => 'gte',
            '<'  => 'lt',
            '<=' => 'lte',
            '!=' => 'ne',
            '<>' => 'ne',
        );

        $option = array();

        if ($op === 'between' && is_array($value))
        {
            list ($min, $max) = $value;

            $option['$gte'] = $min;

            $option['$lte'] = $max;
        }
        elseif ($op==='=')
        {
            if (is_object($value))
            {
                if ($value instanceof MongoCode)
                {
                    $option['$where'] = $value;
                }
                elseif ($value instanceof Database_Expression)
                {
                    $option = $value->value();
                }
                else
                {
                    $option = $value;
                }
            }
            else
            {
                $option = $value;
            }
        }
        elseif ($op==='in')
        {
            $option = array('$in'=>$value);
        }
        elseif ($op==='not in')
        {
            $option = array('$nin'=>$value);
        }
        elseif ($op==='mod')
        {
            if ($value[2]=='=')
            {
                $option = array('$mod'=>array($value[0],$value[1]));
            }
            elseif ($value[2]=='!='||$value[2]=='not')
            {
                $option = array
                (
                    '$ne' => array('$mod'=>array($value[0],$value[1]))
                );
            }
            elseif (substr($value[2], 0, 1)=='$')
            {
                $option = array
                (
                    $value[2] => array('$mod'=>array($value[0],$value[1]))
                );
            }
            elseif (isset($value[2]))
            {
                $option = array
                (
                    '$'.$value[2] => array('$mod'=>array($value[0],$value[1]))
                );
            }
        }
        elseif ($op==='like')
        {
            // 将like转换成正则处理
            $value = preg_quote($value, '/');

            if (substr($value, 0, 1)=='%')
            {
                $value = '/'. substr($value,1);
            }
            else
            {
                $value = '/^'. $value;
            }

            if (substr($value, -1)=='%')
            {
                $value = substr($value, 0, -1) . '/i';
            }
            else
            {
                $value = $value .'$/i';
            }

            $value = str_replace('%', '*', $value);

            $option = new MongoRegex($value);
        }
        else
        {
            if (isset($op_arr[$op]))
            {
                $option['$'.$op_arr[$op]] = $value;
            }
        }

        return $option;
    }

    protected function _compile_paste_data(&$tmp_query , $tmp_option , $last_logic , $now_logic , $column=null)
    {
        if ($last_logic!= $now_logic)
        {
            // 当$and $or 不一致时，则把前面所有的条件合并为一条组成一个$and|$or的条件
            if ($column)
            {
                $tmp_query = array($now_logic => $tmp_query ? array($tmp_query, array($column=>$tmp_option)) : array(array($column=>$tmp_option)));
            }
            else
            {
                $tmp_query = array($now_logic => $tmp_query ? array($tmp_query, $tmp_option) : array($tmp_option));
            }
        }
        elseif (isset($tmp_query[$now_logic]))
        {
            // 如果有 $and $or 条件，则加入
            if (is_array($tmp_option) || !$column)
            {
                $tmp_query[$now_logic][] = $tmp_option;
            }
            else
            {
                $tmp_query[$now_logic][] = array($column=>$tmp_option);
            }
        }
        else if ($column)
        {
            if (isset($tmp_query[$column]))
            {
                // 如果有相应的字段名，注，这里面已经不可能$logic=='$or'了
                if (is_array($tmp_option) && is_array($tmp_query[$column]))
                {
                    // 用于合并类似 $tmp_query = array('field_1'=>array('$lt'=>1));
                    // $tmp_option = array('field_1'=>array('$gt'=>10)); 这种情况
                    // 最后的合并结果就是 array('field_1'=>array('$lt'=>1,'$gt'=>10));
                    $need_reset = false;
                    foreach ($tmp_option as $tmpk => $tmpv)
                    {
                        if (isset($tmp_query[$column][$tmpk]))
                        {
                            $need_reset = true;
                            break;
                        }
                    }

                    if ($need_reset)
                    {
                        $tmp_query_bak = $tmp_query; // 给一个数据copy
                        $tmp_query = array('$and' => array()); // 清除$tmp_query

                        // 将条件全部加入$and里
                        foreach ($tmp_query_bak as $tmpk => $tmpv)
                        {
                            $tmp_query['$and'][] = array($tmpk => $tmpv);
                        }
                        unset($tmp_query_bak);

                        // 新增加的条件也加入进去
                        foreach ($tmp_option as $tmpk => $tmpv)
                        {
                            $tmp_query['$and'][] = array
                            (
                                $column => array($tmpk => $tmpv)
                            );
                        }
                    }
                    else
                    {
                        // 无需重新设置数据则合并
                        foreach ($tmp_option as $tmpk => $tmpv)
                        {
                            $tmp_query[$column][$tmpk] = $tmpv;
                        }
                    }

                }
                else
                {
                    $tmp_query['$and'] = array
                    (
                        array($column => $tmp_query[$column]),
                        array($column => $tmp_option),
                    );
                    unset($tmp_query[$column]);
                }
            }
            else
            {
                // 直接加入字段条件
                $tmp_query[$column] = $tmp_option;
            }
        }
        else
        {
            $tmp_query = array_merge($tmp_query, $tmp_option);
        }

        return $tmp_query;
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
        $last_logic     = '$and';
        $tmp_query_list = array();
        $query          = array();
        $tmp_query      =& $query;

        foreach ($conditions as $group)
        {
            foreach ($group as $logic => $condition)
            {
                $logic = '$'.strtolower($logic);        //$or,$and

                if ($condition === '(')
                {
                    $tmp_query_list[] = array();                                        //增加一行数据
                    unset($tmp_query);                                                  //删除引用关系，这样数据就保存在了$tmp_query_list里
                    $tmp_query         =& $tmp_query_list[count($tmp_query_list)-1];    //把指针移动到新的组里
                    $last_logic_list[] = $last_logic;                                   //放一个备份
                    $last_logic        = '$and';                                        //新组开启，把$last_logic设置成$and
                }
                elseif ($condition === ')')
                {
                    # 关闭一个组
                    $last_logic = array_pop($last_logic_list);                          //恢复上一个$last_logic

                    # 将最后一个移除
                    $tmp_query2 = array_pop($tmp_query_list);

                    $c = count($tmp_query_list);
                    unset($tmp_query);
                    if ($c)
                    {
                        $tmp_query =& $tmp_query_list[$c-1];
                    }
                    else
                    {
                        $tmp_query =& $query;
                    }
                    $this->_compile_paste_data($tmp_query, $tmp_query2, $last_logic, $logic);

                    unset($tmp_query2, $c);
                }
                else
                {
                    list ($column, $op, $value) = $condition;
                    $tmp_option = $this->_compile_set_data($op, $value);
                    $this->_compile_paste_data($tmp_query, $tmp_option, $last_logic, $logic, $column);

                    $last_logic = $logic;
                }

            }
        }

        return $query;
    }

    protected function _get_query_type($options, & $connection_type)
    {
        $type = strtoupper($options['type']);

        $slaverType = array
        (
            'SELECT',
            'SHOW',
            'EXPLAIN'
        );

        if (in_array($type, $slaverType))
        {
            if (true===$connection_type)
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
}