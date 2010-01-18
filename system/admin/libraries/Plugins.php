<?php
/**
 * File helper class.
 *
 * $Id$
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
	
} // End Plugins