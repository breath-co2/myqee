# 使用多项目

MyQEE 一大特色就是多项目支持，并且可以在程序上隔离开，比如在A项目中可以有Test类，在B项目中也可以有一个Test类，相互不影响；而如果希望在任何项目中都使用到同一个类或配置等，只需要把它放在团队类库（或第三方类库或模块、驱动）中就可以了。

MyQEE 的多项目是如何运行的？<Br>
首先，你需要按下面的方法设置好自己的项目，特别是url部分，这样，当一个请求发出时，系统会根据URL的前缀部分去按顺序匹配项目，如果匹配到项目，则系统将调用这个项目的相关配置进行执行

## 多项目设置
------------
在根目录 `config.php` 文件中，有一个 `$config['projects']` 设置了程序的所有项目，内容大致如下：

    /**
     * 项目配置
     * @var array
     * url 可以是字符串也可以是数组，可以/开头，也可以http://开头。结尾不需要加/
     */
    $config['projects'] = array
    (
        'docs' => array
        (
            'name'      => '项目手册',
            'isuse'     => true,
            'dir'       => 'docs',
            'url'       => '/docs',
        ),
        'admin' => array
        (
            'name'      => '后台',
            'isuse'     => true,
            'dir'       => 'admin',
            'url'       => '/admin',
            'url_admin' => '/',
        ),
        'default' => array
        (
            'name'      => '默认项目',
            'isuse'     => true,
            'dir'       => 'default',
            'url'       => array
            (
                '/',
            ),
        ),
    );

下面详细说明下这个配置。
以上的例子中说明有3个项目，分别是项目手册，后台和默认项目，他们的URL分别是/docs，/admin和/，这意味着，URL是/docs开头的都将被归纳到手册项目，/admin开头的URL将被归纳到后台项目，其它都为默认项目。

**URL的设置：**
上例中手册项目设置的是 `/docs` 也可以设置成 `http://www.myqee.com/docs` 唯一的区别是，如果设置成后者通过 `http://v2.myqee.com/docs` 这样的URL来访问就不会被认为是这个项目的。
URL的设置是支持多个的。

注意到后台项目的 `url_admin` 参数了么？这表示在此项目后面的哪部分是进入到后台控制器。这样设置就表示所有 `/admin/` 开头的URL都是后台控制器。
如果设置成 `url='/admin'` 而 `url_admin='/abc'` 则其实 `/admin/def` 这的URL进入的是前台控制器，而只有 `/admin/abc/` 这样的URL才会进入后台控制器（什么是后台控制器？请看[控制器章节](core/class.controller/index.html)）


## 项目加载类库

加载目录可通过根目录的 `config.php` 的 `$config['libraries']` 进行设置。

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
	        'com.myqee.develop',           // 加载libraries/myqee/develop/ 目录类库
	    ),
	
	    // 后台加载的类库
	    'admin'    => array
	    (
	        'com.myqee.administration',    // 加载libraries/myqee/administration/ 目录类库
	    ),
	);

这些设置会影响全部项目的加载，如果只想设置某个项目的加载类库，可在项目目录的 `config.php` 进行设置。
系统加载的类库包括：

* 项目目录的类库
* `$config['libraries']['autoload']` 中设置的加载的类库
* `$config['libraries']['core']` 设置的类库


