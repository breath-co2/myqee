# 核心类库相关配置
核心类库的配置就在 `core/config.php` 中，提供了默认的配置信息，如果你需要对它内容进行扩展（或替换），无须修改这个文件，请直接修改 `team-library/config.php` 或项目中的 `config.php` 配置即可，比如 `$config['cookie']` 就可以替换或扩展掉core中的 `$config['cookie']` 相关配置。

## 数据库配置

数据库配置为 `$config['database']` 相关参数，下面是一个常见的数据库配置

	$config['database']['default'] = array
	(
	    'type' => 'MySQLI',
	    'connection' => array
	    (
	        'hostname'   => '127.0.0.1',
	        'database'   => 'myqee',
	        'username'   => 'root',
	        'password'   => '123456',
	        'persistent' => false,
	    ),
	    'table_prefix' => '',
	    'charset'      => 'utf8',
	    'caching'      => false,
	    'profiling'    => true,
	);

实例化数据库，方法一：

	// 实例化一个数据库对象
	$db1 = new Database('default');

	// 当配置关键字为default时可省略
	$db2 = new Database();

	$db3 = new Database();

	// $db2 和 $db3 为两个不同的实例化对象
	var_dump($db2===$db3);		// false


方法二：

	// 实例化一个可重复使用的数据库对象
	$db1 = Database::instance('default');

	// 可省略default
	$db2 = Database::instance();

	$db3 = Database::instance('test');

	// $db1 和 $db2 为同一个实例化对象
	var_dump($db1===$db2);		// true
	var_dump($db1===$db3);		// false


参数说明

参数          |  说明        | 可选值
-------------|--------------|--------
type         | 数据库类型     | `MySQL`, `MySQLI`, `Mongo`, `SQLite`, `Postgre`
connection   | 数据库连接设置 |
table_prefix | 表前缀        | 例如 `test_`, `myqee_` 等
charset      | 编码          | `utf8`, `gbk` 等
caching      | 是否缓存查询   | `true` | `false`
profiling    | 是否开启性能统计 | `true` | `false`


`connection` 说明：

可以为一个连接符，比如 `mysqli://root:123456@127.0.0.1/myqee/`

`hostname` 参数说明:
参数          |  说明
-------------|--------------
hostname     | 服务器，例如 `127.0.0.1`, 支持主从设置
database     | 库名称，例如 `myqee`
username     | 数据库账号
password     | 数据库密码
persistent   | 是否长连接， `true` | `false`

###主从数据库的设置方法

只要把 `hostname` 设置成数组即可，例如：

	$config['database']['default'] = array
	(
	    'type' => 'MySQLI',
	    'connection' => array
	    (
	        'hostname'   => array
	        (
	            'master' => '192.168.1.1',
	            'slaver' => array
	            (
	                '192.168.1.2',
	                '192.168.1.3',
	                '192.168.1.3',
	                '192.168.1.4',
	            ),
	        ),
	        'database'   => 'myqee',
	        'username'   => 'root',
	        'password'   => '123456',
	        'persistent' => false,
	    ),
	    'table_prefix' => '',
	    'charset'      => 'utf8',
	    'caching'      => false,
	    'profiling'    => true,
	);

如上设置表示，`192.168.1.1` 为主数据库， `192.168.1.2`, `192.168.1.3`, `192.168.1.4` 为从数据库，其中 `192.168.1.3` 有2个会增加它的命中率，例子中它的命中率将达到50%



## 缓存配置

缓存配置为 `$config['cache']` 相关参数，下面是一个常见的缓存配置

	// 默认配置
	$config['cache']['default'] = array
	(
	    'driver'        => 'Redis',	        // 驱动类型
	    'driver_config' => 'default',      // 当前驱动的配置
	);

    // redis驱动默认配置
	$config['cache/redis']['default'] = array
	(
        array
	    (
	        'host'       => '192.168.1.1',
	        'port'       => 6379,
	        'persistent' => true,
	        'timeout'    => 2,
	    ),
	);



	// test配置
	$config['cache']['test'] = array
	(
	    'driver'        => 'Memcache',
	    'driver_config' => 'default',
	);

    // memcache驱动默认配置
	$config['cache/memcache']['default'] = array
	(
        array
	    (
	        'host'   => '192.168.1.1',
	        'port'   => 2001,
	        'weight' => 20
	    ),
	    array
	    (
	        'host'   => '192.168.1.2',
	        'port'   => 2001,
	        'weight' => 20
	    ),
	);

上例中总共配置了2个缓存配置组 `default` 和 `test`，使用时很简单，如果想用 `default` 配置的缓存，只要

	$cache = Cache::instance();
	$cache->set('key', 'value');

	//或

	$cache = new Cache();
	$cache->set('key', 'value');

如果想使用 `test` 组的配置缓存，只要换个key即可，例如 `Cache::instance('test')->set('key', 'value');`

**MyQEE的配置都具有类似的统一性，基本上依葫芦画瓢即可，这里以上例中 `default` 的配置说明下：**

首先，它设置的 `driver`（即驱动）为Redis，而 `driver_config` (即当前驱动配置)就是指定 `$config['cache/redis']` 中的配置关键字为 `default`，所以当我们使用 `Cache::instance()` 时实际上就是第一个配置告诉缓存类是要用Redis作为储存驱动，系统会加载Redis的相关类，然后再会根据配置里的redis的配置去连接到相应的redis服务器上，最终实现程序的需求。

当然，这配置也可以合并起来，写成这样：


	$config['cache']['default'] = array
	(
	    'driver'        => 'Redis',
	    'driver_config' => array
		(
	        array
		    (
		        'host'       => '192.168.1.1',
		        'port'       => 6379,
		        'persistent' => true,   // 是否长连接
		        'timeout'    => 2,      // 秒
		    ),
		)
    );

你会看到，`driver_config` 如果是字符串就是表示对应的配置的key，而如果是数组，则是实际的配置


缓存配置中，驱动类型支持包括:

参数          |  说明
-------------|--------
`Memcache`   | Memcahe 缓存
`File`       | 文件缓存
`Redis`      | Redis，需要安装Redis扩展， [PhpRedis](https://github.com/nicolasff/phpredis) 或 [Pecl](http://pecl.php.net/package/redis)
`Apc`        | Apc缓存
`Database`   | 数据库，使用数据库缓存请按下面的DDL建立一个表
`SQLite`     | SQLite数据库，同上，需要初始化一个SQLite的表
`WinCache`   |

数据库，SQLite 类型的DDL：

``` mysql
CREATE TABLE `cache` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(32) NOT NULL,
  `key_str` varchar(255) NOT NULL,
  `value` longtext,
  `expire_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`),
  KEY `expire_time` (`expire_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

数据库你的数据库不是MySQL等，可参考创建相同的字段即可



## Cookie默认配置

系统内置的默认cookie设置为

	$config['cookie'] = array
	(
	    'domain'   => '',
	    'prefix'   => '',
	    'path'     => '/',
	    'expire'   => 0,
	    'secure'   => false,
	    'httponly' => false,
	);

你可根据自己项目具体情况更改

## Session默认配置

系统内置默认Session配置为：

	$config['session'] = array
	(
	    'name'           => 'SID',    // SessionID名称
	    'expiration'     => 0,
	    'gc_probability' => 0,
	    'check_string'   => '$@de23#$%@.dG2.p4Ad',    // 校验字符串
	);

强烈建议更改 `check_string` 有助于提高系统安全，比如在根目录的 `team-library` 目录的 `config.php` 中加入

	$config['session']['check_string'] = 'your rand code';

即可


## 分页组配置

分页配置存放在 `$config['pagination']` 相关配置中，比如系统默认提供的为

	$config['pagination']['default'] = array
	(
	    'current_page' => array
	    (
	        'source' => 'default',
	        'key'    => '0' ,
	    ),
	    'total_items'    => 0,
	    'items_per_page' => 10,
	    'view'           => 'pagination/basic',
	    'auto_hide'      => true,
	);

使用方法

	$page = new Pagination();      // key为default可省略
	$page->total_items = 1000;	    // 设置总数，可在数据库或缓存中先获取到

	echo $page->render();			// 输出分页的HTML


其中 `current_page` 中的 `source` 和 `key` 的用途如下：
`source` 有 `default` 和 `query_string` 和 `route` 3个值可选

* `default`为默认，表示分页数在uri中的，比如这样的url：<br>
  /test/abc/1<br>
  /test/abc/2<br>
  /test/abc/3<br>
  ...<br>
  这种分页就可以用默认值，假设test是控制器，abc是action，那么后面的分页就是在第一个参数，那么此时，`key` 设置成0 即可，如果分页不在第一个参数位，那么可对应设置
* `query_string` 表示分页在$_GET参数中，比如$_GET['page']，这样 `key` 设置成 `page` 即可
* `route` 表示当前URL是使用一个路由转发，可参考路由章节说明


分页中 `view` 是指定分页的视图文件，系统默认提供 `pagination/basic` 和 `pagination/floating`，如果想自定义，可在视图的 pagination 目录中加入自己的视图即可。



## 日志配置

默认配置：

	$config['log'] = array
	(
	    'use'    => true,            // 是否启用记录日志，默认启用
	    'format' => ':time - :host::port - :url - :msg',
	);

其中format表示日志格式参数模板，可用参数包括：

参数        | 说明
-----------|---------
`:time`    | 当前时间
`:url`     | 请求的URL
`:msg`     | 日志信息
`:type`    | 日志类型
`:host`    | 服务器
`:port`    | 端口
`:ip`      | 请求的IP
`:agent`   | 客户端信息
`:referer` | 来源页面


## 文件保存同步

同步模式设置

	$config['file_sync_mode'] = 'default';

可选参数：

参数      | 说明
---------|---------
default  | 全部轮询同步
rsync    | 到主服务器上执行操作，然后由系统rsync进行同步更新
none     | 表示不同步操作

系统内部调用接口密钥，留空则系统会使用全部core配置和database序列化拼接后md5产生

    $config['system_exec_key'] = '';

WEB服务的服务器列表，**留空则禁用同步功能**（比如只有1台web服务器时请禁用此功能）

	$config['web_server_list'] = array
	(

	);

配置服务器后，可以实现服务器上data目录的文件同步功能，同步逻辑通过本系统完成，如果已经配置了data目录的sync同步机制，只需要配置1个主服务器即可

可通过 HttpCall::sync_exec('test/abc','arg1','arg2','arg3'); 实现在所有服务器上各自运行一遍

    //可以是内网IP，确保服务器之间可以相互访问到，端口请确保指定到apache/IIS/nginx等端口上
    array
    (
      '192.168.1.1',        //80端口可省略:80
      '192.168.1.2:81',
      '192.168.1.3:81',
    );


通过上面3个参数设置后，3种模式会有不一样的运行方式

* default - 当保存一个文件时，系统会通过内部机制把改动信息通知到 `web_server_list` 中所有的服务器，并且只有当所有服务器均返回`success` 时才会返回true，否则会返回false，并且会有相应的DEBUG信息输出
* rsync   - 当保存一个文件时，不管当前执行的服务器是 `web_server_list` 中的哪个，都会调用主的服务器（即第一个）去执行文件操作，而你的服务器需要自行配置一个rsync同步到其它服务器的逻辑，这个在框架中不存在的。
* none    - 即表示当单机对待



## 文件类型

当前的文件类型的设置如下，如果有必要增加或修改只要在项目或团队配置中加入类似 `$config['mimes']['8z'] = array('application/x-7z-compressed');` 这样的设置即可，其中key为文件后缀，value为文件类型

	$config['mimes'] = array
	(
	    '323'      => array('text/h323'),
	    '7z'       => array('application/x-7z-compressed'),
	    'abw'      => array('application/x-abiword'),
	    'acx'      => array('application/internet-property-stream'),
	    'ai'       => array('application/postscript'),
	    'aif'      => array('audio/x-aiff'),
	    'aifc'     => array('audio/x-aiff'),
	    'aiff'     => array('audio/x-aiff'),
	    'amf'      => array('application/x-amf'),
	    'asf'      => array('video/x-ms-asf'),
	    'asr'      => array('video/x-ms-asf'),
	    'asx'      => array('video/x-ms-asf'),
	    'atom'     => array('application/atom+xml'),
	    'avi'      => array('video/avi', 'video/msvideo', 'video/x-msvideo'),
	    'bin'      => array('application/octet-stream','application/macbinary'),
	    'bmp'      => array('image/bmp'),
	    'c'        => array('text/x-csrc'),
	    'c++'      => array('text/x-c++src'),
	    'cab'      => array('application/x-cab'),
	    'cc'       => array('text/x-c++src'),
	    'cda'      => array('application/x-cdf'),
	    'class'    => array('application/octet-stream'),
	    'cpp'      => array('text/x-c++src'),
	    'cpt'      => array('application/mac-compactpro'),
	    'csh'      => array('text/x-csh'),
	    'css'      => array('text/css'),
	    'csv'      => array('text/x-comma-separated-values', 'application/vnd.ms-excel', 'text/comma-separated-values', 'text/csv'),
	    'dbk'      => array('application/docbook+xml'),
	    'dcr'      => array('application/x-director'),
	    'deb'      => array('application/x-debian-package'),
	    'diff'     => array('text/x-diff'),
	    'dir'      => array('application/x-director'),
	    'divx'     => array('video/divx'),
	    'dll'      => array('application/octet-stream', 'application/x-msdos-program'),
	    'dmg'      => array('application/x-apple-diskimage'),
	    'dms'      => array('application/octet-stream'),
	    'doc'      => array('application/msword'),
	    'docx'     => array('application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
	    'dvi'      => array('application/x-dvi'),
	    'dxr'      => array('application/x-director'),
	    'eml'      => array('message/rfc822'),
	    'eps'      => array('application/postscript'),
	    'evy'      => array('application/envoy'),
	    'exe'      => array('application/x-msdos-program', 'application/octet-stream'),
	    'fla'      => array('application/octet-stream'),
	    'flac'     => array('application/x-flac'),
	    'flc'      => array('video/flc'),
	    'fli'      => array('video/fli'),
	    'flv'      => array('video/x-flv'),
	    'gif'      => array('image/gif'),
	    'gtar'     => array('application/x-gtar'),
	    'gz'       => array('application/x-gzip'),
	    'h'        => array('text/x-chdr'),
	    'h++'      => array('text/x-c++hdr'),
	    'hh'       => array('text/x-c++hdr'),
	    'hpp'      => array('text/x-c++hdr'),
	    'hqx'      => array('application/mac-binhex40'),
	    'hs'       => array('text/x-haskell'),
	    'htm'      => array('text/html'),
	    'html'     => array('text/html'),
	    'ico'      => array('image/x-icon'),
	    'ics'      => array('text/calendar'),
	    'iii'      => array('application/x-iphone'),
	    'ins'      => array('application/x-internet-signup'),
	    'iso'      => array('application/x-iso9660-image'),
	    'isp'      => array('application/x-internet-signup'),
	    'jar'      => array('application/java-archive'),
	    'java'     => array('application/x-java-applet'),
	    'jpe'      => array('image/jpeg', 'image/pjpeg'),
	    'jpeg'     => array('image/jpeg', 'image/pjpeg'),
	    'jpg'      => array('image/jpeg', 'image/pjpeg'),
	    'js'       => array('application/x-javascript'),
	    'json'     => array('application/json'),
	    'latex'    => array('application/x-latex'),
	    'lha'      => array('application/octet-stream'),
	    'log'      => array('text/plain', 'text/x-log'),
	    'lzh'      => array('application/octet-stream'),
	    'm4a'      => array('audio/mpeg'),
	    'm4p'      => array('video/mp4v-es'),
	    'm4v'      => array('video/mp4'),
	    'man'      => array('application/x-troff-man'),
	    'mdb'      => array('application/x-msaccess'),
	    'midi'     => array('audio/midi'),
	    'mid'      => array('audio/midi'),
	    'mif'      => array('application/vnd.mif'),
	    'mka'      => array('audio/x-matroska'),
	    'mkv'      => array('video/x-matroska'),
	    'mov'      => array('video/quicktime'),
	    'movie'    => array('video/x-sgi-movie'),
	    'mp2'      => array('audio/mpeg'),
	    'mp3'      => array('audio/mpeg'),
	    'mp4'      => array('application/mp4','audio/mp4','video/mp4'),
	    'mpa'      => array('video/mpeg'),
	    'mpe'      => array('video/mpeg'),
	    'mpeg'     => array('video/mpeg'),
	    'mpg'      => array('video/mpeg'),
	    'mpg4'     => array('video/mp4'),
	    'mpga'     => array('audio/mpeg'),
	    'mpp'      => array('application/vnd.ms-project'),
	    'mpv'      => array('video/x-matroska'),
	    'mpv2'     => array('video/mpeg'),
	    'ms'       => array('application/x-troff-ms'),
	    'msg'      => array('application/msoutlook','application/x-msg'),
	    'msi'      => array('application/x-msi'),
	    'nws'      => array('message/rfc822'),
	    'oda'      => array('application/oda'),
	    'odb'      => array('application/vnd.oasis.opendocument.database'),
	    'odc'      => array('application/vnd.oasis.opendocument.chart'),
	    'odf'      => array('application/vnd.oasis.opendocument.forumla'),
	    'odg'      => array('application/vnd.oasis.opendocument.graphics'),
	    'odi'      => array('application/vnd.oasis.opendocument.image'),
	    'odm'      => array('application/vnd.oasis.opendocument.text-master'),
	    'odp'      => array('application/vnd.oasis.opendocument.presentation'),
	    'ods'      => array('application/vnd.oasis.opendocument.spreadsheet'),
	    'odt'      => array('application/vnd.oasis.opendocument.text'),
	    'oga'      => array('audio/ogg'),
	    'ogg'      => array('application/ogg'),
	    'ogv'      => array('video/ogg'),
	    'otg'      => array('application/vnd.oasis.opendocument.graphics-template'),
	    'oth'      => array('application/vnd.oasis.opendocument.web'),
	    'otp'      => array('application/vnd.oasis.opendocument.presentation-template'),
	    'ots'      => array('application/vnd.oasis.opendocument.spreadsheet-template'),
	    'ott'      => array('application/vnd.oasis.opendocument.template'),
	    'p'        => array('text/x-pascal'),
	    'pas'      => array('text/x-pascal'),
	    'patch'    => array('text/x-diff'),
	    'pbm'      => array('image/x-portable-bitmap'),
	    'pdf'      => array('application/pdf', 'application/x-download'),
	    'php'      => array('application/x-httpd-php'),
	    'php3'     => array('application/x-httpd-php'),
	    'php4'     => array('application/x-httpd-php'),
	    'php5'     => array('application/x-httpd-php'),
	    'phps'     => array('application/x-httpd-php-source'),
	    'phtml'    => array('application/x-httpd-php'),
	    'pl'       => array('text/x-perl'),
	    'pm'       => array('text/x-perl'),
	    'png'      => array('image/png', 'image/x-png'),
	    'po'       => array('text/x-gettext-translation'),
	    'pot'      => array('application/vnd.ms-powerpoint'),
	    'pps'      => array('application/vnd.ms-powerpoint'),
	    'ppt'      => array('application/powerpoint'),
	    'pptx'     => array('application/vnd.openxmlformats-officedocument.presentationml.presentation'),
	    'ps'       => array('application/postscript'),
	    'psd'      => array('application/x-photoshop', 'image/x-photoshop'),
	    'pub'      => array('application/x-mspublisher'),
	    'py'       => array('text/x-python'),
	    'qt'       => array('video/quicktime'),
	    'ra'       => array('audio/x-realaudio'),
	    'ram'      => array('audio/x-realaudio', 'audio/x-pn-realaudio'),
	    'rar'      => array('application/rar'),
	    'rgb'      => array('image/x-rgb'),
	    'rm'       => array('audio/x-pn-realaudio'),
	    'rpm'      => array('audio/x-pn-realaudio-plugin', 'application/x-redhat-package-manager'),
	    'rss'      => array('application/rss+xml'),
	    'rtf'      => array('text/rtf'),
	    'rtx'      => array('text/richtext'),
	    'rv'       => array('video/vnd.rn-realvideo'),
	    'sea'      => array('application/octet-stream'),
	    'sh'       => array('text/x-sh'),
	    'shtml'    => array('text/html'),
	    'sit'      => array('application/x-stuffit'),
	    'smi'      => array('application/smil'),
	    'smil'     => array('application/smil'),
	    'so'       => array('application/octet-stream'),
	    'src'      => array('application/x-wais-source'),
	    'svg'      => array('image/svg+xml'),
	    'swf'      => array('application/x-shockwave-flash'),
	    't'        => array('application/x-troff'),
	    'tar'      => array('application/x-tar'),
	    'tcl'      => array('text/x-tcl'),
	    'tex'      => array('application/x-tex'),
	    'text'     => array('text/plain'),
	    'texti'    => array('application/x-texinfo'),
	    'textinfo' => array('application/x-texinfo'),
	    'tgz'      => array('application/x-tar'),
	    'tif'      => array('image/tiff'),
	    'tiff'     => array('image/tiff'),
	    'torrent'  => array('application/x-bittorrent'),
	    'tr'       => array('application/x-troff'),
	    'tsv'      => array('text/tab-separated-values'),
	    'txt'      => array('text/plain'),
	    'wav'      => array('audio/x-wav'),
	    'wax'      => array('audio/x-ms-wax'),
	    'wbxml'    => array('application/wbxml'),
	    'webm'     => array('video/webm'),
	    'wm'       => array('video/x-ms-wm'),
	    'wma'      => array('audio/x-ms-wma'),
	    'wmd'      => array('application/x-ms-wmd'),
	    'wmlc'     => array('application/wmlc'),
	    'wmv'      => array('video/x-ms-wmv', 'application/octet-stream'),
	    'wmx'      => array('video/x-ms-wmx'),
	    'wmz'      => array('application/x-ms-wmz'),
	    'word'     => array('application/msword', 'application/octet-stream'),
	    'wp5'      => array('application/wordperfect5.1'),
	    'wpd'      => array('application/vnd.wordperfect'),
	    'wvx'      => array('video/x-ms-wvx'),
	    'xbm'      => array('image/x-xbitmap'),
	    'xcf'      => array('image/xcf'),
	    'xhtml'    => array('application/xhtml+xml'),
	    'xht'      => array('application/xhtml+xml'),
	    'xl'       => array('application/excel', 'application/vnd.ms-excel'),
	    'xla'      => array('application/excel', 'application/vnd.ms-excel'),
	    'xlc'      => array('application/excel', 'application/vnd.ms-excel'),
	    'xlm'      => array('application/excel', 'application/vnd.ms-excel'),
	    'xls'      => array('application/excel', 'application/vnd.ms-excel'),
	    'xlsx'     => array('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
	    'xlt'      => array('application/excel', 'application/vnd.ms-excel'),
	    'xml'      => array('text/xml', 'application/xml'),
	    'xof'      => array('x-world/x-vrml'),
	    'xpm'      => array('image/x-xpixmap'),
	    'xsl'      => array('text/xml'),
	    'xvid'     => array('video/x-xvid'),
	    'xwd'      => array('image/x-xwindowdump'),
	    'z'        => array('application/x-compress'),
	    'zip'      => array('application/x-zip', 'application/zip', 'application/x-zip-compressed'),
	);
