Cache 类
==============
缓存操作类，支持Memcache、File、Apc、Database、SQLite和WinCache驱动。
在cache的配置里可以指定具体采用哪个驱动，然后可以通过Cache::instance($config)来获取相应的配置的实例化对象。

Cache::instance($config='default')
-----------
静态方法，获取一个已实例化好的Cache对象

$config不传则为'default'，可以传数组。

get($key)
-----------
获取指定key的缓存数据

    $data = Cache::instance()->get('test');
    var_dump($data);

set( string/array $key [, fixed $value = NULL , int/string $expire = integer 3600 , string $expire_type = NULL ] )
------------
设置指定key的缓存数据

$expire_type默认有4种类型，分别为：

* $expire_type = Cacah::TYPE_MAX_AGE 最长时间，当指定的$expire达到时间后，缓存失效，默认方式
* $expire_type = Cacah::TYPE_MAX_HIT 最大命中数，当get()请求数量达到$expire值后，缓存失效
* $expire_type = Cacah::TYPE_ADV_AGE 高级时效类型，此类型时，传入的$expire可以类似：200~250,1/100，其中200~250表示介于这个时间（单位秒）内时，在1/100请求几率下会失效，其它99/100请求不会失效，并且250为临界时间，超过这个时间将等同TYPE_MAX_AGE方式处理。它的主要用途是在高并发的情况下，避免因缓存失效而集中需要更新导致重复加载。
* $expire_type = Cacah::TYPE_ADV_HIT 高级命中类型，此类型基本同上，只是$expire前的数值表示为请求数

简单例子：

    //简单例子
    Cache::instance()->set('key',123);
    
    
    //表示介于200～250秒之间时命中率为1/100，若命中则更新缓存
    //这样做的好处是，如果是一个高并发的请求，可以有效避免集中在某一个时间失效需要重新构造数据
    Cache::instance()->set('key','value','200~250,1/100',Cache::TYPE_RENEW_AGE); 
    
    
    //批量设置缓存
    $all_cache = array(
        'key1' => '123',
        'key2' => array(1,3,5),
        'key3' => 'test',
    );
    Cache::instance()->set($all_cache);
    
    echo Cache::instance()->get('key3);    //test

clean()
-----------
删除全部缓存，delete_all()的别名

decrement( string $key [, int $offset = integer 1 , int $lifetime = integer 60 ] )
------------
递减，$key为缓存的唯一标识，$offset为增量，默认为1，当指定的key的内容不存在（比如失效了）时，会认为值为0，并且设置有效期为$lifetime的值

    $cache = Cache::instance();
    echo $cache->get('abc');        // 假设为2
    $cache->decrement('abc');
    echo $cache->get('abc');        // 则输出为1

increment( string $key [, int $offset = integer 1 , int $lifetime = integer 60 ] ) 
-------------
递增，用法同递减


delete( string $key ) 
------------
删除指定key的缓存数据

    $status = Cache::instance()->delete('abc');
    if ($status)
    {
        echo '删除成功';
    }
    else
    {
        echo '删除失败';
    }

delete_all()
------------
删除所有的缓存数据（包括未过期的数据）

delete_expired()
------------
删除已过期的数据


