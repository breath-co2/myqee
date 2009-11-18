<?php
$adminmodel = new Admin_Model;
$classtree = $adminmodel -> get_allclass_array();
//print_r($classtree);
$config = array(
	'pagesize' => array(
		'name' => '每页显示留言个数',
		'type' => 'input',
		'set' => array (
			'class' => 'input',
			'size' => 4,
		),
		'default' => '20',
	),
	'maxcontent' => array(
		'name' => '留言最大字数',
		'type' => 'input',
		'set' => array (
			'class' => 'input',
			'size' => 4,
		),
		'default' => '500',
	),
	'enablechckcode' => array(
		'name' => '是否开启验证码',
		'type' => 'radio',
		'candidate' => array (
			'1' => '是',
			'0' => '否',
		),
		'default' => 1,
	),
	'show' => array(
		'name' => '是否前台显示',
		'type' => 'radio',
		'candidate' => array (
			'1' => '是',
			'0' => '否',
		),
		'default' => 1,
	),
	'enableTourist' => array(
		'name' => '是否允许游客留言',
		'type' => 'radio',
		'candidate' => array (
			'1' => '是',
			'0' => '否',
		),
		'default' => 1,
	),
	'checkpass' => array(
		'name' => '留言是否需要审核',
		'type' => 'radio',
		'candidate' => array (
			'1' => '是',
			'0' => '否',
		),
		'default' => 0,
	),
	'classides' => array(
		'name' => '所属栏目',
		'type' => 'classlist',
		'candidate' => $classtree,
		'set' => array(
			'size' => 18,
			'multiple' => 'multiple',
			'class' => 'select',
			'style' => 'width:420px;',
		),
	),
	'isrecursion' => array(
		'name' => '是否包含子栏目',
		'type' => 'checkbox',
		'candidate' => array (
			'1' => '是',
		),
		'default' => 1,
	),
);