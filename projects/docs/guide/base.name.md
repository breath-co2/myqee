文件创建规则及举例
==========================

* 控制器文件存放于controllers目录，文件后缀*.controller.php
* 类文件放在classes目录，文件后缀*.class.php
* 模块文件放在models目录，文件后缀*.model.php
* ORM文件放在orm目录，文件后缀*.orm.php
* 配置文件放在config目录，文件后缀*.config.php
* 视图文件放在views目录，文件后缀*.view.php

classes，models，controller 中若对象含有下划线_，则文件存放在相应目录中，文件夹及文件名都是小写，例如：

* 类文件：MyTest_Lib_abc 应当存放在 classes/mytest/lib/abc.class.php 文件中。
* 模块：Model_M_Test 应当存放在 models/m/test.model.php 文件中。
* 控制器：Controller_Abc_Def_Ghi 当存放在 controllers/abc/def/ghi.controller.php 文件中。

控制器的命名方式
------------
控制器对象名称必须以Controller_开头，存放于controllers目录，扩展到Controller对象。
例如控制器Controller_Test是存放于controllers目录的test.controller.php文件，内容如下：

    <?php
    Controller_Test extends Controller
    {
        public function action_abc()
        {
            echo 'test';
        }
    }

以上可通过 http://yourhost/test/abc 访问到 action_abc 方法。

文件夹支持：
例如 Controller_My_Test 控制器：

    <?php
    Controller_My_Test extends Controller
    {
        public function action_abc()
        {
            echo 'test';
        }
    }

文件应当存放在controllers目录的“my”文件夹的test.controller.php文件里。
以上控制器可通过http://yourhost/my/test/abc 访问到。

另外，控制器可继承另外一个控制器，例如：

    <?php
    Controller_Test extends Controller_Ajax
    {
        public function action_test()
        {
            echo 'test';
        }
    }
    
类文件的命名方式
--------------
以下划线分割为目录，文件大小写于类名称保持一致，后缀为.class.php，例如：

* 类 MyTest 应该存放在 classes 的 mytest.class.php 文件
* 类 My_Test 应该存放于 classes 的 my目录的test.class.php 文件

例如：

    class Mytest {
        public function __construct(){
        
        }
        public function abc(){
        
        }
        public static function test(){
        
        }
    }

存放在classes/mytest.class.php文件中，调用方法：

    $mytest = new Mytest();
    $mytest->abc();

    // 静态调用
    Mytest::test();




同样支持扩展到另外一个对象，比如：

    class Database extends MyQEE_Database {
    
    }

    class Member extends ORM_Memeber_Data {
    
    }
    
    class Member extends Model_Member {
    
    }

模块文件的命名方式
--------------
模块的对象名称必须以Model_开头，存放在models目录，文件后缀为.model.php，推荐扩展到Model上，其它规则同上。
例如：

    class Model_Member extends Model {
        /**
         * 定义此model采用的默认数据库配置名
         */
        public $database = 'default';
        
        public function test() {
    
        }
    }

存放在models/member.model.php文件中，调用方法：

    $m_member = new Model_Member();
    $data = $m_member->test();

模块也可扩展到ORM上，例如：

    class Model_Member extends ORM_Member_Finder {
        public function test() {
    
        }
    }
