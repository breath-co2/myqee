<?php
defined ( 'MYQEEPATH' ) or die ( 'No direct script access.' );

/**
 * 工具集
 *
 * $Id$
 *
 * @package    Core
 * @author     Myqee Team
 * @copyright  (c) 2008-2009 Myqee Team
 * @license    http://myqee.com/license.html
 */
class Tools_Core {
	
	public  static function getweek($time) {
		switch (date ( 'D', $time )) {
			case "Mon" :
				echo "星期一";
				break;
			case "Tue" :
				echo "星期二";
				break;
			case "Wed" :
				echo "星期三";
				break;
			case "Thu" :
				echo "星期四";
				break;
			case "Fri" :
				echo "星期五";
				break;
			case "Sat":
				echo "星期六";
				break;
			case "Sun":
				echo "星期日";
				break;
		}
	}
	
	public static function createfile($file, $data) {
		if (@file_put_contents ( $file, $data )) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * 循环建立目录
	 * @param string $dir 待创建的文件夹
	 * @param boolean $createdefaulfile 对于新创建的文件夹，是否自动创建空默认页
	 * @return boolean true/false
	 */
	public function create_dir($dir,$createdefaulfile = true)
	{
		if (!is_dir($dir))
		{
			$temp = explode('/',$dir);
			$cur_dir = "";
			for($i=0;$i<count($temp);$i++)
			{
				$cur_dir .= $temp[$i]."/";
				if (!is_dir($cur_dir))
				{
					if (mkdir($cur_dir,0777)){
						if($createdefaulfile)self::createfile($cur_dir.'index.html',' ');
					}else{
						return false;
					}
				}
			}
		}
		return true;
	}

	/**
	 * 循环删除目录下的所有目录和文件
	 * @param string $dirName
	 * @return Null
	 */
	public static function remove_dir($dirName)
	{
		if(!is_dir($dirName))
		{
			return false;
		}
		
		$realpath = str_replace('\\','/',realpath($dirName)) .'/';
		if (!$realpath || $realpath==WWWROOT){
			//wwwroot不允许删除的~
			return false;
		}
		//只允许删除wwwroot和MYAPPPATH的cache,logs,temp里面的这些目录
		if (!preg_match("/^(".str_replace('/','\\/',(WWWROOT))."|".str_replace('/','\\/',MYAPPPATH)."(cache\/|logs\/|temp\/|data\/).+).+$/i",$realpath)){
			return false;
		}
		
		$handle = opendir($dirName);
		while(($file = readdir($handle)) !== false)
		{
			if($file != '.' && $file != '..')
			{
				$dir = $dirName . DIRECTORY_SEPARATOR . $file;
				is_dir($dir) ? self::remove_dir($dir) : @unlink($dir);
			}
		}
		closedir($handle);
		@rmdir($dirName);
	}

	/**
	 * 转移目录下的所有目录和文件
	 * @param string $fromdir  源文文件目录
	 * @param string $todir  目标文件目录
	 * @param boolean $autocoverageold 是否覆盖已有文件，true覆盖，false跳过
	 * @return array($dook,$doerror)
	 */
	public static function move_dir($fromdir,$todir,$autocoverageold=true){
		$fromdir = rtrim($fromdir,'/').'/';
		$todir = rtrim($todir,'/').'/';
		if (!is_dir($fromdir)||$fromdir==$todir)return false;
		$files = glob($fromdir.'*');
		$donum = array(0,0);
		foreach($files as $path){
			$tofile = $todir.basename($path);
			if (is_dir($path)){
				self::create_dir($tofile);
				$donum2 = self::move_dir($path,$tofile,$autocoverageold);
				if ($donum2){
					$donum[0] += $donum2[0];
					$donum[1] += $donum2[1];
				}
			}else{
				if ($autocoverageold && file_exists($tofile)){
					//覆盖已有文件
					unlink($tofile);
				}
				if (rename($path,$tofile)){
					$donum[0] ++;
				}else{
					$donum[1] ++;
				}
			}
		}
		//移除旧目录
		self::remove_dir($fromdir);
		return $donum;
	}

	public static function get_rand($len = 10) {
		$chr = '0,1,2,3,4,5,6,7,8,9,a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z,A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z';
		$arr = explode(',', $chr);
		shuffle($arr);
		$str = implode('', $arr);
		$ran = rand(0, count($arr) - $len);
		$rand = substr($str, $ran, $len);
		return $rand;
	}
	
	public static function formatids($allid = 0, $return_string = false) {
		$theID = array ();
		if (! empty ( $allid )) {
			$allid = preg_replace ( "/[^0-9,]+/", '', $allid );
			$idArr = explode ( ',', $allid );
			foreach ( $idArr as $v ) {
				if ($v > 0) {
					$theID [] = (int)$v;
				}
			}
		}
		if (! $return_string == false) {
			if (is_string ( $return_string )) {
				$theID = join ( $theID, $return_string );
			} else {
				$theID = join ( $theID, ',' );
			}
		}
		return $theID;
	}
	
	/**
	 * 格式化字符串
	 *
	 * @param string $string
	 * @param string $length 截取长度，默认0，即不截取
	 * @param boolean $in_slashes 是否有slashes，默认否
	 * @param boolean $out_slashes 输出是否添加slashes，默认否
	 * @param boolean $censor 是否词语屏蔽，默认否
	 * @param boolean $bbcode 是否UBB转换，默认否
	 * @param -1/0/1 $html -1表示过滤HTML标签，0表示转换HTML标签为文本，1表示不处理，默认1
	 * @return string $string 处理后的字符串
	 */
	public static function formatstr($string, $length=0, $in_slashes=0, $out_slashes=0, $censor=0, $ubbcode=0, $html=1) {
		$string = trim($string);
		if($in_slashes) {
			//传入的字符有slashes
			$string = self::sstripslashes($string);
		}
		if($html < 0) {
			//去掉html标签
			$string = preg_replace("/(\<[^\<]*\>|\r|\n|\s|\[.+?\])/is", ' ', $string);
			$string = self::shtmlspecialchars($string);
		} elseif ($html == 0) {
			//转换html标签
			$string = self::shtmlspecialchars($string);
		}
		if($censor) {
			//词语屏蔽
			$censor = Myqee::config('censor');
			if($censor['banned'] && preg_match($censor['banned'], $string)) {
				Myqee::show_error('information_contains_the_shielding_text');
			} else {
				$string = empty($censor['filter']) ? $string :
					@preg_replace($censor['filter']['find'], $censor['filter']['replace'], $string);
			}
		}
		if($length && self::strlen($string) > $length) {
			//截断字符
			$string = self::substr($string,0,$length);
		}
		if($ubbcode) {
			$string = self::ubbcode($string, $bbcode);
		}
		if($out_slashes) {
			$string = self::saddslashes($string);
		}
		return trim($string);
	}
	
	/**
	 * 返回 JOSN 字符串
	 * 与自带的json_encode函数不同的是，此函数不转换中文字符串，所以此函数效率相对高些
	 *
	 * @param array $a 数组
	 * @return string 经过JSON序列化后的字符串
	 */
	public static function json_encode($a) {
//		return json_encode($a);
		if (is_null ( $a ))
			return 'null';
		if ($a === false)
			return 'false';
		if ($a === 1)
			return '1';
		if ($a === true)
			return 'true';
		if (is_scalar ( $a )) {
			$a = addslashes ( $a );
			$a = str_replace ( "\\'", "'", $a );
			$a = str_replace ( "\n", '\n', $a );
			$a = str_replace ( "\r", '\r', $a );
			$a = str_replace ( "/", '\\/', $a );
			//$a = preg_replace ( '{(</)(script)}i', "$1'+'$2", $a );
			if (is_string ( $a )) {
				return '"' . $a . '"';
			} else {
				return $a;
			}
		}
		$isList = true;
		for($i = 0, reset ( $a ); $i < count ( $a ); $i ++, next ( $a ))
			if (key ( $a ) !== $i) {
				$isList = false;
				break;
			}
		$result = array ();
		if ($isList) {
			foreach ( $a as $v )
				$result [] = self::json_encode ( $v );
			return '[' . join ( ',', $result ) . ']';
		} else {
			foreach ( $a as $k => $v )
				$result [] = self::json_encode ( $k ) . ':' . self::json_encode ( $v );
			return '{' . join ( ',', $result ) . '}';
		}
	}
	
	public static function json_decode($s){
		return json_decode($s,true);
	}
	
	/**
	 * 将字符串、数组等压缩后加密
	 *
	 * @param string/array $data
	 * @param string $key
	 * @return string $mydata
	 */
	public static function info_encryp($mydata,$key = '',$gzcompress=true,$autowordwrap=64) {
		if($gzcompress)$mydata = gzcompress ( serialize ( $mydata ), 9 );
		$mydata = $key . '-->-->' . md5 ( $mydata ) . $mydata;
		$mydata = Encryption::encode ( $mydata , $key );
		if ($autowordwrap>0)$mydata = wordwrap ( $mydata, $autowordwrap, "\r\n", true );
		return $mydata;
	}
	
	/**
	 * 解析加密数据文件
	 *
	 * @param string $str 加密字符串
	 * @param string $key 解析的密码
	 * @return array $mydata 解析的数据
	 */
	public static function info_uncryp($str,$key='',$gzcompress=true){
		$mydata = Encryption::decode ( $str, $key ); //反解析
		$keylen = strlen ( $key ) + 6;
		
		if (substr ( $mydata, 0, $keylen ) != $key . '-->-->') {
			//解析失败
			return false;
		}
		
		$md5key = substr ( $mydata, $keylen, 32 ); //获取32位长的MD5值
		$mydata = substr ( $mydata, $keylen + 32 ); //获取正文内容
		if ($md5key != md5 ( $mydata )) {
			//如果验证结果不一样说明文件受损，返回提示
			return - 1;
		}
		if ($gzcompress)$mydata = gzuncompress($mydata);
		$mydata = unserialize($mydata);
		return $mydata;
	}
	
	public static function is_ascii($str) {
		return ! preg_match ( '/[^\x00-\x7F]/S', $str );
	}
	
	public static function strlen($str) {
		// Try mb_strlen() first because it's faster than combination of is_ascii() and strlen()
		if (SERVER_UTF8)
			return mb_strlen ( $str );
		
		if (self::is_ascii ( $str ))
			return strlen ( $str );
		
		return strlen ( utf8_decode ( $str ) );
	}
	
	public static function substr($str, $offset, $length = NULL) {
		if (SERVER_UTF8) {
			return ($length === NULL) ? mb_substr ( $str, $offset ) : mb_substr ( $str, $offset, $length );
		}
		
		if (self::is_ascii ( $str ))
			return ($length === NULL) ? substr ( $str, $offset ) : substr ( $str, $offset, $length );
			
		// Normalize params
		$str = ( string ) $str;
		$strlen = self::strlen ( $str );
		$offset = ( int ) ($offset < 0) ? max ( 0, $strlen + $offset ) : $offset; // Normalize to positive offset
		$length = ($length === NULL) ? NULL : ( int ) $length;
		
		// Impossible
		if ($length === 0 or $offset >= $strlen or ($length < 0 and $length <= $offset - $strlen))
			return '';
			
		// Whole string
		if ($offset == 0 and ($length === NULL or $length >= $strlen))
			return $str;
			
		// Build regex
		$regex = '^';
		
		// Create an offset expression
		if ($offset > 0) {
			// PCRE repeating quantifiers must be less than 65536, so repeat when necessary
			$x = ( int ) ($offset / 65535);
			$y = ( int ) ($offset % 65535);
			$regex .= ($x == 0) ? '' : '(?:.{65535}){' . $x . '}';
			$regex .= ($y == 0) ? '' : '.{' . $y . '}';
		}
		
		// Create a length expression
		if ($length === NULL) {
			$regex .= '(.*)'; // No length set, grab it all
		} // Find length from the left (positive length)
		elseif ($length > 0) {
			// Reduce length so that it can't go beyond the end of the string
			$length = min ( $strlen - $offset, $length );
			
			$x = ( int ) ($length / 65535);
			$y = ( int ) ($length % 65535);
			$regex .= '(';
			$regex .= ($x == 0) ? '' : '(?:.{65535}){' . $x . '}';
			$regex .= '.{' . $y . '})';
		} // Find length from the right (negative length)
		else {
			$x = ( int ) (- $length / 65535);
			$y = ( int ) (- $length % 65535);
			$regex .= '(.*)';
			$regex .= ($x == 0) ? '' : '(?:.{65535}){' . $x . '}';
			$regex .= '.{' . $y . '}';
		}
		
		preg_match ( '/' . $regex . '/us', $str, $matches );
		return $matches [1];
	}
	
	/*等同js脚本里的escape函数*/
	public static function escape($str, $encode = "UTF-8") {
		if ($encode=='UTF-8'){
			preg_match_all("/[\xC0-\xE0].|[\xE0-\xF0]..|[\x01-\x7f]+/",$str,$r);
		}else{
			preg_match_all("/[\x80-\xff].|[\x01-\x7f]+/",$str,$r);
		}
		//prt($r);
		$ar = $r[0];
		foreach($ar as $k => $v) {
			$ord = ord($v[0]);
			if( $ord<=128){
				$ar[$k] = rawurlencode($v);
			}else{
				$ar[$k] = "%u".bin2hex(iconv($encode,"UCS-2BE",$v));
			}
		}//foreach
		return join("",$ar);
	}
	
	/*等同js脚本里的unescape函数*/
	public static function unescape($str, $encode = "UTF-8") {
		$encode = strtoupper ( $encode );
		if ($encode == 'GBK' || $encode == 'GB2312') {
			$substrStrNum = 2;
		} else {
			$substrStrNum = 3;
		}
		$str = rawurldecode ( $str );
		preg_match_all ( "/%u.{4}|&#x.{4};|&#\d+;|&#\d+?|.+/U", $str, $r );
		$ar = $r [0];
		foreach ( $ar as $k => $v ) {
			if (substr ( $v, 0, 2 ) == "%u")
				$ar [$k] = iconv ( "UCS-2BE", $encode, pack ( "H4", substr ( $v, - 4 ) ) );
			elseif (substr ( $v, 0, 3 ) == "&#x")
				$ar [$k] = iconv ( "UCS-2BE", $encode, pack ( "H4", substr ( $v, $substrStrNum, - 1 ) ) );
			elseif (substr ( $v, 0, 2 ) == "&#") {
				$ar [$k] = iconv ( "UCS-2BE", $encode, pack ( "n", preg_replace ( "/[^\d]/", "", $v ) ) );
			}
		}
		return join ( "", $ar );
	}
	
	public static function imageurl($url, $w = 0, $h = 0,$noimg = null) {
		if (! $url){
			if ($noimg){
				return $noimg;
			}else{
				return SITE_URL .'images/none.gif';
			}
		}
		if (substr($url,0,7)=='http://')return $url;
		$thefile = str_replace ( '//', '/', WWWROOT . $url );
		if (! file_exists ( $thefile )) {
			if ($w >= 10 && $w <= 400 && $h >= 10 && $h <= 300) {
				return self::_forimageurlstr ( $url, $w, $h );
			}
			return $url;
		} else {
			if (is_array ( ($imagesize = getimagesize ( $thefile )) )) {
				list ( $width, $height ) = $imagesize;
				if ($w >= 10 && $w <= 400 && $w < $width && $h >= 10 && $h <= 300 && $h < $height) {
					return self::_forimageurlstr ( $url, $w, $h );
				}
				if ($w >= 10 && $w <= 400 && ! ($h >= 10 && $h <= 300)) {
					//宽度符合条件，高度不符合
					if ($w >= $width)
						return $url;
					$h = ceil ( $height * ($w / $width) );
					return self::_forimageurlstr ( $url, $w, $h );
				} elseif (! ($w >= 10 && $w <= 400) && $h >= 10 && $h <= 300) {
					//宽度不符合条件，高度符合
					if ($h >= $height)
						return $url;
					$w = ceil ( $width * ($h / $height) );
					return self::_forimageurlstr ( $url, $w, $h );
				} else {
					return $url;
				}
			} else {
				return $url;
			}
		}
	}
	
	protected static function _forimageurlstr($url, $w = 0, $h = 0) {
		if ($w == 0 || $h == 0)
			return $url;
		$str = '_' . $w . '_' . $h;
		$urlarray = explode ( '.', $url );
		if (count ( $urlarray ) - 2 < 0)
			return $url;
		$urlarray [count ( $urlarray ) - 2] .= '_thumb' . $str;
		$newurl = implode ( '.', $urlarray );
		return $newurl;
	}
	
	public static function array2object($arr){
		foreach ($arr as $k => $v){
			$obj -> $k = $v;
		}
		return $obj;
	}
	
	
	/**
	 * Retrieves current user agent information:
	 * keys:  browser, version, platform, mobile, robot, referrer, languages, charsets
	 * tests: is_browser, is_mobile, is_robot, accept_lang, accept_charset
	 *
	 * @param   string   key or test name
	 * @param   string   used with "accept" tests: user_agent(accept_lang, en)
	 * @return  array    languages and charsets
	 * @return  string   all other keys
	 * @return  boolean  all tests
	 */
	public static function user_agent($key = 'agent', $compare = NULL)
	{
		static $info;
		static $user_agent;
		$user_agent or $user_agent = $_SERVER['HTTP_USER_AGENT'];

		// Return the raw string
		if ($key === 'agent')
			return $user_agent;

		if ($info === NULL)
		{
			// Parse the user agent and extract basic information
			$agents = Myqee::config('user_agents');

			foreach ($agents as $type => $data)
			{
				foreach ($data as $agent => $name)
				{
					if (stripos($user_agent, $agent) !== FALSE)
					{
						if ($type === 'browser' AND preg_match('|'.preg_quote($agent).'[^0-9.]*+([0-9.][0-9.a-z]*)|i', $user_agent, $match))
						{
							// Set the browser version
							$info['version'] = $match[1];
						}

						// Set the agent name
						$info[$type] = $name;
						break;
					}
				}
			}
		}

		if (empty($info[$key]))
		{
			switch ($key)
			{
				case 'is_robot':
				case 'is_browser':
				case 'is_mobile':
					// A boolean result
					$return = ! empty($info[substr($key, 3)]);
				break;
				case 'languages':
					$return = array();
					if ( ! empty($_SERVER['HTTP_ACCEPT_LANGUAGE']))
					{
						if (preg_match_all('/[-a-z]{2,}/', strtolower(trim($_SERVER['HTTP_ACCEPT_LANGUAGE'])), $matches))
						{
							// Found a result
							$return = $matches[0];
						}
					}
				break;
				case 'charsets':
					$return = array();
					if ( ! empty($_SERVER['HTTP_ACCEPT_CHARSET']))
					{
						if (preg_match_all('/[-a-z0-9]{2,}/', strtolower(trim($_SERVER['HTTP_ACCEPT_CHARSET'])), $matches))
						{
							// Found a result
							$return = $matches[0];
						}
					}
				break;
				case 'referrer':
					if ( ! empty($_SERVER['HTTP_REFERER']))
					{
						// Found a result
						$return = trim($_SERVER['HTTP_REFERER']);
					}
				break;
			}

			// Cache the return value
			isset($return) and $info[$key] = $return;
		}

		if ( ! empty($compare))
		{
			// The comparison must always be lowercase
			$compare = strtolower($compare);

			switch ($key)
			{
				case 'accept_lang':
					// Check if the lange is accepted
					return in_array($compare, self::user_agent('languages'));
				break;
				case 'accept_charset':
					// Check if the charset is accepted
					return in_array($compare, self::user_agent('charsets'));
				break;
				default:
					// Invalid comparison
					return FALSE;
				break;
			}
		}

		// Return the key, if set
		return isset($info[$key]) ? $info[$key] : NULL;
	}
	
	public static function get_ipinfo($ip=null,$utf8=true) {
		return ipinfo::convertip($ip,$utf8);
	}
	
	public static function ip_address(){
		return self::getonlineip();
	}
	
	public static function getonlineip() {
		$onlineip = '';
		if (getenv ( 'HTTP_CLIENT_IP' ) && strcasecmp ( getenv ( 'HTTP_CLIENT_IP' ), 'unknown' )) {
			$onlineip = getenv ( 'HTTP_CLIENT_IP' );
		} elseif (getenv ( 'HTTP_X_FORWARDED_FOR' ) && strcasecmp ( getenv ( 'HTTP_X_FORWARDED_FOR' ), 'unknown' )) {
			$onlineip = getenv ( 'HTTP_X_FORWARDED_FOR' );
		} elseif (getenv ( 'REMOTE_ADDR' ) && strcasecmp ( getenv ( 'REMOTE_ADDR' ), 'unknown' )) {
			$onlineip = getenv ( 'REMOTE_ADDR' );
		} elseif (isset ( $_SERVER ['REMOTE_ADDR'] ) && $_SERVER ['REMOTE_ADDR'] && strcasecmp ( $_SERVER ['REMOTE_ADDR'], 'unknown' )) {
			$onlineip = $_SERVER ['REMOTE_ADDR'];
		}
		return $onlineip;
	}
	
	
	
	/**
	 * 去掉slassh
	 *
	 * @param string/array $string
	 * @return string/array $string
	 */
	public static function sstripslashes($string) {
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = self::sstripslashes($val);
			}
		} else {
			$string = stripslashes($string);
		}
		return $string;
	}
	
	/**
	 * 取消HTML代码
	 *
	 * @param string $string
	 * @return string $string
	 */
	public static function shtmlspecialchars($string) {
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = self::shtmlspecialchars($val);
			}
		} else {
			$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4})|[a-zA-Z][a-z0-9]{2,5});)/', '&\\1',
				str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string));
		}
		return $string;
	}
	
	public static function saddslashes($string) {
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = self::saddslashes($val);
			}
		} else {
			$string = addslashes($string);
		}
		return $string;
	}
	
	/**
	 * 解析UBB代码
	 *
	 * @param string $message
	 * @param boolean $parseurl
	 * @return string $string
	 */
	public static function ubbcode($message, $parseurl=0) {
		$search_exp = array(
			"/\s*\[quote\][\n\r]*(.+?)[\n\r]*\[\/quote\]\s*/is",
			"/\[url\]\s*(https?:\/\/|ftp:\/\/|gopher:\/\/|news:\/\/|telnet:\/\/|rtsp:\/\/|mms:\/\/|callto:\/\/|ed2k:\/\/){1}([^\[\"']+?)\s*\[\/url\]/i",
			"/\[em:(.+?):]/is",
		);
		$replace_exp = array(
			"<div class=\"quote\"><span class=\"q\">\\1</span></div>",
			"<a href=\"\\1\\2\" target=\"_blank\">\\1\\2</a>",
			"<img src=\"".Myqee::config('core.mysite_url')."images/face/\\1.gif\" class=\"face\" />"
		);
		$search_str = array('[b]', '[/b]','[i]', '[/i]', '[u]', '[/u]');
		$replace_str = array('<b>', '</b>', '<i>','</i>', '<u>', '</u>');
		
		if($parseurl==2) {//深度解析
			$search_exp[] = "/\[img\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/ies";
			$replace_exp[] = 'self::bb_img(\'\\1\')';
			$message = self::parseurl($message);
		}
		@$message = str_replace($search_str, $replace_str,preg_replace($search_exp, $replace_exp, $message));
		return nl2br(str_replace(array("\t", '   ', '  '), array('&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;'), $message));
	}
	
	//自动解析url
	public static function parseurl($message) {
		return preg_replace("/(?<=[^\]a-z0-9-=\"'\\/])((https?|ftp|gopher|news|telnet|mms|rtsp):\/\/)([a-z0-9\/\-_+=.~!%@?#%&;:$\\()|]+)/i", "[url]\\1\\3[/url]", ' '.$message);
	}
	
	//html转化为bbcode
	public static function html2bbcode($message) {
		$html_s_exp = array(
			"/\<div class=\"quote\"\>\<span class=\"q\"\>(.*?)\<\/span\>\<\/div\>/is",
			"/\<a href=\"(.+?)\".*?\<\/a\>/is",
			"/(\r\n|\n|\r)/",
			"/<br.*>/siU",
			"/\s*\<img src=\"".Myqee::config('core.mysite_url')."images\/face\/(.+?).gif\".*?\ \/>\s*/is",
			"/\s*\<img src=\"(.+?)\".*?\ \/>\s*/is"
		);
		$html_r_exp = array(
			"[quote]\\1[/quote]",
			"\\1",
			'',
			"\n",
			"[em:\\1:]",
			"\n[img]\\1[/img]\n"
		);
		$html_s_str = array('<b>', '</b>', '<i>','</i>', '<u>', '</u>', '&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;', '&lt;', '&gt;', '&amp;');
		$html_r_str = array('[b]', '[/b]','[i]', '[/i]', '[u]', '[/u]', "\t", '   ', '  ', '<', '>', '&');
		
		@$message = str_replace($html_s_str, $html_r_str,
			preg_replace($html_s_exp, $html_r_exp, $message));
			
		$message = self::shtmlspecialchars($message);
		
		return trim($message);
	}
	
	public static function bb_img($url) {
		$url = addslashes($url);
		return '<img src="'.$url.'" />';
	}
	
	
	public static function xss_clean($data)
	{
		if (is_array($data))
		{
			foreach ($data as $key => $val)
			{
				$data[$key] = self::xss_clean($val, $tool);
			}

			return $data;
		}

		// Do not clean empty strings
		if (trim($data) === '')
			return $data;


		// http://svn.bitflux.ch/repos/public/popoon/trunk/classes/externalinput.php
		// +----------------------------------------------------------------------+
		// | Copyright (c) 2001-2006 Bitflux GmbH                                 |
		// +----------------------------------------------------------------------+
		// | Licensed under the Apache License, Version 2.0 (the "License");      |
		// | you may not use this file except in compliance with the License.     |
		// | You may obtain a copy of the License at                              |
		// | http://www.apache.org/licenses/LICENSE-2.0                           |
		// | Unless required by applicable law or agreed to in writing, software  |
		// | distributed under the License is distributed on an "AS IS" BASIS,    |
		// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
		// | implied. See the License for the specific language governing         |
		// | permissions and limitations under the License.                       |
		// +----------------------------------------------------------------------+
		// | Author: Christian Stocker <chregu@bitflux.ch>                        |
		// +----------------------------------------------------------------------+
		//
		// Kohana Modifications:
		// * Changed double quotes to single quotes, changed indenting and spacing
		// * Removed magic_quotes stuff
		// * Increased regex readability:
		//   * Used delimeters that aren't found in the pattern
		//   * Removed all unneeded escapes
		//   * Deleted U modifiers and swapped greediness where needed
		// * Increased regex speed:
		//   * Made capturing parentheses non-capturing where possible
		//   * Removed parentheses where possible
		//   * Split up alternation alternatives
		//   * Made some quantifiers possessive

		// Fix &entity\n;
		$data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
		$data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
		$data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
		$data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

		// Remove any attribute starting with "on" or xmlns
		$data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

		// Remove javascript: and vbscript: protocols
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

		// Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

		// Remove namespaced elements (we do not need them)
		$data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

		do
		{
			// Remove really unwanted tags
			$old_data = $data;
			$data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
		}
		while ($old_data !== $data);

		return $data;
	}
	public static function gzdecode($data) { 
		  $len = strlen($data); 
		  if ($len < 18 || strcmp(substr($data,0,2),"\x1f\x8b")) { 
		   return null;  // Not GZIP format (See RFC 1952) 
		  } 
		  $method = ord(substr($data,2,1));  // Compression method 
		  $flags  = ord(substr($data,3,1));  // Flags 
		  if ($flags & 31 != $flags) { 
		   // Reserved bits are set -- NOT ALLOWED by RFC 1952 
		   return null; 
		  } 
		  // NOTE: $mtime may be negative (PHP integer limitations) 
		  $mtime = unpack("V", substr($data,4,4)); 
		  $mtime = $mtime[1]; 
		  $xfl  = substr($data,8,1); 
		  $os    = substr($data,8,1); 
		  $headerlen = 10; 
		  $extralen  = 0; 
		  $extra    = ""; 
		  if ($flags & 4) { 
		   // 2-byte length prefixed EXTRA data in header 
		   if ($len - $headerlen - 2 < 8) { 
		     return false;    // Invalid format 
		   } 
		   $extralen = unpack("v",substr($data,8,2)); 
		   $extralen = $extralen[1]; 
		   if ($len - $headerlen - 2 - $extralen < 8) { 
		     return false;    // Invalid format 
		   } 
		   $extra = substr($data,10,$extralen); 
		   $headerlen += 2 + $extralen; 
		  } 
		
		  $filenamelen = 0; 
		  $filename = ""; 
		  if ($flags & 8) { 
		   // C-style string file NAME data in header 
		   if ($len - $headerlen - 1 < 8) { 
		     return false;    // Invalid format 
		   } 
		   $filenamelen = strpos(substr($data,8+$extralen),chr(0)); 
		   if ($filenamelen === false || $len - $headerlen - $filenamelen - 1 < 8) { 
		     return false;    // Invalid format 
		   } 
		   $filename = substr($data,$headerlen,$filenamelen); 
		   $headerlen += $filenamelen + 1; 
		  } 
		
		  $commentlen = 0; 
		  $comment = ""; 
		  if ($flags & 16) { 
		   // C-style string COMMENT data in header 
		   if ($len - $headerlen - 1 < 8) { 
		     return false;    // Invalid format 
		   } 
		   $commentlen = strpos(substr($data,8+$extralen+$filenamelen),chr(0)); 
		   if ($commentlen === false || $len - $headerlen - $commentlen - 1 < 8) { 
		     return false;    // Invalid header format 
		   } 
		   $comment = substr($data,$headerlen,$commentlen); 
		   $headerlen += $commentlen + 1; 
		  } 
		
		  $headercrc = ""; 
		  if ($flags & 1) { 
		   // 2-bytes (lowest order) of CRC32 on header present 
		   if ($len - $headerlen - 2 < 8) { 
		     return false;    // Invalid format 
		   } 
		   $calccrc = crc32(substr($data,0,$headerlen)) & 0xffff; 
		   $headercrc = unpack("v", substr($data,$headerlen,2)); 
		   $headercrc = $headercrc[1]; 
		   if ($headercrc != $calccrc) { 
		     return false;    // Bad header CRC 
		   } 
		   $headerlen += 2; 
		  } 
		
		  // GZIP FOOTER - These be negative due to PHP's limitations 
		  $datacrc = unpack("V",substr($data,-8,4)); 
		  $datacrc = $datacrc[1]; 
		  $isize = unpack("V",substr($data,-4)); 
		  $isize = $isize[1]; 
		
		  // Perform the decompression: 
		  $bodylen = $len-$headerlen-8; 
		  if ($bodylen < 1) { 
		   // This should never happen - IMPLEMENTATION BUG! 
		   return null; 
		  } 
		  $body = substr($data,$headerlen,$bodylen); 
		  $data = ""; 
		  if ($bodylen > 0) { 
		   switch ($method) { 
		     case 8: 
		       // Currently the only supported compression method: 
		       $data = gzinflate($body); 
		       break; 
		     default: 
		       // Unknown compression method 
		       return false; 
		   } 
		  } else { 
		   // I'm not sure if zero-byte body content is allowed. 
		   // Allow it for now...  Do nothing... 
		  } 
		
		  // Verifiy decompressed size and CRC32: 
		  // NOTE: This may fail with large data sizes depending on how 
		  //      PHP's integer limitations affect strlen() since $isize 
		  //      may be negative for large sizes. 
		  if ($isize != strlen($data) || crc32($data) != $datacrc) { 
		   // Bad format!  Length or CRC doesn't match! 
		   return false; 
		  } 
		  return $data; 
	}
}

if (extension_loaded ( 'mbstring' )) {
	mb_internal_encoding ( 'UTF-8' );
	define ( 'SERVER_UTF8', TRUE );
} else {
	define ( 'SERVER_UTF8', FALSE );
}
