轻松掌握MyQEE
=============

MyQEE多项目、站点支持
-----------
一套程序多个项目，甚至多个站点，用MyQEE轻易做到。
例如MyQEE官方站点，默认项目为myqee,而http://www.myqee.com/docs/ 则为手册项目，http://www.myqee.com/admin/ 则为后台项目。
他们使用的类库相互独立也有相互重叠，比如后台项目使用了MyQEE的Administration类库，而其它则没有，程序运行于SAE平台，所以设置了每个项目都加载了MyQEE/SAE类库。
所以，你可以将用到的公共配置，视图，类库，ORM，Model等放在自己的一个类库里，每个项目都可以加载，项目自己的文件就放在项目里，完美实现多站点代码共享与分离。

首先，了解MyQEE系统是怎么运行的
----------------------------

1. 客户端请求URL统一由index.php文件进入：<br>
   加载libraries/bootstrap.php文件并加载根目录config.php配置
2. 匹配项目：<br>
   根据url以及config.php的项目配置确定项目，读取项目配置
3. 加载类库和autoload功能：<br>
   根据项目配置加载类库目录，系统默认会包含libraries/MyQEE/Core类库。选择加载对应的类库目录，系统初始化，可自动加载类库。
4. 匹配控制器：<br>
   寻找控制器有2种模式，路由和系统默认方式。路由配置可在全局配置里设置也可在项目配置里设置，具体可见[路由配置](dev.route.html)一节，也可参考[控制器](mvc.controller.html)一节。最后执行控制器输出内容。

多项目设置
------------
在根目录config.php文件中，有一个$config['projects']设置了程序的所有项目，内容大致如下：

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
上例中手册项目设置的是/docs也可以设置成http://www.myqee.com/docs唯一的区别是，如果设置成后者通过http://v2.myqee.com/docs这样的URL来访问就不会被认为是这个项目的。
URL的设置是支持多个的。

注意到后台项目的url_admin参数了么？这表示在此项目后面的哪部分是进入到后台控制器。这样设置就表示所有/admin/开头的URL都是后台控制器。
如果设置成url='/admin'而url_admin='/abc' 则其实/admin/def这的URL进入的是前台控制器，而只有/admin/abc/这样的URL才会进入后台控制器（什么是后台控制器？请看[控制器章节](mvc.controller.html)）


项目加载目录
--------------
加载目录可通过根目录的config.php的$config['libraries']进行设置。

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
	        'com.MyQEE.Develop_Tools',		// 加载libraries/MyQEE/Develop_Tools/ 目录类库
	    ),
	
	    // 后台加载的类库
	    'admin'    => array
	    (
	        'com.MyQEE.Administration',		// 加载libraries/MyQEE/Administration/ 目录类库
	    ),
	
	    // 核心类库
	    'core'     => array
	    (
	        'com.MyQEE.Core',				// 加载libraries/MyQEE/Core/ 目录类库
	    ),
	);

这些设置会影响全部项目的加载，如果只想设置某个项目的加载类库，可在项目的ini.php进行设置。
系统加载的类库包括：

* 项目目录的类库
* $config['libraries']['autoload']中设置的加载的类库
* $config['libraries']['core'] 设置的类库


