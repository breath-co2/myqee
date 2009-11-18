<?php
/**
 * File helper class.
 *
 * $Id: Plugins.php,v 1.1 2009/09/11 07:52:49 jonwang Exp $
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
		return MyqeeCMS::config('plugins/'.self::$plugins.($key?'.'.$key:''));
	}
	
} // End Plugins