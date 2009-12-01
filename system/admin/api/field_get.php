<?php
class Field_get_Api_Core{
	public $_methodname = array(
		'getconfig'=>'获取配置信息',
	);
	
	
	public function getconfig($str=''){
		return MyqeeCMS::config($str);
	}
}
