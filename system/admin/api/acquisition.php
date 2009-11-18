<?php
class Acquisition_Api_Core{
	public $_methodname = array(
		'json_decode' => '将json字符串转化为数组',
		'get_htmlimg' => '采集字符串中图片',
		'gbk2utf8' => 'gk转utf8',
		'ucs22utf8' => '2字节的unicode转换成utf8',
	);
	
	
	/**
	 * 转换为数组
	 * @param $str
	 * @return true
	 */
	public function json_decode( &$str='' ){
		if (Tools::is_ascii($str)){
			//$str = iconv('GBK','UTF-8',$str);
			$isiconv = true;
		}else{
			$isiconv = false;
		}
		$str = Tools::json_decode($str);
		if ($isiconv){
			//$str = iconv('UTF-8','GBK',$str);
		}
		return $str;
	}
	
	public function get_htmlimg( &$str='' ){
		
	}
	public function gbk2utf8( &$str=''){
		$str = iconv('GBK','UTF-8',$str);
	}
	public function ucs22utf8 ( &$str='') {
		$str = iconv('UCS-2','UTF-8',$str);
	}
}