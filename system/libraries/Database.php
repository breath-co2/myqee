<?php defined('MYQEEPATH') or die('No direct script access.');

/**
 * Myqee Core.
 *
 * $Id$
 *
 * @package     MyQEE Core
 * @subpackage	Library
 * @author      Myqee Team
 * @copyright   (c) 2008-2010 Myqee Team
 * @license     http://www.myqee.com/license.html
 * @link		http://www.myqee.com/
 * @since		Version 1.0
 */
class Database_Core {

	// Global benchmark
	public static $benchmarks = array();

	// Configuration
	protected $config = array
	(
		'benchmark'     => TRUE,
		'persistent'    => FALSE,
		'connection'    => '',
		'character_set' => 'utf8',
		'table_prefix'  => '',
		'object'        => TRUE,
		'cache'         => FALSE,
		'escape'        => TRUE,
	);

	// Database driver object
	protected $driver;
	protected $link;

	// Un-compiled parts of the SQL query
	protected $select     = array();
	protected $set        = array();
	protected $from       = array();
	protected $join       = array();
	protected $where      = array();
	protected $orderby    = array();
	protected $order      = array();
	protected $groupby    = array();
	protected $having     = array();
	protected $distinct   = FALSE;
	protected $limit      = FALSE;
	protected $offset     = FALSE;
	protected $last_query = '';
	public $debug = array();
	
	private static $instances = array();
	/**
	 * Returns a singleton instance of Database.
	 *
	 * @param   mixed   configuration array or DSN
	 * @return  Database_Core
	 */
	public static function & instance($name = 'default', $config = NULL)
	{
		if ( ! isset(Database::$instances[$name]))
		{
			// Create a new instance
			Database::$instances[$name] = new Database($config === NULL ? $name : $config);
		}

		return Database::$instances[$name];
	}

	/**
	 * Sets up the database configuration, loads the Database_Driver.
	 *
	 * @throws  Error_Exception
	 */
	public function __construct($config = array())
	{
		
		if (empty($config) || $config===FALSE || $config===NULL)
		{
			$config = Myqee::config('database.default');
		}
		if (is_string($config)){
			// Load the default group
			$config = Myqee::config('database.'.$config);
		}
		elseif (is_array($config) && count($config) > 0)
		{
			if ( ! array_key_exists('connection', $config))
			{
				$config = array('connection' => $config);
			}
		}
		// Merge the default config with the passed config
		$this->config = array_merge($this->config, $config);
	}

	/**
	 * Simple connect method to get the database queries up and running.
	 *
	 * @return  void
	 */
	public function connect()
	{
		// A link can be a resource or an object
		if (is_resource($this->link))
			return $this->link;

		// Import the connect variables
		extract($this->config['connection']);

		// Persistent connections enabled?
		$connect = ($this->config['persistent'] == TRUE) ? 'mysql_pconnect' : 'mysql_connect';

		// Build the connection info
		$host = isset($host) ? $host : $socket;
		$port = isset($port) ? ':'.$port : '';

		// Make the connection and select the database
		if (($this->link = $connect($host.$port, $user, $pass, TRUE)) AND mysql_select_db($database, $this->link))
		{
			if ($charset = $this->config['character_set'])
			{
				$this->set_charset($charset);
			}
			
			if($this->version() > '5.0') {
				mysql_query("SET sql_mode=''", $this->link);
			}

			// Clear password after successful connect
			$this->config['connection']['pass'] = NULL;

			return $this->link;
		}

		return FALSE;
	}
	
	
	public function version() {
		static $version;
		if ($version)return $version;
		$version = mysql_get_server_info($this->link);
		return $version;
	}
	
	public function set_charset($charset)
	{
		$charset = $this->escape_str($charset);
		if($this->version() > '5.0') {
			$this->query('SET NAMES '.$charset);

		}else if($this->version() > '4.1'){
			mysql_query("SET character_set_connection=".$charset.", character_set_results=".$charset.", character_set_client=binary", $this->link);
		}
	}
	/**
	 * Runs a query into the driver and returns the result.
	 *
	 * @param   string  SQL query to execute
	 * @return  Database_Result
	 */
	public function query($sql = '')
	{
		if ($sql == '') return FALSE;

		// No link? Connect!
		$this->link or $this->connect();

		// Start the benchmark
		$start = microtime(TRUE);

		if (func_num_args() > 1) //if we have more than one argument ($sql)
		{
			$argv = func_get_args();
			$binds = (is_array(next($argv))) ? current($argv) : array_slice($argv, 1);
		}

		// Compile binds if needed
		if (isset($binds))
		{
			$sql = $this->compile_binds($sql, $binds);
		}
		$this->debug[] = $sql;
		// Fetch the result
		return new Mysql_Result(mysql_query($sql, $this->link), $this->link, $this->config['object'], $sql);

	}

	/**
	 * Selects the column names for a database query.
	 *
	 * @param   string  string or array of column names to select
	 * @return  Database      This Database object.
	 */
	public function select($sql = '*')
	{
		if (func_num_args() > 1)
		{
			$sql = func_get_args();
		}
		elseif (is_string($sql))
		{
			$sql = explode(',', $sql);
		}
		else
		{
			$sql = (array) $sql;
		}

		foreach ($sql as $val)
		{
			if (($val = trim($val)) === '') continue;

			if (strpos($val, '(') === FALSE AND $val !== '*')
			{
				if (preg_match('/^DISTINCT\s++(.+)$/i', $val, $matches))
				{
					$val            = $this->config['table_prefix'].$matches[1];
					$this->distinct = TRUE;
				}
				else
				{
					$val = (strpos($val, '.') !== FALSE) ? $this->config['table_prefix'].$val : $val;
				}

				$val = $this->escape_column($val);
			}

			$this->select[] = $val;
		}

		return $this;
	}

	/**
	 * Selects the from table(s) for a database query.
	 *
	 * @param   string  string or array of tables to select
	 * @return  Database      This Database object.
	 */
	public function from($sql)
	{
		if (func_num_args() > 1)
		{
			$sql = func_get_args();
		}
		elseif (is_string($sql))
		{
			$sql = explode(',', $sql);
		}
		else
		{
			$sql = (array) $sql;
		}

		foreach ($sql as $val)
		{
			if (($val = trim($val)) === '') continue;

			$this->from[] = $this->config['table_prefix'].$val;
		}

		return $this;
	}

	/**
	 * Generates the JOIN portion of the query.
	 *
	 * @param   string        table name
	 * @param   string|array  where key or array of key => value pairs
	 * @param   string        where value
	 * @param   string        type of join
	 * @return  object        This Database object.
	 */
	public function join($table, $key, $value = NULL, $type = '')
	{
		if ($type != '')
		{
			$type = strtoupper(trim($type));

			if ( ! in_array($type, array('LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER'), TRUE))
			{
				$type = '';
			}
			else
			{
				$type .= ' ';
			}
		}

		$cond = array();
		$keys  = is_array($key) ? $key : array($key => $value);
		foreach ($keys as $key => $value)
		{
			$key    = (strpos($key, '.') !== FALSE) ? $this->config['table_prefix'].$key : $key;
			$cond[] = $this->fwhere($key, $this->escape_column($this->config['table_prefix'].$value), 'AND ', count($cond), FALSE);
		}

		$this->join[] = $type.'JOIN '.$this->escape_column($this->config['table_prefix'].$table).' ON '.implode(' ', $cond);

		return $this;
	}

	/**
	 * Selects the where(s) for a database query.
	 *
	 * @param   string|array  key name or array of key => value pairs
	 * @param   string        value to match with key
	 * @param   boolean       disable quoting of WHERE clause
	 * @return  object        This Database object.
	 */
	public function where($key, $value = NULL, $quote = TRUE)
	{
		$quote = (func_num_args() < 2 AND ! is_array($key)) ? -1 : $quote;
		$keys  = is_array($key) ? $key : array($key => $value);

		foreach ($keys as $key => $value)
		{
			$key           = (strpos($key, '.') !== FALSE) ? $this->config['table_prefix'].$key : $key;
			$this->where[] = $this->fwhere($key, $value, 'AND ', count($this->where), $quote);
		}

		return $this;
	}

	/**
	 * Selects the or where(s) for a database query.
	 *
	 * @param   string|array  key name or array of key => value pairs
	 * @param   string        value to match with key
	 * @param   boolean       disable quoting of WHERE clause
	 * @return  object        This Database object.
	 */
	public function orwhere($key, $value = NULL, $quote = TRUE)
	{
		$quote = (func_num_args() < 2 AND ! is_array($key)) ? -1 : $quote;
		$keys  = is_array($key) ? $key : array($key => $value);

		foreach ($keys as $key => $value)
		{
			$key           = (strpos($key, '.') !== FALSE) ? $this->config['table_prefix'].$key : $key;
			$this->where[] = $this->fwhere($key, $value, 'OR ', count($this->where), $quote);
		}

		return $this;
	}

	/**
	 * Selects the like(s) for a database query.
	 *
	 * @param   string|array  field name or array of field => match pairs
	 * @param   string        like value to match with field
	 * @param   boolean       automatically add starting and ending wildcards
	 * @return  object        This Database object.
	 */
	public function like($field, $match = '', $auto = TRUE)
	{
		$fields = is_array($field) ? $field : array($field => $match);

		foreach ($fields as $field => $match)
		{
			$field         = (strpos($field, '.') !== FALSE) ? $this->config['table_prefix'].$field : $field;
			$this->where[] = $this->flike($field, $match, $auto, 'AND ', count($this->where));
		}

		return $this;
	}
	public function flike($field, $match = '', $auto = TRUE, $type = 'AND ', $num_likes)
	{
		$prefix = ($num_likes == 0) ? '' : $type;

		$match = $this->escape_str($match);

		if ($auto === TRUE)
		{
			// Add the start and end quotes
			$match = '%'.$match.'%';
		}

		return $prefix.' '.$this->escape_column($field).' LIKE \''.$match . '\'';
	}
	/**
	 * Selects the or like(s) for a database query.
	 *
	 * @param   string|array  field name or array of field => match pairs
	 * @param   string        like value to match with field
	 * @param   boolean       automatically add starting and ending wildcards
	 * @return  object        This Database object.
	 */
	public function orlike($field, $match = '', $auto = TRUE)
	{
		$fields = is_array($field) ? $field : array($field => $match);

		foreach ($fields as $field => $match)
		{
			$field         = (strpos($field, '.') !== FALSE) ? $this->config['table_prefix'].$field : $field;
			$this->where[] = $this->flike($field, $match, $auto, 'OR ', count($this->where));
		}

		return $this;
	}
	public function fnotlike($field, $match = '', $auto = TRUE, $type = 'AND ', $num_likes)
	{
		$prefix = ($num_likes == 0) ? '' : $type;

		$match = $this->escape_str($match);

		if ($auto === TRUE)
		{
			// Add the start and end quotes
			$match = '%'.$match.'%';
		}

		return $prefix.' '.$this->escape_column($field).' NOT LIKE \''.$match.'\'';
	}
	/**
	 * Selects the not like(s) for a database query.
	 *
	 * @param   string|array  field name or array of field => match pairs
	 * @param   string        like value to match with field
	 * @param   boolean       automatically add starting and ending wildcards
	 * @return  object        This Database object.
	 */
	public function notlike($field, $match = '', $auto = TRUE)
	{
		$fields = is_array($field) ? $field : array($field => $match);

		foreach ($fields as $field => $match)
		{
			$field         = (strpos($field, '.') !== FALSE) ? $this->config['table_prefix'].$field : $field;
			$this->where[] = $this->fnotlike($field, $match, $auto, 'AND ', count($this->where));
		}

		return $this;
	}

	/**
	 * Selects the or not like(s) for a database query.
	 *
	 * @param   string|array  field name or array of field => match pairs
	 * @param   string        like value to match with field
	 * @return  object        This Database object.
	 */
	public function ornotlike($field, $match = '', $auto = TRUE)
	{
		$fields = is_array($field) ? $field : array($field => $match);

		foreach ($fields as $field => $match)
		{
			$field         = (strpos($field, '.') !== FALSE) ? $this->config['table_prefix'].$field : $field;
			$this->where[] = $this->fnotlike($field, $match, $auto, 'OR ', count($this->where));
		}

		return $this;
	}
	public function fregex($field, $match = '', $type = 'AND ', $num_regexs)
	{
		$prefix = ($num_regexs == 0) ? '' : $type;

		return $prefix.' '.$this->escape_column($field).' REGEXP \''.$this->escape_str($match).'\'';
	}
	/**
	 * Selects the like(s) for a database query.
	 *
	 * @param   string|array  field name or array of field => match pairs
	 * @param   string        like value to match with field
	 * @return  object        This Database object.
	 */
	public function regex($field, $match = '')
	{
		$fields = is_array($field) ? $field : array($field => $match);

		foreach ($fields as $field => $match)
		{
			$field         = (strpos($field, '.') !== FALSE) ? $this->config['table_prefix'].$field : $field;
			$this->where[] = $this->fregex($field, $match, 'AND ', count($this->where));
		}

		return $this;
	}

	/**
	 * Selects the or like(s) for a database query.
	 *
	 * @param   string|array  field name or array of field => match pairs
	 * @param   string        like value to match with field
	 * @return  object        This Database object.
	 */
	public function orregex($field, $match = '')
	{
		$fields = is_array($field) ? $field : array($field => $match);

		foreach ($fields as $field => $match)
		{
			$field         = (strpos($field, '.') !== FALSE) ? $this->config['table_prefix'].$field : $field;
			$this->where[] = $this->fregex($field, $match, 'OR ', count($this->where));
		}

		return $this;
	}
	public function fnotregex($field, $match, $type, $num_regexs)
	{
		throw new Error_Exception('database.not_implemented'. __FUNCTION__);
	}
	/**
	 * Selects the not regex(s) for a database query.
	 *
	 * @param   string|array  field name or array of field => match pairs
	 * @param   string        regex value to match with field
	 * @return  object        This Database object.
	 */
	public function notregex($field, $match = '')
	{
		$fields = is_array($field) ? $field : array($field => $match);

		foreach ($fields as $field => $match)
		{
			$field         = (strpos($field, '.') !== FALSE) ? $this->config['table_prefix'].$field : $field;
			$this->where[] = $this->fnotregex($field, $match, 'AND ', count($this->where));
		}

		return $this;
	}

	/**
	 * Selects the or not regex(s) for a database query.
	 *
	 * @param   string|array  field name or array of field => match pairs
	 * @param   string        regex value to match with field
	 * @return  object        This Database object.
	 */
	public function ornotregex($field, $match = '')
	{
		$fields = is_array($field) ? $field : array($field => $match);

		foreach ($fields as $field => $match)
		{
			$field         = (strpos($field, '.') !== FALSE) ? $this->config['table_prefix'].$field : $field;
			$this->where[] = $this->fnotregex($field, $match, 'OR ', count($this->where));
		}

		return $this;
	}

	/**
	 * Chooses the column to group by in a select query.
	 *
	 * @param   string  column name to group by
	 * @return  Database      This Database object.
	 */
	public function groupby($by)
	{
		if ( ! is_array($by))
		{
			$by = explode(',', (string) $by);
		}

		foreach ($by as $val)
		{
			$val = trim($val);

			if ($val != '')
			{
				$this->groupby[] = $this->escape_column($val);
			}
		}

		return $this;
	}
	public function fwhere($key, $value, $type, $num_wheres, $quote)
	{
		$prefix = ($num_wheres == 0) ? '' : $type;

		if ($quote === -1)
		{
			$value = '';
		}
		else
		{
			if ($value === NULL)
			{
				if ( ! $this->has_operator($key))
				{
					$key .= ' IS';
				}

				$value = ' NULL';
			}
			elseif (is_bool($value))
			{
				if ( ! $this->has_operator($key))
				{
					$key .= ' =';
				}

				$value = ($value == TRUE) ? ' 1' : ' 0';
			}
			else
			{
				if ( ! $this->has_operator($key))
				{
					$key = $this->escape_column($key).' =';
				}
				else
				{
					preg_match('/^(.+?)([<>!=]+|\bIS(?:\s+NULL))\s*$/i', $key, $matches);
					if (isset($matches[1]) AND isset($matches[2]))
					{
						$key = $this->escape_column(trim($matches[1])).' '.trim($matches[2]);
					}
				}

				$value = ' '.(($quote == TRUE) ? $this->escape($value) : $value);
			}
		}

		return $prefix.$key.$value;
	}

	public function has_operator($str)
	{
		return (bool) preg_match('/[<>!=]|\sIS(?:\s+NOT\s+)?\b/i', trim($str));
	}
	/**
	 * Selects the having(s) for a database query.
	 *
	 * @param   string|array  key name or array of key => value pairs
	 * @param   string        value to match with key
	 * @param   boolean       disable quoting of WHERE clause
	 * @return  object        This Database object.
	 */
	public function having($key, $value = '', $quote = TRUE)
	{
		$this->having[] = $this->fwhere($key, $value, 'AND', count($this->having), TRUE);
		return $this;
	}

	/**
	 * Selects the or having(s) for a database query.
	 *
	 * @param   string|array  key name or array of key => value pairs
	 * @param   string        value to match with key
	 * @param   boolean       disable quoting of WHERE clause
	 * @return  object        This Database object.
	 */
	public function orhaving($key, $value = '', $quote = TRUE)
	{
		$this->having[] = $this->fwhere($key, $value, 'OR', count($this->having), TRUE);
		return $this;
	}

	/**
	 * Chooses which column(s) to order the select query by.
	 *
	 * @param   string|array  column(s) to order on, can be an array, single column, or comma seperated list of columns
	 * @param   string        direction of the order
	 * @return  object        This Database object.
	 */
	public function orderby($orderby, $direction = '')
	{
		if ( ! is_array($orderby))
		{
			$orderby = array($orderby => $direction);
		}

		foreach ($orderby as $column => $direction)
		{
			$direction = strtoupper(trim($direction));

			// Add a direction if the provided one isn't valid
			if ( ! in_array($direction, array('ASC', 'DESC', 'RAND()', 'RANDOM()', 'NULL')))
			{
				$direction = 'ASC';
			}

			// Add the table prefix if a table.column was passed
			if (strpos($column, '.'))
			{
				$column = $this->config['table_prefix'].$column;
			}

			$this->orderby[] = $this->escape_column($column).' '.$direction;
		}
		return $this;
	}

	/**
	 * Selects the limit section of a query.
	 *
	 * @param   integer  number of rows to limit result to
	 * @param   integer  offset in result to start returning rows from
	 * @return  object   This Database object.
	 */
	public function limit($limit, $offset = FALSE)
	{
		$this->limit  = (int) $limit;
		$this->offset(($offset === FALSE)? 0 : $offset);

		return $this;
	}

	public function flimit($limit, $offset = FALSE)
	{
		return 'LIMIT '.$offset.', '.$limit;
	}
	/**
	 * Sets the offset portion of a query.
	 *
	 * @param   integer  offset value
	 * @return  object   This Database object.
	 */
	public function offset($value)
	{
		$this->offset = (int) $value;

		return $this;
	}

	/**
	 * Allows key/value pairs to be set for inserting or updating.
	 *
	 * @param   string|array  key name or array of key => value pairs
	 * @param   string        value to match with key
	 * @return  object        This Database object.
	 */
	public function set($key, $value = '')
	{
		if ( ! is_array($key))
		{
			$key = array($key => $value);
		}

		foreach ($key as $k => $v)
		{
			$this->set[$k] = $this->escape($v);
		}

		return $this;
	}

	/**
	 * Compiles the select statement based on the other functions called and runs the query.
	 *
	 * @param   string  table name
	 * @param   string  limit clause
	 * @param   string  offset clause
	 * @return  object  Database_Result
	 */
	public function get($table = '', $limit = NULL, $offset = NULL)
	{
		if ($table != '')
		{
			$this->from($table);
		}

		if ( ! is_null($limit))
		{
			$this->limit($limit, $offset);
		}

		$sql = $this->compile_select(get_object_vars($this));

		$result = $this->query($sql);

		$this->reset_select();
		$this->last_query = $sql;

		return $result;
	}
	

	public function get_result($table = '', $limit = NULL, $offset = NULL,$object = false, $type = MYSQL_ASSOC){
		$result = $this -> get($table , $limit , $offset);
		return $result -> result_array($object,$type);
	}

	/**
	 * Compiles the select statement based on the other functions called and runs the query.
	 *
	 * @param   string  table name
	 * @param   array   where clause
	 * @param   string  limit clause
	 * @param   string  offset clause
	 * @return  Database      This Database object.
	 */
	public function getwhere($table = '', $where = NULL, $limit = NULL, $offset = NULL)
	{
		if ($table != '')
		{
			$this->from($table);
		}

		if ( ! is_null($where))
		{
			$this->where($where);
		}

		if ( ! is_null($limit))
		{
			$this->limit($limit, $offset);
		}

		$sql = $this->compile_select(get_object_vars($this));

		$result = $this->query($sql);
		$this->reset_select();
		return $result;
	}

	/**
	 * Compiles the select statement based on the other functions called and returns the query string.
	 *
	 * @param   string  table name
	 * @param   string  limit clause
	 * @param   string  offset clause
	 * @return  string  sql string
	 */
	public function compile($table = '', $limit = NULL, $offset = NULL)
	{
		if ($table != '')
		{
			$this->from($table);
		}

		if ( ! is_null($limit))
		{
			$this->limit($limit, $offset);
		}

		$sql = $this->compile_select(get_object_vars($this));

		$this->reset_select();

		return $sql;
	}
	public function compile_select($database=array())
	{

		if(empty($database)){
			$database = get_object_vars($this);

		}
		$sql = ($database['distinct'] == TRUE) ? 'SELECT DISTINCT ' : 'SELECT ';
		$sql .= (count($database['select']) > 0) ? implode(', ', $database['select']) : '*';

		if (count($database['from']) > 0)
		{
			// Escape the tables
			$froms = array();
			foreach ($database['from'] as $from)
				$froms[] = $this->escape_column($from);
			$sql .= "\nFROM ";
			$sql .= implode(', ', $froms);
		}

		if (count($database['join']) > 0)
		{
			$sql .= ' '.implode("\n", $database['join']);
		}

		if (count($database['where']) > 0)
		{
			$sql .= "\nWHERE ";
		}

		$sql .= implode("\n", $database['where']);

		if (count($database['groupby']) > 0)
		{
			$sql .= "\nGROUP BY ";
			$sql .= implode(', ', $database['groupby']);
		}

		if (count($database['having']) > 0)
		{
			$sql .= "\nHAVING ";
			$sql .= implode("\n", $database['having']);
		}

		if (count($database['orderby']) > 0)
		{
			$sql .= "\nORDER BY ";
			$sql .= implode(', ', $database['orderby']);
		}

		if (is_numeric($database['limit']))
		{
			$sql .= "\n";
			$sql .= $this->flimit($database['limit'], $database['offset']);
		}

		return $sql;
	}
	/**
	 * Compiles an insert string and runs the query.
	 *
	 * @param   string  table name
	 * @param   array   array of key/value pairs to insert
	 * @return  Database      This Database object.
	 */
	public function insert($table = '', $set = NULL)
	{
		if ( ! is_null($set))
		{
			$this->set($set);
		}

		if ($this->set == NULL)
			throw new Error_Exception('database.must_use_set');

		if ($table == '')
		{
			if ( ! isset($this->from[0]))
				throw new Error_Exception('database.must_use_table');

			$table = $this->from[0];
		}

		// If caching is enabled, clear the cache before inserting
		($this->config['cache'] === TRUE) and $this->clear_cache();

		$keys = array_keys($this->set);
		foreach ($keys as $key => $value)
		{
			$keys[$key] = $this->escape_column($value);
		}
		$sql = 'INSERT INTO '.$this->escape_table($this->config['table_prefix'].$table).' ('.implode(', ', $keys).') VALUES ('.implode(', ', array_values($this->set)).')';
		
		$this->reset_write();
		return $this->query($sql);
	}

	/**
	 * Adds an "IN" condition to the where clause
	 *
	 * @param   string  Name of the column being examined
	 * @param   mixed   An array or string to match against
	 * @param   bool    Generate a NOT IN clause instead
	 * @return  Database      This Database object.
	 */
	public function in($field, $values, $not = FALSE)
	{

		if (is_array($values))
		{
			$escaped_values = array();
			foreach ($values as $v)
			{
				if (is_numeric($v))
				{
					$escaped_values[] = $v;
				}
				else
				{
					$escaped_values[] = "'".$this->escape_str($v)."'";
				}
			}
			$values = implode(",", $escaped_values);
		}

		$this->where($this->escape_column($field).' '.($not === TRUE ? 'NOT ' : '').'IN ('.$values.')');

		return $this;
	}

	/**
	 * Adds a "NOT IN" condition to the where clause
	 *
	 * @param   string  Name of the column being examined
	 * @param   mixed   An array or string to match against
	 * @return  Database      This Database object.
	 */
	public function notin($field, $values)
	{
		return $this->in($field, $values, TRUE);
	}

	/**
	 * Compiles a merge string and runs the query.
	 *
	 * @param   string  table name
	 * @param   array   array of key/value pairs to merge
	 * @return  Database      This Database object.
	 */
	public function merge($table = '', $set = NULL)
	{
		if ( ! is_null($set))
		{
			$this->set($set);
		}

		if ($this->set == NULL)
			throw new Error_Exception('database.must_use_set');

		if ($table == '')
		{
			if ( ! isset($this->from[0]))
				throw new Error_Exception('database.must_use_table');

			$table = $this->from[0];
		}

		$sql = $this->fmerge($this->config['table_prefix'].$table, array_keys($this->set), array_values($this->set));

		$this->reset_write();
		return $this->query($sql);
	}
	public function fmerge($table, $keys, $values)
	{
		// Escape the column names
		foreach ($keys as $key => $value)
		{
			$keys[$key] = $this->escape_column($value);
		}
		return 'REPLACE INTO '.$this->escape_table($table).' ('.implode(', ', $keys).') VALUES ('.implode(', ', $values).')';
	}
	/**
	 * Compiles an update string and runs the query.
	 *
	 * @param   string  table name
	 * @param   array   associative array of update values
	 * @param   array   where clause
	 * @return  Database      This Database object.
	 */
	public function update($table = '', $set = NULL, $where = NULL)
	{
		if ( is_array($set))
		{
			$this->set($set);
		}

		if ( ! is_null($where))
		{
			$this->where($where);
		}

		if ($this->set == FALSE)
			throw new Error_Exception('database.must_use_set');

		if ($table == '')
		{
			if ( ! isset($this->from[0]))
				throw new Error_Exception('database.must_use_table');

			$table = $this->from[0];
		}

		foreach ($this->set as $key => $val)
		{
			$valstr[] = $this->escape_column($key).' = '.$val;
		}
		$sql =  'UPDATE '.$this->escape_table($this->config['table_prefix'].$table).' SET '.implode(', ', $valstr).' WHERE '.implode(' ',$this->where);
		$this->reset_write();
		return $this->query($sql);
	}

	/**
	 * Compiles a delete string and runs the query.
	 *
	 * @param   string  table name
	 * @param   array   where clause
	 * @return  Database      This Database object.
	 */
	public function delete($table = '', $where = NULL)
	{
		if ($table == '')
		{
			if ( ! isset($this->from[0]))
				throw new Error_Exception('database.must_use_table');

			$table = $this->from[0];
		}

		if (! is_null($where))
		{
			$this->where($where);
		}

		if (count($this->where) < 1)
			throw new Error_Exception('database.must_use_where');
		$sql =  'DELETE FROM '.$this->escape_table($this->config['table_prefix'].$table).' WHERE '.implode(' ', $this->where);
		$this->reset_write();
		return $this->query($sql);
	}

	/**
	 * Returns the last query run.
	 *
	 * @return  string
	 */
	public function last_query()
	{
	   return $this->last_query;
	}

	/**
	 * Count query records.
	 *
	 * @param   string   table name
	 * @param   array    where clause
	 * @return  integer
	 */
	public function count_records($table = FALSE, $where = NULL)
	{
		if (count($this->from) < 1)
		{
			if ($table == FALSE)
				throw new Error_Exception('database.must_use_table');

			$this->from($table);
		}

		if ($where !== NULL)
		{
			$this->where($where);
		}

		$rs = $this->select('COUNT(*) AS records_found')->get()->result_array(FALSE);

		return (int) $rs[0]['records_found'];
	}

	/**
	 * Resets all protected select variables.
	 *
	 * @return  void
	 */
	protected function reset_select()
	{
		$this->select   = array();
		$this->from     = array();
		$this->join     = array();
		$this->where    = array();
		$this->orderby  = array();
		$this->groupby  = array();
		$this->having   = array();
		$this->distinct = FALSE;
		$this->limit    = FALSE;
		$this->offset   = FALSE;
	}

	/**
	 * Resets all protected insert and update variables.
	 *
	 * @return  void
	 */
	protected function reset_write()
	{
		$this->set   = array();
		$this->from  = array();
		$this->where = array();
	}

	/**
	 * Lists all the tables in the current database.
	 *
	 * @return  array
	 */
	public function list_tables()
	{
		$this->link or $this->connect();

		$this->reset_select();
		static $tables;

		$sql    = 'SHOW TABLES FROM `'.$this->config['connection']['database'].'`';
		$result = $this->query($sql)->result(FALSE, MYSQL_ASSOC);

		$retval = array();
		foreach ($result as $row)
		{
			$retval[] = current($row);
		}
		$tables = $retval;
		return $tables;
	}
	
	protected function sql_type($str)
	{
		static $sql_types;

		if ($sql_types === NULL)
		{
			// Load SQL data types
			$sql_types = Myqee::config('sql_types');
		}

		$str = strtolower(trim($str));

		if (($open  = strpos($str, '(')) !== FALSE)
		{
			// Find closing bracket
			$close = strpos($str, ')', $open) - 1;

			// Find the type without the size
			$type = substr($str, 0, $open);
		}
		else
		{
			// No length
			$type = $str;
		}
		empty($sql_types[$type]) and exit
		(
			'Unknown field type: '.$type.'. '.
			'Please report this: http://trac.myqee.com/newticket'
		);

		// Fetch the field definition
		$field = $sql_types[$type];

		switch ($field['type'])
		{
			case 'string':
			case 'float':
				if (isset($close))
				{
					// Add the length to the field info
					$field['length'] = substr($str, $open + 1, $close - $open);
				}
			break;
			case 'int':
				// Add unsigned value
				$field['unsigned'] = (strpos($str, 'unsigned') !== FALSE);
			break;
		}

		return $field;
	}
	
	public function list_fields($table)
	{
		$this->link or $this->connect();
		
		$tables =& $this->fields_cache;

		if (empty($tables[$table]))
		{
			foreach ($this->field_data($table) as $row)
			{
				// Make an associative array
				$tables[$table][$row->Field] = $this->sql_type($row->Type);

				if ($row->Key === 'PRI' AND $row->Extra === 'auto_increment')
				{
					// For sequenced (AUTO_INCREMENT) tables
					$tables[$table][$row->Field]['sequenced'] = TRUE;
				}

				if ($row->Null === 'YES')
				{
					// Set NULL status
					$tables[$table][$row->Field]['null'] = TRUE;
				}
			}
		}

		if (!isset($tables[$table]))
			throw new Error_Exception('database.table_not_found');
			//throw new Kohana_Database_Exception('database.table_not_found', $table);

		return $tables[$table];
	}

	/**
	 * Get the field data for a database table, along with the field's attributes.
	 *
	 * @param   string  table name
	 * @return  array
	 */
	public function field_data($table)
	{
		$this->link or $this->connect();
		
		$columns = array();

		if ($query = mysql_query('SHOW COLUMNS FROM '.$this->escape_table($this->config['table_prefix'].$table), $this->link))
		{
			if (mysql_num_rows($query))
			{
				while ($row = mysql_fetch_object($query))
				{
					$columns[] = $row;
				}
			}
		}

		return $columns;
	}
	
	/**
	 * See if a table exists in the database.
	 *
	 * @param   string   table name
	 * @return  boolean
	 */
	public function table_exists($table_name)
	{

		return in_array($this->config['table_prefix'].$table_name, $this->list_tables());
	}

	/**
	 * Combine a SQL statement with the bind values. Used for safe queries.
	 *
	 * @param   string  query to bind to the values
	 * @param   array   array of values to bind to the query
	 * @return  string
	 */
	public function compile_binds($sql, $binds)
	{
		foreach ((array) $binds as $val)
		{
			// If the SQL contains no more bind marks ("?"), we're done.
			if (($next_bind_pos = strpos($sql, '?')) === FALSE)
				break;

			// Properly escape the bind value.
			$val = $this->escape($val);

			// Temporarily replace possible bind marks ("?"), in the bind value itself, with a placeholder.
			$val = str_replace('?', '{%B%}', $val);

			// Replace the first bind mark ("?") with its corresponding value.
			$sql = substr($sql, 0, $next_bind_pos).$val.substr($sql, $next_bind_pos + 1);
		}

		// Restore placeholders.
		return str_replace('{%B%}', '?', $sql);
	}


	/**
	 * Escapes a value for a query.
	 *
	 * @param   mixed   value to escape
	 * @return  string
	 */
	public function escape($value)
	{
		if ( ! $this->config['escape'])
			return $value;

		switch (gettype($value))
		{
			case 'string':
				$value = '\''.$this->escape_str($value).'\'';
			break;
			case 'boolean':
				$value = (int) $value;
			break;
			case 'double':
				// Convert to non-locale aware float to prevent possible commas
				$value = sprintf('%F', $value);
			break;
			default:
				$value = ($value === NULL) ? 'NULL' : $value;
			break;
		}

		return (string) $value;
	}

	/**
	 * Escapes a string for a query.
	 *
	 * @param   string  string to escape
	 * @return  string
	 */
	public function escape_str($str)
	{
		if (!$this->config['escape'])
			return $str;

		is_resource($this->link) or $this->connect();

		return mysql_real_escape_string($str, $this->link);
	}

	/**
	 * Escapes a table name for a query.
	 *
	 * @param   string  string to escape
	 * @return  string
	 */
	public function escape_table($table)
	{
		if (!$this->config['escape'])
			return $table;
		if (stripos($table, ' AS ') !== FALSE)
		{
			// Force 'AS' to uppercase
			$table = str_ireplace(' AS ', ' AS ', $table);

			// Runs escape_table on both sides of an AS statement
			$table = array_map(array($this, __FUNCTION__), explode(' AS ', $table));

			// Re-create the AS statement
			return implode(' AS ', $table);
		}
		return '`'.str_replace('.', '`.`', $table).'`';
	}

	/**
	 * Escapes a column name for a query.
	 *
	 * @param   string  string to escape
	 * @return  string
	 */
	public function escape_column($column)
	{
		if (!$this->config['escape'])
			return $column;

		if (strtolower($column) == 'count(*)' OR $column == '*')
			return $column;

		// This matches any modifiers we support to SELECT.
		if ( ! preg_match('/\b(?:rand|all|distinct(?:row)?|high_priority|sql_(?:small_result|b(?:ig_result|uffer_result)|no_cache|ca(?:che|lc_found_rows)))\s/i', $column))
		{
			if (stripos($column, ' AS ') !== FALSE)
			{
				// Force 'AS' to uppercase
				$column = str_ireplace(' AS ', ' AS ', $column);

				// Runs escape_column on both sides of an AS statement
				$column = array_map(array($this, __FUNCTION__), explode(' AS ', $column));

				// Re-create the AS statement
				return implode(' AS ', $column);
			}

			return preg_replace('/[^.*]+/', '`$0`', $column);
		}

		$parts = explode(' ', $column);
		$column = '';

		for ($i = 0, $c = count($parts); $i < $c; $i++)
		{
			// The column is always last
			if ($i == ($c - 1))
			{
				$column .= preg_replace('/[^.*]+/', '`$0`', $parts[$i]);
			}
			else // otherwise, it's a modifier
			{
				$column .= $parts[$i].' ';
			}
		}
		return $column;
	}

	/**
	 * Returns table prefix of current configuration.
	 *
	 * @return  string
	 */
	public function table_prefix()
	{
		return $this->config['table_prefix'];
	}

	
	/**
	 * Clears the query cache.
	 *
	 * @param   string|TRUE  clear cache by SQL statement or TRUE for last query
	 * @return  object       This Database object.
	 */
	public function clear_cache($sql = NULL)
	{
		if (empty($sql))
		{
			self::$query_cache = array();
		}
		else
		{
			unset(self::$query_cache[$this->query_hash($sql)]);
		}

		Log::add('debug', 'Database cache cleared: '.get_class($this));
	}
} // End Database Class



/**
 * MySQL result.
 */
class Mysql_Result implements  ArrayAccess, Iterator, Countable {

	/**
	 * Result resource
	 */
	protected $result = NULL;

	/**
	 * Total rows
	 */
	protected $total_rows  = FALSE;

	/**
	 * Current row
	 */
	protected $current_row = FALSE;

	/**
	 * Last insterted ID
	 */
	protected $insert_id = FALSE;

	/**
	 * Fetch type
	 */
	protected $fetch_type  = 'mysql_fetch_array';

	/**
	 * Return type
	 */
	protected $return_type = MYSQL_ASSOC;

	/**
	 * Sets up the result variables.
	 *
	 * @param  resource  query result
	 * @param  resource  database link
	 * @param  boolean   return objects or arrays
	 * @param  string    SQL query that was run
	 */
	public function __construct($result, $link, $object = TRUE, $sql)
	{

		$this->result = $result;

		// If the query is a resource, it was a SELECT, SHOW, DESCRIBE, EXPLAIN query
		if (is_resource($result))
		{
			$this->current_row = 0;
			$this->total_rows  = mysql_num_rows($this->result);
			$this->fetch_type = ($object === TRUE) ? 'mysql_fetch_object' : 'mysql_fetch_array';
		}
		elseif (is_bool($result))
		{
			if ($result == FALSE)
			{
				// SQL error
				throw new Error_Exception('SQL error:'.mysql_error().'<pre style="padding:10px;">'.htmlspecialchars($sql).'</pre>');
			}
			else
			{
				// Its an DELETE, INSERT, REPLACE, or UPDATE query
				$this->insert_id  = mysql_insert_id($link);
				$this->total_rows = mysql_affected_rows($link);
			}
		}

		// Set result type
		$this->result($object);
	}

	/**
	 * Destruct, the cleanup crew!
	 */
	public function __destruct()
	{
		if (is_resource($this->result))
		{
			mysql_free_result($this->result);
		}
	}

	public function result($object = TRUE, $type = MYSQL_ASSOC)
	{
		$this->fetch_type = ((bool) $object) ? 'mysql_fetch_object' : 'mysql_fetch_array';

		// This check has to be outside the previous statement, because we do not
		// know the state of fetch_type when $object = NULL
		// NOTE - The class set by $type must be defined before fetching the result,
		// autoloading is disabled to save a lot of stupid overhead.
		if ($this->fetch_type == 'mysql_fetch_object' AND $object === TRUE)
		{
//			$this->return_type = (is_string($type) AND Kohana::auto_load($type)) ? $type : 'stdClass';
		}
		else
		{
			$this->return_type = $type;
		}

		return $this;
	}

	public function as_array($object = NULL, $type = MYSQL_ASSOC)
	{
		return $this->result_array($object, $type);
	}

	/**
	 * 返回查询的结果集，用第一列作为索引
	 *
	 * @return array
	 */
	public function result_assoc () {
		$rows = array();
		if (mysql_num_rows($this->result))
		{
			// Reset the pointer location to make sure things work properly
			mysql_data_seek($this->result, 0);
			while (($row = mysql_fetch_assoc($this->result)) !== FALSE)
			{
				$tmp = array_values(array_slice($row, 0, 1));
				$rows[$tmp[0]] = $row;
			}
		}

		return isset($rows) ? $rows : array();
	}
	
	public function result_array($object = NULL, $type = MYSQL_ASSOC)
	{

		$rows = array();

		if (is_string($object))
		{
			$fetch = $object;
		}
		elseif (is_bool($object))
		{
			if ($object === TRUE)
			{
				$fetch = 'mysql_fetch_object';

				$type = 'stdClass';
			}
			else
			{
				$fetch = 'mysql_fetch_array';
			}
		}
		else
		{
			// Use the default config values
			$fetch = $this->fetch_type;

			if ($fetch == 'mysql_fetch_object')
			{
				$type = (is_string($this->return_type) AND Myqee::auto_load($this->return_type)) ? $this->return_type : 'stdClass';
			}
		}

		if (mysql_num_rows($this->result))
		{
			// Reset the pointer location to make sure things work properly
			mysql_data_seek($this->result, 0);
			while ($row = $fetch($this->result, $type))
			{

				$rows[] = $row;
			}
		}

		return isset($rows) ? $rows : array();
	}

	public function insert_id()
	{
		return $this->insert_id;
	}

	public function list_fields()
	{
		$field_names = array();
		while ($field = mysql_fetch_field($this->result))
		{
			$field_names[] = $field->name;
		}

		return $field_names;
	}
	// End Interface


	// Interface: Countable

	/**
	 * Counts the number of rows in the result set.
	 *
	 * @return  integer
	 */
	public function count()
	{
		return $this->total_rows;
	}
	// End Interface


	// Interface: ArrayAccess
	/**
	 * Determines if the requested offset of the result set exists.
	 *
	 * @param   integer  offset id
	 * @return  boolean
	 */
	public function offsetExists($offset)
	{
		if ($this->total_rows > 0)
		{
			$min = 0;
			$max = $this->total_rows - 1;

			return ($offset < $min OR $offset > $max) ? FALSE : TRUE;
		}

		return FALSE;
	}

	/**
	 * Retreives the requested query result offset.
	 *
	 * @param   integer  offset id
	 * @return  mixed
	 */
	public function offsetGet($offset)
	{
		// Check to see if the requested offset exists.
		if ( ! $this->offsetExists($offset))
			return FALSE;

		// Go to the offset
		mysql_data_seek($this->result, $offset);

		// Return the row
		$fetch = $this->fetch_type;
		return $fetch($this->result);
	}

	/**
	 * Sets the offset with the provided value. Since you can't modify query result sets, this function just throws an exception.
	 *
	 * @param   integer  offset id
	 * @param   integer  value
	 * @throws  Kohana_Database_Exception
	 */
	public function offsetSet($offset, $value)
	{
		throw new Error_Exception('database.result_read_only');
	}

	/**
	 * Unsets the offset. Since you can't modify query result sets, this function just throws an exception.
	 *
	 * @param   integer  offset id
	 * @throws  Kohana_Database_Exception
	 */
	public function offsetUnset($offset)
	{
//		throw new Kohana_Database_Exception('database.result_read_only');
	}
	// End Interface

	// Interface: Iterator
	/**
	 * Retrieves the current result set row.
	 *
	 * @return  mixed
	 */
	public function current()
	{
		return $this->offsetGet($this->current_row);
	}

	/**
	 * Retreives the current row id.
	 *
	 * @return  integer
	 */
	public function key()
	{
		return $this->current_row;
	}

	/**
	 * Moves the result pointer ahead one step.
	 *
	 * @return  integer
	 */
	public function next()
	{
		return ++$this->current_row;
	}

	/**
	 * Moves the result pointer back one step.
	 *
	 * @return  integer
	 */
	public function prev()
	{
		return --$this->current_row;
	}

	/**
	 * Moves the result pointer to the beginning of the result set.
	 *
	 * @return  integer
	 */
	public function rewind()
	{
		return $this->current_row = 0;
	}

	/**
	 * Determines if the current result pointer is valid.
	 *
	 * @return  boolean
	 */
	public function valid()
	{
		return $this->offsetExists($this->current_row);
	}
	// End Interface
} // End Mysql_Result Class
