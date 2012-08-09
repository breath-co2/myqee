代码调试
=========
为方便查看调试信息，请使用Firefox+Firebug插件+FirePHP插件
Firebug : [http://getfirebug.com/](http://getfirebug.com/)
FirePHP : [http://www.firephp.org/](http://www.firephp.org/)


本地调试环境开启
--------------
首先查看config.php文件中$config['local_debug_cfg']的值（若没有修改，系统默认为：myqee.debug）
打开php.ini文件，添加如下内容：

    myqee.debug = On

您可以设置不同的开始调试方式，比如

    $config['local_debug_cfg'] = 'testdebug';

然后在php.ini里增加一行：

    testdebug = On

即可。（修改php.ini需要重启服务器才可生效）
**请不要在服务器的正式环境上做如上设置，在需要查看服务器调试信息，可通过下面的“远程调试模式开启”模式查看。**

开启本地调试环境后，你可以浏览MyQEE程序并开启Firebug，此时，您在“控制台”栏会看到系统输出的详细信息，如下截图：
<img src="/~docs~demo_img/dev_debug_1.gif" />
***请放心，在未开启调试模式的情况下，是不会输出以上图片的内容的***


远程调试模式开启
--------------
在生产环境中，或或者您未修改php.ini时，可通过项目根目录下opendebugger/页面输入密码开启调试模式，例如：[http://www.myqee.com/opendebugger](http://www.myqee.com/opendebugger)页面，密码可在config.php文件中的

    $config['debug_open_password'] = array();

设置，支持多个。若不想使用本功能，保持$config['debug_open_password']为空即可。

开启调试环境后，就和开启“本地调试环境”具有相同的效果。

查看页面执行分析信息
-------------
*此功能必须开启本地调试环境或者开启远程调试模式下才可用。*
在URL后加入: ?debug=yes 若已经有参数，则加入&debug=yes，例如:http://www.myqee.com/?debug=yes
此时，在页面最下面会多出一栏调试栏，把页面滚动到最后，你会发现页面结尾有一个表示显示执行信息内容，如下图：
<img src="/~docs~demo_img/dev_debug_2.gif" />

查看SQL分析信息
--------------
在URL后加入: ?debug=sql 若已经有参数，则加入&debug=sql 或者 在调试工具栏上勾选“SQL:Explain”，然后点击“GO”按钮查看。
会出现类似如下图示：
<a href="/~docs~demo_img/dev_debug_3.gif" target="_blank"><img width="690" src="/~docs~demo_img/dev_debug_3.gif" /></a>
会比前面的执行信息多一个Database项目。里面会有每一条SQL的分析结果。

再来一张演示图片：
<img src="/~docs~demo_img/dev_debug_4.gif" />


自定义输出调试信息
----------------
输出调试信息其实很简单，主要使用Core::debug()方法即可实现

例如：实现FirePHP的输出信息：

    Core::debug()->info('信息内容');
    Core::debug()->error('错误信息');
    Core::debug()->group('分组开启');
    Core::debug()->log('日志信息');
    Core::debug()->warn('警示信息');
    Core::debug()->table('表格',
        array(
            array('标题一','二','三'),
            array('行1列1','行1列2','行1列3'),
            array('行2列1','行2列2','行2列3'),
        )
    );
    Core::debug()->groupEnd();      //分组关闭
    
输出截图如下：
<img src="/~docs~demo_img/dev_debug_5.gif" />

*更详细的功能请查看FirePHP相关说明。*

自定义输出分析信息
------------------
上面的方法实际是是通过header头信息输出的，所以它不会影响页面使用，必须配合FirePHP才可以使用。
现在介绍通过页面尾部输出信息的方式来实现查看详细信息的方法。
主要通过方法 Core::debug()->profiler() 来实现。

    //判断是否开启
    if ( IS_DEBUG && Core::debug()->profiler()->is_open() )
    {
        //执行一个
        $benchmark = Core::debug()->profiler()->start('Test','abc');
        //一些代码
        sleep(1);
        //统计结束
        Core::debug()->profiler()->stop();

        //再执行一个试试
        $benchmark = Core::debug()->profiler()->start('Test','abc');
        Core::debug()->profiler()->stop();

        //再执行一个def
        $benchmark = Core::debug()->profiler()->start('Test','def');
        sleep(1);
        $data = array(
            'abc' => 1,
            'def' => 2,
            'aaa' => '输出内容',
        );
        Core::debug()->profiler()->stop($data);
    }

执行时在URL上加入?debug=yes 输出截图如下：
<img src="/~docs~demo_img/dev_debug_6.gif" />


其它说明
-------------
通过Core::debug()输出的内容，在未开启调试的情况下，是不会在生产环境上输出的，所以可以放心的发布代码而没有必要一定删除调试代码。
当然，本系统内置了IS_DEBUG常量，您可以在调用Core::debug()时进行判断，比如：

	if (IS_DEBUG)
	{
		//在生产环境中是不会运行的
		
		Core::debug()->info('我的调试内容');
		
		if ( Core::debug()->profiler('sql')->is_open() )
    	{
    		// 在页面中加 ?debug=yes 是无效的，必需?debug=sql（?debug=sql|nocached等也可以）
			Core::debug()->info('SQL调试模式开启时才会被输出');
    	}
	}

而如果不做IS_DEBUG判断，系统在每次执行时会执行Core::debug()->info('我的调试内容');这样的代码，但是不会有任何输出