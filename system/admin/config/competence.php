<?php
$config['index'] = array(
	'name' => '管理首页',
	'sub' => array(
		'indexshowpath' => '欢迎页显示服务器信息',
		'cache' => '缓存管理页',
		'config' => '修改站点配置',
		'config_bak' => '恢复站点配置',
		'runsql' => '执行SQL语句',
		'phpinfo' => '查看phpinfo',
		'adminmenu' => '后台菜单管理',
        'adminmenu_edit' => '后台菜单编辑',
        'adminmenu_add' => '后台菜单添加',
		'site_list' => '站点管理',
		'site_add' => '站点添加',
		'site_edit' => '站点修改',
		'site_del' => '删除站点',
	),
);

$config['class'] = array(
	'name' => '栏目管理',
	'sub' => array(
		'list' => '列表列表',
		'add' => '栏目添加',
		'edit' => '栏目修改',
		'edit_paixu' => '修改栏目排序',
		'del' => '栏目删除',
		'navigation' => '导航设置',
		'set' => '参数批量设置',
		'dbchangesite' => '更换数据表所属站点',
		'special_list' => '专题列表',
		'special_add' => '添加专题',
		'special_del' => '删除专题',
		'special_delinfo' => '删除专题信息',
		'special_manageinfo' => '管理专题信息',
	),
);

$config['member'] = array(
	'name' => '用户管理',
	'sub' => array(
		'list' => '用户列表',
		'group' => '用户组管理',
		'field_config' => '字段关联',
		'data_update' => '数据同步',
	),
);

$config['info'] = array(
	'name' => '内容管理',
	'sub' => array(
		'list' => '内容列表',
		'list_showclasstree' => '列表显示栏目',
		'list_showdbtree' => '列表显示数据表',
		'edit' => '信息修改',
		'view' => '查看信息',
		'add' => '信息添加',
		'del' => '信息删除',
		'move' => '信息移动',
		'setvalue' => '属性设置',
		'block_list' => '碎片管理',
		'block_add' => '碎片添加',
		'block_edit' => '碎片修改',
		'comment' => '评论查看',
		'comment_check' => '评论审核',
		'comment_del' => '评论删除',
		'uploadlist' => '上传文件列表',
		'uploaddel' => '删除上传文件',
		'uploadedit' => '修改上传文件',
		'uploadfile' => '上传文件',
		'uploadexplorer' => '上传资源管理器',
        'custompage' => '自定义页面管理',
        'custompageadd' => '新增自定义页面',
        'custompageedit' => '修改自定义页面',
        'custompagedel' => '删除自定义页面',
        'customlist' => '自定义列表管理',
        'customlistadd' => '新增自定义列表',
        'customlistedit' => '修改自定义列表',
        'customlistdel' => '删除自定义列表',
		'mydata_list' => '数据调用',
		'mydata_add' => '添加数据调用',
		'mydata_copy' => '复制数据调用',
		'mydata_edit' => '修改数据调用',
		'mydata_order' => '修改数据调用排序',
		'mydata_del' => '删除数据调用',
		'mydata_input' => '数据调用导入',
		'mydata_output' => '数据调用导出',
		'mydata_renewfiles' => '更新数据调用配置',
        'mylink_lists' => '快速链接',
		'mylink_order' => '修改快速链接排序',
        'mylink_add' => '快速链接添加',
        'mylink_edit' => '快速链接编辑',
		'mylink_del' => '删除快速链接',
        'mylink_child_links' => '快速链接导航设置',
        'save_links' => '保存快速链接导航',
		'mylink_renewfiles' => '更新快速链接配置',
		'mylink_output' => '快速链接导出',
		'mylink_input' => '快速链接导入',
	),
);


$config['model'] = array(
	'name' => '模型/数据表管理',
	'sub' => array(
		'list' => '模型列表',
		'add' => '添加模型',
		'edit' => '修改模型',
		'del' => '删除模型',
		'editfield' => '修改模型字段',
		'output' => '导出模型',
		'input' => '导入模型',
		'renewfiles' => '更新模型缓存',
		'dblist' => '数据表列表',
		'dbadd' => '添加数据表',
		'dbedit' => '修改数据表',
		'dbdel' => '删除数据表',
		'dbfieldlist' => '数据表字段管理',
		'dbfieldlist' => '数据表字段管理',
		'dbfieldadd' => '添加数据表字段',
		'dbfieldedit' => '修改数据表字段',
		'dbfielddel' => '删除数据表字段',
		'dborder' => '数据表排序',
		'dboutput' => '导出数据表',
		'dbinput' => '导入数据表',
		'dbrenewfiles' => '更新数据表缓存',
	),
);

$config['template'] = array(
	'name' => '模板管理',
	'sub' => array(
		'list' => '模板列表',
		'add' => '添加模板',
		'edit' => '修改模板',
		'del' => '删除模板',
		'output' => '导出模板',
		'input' => '导入模板',
		'renewfiles' => '更新缓存',
		'grouplist' => '模板组管理',
		'groupadd' => '添加模板组',
		'groupedit' => '修改模板组',
		'groupdel' => '删除模板组',
		'systemp' => '系统模板设置',
	),
);


$config['task'] = array(
	'name' => '任务管理',
	'sub' => array(
		'list' => '任务管理',
		'task_add' => '添加任务',
		'task_edit' => '修改任务',
		'task_del' => '删除任务',
		'task_input' => '导入任务',
		'task_output' => '导出任务',
		'task_renewfiles' => '更新任务 配置文件',
		'dohtml_index' => '生成首页',
		'dohtml_siteindex' => '生成子站点首页',
		'dohtml_custompage' => '生成自定义页',
		'dohtml_customlist' => '生成自定义列表页',
		'dohtml_class' => '生成栏目页',
		'dohtml_info' => '生成内容页',
		'acquisition_list' => '采集管理',
		'acquisition_add' => '采集添加',
		'acquisition_edit' => '采集修改',
		'acquisition_del' => '采集修改',
		'acquisition_datatodb' => '采集数据入库',
		'acquisition_run' => '运行采集程序',
		//'urlread_list' => '远程读取',
	),
);


$config['admin'] = array(
	'name' => '管理员管理',
	'sub' => array(
		'list' => '管理员列表',
		'changepassword_1' => '修改本人密码',
		'changepassword_2' => '修改他人密码',
		'changecompetence' => '修改权限',
		'add' => '添加管理员',
		'del' => '管理员删除',
		'group_list' => '管理组列表',
		'group_edit' => '管理组修改',
		'group_add' => '管理组添加',
		'group_del' => '管理组删除',
	),
);



$config['plugins'] = array(
	'name' => '插件管理',
	'sub' => array(
		'list' => '插件管理',
		'setup' => '安装插件',
		'edit' => '修改插件',
		'del' => '卸载插件',
		'createsetupfile' => '打包插件',
		'setup' => '安装插件',
		'uninstall' => '卸载插件',
	),
);