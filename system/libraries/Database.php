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
class Database_Core extends Query_bulider {
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
	public $debug = array();
	
	protected static $instances = array();
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
	 * @return  Mysql_Result
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

//		Log::add('debug', 'Database cache cleared: '.get_class($this));
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
	protected $sql;

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
	 * Returns the SQL used to fetch the result.
	 *
	 * @return  string
	 */
	public function sql()
	{
		return $this->sql;
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
	
	public function result_orm($type = MYSQL_ASSOC){
		$this -> result(true,$type);
		foreach ($this as $item){
			
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
			$this->return_type = (is_string($type) AND Myqee::auto_load($type)) ? $type : 'stdClass';
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

//				$type = 'stdClass';
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

			return ! ($offset < $min OR $offset > $max);
		}

		return FALSE;
	}
	
	public function seek($offset)
	{
		if ($this->offsetExists($offset) AND mysql_data_seek($this->result, $offset))
		{
			// Set the current row to the offset
			$this->current_row = $offset;

			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Retreives the requested query result offset.
	 *
	 * @param   integer  offset id
	 * @return  mixed
	 */
	public function offsetGet($offset)
	{
		if ( ! $this->seek($offset))
			return FALSE;
			
		return call_user_func($this->fetch_type, $this->result, $this->return_type);
	}

	/**
	 * Sets the offset with the provided value. Since you can't modify query result sets, this function just throws an exception.
	 *
	 * @param   integer  offset id
	 * @param   integer  value
	 * @throws  Error_Exception
	 */
	public function offsetSet($offset, $value)
	{
		throw new Error_Exception('database.result_read_only');
	}

	/**
	 * Unsets the offset. Since you can't modify query result sets, this function just throws an exception.
	 *
	 * @param   integer  offset id
	 * @throws  Error_Exception
	 */
	public function offsetUnset($offset)
	{
		throw new Error_Exception('database.result_read_only');
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
