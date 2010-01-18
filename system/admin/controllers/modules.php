<?php
/**
 * $Id$
 *
 * @package    Plugins Controller
 * @author     Myqee Team
 * @copyright  (c) 2008-2010 Myqee Team
 * @license    http://Myqee.com/license.html
 */
class Modules_Controller_Core extends Controller {

	protected $config = NULL;
	
	protected $modules;

	function __construct() {
		parent::__construct();
		$this -> session = Passport::chkadmin();
	}

	public function _default() {
		$modules = __ERROR_FUNCTION__;
		if (!$modules) {
			MyqeeCMS::show_error('不存在指定的模块！');
		}
		$this -> modules = $modules;
		
		if (!$path = $this->_get_modules()) {
			Event::run('system.404');
		}
		$html = myqee_root::sub_controller( true , $path ,true );
		
		echo $html;
	}
	

	protected function _get_modules() {
		static $pluginspath = null;

		if (!$this -> modules)return false;
		if ($pluginspath!==null)return $pluginspath;

		$modules = $this -> modules;

		$pluginspath = array();
		if(is_dir($path = MODULEPATH.$modules.'/admin/')) {
			$pluginspath['z'] = $path;
		}
		if(is_dir($path = ADMINPATH.'modules/'.$modules.'/')) {
			$pluginspath['y'] = $path;
		}
		if(is_dir($path = MYQEEPATH.'modules/'.$modules.'/admin/')) {
			$pluginspath['w'] = $path;
		}
		return $pluginspath;
	}
}
