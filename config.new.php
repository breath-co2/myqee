<?php
/**
 * 项目配置
 *
 * 建议将default项目放在最后面
 *
 * @var array
 */
$config['projects'] = array
(
    // 请在这边加入其它项目，参照default
    /*
	'admin' => array
    (
		'name'      => '独立后台',
		'dir'       => 'admin',
        'isuse'     => true,
		'url'	    => '/admin/',
		'url_admin' => '/',
	),
	*/

    // 请确保Default放在最后
	'default' => array
    (
		'name'      => '默认项目',        //名称
		'dir'       => 'default',        //目录
        'isuse'     => true,             //是否启用
		'url'	    => '/',              //URL根目录，可以是字符串也可以是数组，可以/开头，也可以http://开头。
	    'url_admin' => '/admin/',        //后台URL根目录，同上
        /*
        // 此项目有的加载类库配置
        'libraries' => array
        (
            'autoload' => array(),
            'cli'      => array(),
            'debug'    => array(),
            'admin'    => array(),
        ),
        */
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
 * 实时配置文件
 *
 * 服务器通常设置为 server，本地开发可设置为 dev1, dev_jonwang 等等。只允许a-z0-9_字符串，留空则禁用
 *
 * 用途说明：
 * 在团队成员开发时，个人的配置和服务器配置可能会有所不同，所以每个人希望有一个自己独有的配置文件可覆盖默认配置，通过runtime_config设置可轻松读取不同的配置
 * 比如，在服务器上设置 `$config['runtime_config'] = 'server';` 在本地开发时设置 `$config['runtime_config'] = 'dev';`
 * 那么，服务器上除了会读取 `config.php` 还会再读取 `config.server.php` 的配置文件，而在开发环境上则读取 `config.dev.php` 配置文件
 *
 * !!! V2中 `$config['debug_config'] = false;` 参数已废弃，可用次参数设为debug实现类似功能
 *
 * @var string
 */
$config['runtime_config'] = '';


/**
 * 用于 http://domain/opendebugger 页面开启在线debug功能
 *
 * key为用户名，value为密码
 * 支持多个，留空则关闭此功能
 *
 * @example $config['debug_open_password'] = array('user1'=>'pw1','user2'=>'pw2');
 * @var array
 */
$config['debug_open_password'] = array
(
    //'myqee' => '123456',
);


/**
 * 调试环境打开关键字
 *
 * 可在php.ini中加入：
 *
 *   [MyQEE]
 *   myqee.debug = On
 *
 *
 * 强烈推荐在本地开发时开启此功能，方便开发。但注意：生产环境中绝不能在php.ini设置
 *
 * @var string
 */
$config['local_debug_cfg'] = 'myqee.debug';

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
 * 默认语言包
 *
 * @var string
 */
$config['lang'] = 'zh-cn';


/**
 * HTTPS是否开启的关键字，通常不用改
 *
 * @var string
 */
$config['server_https_on_key'] = 'HTTPS';


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
 * 关闭错误页面记录错误数据
 *
 * @boolean
 */
$config['error500']['close'] = false;

/**
 * 错误页面数据记录方式
 *
 * file     - 文件(默认方式)
 * database - 数据库
 * cache    - 缓存保存
 *
 * @string
 */
$config['error500']['save_type'] = 'file';

/**
 * 错误页面数据记录方式对应配置
 *
 * 例如save_type为database，则此参数为数据库的配置名
 * 如果save_type为cache，则此参数为驱动的配置名
 *
 * @string
 */
$config['error500']['type_config'] = 'default';


/**
 * assets允许的文件后缀名，用|隔开
 *
 * @var string
 */
$config['asset_allow_suffix'] = 'js|css|jpg|jpeg|png|gif|bmp|pdf|html|htm|mp4|swf';


/**
 * 加载库配置
 *
 * @var array
 */
$config['libraries'] = array
(
    // 默认会自动加载的类库
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

    // 后台会加载的类库
    'admin'    => array
    (
        'com.myqee.administration',
    ),
);


/**
 * nodejs 执行文件默认路径
 * 此功能在devassets等处理css时用到，通常不用改，除非你的node安装目录不是默认目录
 *
 * 留空则使用默认值：
 *   Window:
 *      程序路径 c:\Program Files\nodejs\node.exe
 *      模块路径 c:\Program Files\nodejs\node_modules\
 *   其它系统:
 *      程序路径 /usr/local/bin/node
 *      模块路径 /usr/local/lib/node_modules/
 *
 * @array
 */
$config['nodejs'] = array
(
    '',    // 执行脚本路径，留空则默认
    '',    // node_modules路径，留空则默认
);






