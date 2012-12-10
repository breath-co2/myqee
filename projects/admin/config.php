<?php

/**
 * 加载库管理类库
 *
 * @var array
 */
$config['libraries']['autoload'][] = 'com.myqee.administration';

/**
 * 路由配置
 * @var array
 */
$config['route'] = array
(
    'project' => array
    (
		# 匹配的URL
		'uri' => '/p/<project>(/<uri>)',
		# 匹配正则
		'preg' => array
        (
			'project' => '[a-zA-Z0-9_]+',
			'uri' => '.*',
		),
		# 默认值
		'default' => array
        (
			'controller' => 'Admin_P',
			'action'     => 'default',
		),
	),
);