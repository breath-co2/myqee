<?php defined('MYQEEPATH') or die('No direct script access.');

/**
 * ucenter api
 *
 * $Id$
 *
 * @package    Core
 * @author     Myqee Team
 * @copyright  (c) 2008-2009 Myqee Team
 * @license    http://myqee.com/license.html
 */
class Ucenter_Api_Core {
	protected static $instance;
	protected $config;

	protected $loadedfiles = array();
	
	protected $db;
	protected $memberdb;
	/**
	 * Singleton instance of session.
	 */
	public static function instance($autoload_uc=true)
	{
		if (self::$instance == NULL)
		{
			// Create a new instance
			self::$instance = new Ucenter_Api($autoload_uc);
		}
		return self::$instance;
	}
	
	public function __construct($autoload_uc=true){
		if (defined('UC_CONNECT')){
			if ($autoload_uc){
				$this -> load_client();
			}
			return;
		}
		$ucconfig = Myqee::config('ucconfig');
		$this -> config = $ucconfig;
		if (is_array($ucconfig['db'])){
			$ucdbconfig = $ucconfig['db'];
		}else{
			$ucdbconfig = Myqee::config('database.'.$ucconfig['db']);
		}
		$this -> db = Database::instance($ucdbconfig);
		
		//本系统用户表名
		$memberdb = Myqee::config('core.member_db');
		$memberdb or $memberdb = 'members';
		$this -> memberdb = $memberdb;
		
		if (!$ucdbconfig){
			Myqee::show_error('配置错误，请联系管理员！');
		}
		define('UC_CONNECT', 'mysql');
		define('UC_DBHOST', $ucdbconfig['connection']['host']);
		define('UC_DBUSER', $ucdbconfig['connection']['user']);
		define('UC_DBPW', $ucdbconfig['connection']['pass']);
		define('UC_DBNAME', $ucdbconfig['connection']['database']);
		define('UC_DBCHARSET', $ucdbconfig['character_set']);
		define('UC_DBTABLEPRE', '`'.$ucdbconfig['connection']['database'].'`.'.$ucdbconfig['table_prefix']);
		define('UC_DBCONNECT', $ucconfig['dbconnect']);
		define('UC_KEY', $ucconfig['key']);
		define('UC_API', $ucconfig['api']);
		define('UC_CHARSET',$ucdbconfig['character_set']=='utf8'?'utf-8':$ucdbconfig['character_set']);
		define('UC_IP', $ucconfig['ip']);
		define('UC_APPID', $ucconfig['appid']);
		define('UC_PPP', $ucconfig['ppp']);
		if ($autoload_uc){
			$this -> load_client();
		}
	}
	
	public function load_client(){
		if (!defined('IN_UC')){
			$ucapifilepath = MYQEEPATH . 'api/uc_client/client.php';
			include $ucapifilepath;
		}
	}
	
	public function load_files($file){
		if (!$loadedfiles[$file]){
			if ( include(MYQEEPATH.'api/uc_client/' . ltrim($file,'/.\\'))){
				return true;
			}else{
				return FALSE;
			}
		}else{
			return true;
		}
	}

	public function deleteuser($get, $post = NULL){
		$this -> db -> query("DELETE FROM {$this -> db -> table_prefix()}{$this -> memberdb} WHERE uid IN ($get[ids])");
		return API_RETURN_SUCCEED;
	}
	
	public function renameuser($get, $post = NULL) {
		$uid = $get['uid'];
		$usernamenew = $get['newusername'];
		
		if ($this -> db -> merge($this -> memberdb,array('id'=>$uid,'username'=>$usernamenew)) -> count()){
			return API_RETURN_SUCCEED;
		}else{
			return API_RETURN_FAILED;
		}
	}
	
	
	public function test($get, $post = NULL) {
		return API_RETURN_SUCCEED;
	}
	
	public function updatepw($get, $post = NULL) {
		$uid = $get['uid'];
		$username = $get['username'];
		$password = md5($get['password']);
		if ($this -> db -> merge($this ->memberdb,array('id'=>$uid,'password'=>$password,'username'=>$username)) -> count()){
			return API_RETURN_SUCCEED;
		}else{
			return API_RETURN_FAILED;
		}
	}
	
	
	public function synlogin($get, $post = NULL) {
		$uid = $get['uid'];
		$username = $get['username'];
		if(!API_SYNLOGIN) {
			return API_RETURN_FORBIDDEN;
		}

		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		$uid = intval($uid);
		
		if (!Passport::get_userinfo($uid)){
			self::load_client();
			$memberdb = Myqee::config('core.member_db');
			$memberdb or $memberdb = 'members';
			$data = uc_get_user($uid,true);
			Myqee::db() -> merge($memberdb,array('id'=>$uid,'username'=>$username,'email'=>$data[2],'password'=>$get['password']));
		}

		Passport::set_login($uid,$username);
		return API_RETURN_SUCCEED;
	}
	

	public function synlogout($get, $post = NULL) {
		if(!API_SYNLOGOUT) {
			return API_RETURN_FORBIDDEN;
		}

		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		
		Passport::set_logout();
		return API_RETURN_SUCCEED;
	}
	
	
	public function updatecreditsettings($get, $post = NULL) {
		
		if(!API_UPDATECREDITSETTINGS) {
			return API_RETURN_FORBIDDEN;
		}
		
		$outextcredits = array();
		if (is_array($get['credit'])){
			foreach($get['credit'] as $appid => $credititems) {
				if($appid == UC_APPID) {
					foreach($credititems as $value) {
						$outextcredits[$value['appiddesc'].'|'.$value['creditdesc']] = array(
							'creditsrc' => $value['creditsrc'],
							'title' => $value['title'],
							'unit' => $value['unit'],
							'ratio' => $value['ratio']
						);
					}
				}
			}
		}
		
		//Myqee::db() ->query ("REPLACE INTO cdb_settings (variable, value) VALUES ('outextcredits', '".addslashes(serialize($outextcredits))."');");
		
		return API_RETURN_SUCCEED;
	}
	
	public function updateapps($get ,$post){
		return API_RETURN_SUCCEED;
	}
}