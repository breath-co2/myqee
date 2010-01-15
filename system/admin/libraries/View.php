<?php defined('MYQEEPATH') OR die('No direct access allowed.');
/**
 * 视图类
 *
 * $Id$
 *
 * @package     系统核心
 * @subpackage	类库
 * @author      rovewang@gmail.com
 * @copyright   (c) 2008-2010 Myqee Team
 * @license     http://www.myqee.com/license.html
 * @link		http://www.myqee.com/
 * @since		Version 1.0
 */
class View_Core {

	// The view file name and type
	protected $myqee_filename = FALSE;
	protected $myqee_filetype = FALSE;

	// View variable storage
	protected $myqee_local_data = array();
	protected static $myqee_global_data = array();

	protected $start_time;
	
	/**
	 * 静态构造函数
	 *
	 * @param   string  view name
	 * @param   array   pre-load data
	 * @param   string  type of file: html, css, js, etc.
	 * @return  object
	 */
	public static function factory($name = NULL, $data = NULL, $type = NULL)
	{
		return new View($name, $data, $type);
	}

	/**
	 * Attempts to load a view and pre-load view data.
	 *
	 * @throws  Kohana_Exception  if the requested view cannot be found
	 * @param   string  view name
	 * @param   array   pre-load data
	 * @param   string  type of file: html, css, js, etc.
	 * @return  void
	 */
	public function __construct($name = NULL, $data = NULL, $ext = NULL)
	{
		if (is_string($name) AND $name !== '')
		{
			// Set the filename
			$this->set_filename($name, $ext);
		}

		if (is_array($data) AND ! empty($data))
		{
			// Preload data using array_merge, to allow user extensions
			$this->myqee_local_data = array_merge($this -> myqee_local_data, $data);
		}
	}

	/**
	 * Magic method access to test for view property
	 *
	 * @param   string   View property to test for
	 * @return  boolean
	 */
	public function __isset($key = NULL)
	{
		return $this->is_set($key);
	}

	/**
	 * Sets the view filename.
	 *
	 * @chainable
	 * @param   string  view filename
	 * @param   string  view file type
	 * @return  object
	 */
	public function set_filename($name, $ext = NULL)
	{
		if ($ext == NULL)
		{
			// Load the filename and set the content type
			$this->myqee_filename = Myqee::find_file('views', $name, TRUE);
			$this->myqee_filetype = EXT;
		}
		else
		{
			// Check if the filetype is allowed by the configuration
			if ( ! in_array($ext, Myqee::config('view.allowed_filetypes')))
				throw new Error_Exception('core.invalid_filetype', $ext);

			// Load the filename and set the content type
			$this->myqee_filename = Myqee::find_file('views', $name, TRUE, $ext);
			$this->myqee_filetype = Myqee::config('mimes.'.$ext);

			if ($this->myqee_filetype == NULL)
			{
				// Use the specified type
				$this->myqee_filetype = $ext;
			}
		}
		return $this;
	}

	/**
	 * Sets a view variable.
	 *
	 * @param   string|array  name of variable or an array of variables
	 * @param   mixed         value when using a named variable
	 * @return  object
	 */
	public function set($name, $value = NULL)
	{
		if (is_array($name))
		{
			foreach ($name as $key => $value)
			{
				$this->__set($key, $value);
			}
		}
		else
		{
			$this->__set($name, $value);
		}

		return $this;
	}

	/**
	 * Checks for a property existence in the view locally or globally. Unlike the built in __isset(),
	 * this method can take an array of properties to test simultaneously.
	 *
	 * @param string $key property name to test for
	 * @param array $key array of property names to test for
	 * @return boolean property test result
	 * @return array associative array of keys and boolean test result
	 */
	public function is_set( $key = FALSE )
	{
		// Setup result;
		$result = FALSE;

		// If key is an array
		if (is_array($key))
		{
			// Set the result to an array
			$result = array();

			// Foreach key
			foreach ($key as $property)
			{
				// Set the result to an associative array
				$result[$property] = (array_key_exists($property, $this->myqee_local_data) OR array_key_exists($property, View::$myqee_global_data)) ? TRUE : FALSE;
			}
		}
		else
		{
			// Otherwise just check one property
			$result = (array_key_exists($key, $this->myqee_local_data) OR array_key_exists($key, View::$myqee_global_data)) ? TRUE : FALSE;
		}

		// Return the result
		return $result;
	}

	/**
	 * Sets a bound variable by reference.
	 *
	 * @param   string   name of variable
	 * @param   mixed    variable to assign by reference
	 * @return  object
	 */
	public function bind($name, & $var)
	{
		$this->myqee_local_data[$name] =& $var;

		return $this;
	}

	/**
	 * 设置视图全局变量
	 *
	 * @param   string|array  name of variable or an array of variables
	 * @param   mixed         value when using a named variable
	 * @return  void
	 */
	public static function set_global($name, $value = NULL)
	{
		if (is_array($name))
		{
			foreach ($name as $key => $value)
			{
				View::$myqee_global_data[$key] = $value;
			}
		}
		else
		{
			View::$myqee_global_data[$name] = $value;
		}
	}

	/**
	 * Magically sets a view variable.
	 *
	 * @param   string   variable key
	 * @param   string   variable value
	 * @return  void
	 */
	public function __set($key, $value)
	{
		$this->myqee_local_data[$key] = $value;
	}

	/**
	 * Magically gets a view variable.
	 *
	 * @param  string  variable key
	 * @return mixed   variable value if the key is found
	 * @return void    if the key is not found
	 */
	public function &__get($key)
	{
		if (isset($this->myqee_local_data[$key]))
			return $this->myqee_local_data[$key];

		if (isset(View::$myqee_global_data[$key]))
			return View::$myqee_global_data[$key];

		if (isset($this->$key))
			return $this->$key;
	}
	
	public function &__getglobal(){
		return View::$myqee_global_data;
	}

	/**
	 * Magically converts view object to string.
	 *
	 * @return  string
	 */
	public function __toString()
	{
		try
		{
			return $this->render();
		}
		catch (Exception $e)
		{
			// Display the exception using its internal __toString method
			return (string) $e;
		}
	}

	/**
	 * 输出页面
	 *
	 * @param   boolean   set to TRUE to echo the output instead of returning it
	 * @param   callback  special renderer to pass the output through
	 * @return  string    if print is FALSE
	 * @return  void      if print is TRUE
	 */
	public function render($print = FALSE, $renderer = FALSE)
	{
		if (empty($this->myqee_filename))
			throw new Kohana_Exception('core.view_set_filename');

		if (is_string($this->myqee_filetype))
		{
			// Merge global and local data, local overrides global with the same name
			//$data = array_merge(View::$myqee_global_data, $this->myqee_local_data);

			$view = new _myqee_view_create;
			
			// Load the view in the controller for access to $this
			$output = $view->_myqee_load_view( $this->myqee_filename ,$this->myqee_local_data );
			

			if ($renderer !== FALSE AND is_callable($renderer, TRUE))
			{
				// Pass the output through the user defined renderer
				$output = call_user_func($renderer, $output);
			}

			if ($print === TRUE)
			{
				// Display the output
				echo $output;
				return;
			}
		}
		else
		{
			// Set the content type and size
			header('Content-Type: '.$this->myqee_filetype[0]);

			if ($print === TRUE)
			{
				if ($file = fopen($this->myqee_filename, 'rb'))
				{
					// Display the output
					fpassthru($file);
					fclose($file);
				}
				return;
			}

			// Fetch the file contents
			$output = file_get_contents($this->myqee_filename);
		}

		return $output;
	}
} // End View



class _myqee_view_create{
	protected $data = array();
	protected $global_data = array();
	protected $_group_id;
	public function __construct($group='default'){
		$this -> _group_id = $group;
	}
	
	public function _myqee_load_view($__myqee_view_filename_,$__myqee_view_data_){
		if ($__myqee_view_filename_ == '')
			return;

		// Buffering on
		ob_start();
		
		
		$this -> data = $__myqee_view_data_;
		$this -> global_data =& View::__getglobal();
		unset($__myqee_view_data_);
		
		//视图局部变量
		if ($this -> global_data){
			foreach ($this -> data as $__k_ => $__v_){
				if(is_string($__k_)){
					$$__k_ = $this -> data[$__k_];
				}
			}
			unset($__k_,$__v_);
		}
		
		//全局变量，如果同名视图局部变量已定义，则对应全局变量被忽略，但视图内仍可通过$this->global来获取
		if ($this -> global_data){
			foreach ($this -> global_data as $__k_ => $__v_){
				if(is_string($__k_) && !isset($$__k_)){
					$$__k_ =& $this -> global_data[$__k_];
				}
			}
			unset($__k_,$__v_);
		}

		$__ER_ = error_reporting(7);
		
		include $__myqee_view_filename_;

		error_reporting($__ER_);
		
		return ob_get_clean();
	}
	
	protected function view($view,$data=null,$isrender=true){
		return View::factory($view,$data,null,$this->_group_id) -> render($isrender);
	}
	
	protected function location($classid=NULL,$myclass=NULL){
		if (!$classid)$classid=$this -> data['class_id'];
		return Createhtml::get_location_array($classid,$myclass);
	}
}