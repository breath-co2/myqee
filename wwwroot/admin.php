<?php
/*
//对进入的域名进行限制处理，防止非法调用
$allowHOST = array();
if (count($allowHOST)>0 && !in_array($_SERVER['HTTP_HOST'],$allowHOST)){
	header("HTTP/1.0 404 Not Found");exit;
}
*/
error_reporting(E_ALL & ~E_NOTICE);


$myqee_wwwroot 		= './';				//网站WWWROOT目录
$myqee_sysapppath 	= '../system';				//myqee系统目录
$myqee_adminpath 	= '../admin';				//用户后台程序目录
$myqee_cachepath 	= '../admin/cache';			//缓存目录
$myqee_userapppath 	= '../application';			//用户APP目录
$myqee_modulepath 	= '../modules';				//程序模块目录
$myqee_adminimgpath = '/images';					//管理员页面图片、样式、脚本等主目录。以/开头，最后不要加/若图片在主目录，则留空
if (!isset($myqee_adminurlpath))
	$myqee_adminurlpath = '/admin/';						//后台管理页面起始路径，以/或http://等开头，最后要加/
$myqee_uploadpath 	= $myqee_wwwroot . 'upload/';	//上传目录
$myqee_edittemplate = 1;							//是否允许修改模板，1-允许，0-禁止

/*
上传目录默认值为：wwwroot目录的upload/。若修改为其它值，为使自动缩略图功能生效，请务必在系统参数设置的“路由设置”里增加一条为，并启用路由功能：

^newfloder\/(.*)=>upload/\1

其中，newfloder为您新设置的上传目录。
*/


header('Content-Type: text/html;charset=utf-8');




////////////////////////////以下不用修改//////////////////////////////

/**
 * 后台管理页面起始路径
 * 以/或http://等开头，最后要加/
 *
 * @see http://myqee.com/
 */
define("ADMIN_URLPATH",$myqee_adminurlpath);


/**
 * 特殊处理生成页面，将它转入到MYQEE程序内执行
 *
 * @return boolean true/false
 */
function _check_is_tohtml_fun(){
	if (!isset($_GET['_timeline']) || !isset($_GET['_code'])){
		return false;
	}
	$tmpstr = strtolower(ADMIN_URLPATH).'myqee_tohtml/';
	$tmpstrlen = strlen($tmpstr);
	if (substr(strtolower($_SERVER['REQUEST_URI']),0,$tmpstrlen)==$tmpstr){
		list($tmppath) = explode('?',$_SERVER['REQUEST_URI'],2);
		$_SERVER['PATH_INFO'] = '/__tohtml/'.substr($tmppath,$tmpstrlen);
		return true;
	}
	return false;
}


function _get_tohtmlurl($type = 'toindexpage',$key = 'myqee_com_&$22.13#@$sw',$otherstring = ''){
	$otherstring and $otherstring = '&'.$otherstring;
	$time = time();
	$url = ADMIN_URLPATH.'myqee_tohtml/'.$type.'?_timeline='.$time.'&_code='.md5($key.'__'.$time).$otherstring;
	return $url;
}


// 用于加载批量生成文件
if ( ( $model_begin =_check_is_tohtml_fun() ) == true ){
	define("IN_ADMINMODEL",'yes');
	chdir(str_replace('\\', '/', realpath($myqee_wwwroot)).'/');include('myqee.php');exit;
}


/**
 * 网站主目录路径
 * 若此页面和wwwroot目录在同一目录，则realpath('./')
 *
 * @see http://myqee.com/
 */
define('WWWROOT', str_replace('\\', '/', realpath($myqee_wwwroot)).'/');

define('MYQEEPATH', str_replace('\\', '/', realpath($myqee_sysapppath)).'/');

define('ADMINPATH', str_replace('\\', '/', realpath($myqee_adminpath)).'/');

define('CACHEPATH', str_replace('\\', '/', realpath($myqee_cachepath)).'/');

define('MYAPPPATH', str_replace('\\', '/', realpath($myqee_userapppath)).'/');

define('MODULEPATH', str_replace('\\', '/', realpath($myqee_modulepath)).'/');

define('UPLOADPATH', str_replace('\\', '/', realpath($myqee_uploadpath)).'/');

if (WWWROOT==='/')die('The wwwroot does not exist');
if (MYQEEPATH==='/')die('The system does not exist');
if (ADMINPATH==='/')die('The adminpath does not exist');
if (CACHEPATH==='/')die('The admincache does not exist');
if (MYAPPPATH==='/')die('The application does not exist');
if (MODULEPATH==='/')die('The modulepath does not exist');
if (UPLOADPATH==='/')die('The uploadpath does not exist');

/**
 * 管理员页面图片、样式、脚本等主目录
 * 以/开头，最后不要加/若图片在主目录，则留空
 *
 * @see http://myqee.com/
 */
define('ADMIN_IMGPATH',$myqee_adminimgpath);
define('EDIT_TEMPLATE',$myqee_edittemplate);
define('EXT', '.php');


unset($model_begin,$myqee_wwwroot,$myqee_myqeepath,$myqee_adminpath,$myqee_cachepath,$myqee_modulepath,$myqee_userapppath,$myqee_adminimgpath,$myqee_adminurlpath,$myqee_uploadpath,$myqee_edittemplate);

//防止其它引用页include时解析地址错误
$_SERVER['SCRIPT_FILENAME'] = __FILE__;



version_compare(PHP_VERSION, '5.2', '<') and exit('<ul><li>Now php Version:<b>'.PHP_VERSION.'</b></li><li>It\'s Myqee CMS For PHP5.<br/>Please download Myqee For PHP4.<a href="http://www.myqee.com/download/php4/">http://www.myqee.com/download/php4/</a></li></ul>');



/*
if (substr($_SERVER['PATH_INFO'],0,3) == '/__'){
	if (substr($_SERVER['PATH_INFO'],0,11) == '/__run_task'){
		//处理任务
		require MYQEEPATH.'admin/runphps/run_task.php';
	}elseif(substr($_SERVER['PATH_INFO'],0,18) == '/__run_acquisition'){
		//处理采集
		require MYQEEPATH.'admin/runphps/do_acquisition.php';
	}elseif(substr($_SERVER['PATH_INFO'],0,15) == '/__readurlfiles'){
		//远程读取
		require MYQEEPATH.'admin/runphps/readurlfiles.php';
	}else{
		echo 'ERROR PAGE';
	}
}else{
	require MYQEEPATH.'core/myqee.php';
}
*/

require MYQEEPATH.'core/myqee.php';