<?php
/**
 * File helper class.
 *
 * $Id: Plugins.php,v 1.1 2009/11/04 08:07:34 songwubin Exp $
 *
 * @package    Core
 * @author     Myqee Team
 * @copyright  (c) 2007-2008 Myqee Team
 * @license    http://myqee.com/license.html
 */
class Plugins_Core {
	
	protected static $plugins;
	
	public static function instance($plugins) {
		static $instance;
		
		// Create the instance if it does not exist
		($instance === NULL) and $instance = new Plugins($plugins);
		
		return $instance;
	}
	
	public function __construct($plugins) {
		self::$plugins = $plugins;
	}
	
	public static function config($key=''){
		if (!self::$plugins)return false;
		return Myqee::config('plugins/'.self::$plugins.($key?'.'.$key:''));
	}
	
	public static function api ($plugins='',$apiname='') {
		$paths = array();
		$paths[] = MYAPPPATH.'plugins/'.$plugins.'/api/'.$apiname.EXT;
		$paths[] = MYQEEPATH.'plugins/'.$plugins.'/api/'.$apiname.EXT;
		$api = null;
		foreach ($paths as $file) {
			if (file_exists($file)) {
				include $file;
				if (class_exists($plugins.'_Api_Core')) {
					$classname = $plugins.'_Api_Core';
					$api = new $classname;
				}
			}
		}
		return $api;
	}
} // End Plugins