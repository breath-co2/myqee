<?php
$config = array
(
    'index' => array
    (
        'innerHTML' => '管理首页',
        'style'     => 'font-weight:bold',
        'href'      => '/',
        'dev_tools' => array
        (
            'prem'      => 'index.phpinfo',
            'innerHTML' => '开发工具',
            'phpinfo'   => array
            (
                'innerHTML' => 'phpinfo()',
                'href'      => 'phpinfo/',
                'perm'      => 'default.view_phpinfo',
            ),
        ),
        /*
        'sup_admin' => array(
            'innerHTML' => '高级管理',
            'prem' => 'index.supadmin',
            'admin_log' => array(
            	'prem' => 'index.supadmin.logs.list',
                'innerHTML' => '查看操作日志',
                'href' => 'view_logs/',
            ),
        ),
        */
    ),

    /*
    'apps' => array(
        'innerHTML' => '应用管理',
        'icon' => 'icon/menu_5.png',
         'list' => array(
            'innerHTML' => '应用商店',
            'list' => array(
                'innerHTML' => '精选应用',
                'href' => 'apps/',
            ),
            'paihang' => array(
                'innerHTML' => '排行榜',
                'href' => 'apps/paihang',
            ),
            'cat' => array(
                'innerHTML' => '应用分类',
                'href' => 'apps/cat',
            ),
            'buyed' => array(
                'innerHTML' => '已购买的应用',
                'href' => 'apps/buyed',
            ),
            'update' => array(
                'innerHTML' => '更新',
                'href' => 'apps/update',
            ),
        ),
    ),
    */

    'member' => array
    (
        'innerHTML' => '管理员管理',
        'admin'     => array
        (
            'innerHTML' => '成员管理',
        	'list'      => array
            (
                'innerHTML' => '管理员列表',
                'href'      => 'administrator/',
            	'perm'      => 'administrator.view_user_info||administrator.is_group_manager',
            ),
        	'add' => array
            (
                'innerHTML' => '添加管理员',
                'href'      => 'administrator/add',
            	'perm'      => 'administrator.add_new_user||administrator.is_group_manager',
            ),
        ),
        'admin_group' => array
        (
            'innerHTML' => '组管理',
        	'list' => array
            (
                'innerHTML' => '权限组列表',
                'href'      => 'administrator/group/',
                'perm'      => 'administrator.view_group_info||administrator.can_edit_group',
            ),
        	'add' => array
            (
                'innerHTML' => '添加权限组',
                'href'      => 'administrator/group/add',
            	'perm'      => 'administrator.add_group',
            ),
        ),
    ),
);