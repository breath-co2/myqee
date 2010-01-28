<?php defined('MYQEEPATH') or die('No direct script access.');

/**
 * Myqee Core.
 *
 * $Id: orm.php 75 2010-01-18 04:17:23Z rovewang $
 *
 * @package     MyQEE Core
 * @subpackage	Library
 * @author      Myqee Team
 * @copyright   (c) 2008-2010 Myqee Team
 * @license     http://www.myqee.com/license.html
 * @link		http://www.myqee.com/
 * @since		Version 1.0
 */
class ORM_Core extends Query_bulider {
	protected $_database = 'default';
	protected $_dbtable;
	protected $_orm_name;
	
	public static $DB;
	
	protected static $instances;
	public static function instance($name){
		if (!isset(ORM::$instances)){
			ORM::$instances[$name] = new ORM($name);
		}
		
		return ORM::$instances[$name];
	}
	/**
	 * 数据库对象
	 * @param string $dbtable 数据表
	 * @param string $database 库
	 */
	public function __construct($name){
		$this->_orm_name = $orm_name = $name.'_ORM';
		if (!Myqee::auto_load($orm_name)){
			throw new Error_Exception('new orm dbtable error');
		}
		
		$orm = new $orm_name;
		$dbname = $orm -> get_dbname();
		$database = $orm -> get_database();
		
		unset($orm);
		
		if (!$dbname){
			throw new Error_Exception($orm_name.' 配置错误，缺少表名称');
		}
		if ($database)
			$this -> _database = $database;
		
		$this -> _dbtable = $dbname;
		
		parent::__construct($this->_database);
	}
	
	/**
	 * 
	 * @param string/array $key
	 * @param string $value
	 * @param boolean $quote
	 * @return Mysql_Result
	 */
	public function find($key = null, $value = null, $quote = TRUE){
		if ($key)
			$this -> where($key, $value , $quote );
		
		$this -> _settable();
		$sql = $this -> compile();
		
		$obj = Database::instance($this->_database) -> query($sql) -> result_array(true,$this->_orm_name);
		
		return $obj;
	}
	
	public function join($table, $key = NULL, $value = NULL, $type = ''){
		if (is_string($table) && $key===null && isset($this->_join[$table]) ){
			$join = $this->_join[$table];
			$this -> join($join['tables'],$join['key'],$join['value'],$join['type']);
		}else{
			parent::join($table, $key , $value ,$type );
		}
		return $this;
	}
	
	public function find_join($join,$where=null){
		if (isset($this->_join[$join])){
			$join = $this->_join[$join];
			$this -> join($join['tables'],$join['key'],$join['value'],$join['type']);
			return $this -> find();
		}
		return false;
	}
	
	public function result(){
		$this -> _settable();
		$sql = $this -> compile();
		$obj = Database::instance($this->_database) -> query($sql) -> result(true,$this->_orm_name);
		return $obj;
	}
	
	public function query($sql){
		return Database::instance($this->_database) -> query($sql);
	}
	
	protected function _settable(){
		if (!$this->from){
			$this->from($this->_dbtable);
		}
		if (!$this->select){
			$this->select[] = '*';
		}
		$select = $this->select;
		foreach ($select as $k=>$item){
			if (strpos($item,'*')!==false){
				echo $item."<Br>";
				unset($this->select[$k]);
//				$this->select[] = 
			}
		}
	}
}



abstract class ORM_DB{
	/**
	 * @var string 所属数据库
	 */
	protected $_database = 'default';
	/**
	 * @var string 数据表
	 */
	protected $_dbname = '';
	
	protected $_data = array();
	protected $_fdata = array();
	protected $_field = array();
	protected $_fieldlist = array();
	
	public function __construct(){
		if (!$this->_data){
			return false;
		}
		
		if ( $id = $this->_fdata[$this->_id_field] ){
			ORM::$DB[get_class($this)][$id] = & $this;
		}
	}
	
	public function get_database(){
		return $this -> $_database;
	}
	public function get_dbname(){
		return $this -> _dbname;
	}
	
	public function __get($key){
		if ( isset($this -> _data[$key]) ){
			return $this -> _data[$key];
		}
		if (isset($this->_field_join[$key]) && is_array($this->_field_join[$key])){
			//获取JOIN对象
			//$this -> join($this->_field_join[$key]);
		}
		return null;
	}
	
	public function __set($key,$value){
		$this -> _data[] = $key.'___'.$value;
//		$k = array_search($key,$this -> _field);
//		if ( $k ){
//			$this -> _data[$k] = $value;
//			$this -> _fdata[$key] =& $this -> _data[$k];
//		}
	}
	
	public function delete(){
		if (!$this->_dbtable){
			return false;
		}
		return Database::instance($this->_database) -> delete($this->_dbtable,array($this->_id_field =>$this -> _data[$this->_id_field] )) -> count();
	}
	
	public function getArray(){
		$data = array();
		foreach ($this -> _data as $key=>$item){
			if (is_object($item)){
				$data[$key] = $item -> getArray();
			}else{
				$data[$key] =& $item;
			}
		}
		return $data;
	}
	
	protected static $database = 'ddd';
}
