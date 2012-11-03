<?php
/**
 * 加载库
 *
 * @var array
 */
$config['libraries'] = array
(
    // 会自动加载的类库
    'autoload' => array
    (

    ),

    // 命令行下会加载的类库
    'cli'      => array
    (

    ),

    // 调试环境下会加载的类库
    'debug'    => array
    (
        'com.myqee.develop',
    ),

    // 后台加载的类库
    'admin'    => array
    (
        'com.myqee.administration',
    ),

    // 核心类库
    'core'     => array
    (
        'com.myqee.core',
    ),
);

/**
 * 静态资源的URL，可以是http://开头，例如 http://assets.test.com/
 *
 * 对应于wwwroot目录下的assets目录，建议绑定二级域名
 *
 * @var string
 */
$config['url']['assets'] = '/assets/';

/**
 * 读取配置时是否获取***.debug.config.php文件的配置
 *
 * 通常本地开发的测试服务器和正式服务器的环境配置都是不相同的，此开关可帮助你在本地读取补充配置
 * 例如设置true后：
 * database.config.php
 * 可被
 * database.debug.config.php
 * 里的数据覆盖
 *
 * @var boolean
 */
$config['debug_config'] = false;

/**
 * 开启调试模式的密码，支持多个
 *
 * @example $config['debug_open_password'] = array('pw1','pw2');
 * @var array
 */
$config['debug_open_password'] = array();

/**
 * 页面编码
 *
 * @var string
 */
$config['charset'] = 'utf-8';

/**
 * 网站根目录
 *
 * 设为null则自动判断
 *
 * @var string
 */
$config['base_url'] = null;

/**
 * 错误等级
 *
 * @var int
 */
$config['error_reporting'] = 7;

/**
 * 服务器默认文件夹文件
 * @var string
 */
$config['server_index_page'] = 'index.html';

/**
 * 默认控制器
 *
 * @var string
 */
$config['default_controller'] = 'index';

/**
 * 默认控制器方法
 *
 * @var string
 */
$config['default_action'] = 'default';

/**
 * 默认时区
 *
 * @var string
 * @see http://www.php.net/manual/en/timezones.php
 */
$config['timezone'] = 'PRC';

/**
 * 语言包
 * @var string
 */
$config['lang'] = 'zh-cn';

/**
 * 是否允许在线安装(及删除)应用
 *
 * @var boolean
 */
//$config['online_install_apps'] = true;

/**
 * WEB服务的服务器列表，留空则禁用同步功能（比如只有1台web服务器时请禁用此功能）
 *
 * 配置服务器后，可以实现服务器上data目录的文件同步功能，同步逻辑通过本系统完成，如果已经配置了data目录的sync同步机制，只需要配置1个主服务器即可
 * 可通过 Core::sync_exec($function,$param_1,$param_2,...); 实现在所有服务器上各自运行一遍$function
 *
 *	 //可以是内网IP，确保服务器之间可以相互访问到，端口请确保指定到apache/IIS/nginx等端口上
 *   array(
 * 	   '192.168.1.1',		//80端口可省略:80
 * 	   '192.168.1.2:81',
 * 	   '192.168.1.3:81',
 *   )
 *
 * @var array
 */
$config['web_server_list'] = array
(

);

/**
 * 调试环境打开关键字
 *
 * 可在php.ini中加入：
 *
 *   [MyQEE]
 *   myqee.debug = On
 *
 * 即可设置为默认打开调试模式，建议本地开发时设置，生产环境不设置php.ini即可
 *
 * @var string
 */
$config['local_debug_cfg'] = 'myqee.debug';

/**
 * 使用项目代码合并构建功能
 *
 * 此功能能够大大提升系统加载相应速度，建议远程环境开启，本地调试关闭
 * 如果设为自动，则本地调试环境打开是则禁用，远程启用
 * 也可在项目中设置
 * 注意，开启项目代码合并功能后，改动过文件需要执行合并脚本
 * false=关闭，true=开启，auto=自动
 *
 * !! 暂不支持
 *
 * @var boolean
 */
$config['use_bulider'] = false;


/**
 * 记录慢查询的时间，单位毫秒。0表示不记录
 *
 * 在shell下执行SQL不记录
 * 慢查询将都记录在 Log目录的slow_query目录下，按年月分目录记录。类似：
 *
 *    GET  22:46:33 -   9037 - 127.0.0.1       http://www.test.com/abc/?a=1
 *         22:46:33 -   3003 - SELECT * FROM `admin_member` WHERE `id` = '1'
 *         22:46:36 -   3000 - SELECT * FROM `test` WHERE `id` = '1'
 *
 *  表示：
 *    11点13分50秒GET请求的http://www.test.com/abc/?a=1页面产生的SQL
 *    执行时的时间    耗时(单位毫秒)   查询语句
 *
 * @var int
 */
$config['slow_query_mtime'] = 2000;


/**
 * 项目配置
 *
 * @var array
 *
 * url 可以是字符串也可以是数组，可以/开头，也可以http://开头。结尾不需要加/
 */
$config['projects'] = array
(
	'docs' => array
    (
		'name'	    => '项目手册',
		'isuse'	    => true,
		'dir'	    => 'docs',
		'url'	    => '/docs',
	),
	'admin' => array
    (
		'name'	    => '后台',
		'isuse'	    => true,
		'dir'	    => 'admin',
		'url'	    => '/admin',
	    'url_admin' => '/',
	),
	'default' => array
    (
		'name'      => '默认项目',
		'isuse'     => true,
		'dir'       => 'default',
		'url'	    => '/',
	),
);

