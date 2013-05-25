<?php
/**
 * 后台首页欢迎标语
 *
 * @var string
 */
$config['admin/core']['index_title'] = '欢迎您使用 MyQEE '.MyQEE_VERSION.' 管理平台';

/**
 * 登录后台后是否检查更新
 *
 * @var boolean
 */
$config['admin/core']['check_update'] = true;

/**
 * 后台首页是否显示支持信息
 *
 * @var boolean
 */
$config['admin/core']['show_support'] = true;

/**
 * 后台管理模块默认数据表配置
 *
 * @var string
 */
$config['admin/core']['database'] = 'admin';

/**
 * 登录尝试几次后显示验证码
 */
$config['admin/core']['login_error_show_captcha_num'] = 5;

/**
 * 24小时最大尝试次数，超过后当前IP将截止登录
 */
$config['admin/core']['login_max_error_num'] = 50;

/**
 * 管理员登录不活动超时时间，0表示只要浏览器不关闭则不限制
 *
 * @var int
 */
$config['admin/core']['admin_login_expired_time'] = 3600;




/**
 * 超管用户表名称
 *
 * @var string
 */
$config['admin/administrator']['tablename'] = 'admin_member';

/**
 * 管理员组表名称
 *
 * @var string
 */
$config['admin/administrator']['group']['tablename'] = 'admin_member_group';

/**
 * 管理员组和管理员对应表名称
 *
 * @var string
 */
$config['admin/administrator']['group']['ids_tablename'] = 'admin_member_group_ids';


/**
 * 超管用户表名称
 *
 * @var string
 */
$config['admin/log']['tablename'] = 'admin_log';


$config['admin/pagination']['current_page'] = array
(
    'source' => 'default',
    'key'    => '0',
);

/**
 * 后台分页默认视图
 *
 * @var string
*/
$config['admin/pagination']['view'] = 'pagination/floating';

/**
 * 分页如果只有1页是否自动隐藏
 *
 * @var boolean
 */
$config['admin/pagination']['auto_hide'] = true;

/**
 * 每页显示数
 *
 * @var int
 */
$config['admin/pagination']['items_per_page'] = 100;




/**
 * 后台权限配置
 *
 * @var array
 */
$config['admin/permission'] = array
(
    'default' => array
    (
        'name' => '常规权限',
        'perm' => array(
            'view_serverinfo' => '首页查看服务器信息',
            'use_notepad'     => '使用首页便签',
            'view_phpinfo'    => '查看phpinfo()',
            'view_log'        => '查看操作日志',
            'update'		  => '系统升级',
        ),
    ),
    'administrator' => array
    (
        'name' => '后台用户管理权限',
        'perm' => array
        (
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


/**
 * 默认菜单配置
 */
$config['admin/menu/default'] = array
(
    '_name' => '默认菜单',
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
        'sup_admin' => array
        (
            'innerHTML' => '高级管理',
            'prem' => 'index.supadmin',
            'admin_log' => array
            (
            	'prem' => 'index.supadmin.logs.list',
                'innerHTML' => '查看操作日志',
                'href' => 'view_logs/',
            ),
        ),
    ),

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






/**
 * 权限验证配置
 */
$config['auth'] = array
(
    'admin' => array
    (
        'driver'             => Auth::DRIVER_DATABASE,
        'database'           => $config['admin/core']['database'],
        'tablename'          => 'admin_member',
        'username_field'     => 'username',
        'password_field'     => 'password',
        'member_object_name' => 'ORM_Admin_Member_Data',
    ),
);



/**
 * CoreCofnig数据库配置
 *
 * @var string
 */
$config['configuration']['database'] = $config['admin/core']['database'];

/**
 * 表名称
 *
 * @var string
 */
$config['configuration']['tablename'] = 'admin_config';





/**
 * Cookie设置作用的域
 *
 * @var string
 */
$config['cookie']['domain'] = $_SERVER['HTTP_HOST'];

/**
 * 浏览器进程有效
 *
 * @var boolean
 */
$config['cookie']['httponly'] = true;



/**
 * Session名
 *
 * @var string
 */
$config['session']['name'] = 'adminsid';

