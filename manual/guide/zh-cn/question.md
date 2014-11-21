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
您可以通过QQ群(531219) 或 [Github Issues](https://github.com/breath-co2/myqee/issues/new) 进行反馈。

## 当使用 Nginx + php-fpm 方式部署时，静态资源文件（js,css,image等）无法输出，显示位NET:ERR_CONTENT_LENGTH_MISMATCH 错误的解决办法

当静态文件通过php输出时，浏览器会报错，文件、图片无法正确显示，使用 curl 请求时会在最后提示类似 `curl: (18) transfer closed with 56097 bytes remaining to read` 这样的错误。

这个问题主要发生在 nginx 调用 php-fpm 的环境里，主要是因为当php直接输出文件后，nginx首先误以为是html文件会对文件进行压缩，而头信息的长度却是压缩前的长度，并且nginx会只输出压缩后长度的，之后长度的内容就不输出了，这就导致浏览器解析失败。

说了这么多，那就看怎么解决吧，在nginx的配置里找到 `gzip on;`  的地方（如果没有则自己创建）
然后下面的 `gzip_types text/plain;` 改成
`gzip_types text/plain application/x-javascript text/css image;` 
即可。当然，如果你的php还有可能输出别的类型的文件，那一样要加类型，否则也会报错

