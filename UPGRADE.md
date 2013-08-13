V2.0 TO V3
-------

1. 将原来在v2里的projects的所有文件复制到新下载的V3的projects里，并把 statics 目录修改成 assets
2. 将自己在v2里写的自有的library的类库复制到team_lib目录中（team_lib是一个全局的团队私有的公共类库目录）
3. 后台控制器，命令行控制器和系统控制器目录由`controllers/[admin]/`, `controllers/[shell]/`, `controllers/[system]/` 分别移动到了`controllers-admin/`, `controllers-shell/`, `controllers-system/` 目录，请将你自己相应的这些控制器移动过去
4. 检查调整自己的项目和类库的部分代码写法：
   * 请将config.new.php 复制为config.php并对比旧的config.php中配置做相应的修改，具体说明在里面都有
   * 不可以再通过 Core::$config 变量获取config配置（在3.0中此变量为受保护的），必需通过Core::config('…')标准格式来获取
   * `Core::$include_path` 或 `Bootstrap::$include_path` 将返回的是一个2维结构，请改为 Core::include_path() 来获取，将返回一个一维的等同于v2中那样的路径数组
   * 带下划线或有子文件夹的控制器需要修改：
     * 如果有你有带下划线的控制器，系统默认是autoload不到它的，在autoload前必需通过`Core::load_controller()`来加载后才可以。比如test_abc.contoller.php 文件里的控制器类名称为 `Controller_Test_Abc` 这个在v3系统里它推荐的保存路径应该是控制器目录的test目录的abc.controller.php 文件，如果用autoload的功能是无法找到的，那么就需要先`Core::load_controller('test_abc')`来include这个文件
     * 如果原来的控制器在abc/目录下的test.controller.php 类名称为 `Controller_abc__test` ，v2中test前是双下滑线，v3中只需要单下划线，即应该改为：`Controller_abc_test`
   * V3中优化了config的文件，v2的配置过于分散导致不好维护，可将所有配置集中写在类库或项目目录下的config.php中，例如：`project/default/config.php` 文件，写法和v2一样，支持多维
   * 如果您代码有扩展过系统类库的类，Model，控制器等，推荐这样修改：
     例如原来是这样扩展
     
        <?php
        class Database extend MyQEE_Database
        {
          //...
        }
        ?>
     
     请修改成
     
        <?php
        class Database extend EX_Database
        {
          //...
        }
        ?>
     
     EX_Database会自动扩展到 Core_Database（PHP5.3程序里是用的class_alias） 因为EX_开头的类库是用来被扩展的虚拟类库（V3新增），用于解决多级扩展的问题
    * 使用 `Core::protocol()` 的话注意，原来返回的是http或https，在v3中返回的是http://或https://，多了一个://
    * shell目录已被跟换为bin目录，并提供了一些简单的工具，linux系统可直接使用
    * statics 目录全部更换为 assets 目录，在bin目录中有批量重新生成的工具可重新生成合并文件，切支持less，css，js合并压缩
    * 如果使用过`Core::set_project($project)`，则需要改为 Core::change_project($project) 
5. 后台类库进行了全面的升级，如果你原来对后台类库做过扩展，请按下面的说明进行调整：
 * 
 * 

通过以上几步修改，你的项目代码基本上就可以运行在v3里了。





V2.0 RC3 TO V2.0 release
------------

已使用RC版本开发的请按下面方法升级你的代码：
首先备份你的文件和数据库


移除libraries/目录下的MyQEE目录，将正式版的libraries/myqee目录复制进来
将libraries/bootstrap.php替换为正式版的libraries/bootstrap.php文件
config.php 参考 config.new.php 进行修改，特别是 $config['libraries'] 需要修改，正式版和RC版不一样

程序内的改动：
原来的Request重命名为HttpIO，并且将部分参数进行调整，具体为：

    Request::$is_ajax    => HttpIO::IS_AJAX
    Request::$client_ip  => HttpIO::IP
    Request::$method     => HttpIO::METHOD
    Request::$user_agent => HttpIO::USER_AGENT
    Request::$protocol   => HttpIO::PROTOCOL
    Request::$referrer   => HttpIO::REFERRER

请做相应替换
原来的HttpGet已重命名为HttpClient，所以你需要把代码中HttpGet全部替换成HttpClient;
Core::import_library() 参数写法修改了，例如：以前是 `Core::import_library('MyQEE/Test')` 现在是 `Core::import_library('com.myqee.test')`

经过以上几步修改，基本上就可以正常工作了。如果仍旧有错误，请根据错误提示进行相应修改


更详细的修改请看[CHANGELOG.md](changelog.html)文件