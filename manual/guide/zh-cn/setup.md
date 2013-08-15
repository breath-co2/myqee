# 服务器安装与配置


## 环境要求
MyQEE PHP Framework可以支持Windows/Unix服务器环境，可运行于包括Apache、IIS和nginx在内的多种WEB服务器和模式，需要 PHP5.2.1 及以上版本。

* 数据库方面可扩展支持MySQL、MsSQL、PgSQL、SQLite、Oracle、Ibase、MongoDB以及PDO等多种数据库和连接。
* 框架本身没有什么特别模块要求，具体的应用系统运行环境要求视开发所涉及的模块。MyQEE具有完善的可扩展性，可以依照自己系统的支持情况选择不同的对象驱动，比较适合中小型甚至大型开发项目，特别是具有多项目开发需求的团队。
* 对于刚刚接触PHP或者MyQEE的新手，本地测试环境推荐使用XAMMP。

### 推荐环境
* Linux/Unix
* Apache 2+
* MySQL 5.1+
* PHP 5.3+

本地开发无需特殊环境，只需要PHP5.2.1以上版本即可。


## 根目录config.php配置
您会在根目录里发现有一个config.new.php文件，首先，请先将此文件重命名（或复制）为config.php，在config.php里您可以根据自己都需求进行设置，内有详细说明。

## Apache配置
以本站为例：

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
	
!!! 注意在写入配置文件时把中文注释删除或保存为UTF-8编码


若您无法修改Apache的配置，可在系统目录下加入".htaccess"文件开启rewrite，内容如下：

	RewriteEngine On
	
	RewriteRule ^(?!(?:wwwroot/|index\.php|projects/[a-zA-Z0-9\-_]+/wwwroot/)).+$ wwwroot/$0 [PT,NS]	
	 
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteRule wwwroot/(.*)$ index.php/$1 [PT,L]


## Nginx + PHP-FPM 配置样例

	server {
	    set         $www /home/www/myqee/;
	    root        $www;
	    index       index.html index.htm index.php;
	    listen      80;
	    charset     utf-8;
	    server_name www.myqee.com;
	    server_name myqee.com;

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

		location ~ \.php$ {
			fastcgi_split_path_info ^(.+\.php)(/.+)$;
			# With php5-fpm:
			fastcgi_pass unix:/var/run/php5-fpm.sock;
			fastcgi_index index.php;
			include fastcgi_params;
		}
	    include fastcgi_params;
	}

!!! 其中fastcgi_php指 `/etc/nginx/fastcgi_params` 文件，某些系统可指定为 `/etc/nginx/fastcgi_php` 可设置 `include fastcgi_php;` 网上类似php+nginx配置说明很多，可参考后加入rewrite部分就可以了


## 新浪SAE配置
首先，请下载 MyQEE For SAE 类库，然后解压缩到libraries目录。<br />
在根目录的 `config.php` 里修改 `$config['autoload']`，加入'SAE'类库，这样系统会自动加载SAE支持包。使用此类库扩展后，您就可以像在普通环境里运行程序而无需考虑平台的差异，无需要修改您的代码。此类库主要兼容了包括：`Database(Mysql)`, `HttpClient`, `Memcached`, `Cache` 等，并集成了SAE SDK。

**SAE里开启rewrite方法:**

打开您部署版本目录里的config.yaml，例如：`app/myqee/1/config.yaml`
<br />加入以下内容：

	handle:
	  - rewrite: if (path ~ "^~([a-zA-Z0-9\-_]+)~(.*)$") goto "/projects/$1/wwwroot/$2"
	  - rewrite: if(!is_dir() && !is_file()) goto "/index.php?%{QUERY_STRING}"

新浪SAE云计算平台URL：[http://sae.sina.com.cn/](http://sae.sina.com.cn/)

