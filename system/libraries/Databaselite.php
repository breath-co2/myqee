<?php defined('MYQEEPATH') or die('No direct script access.');

class Databaselite_Core {
	protected $querynum = 0;
	protected $driver;
	protected $link;
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


	/**
	 * Returns a singleton instance of databasebase.
	 *
	 * @param   mixed   configuration array or DSN
	 * @return  object
	 */
	public static function instance($config = array())
	{
		static $instance;

		// Create the instance if it does not exist
		($instance === NULL) and $instance = new Databaselite($config);

		return $instance;
	}
	public function __destruct(){
		is_resource($this->link) and mysql_close($this->link);
	}

	public function __construct($config = array()){
		$this -> db_config = $config;
		$this -> connect();
	}
	public function connect(){
		// Check if link already exists
		if (is_resource($this->link))
			return $this->link;

		// Import the connect variables
		extract($this->db_config['connection']);

	//function connect($dbhost, $dbuser, $dbpw, $dbname = '', $dbcharset = '', $pconnect = 0, $tablepre='', $time = 0) {
		if (is_array($config) && count($config) > 0){
			if ( ! array_key_exists('connection', $config))
			{
				$config = array('connection' => $config);
			}
		}else{
			// Load the default group
			$config = Myqee::config('database.default');
		}
		$this->config = array_merge($this->config, $config);

		if($config['persistent']) {
			if(!$this->link = mysql_pconnect($config['connection']['dbhost'], $config['connection']['user'], $config['connection']['pass'])) {
				$this->halt('Can not connect to MySQL server');
			}
		} else {
			if(!$this->link = mysql_connect($config['connection']['dbhost'], $config['connection']['user'], $config['connection']['pass'] , 1)) {
				$this->halt('Can not connect to MySQL server');
			}
		}

		if($this->version() > '4.1') {

			if($config['character_set']) {
				mysql_query("SET character_set_connection=".$config['character_set'].", character_set_results=".$config['character_set'].", character_set_client=binary", $this->link);
			}

			if($this->version() > '5.0.1') {
				mysql_query("SET sql_mode=''", $this->link);
			}
		}
		if($config['connection']['database']) {
			mysql_select_db($config['connection']['database'], $this->link);
		}

	}

	public function fetch_array($query, $result_type = MYSQL_ASSOC) {
		return mysql_fetch_array($query, $result_type);
	}

	public function result_first($sql, &$data) {
		$query = $this->query($sql);
		$data = $this->result($query, 0);
	}

	public function fetch_first($sql, &$arr) {
		$query = $this->query($sql);
		$arr = $this->fetch_array($query);
	}

	public function fetch_all($sql, &$arr) {
		$query = $this->query($sql);
		while($data = $this->fetch_array($query)) {
			$arr[] = $data;
		}
	}

	/*
	function cache_gc() {
		$this->query("DELETE FROM {$this->tablepre}sqlcaches WHERE expiry<$this->time");
	}
	*/

	public function query($sql, $type = '', $cachetime = FALSE) {
		$func = $type == 'UNBUFFERED' && @function_exists('mysql_unbuffered_query') ? 'mysql_unbuffered_query' : 'mysql_query';
		if(!($query = $func($sql, $this->link)) && $type != 'SILENT') {
			$this->halt('MySQL Query Error', $sql);
		}
		$this->querynum++;
		$this->histories[] = $sql;
		return $query;
	}

	public function affected_rows() {
		return mysql_affected_rows($this->link);
	}

	public function error() {
		return (($this->link) ? mysql_error($this->link) : mysql_error());
	}

	public function errno() {
		return intval(($this->link) ? mysql_errno($this->link) : mysql_errno());
	}

	public function result($query, $row) {
		$query = @mysql_result($query, $row);
		return $query;
	}

	public function num_rows($query) {
		$query = mysql_num_rows($query);
		return $query;
	}

	public function num_fields($query) {
		return mysql_num_fields($query);
	}

	public function free_result($query) {
		return mysql_free_result($query);
	}

	public function insert_id() {
		return ($id = mysql_insert_id($this->link)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
	}

	public function fetch_row($query) {
		$query = mysql_fetch_row($query);
		return $query;
	}

	public function fetch_fields($query) {
		return mysql_fetch_field($query);
	}

	public function version() {
		return mysql_get_server_info($this->link);
	}

	public function close() {
		return mysql_close($this->link);
	}

	public function halt($message = '', $sql = '') {
		exit($message.'<br /><br />'.$sql.'<br /> '.mysql_error());
	}

	public function table_prefix()
	{
		return $this->config['table_prefix'];
	}

	public function querynum(){
		return $this -> querynum;
	}
}
