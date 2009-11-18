<?php

$config = array(
	
	'AccessControl' => array(
		'name' => '权限控制',
		'sub' => array(
			'folderView' => array(
				'name' => '文件夹浏览',
				'type' => 'radio',
				'candidate' => array (
					'1' => '允许',
					'0' => '禁止',
				),
			),
			'folderCreate' => array(
				'name' => '创建文件夹',
				'type' => 'radio',
				'candidate' => array (
					'1' => '允许',
					'0' => '禁止',
				),
			),
			'folderRename' => array(
				'name' => '重命名文件夹',
				'type' => 'radio',
				'candidate' => array (
					'1' => '允许',
					'0' => '禁止',
				),
			),
			'folderDelete' => array(
				'name' => '删除文件夹',
				'type' => 'radio',
				'default' => 0,
				'candidate' => array (
					'1' => '允许',
					'0' => '禁止',
				),
			),
			'fileView' => array(
				'name' => '查看文件',
				'type' => 'radio',
				'candidate' => array (
					'1' => '允许',
					'0' => '禁止',
				),
			),
			'fileView' => array(
				'name' => '查看文件',
				'type' => 'radio',
				'candidate' => array (
					'1' => '允许',
					'0' => '禁止',
				),
			),
			'fileUpload' => array(
				'name' => '文件上传',
				'type' => 'radio',
				'candidate' => array (
					'1' => '允许',
					'0' => '禁止',
				),
			),
			'fileRename' => array(
				'name' => '重命名文件',
				'type' => 'radio',
				'candidate' => array (
					'1' => '允许',
					'0' => '禁止',
				),
			),
			'fileDelete' => array(
				'name' => '删除文件',
				'type' => 'radio',
				'default' => 0,
				'candidate' => array (
					'1' => '允许',
					'0' => '禁止',
				),
			),
		),
	),
	'FilesystemEncoding' => array(
		'name' => '文件名编码',
		'type' => 'selectinput',
		'set' => array (
			'class' => 'input',
			'size' => 8,
		),
		'default' => 'GBK',
		'candidate' => array (
			'GBK' => 'GBK',
			'UTF-8' => 'UTF-8',
			'BIG5' => 'BIG5',
			'CP1250' => 'CP1250',
			'ISO-8859-2' => 'ISO-8859-2',
		),
		'description' => '中文系统服务器通常选择GBK',
	),
	'skin' => array(
		'name' => '界面风格',
		'type' => 'select',
		'default' => 'silver',
		'candidate' => array (
			'default' => '默认',
			'office2003' => 'Office2003',
			'silver' => 'silver',
		),
	),
	'LicenseName' => array(
		'tag_name' => '程序授权',
//		'field_width' => 120,
		'name' => '授权用户名',
		'type' => 'input',
		'set' => array (
			'size' => 30,
			'class' => 'input',
		),
	),
	'LicenseKey' => array(
		'name' => '授权码',
		'type' => 'textarea',
		'set' => array (
			'cols' => 50,
			'rows' => 4,
			'class' => 'input',
		),
	),
);