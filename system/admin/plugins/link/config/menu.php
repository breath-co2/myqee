<?php

$config['name'] = '用户管理';


/**
 *
 * @global array $config['menu']
 * @name $config
 */
$config = array(
	'member' => array(
		'name' => '友情链接管理',
		'href' => 'mylink/index',
		'target' => '',
		/*
		'submenu' => array(
			'group' => array(
				'name' => '添加新链接',
				'href' => 'group_list',
				'target' => '',
			),
			'group3' => array(
				'name' => '用户333管理1',
				'href' => 'group_list',
				'target' => '',
			),
		),
		*/
	),
	'group' => array(
		'name' => '添加新链接',
		'href' => 'mylink/add',
		'target' => '',
		'icon' => 'pk.gif',
	),
);