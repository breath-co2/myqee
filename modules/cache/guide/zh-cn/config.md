# 缓存配置


MyQEE目前已支持的驱动包括：

类型                      | Driver 参数
--------------------------|-------------------------
文件缓存                   | `file`
Memcache（Memcached）缓存  | `memcache`
Apc                       | `apc`
数据库                     | `database`
SQLite数据库               | `sqlite`
Redis                     | `redis`
WinCache                  | `wincache`


## 配置举例


    <?php
    // 缓存配置 $config['cache'] ，存放在项目或团队类库 config.php 中即可
    
    // 默认配置
    $config['cache']['default'] = array
    (
        'driver' => 'memcache',              //驱动类型为Memcache
        'driver_config' => 'default',        //驱动配置 将读取config/cache/memcache.config.php中$config['default']的配置
    );
    
    // 另一种配置方法
    $config['cache']['m'] = array
    (
        'driver' => 'memcache',         //驱动类型为Memcache
        'driver_config' => array
        (
            // memcache 配置
             array
             (
                'host' => '127.0.0.1',
                'port' => 11211,
                'weight' => 20
            ),
             array
             (
                'host' => '127.0.0.2',
                'port' => 11211,
                'weight' => 20
            ),
        ),
    );

    $config['cache']['file'] = array
    (
        'driver' => 'file',         //驱动类型为文件
    );
    
如上配置好后可使用类似下面的代码进行调用相应的配置
    
    //返回默认配置的Cache对象
    $cache = Cache::instance('default');
    //或    
    $cache = Cache::instance();  //若采用默认配置（default）则可不传参数
    
    // 将调用上面 $config['file'] 的配置的实例化对象
    $cache = Cache::instance('file');
    
 


## Memcache 配置举例

Memcache配置 `$config['cache/memcache']`

    <?php
    
    // Memcache默认配置
    $config['cache/memcache']['default'] = array
    (
        array
        (        
            'host'   => '127.0.0.1',
            'port'   => 11211,
            'weight' => 20
        ),
         array
         (
            'host'   => '127.0.0.2',
            'port'   => 11211,
            'weight' => 20
        ),
    );
    
    // Memcache另外一个配置
    $config['cache/memcache']['test'] = array
    (
        array
        (
            'host'   => '127.0.0.1',
            'port'   => 11211,
            'weight' => 20
        ),
    );

在MyQEE系统里，memcache类库只是cache类库的一个驱动，所以在写程序时不会直接接触到，而是通过Cache类来操作memcache。
例如上例中，我已经配置好了 `$config['cache/memcache']['test']` 的参数了该如何调用到呢？可在 `config.php` 里加入一个缓存配置，例如：

	// 默认配置
    $config['cache']['mytest'] = array
    (
        'driver' => 'memcache',             //驱动类型为Memcache
        'driver_config' => 'test',			 //指定使用上例中 $config['cache/memcache']['test'] 配置
    );

然后代码里可以直接用

	Cache::instance('mytest')->get('key1');	//获取一个key1的数据

这样的话，如果我们需要修改驱动类型（比如把文件缓存缓存Apc或Memcache来缓存）不需要修改任何程序代码，只需要将对应的Cache配置修改下即可，大大提高了程序的灵活性。

文件缓存配置
-------------
暂无须配置
