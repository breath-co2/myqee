<?php
class Field_Api_Core{
	public $_methodname = array(
		'timelinetostr'=>'时间输出转换',
	);
	
	
	public function timelinetostr($value,$str){
		return date($str,(int)$value);
	}
}
