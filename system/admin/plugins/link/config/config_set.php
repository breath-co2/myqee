<?php

$config = array(
	'c1ddf' => array(
		'tag_name' => '默认设置',
		'field_width' => 120,
		'name' => 'Meta Keywords（网页关键词）',
		'title' => '针对搜索引擎设置的关键词',
		'default' => '友情链接',
		'type' => 'textarea',
		'set' => array (
			'cols' => 50,
			'rows' => 1,
			'class' => 'input',
		),
	),
	'c2' => array(
		'name' => 'Meta Description（网页描述）',
		'title' => '针对搜索引擎设置的网页描述',
		'default' => '友情链接',
		'type' => 'textarea',
		'set' => array (
			'cols' => 50,
			'rows' => 4,
			'class' => 'input',
		),
	),
	'c3' => array(
		'name' => '模块绑定域名',
		'default' => 'link/',
		'type' => 'input',
		'set' => array (
			'size' => 30,
			'class' => 'input',
		),
	),
	'c4' => array(
		'name' => '是否开启验证码',
		'type' => 'radio',
		'candidate' => array (
			1 => '是',
			0 => '否',
		),
		'default' => 0,
	),
	'c5' => array(
		'name' => '是否需要审核',
		'type' => 'radio',
		'candidate' => array (
			1 => '是',
			0 => '否',
		),
		'default' => 1,
	),

	'tttt' => array(
		'tag_name' => '高级设置',
		'field_width' => 120,
		'name' => 'Meta Keywords（网页关键词）',
		'title' => '针对搜索引擎设置的关键词',
		'default' => '友情链接',
		'type' => 'textarea',
		'set' => array (
			'size' => 50,
			'rows' => 4,
			'class' => 'input',
		),
	),
);