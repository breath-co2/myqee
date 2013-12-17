# MyQEE 系统相关规范和约定

## 文件目录规范 


* 控制器文件存放于 `controllers` 目录，文件后缀 `.controller.php`，例如 `test.controller.php`
* 类文件放在 `classes` 目录，文件后缀 `.class.php`，例如 `myclass.class.php`
* 模块文件放在 `models` 目录，文件后缀 `.model.php`，例如 `member.model.php`
* ORM文件放在 `orm` 目录，文件后缀 `.orm.php`，例如 `member.orm.php`
* 视图文件放在 `views` 目录，文件后缀 `.view.php`，例如 `test.view.php`
* 语言包放在 `i18n` 目录，文件后缀 `.lang`，例如 `zh-cn.lang`
* 文档放在 `guide` 目录，且用语言文件夹分开，文档后缀为 `.md`，例如 `guide/zh-cn/readme.md` 表示中文的 `readme.md` 文档

## 类的命名规范

`classes`, `models`, `controller`, `orm` 中若对象含有下划线 `_`，则文件存放在相应目录中，文件夹及文件名都是小写，例如：

* 类文件：`MyTest_Lib_abc` 应当存放在 `classes/mytest/lib/abc.class.php` 文件中。
* 模块：`Model_M_Test` 应当存放在 `models/m/test.model.php` 文件中。
* 控制器：`Controller_Abc_Def_Ghi` 当存放在 `controllers/abc/def/ghi.controller.php` 文件中。

核心类库的类前缀为 `Core_`，类库的前缀为 `Library_[a-z0-9]+_[a-z0-9]+_` 的格式，例如 `Library_MyQEE_Admin_`，模块的前缀为 `Module`，驱动的前缀为 `Driver_`。

但是在实际开发中，使用所有的类均无需加任何前缀，你写的代码也无需加前缀调用。比如，核心类库中 `Core_Form` 方法，你在调用的时候直接 `Form::input('test')` 即可，不需要写成 `Core_Form::input('test')`。



## 控制器的命名方式

控制器对象名称必须以 `Controller_` 开头，存放于 `controllers` 目录，扩展到 `Controller` 对象，如果是命令行的控制器，请扩展到 `Controller_Shell` 上。
例如控制器 `Controller_Test` 是存放于 `controllers` 目录的 `test.controller.php` 文件，内容如下：

    <?php
    Controller_Test extends Controller
    {
        public function action_abc()
        {
            echo 'test';
        }
    }

以上可通过 `http://yourhost/test/abc` 访问到 action_abc 方法。

文件夹支持：
例如 `Controller_My_Test` 控制器：

    <?php
    Controller_My_Test extends Controller
    {
        public function action_abc()
        {
            echo 'test';
        }
    }

文件应当存放在 `controllers/my/test.controller.php` 文件里。
以上控制器可通过 `http://yourhost/my/test/abc` 访问到。

另外，控制器可继承另外一个控制器，例如：

    <?php
    Controller_Test extends Controller_Ajax
    {
        public function action_test()
        {
            echo 'test';
        }
    }
    
## 类文件的命名方式

以下划线分割为目录，文件大小写于类名称保持一致，后缀为 `.class.php`，例如：

* 类 `MyTest` 应该存放在 `classes/mytest.class.php` 文件
* 类 `My_Test` 应该存放于 `classes/my/test.class.php` 文件

例如：

    class Mytest 
    {
        public function __construct(){
        
        }
        public function abc(){
        
        }
        public static function test(){
        
        }
    }

存放在 `classes/mytest.class.php` 文件中，调用方法：

    $mytest = new Mytest();
    $mytest->abc();

    // 静态调用
    Mytest::test();




同样支持扩展到另外一个对象，比如：

    class Database extends Core_Database {
    
    }

    class Member extends ORM_Memeber_Data {
    
    }
    
    class Member extends Model_Member {
    
    }

## 模型文件的命名方式

模型的对象名称必须以 `Model_` 开头，存放在 models 目录，文件后缀为 `.model.php` ，推荐扩展到 `Model` 类库上，其它规则同上。
例如：

    class Model_Member extends Model {
        /**
         * 定义此model采用的默认数据库配置名
         */
        public $database = 'default';
        
        public function test() {
    
        }
    }

存放在 `models/member.model.php` 文件中，调用方法：

    $m_member = new Model_Member();
    $data = $m_member->test();

模块也可扩展到ORM上，例如：

    class Model_Member extends ORM_Member_Finder {
        public function test() {
    
        }
    }
