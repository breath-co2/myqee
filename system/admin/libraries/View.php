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
	
	protected $group = '';
	
	/**
	 * 静态构造函数
	 *
	 * @param   string  $name view name
	 * @param   array   $data pre-load data
	 * @param   string  $type type of file: html, css, js, etc.
	 * @return  View
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
	 * @return  View
	 */
	public function set_filename($name, $ext = NULL)
	{
		$strpos = stripos($name,'/');
		if ($strpos!==false && $strpos>0){
			$this -> group = substr($name,0,$strpos) .'/';
		}
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
	 * 设置模板组
	 * @param string $groupname
	 * @return View
	 */
	public function set_group($groupname){
		$this -> group = $groupname;
		return $this;
	}

	/**
	 * Sets a view variable.
	 *
	 * @param   string|array  name of variable or an array of variables
	 * @param   mixed         value when using a named variable
	 * @return  View
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
	 * 传递同内存变量
	 * 应用点：当一个变量在控制器里被定义，将它传递给视图变量中，即可以在视图中使用到
	 * 若某个视图对其值进行修改，所有地方的值都会变化，因为他们使用相同的内存地址
	 * 这样当render执行过后，在控制器里那个变量也会变成新的值
	 *
	 * @param   string   name of variable
	 * @param   mixed    variable to assign by reference
	 * @return  View
	 */
	public function bind($name, & $var)
	{
		$this->myqee_local_data[$name] =& $var;

		return $this;
	}
	
	/**
	 * 传递同内存视图全局变量
	 * 它与bind的区别就是bind传递给的只是单独的视图，而set_global_bind传递给了所有视图
	 * 
	 * @param string $name
	 * @param mixed $var
	 * @return  View
	 */
	public function set_global_bind($name, & $var)
	{
		View::$myqee_global_data[$name] =& $var;

		return $this;
	}

	/**
	 * 设置视图全局变量
	 *
	 * @param   string|array  name of variable or an array of variables
	 * @param   mixed         value when using a named variable
	 * @return  View
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
		return $this;
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
			throw new Error_Exception('core.view_set_filename');

		if (is_string($this->myqee_filetype))
		{
			// Merge global and local data, local overrides global with the same name
			$data = array_merge(View::$myqee_global_data, $this -> myqee_local_data);

			$view = new _myqee_view_create;
			
			// Load the view in the controller for access to $this
			$output = $view->_myqee_load_view( $this->myqee_filename ,$data ,$this -> group );
			

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
				if (($file = fopen($this->myqee_filename, 'rb')))
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
	/**
	 * 视图所有变量
	 * @var array
	 */
	protected $data = array();
	
	/**
	 * 视图文件完整路径
	 * @var string
	 */
	protected $filename;
	
	protected $tplgroup = '';
	
	public function __construct(){
	}
	
	public function _myqee_load_view($filename, $data , $group){
		if ($filename == '')
			return;
		
		$this -> data = $data;
		$this -> filename = $filename;
		$this -> tplgroup = $group;
		
		unset($filename,$data);
		
		//视图局部变量
		if ($this -> data){
			foreach ($this -> data as $k => $v){
				if(is_string($k)){
					$$k =& $this -> data[$k];
				}
			}
			unset($k,$v);
		}
		
		ob_start();
		
		$__ER_ = error_reporting(7);
		include $this -> filename;
		error_reporting($__ER_);
		
		return ob_get_clean();
	}
	
	protected function block($type='index',$no=0,$isrender=true){
		$block = Myqee::config('block/block_'.$type.'_'.$no);
		if ($block && $block['isuse']){
			if ($block['cache_time']){
				$cachefile = 'block_'.$block['type'].'_'.$block['no'];
				$blockhtml = Cache::get($cachefile,$block['cache_time']);
			}else{
				$blockhtml = false;
			}
			$varname = $block['varname'];
			if ($blockhtml===false){
				$blockinfo = Myqee::db() -> select('show_type,content') -> getwhere('[block]',array('type'=>$type,'no'=>$no)) -> result_array(false);
				$blockinfo = $blockinfo[0];
				if ($blockinfo['show_type']>0){
					//规律的格式
					$data = array($varname => unserialize($blockinfo['content']));
					if (!is_array($data[$varname])){
						$data[$varname] = array();
					}
					if ($block['tlp_id']){
						$blockhtml = Myhtml::createhtml($blockinfo['tlp_id'],null,$data);
					}elseif($block['template']){
						$templage = $block['engie']?$block['engie']:'default';
						$engine = Myqee::config('template.engine');
						if (!$engine[$templage]){
							$blockhtml = $data[$varname];
						}else{
							$api = $engine[$templage]['api'];
							
							$tpl = new $api($block['template'],true);
							$blockhtml = $tpl -> render(false,$data);
						}
					}else{
						$blockhtml = $data[$varname];
					}
				}else{
					$blockhtml = $blockinfo['content'];
				}
			}
			if ($block['cache_time']){
				$blockhtml = Cache::set($cachefile,$blockhtml);
			}
		}
		
		if (ADMIN_EDIT_BLOCK===true){
			//后台编辑模式
			$id = 'block_'.Tools::get_rand(12);
			$outblockhtml = '<div style="padding:0;margin:0;" title="点击编辑碎片" id="'.$id.'"><div style="float:left;text-align:left;"><div id="'.$id.'_div" style="padding:3px;margin:-3px;position:absolute;background:red;cursor:pointer;filter:alpha(opacity=50);opacity:0.5;" onclick="parent.edit_block(\''.$type.'\',\''.$no.'\',this);" onmouseover="this.style.opacity=0.7;this.style.filter=\'alpha(opacity=70)\';parent.set_myqeediv(\''.$id.'\');" onmouseout="this.style.opacity=0.5;this.style.filter=\'alpha(opacity=50)\'"></div><div onclick="document.getElementById(\''.$id.'_div\').onclick();" onmouseover="document.getElementById(\''.$id.'_div\').onmouseover();" onmouseout="document.getElementById(\''.$id.'_div\').onmouseout();" style="cursor:pointer;position:absolute;font-size:12px;color:red;font-weight:bold;background:#fff;z-index:2;pading:3px;line-height:1.6em;white-space:nowrap;">&nbsp;点击编辑:“'.$block['title'].'”&nbsp;</div></div>'.$blockhtml.'</div><script type="text/javascript">parent.set_myqeediv("'.$id.'");</script>';
		
			echo $outblockhtml;
		}else{
			print_r($blockhtml);
		}
		if ($isrender){
			return true;
		}else{
			return $blockhtml;
		}
	}
	
	protected function view($view, $data=null, $isrender=true , $type = null){
		return View::factory( $this->tplgroup.$view, $data, $type ) -> render($isrender);
	}
	
	protected function location($classid=NULL,$myclass=NULL){
		if (!$classid)$classid = $this -> data['class_id'];
		return Myhtml::get_location_array($classid,$myclass);
	}
	
	protected function lang($str=null){
		if ($str===null)return false;
		return Myqee::lang($str);
	}
}