<?php 
$config = array (
	'index' => 
	array (
		'name' => '管理首页',
		'address' => 'index',
		'level' => '',
		'target' => '',
		'myorder' => '1',
		'sub' => array (
			'index_welcome' => array (
				'name' => '管理首页',
				'address' => 'index/welcome',
				'level' => '',
			),
			'site_manage' => array (
				'name' => '站点管理',
				'address' => 'site/index',
				'level' => '',
			),
			'index_config' => array (
				'name' => '参数设置',
				'address' => 'index/config',
				'level' => 'index.config',
			),
			'cache_manage' => array (
				'name' => '缓存管理',
				'address' => 'index/cache',
				'level' => 'index.cache',
			),
			'index_menu' => array (
				'name' => '后台菜单',
				'address' => 'index/menu',
				'level' => 'index.adminmenu',
			),
			'run_sql' => array (
				'name' => '执行SQL',
				'address' => 'index/runsql',
				'level' => 'index.runsql',
			),
			'index_phpinfo' => array (
				'name' => 'phpinfo',
				'address' => 'index/phpinfo',
				'level' => 'index.phpinfo',
				'target' => '_blank',
			),
		),
	),
	'info' => array (
		'name' => '内容管理',
		'address' => 'info/index',
		'level' => 'info.list',
		'target' => '',
		'myorder' => '2',
		'sub' => array (
			'info_index' => array (
				'name' => '内容管理',
				'address' => 'info/index',
				'level' => 'info.list',
			),
			'block_index' => array (
				'name' => '碎片管理',
				'address' => 'block/index',
				'level' => 'info.block_list',
			),
			'comment_index' => array (
				'name' => '评论管理',
				'address' => 'comment/index',
				'level' => 'info.comment',
			),
			'uploadfile_index' => array (
				'name' => '上传管理',
				'address' => 'uploadfile/index',
				'level' => 'info.uploadlist',
			),
			'uploadfile_up' => array (
				'name' => '上传文件',
				'address' => 'uploadfile/up',
				'level' => 'info.uploadfile',
			),
			'custompage_index' => array (
				'name' => '自定义页',
				'address' => 'custompage/index',
				'level' => 'info.custompage',
			),
			'customlist' => array (
				'name' => '自定义列表',
				'address' => 'customlist/',
				'level' => 'info.customlist',
			),
			'mydata_index' => array (
				'name' => '数据调用',
				'address' => 'mydata/index',
				'level' => 'info.mydata_list',
			),
			'mylink_index' => array (
				'name' => '快速链接',
				'address' => 'mylink/index',
				'level' => 'info.mylink_lists',
			),
		),
	),
	'class' => array (
		'name' => '栏目管理',
		'address' => 'class/index',
	 	'level' => 'class.list',
		'target' => '',
		'myorder' => '2',
		'sub' => array (
			'class_index' => array (
				'name' => '全部栏目',
				'address' => 'class/index',
				'level' => 'class.list',
			),
			'class_mylist' => array (
				'name' => '分页显示',
				'address' => 'class/mylist',
				'level' => '',
			),
			'class_add' => array (
				'name' => '添加栏目',
				'address' => 'class/add',
				'level' => 'class.add',
			),
			'class_set' => array (
				'name' => '批量设置',
				'address' => 'class/set',
				'level' => 'class.set',
			),
			'class_navigation' => array (
				'name' => '导航管理',
				'address' => 'class/navigation',
				'level' => 'class.navigation',
			),
			'class_ztlist' => array (
				'name' => '专题管理',
				'address' => 'special/index',
			),
			'class_ztedit' => array (
				'name' => '添加专题',
				'address' => 'special/add',
			),
		),
	),
	'model' => array (
		'name' => '模型管理',
		'address' => 'model/index',
		'level' => 'model.list',
		'target' => '',
		'myorder' => '4',
		'sub' => array (
			'model_index' => array (
				'name' => '模型管理',
				'address' => 'model/index',
				'level' => 'model.list',
			),
			'model_inputmodel' => array (
				'name' => '导入模型',
				'address' => 'model/inputmodel',
				'level' => 'model.input',
			),
			'model_dblist' => array (
				'name' => '数据表管理',
				'address' => 'model/dblist',
				'level' => 'model.dblist',
			),
			'model_inputdb' => array (
				'name' => '导入数据表',
				'address' => 'model/inputdb',
				'level' => 'model.dbinput',
			),
		),
	),
	'template' => array (
		'name' => '模板管理',
		'address' => 'template/index',
		'level' => 'template.systemp|template.list',
		'target' => '',
		'myorder' => '5',
		'sub' => array (
			'template_index' => array (
				'name' => '全部',
				'address' => 'template/index',
				'level' => 'template.list',
			),
			'template_cover' => array (
				'name' => '封面',
				'address' => 'template/index/1/cover',
				'level' => 'template.list',
			),
			'template_list' => array (
				'name' => '列表',
				'address' => 'template/index/1/list',
				'level' => 'template.list',
			),
			'template_content' => array (
				'name' => '内容',
				'address' => 'template/index/1/content',
				'level' => 'template.list',
			),
			'template_search' => array (
				'name' => '搜索',
				'address' => 'template/index/1/search',
				'level' => 'template.list',
			),
			'template_block' => array (
				'name' => '区块',
				'address' => 'template/index/1/block',
				'level' => 'template.list',
			),
			'template_page' => array (
				'name' => '单页',
				'address' => 'template/index/1/page',
				'level' => 'template.list',
			),
			'template_frame' => array (
				'name' => '框架',
				'address' => 'template/index/1/frame',
				'level' => 'template.list',
			),
			'template_systemp' => array (
				'name' => '系统模板',
				'address' => 'template/systemp',
				'level' => 'template.systemp',
			),
			'template_inputtpl' => array (
				'name' => '导入模板',
				'address' => 'template/inputtpl',
				'level' => 'template.input',
			),
			'template_grouplist' => array (
				'name' => '模板组管理',
				'address' => 'template/grouplist',
				'level' => 'template.grouplist',
			),
		),
	),
	'task' => array (
		'name' => '任务管理',
		'address' => 'task/index',
		'level' => 'task.list',
		'target' => '',
		'myorder' => '6',
		'sub' => array (
			'task_index' => array (
				'name' => '任务管理',
				'address' => 'task/index',
				'level' => 'task.list',
			),
			'task_tohtml' => array (
				'name' => '页面生成',
				'address' => 'task/tohtml',
				'level' => 'task.dohtml_index',
			),
			'acquisition_index' => array (
				'name' => '采集管理',
				'address' => 'acquisition/index',
				'level' => 'task.acquisition_list',
			),
		),
	),
	'member' => array (
		'name' => '用户管理',
		'address' => 'member/index',
		'level' => 'member.list|model.dblist|admin.list',
		'target' => '',
		'myorder' => '7',
		'sub' => array (
			'member_list' => array (
				'name' => '用户列表',
				'address' => 'member/index',
				'level' => 'member.list',
			),
			'field_config' => array (
				'name' => '字段关联',
				'address' => 'member/field_config',
				'level' => 'member.field_config',
			),
			'data_update' => array (
				'name' => '数据同步',
				'address' => 'member/data_update',
				'level' => 'member.data_update',
			),
			'member_group' => array (
				'name' => '用户组管理',
				'address' => 'member/group',
				'level' => 'member.group',
			),
			'dblist_member' => array (
				'name' => '用户数据表',
				'address' => '/model/dblist/member',
				'level' => 'model.dblist',
			),
			'admin_mylist' => array (
				'name' => '管理员管理',
				'address' => 'admin/mylist',
				'level' => 'admin.list',
			),
			'admin_group_list' => array (
				'name' => '管理组管理',
				'address' => 'admin/group_list',
				'level' => 'admin.group_list',
			),
		),
	),
	'plugins' => array (
		'name' => '插件管理',
		'address' => 'plugins/index',
		'level' => 'plugins.list',
		'target' => '',
		'myorder' => '8',
		'sub' => array (
			'plugins_index' => array (
				'name' => '插件管理',
				'address' => 'plugins/index',
				'level' => 'plugins.list',
			),
			'plugins_add' => array (
				'name' => '安装插件',
				'address' => 'plugins/setup',
				'level' => '',
			),
		),
	),
);