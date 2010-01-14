<?php
/**
 * Myqee Core.
 *
 * $Id: myqee.php 65 2010-10-29 23:08:09Z jonwang $
 *
 * @package    MyQEE Core
 * @subpackage	core
 * @author     Myqee Team
 * @copyright  (c) 2008-2010 Myqee Team
 * @license    http://www.myqee.com/license.html
 * @link		http://www.myqee.com/
 * @since		Version 1.0
 */

define('STARTTIME', _getthistime () );
define('MYQEE_VERSION', 'V1.0 RC1');
define('MYQEE_CODENAME', 'jonwang');
define('MYQEE_IS_WIN', DIRECTORY_SEPARATOR === '\\');
define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());


if (MAGIC_QUOTES_GPC){
	function _stripslashes($string) {
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = _stripslashes($val);
			}
		} else {
			$string = stripslashes($string);
		}
		return $string;
	}
	$_GET     = _stripslashes($_GET);
	$_POST    = _stripslashes($_POST);
	$_COOKIE  = _stripslashes($_COOKIE);
	$_REQUEST = _stripslashes($_REQUEST);
}

if (!defined ('ADMINPATH')){
	define('ADMINPATH',false);
}

// Myqee SetUp
Event::add('system.setup',array('myqee_root','setup'));

// Enable 404 pages
Event::add ( 'system.404', array ('Myqee', 'show_404' ) );
// Enable 500 pages
Event::add ( 'system.500', array ('Myqee', 'show_500' ) );

// Shutdown
Event::add ( 'system.shutdown', array ('Myqee', 'internal_cache_save' ) );
Event::add ( 'system.shutdown', array ('Myqee', 'shutdown' ) );


// SetUp
Event::run('system.setup');


/**
 * 获取当前运行的时间
 *
 */
function _getthistime() {
	list ( $usec, $sec ) = explode ( " ", microtime () );
	return (( float ) $usec + ( float ) $sec);
}

/**
 * myqee setup
 * @author jonwang
 *
 */
abstract class myqee_root{
	private static $arguments;
	private static $includepath;
	private static $output = '';
	private static $sub_controller = false;
	
	public static function setup() {
		static $run;
		// This function can only be run once
		if ($run === TRUE)
			return;
			
		$run = TRUE;
		
		header ( 'X-Powered-By: Myqee ' . MYQEE_VERSION );
//		header ( 'Connection: close' );
		
		$myconfig = Myqee::config('core'); //读取配置文件
	
		define ( 'SITE_NAME', $myconfig ['sitename'] );
		define ( 'SITE_DOMAIN', $myconfig ['mysite_domain'] );
		define ( 'SITE_URL', $myconfig ['mysite_url'] );
		

		Myqee::$is_cli = (PHP_SAPI === 'cli');
		Myqee::$charset = $myconfig['charset'];
		
		
		if ($myconfig['internal_cache']>0){
			Myqee::internal_cache_load('find_file_paths');
		}
		
		if (isset($_SERVER['PATH_INFO']) AND $_SERVER['PATH_INFO'])
		{
			$pathinfo = $_SERVER ["PATH_INFO"];
		}
		elseif (isset($_SERVER['ORIG_PATH_INFO']) AND $_SERVER['ORIG_PATH_INFO'])
		{
			$pathinfo = $_SERVER["ORIG_PATH_INFO"];
		}
		elseif (isset($_SERVER['PHP_SELF']) AND $_SERVER['PHP_SELF'])
		{
			$pathinfo = $_SERVER ["PHP_SELF"];
		}
		if (isset($_SERVER['SCRIPT_NAME']) AND $_SERVER['SCRIPT_NAME'])
		{
			// Clean up PATH_INFO fallbacks
			// PATH_INFO may be formatted for ISAPI instead of CGI on IIS
			if (strncmp($pathinfo, $_SERVER['SCRIPT_NAME'], strlen($_SERVER['SCRIPT_NAME'])) === 0)
			{
				// Remove the front controller from the current uri
				$pathinfo = (string) substr($pathinfo, strlen($_SERVER['SCRIPT_NAME']));
			}
		}
		$pathinfo = trim($pathinfo,'/');
		
		if (!defined('ADMINPATH') || ADMINPATH==false) {
			if ($myconfig ['useroutes']) {
				//路由处理
				if (count ( $myconfig ['routes'] ['key'] ) > 0) {
					if ($myconfig ['saferoutes']) {
						$pathinfo_new = @preg_replace ( $myconfig['routes']['key'], $myconfig['routes']['value'], $pathinfo );
					} else {
						$pathinfo_new = preg_replace ( $myconfig['routes']['key'], $myconfig['routes']['value'], $pathinfo );
					}
					if ($pathinfo_new) {
						$pathinfo = ltrim ( $pathinfo_new );
						if (substr ( $pathinfo, 0, 7 ) == 'http://' || substr ( $pathinfo, 0, 8 ) == 'https://') {
							header ( 'location:' . $pathinfo );
							echo '<a href="' . str_replace ( '<', '&lt;', str_replace ( '"', '', $pathinfo ) ) . '">' . str_replace ( '<', '&lt;', $pathinfo ) . '</a>';
							exit ();
						}
						unset ( $pathinfo_new );
					}
				}
			}
		}
		
		//处理模块
		if (defined('ADMINPATH') && ADMINPATH!=false) {
			
		} elseif (! defined ( 'MY_MODULE_PATH' ) && count ( $myconfig ['modules'] )) {
			foreach ( $myconfig ['modules'] as $modelpath => $item ) {
				if (! $item ['isuse'] == 1)
					continue;
				
				$modulesurllen = strlen ( $item ['url'] );
				$protocol = Myqee::protocol().'://';
				if (substr ( $item ['url'], 0, strlen($protocol) ) == $protocol) {
					$uri = $protocol . $_SERVER ['HTTP_HOST'] . $_SERVER ['REQUEST_URI'];
					if (substr ( $uri, 0, $modulesurllen ) == $item ['url']) {
						//OK
						$is_modules = true;
						$pathinfo = ltrim ( substr ( $uri, $modulesurllen ), '/' );
						list($pathinfo) = explode('?',$pathinfo,2);
						break;
					} else {
						continue;
					}
				} else {
					//$urilen = strlen ( ltrim ( preg_replace ( "/^(http\:\/\/[^\/]+)/", '', $myconfig ['mysite_url'] ), '/' ) );
					if (substr ( $pathinfo, 0, $modulesurllen ) == $item ['url']) {
						//OK
						$is_modules = true;
						$pathinfo = ltrim ( substr ( $pathinfo, $modulesurllen + $urilen ), '/' );
						break;
					} else {
						continue;
					}
				}
			}
			if ($is_modules == true) {
				//SET MODULES
				define ('MY_MODULE_PATH', $modelpath );
				$_SERVER ["PATH_INFO"] = '/'.$pathinfo;
			}
		}
		
		if ($myconfig ['url_suffix']) {
			$pathinfo = preg_replace ( "/{$myconfig['url_suffix']}$/i", '', $pathinfo );
		}
		Myqee::$current_uri = $pathinfo;
		
		self::$arguments = explode ( '/', $pathinfo );
		
		//构造路径
		$includepath = Myqee::include_paths ( TRUE );
		
		//注册自动加载函数
		spl_autoload_register ( array ('Myqee', 'auto_load' ) );
		
		if ($myconfig['display_errors'])
		{
			// Enable the Kohana shutdown handler, which catches E_FATAL errors.
			register_shutdown_function(array('Error_Exception', 'shutdown_handler'));

			// Enable Kohana exception handling, adds stack traces and error source.
			set_exception_handler(array('Error_Exception', 'exception_handler'));

			// Enable Kohana error handling, converts all PHP errors to exceptions.
			set_error_handler(array('Error_Exception', 'error_handler'));
		}else{
			error_reporting(0);
			set_exception_handler(array('Myqee', 'show_error_page'));
			set_error_handler(array('Myqee', 'show_error_page'));
		}
		
		
		
		if (! $includepath || ! is_array ( $includepath )) {
			Event::run('system.404');
		}
		if (defined ( 'ADMINPATH' ) && ADMINPATH!=false) {
			unset ( $includepath ['a'] );
		}
		self::$includepath = $includepath;
		
	
		if ($myconfig['system_class_name'] && preg_match("#[a-z][a-z0-9]+#i",$myconfig['system_class_name']) && ($classlower = strtolower($myconfig['system_class_name']))!='myqee'){
			$file = Myqee::find_file('core',$classlower);
			if ($file){
				include $file;
			}else{
				eval('class '.$myconfig['system_class_name'].' extends Myqee{}');
			}
		}
		
		//加载控制器
		self::_load_controller(FALSE);
		
		Event::run('system.shutdown');
	}

	/**
	 * 执行子控制器
	 *
	 * @param boolean $returnhtml 返回HTML
	 * @param array $path 设定路径
	 * @param boolean $addoldcontrollor 是否将父控制器名称重新加上去，默认否
	 * @param number $arguments_begin 页面参数起始位置，默认0,即控制器后面的所有参赛
	 * @return string 返回HTML
	 */
	public static function & sub_controller( $returnhtml = false , $path = null, $addoldcontrollor=false ,$arguments_begin=0){
		static $run;
		// This function can only be run once
		if ($run === TRUE)
			return;
		$run = TRUE;
		
		if (is_array($path)){
			self::$includepath = $path;
			Myqee::include_paths(true,$path);
		}
		self::$sub_controller = TRUE;
		if ($arguments_begin>0){
			self::$arguments = array_slice(self::$arguments,$arguments_begin);
		}
		if ($addoldcontrollor){
			if (defined('__ERROR_FUNCTION__')){
				array_unshift(self::$arguments,__ERROR_FUNCTION__);
			}else{
				array_unshift(self::$arguments,Myqee::$method_name);
			}
		}
		if (self::_load_controller($returnhtml)===false){
			return false;
		}
		
		if ($returnhtml){
			return self::$output;
		}
	}
	
	/**
	 * 加载控制器
	 *
	 * @param boolean $returnhtml
	 */
	private static function _load_controller($returnhtml = false ){
		$myconfig = Myqee::config('core');
		
		if (!is_array(self::$arguments) || !self::$arguments [0]){
			self::$arguments = array ($myconfig['defaultpage']);
		}
		
		//待搜寻的目录
		$includepath = array_unique(self::$includepath);
		if (!$includepath || !is_array($includepath)){
			if (self::$sub_controller)return false;
			Event::run('system.404');
		}
		$controller_path = '';
		$isLoad = FALSE;
		$foundcontroller = '';
		foreach ( self::$arguments as $item ) {
			if ( !$includepath || count($includepath) == 0)
				break;
			
			$item = preg_replace ( "/[^a-zA-Z0-9_-]/", '', strtolower ( $item ) );
			if ( empty($item) ){
				break;
			}
			
			$found_sondir = false;
			$isfoundfile = false;
			foreach ( $includepath as $k => $mypath ) {
				$tmp_c_path = $mypath . 'controllers' . $controller_path .'/';
				$tmp_c_file = $tmp_c_path . $item . EXT;
			
				if(!is_dir($tmp_c_path)){
					unset($includepath[$k]);
				}elseif (is_dir($tmp_c_path.$item.'/')){
					//文件夹优先
					$found_sondir = true;
				}
				
				if ( $isfoundfile==false && is_file($tmp_c_file) ){
					$isfoundfile = true;
					$foundcontroller = $tmp_c_file;
					$mypath = $k;
					$extincludepath = $includepath;
					$controller_name = $c_name = $item;
					array_shift(self::$arguments);
					Myqee::$controller_path = $controller_path;
				}
				
			}
			if ( $foundcontroller && $found_sondir==false ){
				break;
			}
			$controller_path .= '/' . $item;
		}
		if ( $foundcontroller ){
			if ( include($foundcontroller) ){
				$isLoad = TRUE;
			}
			
			//记录扩展目录，将当前已加载目录排除掉
			unset ( $extincludepath[$mypath] );
		}
		if (!$isLoad){
			if (self::$sub_controller)return false;
			Event::run('system.404');
		}
			
		/*if (self::$sub_controller==TRUE){
			$controller_name .= '_Sub_Controller';
		}else{
			$controller_name .= '_Controller';
		}*/
		$controller_name .= '_Controller';
			
		if (class_exists ( $controller_name, FALSE )) 
		{
			
		}
		elseif (class_exists ( $controller_name .'_Core' ,FALSE)) 
		{
			//控制器扩展
			if ($myconfig['extension_prefix'] && is_array($extincludepath) && count($extincludepath) ){
				$isLoad = FALSE;
				foreach ( $extincludepath as $mypath ) {
					$tmp_c_file = $mypath . 'controllers' . $controller_path .'/'. $myconfig['extension_prefix'].$c_name.EXT;
					if (file_exists ( $tmp_c_file )) {
						include $tmp_c_file;
						break;
					}
				}
			}
			if (!class_exists ( $controller_name  ,FALSE)) {
				eval('class '.$controller_name .' extends ' . $controller_name .'_Core{}');
			}
		}
		else 
		{
			if (self::$sub_controller)return false;
			Event::run('system.404');
		}
		
		Myqee::$controller_name = $controller_name;
		
		Myqee::$controller = new $controller_name();

		Event::run('controller.loaded');		//执行控制器加载完成的对象
		
		//执行控制器
		self::_run_controller( $returnhtml );
	}
	
	/**
	 * 运行控制器
	 *
	 * @param boolean $returnhtml
	 * @return $html
	 */
	private static function _run_controller( $returnhtml = false ){
		if (!is_array(self::$arguments)){
			self::$arguments = array();
		}
		$method_name = array_shift(self::$arguments);
		if($method_name[0]==='_'){
			// Do not allow access to hidden methods
			if (self::$sub_controller)return false;
			Event::run('system.404');
		}
		$arguments = self::$arguments;
		
		$method_name or $method_name = 'index';
		define('METHOD_NAME',$method_name);
		
		if (!method_exists(Myqee::$controller, $method_name)){
			define('__ERROR_FUNCTION__',$method_name);
			
			$method_name = '_default';
			if (!method_exists(Myqee::$controller, $method_name)){
				$method_name = '__call';
				$arguments = array($method_name,$arguments);
				if (!method_exists(Myqee::$controller, $method_name)){
					if (self::$sub_controller)return false;
					Event::run('system.404');
				}
			}
		}
		
		//Method is Public?
		$ispublicmethod = new ReflectionMethod(Myqee::$controller_name,$method_name);
		if ( !$ispublicmethod->isPublic() ){
			if (self::$sub_controller)return false;
			Event::run('system.404');
		}
		unset($ispublicmethod);
		
		Myqee::$method_name = $method_name;
		Myqee::$arguments = self::$arguments;
		
		Myqee::close_buffers(false);
		ob_start();
		
		switch (count($arguments)) {
			case 0:
				Myqee::$controller -> $method_name();
				break;
			case 1:
				Myqee::$controller -> $method_name($arguments[0]);
				break;
			case 2:
				Myqee::$controller -> $method_name($arguments[0], $arguments[1]);
				break;
			case 3:
				Myqee::$controller -> $method_name($arguments[0], $arguments[1], $arguments[2]);
				break;
			case 4:
				Myqee::$controller -> $method_name($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
				break;
			default:
				// Resort to using call_user_func_array for many segments
				call_user_func_array(array(Myqee::$controller,$method_name), $arguments);
				break;
		}
		
		Event::run('controller.shutdown');			//关闭控制器
		
		self::$output = ob_get_clean();
		
		self::output();
		
		if(strpos(strtolower($_SERVER['HTTP_ACCEPT_ENCODING']),'gzip')!==false)
		{
			//开启gzip压缩
			ob_start("ob_gzhandler");
		}
		
		if ($returnhtml){
			return self::$output;
		}else{
			echo self::$output;
		}
	}
	
	public static function output( &$output = NULL ){
		$memory = function_exists('memory_get_usage') ? (memory_get_usage() / 1024 / 1024) : 0;
		if($output===NULL)$output =& self::$output;
		$output = str_replace(
			array
			(
				'{site_url}',
				'{site_name}',
				'{site_domain}',
				'{myqee_version}',
				'{myqee_codename}',
				'{memory_usage}',
				'{included_files}',
				'{execution_time}',
			),
			array
			(
				SITE_URL,
				SITE_NAME,
				SITE_DOMAIN,
				MYQEE_VERSION,
				MYQEE_CODENAME,
				number_format($memory, 2).'MB',
				count(get_included_files()),
				number_format((_getthistime() - STARTTIME),4),
			),
			$output
		);
	}
}

abstract class Controller {
	public $input;
	
	public function __construct() {
		if (Myqee::$instance == NULL) {
			// Set the instance to the first controller loaded
			Myqee::$instance = $this;
		}
	}
	
	public function __call($method, $args) {
		// Default to showing a 404 page
		Event::run ( 'system.404' );
	}
	
	public function _myqee_load_view($myqee_view_filename, $myqee_input_data) {
		if ($myqee_view_filename == '')
			return;
			
		// Buffering on
		ob_start ();
		$ER = error_reporting ( 7 );
		
		// Import the view variables to local namespace
		extract ( $myqee_input_data, EXTR_SKIP );
		
		include $myqee_view_filename;
		
		error_reporting ( $ER );
		// Fetch the output and close the buffer
		return ob_get_clean ();
	}
	
	public function _kohana_load_view($myqee_view_filename, $myqee_input_data) {
		return $this -> _myqee_load_view($myqee_view_filename, $myqee_input_data);
	}
}

abstract class Model {
	protected $db;
	
	/**
	 * Loads or sets the database instance.
	 *
	 * @param   object   Database instance
	 * @return  void
	 */
	public function __construct($database = NULL) {
		if (is_object ( $database ) and ($database instanceof Database)) {
			$this->db =& $database;
		} else {
			$this->db =& Myqee::db ( $database );
		}
	}
}

final class Event {
	
	// Event callbacks
	private static $events = array ();
	// Cache of events that have been run
	private static $has_run = array ();
	// Data that can be processed during events
	public static $data;
	/**
	 * Add a callback to an event queue.
	 *
	 * @param   string   event name
	 * @param   array    http://php.net/callback
	 * @return  boolean
	 */
	public static function add($name, $callback) {
		if (! isset ( self::$events [$name] )) {
			// Create an empty event if it is not yet defined
			self::$events [$name] = array ();
		} elseif (in_array ( $callback, self::$events [$name], TRUE )) {
			// The event already exists
			return FALSE;
		}
		
		// Add the event
		self::$events [$name] [] = $callback;
		
		return TRUE;
	}
	
	/**
	 * Add a callback to an event queue, before a given event.
	 *
	 * @param   string   event name
	 * @param   array    existing event callback
	 * @param   array    event callback
	 * @return  boolean
	 */
	public static function add_before($name, $existing, $callback) {
		if (empty ( self::$events [$name] ) or ($key = array_search ( $existing, self::$events [$name] )) === FALSE) {
			// Just add the event if there are no events
			return self::add ( $name, $callback );
		} else {
			// Insert the event immediately before the existing event
			return self::insert_event ( $name, $key, $callback );
		}
	}
	
	/**
	 * Add a callback to an event queue, after a given event.
	 *
	 * @param   string   event name
	 * @param   array    existing event callback
	 * @param   array    event callback
	 * @return  boolean
	 */
	public static function add_after($name, $existing, $callback) {
		if (empty ( self::$events [$name] ) or ($key = array_search ( $existing, self::$events [$name] )) === FALSE) {
			// Just add the event if there are no events
			return self::add ( $name, $callback );
		} else {
			// Insert the event immediately after the existing event
			return self::insert_event ( $name, $key + 1, $callback );
		}
	}
	
	/**
	 * Inserts a new event at a specfic key location.
	 *
	 * @param   string   event name
	 * @param   integer  key to insert new event at
	 * @param   array    event callback
	 * @return  void
	 */
	private static function insert_event($name, $key, $callback) {
		if (in_array ( $callback, self::$events [$name], TRUE ))
			return FALSE;
			
		// Add the new event at the given key location
		self::$events [$name] = array_merge ( // Events before the key
		array_slice ( self::$events [$name], 0, $key ), // New event callback
		array ($callback ), // Events after the key
		array_slice ( self::$events [$name], $key ) );
		
		return TRUE;
	}
	
	/**
	 * Replaces an event with another event.
	 *
	 * @param   string   event name
	 * @param   array    event to replace
	 * @param   array    new callback
	 * @return  boolean
	 */
	public static function replace($name, $existing, $callback) {
		if (empty ( self::$events [$name] ) or ($key = array_search ( $existing, self::$events [$name], TRUE )) === FALSE)
			return FALSE;
		
		if (! in_array ( $callback, self::$events [$name], TRUE )) {
			// Replace the exisiting event with the new event
			self::$events [$name] [$key] = $callback;
		} else {
			// Remove the existing event from the queue
			unset ( self::$events [$name] [$key] );
			
			// Reset the array so the keys are ordered properly
			self::$events [$name] = array_values ( self::$events [$name] );
		}
		
		return TRUE;
	}
	
	/**
	 * Get all callbacks for an event.
	 *
	 * @param   string  event name
	 * @return  array
	 */
	public static function get($name) {
		return empty ( self::$events [$name] ) ? array () : self::$events [$name];
	}
	
	/**
	 * Clear some or all callbacks from an event.
	 *
	 * @param   string  event name
	 * @param   array   specific callback to remove, FALSE for all callbacks
	 * @return  void
	 */
	public static function clear($name, $callback = FALSE) {
		if ($callback === FALSE) {
			self::$events [$name] = array ();
		} elseif (isset ( self::$events [$name] )) {
			// Loop through each of the event callbacks and compare it to the
			// callback requested for removal. The callback is removed if it
			// matches.
			foreach ( self::$events [$name] as $i => $event_callback ) {
				if ($callback === $event_callback) {
					unset ( self::$events [$name] [$i] );
				}
			}
		}
	}
	
	/**
	 * Execute all of the callbacks attached to an event.
	 *
	 * @param   string   event name
	 * @param   array    data can be processed as Event::$data by the callbacks
	 * @return  void
	 */
	public static function run($name, & $data = NULL) {
		if (! empty ( self::$events [$name] )) {
			// So callbacks can access Event::$data
			self::$data = & $data;
			$callbacks = self::get ( $name );
			
			foreach ( $callbacks as $callback ) {
				call_user_func ( $callback );
			}
			
			// Do this to prevent data from getting 'stuck'
			$clear_data = '';
			self::$data = & $clear_data;
		}
		
		// The event has been run!
		self::$has_run [$name] = $name;
	}
	
	/**
	 * Check if a given event has been run.
	 *
	 * @param   string   event name
	 * @return  boolean
	 */
	public static function has_run($name) {
		return isset ( self::$has_run [$name] );
	}

} // End Event


/**
 * MyQEE 核心函数
 * @author jonwang
 *
 */
abstract class Myqee {
	/**
	 * 
	 * @var object $instance
	 * @return Myqee
	 */
	public static $instance;
	
	//控制器对象
	public static $controller;
	
	public static $controller_name;
	public static $method_name;
	public static $current_uri;
	public static $arguments;
	
	/**
	 * @var  boolean  command line environment?
	 */
	public static $is_cli = FALSE;
	
	public static $charset = 'utf-8';
	
	//配置文件
	protected static $config = array();
	
	//记录栏目数组
	public static $myclass;
	protected static $plus;
	public static $controller_path;
	
	
	protected static $internal_cache;
	protected static $include_paths;
	protected static $write_cache;
	
	// Output buffering level
	protected static $buffer_level;
	
	// Will be set to TRUE when an exception is caught
	public static $has_error = FALSE;
	
	// Log levels
	protected static $log_levels = array ('error' => 1, 'alert' => 2, 'info' => 3, 'debug' => 4 );
	
	/**
	 * @var  object  logging object
	 */
	public static $log;
	
//	// Log message types
//	const ERROR = 'ERROR';
//	const DEBUG = 'DEBUG';
//	const INFO  = 'INFO';
//	
//	/**
//	 * @var  array  PHP error code => human readable name
//	 */
//	public static $php_errors = array(
//		E_ERROR              => 'Fatal Error',
//		E_USER_ERROR         => 'User Error',
//		E_PARSE              => 'Parse Error',
//		E_WARNING            => 'Warning',
//		E_USER_WARNING       => 'User Warning',
//		E_STRICT             => 'Strict',
//		E_NOTICE             => 'Notice',
//		E_RECOVERABLE_ERROR  => 'Recoverable Error',
//	);
	
	/**
	 * 获取配置
	 *
	 * @param string $myconfig
	 * @return array/string
	 */
	public static function config($myconfig) {
		$c = explode ( '.', $myconfig );
		$cname = array_shift ( $c );
		if ($cname == 'core') {
			$filename = 'config';
		} else {
			$filename = $cname;
		}
		
		if (! array_key_exists ( $cname, self::$config )) {
			if ($filename == 'config') {
				include (MYAPPPATH . 'config' . EXT);
				self::$config[$cname] = $config;
			} else {
				$thefiles = self::find_file ( 'config', $filename, false );
				if (is_array ( $thefiles )) {
					arsort($thefiles);	//逆向排序
					$config = array();
					foreach ( $thefiles as $thefile ) {
						if ($thefile) {
							include $thefile;
							if (isset($break) && $break==true){
								break;
							}
						}
					}
				}
				self::$config[$cname] = $config;
			}
		}
		
		$v = self::$config[$cname];
		foreach ( $c as $i ) {
			$v = $v [$i];
		}
		return $v;
	}
	
	public static function key_string($array, $key) {
		$c = explode ( '.', $key );
		foreach ( $c as $i ) {
			$array = $array [$i];
		}
		return $array;
	}
	
	/**
	 * Returns a singleton instance of Database.
	 * 
	 * @param $dbconfig
	 * @return  Database
	 */
	public static function & db($dbconfig = 'default') {
		return Database::instance($dbconfig);
	}
	
	public static function myclass($classid = 0) {
		if ( !isset(self::$myclass[$classid]) ){
			self::$myclass[$classid] = self::config ( 'class/class_' . $classid );
		}
		return self::$myclass[$classid];
	}
	
	public static function runtime($decimals = 4, $begintime = null) {
		$begintime or $begintime = STARTTIME;
		list ( $usec, $sec ) = explode ( " ", microtime () );
		$thistime = (( float ) $usec + ( float ) $sec);
		return number_format ( $thistime - $begintime, $decimals );
	}
	
	/**
	 * Provides class auto-loading.
	 *
	 * @param   string  name of class
	 * @return  bool
	 */
	public static function auto_load($class) {
		if (class_exists ( $class, FALSE ))
			return TRUE;
		
		if (($suffix = strrpos ( $class, '_' )) > 0) {
			// Find the class suffix
			$suffix = ucfirst ( strtolower ( substr ( $class, $suffix + 1 ) ) );
		} else {
			// No suffix
			$suffix = FALSE;
		}
		
		$class2 = '';
		if ($suffix === 'Core') {
			$type = 'libraries';
			$file = substr ( $class, 0, - 5 );
		} elseif ($suffix === 'Controller') {
			$type = 'controllers';
			// Lowercase filename
			$file = strtolower ( substr ( $class, 0, - 11 ) );
		} elseif ($suffix === 'Model') {
			$type = 'models';
			// Lowercase filename
			$class2 = substr ( $class, 0, - 6 );
			$file = strtolower ( $class2 );
		} elseif ($suffix === 'Api') {
			$type = 'api';
			// Lowercase filename
			$class2 = substr ( $class, 0, - 4 );
			$file = strtolower ( $class2 );
		} elseif ($suffix === 'Driver') {
			$type = 'libraries/drivers';
			$file = str_replace ( '_', '/', substr ( $class, 0, - 7 ) );
		} else {
			// This could be either a library or a helper, but libraries must
			// always be capitalized, so we check if the first character is
			// uppercase. If it is, we are loading a library, not a helper.
			$type = ($class [0] < 'a') ? 'libraries' : 'helpers';
			$file = $class;
		}
		
		if ($filename = self::find_file ( $type, $file )) {
			// Load the class
			require $filename;
		} else {
			// The class could not be found
			return FALSE;
		}
		if ($class2 && Myqee::$config['core']['extension_prefix'] && ($filename = Myqee::find_file ( $type, self::$config['core']['extension_prefix'] . $class2 )) ) {
			// Load the class extension
			require $filename;
		} elseif ($suffix !== 'Core' && class_exists ( $class . '_Core', FALSE )) {
			// Class extension to be evaluated
			$extension = 'class ' . $class . ' extends ' . $class . '_Core { }';
			
			// Start class analysis
			$core = new ReflectionClass ( $class . '_Core' );
			
			if ($core->isAbstract ()) {
				// Make the extension abstract
				$extension = 'abstract ' . $extension;
			}
			
			// Transparent class extensions are handled using eval. This is
			// a disgusting hack, but it gets the job done.
			eval ( $extension );
		}
		
		return TRUE;
	}
	
	public static function api_load($path, $file) {
		$filename = self::find_file('api/'.$path,$file,true);
		require $filename;
		return $filename;
	}
	
	/**
	 * 寻找文件
	 *
	 * @param string $filepath 目录
	 * @param string $file 文件名
	 * @param boolean $onlyuserapp 是否仅读取用户APP目录
	 * @return string $filename 文件路径
	 */
	
	public static function find_file($directory, $filename, $required = FALSE, $ext = FALSE) {
		// NOTE: This test MUST be not be a strict comparison (===), or empty
		// extensions will be allowed!
		if ($ext == '') {
			// Use the default extension
			$ext = EXT;
		} else {
			// Add a period before the extension
			$ext = '.' . $ext;
		}
		
		// Search path
		$search = $directory . '/' . $filename . $ext;
		
		
		if (isset ( self::$internal_cache ['find_file_paths'] [$search] ))
			return self::$internal_cache ['find_file_paths'] [$search];
			
		// Load include paths
		if (!$paths = self::$include_paths)return false;
		
		// Nothing found, yet
		$found = NULL;
		
		if ($directory === 'config' or $directory === 'i18n') {
			// Search in reverse, for merging
			//$paths = array_reverse($paths);
			ksort ( $paths ); //ksort array
			

			foreach ( $paths as $path ) {
				if (is_file ( $path . $search )) {
					// A matching file has been found
					$found [] = $path . $search;
				}
			}
		} else {
			foreach ( $paths as $path ) {
				if (is_file ( $path . $search )) {
					// A matching file has been found
					$found = $path . $search;
					
					// Stop searching
					break;
				}
			}
		}
		
		if ($found === NULL) {
			if ($required === TRUE) {
				Myqee::show_500('resource_not_found');
			} else {
				// Nothing was found, return FALSE
				$found = FALSE;
			}
		}
		
		if (! isset ( self::$write_cache ['find_file_paths'] )) {
			// Write cache at shutdown
			self::$write_cache ['find_file_paths'] = TRUE;
		}
		
		return self::$internal_cache ['find_file_paths'] [$search] = $found;
	}
	
	/**
	 * Get all include paths. APPPATH is the first path, followed by module
	 * paths in the order they are configured, follow by the MYQEEPATH.
	 *
	 * @param   boolean  re-process the include paths
	 * @return  array
	 */
	public static function include_paths($process = FALSE ,$addpath = NULL) {
		if ($process === TRUE) {
			$include_paths = array ();
			
			if (defined('ADMINPATH') && ADMINPATH!=false) {
				//管理员模式
				if (defined ( 'MY_MODULE_PATH' ) && MY_MODULE_PATH!=false && preg_match("/^[a-z0-9]+$/i",MY_MODULE_PATH)) {
					//支持自定义管理模块功能
					$path = realpath(MODULEPATH . MY_MODULE_PATH . '/admin/');
					if ($path) {
						$include_paths ['h'] = str_replace('\\', '/',$path).'/';
					}
					$path = realpath(ADMINPATH . 'modules/' . MY_MODULE_PATH . '/');
					if ($path) {
						$include_paths ['g'] = str_replace('\\', '/',$path).'/';
					}

					//系统管理目录
					$path = realpath(MYQEEPATH . 'admin/modules/' . MY_MODULE_PATH . '/');
					if ($path) {
						$include_paths ['f'] = str_replace('\\', '/',$path).'/';
					}
				}
				$include_paths ['e'] = ADMINPATH;
				//application目录
				$include_paths ['d'] = MYQEEPATH . 'admin/';
				$include_paths ['c'] = MYAPPPATH;
			} else {
				if (defined ( 'MY_MODULE_PATH' ) && MY_MODULE_PATH!=false && preg_match("/^[a-z0-9]+$/i",MY_MODULE_PATH)) {
					//支持用户自定义模块功能
					$path = realpath(MODULEPATH . MY_MODULE_PATH . '/');
					if ($path) {
						$include_paths ['e'] = str_replace('\\', '/',$path).'/';
					}
					//系统模块路径
					$path = realpath(MYQEEPATH . 'modules/' . MY_MODULE_PATH . '/');
					if ($path) {
						$include_paths ['d'] = str_replace('\\', '/',$path).'/';
					}
				}
				$include_paths ['c'] = MYAPPPATH;
			
			}
		
			//Include SYSPATH path
			if(defined('SYSPATH')){
				$include_paths ['b'] = SYSPATH;
			}
			
			$include_paths ['a'] = MYQEEPATH;
		
			
			if ($addpath && is_array($addpath)){
				foreach ($include_paths as $k=>$v){
					$addpath[$k] = $v;
				}
				$include_paths = $addpath;
			}
			
			self::$include_paths = $include_paths;
		}
		return self::$include_paths;
	}
	
	/**
	 * Fetch an i18n language item.
	 *
	 * @param   string  language key to fetch
	 * @param   array   additional information to insert into the line
	 * @return  string  i18n language string, or the requested key if the i18n item is not found
	 */
	public static function lang($key, $args = array()) {
		// Extract the main group from the key
		$group = explode ( '.', $key, 2 );
		$group = $group [0];
		
		// Get locale name
		$locale = self::config ( 'core.language.0' );
		
		static $run;
		if ($run!==true){
			$run=true;
			self::internal_cache_load('language');
		}
		
		if (! isset ( self::$internal_cache ['language'] [$locale] [$group] )) {
			// Messages for this group
			$messages = array ();
			if ($files = self::find_file ( 'i18n', $locale . '/' . $group )) {
				foreach ( $files as $file ) {
					include $file;
					
					// Merge in configuration
					if (! empty ( $lang ) and is_array ( $lang )) {
						foreach ( $lang as $k => $v ) {
							$messages [$k] = $v;
						}
					}
				}
			}
			
			if (! isset ( self::$write_cache ['language'] )) {
				// Write language cache
				self::$write_cache ['language'] = TRUE;
			}
			
			self::$internal_cache ['language'] [$locale] [$group] = $messages;
		}
		// Get the line from cache
		$line = self::key_string ( self::$internal_cache ['language'] [$locale], $key );
		if ($line === NULL) {
			self::log ( 'error', 'Missing i18n entry ' . $key . ' for language ' . $locale );
			
			// Return the key string as fallback
			return $key;
		}
		
		if (is_string ( $line ) and func_num_args () > 1) {
			$args = array_slice ( func_get_args (), 1 );
			
			// Add the arguments into the line
			$line = vsprintf ( $line, is_array ( $args [0] ) ? $args [0] : $args );
		}
		
		return $line;
	}
	
	/**
	 * Add a new message to the log.
	 *
	 * @param   string  type of message
	 * @param   string  message text
	 * @return  void
	 */
	public static function log($type, $message) {
		if (function_exists('logs')){
			logs($type, $message);
		}
		return false;
		
		if (self::$log_levels [$type] <= self::$config ['core'] ['log_threshold']) {
			$message = array (date ( 'Y-m-d H:i:s P' ), $type, $message );
			
			// Run the system.log event
			Event::run ( 'system.log', $message );
			
			self::$log [] = $message;
		}
	}
	
	/**
	 * Saves the internal caches: configuration, include paths, etc.
	 *
	 * @return  boolean
	 */
	public static function internal_cache_save() {
		if (!(self::$config['core']['internal_cache']>0)){
			return FALSE;
		}
		if (! is_array ( self::$write_cache ))
			return FALSE;
			
		// Get internal cache names
		$caches = array_keys ( self::$write_cache );
		
		// Nothing written
		$written = FALSE;
		
		foreach ( $caches as $cache ) {
			if (isset ( self::$internal_cache [$cache] )) {
				// Write the cache file
				self::cache_save($cache, self::$internal_cache[$cache]);
				// A cache has been written
				$written = TRUE;
			}
		}
		
		return $written;
	}
	
	public static function internal_cache_load($type=''){
		if (!$type)return false;
		if (self::$config['core']['internal_cache']>0){
			if (is_file($file = CACHEPATH.'_myqee_cache_'.md5($type))){
				
				if (self::$config['core']['internal_cache']>1){
					//自动模式，有数据缓存
					if ($_SERVER['REQUEST_TIME']-filemtime($file)>self::$config['core']['internal_cache']){
						//缓存过时了
						return true;
					}
				}
				
				$config = unserialize(file_get_contents($file));
				if (is_array($config))self::$internal_cache [$type] = $config;
			}
		}
		return true;
	}
	
	public static function cache_save($name, $data){
		if (!$data)return false;
		file_put_contents(CACHEPATH.'_myqee_cache_'.md5($name),serialize($data));
	}
	

	/**
	 * 允许程序在后台运行
	 *
	 * @param unknown_type $info
	 */
	public static function run_in_system($info='OK'){
		Myqee::close_buffers(FALSE);
		
		if (class_exists('Session')){
			//将SESSION对话关闭，防止其他页面被卡住
			Session::instance()->write_close();
		}
		
		header("Connection: close");
		ignore_user_abort();
		ob_start();
		echo ($info);
		$size = ob_get_length();
		header("Content-Length: $size");
		ob_end_flush();
		flush();
	}
	
	
	/**
	 * 指定面不存在
	 * @param string $info
	 */
	public static function show_404($info = 'Page Not Found')
	{
		throw new Error_Exception ( $info , E_PAGE_NOT_FOUND );
	}
	
	
	
	
	/**
	 * 系统错误
	 * @param string $info
	 */
	public static function show_500($info = '500 Error: Internal Server Error')
	{
		throw new Error_Exception ( $info , E_FRAME_ERROR);
	}
	
	
	
	
	public static function show_ok($msg = '', $gotoUrl = false, $isInHiddenFrame = false) {
		self::show_info ( $msg, $gotoUrl, $isInHiddenFrame, 'succeed' );
	}
	
	
	public static function show_error($msg = '', $gotoUrl = false, $isInHiddenFrame = false) {
		self::show_info ( $msg, $gotoUrl, $isInHiddenFrame, 'error' );
	}
	
	public static function show_info($msg = '', $gotoUrl = false, $isInHiddenFrame = false, $type = 'alert') {
		if ($isInHiddenFrame) {
			self::_show_info_hiddenframe ( $msg, $gotoUrl, $type );
		} else {
			self::_show_info_selfframe ( $msg, $gotoUrl, $type );
		}
	}
	
	protected static function _show_info_hiddenframe($infoarr = '', $gotoUrl = false, $type = 'alert') {
		if (! is_array ( $infoarr )) {
			$infoarr = array ('message' => $infoarr );
		}
		$handler = $infoarr ['handler'];
		
		unset ( $infoarr ['handler'] );
		echo '<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>信息提示</title>
		</head>
		<body>
		<div style="font-size:12px;padding:10px;">
		', $infoarr ['message'], '
		</div>
		<br/><br/><a href="' . SITE_URL . '" target="_top">点击返回首页</a>
		<script type="text/javascript">
		var A=0;
		function handler(el){
		';
		
		if ($handler) {
			echo '
			try{
				(', $handler, ')(el);
			}catch(e){}
			';
		}
		if ($gotoUrl) {
			if ($gotoUrl == 'refresh') {
				$gotoUrl = 'parent.location.reload()';
			} elseif ($gotoUrl == 'goback') {
				$gotoUrl = 'parent.history.go(-1);';
			} else {
				$gotoUrl = 'parent.document.location="' . str_replace ( '"', '\"', self::url ( $gotoUrl ) ) . '";';
			}
			echo $gotoUrl;
		} else {
			echo 'if (window.name == "hiddenFrame"){
				window.close();
			}';
		}
		echo '}
			var MyQEE = {};
			MyQEE.alert= function(runset) {
				alert(runset.message);
				if (runset.handler){
					try{runset.handler()}catch(e){}
				}
			}
			MyQEE.error = MyQEE.alert;
			MyQEE.succeed = MyQEE.alert;
			
			var runWindow = MyQEE;
			try{
				if (typeof (parent.MyQEE.', $type, ')=="function"){
					runWindow = parent.MyQEE;
				}
			}catch(e){}
			var runset = eval("("+unescape("', Tools::escape ( Tools::json_encode ( $infoarr ) ), '")+")");
			runset.message = runset.message || "";
			runset.handler = handler;
			runset.width = runset.width || 400;
			runWindow.', $type, '(runset);
		</script>';
		Event::run('system.shutdown');
		exit ();
	}
	
	protected static function _show_info_selfframe($msg = '', $gotoUrl = false) {
		if (! $v = Myqee::config ( 'core.systemplate.show_info' )) {
			$v = 'show_info';
		}
		if ($gotoUrl == 'refresh') {
			$gotoUrl = $_SERVER ["SCRIPT_URI"];
		} elseif ($gotoUrl == 'goback') {
			$gotoUrl = $_SERVER ['HTTP_REFERER'];
			;
		} else {
			$gotoUrl = self::url ( $gotoUrl );
		}
		$view = new View ( $v );
		$view->set ( 'message', $msg );
		if (strlen ( $gotoUrl )) {
			$view->set ( 'forward', $gotoUrl );
		}
		$out= $view->render ( false );
		myqee_root::output($out);
		echo $out;
		Event::run('system.shutdown');
		exit ();
	}
	
	public static function get_cookie($name) {
		$config = Myqee::config ( 'core.cookie' );
		$config ['prefix'] and $name = $config ['prefix'] . $name;
		return $_COOKIE [$name];
	}
	
	/**
	 * 创建cookie 详细请参考setcookie函数参数
	 *
	 * @param string/array $name
	 * @param string $value
	 * @param number $expire
	 * @param string $path
	 * @param string $domain
	 * @param boolean $secure 
	 * @param boolean $httponly
	 * @return boolean true/false
	 */
	public static function create_cookie($name, $value = NULL, $expire = NULL, $path = NULL, $domain = NULL, $secure = NULL, $httponly = NULL) {
		if (headers_sent ())
			return FALSE;
			
		// If the name param is an array, we import it
		is_array ( $name ) and extract ( $name, EXTR_OVERWRITE );
		
		// Fetch default options
		$config = Myqee::config ( 'core.cookie' );
		
		foreach ( array ('value', 'expire', 'domain', 'path', 'secure', 'httponly', 'prefix' ) as $item ) {
			if ($$item === NULL and isset ( $config [$item] )) {
				$$item = $config [$item];
			}
		}
		$config ['prefix'] and $name = $config ['prefix'] . $name;
		
		// Expiration timestamp
		$expire = ($expire == 0) ? 0 : $_SERVER ['REQUEST_TIME'] + ( int ) $expire;
		
		return setcookie ( $name, $value, $expire, $path, $domain, $secure, $httponly );
	}
	
	/**
	 * 删除cookie
	 *
	 * @param string $name cookie名称
	 * @param string $path cookie路径
	 * @param string $domain cookie作用域
	 * @return boolean true/false
	 */
	public static function delete_cookie($name, $path = NULL, $domain = NULL) {
		return self::create_cookie ( $name, '', - 864000, $path, $domain, FALSE, FALSE );
	}
	
	/**
	 * 获取页面URL地址
	 *
	 * @param string $urlstr 页面路径
	 * @param string $suffix 后缀
	 * @param boolean $full_url 是否返回完整路径
	 * @return string URL
	 */
	public static function url($urlstr = '', $suffix = null ,$full_url=false) {
		if (empty($urlstr)) {
			return '';
		}
		static $urlache;
		$urlkeystr = $urlstr.'_'.$suffix.'__'.($full_url?'true':'false');
		if (isset($urlache[$urlkeystr])){
			return $urlache[$urlkeystr];
		}
		$urlstr_1 = explode ( '?', $urlstr, 2 );
		$urlstr_2 = explode ( '#', $urlstr_1[0], 2 );
		if ( preg_match("/^\/|https?\:\/\//i",$urlstr_2[0]) ){
			$url = $urlstr_2[0];
		}else{
			$myqeepage = self::config ( 'core.myqee_page' );
			$url =  ( defined('ADMIN_URLPATH')?ADMIN_URLPATH:SITE_URL.($myqeepage ? $myqeepage . '/' : '') ).$urlstr_2[0];
		}
		$url .= (substr($urlstr_2[0],-1)!='/'?($suffix?$suffix:self::config('core.url_suffix')):'') . 
				(isset($urlstr_1[1])?'?'.$urlstr_1[1]:'');
				(isset($urlstr_2[1])?'#'.$urlstr_2[1]:'');
		
		if ($full_url && substr($url,0,1)=='/'){
			$url = self::protocol() .'://'. SITE_DOMAIN . $url;
		}
		$urlache[$urlkeystr] = $url;
		return $url;
	}
	
	public static function url_base() {
		return defined('ADMIN_URLPATH')?ADMIN_URLPATH:self::config ( 'core.mysite_url' );
	}
	
	public static function protocol()
	{
		if (Myqee::$is_cli)
		{
			return NULL;
		}
		elseif ( ! empty($_SERVER['HTTPS']) AND $_SERVER['HTTPS'] === 'on')
		{
			return 'https';
		}
		else
		{
			return 'http';
		}
	}
	
	/**
	 * Triggers the shutdown of Kohana by closing the output buffer, runs the system.display event.
	 *
	 * @return  void
	 */
	public static function shutdown() {
		self::close_buffers ( TRUE );
	}
	
	/**
	 * Closes all open output buffers, either by flushing or cleaning all
	 * open buffers, including the Kohana output buffer.
	 *
	 * @param   boolean  disable to clear buffers, rather than flushing
	 * @return  void
	 */
	public static function close_buffers($flush = TRUE) {
		if (ob_get_level () >= self::$buffer_level) {
			// Set the close function
			$close = ($flush === TRUE) ? 'ob_end_flush' : 'ob_end_clean';
			
			while ( ob_get_level () > self::$buffer_level ) {
				// Flush or clean the buffer
				$close ();
			}
			
			
			// Reset the buffer level
			self::$buffer_level = ob_get_level ();
		}
	}
	
	
	public static function show_error_page(){
		Myqee::close_buffers();
		echo '请稍后访问！';
		exit(1);
	}
	
	
	public static function segment($index = 1, $default = FALSE) {
		if (is_string ( $index )) {
			if (($key = array_search ( $index, Myqee::$arguments )) === FALSE)
				return $default;
			
			$index = $key + 2;
		}
		
		$index = ( int ) $index - 1;
		return isset ( Myqee::$arguments[$index] ) ? Myqee::$arguments[$index] : $default;
	}
	
	public static function segment_array($offset = 0, $associative = FALSE){
		$segment = array_slice(Myqee::$arguments,$offset);
		if ($associative){
			$segment1= array();
			$c = count($segment);
			for ($i=0;$i=$i+2;$i<$c){
				$segment1[$segment[$i]] = (string)$segment[$i+1];
			}
			$segment = $segment1;
		}
		return $segment;
	}
}
