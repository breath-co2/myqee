<?php defined('MYQEEPATH') or die('No direct script access.');
 class Passport_Core {
 	protected static $key;
 	
 	/**
 	 * 检测用户登陆
 	 *
 	 * 若用户登陆超时或需要更新COOKIE，将会将登陆用的HTML传递到$GLOBALS['set_login_html']全局变量里，供页面调用
 	 * @param boolean $autoreturnuid 是否返回用户ID
 	 * @return $uid/$loginhtml/false
 	 */
	public static function check_islogin($autoreturnuid = false){
		$uid = (int)Myqee::get_cookie('uid');
		$username = Myqee::get_cookie('uname');
		$login_time = Myqee::get_cookie('ltime');
		$code = Myqee::get_cookie('code');
		if (!$uid || !$login_time || !$code || !$username){
			return false;
		}
		if (!self::check_code($uid,$login_time,$code) ){
			//清除COOKIE
			$loginhtml = self::set_logout();
			$GLOBALS['login_html'] = $loginhtml;
			return false;
		}
		$time = $_SERVER['REQUEST_TIME'];
		if ( $time - $login_time > 1800 ){
			//超时处理
			$autologin = Myqee::get_cookie('autologin');
			if ($autologin && self::get_code($code,$login_time)==$autologin){
				//若为自动登陆，且检测数据匹配
				$loginhtml = self::set_login($uid,$username,true);
				$GLOBALS['set_login_html'] = $loginhtml;
				if ($autoreturnuid){
					return $uid;
				}else{
					return $loginhtml;
				}
			}else{
				//清除COOKIE
				$loginhtml = self::set_logout();
				$GLOBALS['set_login_html'] = $loginhtml;
				return false;
			}
		}elseif ($time - $login_time > 600 ){
			//每10分钟更新一次COOKIE
			$loginhtml = self::set_login($uid,$username,true);
			$GLOBALS['login_html'] = $loginhtml;
			if ($autoreturnuid){
				return $uid;
			}else{
				return $loginhtml;
			}
		}
		return $uid;
	}
	
	/**
	 * 获取已登陆用户ID
	 * 若用户登陆超时或未登陆，将返回false
	 *
	 * @return unknown
	 */
	public static function get_loginuid(){
		return self::check_islogin(true);
	}
	
	public static function check_code($uid,$login_time,$code){
		if (!empty($code) && self::get_code($uid,$login_time) == $code  ){
			return true;
		}else{
			return false;
		}
	}
	
	public static function get_code($info,$time){
		self::$key or self::$key = Myqee::config ('encryption.default.key');
		return md5(self::$key.'_#$2_26_'.$info.'_dgdw7we_'.$time);
	}
	
	public static function get_userinfo($uid,$dbname = null){
		$memberdb = Myqee::config('core.member_db');
		$memberdb or $memberdb = 'members';
		$data = Myqee::db($dbname) -> getwhere($memberdb,array('id'=>$uid)) -> result_array(FALSE);
		$data = $data[0];
		return $data;
	}
	
	public static function set_login($uid,$username,$autologin=false){
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		
		$uid = (int)$uid;

		$time = $_SERVER['REQUEST_TIME'];
		if ($autologin){
			$settime = 99999999;
		}else{
			$settime = NULL;
		}
		$code = self::get_code($uid,$time);
		Myqee::create_cookie('uid',$uid,$settime);
		Myqee::create_cookie('uname',$username,$settime);
		Myqee::create_cookie('code',$code,$settime);
		Myqee::create_cookie('ltime',$time,$settime);
		if ($autologin){
			Myqee::create_cookie('autologin',self::get_code($code,$time,$settime),$settime);
		}
		// ucenter api
		if (Myqee::config('core.use_ucenter')){
			new Ucenter_Api(true);
			return uc_user_synlogin($uid);
		}
		return true;
	}
	
	public static function set_logout(){
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		
		Myqee::delete_cookie('uid');
		Myqee::delete_cookie('uname');
		Myqee::delete_cookie('code');
		Myqee::delete_cookie('ltime');
		Myqee::delete_cookie('autologin');
		if (Myqee::config('core.use_ucenter')){
			new Ucenter_Api(true);
			return uc_user_synlogout();
		}
		return '';
	}
}
