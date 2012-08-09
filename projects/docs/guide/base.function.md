常用类、方法
============

Core::config() 获取指定配置文件
------------
关于网站config的配置方法，请点击查看详细

    //将返回根目录config.php文件的$config['charset']
    Core::config('core.charset');	//utf-8
    
    //将返回config/database.config.php文件中全部配置
    Core::config('database');
    
    //将返回config/database.config.php文件中$config['default']
    Core::config('database.default');
    
    //将返回config/database.config.php文件中$config['default']['type']
    Core::config('database.default.type');


Core::url() 返回Core_Url对象，主要用于创建URL
------------

    echo Core::url('test/123');


Core::show_404() 和 Core::show_500()
------------------
分别用于输出404页面和500错误页面

HttpIO::GET() ， HttpIO::POST() ， HttpIO::REQUEST()
-----------------
获取页面原始$_GET,$_POST,$_REQUEST数据
HttpIO::GET()和$_GET的区别是，$_GET已经过XSS安全处理，而HttpIO::GET()是页面原始接受的数据。
转义前和转义后区别的字符主要包括：**&amp;，&lt;，&gt;，&quot;（英文双引号），&#039;（英文单引号）** 这几个，详情见手册htmlspecialchars()说明。
另外，\r\n，\r 将都被全部替换为\

    //test?key=1+4>3&id=abc
    echo HttpIO::GET('key');	//1+4&gt;3
    echo $_GET['key]			//1+4&amp;gt;3
    
    echo HttpIO::GET('id');	//abc
    echo $_GET['id']			//abc
    
    // 待获取一个含有&amp;的URL
    //search?q=http%3A%2F%2Fwww.myqee.com%2F%3Fid%3D123%26m%3Dblog
    echo HttpIO::GET('q',HttpIO::PARAM_TYPE_URL);	//http://www.myqee.com/?id=123&amp;m=blog
    echo $_GET['q'] 								//http://www.myqee.com/?id=123&amp;amp;m=blog

>在V2.0 RC1-3之间，HttpIO为Request


Controller 控制器的方法
-------------------
根据URL直接处理的对象，详细请看[控制器章节](mvc.controller.html)
    
    //返回一个Session实例化对象
    $this->session();
    
    // 页面跳转到指定URL
    $this->redirect('http://www.google.com/');
    
    // 执行一个URL
    $this->execute('test/abc/123');
    /*
      若存在Controller_Test且存在sub_action_abc()则会执行这个控制器方法。
    */



View 视图类
-------------------
通常在控制器里用的最多，详细请看[视图章节](mvc.view.html)。

    //构造一个新对象
    $view = new View('global/header');
    //设置变量，也可写成$view->set('title','test');
    $view->title = 'test';
    $view->set_global('global_var','所有视图都能用到的变量');
    //输出
    $view->render();
    
    //以上代码也可写成
    View::factory('global/header')
    	->set('title','test')
    	->set_global('global_var','所有视图都能用到的变量')
    	->render();


Database 数据库对象
---------------------

    //将返回'select `id`,`name`,`title` from `memeber` where `id`=1 limit 1'的查询结果的第一个数据
    $data = Database::instance()
    	->select('id','name','title')
    	->where('id',1)
    	->limit(1)
    	->form('member')
    	->get()			//执行查询
    	->current();	//返回第一行数据，若执行->as_array()则返回全部数组数据
    print_r($data);


Form() 表单对象
--------------------

    /*
     <input type="text" name="text" value="123" />
    */
    echo Form::input('test',123);
    
    /*
     <input type="text" name="test" value="123" onclick="alert('abc')" />
    */
    echo Form::input('test',123,array('onclick'=>'alert("abc")'));
    
    /*
    <select name="test">
    <option value="1">a</option>
    <option value="2" selected="selected">b</option>
    </select>
    */
    echo Form::select('test',array('1'=>'a','2'=>'b'),'2');


