# 常见问题

## V2 的配置在 V3 中读取不到

这是因为V3对 config 进行了升级，配置不再分文件存放，而是放在同一个 `config.php` 中，比如之前你在项目中有配置 `config/database.config.php`, `config/test/abc.config.php` 等，对应内容如下：

	// config/database.config.php 内容为
	<?php
	$config['default'] = array(
	    // 内容
	);
	
	// config/test/abc.config.php 内容为
	<?php
	$config['a'] = 1;
	$config['b'] = 2;

只需要把这些文件的内容移动到项目目录的 `config.php` 中，并设置相应目录的key即可

    // config.php 内容
    <?php
    // database.config.php
    $config['database']['default'] = array(
	    // 内容
	);
	
    // test/abc.config.php
    $config['test/abc']['a'] = 1;
    $config['test/abc']['b'] = 2;


## 当 V2 RC1-RC3 版本升级到 V2 正式版后页面提示“系统类库丢失，请检查配置文件。”该如何解决？

打开根目录的config.php文件，找到最上面的 `$config['libraries']` 把 `MyQEE/Core` 改成 `com.MyQEE.Core`
若还有类似 `MyQEE/Administration` 写法的类库，也一并改成类似 `com.MyQEE.Administration` 的，规则就是前面加com.然后把/改成.


## 当使用 Nginx 服务器或 Nginx 做代理转发时开启 Debug 后出现502错误的解决

主要是header头部输出了大量的debug信息导致的
可修改
Nginx的设置：

	fastcgi_buffer_size 128k;
	fastcgi_buffers 8 128k;

如果是用Nginx做代理转发的，在代理转发处增加

	proxy_buffer_size  128k;
	proxy_buffers   32 32k;
	proxy_busy_buffers_size 128k;

可参考文章：[http://hi.baidu.com/wastorode/item/ec86ade6ac0af7a2c10d75f4](http://hi.baidu.com/wastorode/item/ec86ade6ac0af7a2c10d75f4)

<br>
<br>
您可以发送邮件到 jonwang@queyang.com 进行反馈，注意标题写明“MyQEE问题反馈”或在QQ群里进行咨询，QQ群为：531219。