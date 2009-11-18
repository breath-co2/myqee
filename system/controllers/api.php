<?php
class Api_Controller_Core extends Controller {
	public $ucenterapi;
	public function __construct(){
		parent::__construct();
		$this -> ucenterapi = Ucenter_Api::instance(false);
		
		define('API_DELETEUSER', 1);
		define('API_RENAMEUSER', 1);
		define('API_GETTAG', 1);
		define('API_SYNLOGIN', 1);
		define('API_SYNLOGOUT', 1);
		define('API_UPDATEPW', 1);
		define('API_UPDATEBADWORDS', 1);
		define('API_UPDATEHOSTS', 1);
		define('API_UPDATEAPPS', 1);
		define('API_UPDATECLIENT', 1);
		define('API_UPDATECREDIT', 1);
		define('API_GETCREDITSETTINGS', 1);
		define('API_GETCREDIT', 1);
		define('API_UPDATECREDITSETTINGS', 1);
		
		define('API_RETURN_SUCCEED', '1');
		define('API_RETURN_FAILED', '-1');
		define('API_RETURN_FORBIDDEN', '-2');
	}
	public function uc(){
		if(!defined('IN_UC')) {
			$get = $post = array();
			$code = @$_GET['code'];
			parse_str(_authcode($code, 'DECODE', UC_KEY), $get);
			if(MAGIC_QUOTES_GPC) {
				$get = _stripslashes($get);
			}
			
			$timestamp = $_SERVER['REQUEST_TIME'];
			if(empty($get)) {
				exit('Invalid Request');
			} elseif($timestamp - $get['time'] > 3600) {
				exit('Authracation has expiried');
			}
			$action = $get['action'];
			
			$this -> ucenterapi -> load_files('lib/xml.class.php');
			$post = xml_unserialize(file_get_contents('php://input'));
		
			if(in_array($get['action'], array('test', 'deleteuser', 'renameuser', 'gettag', 'synlogin', 'synlogout', 'updatepw', 'updatebadwords', 'updatehosts', 'updateapps', 'updateclient', 'updatecredit', 'getcreditsettings', 'updatecreditsettings', 'addfeed'))) {
				echo $this -> ucenterapi -> $get['action']($get, $post);
			} else {
				echo API_RETURN_FAILED;
			}
		}
	}
	
	public function _default(){
		$this -> uc();
	}
}


function _authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	$ckey_length = 4;

	$key = md5($key ? $key : UC_KEY);
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + $_SERVER['REQUEST_TIME'] : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);

	$result = '';
	$box = range(0, 255);

	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - $_SERVER['REQUEST_TIME'] > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
				return '';
			}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}

}

function _stripslashes($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = _stripslashes($val);
		}
	} else {
		$string = stripslashes($string);
	}
	return $string;
}