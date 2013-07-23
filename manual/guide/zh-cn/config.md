程序配置说明
------

根目录下的 `config.php` 配置内容为主要配置，每个类库和项目目录下都可以创建 `config.php` 配置进行扩充。

!!! V2的配置除根目录的 `config.php`外，其它的扩展配置都存放在config目录里对应的文件里，比如 `projects/default/config/database.config.php` 里，在V3里不需要分文件单独存，而是全部放在 `config.php` 中，这样便于管理

## 配置优先级顺序

假设根目录`config.php`(下称：全局配置)有一个配置 `$config['test'] = 1;` 则在程序中使用 `Core::config('test')` 则会返回1

如果在某类库配置中也有一个相同的配置，则会覆盖此配置，比如在 `core/config.php` (核心类库)中设置 `$config['test'] = 2;` 则 `Core::config('test')` 会返回2

最高优先级的当然是项目中的配置，比如默认项目配置(`projects/default/config.php`)中设置 `$config['test'] = 3;` 则 `Core::config('test')` 会返回3

**所以总结下**<br />
全局配置是最基本的配置，它会被类库和项目配置进行覆盖，形成最终的配置，配置的优先级和include_path相同，通常是项目具有最高优先级，类库其次，核心类库再其次，全局配置为基础。

###那么，如果一个全局配置被扩展了，程序还希望获取相应的配置怎么办？
可以执行 `Core::config('core.test')` 这样不管核心类库中的test被“覆盖”成什么值，它仍旧返回原始的值，即1。


## 可设置配置
程序中的config是不能变的，那么如果我们希望设置一些动态配置要怎么做呢？可以使用 `Core::config()->get('test');` 这样的代码，这表示获取一个数据库的key为test的配置。

数据库DDL如下：
	
	CREATE TABLE IF NOT EXISTS `config` (
	  `type` varchar(32) NOT NULL COMMENT '分类类型',
	  `key_md5` varchar(32) NOT NULL COMMENT 'key md5',
	  `key_name` varchar(128) NOT NULL COMMENT '关键字',
	  `value` blob NOT NULL COMMENT '值',
	  PRIMARY KEY (`type`,`key_md5`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='配置表';

`Core::config()` 将返回一个Config的对象，这个对象的具体的操作课参考[Config类API](core/class.config/api.html)


## 常用配置参考

### 数据库配置

	$config['database']['default'] = array
	(
	    'type' => 'MySQL',
	    'connection' => array
	    (
	        'hostname'   => '127.0.0.1',
	        'database'   => 'myqee',
	        'username'   => 'root',
	        'password'   => '123456',
	        'persistent' => false,
	    ),
	    'table_prefix' => '',
	    'charset'      => 'utf8',
	    'caching'      => false,
	    'profiling'    => true,
	);

其中：

* type - 数据库类型，目前支持 mysql, mysqli, mongo, sqlite, postgre 
* persistent - 是否长连接
* table_prefix - 表前缀，比如 `mq_`
* charset - 表内容编码
* caching - 是否自动对查询数据进行缓存
* profiling - 是否开启debug时分析数据

**特别说明**

MyQEE是支持自动主从数据库的，只需要对hostname部分设置下即可，例如：

	// 表示192.168.1.1为主数据库，其它为从数据库
	'hostname' => array
	(
		'master' => '192.168.1.1',
		'slaver' => array
		(
			'192.168.1.2',
			'192.168.1.3',
			'192.168.1.4',
		),
	),

详细请查看数据库相关文档
