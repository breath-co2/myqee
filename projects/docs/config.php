<?php
$config['site_views'] = array
(
//    'by_project' => 'default',            //页面头部和底部视图所在项目
//    'header'     => 'docs_header',        //项目的顶部文件，不配置则使用默认头部
//    'bottom'     => 'docs_bottom',        //项目的底部文件，不配置则使用默认头部
    'main_width'    => 960,               //宽度
    'header_height' => 45,                //顶部高度
    'bottom_height' => 56,                //底部高度
);

/**
 * 是否启动API Explorer
 */
$config['api_explorer']['use'] = true;

/**
 * API Explorer 访问密码，可设置多个
 */
$config['api_explorer']['pass'] = array();


// 下面是项目文件路由，非必要时请勿修改

$config['route'] = array(
	'md_file' => array(
		# 匹配的URL
		'uri' => '/(<md_file>).html',
		# 匹配正则
		'preg' => array(
			'md_file' => '[0-9a-zA-Z\.]+',
		),
		# 默认值
		'default' => array(
			'controller' => 'docs_index',
			'action'     => 'default',
			'md_file'   => 'base.welcome',
		),
	),
	'api' => array(
		# 匹配的URL
		'uri' => '/api/(<project>)/(<dir>)/(<file>)',
		# 匹配正则
		'preg' => array(
			'project' => '[0-9a-zA-Z_]+',
			'dir' => '[0-9a-zA-Z_]+',
			'file' => '[0-9a-zA-Z_/\.]+',
		),
		# 默认值
		'default' => array(
			'controller' => 'docs_api',
			'action'     => 'api',
		),
	),
	'api_view' => array(
		# 匹配的URL
		'uri' => '/api/(<action>)',
		# 匹配正则
		'preg' => array(
			'action' => '[0-9a-zA-Z_]*',
		),
		# 默认值
		'default' => array(
			'controller' => 'docs_api',
			'action'     => 'default',
		),
	),
	'default' => array(
		# 匹配的URL
		'uri' => '<c>',
		# 匹配正则
		'preg' => array(
			'c' => '^/$',
		),
		# 默认值
		'default' => array(
			'controller' => 'docs_index',
			'action'     => 'default',
		),
	),
);