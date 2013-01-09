系统常量
==============

START_TIME
---------------
请求开始时间，常量

    //获取已执行时间
    $time = microtime(true) - START_TIME;
    echo number_format($time,3);

START_MEMORY
---------------
请求开始时占用内存，常量

EXT
-------
系统PHP文件的后缀，默认值为.php，常量

TIME
-------
请求时的时间戳，常量。这样在你的程序里可以用TIME取代time()方法了，当然，如果你的脚本执行很长时间，TIME是不变的，而time()为当前的时间戳

IS_WIN
-------
当前服务器是否window服务器，常量，布尔型

IS_DEBUG
-------
是否开启了DEBUG模式，常量，布尔型

CRLF
-------
换行符，常量，\r\n，即window系统的换行方式
>**（V2.0 RC1-RC3中为常量“N”，在正式版中常量“N”已被删除，改用CRLF）**


DIR_SYSTEM
-----------
系统实际路径，常量。结尾有/（window下是反斜杠“\”），可在index.php中进行设置。

DIR_PROJECT
-----------
项目文件夹路径，常量，通常是在系统目录下project目录的实际路径，其它同上。

DIR_WWWROOT
-----------
www文件夹路径，常量，通常是在系统目录下wwwroot目录的实际路径，其它同上。

DIR_DATA
-----------
数据文件存放路径，常量，通常是在系统目录下data目录的实际路径，其它同上。

DIR_LIBRARY
-----------
类库文件存放路径，常量，通常是在系统目录下libraries目录的实际路径，其它同上。

DIR_SHELL
-----------
系统SHELL运行脚本目录，常量，通常是在系统目录下shell目录的实际路径，其它同上。

DIR_LOG
-----------
系统LOG存放目录，常量，通常是在data目录的log/目录。

IS_CLI
--------------
2.0正式版新增。判断是否命令行运行，true = 是,false = 否
>(V2.0 RC1-RC3 中为 Core::$is_cli

MAGIC_QUOTES_GPC
-----------
系统是否启用了magic_quotes_gpc，常量，布尔型，PHP5.4版本及以上，此常量===false

URL_ASSETS
-----------
静态资源URL，可在根目录的config.php中配置 $config['url']['assets'] 进行修改


__( $string, array $values = null )
------------
多国语言输出函数

    /**
     * 语言包
     * (http://php.net/strtr) is used for replacing parameters.
     *
     * __('Welcome back, :user', array(':user' => $username));
     *
     * @uses    I18n::get
     * @param   string  text to translate
     * @param   array   values to replace in the translated text
     * @param   string  target language
     * @return  string
     */

Core核心类
==============


Core::$config
-------------
系统配置内容

Core::$project
-------------
当前项目名，字符串型


Core::$charset
--------------
页面编码，可通过config.php的$config['charset']进行设置

Core::$include_path
-------------
返回当前项目所包含的类库目录

Core::trace($msg = 'Trace Tree', $code = E_NOTICE)
--------------
输出程序调试跟踪结果，页面会输出类似以下内容：[Link](docs/index/trace)
<iframe src="docs/index/trace" framespacing="0" frameborder="no" width="100%" height="280"></iframe>
**注意：只有开启本地调试环境时，才会显示调试信息。** [如何开启本地调试环境](dev.debug.html)

Core::auto_load($class)
---------------
自动加载类，系统已通过spl_autoload_register(array('Core', 'auto_load'));方法注册自动加载，若您需要对自动加载规则进行修改，可扩展Core::auto_load()方法或再注册一个自己的auto_load方法。

Core::find_file($dir, $file, $ext = null, $auto_require = false, $project = null)
---------------
寻找文件。这个功能比较常用。

    /**
     * 查找文件
     * 
     * @param string $dir 目录
     * @param string $file 文件
     * @param string $ext 后缀 例如：.html
     * @param boolean $auto_require 是否自动加载上来，对config,i18n无效
     * @param string $project 跨项目读取文件
     */
     
* $dir是文件夹（比如：类的文件是classes,模块的是model，必需小写）
* $file是文件，不需要传后缀
* $ext后缀，不传则为默认（EXT常量的值）
* $auto_require 若找到文件，是否自动包含执行该文件
* project 指定项目名，不传则为当前

若没有找到指定文件，则返回false。
注意，config,i18n文件夹返回的是所有符合条件的文件的数组，而其它则是返回优先级最高的那个文件。
比如：在libraries/MyQEE/Core/classes/和project/default/classes/分别都有一个Test.class.php 则系统将会返回project/default/classes/的Test.class.php的实际路径。
而config和i18n的则不一样，它会返回所有文件的数组，不过是你通常不需要直接使用这个Core::find_file()去获取config和i18n文件，而是直接使用Core::config()来获取相关数据。

一些简单例子：

    //希望获取一个wwwroot目录下的js/test.js文件具体路径
    $file = Core::find_file('wwwroot','js/test','js');
    echo $file;
    

    // 在视图里包含另外一个视图文件
    include Core::find_file('views','test/abc');

Core::config($key, $project = null)
---------------
    /**
     * 获取指定key的配置
     *
     * @param string $key
     * @param string $project 跨项目读取配置，若本项目内的不需要传
     * @return fixed 返回配置
     */

一些例子：

    //core.开头将获取系统及项目的核心配置，而并非core.config.php里的内容
    //将返回根目录config.php文件或项目config.php（项目优先）的$config['charset']
    Core::config('core.charset');   //utf-8
    
    //将返回config/database.config.php文件中全部配置
    Core::config('database');
    
    //将返回config/database.config.php文件中$config['default']
    Core::config('database.default');
    
    //支持文件夹中的文件，将返回config/test/abc.config.php文件中$config['type']
    Core::config('test/abc.type');

Core::cookie()
---------------
返回一个实例化后的cookie对象，通过此方法拿到的都为同一个对象

Core::route()
---------------
返回一个实例化后的路由对象，其它同上

Core::url()
---------------
返回一个实例化后的URL处理对象，其它同上

Core::log($msg , $type = 'log')
---------------
记录日志
$msg为日志内容，$type为日志类型，例如log,error,info,debug等可任意自定义


Core::debug()
---------------
返回一个debug调试对象，此方法只会作用于测试环境，在生产环境中会被忽略。
本系统的调试信息默认是通过FirePHP输出的，所以您需要使用Firefox浏览器并且安装FireBug+FirePHP，进入到FireBug的控制台，您可看到相关调试信息。

    //输出一个文本信息提示
    Core::debug()->info('test');
    
    //SQL Profiler是否开启，若开启则返回true
    Core::debug()->profiler('sql')->is_open();

更详细的请访问：[代码调试](dev.debug.html) 章节(带截图)

Core::import_library($lib)
----------------
动态载入指定类库

类库被载入后不可移除，类库将被加在项目目录之下的最高优先级，若已经包含了目录则不会加入，且不会调整原先的优先级

例如，原来的目录是
array('a','b','c'); 其中a是项目目录，则如果加入d的话，最后的结果将是array('a','d','b','c');
