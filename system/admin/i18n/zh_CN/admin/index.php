<?php defined('MYQEEPATH') or die('No direct script access.');

$lang = array
(
	'page_title' => '后台管理首页',
	'menu' => array(
		'makesuredelete_menu' => '您确认删除本链接？将删除所有子链接！\n\n是否继续？',
	),
	'info' => array(
		'savemenuok' => '恭喜，保存成功！',
		'savemenunone' => '未保存任何数据！',
		'saveok' => '恭喜，配置保存成功！\n\n若页面加载失败，请手动恢复config.php.bak为config.php！',
		'savenone' => '没有修改任何数据！',
		'bakok' => '恭喜，配置文件恢复成功！\n\n若页面加载失败，请手动恢复config.php.bak为config.php！',
		'configbackok' => '恢复默认配置文件成功！',
		'configbackerror' => '恢复默认配置文件失败！',
	),
	'error' => array(
		'nobakconfig' => '不存在备份配置文件，无法恢复！',
		'dobakconfigerror' => '操作备份文件失败，可能没有相关权限。恢复备份取消！',
		'doconfigerror' => '操作配置文件失败，可能没有相关权限。恢复备份取消！',
		'bakconfigerror' => '操作恢复备份失败，可能文件已删除或无相关权限！',
		'nofoundadminmenu' => '不存在指定ID的后台菜单！',
		'noacqufathername' => '请输入父菜单名称！',
		'noacqufatheraddress' => '请输入父链接地址！',
		'noacqufatherlevel' => '请输入父权限！',
		'noacqufatherkey' => '请输入父KEY值！',
		'noacquchildname' => '请输入子菜单名称！',
		'noacquchildaddress' => '请输入子链接地址！',
		'noacquchildlevel' => '请输入子权限！',
		'noacquchildkey' => '请输入子KEY值！',
		'dataerror' => '输入的信息出错！',
	),
	
	'cachemanage' => '缓存管理',
);