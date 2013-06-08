MyQEE PHP Framework
====
MyQEE是一个开源的PHP框架，支持HMVC模式，建立在PHP5.2基础之上，支持多项目管理开发，数据库内置自动主从分离功能，MySQL支持事务操作功能，并且支持自动嵌套功能，多驱动设计灵活适应各种环境而基本不需要改动什么代码。

MyQEE PHP框架的的特色：

* 包括Database, Cache, HttpClient, Session, Storage, Upload等支持多驱动，可以适应不同环境的需求；
* 运行速度快，HelloWorld只需要毫秒级，相比现在主流的PHP框架有很大的优势；
* 特别适合团队开发，系统提供了调试类库可；
* HMVC,多项目支持，开发更灵活；
* 特别开发的ORM适合复杂的程序逻辑，让程序维护变得轻松不杂乱更不会变为失控状态；
* 独有的控制器分离机制，普通控制器，后台控制器，命令行控制器相互独立，提升系统安全；


文档
====
本地文件，用浏览器直接打开 manual/index.html 即可查看

* 在线文档 [http://www.myqee.com/docs/](http://www.myqee.com/docs/)
* API接口 [http://www.myqee.com/docs/zh-cn/core/index.html](http://www.myqee.com/docs/zh-cn/core/index.html)


安装配置
====
系统要求
----
推荐lamp架构，php至少5.2.3以上版本，推荐5.3以上最新版本，apache或nginx需要支持rewrite

**Apache配置样例：**
	
	<Virtualhost *>
	    # 网站域名，（写入配置时请把中文注释去掉，下同）
		ServerName www.myqee.com
		
		# DocumentRoot一定要/结尾
		DocumentRoot "D:/php/myqee_v2/"
		
	    # 以下内容无需修改
	    
		DirectoryIndex index.html index.php
		RewriteEngine On
		RewriteRule .*/\..* - [F,L]
		
	    RewriteCond %{DOCUMENT_ROOT}wwwroot/%{REQUEST_FILENAME} -f [OR]
	    RewriteCond %{DOCUMENT_ROOT}wwwroot/%{REQUEST_FILENAME} -d
	    RewriteRule ^/(.*)$ /wwwroot/$1 [PT,L]
	
	    RewriteRule ^/(.*)$ /index.php/$1 [PT,L]
	    
	    # 以下是一些文件的缓存设置，可修改或去掉
	    <IfModule expires_module>
	    	ExpiresActive On
	    	ExpiresByType text/css "access plus 3 days"
	    	ExpiresByType image/png "access plus 14 days"
	    	ExpiresByType image/gif "access plus 14 days"
	    	ExpiresByType image/jpeg "access plus 14 days"
	    	ExpiresByType application/x-shockwave-flash "access plus 28 days"
		</IfModule>
	</Virtualhost>
 
!!! 注意，请去掉中文注释 

若无Apache的管理权限或怕麻烦，可在系统目录example.htaccess文件重命名为".htaccess"文件开启rewrite
（window下可以直接双击根目录的 “将example.htaccess修改为.htaccess文件” 文件修改后缀）


**Nginx配置样例：**
	
	server {
	    set         $www /home/www/myqee;
	    root        $www;
	    index       index.html index.php;
	    listen      80;
	    charset     utf-8;
	    server_name www.myqee.com;
	    server_name myqee.com;
	    
	    if ( $host != 'www.myqee.com') {
	        rewrite ^/(.*)$ http://www.myqee.com/$1 redirect;
	    }
	
	    location ~* .(css|js)$ {
	        if (-f $request_filename) {
	            expires 3d;
	            break;
	        }
	    }
	    location ~* .(jpg|gif|png)$ {
	        if (-f $request_filename) {
	            expires 15d;
	            break;
	        }
	    }
	    location ~* .(swf|zip|rar)$ {
	        if (-f $request_filename) {
	            expires 1m;
	            break;
	        }
	    }
	
	    rewrite ^/~([a-zA-Z0-9\-_]+)~(.*)$ /projects/$1/wwwroot/$2 last;
	    rewrite ^/((?!(?:wwwroot/)).*)$ /wwwroot/$1;
	
	    if (!-e $request_filename) {
	        rewrite ^/wwwroot/.* /index.php last;
	    }
	
	    include fastcgi_php;
	}
	




升级
====
升级方法及注意事项请看[UPGRADE.md](upguade.html)文件


修改日志
====
请查阅[CHANGELOG.md](changelog.html)文件