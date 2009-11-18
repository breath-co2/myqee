<?php defined('MYQEEPATH') or die('No direct script access.');

$lang = array
(
	'list' => array(
		'makesuredelete' => '您确认删除本栏目？将删除所有子栏目，不可恢复！\n\n是否继续？',
		'makesuredelete_nav' => '您确认删除本菜单？将删除所有子菜单！\n\n是否继续？\n\n★注：对于系统栏目，只是取消在导航栏的显示，并不删除栏目本身！',
	),
	'error' => array(
		'needclassname' => '请输入栏目名称！',
		'needmodelid' => '请选择系统模型！',
		'needclasspath' => '请输入指定栏目的目录！',
		'needcovertemplate' => '您已启用栏目封面功能，请选择封面模板！',
		'needlisttemplate' => '您已启用列表页面功能，请选择列表模板！',
		'needcontenttemplate' => '您已启用内容页面功能，请选择内容模板！',
		'needcontentpath' => '您选择了内容输出自定义目录，请填写自定义目录路径！',
		'needsearchtemplate' => '您已启用页面搜索功能，请选择搜索列表模板！',
		'canotinsonclass' => '父栏目选择错误！\n\n新的父栏目不能选择自身或自身子栏目的栏目，请重新选择！',
		'folderexist' => '指定栏目目录%s已经存在，\n\n请更换其它目录！',
		'noparameters' => '缺少参数！',
		'nothisclass' => '不存在此ID,可能已被删除！',
		'noorderinfo' => '排序没有发生变化！',
	),
	'info' => array(
		'nothisclassid' => '不存在此栏目，可能已删除！',
		'updataok' => '恭喜，保存成功！',
		'noupdata' => '没有变动数据更新！',
		'deleteok' => '成功删除%s栏目！',
		'editmyorderok' => '成功更改%s栏目排序！',
		'updataset' => '成功更新%s栏目！',
		'renewconfigok' => '成功更新配置文件：%s！',
		'renewconfigerror' => '更新配置文件失败：%s！',
	),
);