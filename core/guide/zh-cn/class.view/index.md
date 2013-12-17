# 视图

视图主要处理控制器传递来的变量，并输出HTML内容。

## 命名规则

支持文件夹，文件名支持下划线且全小写，后缀为 `.view.php`
例如：`views/test/abc.view.php` 调用方法：

    $view = new View('test/abc');

## 应用举例

    //controllers/test.controller.php
    class Test_Controller extends Controller
    {
        public function action_hello ()
        {
    		$view = new View('test/abc');
    		$view->set('abc',123);				//通过set方式设置
    		$view->test = 'test string';		//通过给对象设置变量设置，效果同上
    		$view->set_global('num',1);			//设置全局视图变量
    		
    		$view2 = new View('view2');
    		
    		$view->v2 = $view2->render(false);	//将$view2输出的HTML设置给$view的$v2变量
    		$view->render(true);				//输出HTML
    	}
    }
    
    //views/view2.view.php
    <?php
    echo $num;		//1
    $num++;
    echo $num;		//2
    >
    
    //views/test/abc.view.php
    <?php
    echo $abc;		//123
    echo $test;		//test string
    echo $num;		//2
    ?>


## 视图加载视图

有2种方式加载
<br>
方法一：

    include Core::find_file('views','test');

方法二：

    View::factory('test')->render(true);

这2种方式的区别是：
<br>
方法一采用的是系统的include方法，所以它加载的视图文件会沿用本视图的所有变量，而方法二视图内的变量是相互独立的（只有全局视图变量才会通用）。