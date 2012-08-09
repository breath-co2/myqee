<?php
$config = array(
    'default' => array(
        'name' => '常规权限',
        'perm' => array(
            'view_serverinfo' => '首页查看服务器信息',
            'use_notepad'     => '使用首页便签',
            'view_phpinfo'    => '查看phpinfo()',
            'view_log'        => '查看操作日志',
            'update'		  => '系统升级',
        ),
    ),
    'administrator' => array(
        'name' => '后台用户管理权限',
        'perm' => array(
            '普通用户---------------------------------------------',
            'edit_self_password'               => '修改自己的密码',
            'edit_self_info'                   => '修改自己的信息',

            '用户管理权限------------------------------------------',
            'view_user_info'                   => '查看所有用户信息',
            'edit_user_info'                   => '修改所有用户信息',
            'change_user_password'             => '修改所有用户密码',
            'change_user_perm'                 => '修改所有用户权限',
            'add_new_user'                     => '创建新用户',
            'shield_user'                      => '屏蔽用户',
            'liftshield_user'                  => '解除屏蔽用户',
            'delete_user'                      => '删除用户',

            '权限组管理--------------------------------------------',
            'view_group_info'                  => '查看权限组信息',
            'edit_group_info'                  => '修改权限组信息',
            'edit_group_perm'                  => '修改权限组权限',
            'add_group'                        => '添加权限组',
            'delete_group'                     => '删除权限组',
            'edit_menu_config'                 => '修改用户菜单配置',
        ),
    ),
);