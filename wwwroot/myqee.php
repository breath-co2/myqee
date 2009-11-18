<?php
$myqee_wwwroot 		= './';			//网站WWWROOT目录
$myqee_sysapppath 	= '../system';		//系统目录
$myqee_userapppath 	= '../application';	//自定义APP目录
$myqee_modulepath 	= '../modules';		//模块目录
$myqee_cachepath 	= '../myapp/cache';	//缓存目录



//@date_default_timezone_set("PRC");	//定义时区
error_reporting(7);
set_magic_quotes_runtime(0);


chdir(dirname(__FILE__));
define('MYAPPPATH', str_replace('\\', '/', realpath($myqee_userapppath)).'/');
define('MYQEEPATH', str_replace('\\', '/', realpath($myqee_sysapppath)).'/');
define('WWWROOT', str_replace('\\', '/', realpath($myqee_wwwroot)).'/');
define('MODULEPATH', str_replace('\\', '/', realpath($myqee_modulepath)).'/');
define('CACHEPATH', str_replace('\\', '/', realpath($myqee_cachepath)).'/');
define('EXT', '.php');


header('Content-Type: text/html;charset=utf-8');

unset($GLOBALS, $_ENV, $HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_COOKIE_VARS, $HTTP_SERVER_VARS, $HTTP_ENV_VARS);
unset($myqee_userapppath,$myqee_sysapppath,$myqee_wwwroot,$myqee_modulepath,$myqee_cachepath);



$_SERVER['SCRIPT_FILENAME'] = __FILE__;


version_compare(PHP_VERSION, '5.2', '<') and exit('<ul><li>Now php Version:<b>'.PHP_VERSION.'</b></li><li>It\'s Myqee CMS For PHP5.<br/>Please download Myqee For PHP4.<a href="http://www.myqee.com/download/php4/">http://www.myqee.com/download/php4/</a></li></ul>');

require MYQEEPATH.'core/myqee'.EXT;