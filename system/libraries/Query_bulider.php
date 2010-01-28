<?php
/**
 * Myqee Core.
 *
 * $Id: Query_bulider 75 2010-01-18 04:17:23Z rovewang $
 *
 * @package     MyQEE Core
 * @subpackage	Library
 * @author      Myqee Team
 * @copyright   (c) 2008-2010 Myqee Team
 * @license     http://www.myqee.com/license.html
 * @link		http://www.myqee.com/
 * @since		Version 1.0
 */
abstract class Query_bulider_Core {
	protected $config = array
	(
		'table_prefix'  => '',
		'escape'        => TRUE,
	);

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
	
	public function __construct($config){
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
	 * Selects the column names for a database query.
	 *
	 * @param   string  string or array of column names to select
	 * @return  Query_bulider_Core
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
					// Only prepend with table prefix if table name is specified
					$val = (strpos($matches[1], '.') !== FALSE) ? $this->config['table_prefix'].$matches[1] : $matches[1];

					$this->distinct = TRUE;
				}
				else
				{
					$val = (strpos($val, '.') !== FALSE) ? $this->config['table_prefix'].$val : $val;
				}

				$val = $this->_escape_column($val);
			}

			$this->select[] = $val;
		}

		return $this;
	}

	/**
	 * Selects the from table(s) for a database query.
	 *
	 * @param   string  string or array of tables to select
	 * @return  Query_bulider_Core
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
	 * @return  Database_Core
	 */
	public function join($table, $key, $value = NULL, $type = '')
	{
		$join = array();

		if ( ! empty($type))
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

			if (is_string($value))
			{
				// Only escape if it's a string
				$value = $this->escape_column($this->config['table_prefix'].$value);
			}

			$cond[] = $this->_where($key, $value, 'AND ', count($cond), FALSE);
		}

		if ( ! is_array($this->join))
		{
			$this->join = array();
		}

		if ( ! is_array($table))
		{
			$table = array($table);
		}

		foreach ($table as $t)
		{
			if (is_string($t))
			{
				// TODO: Temporary solution, this should be moved to database driver (AS is checked for twice)
				if (stripos($t, ' AS ') !== FALSE)
				{
					$t = str_ireplace(' AS ', ' AS ', $t);

					list($table, $alias) = explode(' AS ', $t);

					// Attach prefix to both sides of the AS
					$t = $this->config['table_prefix'].$table.' AS '.$this->config['table_prefix'].$alias;
				}
				else
				{
					$t = $this->config['table_prefix'].$t;
				}
			}

			$join['tables'][] = $this->escape_column($t);
		}

		$join['conditions'] = '('.trim(implode(' ', $cond)).')';
		$join['type'] = $type;

		$this->join[] = $join;

		return $this;
	}

	/**
	 * Selects the where(s) for a database query.
	 *
	 * @param   string|array  key name or array of key => value pairs
	 * @param   string        value to match with key
	 * @param   boolean       disable quoting of WHERE clause
	 * @return  Query_bulider_Core
	 */
	public function where($key, $value = NULL, $quote = TRUE)
	{
		$quote = (func_num_args() < 2 AND ! is_array($key)) ? -1 : $quote;
		if (is_object($key))
		{
			$keys = array((string) $key => '');
		}
		elseif ( ! is_array($key))
		{
			$keys = array($key => $value);
		}
		else
		{
			$keys = $key;
		}

		foreach ($keys as $key => $value)
		{
			$key           = (strpos($key, '.') !== FALSE) ? $this->config['table_prefix'].$key : $key;
			$this->where[] = $this->_where($key, $value, 'AND ', count($this->where), $quote);
		}

		return $this;
	}

	/**
	 * Selects the or where(s) for a database query.
	 *
	 * @param   string|array  key name or array of key => value pairs
	 * @param   string        value to match with key
	 * @param   boolean       disable quoting of WHERE clause
	 * @return  Query_bulider_Core
	 */
	public function orwhere($key, $value = NULL, $quote = TRUE)
	{
		$quote = (func_num_args() < 2 AND ! is_array($key)) ? -1 : $quote;
		$keys  = is_array($key) ? $key : array($key => $value);

		foreach ($keys as $key => $value)
		{
			$key           = (strpos($key, '.') !== FALSE) ? $this->config['table_prefix'].$key : $key;
			$this->where[] = $this->_where($key, $value, 'OR ', count($this->where), $quote);
		}

		return $this;
	}

	/**
	 * Selects the like(s) for a database query.
	 *
	 * @param   string|array  field name or array of field => match pairs
	 * @param   string        like value to match with field
	 * @param   boolean       automatically add starting and ending wildcards
	 * @return  Query_bulider_Core
	 */
	public function like($field, $match = '', $auto = TRUE)
	{
		$fields = is_array($field) ? $field : array($field => $match);

		foreach ($fields as $field => $match)
		{
			$field         = (strpos($field, '.') !== FALSE) ? $this->config['table_prefix'].$field : $field;
			$this->where[] = $this->_like($field, $match, $auto, 'AND ', count($this->where));
		}

		return $this;
	}
	
	protected function _like($field, $match = '', $auto = TRUE, $type = 'AND ', $num_likes)
	{
		$prefix = ($num_likes == 0) ? '' : $type;

		$match = $this->escape_str($match);

		if ($auto === TRUE)
		{
			// Add the start and end quotes
			$match = '%'.$match.'%';
		}

		return $prefix.' '.$this -> _escape_column($field).' LIKE \''.$match . '\'';
	}
	
	/**
	 * Selects the or like(s) for a database query.
	 *
	 * @param   string|array  field name or array of field => match pairs
	 * @param   string        like value to match with field
	 * @param   boolean       automatically add starting and ending wildcards
	 * @return  Query_bulider_Core
	 */
	public function orlike($field, $match = '', $auto = TRUE)
	{
		$fields = is_array($field) ? $field : array($field => $match);

		foreach ($fields as $field => $match)
		{
			$field         = (strpos($field, '.') !== FALSE) ? $this->config['table_prefix'].$field : $field;
			$this->where[] = $this->_like($field, $match, $auto, 'OR ', count($this->where));
		}

		return $this;
	}
	protected function _notlike($field, $match = '', $auto = TRUE, $type = 'AND ', $num_likes)
	{
		$prefix = ($num_likes == 0) ? '' : $type;

		$match = $this->escape_str($match);

		if ($auto === TRUE)
		{
			// Add the start and end quotes
			$match = '%'.$match.'%';
		}

		return $prefix.' '.$this->_escape_column($field).' NOT LIKE \''.$match.'\'';
	}
	/**
	 * Selects the not like(s) for a database query.
	 *
	 * @param   string|array  field name or array of field => match pairs
	 * @param   string        like value to match with field
	 * @param   boolean       automatically add starting and ending wildcards
	 * @return  Query_bulider_Core
	 */
	public function notlike($field, $match = '', $auto = TRUE)
	{
		$fields = is_array($field) ? $field : array($field => $match);

		foreach ($fields as $field => $match)
		{
			$field         = (strpos($field, '.') !== FALSE) ? $this->config['table_prefix'].$field : $field;
			$this->where[] = $this->_notlike($field, $match, $auto, 'AND ', count($this->where));
		}

		return $this;
	}

	/**
	 * Selects the or not like(s) for a database query.
	 *
	 * @param   string|array  field name or array of field => match pairs
	 * @param   string        like value to match with field
	 * @return  Query_bulider_Core
	 */
	public function ornotlike($field, $match = '', $auto = TRUE)
	{
		$fields = is_array($field) ? $field : array($field => $match);

		foreach ($fields as $field => $match)
		{
			$field         = (strpos($field, '.') !== FALSE) ? $this->config['table_prefix'].$field : $field;
			$this->where[] = $this->_notlike($field, $match, $auto, 'OR ', count($this->where));
		}

		return $this;
	}
	
	protected function _regex($field, $match = '', $type = 'AND ', $num_regexs)
	{
		$prefix = ($num_regexs == 0) ? '' : $type;

		return $prefix.' '.$this->_escape_column($field).' REGEXP \''.$this->escape_str($match).'\'';
	}
	/**
	 * Selects the like(s) for a database query.
	 *
	 * @param   string|array  field name or array of field => match pairs
	 * @param   string        like value to match with field
	 * @return  Query_bulider_Core
	 */
	public function regex($field, $match = '')
	{
		$fields = is_array($field) ? $field : array($field => $match);

		foreach ($fields as $field => $match)
		{
			$field         = (strpos($field, '.') !== FALSE) ? $this->config['table_prefix'].$field : $field;
			$this->where[] = $this -> _regex($field, $match, 'AND ', count($this->where));
		}

		return $this;
	}

	/**
	 * Selects the or like(s) for a database query.
	 *
	 * @param   string|array  field name or array of field => match pairs
	 * @param   string        like value to match with field
	 * @return  Query_bulider_Core
	 */
	public function orregex($field, $match = '')
	{
		$fields = is_array($field) ? $field : array($field => $match);

		foreach ($fields as $field => $match)
		{
			$field         = (strpos($field, '.') !== FALSE) ? $this->config['table_prefix'].$field : $field;
			$this->where[] = $this -> _regex($field, $match, 'OR ', count($this->where));
		}

		return $this;
	}
	
	protected function _notregex($field, $match, $type, $num_regexs)
	{
		$prefix = $num_regexs == 0 ? '' : $type;

		return $prefix.' '.$this->_escape_column($field).' NOT REGEXP \''.$this->escape_str($match) . '\'';
	}
	/**
	 * Selects the not regex(s) for a database query.
	 *
	 * @param   string|array  field name or array of field => match pairs
	 * @param   string        regex value to match with field
	 * @return  Query_bulider_Core
	 */
	public function notregex($field, $match = '')
	{
		$fields = is_array($field) ? $field : array($field => $match);

		foreach ($fields as $field => $match)
		{
			$field         = (strpos($field, '.') !== FALSE) ? $this->config['table_prefix'].$field : $field;
			$this->where[] = $this->_notregex($field, $match, 'AND ', count($this->where));
		}

		return $this;
	}

	/**
	 * Selects the or not regex(s) for a database query.
	 *
	 * @param   string|array  field name or array of field => match pairs
	 * @param   string        regex value to match with field
	 * @return  Query_bulider_Core
	 */
	public function ornotregex($field, $match = '')
	{
		$fields = is_array($field) ? $field : array($field => $match);

		foreach ($fields as $field => $match)
		{
			$field         = (strpos($field, '.') !== FALSE) ? $this->config['table_prefix'].$field : $field;
			$this->where[] = $this->_notregex($field, $match, 'OR ', count($this->where));
		}

		return $this;
	}

	/**
	 * Chooses the column to group by in a select query.
	 *
	 * @param   string  column name to group by
	 * @return  
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
				// Add the table prefix if we are using table.column names
				if(strpos($val, '.'))
				{
					$val = $this->config['table_prefix'].$val;
				}

				$this->groupby[] = $this->_escape_column($val);
			}
		}

		return $this;
	}
	
	protected function _where($key, $value, $type, $num_wheres, $quote)
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
				if ( ! $this->_has_operator($key))
				{
					$key .= ' IS';
				}

				$value = ' NULL';
			}
			elseif (is_bool($value))
			{
				if ( ! $this->_has_operator($key))
				{
					$key .= ' =';
				}

				$value = ($value == TRUE) ? ' 1' : ' 0';
			}
			else
			{
				if ( ! $this->_has_operator($key))
				{
					$key = $this->_escape_column($key).' =';
				}
				else
				{
					preg_match('/^(.+?)([<>!=]+|\bIS(?:\s+NULL))\s*$/i', $key, $matches);
					if (isset($matches[1]) AND isset($matches[2]))
					{
						$key = $this->_escape_column(trim($matches[1])).' '.trim($matches[2]);
					}
				}

				$value = ' '.(($quote == TRUE) ? $this->escape($value) : $value);
			}
		}

		return $prefix.$key.$value;
	}

	protected function _has_operator($str)
	{
		return (bool) preg_match('/[<>!=]|\sIS(?:\s+NOT\s+)?\b/i', trim($str));
	}
	/**
	 * Selects the having(s) for a database query.
	 *
	 * @param   string|array  key name or array of key => value pairs
	 * @param   string        value to match with key
	 * @param   boolean       disable quoting of WHERE clause
	 * @return  Query_bulider_Core
	 */
	public function having($key, $value = '', $quote = TRUE)
	{
		$this->having[] = $this->_where($key, $value, 'AND', count($this->having), TRUE);
		return $this;
	}

	/**
	 * Selects the or having(s) for a database query.
	 *
	 * @param   string|array  key name or array of key => value pairs
	 * @param   string        value to match with key
	 * @param   boolean       disable quoting of WHERE clause
	 * @return  Query_bulider_Core
	 */
	public function orhaving($key, $value = '', $quote = TRUE)
	{
		$this->having[] = $this->_where($key, $value, 'OR', count($this->having), TRUE);
		return $this;
	}

	/**
	 * Chooses which column(s) to order the select query by.
	 *
	 * @param   string|array  column(s) to order on, can be an array, single column, or comma seperated list of columns
	 * @param   string        direction of the order
	 * @return  Query_bulider_Core
	 */
	public function orderby($orderby, $direction = NULL)
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

			$this->orderby[] = $this->_escape_column($column).' '.$direction;
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

		if ($offset !== NULL OR ! is_int($this->offset))
		{
			$this->offset($offset);
		}

		return $this;
	}

	protected function _limit($limit, $offset = FALSE)
	{
		return 'LIMIT '.$offset.', '.$limit;
	}
	
	


	/**
	 * Compiles the select statement based on the other functions called and runs the query.
	 *
	 * @param   string  table name
	 * @param   string  limit clause
	 * @param   string  offset clause
	 * @return  Mysql_Result  Database_Result
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

		$sql = $this->_compile_select(get_object_vars($this));
		
		$this->reset_select();

		$result = $this->query($sql);

		$this->last_query = $sql;

		return $result;
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

		$sql = $this->_compile_select(get_object_vars($this));

		$this->reset_select();
		
		$result = $this->query($sql);
		
		return $result;
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
	 * @return  Query_bulider_Core
	 */
	public function set($key, $value = '')
	{
		if ( ! is_array($key))
		{
			$key = array($key => $value);
		}

		foreach ($key as $k => $v)
		{
			// Add a table prefix if the column includes the table.
			if (strpos($k, '.'))
				$k = $this->config['table_prefix'].$k;

			$this->set[$k] = $this->escape($v);
		}

		return $this;
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
		$this ->_settable();
		print_r($this->select);
		print_r($this->join);
		if ($table != '')
		{
			$this->from($table);
		}

		if ( ! is_null($limit))
		{
			$this->limit($limit, $offset);
		}

		$sql = $this->_compile_select(get_object_vars($this));

		$this->reset_select();

		return $sql;
	}
	
	protected function _compile_select($database)
	{
		$sql = ($database['distinct'] == TRUE) ? 'SELECT DISTINCT ' : 'SELECT ';
		$sql .= (count($database['select']) > 0) ? implode(', ', $database['select']) : '*';

		if (count($database['from']) > 0)
		{
			// Escape the tables
			$froms = array();
			foreach ($database['from'] as $from)
			{
				$froms[] = $this->_escape_column($from);
			}
			$sql .= "\nFROM (";
			$sql .= implode(', ', $froms).")";
		}

		if (count($database['join']) > 0)
		{
			foreach($database['join'] AS $join)
			{
				$sql .= "\n".$join['type'].'JOIN '.implode(', ', $join['tables']).' ON '.$join['conditions'];
			}
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
			$sql .= $this->_limit($database['limit'], $database['offset']);
		}

		return $sql;
	}

	/**
	 * Adds an "IN" condition to the where clause
	 *
	 * @param   string  Name of the column being examined
	 * @param   mixed   An array or string to match against
	 * @param   bool    Generate a NOT IN clause instead
	 * @return  
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

		$where = $this->_escape_column(((strpos($field,'.') !== FALSE) ? $this->config['table_prefix'] : ''). $field).' '.($not === TRUE ? 'NOT ' : '').'IN ('.$values.')';
		$this->where[] = $this->_where($where, '', 'AND ', count($this->where), -1);
		
		return $this;
	}

	/**
	 * Adds a "NOT IN" condition to the where clause
	 *
	 * @param   string  Name of the column being examined
	 * @param   mixed   An array or string to match against
	 * @return  
	 */
	public function notin($field, $values)
	{
		return $this->in($field, $values, TRUE);
	}
	
	protected function _d_merge($table, $keys, $values)
	{
		// Escape the column names
		foreach ($keys as $key => $value)
		{
			$keys[$key] = $this->_escape_column($value);
		}
		return 'REPLACE INTO '.$this->escape_table($table).' ('.implode(', ', $keys).') VALUES ('.implode(', ', $values).')';
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
		return addslashes($str);
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
	
	public function escape_column($table){
		return $this -> _escape_column($table);
	}
	
	protected function _escape_column($column)
	{
		if (!$this->config['escape'])
			return $column;

		if ($column == '*')
			return $column;

		// This matches any functions we support to SELECT.
		if ( preg_match('/(avg|count|sum|max|min)\(\s*(.*)\s*\)(\s*as\s*(.+)?)?/i', $column, $matches))
		{
			if ( count($matches) == 3)
			{
				return $matches[1].'('.$this->_escape_column($matches[2]).')';
			}
			else if ( count($matches) == 5)
			{
				return $matches[1].'('.$this->_escape_column($matches[2]).') AS '.$this->_escape_column($matches[2]);
			}
		}
		
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
	 * @return  Mysql_Result
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

		$query = $this->select('COUNT(*) AS '.$this->escape_column('records_found'))->get()->result(TRUE);

		return (int) $query->current()->records_found;
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
	 * Returns table prefix of current configuration.
	 *
	 * @return  string
	 */
	public function table_prefix()
	{
		return $this->config['table_prefix'];
	}
	
	/**
	 * @return Mysql_Result
	 */
	function find($key = null, $value = null, $quote = TRUE){}
	
	function connect(){
		throw new Error_Exception('程序编写错误，缺少连接器，请在扩展程序中编写连接器代码');
	}
}