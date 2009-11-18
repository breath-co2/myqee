<?php
 $config['engine'][''] = array(
	'name' => '无(直接使用视图)',
	'api' => 'View',
	'render' => 'render',
	'suffix' => '.php',
	'allsuffix' => '.php|.txt',
);

 $config['engine']['default'] = array(
	'name' => '麦琪模板引擎(系统引擎)',
	'api' => 'Template',
	'suffix' => '.html',
	'allsuffix' => '.html|.txt',
);

$config['engine']['smarty'] = array(
	'name' => 'Smarty模板引擎',
	'api' => 'Smatry',
	'suffix' => '.tpl',
	'allsuffix' => '.tpl|.htm|.conf|.txt',
);