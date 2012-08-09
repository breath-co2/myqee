常见问题
==============

当V2 RC1-RC3版本升级到V2正式版后页面提示“系统类库丢失，请检查配置文件。”该如何解决？
-----------
打开根目录的config.php文件，找到最上面的 $config['libraries'] 把 MyQEE/Core 改成 com.MyQEE.Core
若还有类似 MyQEE/Administration 写法的类库，也一并改成类似 com.MyQEE.Administration 的，规则就是前面加com.然后把/改成.


当使用Nginx服务器或Nginx做代理转发时开启Debug后出现502错误的解决
----------
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
您可以发送邮件到 jonwang@myqee.com 进行反馈，注意标题写明“MyQEE问题反馈”或在QQ群里进行咨询，QQ群为：531219。