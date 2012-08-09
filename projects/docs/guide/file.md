文件类
======
我们会遇到这样的情况：当我们PHP服务器有好几台时，保存在本机的临时文件（比如文件缓存）在更新、修改时会比较麻烦，比如A服务器文件更新了，无法让B服务器也更新。MyQEE文件处理类提供了一个解决方案，可以让多服务器情况下能够方便的实时删除、修改、新建文件和文件夹。
当然，在使用前需要先配置好（如果你只有1台服务器可以忽略这些）
在系统根目录的config.php中你会发现有个 $config['web_server_list']和$config['system_exec_key']和$config['file_sync_mode']的选项，这几个参数的设定会影响文件操作逻辑，下面来了解下这几个参数设定的用途。

根目录config.php中$config['web_server_list']
------
WEB服务的服务器列表，留空则禁用同步功能，比如只有1台web服务器时请禁用此功能

	//可以是内网IP，确保服务器之间可以相互访问到，端口请确保指定到apache/IIS/nginx等端口上
	array(
	    'default' => array
	    (
	        '192.168.1.2:81',      //第一个为主服
	        '192.168.1.1',         //80端口可省略:80
	        '192.168.1.3:81',
	    ),
	)


根目录config.php中$config['file_sync_mode']
------
多服务器文件同步模式，可选参数：rsync|system|none

 * rsync  : 可靠性比较好。但需要在服务器上配置好rsync，将主服data,wwwroot目录同步到其它服上。此种模式下，系统在执行文件操作时，不管轮询到哪台机器上，都将只调用主服务器进行文件操作，然后由rsync来实现文件同步
 * system : 通过本系统内置的同步模式进行同步，无须额外配置。在执行文件操作时，将通过内部系统调用所有服务器进行操作，可靠性不及rsync，但基本上可以满足对临时文件的操作
 * none   : 不做同步处理


根目录config.php中$config['system_exec_key']
------
系统内部请求通讯密钥，至少10位字符，内容不限， 留空时系统将采用config配置中所有core及database的序列化字符串作为key，这样也可确保系统内部通信的安全。

rsync和system模式详解
------
在开始讨论前，我们先假设有5台服务器，IP分别为192.168.1.11-192.168.1.15。

如果是rsync模式，需要运维人员首先挑出1台服务器作为文件同步的主服务器，比如就是192.168.1.11吧。
需要在服务器上把rsync设置好，需要同步的目录包括data和wwwroot目录（当然，如果程序的代码也是通过主服务器同步过去的，那么也可以把其它目录加上）
此时假设我们要保存一个缓存文件，这时候假设轮询到的是192.168.1.12这台机器，当它调用File::create_file()方法时，程序是不会直接写文件到自己机器上的，而是会通过MyQEE系统内部通信方式强制调用192.168.1.11这台主服务器去保存文件，当保存完毕时会返回保存状态。因为是192.168.1.11这台主服务器去保存的文件，所以会通过事先配置好的rsync方式进行同步，如果没有问题，则所有机器都会同步到修改了，这就达到我们的目的了。

如果是system模式，也就意味着这5台服务器都是一样的等级，没有什么所谓的主服务器，这时候任意一台机器执行到
File::create_file()方法时，系统就会依次强制调用这5个服务器执行文件保存操作，让5台服务器实时生成需要的数据，其中有任何一台服务保存失败时将会返回false的状态。


File::$dir
-----
允许操作的目录列表，只有被列出的目录才可以执行操作，否则都将不允许操作。默认为：

	static $dir = array
	(
	   'data'    => DIR_DATA,		//data目录
	   'wwwroot' => DIR_WWWROOT,	//wwwroot目录
	   'log'     => DIR_LOG,		//log目录
	   'temp'    => DIR_TEMP,		//临时目录
	   'assets'  => DIR_ASSETS,		//静态文件目录
	);


File::create_file($file, $data ,$flags = null, $context = null , $storage = 'default')
-----
保存文件，返回是否成功

	// 保存内容
	File::create_file(DIR_DATA.'test','ABC');
	
	// 追加内容
	File::create_file(DIR_DATA.'test','ABC',FILE_APPEND);
	
	// 保存在test这个存储里，当有集群配置时此参数将生效采用不同的服务器列表
	File::create_file(DIR_DATA.'test','ABC',null,'test');

File::create_dir($dir, $auto_create_default_file = true , $storage = 'default')
-----
创建一个目录

	File::create_dir(DIR_DATA.'test_dir');

File::unlink($file, $storage='default')
-----
删除一个文件

	File::unlink(DIR_DATA.'test');

File::remove_dir($dir , $storage = 'default')
-----
循环删除目录下的所有目录和文件

	File::remove_dir(DIR_DATA.'test_dir/');

File::move_dir($fromdir, $todir, $autocoverageold = true , $storage = 'default')
-----
转移目录下的所有目录和文件，可操作非空目录


File::copy_dir($fromdir, $todir, $autocoverageold = true , $storage = 'default')
-----
复制目录下的所有目录和文件到另外一个目录

