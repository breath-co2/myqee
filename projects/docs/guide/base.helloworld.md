Hello World
==========================
添加第一个控制器
---------------------

在projects/default/controllers/目录里添加 hello.controller.php 文件，内容如下：

    <?php
    /**
     * Hello控制器
     */
    class Controller_Hello extends Controller
    {
    	public function action_test()
    	{
    		echo 'hello world';
    	}
    	
    	public function action_default()
    	{
    		echo '如果输入不存在的方法，则会显示default方法';
    	}
    }

打开浏览器输入 http://localhost/hello/test 和 http://localhost/hello/test2 看看。

控制器入门
---------------------
通过以上简单例子，你会发现建立新的控制器如此简单。
例如希望创建一个URL是 http://localhost/test/abc/123 该如何创建控制器？
很简单，只需要在项目的controllers目录里添加 test.controller.php 文件，内容为：

    <?php
    class Controller_Test extends Controller
    {
    	public function action_abc($id=0)
    	{
    		echo 'ID='.$id;
    	}
    }

这样，在浏览器里看到的结果就是

    ID=123

更多的控制器介绍请查看 [创建控制器](mvc.controller.html)
