<?php defined('MYQEEPATH') or die('No direct script access.');

class Encryption_Core {

	protected static $config;
	
	
	/**
	* Encrypt 加密函数
	*
	* @param		string		等待加密的原字串
	* @param		string		私有密匙(用于解密和加密)
	*
	* @return	string		原字串经过私有密匙加密后的结果
	*/
	public static function encode($txt, $key = NULL) {
		if ($key===NULL){
			if (!self::$config){
				self::$config = Myqee::config('encryption.key');
			}
			$key = self::$config;
		}
		
		// 使用随机数发生器产生 0~32000 的值并 MD5()
		srand((double)microtime() * 1000000);
		$encrypt_key = md5(rand(0, 32000));

		// 变量初始化
		$ctr = 0;
		$tmp = '';

		// for 循环，$i 为从 0 开始，到小于 $txt 字串长度的整数
		for($i = 0; $i < strlen($txt); $i++) {
			// 如果 $ctr = $encrypt_key 的长度，则 $ctr 清零
			$ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
			// $tmp 字串在末尾增加两位，其第一位内容为 $encrypt_key 的第 $ctr 位，
			// 第二位内容为 $txt 的第 $i 位与 $encrypt_key 的 $ctr 位取异或。然后 $ctr = $ctr + 1
			$tmp .= $encrypt_key[$ctr].($txt[$i] ^ $encrypt_key[$ctr++]);
		}

		// 返回结果，结果为 encrypt_key() 函数返回值的 base64 编码结果
		return base64_encode(self::_encrypt_key($tmp, $key));

	}

	/**
	* Passport 解密函数
	*
	* @param		string		加密后的字串
	* @param		string		私有密匙(用于解密和加密)
	*
	* @return	string		字串经过私有密匙解密后的结果
	*/
	public static function decode($txt, $key = NULL) {
		if ($key===NULL){
			if (!self::$config){
				self::$config = Myqee::config('encryption.default.key');
			}
			$key = self::$config;
		}
		// $txt 的结果为加密后的字串经过 base64 解码，然后与私有密匙一起，
		// 经过 encrypt_key() 函数处理后的返回值
		$txt = self::_encrypt_key(base64_decode($txt), $key);

		// 变量初始化
		$tmp = '';

		// for 循环，$i 为从 0 开始，到小于 $txt 字串长度的整数
		for ($i = 0; $i < strlen($txt); $i++) {
			// $tmp 字串在末尾增加一位，其内容为 $txt 的第 $i 位，
			// 与 $txt 的第 $i + 1 位取异或。然后 $i = $i + 1
			$tmp .= $txt[$i] ^ $txt[++$i];
		}

		// 返回 $tmp 的值作为结果
		return $tmp;

	}

	/**
	* Passport 密匙处理函数
	*
	* @param		string		待加密或待解密的字串
	* @param		string		私有密匙(用于解密和加密)
	*
	* @return	string		处理后的密匙
	*/
	protected function _encrypt_key($txt, $encrypt_key) {

		// 将 $encrypt_key 赋为 $encrypt_key 经 md5() 后的值
		$encrypt_key = md5($encrypt_key);

		// 变量初始化
		$ctr = 0;
		$tmp = '';

		// for 循环，$i 为从 0 开始，到小于 $txt 字串长度的整数
		for($i = 0; $i < strlen($txt); $i++) {
			// 如果 $ctr = $encrypt_key 的长度，则 $ctr 清零
			$ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
			// $tmp 字串在末尾增加一位，其内容为 $txt 的第 $i 位，
			// 与 $encrypt_key 的第 $ctr + 1 位取异或。然后 $ctr = $ctr + 1
			$tmp .= $txt[$i] ^ $encrypt_key[$ctr++];
		}

		// 返回 $tmp 的值作为结果
		return $tmp;

	}
} // End Encrypt