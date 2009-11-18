<?php defined('MYQEEPATH') or die('No direct script access.');

class Cache_Core{
	public static function & instance(){
		return new Cache;
	}
	protected static function _getcachepath($cachename){
		$cachename = trim(preg_replace("/[^a-zA-Z0-9_\-\.\/]+/",'',$cachename),'./');
		$cachepath = CACHEPATH. $cachename .'_'. md5($cachename);
		return $cachepath;
	}
	
	public static function set($cachename,$content){
		$cachepath = self::_getcachepath($cachename);
		if (is_array($content))$content = '::SERIALIZE::'.serialize($content);
		return Tools::createfile($cachepath,$content);
	}

	public static function get($cachename,$thetime = 1800,$echo=false){
		$cachepath = self::_getcachepath($cachename);
		if (file_exists($cachepath)){
			if ( $_SERVER['REQUEST_TIME'] - filemtime($cachepath) < $thetime ){
				$data = file_get_contents($cachepath);
				if ($echo==true){
					$etag = md5($data);
					header('ETag: '.$etag);
					if ($_SERVER["HTTP_IF_NONE_MATCH"] && $etag==$_SERVER["HTTP_IF_NONE_MATCH"] && $_SERVER["HTTP_CACHE_CONTROL"]!='no-cache'){
						header('HTTP/1.1 304 Not Modified');
					}else{
						if (strpos($_SERVER["HTTP_ACCEPT_ENCODING"],'gzip')!==false && !preg_match("/MSIE ([1|2|3|4|5|6]\.[0-9])/i",$_SERVER["HTTP_USER_AGENT"])){
							//GZIP
							$data = gzencode($data,9);
							header('Content-Encoding: gzip');
						}
						header('Last-Modified: '.date("F d Y H:i:s.",filemtime($cachepath)));
						echo $data;
					}
					return TRUE;
				}else{
					if (substr($data,0,13)=='::SERIALIZE::'){
						$data = unserialize(substr($data,13));
					}
					return $data;
				}
			}else{
				return FALSE;
			}
		}else{
			return FALSE;
		}
	}
	public static function filemtime($cachename){
		$cachepath = self::_getcachepath($cachename);
		if (file_exists($cachepath)){
			return filemtime($cachepath);
		}else{
			return FALSE;
		}
	}
	
	public static function delete($cachename){
		$cachepath = self::_getcachepath($cachename);
		if (file_exists($cachepath)){
			return unlink($cachepath);
		}
		return true;
	}

}