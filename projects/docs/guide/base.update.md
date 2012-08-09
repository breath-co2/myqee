版本升级
=============
以下主要列举版本之间的差异，这样你可以在升级时针对版本间的差异对自己的代码做必要的修改。

V2.0 RC1,RC2,RC3 与 V2.0正式版
---------------

* 常量N更名为CRLF
* Core::$local_debug 更换为 IS_DEBUG 常量
* Core::$is_cli 更换为 IS_CLI 常量
* 增加 DS 常量，为DIRECTORY_SEPARATOR的别名
* 增加 IS_MBSTRING 常量
* 类 Request 更换为 HttpIO，并且修改了部分变量，包括：
	* Request::$is_ajax    => HttpIO::IS_AJAX
    * Request::$client_ip  => HttpIO::IP
    * Request::$method     => HttpIO::METHOD
    * Request::$user_agent => HttpIO::USER_AGENT
    * Request::$protocol   => HttpIO::PROTOCOL
    * Request::$referrer   => HttpIO::REFERRER
* 将HttpGet重命名为HttpClient
* 增加HttpCall系统Http请求类库
* 后台控制器目录由admin/目录移动到controllers/\[admin\]/目录，并且后缀不再是.admin.php，而统一为.controller.php
* Shell控制器由shell/目录移动到controllers/\[shell\]/目录，并且后缀不再是.shell.php，而统一为.controller.php
* 根目录的config.php中$config['libraries']项里的配置有细微变化，例如原配置核心类库为MyQEE/Core，正式版统一为com开头，分隔符为“.”，即com.MyQEE.Core
* Core::import_library() 参数修改，原来是这样调用:Core::import_library('MyQEE/Test');现在是Core::import_library('com.MyQEE.Test');
* ORM_Finder_HttpGet 更名为 ORM_Finder_REST
* 增加Email,FTP类库
* 缓存驱动增加SQLite，WinCache，并完善Database驱动
* 数据库驱动增加SQLite