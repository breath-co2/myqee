<?php
/**
 * 后台首页欢迎标语
 *
 * @var string
 */
$config['index_title'] = '欢迎您使用 MyQEE '.Core::VERSION.' 管理平台';

/**
 * 登录后台后是否检查更新
 *
 * @var boolean
 */
$config['check_update'] = true;

/**
 * 后台首页是否显示支持信息
 *
 * @var boolean
 */
$config['show_support'] = true;

/**
 * 后台管理模块默认数据表配置
 *
 * @var string
 */
$config['database'] = 'admin';

/**
 * 菜单配置列表
 *
    array(
        'default' => '默认菜单',
        'menu1' => '菜单1',
    )
    //例如设置成menu1后，系统讲读取admin/menu/menu1.config.php文件中配置的内容

 *
 * @var array
 */
$config['menu'] = array(
    'default' => '默认菜单',
);


/**
 * 登录尝试几次后显示验证码
 */
$config['login_error_show_captcha_num'] = 5;

/**
 * 24小时最大尝试次数，超过后当前IP将截止登录
 */
$config['login_max_error_num'] = 50;

/**
 * 管理员登录不活动超时时间，0表示只要浏览器不关闭则不限制
 *
 * @var int
 */
$config['admin_login_expired_time'] = 3600;