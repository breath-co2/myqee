V2.0 RC3 TO V2.0 release
====

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
原来的HttpGet已重命名为HttpClient，所以你需要把代码中HttpGet全部替换成HttpClient
Core::import_library() 参数写法修改了，例如：以前是 Core::import_library('MyQEE/Test') 现在是 Core::import_library('com.myqee.test')

经过以上几步修改，基本上就可以正常工作了。如果仍旧有错误，请根据错误提示进行相应修改


更详细的修改请看CHANGELOG.TXT文件