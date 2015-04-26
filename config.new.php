<?php
/*
 *************** 简要说明，必看 ******************

 1.本地开发时，请务必找到 $config['local_debug_cfg'] 把它设置成 true（生产环境务必关闭），这样你可使用Chrome浏览器并安装我在做的插件（也可FireFox+FireBug+FirePHP插件），打开控制器台切换到FirePHP标签刷新页面就可以看到程序调试输出的信息了
 2.希望开启线上调试可设置 $config['debug_open_password'] 参数的帐号和密码，这样开启后，就可查看和本地开发模式一样的调试信息
 3.如果只有1个项目，使用我们提供的 $config['projects'] 配置即可，如果需要增加，可以自己增加
 4.如果线上有多个PHP服务器，请配置 $config['web_server_list'] 项目
 5.每个参数都有详细使用说明，有兴趣可以看看

*/




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
        'url'       => '/admin/',
        'url_admin' => '/',
    ),
    */

    // 请确保Default放在最后
    'default' => array
    (
        'name'      => '默认项目',        //名称
        'dir'       => 'default',        //目录
        'isuse'     => true,             //是否启用
        'url'       => '/',              //URL根目录，可以是字符串也可以是数组，可以/开头，也可以http://开头。
        'url_admin' => '/admin/',        //后台URL根目录，同上
        'url_rest'  => '/api/',          //RESTFul接口起始URI，同上
        /*
        // 此项目有的加载类库配置
        'libraries' => array
        (
            'autoload' => array(),
            'cli'      => array(),
            'debug'    => array(),
            'admin'    => array(),
            'rest'     => array(),
        ),
        */
    ),
);


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

    // RESTFul模式时会加载的类库
    'rest'     => array
    (

    ),

    // 调试环境下会加载的类库
    'debug'    => array
    (
        'com.myqee.develop',
    ),

    // 后台模式时会加载的类库
    'admin'    => array
    (
        'com.myqee.administration',
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
 * 开发实时配置文件，留空则禁用此功能
 *
 * 服务器通常可设置为 server，本地开发可设置为 dev1, dev_myname 等等。只允许a-z0-9_字符串
 *
 * 用途说明：
 * 在团队成员开发时，个人的配置和服务器配置可能会有所不同，所以每个人希望有一个自己独有的配置文件可覆盖默认配置，通过此参数可轻松读取不同的配置
 * 比如，在服务器上设置 `$config['env_config_suffix'] = 'server';` 在本地开发时设置 `$config['env_config_suffix'] = 'dev';`
 * 那么，服务器上除了会读取 `config.php` 还会再读取 `config.server.env.php` 的配置文件，而在开发环境上则读取 `config.dev.env.php` 配置文件
 *
 * !!! 只会读取根目录、团队类库和项目中的 .runtime.php，不支持类库(含Core)中 .env.php
 * !!! V2中 `$config['debug_config'] = false;` 参数已废弃，可用此参数设为debug实现类似功能
 *
 * @var string
 */
$config['env_config_suffix'] = '';





//---------------------------------------------------------- 开发调试相关



/**
 * 用于 http://your_domain/opendebugger 页面开启在线debug功能
 *
 * key为用户名，value为密码的md5值，支持多个，留空则关闭此功能
 *
 * !!! 密码为md5后的值，并非密码明文
 *
 * @example $config['debug_open_password'] = array('user1'=>'6e6fdf956d04289354dcf1619e28fe77', 'user2'=>'6e54dcf166fdf956d04289319e28fe77');
 * @var array
 */
$config['debug_open_password'] = array
(
    //'myqee' => 'e10adc6e057f20f8833949ba59abbe5e',
);


/**
 * 打开开发调试环境的关键字
 *
 * !!! 本地开发强烈推荐开启，生产环境务必关闭。
 *
 * 使用方法：
 * 首先需要安装浏览器插件。 Firefox 里安装 FireBug 和 FireBug 插件，Chrome 安装我制作的 FirePHP 插件，下面有插件地址。
 * 安装好后，Firefox打开控制器，Chrome打开开发者工具并切换到FirePHP标签。
 * 打开任意MyQEE的php页面，你就可以看到相关的系统输出的信息，这些信息在关闭时时不会有任何输出的。
 * 生成环境可以用上面的 `$config['debug_open_password']` 参数设置后开启关闭
 *
 * 如何在程序中自行输出些 debug 信息？
 *
 * 在程序中加入 `Core::debug()->info('test message');`，然后刷新页面看看，在控制台就会得到 test message 的内容
 *
 * `Core::debug()` 常用方法举例：
 *
 *     Core::debug()->info('信息内容');
 *     Core::debug()->error('错误信息');
 *     Core::debug()->group('分组开启');
 *     Core::debug()->log('日志信息');
 *     Core::debug()->warn('警示信息');
 *     Core::debug()->table('表格', array(
 *         array('标题一','二','三'),
 *         array('行1列1','行1列2','行1列3'),
 *         array('行2列1','行2列2','行2列3'),
 *     ));
 *     Core::debug()->groupEnd();      //分组关闭
 *
 * 详细用法可参考文档 <http://www.myqee.com/docs/zh-cn/dev.html> 或 <http://www.firephp.org/HQ/Use.htm> 的方法
 *
 * 我制作的 FirePHP For Chrome 插件下载地址: <https://chrome.google.com/webstore/detail/firephp/gkkcbhnljobgoehijcpenalbacdmhaff?hl=zh-CN&utm_source=chrome-ntp-launcher&authuser=1>
 * 若被墙打不开链接，请到我博客去下载插件 <http://www.queyang.com/blog/archives/549>
 *
 * 参数说明：可以是字符串也可以是bool类型
 *
 * 参数       |  说明
 * ----------|-----------
 * `true`    | 表示一定开启本地开发模式
 * `false`   | 表示一定关闭本地开发模式
 * 字符串     | 根据 php.ini 中设置判断
 *
 * 设置成字符串时：比如 `$config['local_debug_cfg'] = 'myqee.debug'` 则在 php.ini 中加入如下内容可开启本地 debug，否则关闭：
 *
 *      myqee.debug = On
 *
 * 如果值为 `$config['local_debug_cfg'] = 'test.abc'` 则可在php.ini中加入如下内容可开启本地 debug，否则关闭：
 *
 *     test.abc = On
 *
 * @var string|bool
 */
$config['local_debug_cfg'] = false;


/**
 * 当DEBUG开启后，是否关闭 /phpinfo 页面的访问
 *
 * 设成成 false 时，当DEBUG开启（包括本地开发模式开启）时，打开根目录 /phpinfo 页面则直接输出 `phpinfo()` 的内容
 * 设置成 true， 则禁用此功能，打开此页面则会显示404错误页面
 *
 * @var bool
 */
$config['disable_phpinfo_page'] = false;









//----------------------------------------------------------系统设置相关



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
 * Data文件、Log、文件缓存等文件写入模式
 *
 * 参数                        | 描述
 * ---------------------------|--------------------------------------------------------------------
 * normal                     | 正常的文件写入，请确保相应目录有写入权限
 * disable                    | 禁用所有写入并丢弃内容，可用于SAE,BAE等程序目录不允许写入的安全级别高的环境，安全级别高
 * db://for_file/filetable    | 用于目录不能写入内容又不希望丢弃数据的情况，系统自动转为写入数据库，将会写入 `$db = new Database('for_file');` 表名称 `filetable` 中
 * cache://for_file/prefix_   | 同上，将会使用缓存对象写入 `$cache = new Cache('for_file');` 缓存前缀为 `prefix_`
 *
 * @string
 */
$config['file_write_mode'] = 'normal';


/**
 * 500错误页面相关设置
 *
 * @var array
 */
$config['error500'] = array
(
    /**
     * 关闭错误页面记录错误数据功能
     *
     * true - 关闭.关闭后所有的500错误页面只在页面上输出简单错误数据，错误信息不记录在服务器上
     *
     * @boolean
     */
    'close' => false,

    /**
     * 错误页面数据记录方式
     *
     * 参数      | 描述
     * ---------|-----
     * file     | 文件(默认方式)
     * database | 数据库
     * cache    | 缓存保存
     *
     * @string
     */
    'save_type' => 'file',

    /**
     * 错误页面数据记录方式对应配置
     *
     * 例如save_type为database，则此参数为数据库的配置名
     * 如果save_type为cache，则此参数为驱动的配置名
     *
     * @string
     */
    'type_config' => 'default',
);


/**
 * 错误等级
 *
 * 开发环境可以用 E_ALL 表示所有的错误都会抛出错误，可以帮助你发现一些不容易察觉的编写不规范的代码
 * 正式环境可用 7, 即 `E_WARNING ^ E_PARSE ^ E_ERROR`，只会有 E_ERROR、E_WARNING 和 E_PARSE 错误才会报错
 *
 * @link http://php.net/manual/zh/function.error-reporting.php
 * @var int
 */
$config['error_reporting'] = 7;


/**
 * 服务器默认文件夹文件
 *
 * @example index.htm, default.html
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
 *
 * 多个语言包请用,隔开
 * 设置成 auto 则表示根据浏览器自动选择
 *
 * 例如 zh-cn，采样的语言包将是 `i18n/zh-cn.lang` 文件
 *
 * @var string
 */
$config['lang'] = 'auto';


/**
 * 默认语言包，仅当 `$config['lang']` 设置成 auto 时有效
 *
 * 即便 `$config['lang']` 设置成 auto 时，此参数仍旧有效，此参数设置成 null 才停用此功能
 * 例如 zh-cn
 *
 * @var string
 */
$config['default_lang'] = 'zh-cn';


/**
 * 可用于html中meta的语言类型数据变量
 *
 *      <meta http-equiv="Content-Language" content="zh-CN" />
 *      或
 *      <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="zh-CN" lang="zh-CN">
 *
 */
$config['html_content_language'] = 'zh-CN';


/**
 * 语言包缓存时间
 *
 *  `$config['i18n_cache_time'] = '2592000~5184000,1/10000'` 表示缓存30-60天，在30-60天后1/10000几率命中后更新缓存
 *
 * @var string
 */
$config['lang_cache_time'] = '2592000~5184000,1/10000';


/**
 * 动态语言包cookie设置的key名称，用于国际化网站用户，可以根据自己语言选择设置对应的语言
 *
 * 即便 `$config['lang']` 设置成 auto 时，此参数仍旧有效，此参数设置成 null 才停用此功能
 * 例如: `$config['local_lang_cookie_name'] = 'lang'` 表示当前动态语言包名称是获取 `$_COOKIE['lang']` 的值
 *
 * @var string
 */
$config['local_lang_cookie_name'] = null;       // 例如 lang


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
 * URL后缀
 *
 * 例如设置成：html，则可以接受 http://localhost/test.html 这样的请求，并且通过 `Core::url()` 生成的url也带.html后缀
 *
 * @var string
 */
$config['url_suffix'] = '';

/**
 * assets允许的文件后缀名，用|隔开
 *
 * @var string
 */
$config['asset_allow_suffix'] = 'js|css|jpg|jpeg|png|gif|bmp|pdf|html|htm|mp4|swf';


/**
 * HTML5自动跨越请求支持
 *
 * 开启后，如果遇到AJAX跨越请求，则会自动加上 Access-Control-Allow-Origin 的支持
 * 注意，只有支持HTML5的此协议的浏览器有用，IE6,7等浏览器不支持这个
 *
 *      header("Access-Control-Allow-Origin: http://.../');
 *
 * none - 不自动处理
 * auto - 自动（可自动允许相同主域名下的所有的请求）
 * 也可设置一个数组，指定允许的域名，支持通配符*。例如：
 *
 *      $config['ajax_cross_domain'] = array
 *      (
 *          '*.myqee.com',
 *          '*.myqee.sinaapp.com',
 *          'www.queyang.com',
 *      );
 *
 * @var string|array
 */
$config['ajax_cross_domain'] = 'auto';


/**
 * 是否隐藏 X-Powered-By 头部版本输出
 *
 * true   - 隐藏
 * false  - 显示
 * string - 自定义输出的头信息
 *
 * @var bool|string
 */
$config['hide_x_powered_by_header'] = false;







//---------------------------------------------------------- CSRF 相关

/**
 * POST请求模式下自动检查引用页，如果是同主域名下的请求，将被通过，否则返回406错误
 *
 * 开启后将屏蔽所有非本域下URL的POST请求，建议开启，可有效避免 CSRF 攻击
 *
 * 自行检查的方法: `HttpIO::csrf_check()`, 若返回 `true` 则表示检查通过
 *
 * 若需要某个控制器关闭自动检查，在控制器里设置变量 `public $auto_check_post_method_referrer = false` 即可，反之亦然，例如：
 *
 *      class Controller_Test extends Controller
 *      {
 *          // 设置不自动验证
 *          public $auto_check_post_method_referrer = false;
 *
 *          public function action_default()
 *          {
 *              echo 'hi';
 *          }
 *      }
 *
 * @see http://baike.baidu.com/view/1609487.htm
 * @see http://www.nxadmin.com/web/924.html
 * @return boolean
 */
$config['auto_check_post_method_referrer'] = true;


/**
 * 在表单使用token时创建校验数据存放在服务器缓存中的时间，单位秒
 *
 * 设置成0表示禁用缓存保存校验数据，此时数据将随表单一起输出，为了提高安全请设置 `$config['form_token_hash_key']` 值
 * 设置成0将不会对服务器造成缓存数据压力，但相对于把验证数据存在服务器安全性会差些，此时所以的校验将依赖 `$config['form_token_hash_key']`，并且存在在有效期内被重复利用的可能
 *
 * @var string
 */
$config['form_token_cache_time'] = 86400;


/**
 * 表单使用token时存放随机key的缓存配置名，默认null即 `Cache::DEFAULT_CONFIG_NAME` 的值
 *
 * 在 `Form::open('uri', array(), true)` 和 `Form::check_token()` 时使用到
 *
 * @var string
 */
$config['form_token_cache_name'] = null;


/**
 * 在表单使用token时创建hash值时用到的key
 *
 * @var string
 */
$config['form_token_hash_key'] = '';









// ----------------------------------------------------------- 服务器同步

/**
 * 文件保存同步模式
 *
 * 可选参数 default|rsync|none
 *
 * 参数     | 说明
 * --------|---------------------------------
 * default | 全部轮询同步
 * rsync   | 到主服务器上执行操作，然后由系统rsync进行同步更新
 * none    | 表示不同步操作
 *
 * @var string
 */
$config['file_sync_mode'] = 'default';


/**
 * 系统内部调用接口密钥，留空则系统会使用全部core配置和database序列化拼接后md5产生
 *
 * @var string
 */
$config['system_exec_key'] = '';


/**
 * WEB服务的服务器列表，留空则禁用同步功能（比如只有1台web服务器时请禁用此功能）
 *
 * 配置服务器后，可以实现服务器上data目录的文件同步功能，同步逻辑通过本系统完成，如果已经配置了data目录的sync同步机制，只需要配置1个主服务器即可
 * 并且系统 `File` 类库的所有文件操作也会根据设置进行同步保存
 * 可通过 HttpCall::sync_exec('test/abc','arg1','arg2','arg3'); 实现在所有服务器上各自运行一遍
 *
 *     //可以是内网IP，确保服务器之间可以相互访问到，端口请确保指定到apache/IIS/nginx等端口上
 *     array
 *     (
 *         '192.168.1.1',        //80端口可省略:80
 *         '192.168.1.2:81',
 *         '192.168.1.3:81',
 *     )
 *
 * @var array
 */
$config['web_server_list'] = array
(
    'default' => array
    (

    )
);


// ----------------------------------------------------------- 日志设置

/**
 * 日志配置
 *
 * 日志存放目录在index.php中配置
 *
 * format 可以有的参数包括：
 *
 *   :time     当前时间
 *   :url      请求的URL
 *   :msg      日志信息
 *   :type     日志类型
 *   :host     服务器
 *   :port     端口
 *   :ip       请求的IP
 *   :agent    客户端信息
 *   :referrer 来源页面
 *
 * @var array
 */
$config['log'] = array
(
    'use'   => true,          // 是否启用记录日志，默认启用
    'level' => LOG_NOTICE,    // 记录日志等级，只有等级在这个之上(含)的日志才会被记录
);

/**
 * fluent 日志分发设置
 *
 * 推荐使用fluent日志分发系统
 *
 * @see http://docs.fluentd.org/
 * @var array
 */
$config['log']['fluent'] = 'tcp://127.0.0.1:24224';
