控制器(Controller)
====================
控制器主要处理页面请求参数，并获取模块相关数据并传递给视图。

创建一个控制器
--------------------
控制器必须符合以下要求：

* 前台存放在controllers目录，后台控制器放在controllers/\[admin\]/目录，命令行控制器放在controllers/\[shell\]/目录，系统内部调用控制在controllers/\[system\]/目录
* 控制器后缀.controller.php
* 文件名必须小写
* 下划线_将被存放在对应的文件夹里

> V2.0RC1-RC3用户注意
> 此版本后台控制器后缀.admin.php 命令行控制器后缀.shell.php 
> 后台控制在admin/目录，命令行控制器在shell/目录

简单例子：

    // controllers/foobar.controller.php
    class Controller_Foobar extends Controller {
        public function action_test()
        {
            echo 'abc';
        }
    }
     
    // controllers/admin.controller.php
    class Controller_Admin extends Controller {
        public function action_123()
        {
            echo 'abc';
        }
    }

控制器允许存放在子文件夹里，例如：

    // controllers/test/admin.controller.php
    class Controller_Test_Admin extends Controller {
    
    }
     
    // controllers/product/category.controller.php
    class Controller_Product_Category extends Controller {
    
    }

控制器可扩展到另外一个控制器，例如：

    // controllers/users.controller.php
    class Controller_Users extends Controller_Template {
    
    }
     
    // controllers/api.controller.php
    class Controller_Api extends Controller_REST {
    
    }


$this->action 和 $this->arguments
---------------------
页面传递过来的参数。
例如url是：/test/abc/123/456，控制器如下：

    // controllers/test.controller.php
    class Controller_Test extends Controller {
        public function action_abc($a='',$b='',$c='')
        {
            echo $a;  //123
            echo $b;  //456
            echo $b;  //空字符
            echo $this->action;        //abc
            print_r($this->arguments)  //array(123,456)
        }
    }

$this->before() 和 $this->after()
--------------------
before()和after()函数分别在控制器之前前后执行。例如：

    Controller_Admin extends Controller {
     
        public function before()
        {
        	echo 'begin.';
        }
     
        public function action_login() {
            echo 'run.';
        }
        
        public function after()
        {
        	echo 'ok.';
        }
    }

访问/admin/login首先会执行before()方法，然后执行action_login()方法，然后执行after()方法，上例执行后将输出begin.run.ok.。


$this->header_cache($time)
--------------------
输出一个缓存的头部信息，$time为需要缓存的时间，单位秒，如果要设定强制不缓存，$time=0即可。
>此方法在V2.0正式版后增加

$this->show_message($msg , $code=0 , $data = array())
--------------------
页面输出相关信息，并强制exit()
通常用于一般页面提示，系统内置show_message()方法是支持ajax等判断输出的

* 当页面是通过AJAX请求时，将返回JSON个数数据，为一个数组的json格式array('code'=>$code,'msg'=>$msg,'data'=>$data)
* 当页面是非AJAX请求时，将执行 $this->after() 方法后再 exit() 结束

执行一个页面输出：

	$this->show_message('页面提示内容');

>此方法在V2.0正式版后增加

$this->show_success( $msg , $data = array() )
--------------------
页面输出成功信息，此方法调用$this->show_message()方法，且$code=1

	$this->show_success('页面执行成功');
	// 等同于
	$this->show_message('页面执行成功',1);
	
	// 附带其它数据
	$this->show_success('成功',array(1,3,5));
	// 等同于
	$this->show_message('成功',1,array(1,3,5));
	

$this->show_error( $msg , $code = -1 , $data = array() )
--------------------
页面输出错误信息，此方法调用$this->show_message()方法

	$this->show_error('页面执行失败');
	// 等同于
	$this->show_message('页面执行失败',-1);



__call()方法
--------------------
将执行一个不存在的方法，通常可用于通用抛错页面

action_default()方法
--------------------
默认页面，等同于__call()方法，通常可作为默认页面。
默认页面可以在config.php里设置$config['default_action']。

sub_action 子控制器
--------------------
sub_action与普通的action方法的区别是sub_action只允许内部调用，不可通过URL直接访问，其它一样。

    //admin.controller.php
    Controller_Admin extends Controller
    {
        public function action_login()
        {
            $this->execute('test/abc');
        }
    }
    
    //test.controller.php
    Controller_Test extends Controller
    {
        public function sub_action_abc()
        {
            echo 'test';
        }
    }

上例中，在action_login()方法中执行$this->execute('admin/test');将调用Controller_Test的sub_action_test()方法。这个方法是无法在URL里直接调用出来的。

$this->execute($uri, $print = true, $use_route = true, $is_internal = true)
----------------------
执行一个指定url的控制器的方法。通过此方法，您可以在控制器里自由模拟URL调用其它（或自己）的控制器方法。

* $url 指定url
* $print 是否直接echo出，默认true
* $use_route 是否采用路由规则，默认true，若设置false的话，则跳过路由规则
* $is_internal 是否内部调用，默认true，内部调用的方法是sub_action而不是action

本函数其实是直接调用Request::execute()执行的，它和Request::execute()的区别是Request::execute()的$is_internal默认值是false，而控制器的execute()的$is_internal默认值是true

$this->session()
-----------------------
返回一个Session实例化对象。

$this->redirect( $url, $code = 302 )
-----------------------
URL跳转。

<br>
默认查找控制器及action顺序规则
---------------
本规则不适用于路由方式的控制器

     例1
     URI:/test/abc 筛选控制器优先级排列如下：
     [file]                                  [action]
     ----------------------------------------------------------------------------------------
     test/abc/index.controller.php           default        ->  此时若url结尾没有/且为GET方式则跳转到：/test/abc/
     ↓
     test/abc.controller.php                 index          ->  此时若url结尾没有/且为GET方式则跳转到：/test/abc/
     ↓
     test/index.controller.php               abc
     ↓
     test.controller.php                     abc
     index.controller.php                    test
     ↓
     test/abc.controller.php                 default
     test.controler.php                      default
    
    
     例2
     URL:/test/abc/def 筛选控制器优先级排列如下：
     [file]                                  [action]
     ----------------------------------------------------------------------------------------
     test/abc/def/index.controller.php       default        ->  此时若url结尾没有/且为GET方式则跳转到：/test/abc/def/
     ↓
     test/abc/def.controller.php             index          ->  此时若url结尾没有/且为GET方式则跳转到：/test/abc/def/
     ↓
     test/abc/index.controller.php           def
     ↓
     test/abc.controller.php                 def
     test/index.controller.php               abc
     ↓
     test.controller.php                     abc
     index.controller.php                    test
     ↓
     test/abc/def.controller.php             default
     test/abc.controller.php                 default
     test.controller.php                     default

 只要你建立的控制器符合其中一个就可以读取，若发生有重复的控制器，则以优先级高的为准
 例如例1中，你可以建立 test/abc/index.controller.php 文件，内容为：
 
     <?php
     class Controller_Test__Abc__Index extends Controller
     {
         public function action_default()
         {
             echo 'ok';
         }
     }
 
 也可以建立 test.controller.php 文件，内容为：
 
     <?php
     class Controller_Test extends Controller
     {
         public function action_abc()
         {
             echo 'ok';
         }
     }

<br>

普通控制器，命令行控制器，系统内部控制器和后台控制器的区别
-------------------------
MyQEE控制器分4种类型，普通的控制器，命令行下的控制器，后台控制器和系统内部调用的控制器，普通控制器存放在controllers目录里，命令行，后台，系统内部控制器分别在controllers目录下的\[shell\],\[admin\],\[system\]目录里（注意，是有方括号的），后缀都为.controller.php，类名称命名规则都一样。
即：

*  普通控制器       controllers/ 目录
*  后台控制器：     controllers/\[admin\]/ 目录
*  shell控制器：    controllers/\[shell\]/ 目录
*  系统调用控制器：  controllers/\[system\]/ 目录

区别：

* 系统内部控制只运行系统内部调用运行;
* shell下的控制器只允许在shell模式下运行;
* 后台控制器只允许在admin模式下运行;
* 其它都算是正常的前台控制器，这是为了系统安全才这样做的。

例如：shell下某控制器：

    //controllers/[shell]/test.controller.php
    //可在命令行里运行 “php index.php default test abc”执行，这个控制器是无法在前端URL上获取执行机会的
    //其中default为默认项目
   
    <?php
    Controller_Test extends Controller_Shell
    {
        public function action_abc()
        {
            echo 'hello';
        }
    }
  
例如：后台控制器：

    //controllers/[admin]/test.controller.php
    //只有在项目配置的url_admin后的URL才会被解析到这个控制里
   
    <?php
    Controller_Test extends Controller_Admin
    {
        public function action_abc()
        {
            echo 'hello';
        }
    }

例如当前项目配置是：

    'test' => array
    (
        'name'      => '测试项目',
        'isuse'     => true,
        'dir'       => 'test',
        'url'       => '/abc',
        'url_admin' => '/myadmin',
    ),

只有通过 /abc/myadmin/test/abc 才会执行到上例的后台控制器，通过 /abc/test/abc 是执行不到的。