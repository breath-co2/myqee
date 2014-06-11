# Session 模块

## Session配置

MyQEE核心类库提供的是PHP自带的Session函数，若你打算采用默认配置，就不需要在项目中配置session了，以下为配置内容

配置存放在`config.php` 的 `$config['session']` 里，内容大致如下：

    $config['session'] = array
    ( 
        'name'           => 'sid', 
        'expiration'     => 0, 
        'gc_probability' => 0 ,
        'save_handler'   => '',		//2.0正式版后支持save_handler设置，默认为files
        'save_path'      => '',		//2.0正式版后支持save_path设置，默认为系统临时目录
    );

## Session 采用 Redis 存储

`php.ini` 中默认的 `save_handler` 为 files 表示采用文件存储方式，如果希望session保存在redis里，可以设置 `save_handler = redis`，然后 save_path` 为redis的IP和端口，例如：

    $config['session] = array
    ( 
        'name'           => 'sid', 
        'expiration'     => 0, 
        'gc_probability' => 0 ,
        'save_handler'   => 'redis',
        'save_path'      => 'tcp://127.0.0.1:6379',
    );


## 使用 Memcache 来存储


    $config['session'] = array
    (
        'driver' => 'Cache',                // 所使用的驱动类型为Cache
        'name'   => 'sid',                  // session id的名称
        'driver_config' => 'cache_session', // 使用Cache的 cache_session 配置
    );
    
    //缓存 cache_session 配置
    $config['cache']['cache_session'] = array
    (
        // 缓存配置
        'driver' => 'Memcache',         // 驱动采用Memcache
        'prefix' => 'SESSION_',         // 前缀（加一个前缀可以有效的避免key冲突）
        'driver_config' => 'default',   // 使用 $config['cache/memcache'] 的 default 的配置
    );
    
    // memcache 配置
    $config['cache/memcache']['default'] = array
    (
        array
        (
            'host'   => '192.168.1.101',
            'port'   => 11211,
            'weight' => 20
        ),
        array
        (
            'host'   => '192.168.1.102',
            'port'   => 11211,
            'weight' => 20
        ),
    );



## Session::instance()

获取一个实例化好的session对象，由于session的特殊性，一个请求就只有一个session实例化对象，尽量不要自己new Session();

在控制器中，可以用$this->session()获取到Session对象（这里的$this是指控制器）

当程序里执行过Session::instance()，则可以在下面的代码里直接使用$_SESSION全局变量来操作Session了

    Session::instance();
    print_r($_SESSION);

## id()

返回当前Session的ID

    echo Session::instance()->id();

## destroy()

销毁Session


## member()

获取当前用户对象

    // 得到的 $member 为一个Member对象
    $member = Session::instance()->member();
    echo $member->id;

## set_member(Member $member)

将一个实例化好的Member对象设置给Session

    $model_member = new Model_Member();
    $member = $model_member->get_by_id(1);      //根据ID获取一个用户对象
    Session::instance()->set_member($member);
    
    // 也可这样    
    $member = new Member();
    $member->id = 1;
    Session::instance()->set_member($member);

## Session::write_close()

保存session数据，这个方法通常情况下不需要调用，系统会在请求结束的时候自动执行这个方法


## set($keys, $val = FALSE)

设置一个 Session 数据，也可以直接 `$_SESSION['key'] = 'value';` 设置

    Session::instance()->set('test',123);
    
    //下面的等效
    $_SESSION['test'] = 123;    // 注意，请确保之前执行过Session::instance()

## set_flash($keys, $val = FALSE)

设置一个闪存数据，和set的区别是，这样设置后，在下次请求后，数据会自动销毁，比较适合做临时数据，支持批量设置

## keep_flash($keys)

延长set_flash()的数据寿命，在请求后不销毁，继续留到下次请求时才销毁

    // a控制器
    Session::instance()->set_flash('test',1);
    
    
    // b控制器
    $status = ....
    if ( $status===false )
    {
        Session::instance()->keep_flash('test');
    }

## expire_flash()

清除所有闪存Session数据

## get($key = FALSE, $default = FALSE)

获取指定$key的数据，若获取失败，则使用$default数据

    Session::instance()->get('test');
    //等同于
    $_SESSION['test'];  //请确保这样取之前执行过Session::instance()
    
    
    //若$_SESSION中有test，则返回此数据，否则，返回0
    $test = Session::instance()->get('test',0);

## get_once($key, $default = FALSE)

获取数据后将此数据删除，相当于执行了get()后再执行delete()方法


## delete($key1,$key2,...)

删除指定的$key的数据，支持删除多个

    //删单个
    Session::instance()->delete('test');
    //相当于
    unset($_SESSION['test']);
    
    //删多个
    Session::instance()->delete('test','test2','test3');
    //相当于
    unset($_SESSION['test'],$_SESSION['test2'],$_SESSION['test3']);
    
## Session::session_name()

获取Session ID的名称