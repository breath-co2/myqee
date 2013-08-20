# 控制器

## 控制器是如何运行的
当页面请求某个URL时，MyQEE首先会根据URL中路径信息去寻找控制器，然后再调用相应的action方法，并把所有的参数传给它，通过控制器的执行，完成一个完整的请求。

在MyQEE中，控制器的后缀均为 `.controller.php` 例如 `hello.controller.php`，并且控制器的文件名都是小写。控制器文件中需要定义一个同名的 `Controller_` 开头的类并扩展到 `Controller` 上，比如

    <?php
    // hello.controller.php
    Controller_Hello extend Controller
    {
        public function action_test()
        {
            // code
        }
        
        public function action_test2()
        {
            // code
        }
    }

这个文件保存在控制器目录中即可通过地址 `/hello/test/` 访问 `action_test()` 方法，而 `/hello/test2/` 则会调用 `action_test2()` 执行。

注意，所有的Action均是以 `action_` 开头的，并且都是public属性。

!!! 如果控制器是在某个目录下，要注意控制器的类名也要包含目录名的，比如 `test/abc.controller.php` 控制器，它的类应该是这样的 `Controller_Test_Abc extend Controller {} `

PS:控制器类名不区分大小写，不过推荐使用首字母和下划线后的首字母都大写 

所以，通过不同的URL可以直接执行对应的控制器的action方法，这样我们就可以写出优雅的控制器了。


## 控制器和URL访问的对应关系

默认情况下访问的URL中格式为 `<directory>/<controller>/<action>/<arguments1>/<arguments2>/...`

例如请求的URL为：`/test/abc/def/123/456/`，则有可能请求的就是 `test/` 目录下的 `abc.controller.php` 的 `action_def($arg1 = '', $arg2 = '')` 方法，其中，`$arg1=123`, `$arg2=456`


## 控制器优先级

当然，系统并没有规定 `test` 就一定是目录，你也可以说它是根目录下的 `test.controller.php` 控制器的 `action_abc()` 方法，后面的都是参数。是的，这样完全正确，此时就涉及到了控制器的优先级问题。

对于一个请求的URL，目录越深的控制器越优先，怎么来理解这句话呢？下面举个例子：<br>
例如有2个控制器，分别是 `test/abc/def.controller.php` 和 `test.controller.php` ，请求的URL仍旧是上例中 `/test/abc/def/123/456/`，系统会按这样的顺序去找控制器：

1. /test/abc/def/123/456.controller.php
2. /test/abc/def/123.controller.php
3. /test/abc/def.controller.php
4. /test/abc.controller.php
5. /test.controller.php

这样，系统就会首先发现存在 `test/abc/def.controller.php` 这个控制器，那么结束查找直接加载这个控制器，然后把路径后面的当相应的action 和 arguments 来处理

这样做就会产生一个非常有意思的结果：<br>
`/test/abc/def/123/456/` 执行的是 `test/abc/def.controller.php` 控制器的 `action_123()` 方法<br>
`/test/abc/defghi/123/456/` 执行的是 `test.controller.php` 的 `action_abc()` 方法

虽然这2个URL看上去似乎都在 `/test/abc/` 这个目录中，但是他们实际上执行了2个完全不同的控制器。

这样还不够，那么再来一个有意思的做法吧，同样是上面的这些，如果再创建 `/test/abc/defghi.controller.php` 这个控制器呢？显然，此时就会执行到这个控制器。所以，同样的一个URL地址你可以有更多的控制器的创建方法，完全看项目需求，更多的想象空间。


## 特殊控制器

到这里，你是否觉得控制器是一个非常灵活而有趣的东西？那么我再介绍2个特殊的控制器 `index.controller.php` 和 `default.controller.php` 这2个控制器分别是首页控制器和默认控制。下面分别介绍：

### `index.controller.php` 控制器

它是一个目录的控制器，最简单的例子，你网站的首页路径就是用的这个控制器。例如 `index.controller.php` 存放在控制器根目录，访问 `http://your_domain/` 就可以方法到它了。

如果是某个目录下，就放在一个目录下，比如 `test/index.controller.php` 访问 `/test/` 这样的URL就可以执行它，而访问 `/test/abc/` 就执行不到它的。

注意，通常情况下，`index.controller.php` 里只能用到 `action_index()` 或 `action_default()` 这其中的一个action，例如 `/test/`这样的URL就可以执行到这个控制器：

    <?php
    // test/index.controller.php
    class Controller_Test_Index extend Controller 
    {
        public function action_index()
        {
            echo 'it is index controller.';
        }
    }

    
### `default.controller.php` 控制器

它和 `index.controller.php` 的区别在于，`index.controller.php` 只接受最后一级目录的首页控制器查找，而 `default.controller.php` 则可以作为“不存在”的控制器的替代控制器，即此目录下的“万能”控制器。

例如，`test` 目录下有2个控制器，分别是 `default.controller.php` 和 `abc.controller.php`，那么当访问 `/test/abc/123/` 这样的URL时就会使用 `abc.controller.php` 控制器执行，而如果是一个类似 `/test/aaaa/123/` 这样的URL请求呢？此时，就会调用 `default.controller.php` 控制器执行 `action_aaaa()` 方法了。

这是一个美妙的组合。由于有了 `default.controller.php` 和 `index.controller.php` 控制器的参与，在前面 “控制器优先级” 中举的优先级例子就发生了一个有趣的变化，这样 `/test/abc/def/123/456/` 完整的控制器查找顺序就是这样的：

1. /test/abc/def/123/456/**index.controller.php**
2. /test/abc/def/123/456/**default.controller.php**
3. /test/abc/def/123/456.controller.php
4. /test/abc/def/123/**default.controller.php**
5. /test/abc/def/123.controller.php
6. /test/abc/def/**default.controller.php**
7. /test/abc/def.controller.php
8. /test/abc/**/default.controller.php**
9. /test/abc.controller.php
10. /test/**default.controller.php**
11. /test.controller.php
12. /**default.controller.php**

从这个完整的顺序来看，最高优先级的控制器为 `/test/abc/def/123/456/` 目录下的 `index.controller.php` 控制器，然后是这个目录下的 `default.controller.php` 控制器，然后在每一级目录下，`default.controller.php` 则作为当前目录的“后备”控制器来寻找。

!!! 看到这，你或许会担心MyQEE寻找控制器的性能，需要查找这么多的控制器。其实你完全不用担心，上面列出来的只是理论上需要查找的控制器，实际上MyQEE在查找控制器前，首先会找出存在的目录，如果没有相应的目录则会直接忽略到这些控制器的查找了。比如在你的控制器里，也许只有 `test` 这个目录，在 `test` 目录下没有 `abc` 这个目录，那么上例中1-8就会直接跳过的，直接从9开始查找控制器，实际上最多也就查找4次而已，况且找到了对应控制器就立即终止查找了，所以你完全没有必要担心MyQEE在寻找控制器上的性能问题。


## 控制器的Action

控制器的action是以 `action_` 开头的方法，比如 `action_test()`, `action_entry($id=0)`。当系统根据URL找到一个控制器后，会去实例化对象，并把相应的参数传给控制器，然后会执行对应的action方法。

### action的参数

通过前面控制器的加载或许你已经熟悉了action的使用方法，在实际使用中URL除了能体现控制器和action还会有一些参数，那么这些参数是怎么传给action呢？其实很简单，就是action方法对应的参数，例如控制器`test.controller.php`：

    <?php
    // test.controller.php
    class Controller_Test extend Controller
    {
        public function action_abc($arg1 = '', $arg2 = '')
        {
            echo '$arg1 = ' , $arg1;
            echo "\n";
            echo '$arg2 = ' , $arg2;
        }
    }

如果请求的URL是 `/test/abc/123/456/789`，此时控制器将输出

    $arg1 = 123
    $arg2 = 456
    
对于地址栏中 `789` 这个值，由于action中只有2个参数，第三个和后面的就不会读取到，需要的话则可通过 `$this->arguments` 来获取。

如果访问的URL是 `/test/abc/def/`，此时 `$arg1` 的值就是 `def`，`$arg2` 的值就位空

是的，在url中action后面的用斜杠分开的所有参数都将会通过action的参数直接传给程序使用。

### action_default() - 默认控制器

有时候我们希望能够实现更完美的URL地址，比如 `/test/1/`, `/test/2/`, `/test/3/` 这样的URL，我们希望能够在 `test.controller.php` 得到执行，可是显然控制器后面是一个随机的内容，我们不可能为分别建立 `action_1()`, `action_2()`, `action_2()` 吧？那么此时 `action_default()` 就是最好的选择。

    <?php
    // test.controller.php
    class Controller_Test extend Controller
    {
        public function action_default($id = '')
        {
            echo $id;
        }
        
        public function action_abc()
        {
            echo 'test';
        }
    }
    
这样就可以通过 `action_default` 得到需要 `$id` 了。访问 `/test/1/`, `/test/999/` 后分别会输出1和999了，而访问 `/test/abc/` 则会输出 `test`


### before(), after() - 前、后置方法

有时候，我们希望系统能够在执行某个action前先执行一个方法，比如我们的后台或会员系统，我们希望能够在执行确定的action前判断下该用户是否登录或是否有权限，那么不需要一个个的action里写，只要把相关的代码写在before里即可，例如

    <?php
    // test.controller.php
    class Controller_Test extend Controller
    {
        public function before()
        {
            echo 'a-';
        }

        public function after()
        {
            echo '-c';
        }
        
        public function action_abc()
        {
            echo 'b';
        }
    }

如果请求URL `/test/abc/` 则最终会输出 `a-b-c`。

从这里你可以看出`before()` 类似 `__construct()` , `after()` 类似 `__destruct()`， 那么你或许会疑问为何不用这2个系统默认的。实际上， `__construct()` 和 `__destruct()` 并不是不能用，而是它们执行的时机不合适，因为执行 `__construct()` 时控制器还没有完全实例化完毕，包括各种参数还没有给控制器设置，所以我们推荐使用MyQEE提供的这2个对应的方法来执行，这样能够达到更好的效果。



### __call($action_name, $arguments) 方法

这个是PHP类库中特殊的方法，用来执行一个不存在的方法，MyQEE同样执行，你可以利用它来处理一些类似404错误输出相关功能，例如一个控制器中，有固定的几个action，如果用户请求了一个不存在的action方法，此时如果设置了 `__call()`，系统就会直接调用，如果没有这个方法，系统则会直接调 `Core::show_404()` 输出一个页面不存在的404页面。


## 控制器的扩展

控制器除了可以扩展到`Controller` 这个基类外，还可以扩展到另外一个控制器，比如 `Controller_Test_Abc extend Controller_Test_Core`

## 后台控制器、系统控制器、命令行控制器

除了正常的控制器外，MyQEE还提供了3种控制器类型对于3种运行模式。每个请求只会存在一种模式，并且在系统执行时就会判断好，这样分开可以起到相互分离、方便开发维护、提升安全的作用，**这也是MyQEE框架特有的**。这几种模式的控制器除了目录不一样，其它所有的规则都是一样。

普通的控制器都是在 `controllers` 目录下的，这3类控制器并不在这个目录下，他们分别在：

* 后台控制器 - `controllers-admin` 目录
* 系统控制器 - `controllers-system` 目录
* 命令行控制器 - `controllers-shell` 目录

***后台控制器***：当一个URL请求时，MyQEE首先会根据URL去判断是属于哪个项目，然后会再判断是否符合项目设置中 `url_admin` 的URL规则，如果符合，此时系统进入后台模式，所有加载的控制器都会在 `controllers-admin` 目录中寻找，而不会在 `controllers` 里寻找，所以不用担心自己的后台控制器被前端访问到。

例如根目录 `config.php` 中项目配置如下:

    $config['projects'] = array
    (
        'default' => array
        (
            'name'      => '默认项目',
            'dir'       => 'default',
            'isuse'     => true,
            'url'       => '/',
            'url_admin' => '/admin/',
        ),
    );

当请求 `/test/abc/def/` 这样的URL时，会被认为是普通控制器，只会读到 `controllers` 目录中的控制器，而如果是 `/admin/test/abc/def/` 则会被认为是后台控制器，因为它符合 `url_admin` 中设置的URL前缀，那么它会在 `controllers-admin` 目录中寻找控制器。


***命令行控制***：当你在命令行里使用类似 `php index.php default test abc` 这样的命令执行时，系统会直接进入命令行模式，其中，`default` 表示项目，后面的参数和URL中相似，只不过通常，URL中是以/分开，而命令行里习惯用空格分开。

***系统控制器***：这个主要发生在存在2个或以上的服务器时使用MyQEE系统的同步类库 `HttpCall` 处理服务器同步时会进入的模式。如果你设置了文件同步模式为服务器同步，则在使用 `File` 类进行操作文件时，也会内部调用，具体可参看“系统内部调用”。




## 进阶

可否存在横线或特殊字符的控制器？答案是肯定的，比如： `my-test.controller.php` 注意到这个控制器是用-隔开的，那么控制器应该这样写：

    <?php
    // my-test.controller.php
    class Controller_My_Test extend Controller 
    {
        public function hi()
        {
            echo 'hello';
        }
    }

请特别注意，是 `Controller_My_Test` 不是 `Controller_My-Test`，因为PHP的类命名只允许字母数字和下划线，所以，所有特殊字符全部转换成下划线命名即可，例如 `my-+:test.controller.php` 控制器的类应该命名成 `Controller_My___Test`

当然，这样做势必会带来一些问题，需要你特别注意，例如使用auto_load将会出现问题，如果你在一个控制器里扩展了这样特殊的类，你必须先include这个文件，再去使用。可以通过 `Core::load_controller('my-+:test');`，比如：

    <?php
    // test.controller.php
    
    // 先加载控制器
    Core::load_controller('my-+:test');
    
    class Controller_Test extend Controller_My___Test 
    {
        public function hi()
        {
            echo 'hello';
        }
    }

这样做就可以先include对应控制器文件，然后 `Controller_Test` 即可成功扩展到了 `Controller_My___Test` 上了。

另外一个问题也是显而易见的，就是可能会存在控制器名称冲突，例如有2个控制器 `my_test.controller.php` 和 `my-test.controller.php` 他们的控制器名按规则都应该是 `Controller_My_Test`,  在URL访问时，`/my_test/` 加载的是前者，`/my-test/` 加载的是后者，一般情况下都不会有问题，但是你不能同时载入2个文件，否则程序会报错，这个是无法解决的，你只能自行避免。

!! V2不支持这种特殊字符的控制器