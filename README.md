MyQEE PHP Framework  ![MyQEE DIR](./manual/html/assets/images/passing.png)
====

## 简介


MyQEE是一个开源、快速、优雅的PHP框架，支持HMVC模式，建立在PHP5.2基础之上，支持多项目管理开发，数据库内置自动主从分离功能，MySQL支持事务操作功能并且支持自动嵌套功能，多驱动设计灵活适应各种环境。点击访问 [MyQEE入门指引](./manual/guide/zh-cn/starting.md)。

**帮助你和你的团队写出优雅的PHP是我们追求的目标。**

## MyQEE PHP框架的特色

* [HMVC](./manual/guide/zh-cn/hmvc.md) (分层MVC设计),多项目支持，开发更灵活；
* 包括`Database`, `Cache`, `HttpClient`, `Session`, `Storage`, `Upload` 等支持多驱动，可以适应不同环境的需求，其中数据库支持 `MySQL`, `MySQLI`, `Mongo`, `SQLite`, `Postgre`，缓存支持 `Memcache`, `File`, `Redis`, `Apc`, `Database`, `SQLite`, `WinCache` 等；
* 数据库提供强大的QueryBuilder功能，可实现同相同程序兼容多种数据库，解决迁移环境后顾之忧；
* 云引擎支持：提供SAE类库可以让系统几乎不用修改任何代码即可完美运行在各云服务器中；
* 高性能和优雅的代码；
* 完备和详细的文档和API支持，更可简单的生成自己的团队文档；
* 为团队开发而生，特别提供团队类库功能，多项目设置可以帮助团队成员之间规划独立和共用的代码；
* ORM支持，提供了特有的高性能ORM；
* 独创4模式设计：普通控制器和后台、命令行、系统调用的控制器相互分离，系统更加安全可靠；
* 独有的控制器分离机制，普通控制器，后台控制器，命令行控制器相互独立，提升系统安全；

## 安全性

* 系统内置XSS安全过滤
* 防SQL注入，强大的QueryBuilder
* 强制数据类型转换



## 安装配置

系统要求：推荐lamp架构，php至少5.2.3以上版本，推荐5.3以上最新版本，apache或nginx需要支持rewrite

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
	
	    RewriteRule ^/.* /index.php [PT,L]
	    
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
	




## 升级
升级方法及注意事项请看[UPGRADE.md](UPGRADE.md)文件


##修改日志

请查阅[CHANGELOG.md](CHANGELOG.md)文件


## 商业友好的开源协议


MyQEE遵循Apache2开源协议发布。Apache Licence是著名的非盈利开源组织Apache采用的协议。该协议和BSD类似，鼓励代码共享和尊重原作者的著作权，同样允许代码修改，再作为开源或商业软件发布。

