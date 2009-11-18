<?php
class Plugins_Controller_core extends Controller {
	
	public function run($plugins=''){
		//Passport::checkallow('plugins.list',NULL,$showheader?FALSE:TRUE);
		$this -> plugins = $plugins;

		if (!$path = $this->_get_plugins()) {
			//MyqeeCMS::show_error('不存在指定的插件！',$showheader?FALSE:TRUE,'goback');
		}
		
		$plugins_config = Myqee::config('plugins.'.$plugins);
		if (!$plugins_config || !$plugins_config['isuse']){
			//MyqeeCMS::show_error('指定的插件未启用！',$showheader?FALSE:TRUE,'goback');
		}
//		Plugins::instance($plugins);
		
		//定义插件路径
		define('PLUGINS_PATH',$plugins);
		$result = myqee_root::sub_controller(true,$path,false,1);
		if (defined('PLUGINS_SHOWSELF') && PLUGINS_SHOWSELF==true){
			while ( ob_get_level() ) {
				ob_end_clean ();
			}
			echo $result;
			return true;
		} else {
			echo $result;
		}
		return true;		
	}

	protected function _get_plugins($plugins=null) {
		$plugins or $plugins = $this -> plugins;
		if (!$plugins)return false;
		
		static $pluginspath = null;
		if ($pluginspath!==null)return $pluginspath;

		

		$pluginspath = array();
		if(is_dir($path = MYAPPPATH.'plugins/'.$plugins.'/')) {
			$pluginspath['q'] = $path;
		}
		if(is_dir($path = MYQEEPATH.'plugins/'.$plugins.'/')) {
			$pluginspath['p'] = $path;
		}
		return $pluginspath;
	}
}