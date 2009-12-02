<?php defined('MYQEEPATH') or die('No direct script access.');

/**
 * Loads and displays Myqee view files. Can also handle output of some binary
 * files, such as image, Javascript, and CSS files.
 *
 * $Id: View.php,v 1.13 2009/11/05 01:02:43 jonwang Exp $
 *
 * @package    Core
 * @author     Myqee Team
 * @copyright  (c) 2008-2009 Myqee Team
 * @license    http://myqee.com/license.html
 */
class View_Core {

	// The view file name and type
	protected $myqee_filename = FALSE;
	protected $myqee_filetype = FALSE;

	// View variable storage
	protected $myqee_local_data = array();
	protected static $myqee_global_data = array();

	protected $template_group = FALSE;
	
	protected $start_time;
	protected $viewtype;

	/**
	 * Creates a new View using the given parameters.
	 *
	 * @param   string  view name
	 * @param   array   pre-load data
	 * @param   string  type of file: html, css, js, etc.
	 * @return  object
	 */
	public static function factory($name = NULL, $data = NULL, $type = NULL, $group = NULL ,$viewtype = NULL)
	{
		return new View($name, $data, $type ,$group , $viewtype);
	}

	/**
	 * Attempts to load a view and pre-load view data.
	 *
	 * @throws  Error_Exception  if the requested view cannot be found
	 * @param   string  view name
	 * @param   array   pre-load data
	 * @param   string  type of file: html, css, js, etc.
	 * @return  void
	 */
	public function __construct($name = NULL, $data = NULL, $type = NULL, $group = NULL, $viewtype = NULL)
	{
		//this run time
		$this -> start_time = _getthistime();
		$this->set_tplgroup($group);

		if (is_string($name) AND $name !== '')
		{
			// Set the filename
			$this->set_filename($name, $type);
		}

		if (is_array($data) AND ! empty($data))
		{
			// Preload data using array_merge, to allow user extensions
			$this->myqee_local_data = array_merge($this->myqee_local_data, $data);
		}
		$this -> viewtype = $viewtype;
	}
	
	public function set_viewtype($viewtype){
		$this -> viewtype = $viewtype;
	}

	/**
	 * Sets the view filename.
	 *
	 * @chainable
	 * @param   string  view filename
	 * @param   string  view file type
	 * @return  object
	 */
	public function set_filename($name, $type = NULL)
	{
		if ($type){
			$ext = '.'.preg_replace("/[^0-9a-z]+/",'',strtolower($type));
		}else{
			$ext = EXT;
		}
		$this->myqee_filetype = $ext;
		if (defined('MY_MODULE_PATH')){
			$myqee_filename = MODULEPATH .MY_MODULE_PATH. '/views' . $this -> template_group.'/'.$name . $ext;
			if (file_exists($myqee_filename)){
				$this -> myqee_filename = $myqee_filename;
				return $this;
			}else{
				$myqee_filename = MYQEEPATH .'modules/'.MY_MODULE_PATH. '/views' . $this -> template_group.'/'.$name . $ext;
				if (file_exists($myqee_filename)){
					$this -> myqee_filename = $myqee_filename;
					return $this;
				}
			}
		}
		
		$myqee_filename = MYAPPPATH . 'views' . $this -> template_group.'/'.$name . $ext;
		if (file_exists($myqee_filename)){
			$this -> myqee_filename = $myqee_filename;
		}else{
			$myqee_filename = MYQEEPATH . 'views/'.$name . $ext;
			if (file_exists($myqee_filename)){
				$this -> myqee_filename = $myqee_filename;
			}
		}

		return $this;
	}

	public function set_tplgroup($group = NULL){
		if ($group !== NULL ){
			$template_group = $group;
		}else{
			if (!defined('TEMPLATE_GROUP')){
				define('TEMPLATE_GROUP',Myqee::config('core.default_viewgroup'));
			}
			$template_group = TEMPLATE_GROUP;
		}
		$template_group = ltrim($template_group,'/');
		$this -> template_group = strlen($template_group)?'/'.$template_group:'';
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
	 * Sets a view global variable.
	 *
	 * @param   string|array  name of variable or an array of variables
	 * @param   mixed         value when using a named variable
	 * @return  object
	 */
	public function set_global($name, $value = NULL)
	{
		if (is_array($name))
		{
			foreach ($name as $key => $value)
			{
				self::$myqee_global_data[$key] = $value;
			}
		}
		else
		{
			self::$myqee_global_data[$name] = $value;
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
		if ( ! isset($this->$key))
		{
			$this->myqee_local_data[$key] = $value;
		}
	}

	/**
	 * Magically gets a view variable.
	 *
	 * @param  string  variable key
	 * @return mixed   variable value if the key is found
	 * @return void    if the key is not found
	 */
	public function __get($key)
	{
		if (isset($this->myqee_local_data[$key]))
			return $this->myqee_local_data[$key];

		if (isset(self::$myqee_global_data[$key]))
			return self::$myqee_global_data[$key];

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
		return $this->render();
	}

	/**
	 * Renders a view.
	 *
	 * @param   boolean   set to TRUE to echo the output instead of returning it
	 * @param   callback  special renderer to pass the output through
	 * @return  string    if print is FALSE
	 * @return  void      if print is TRUE
	 */
	public function render($print = FALSE, $renderer = FALSE , $intohtml = TRUE)
	{

		if (is_string($this->myqee_filetype))
		{
			// Merge global and local data, local overrides global with the same name
			$data = array_merge(self::$myqee_global_data, $this->myqee_local_data);

			$viewtype = array('class','info','list');
			if (in_array($this->viewtype,$viewtype)){
				$viewclass = '_myqee_view_for'.$this->viewtype;
			}else{
				$viewclass = '_myqee_view_create';
			}
			// Load the view in the controller for access to $this
			if ($intohtml) {
				$view = new $viewclass($this->template_group);
				$output = $view ->_myqee_load_view($this->myqee_filename, $data);
			}else{
				$output = Myqee::$instance ->_myqee_load_view($this->myqee_filename, $data);
			}
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
			if ($print === TRUE)
			{
				// Set the content type and size
				header('Content-Type: '.$this->myqee_filetype[0]);

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
	
	
	public function get_view(){
		$view = new _myqee_view_create();
		return $view;
	}



} // End View


class _myqee_view_forinfo extends _myqee_view_create{
	public function __construct($group='default'){
		parent::__construct($group);
	}
	
	public function comment($tpl=NULL,$limit=10,$offset=0){
		
	}
	
	public function pre_info($limit=1){
		if (isset($this -> data['pre_info']))return $this -> data['pre_info'];
		
		if ($this -> data['db_config']['sys_field']['posttime']){
			$orderby_field = $this -> data['db_config']['sys_field']['posttime'];
		}elseif ($this -> data['db_config']['sys_field']['posttime2']){
			$orderby_field = $this -> data['db_config']['sys_field']['posttime2'];
		}elseif ($this -> data['db_config']['sys_field']['id']){
			$orderby_field = $this -> data['db_config']['sys_field']['id'];
		}else{
			$orderby_field = 'id';
		}
		
		$where = array($orderby_field.'<'=>$this -> data['info'][$orderby_field]);
		if ($this -> data['db_config']['sys_field']['class_id']){
			$where[$this -> data['db_config']['sys_field']['class_id']] = $this -> data['info'][$this -> data['db_config']['sys_field']['class_id']];
			$getinfoclass = $this -> data['class_id'];
		}else{
			$getinfoclass = $this -> data['db_name'];
		}
		$data = Myqee::db() -> from($this -> data['db_name']) -> where ($where) -> orderby($orderby_field,'DESC') -> limit($limit) -> get() -> result_array ( FALSE );
		
		if ($limit>1){
			$count_data = count($data);
			for($i=0;$i<$count_data;$i++){
				$data[$i]['URL'] = Myhtml::getinfourl($getinfoclass,$data[$i]);
				$data[$i]  = Tools::array2object($data[$i]);
			}
		}else{
			$data = $data[0];
			$data['URL'] = Myhtml::getinfourl($getinfoclass,$data);
			$data  = Tools::array2object($data);
		}
		
		$this -> data['pre_info'] = $data;
		return $data;
	}

	public function next_info($limit=1){
		if (isset($this -> data['next_info']))return $this -> data['next_info'];
		
		if ($this -> data['db_config']['sys_field']['posttime']){
			$orderby_field = $this -> data['db_config']['sys_field']['posttime'];
		}elseif ($this -> data['db_config']['sys_field']['posttime2']){
			$orderby_field = $this -> data['db_config']['sys_field']['posttime2'];
		}elseif ($this -> data['db_config']['sys_field']['id']){
			$orderby_field = $this -> data['db_config']['sys_field']['id'];
		}else{
			$orderby_field = 'id';
		}
		
		$where = array($orderby_field.'>'=>$this -> data['info'][$orderby_field]);
		if ($this -> data['db_config']['sys_field']['class_id']){
			$where[$this -> data['db_config']['sys_field']['class_id']] = $this -> data['info'][$this -> data['db_config']['sys_field']['class_id']];
			$getinfoclass = $this -> data['class_id'];
		}else{
			$getinfoclass = $this -> data['db_name'];
		}
		$data = Myqee::db() -> from($this -> data['db_name']) -> where ($where) -> orderby($orderby_field,'ASC') -> limit($limit) -> get() -> result_array ( FALSE );
		
		if ($limit>1){
			$count_data = count($data);
			for($i=0;$i<$count_data;$i++){
				$data[$i]['URL'] = Myhtml::getinfourl($getinfoclass,$data[$i]);
				$data[$i]  = Tools::array2object($data[$i]);
			}
		}else{
			$data = $data[0];
			$data['URL'] = Myhtml::getinfourl($getinfoclass,$data);
			$data  = Tools::array2object($data);
		}
		$this -> data['next_info'] = $data;
		return $data;
	}
	
	public function likeinfo($tpl=NULL,$limit=10,$offset=0,$isecho=false){
		if ($keyfield = $this -> data['db_config']['sys_field']['keyword']){
			$keyword = trim(str_replace('\'','',$this -> data['info'][$keyfield]),' ');
			if (!empty($keyword))$keywords = explode(' ',$keyword);
		}
		if (!$keywords){
			if ($tpl){
				return '';
			}else{
				return array();
			}
		}
		
		$data = Myqee::db() -> from($this -> data['db_name']);
		if ($idfield = $this -> data['db_config']['sys_field']['id']){
			$data = $data -> where($idfield.'!=',$this -> data['info'][$idfield]);
		}
		$sql = $data -> compile();
		unset($data);
		
		$count_k = count($keywords);
		$sql .= ' AND (';
		for ($i=0; $i<$count_k;$i++){
			$sql .= ($i==0?'':'OR ').'`'.$keyfield.'` LIKE \'%'.$keywords[$i] .'%\' ';
		}
		$sql .= ') ';
		if ($idfield){
			$sql .= 'order by `'.$idfield.'` DESC ';
		}
		$sql .= 'limit '.$offset.','.$limit;
		$data = Myqee::db() -> query($sql) -> result_array ( FALSE );
		
		$c =count($data);
		for($i=0;$i<$c;$i++){
			$data[$i]['URL'] = Myhtml::getinfourl(
				$this -> data['db_config']['sys_field']['class_id']?$data[$i][$this -> data['db_config']['sys_field']['class_id']]:$this -> data['db_name'],
				$data[$i]
			);
		}
//		return $sql;
		//return $data;
		if (!$tpl){
			return $data;
		}else{
			$data =  Createhtml::createhtml($tpl,NULL,$data);
			if ($isecho){
				echo $data;
				return TRUE;
			}else{
				return $data;
			}
		}
	}

	public function totalurl($returnhtml=false,$defer=true){
		$url = Myqee::url('myinfo/total/'.substr(Des::Encrypt('d='.$this -> data['db_name'].'&i='.$this -> data['id'].'&o=1&f='.($defer?'1':'0')),2),false,'.js');
		if ($returnhtml){
			return '<script type="text/javascript"'.($defer?' defer="defer"':'').' src="'.$url.'"></script>';
		}else{
			return $url;
		}
	}
}


class _myqee_view_forclass extends _myqee_view_create{
	public function __construct($group='default'){
		parent::__construct($group);
	}
	public function page($type=null){
		echo Myhtml::page($this -> data['page'],$this -> data['allpage'],$this -> data['listpage']);
	}
	
	public function totalurl(){
		return Myqee::url('myclass/total/'.substr(Des::Encrypt('classid='.$this -> data['class_id'].'&mid='.$this -> data['model_id'].'&ok=ok'),2),false,'.js');
	}
}

class _myqee_view_forlist extends _myqee_view_create{
	public function __construct($group='default'){
		parent::__construct($group);
	}
	public function page($type=null){
		echo Myhtml::page($this -> data['page'],$this -> data['allpage'],$this -> data['listpage']);
	}
}


class _myqee_view_create{
	protected $data = array();
	protected $_group_id;
	
	public function __construct($group='default'){
		$this -> _group_id = $group;
	}
	public function _myqee_load_view($__myqee_view_filename_, $__myqee_input_data_){
		if ($__myqee_view_filename_ == '')
			return;

		// Buffering on
		ob_start();
		$__ER_ = error_reporting(7);
		
		$this -> data = $__myqee_input_data_;
		unset($__myqee_input_data_);
		
		// Import the view variables to local namespace
		//extract($this -> data, EXTR_SKIP);
		if (is_array($this -> data) && count($this -> data)){
			foreach ($this -> data as $__k_ => $__v_){
				if(is_string($__k_)){
					$$__k_ =& $this -> data[$__k_];
				}
			}
			unset($__k_,$__v_);
		}		

		// Views are straight HTML pages with embedded PHP, so importing them
		// this way insures that $this can be accessed as if the user was in
		// the controller, which gives the easiest access to libraries in views
		//set_error_handler(array('myqeetohtml', 'exception_handler'));

		include $__myqee_view_filename_;

		error_reporting($__ER_);
		// Fetch the output and close the buffer
		return ob_get_clean();
	}
	
	protected function view($view,$data=null,$isrender=true){
		return View::factory($view,$data,null,$this->_group_id) -> render($isrender);
	}
	
	protected function location($classid=NULL,$myclass=NULL){
		if (!$classid)$classid=$this -> data['class_id'];
		return Createhtml::get_location_array($classid,$myclass);
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
	
	protected function tag($id=0){
		echo 'tag test';
	}
	protected function page(){
		return '';
	}
	
}
