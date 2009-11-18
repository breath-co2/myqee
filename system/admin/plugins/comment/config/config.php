<?php

$config['name'] = 'Comment';
/**
 * 不能改变
 * 
 */

/**
 * 虚拟字段
 */
$config['model']['virtualfield'][] = array(
	'title'	=> '是否评论',
	'type'	=> 'radio',
	'candidate'	=> array (
		'1'	=> '是',	
		'0'	=> '否',	
	),
//	'infohook' => array('plugins_Model','commentinfo'),
//	'modelhook' => array('plugins_Model','commentmodel'),
);