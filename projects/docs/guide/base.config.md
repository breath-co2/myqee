服务器配置
=======
config.php配置
-----------------
您会在根目录里发现有一个config.new.php文件，首先，请先将此文件重命名为config.php，在config.php里您可以根据自己都需求进行设置。

Apache配置
---------
以本站为例：

    <Virtualhost *>
        ServerName www.myqee.com
        ServerAlias myqee.com
        DocumentRoot "/home/www/myqee/"
        
        # 以下内容都不需要修改
        
        RewriteEngine On
        # 禁止.开头的文件被访问
        RewriteRule .*/\..* - [F,L]

        # 各个项目的wwwroot下的文件访问
        RewriteRule ^/~([a-zA-Z0-9\-_]+)~(.*)$ "/projects/$1/wwwroot/$2" [L]
        
        # wwwroot有文件，则定向到该文件上
        RewriteCond %{DOCUMENT_ROOT}wwwroot%{REQUEST_FILENAME} -f [OR]
        RewriteCond %{DOCUMENT_ROOT}wwwroot%{REQUEST_FILENAME} -d [OR]
        RewriteCond %{DOCUMENT_ROOT}wwwroot%{REQUEST_FILENAME} -l
        RewriteRule ^/(.*)$ /wwwroot/$1 [L]
        
        # 文件不存在则都定向到php上执行
        RewriteRule ^/(.*)$ /index.php/$1 [PT,L]
        
        # 以下为过期设置
        <IfModule expires_module>
            ExpiresActive On
            ExpiresByType text/css "access plus 3 days"
            ExpiresByType image/png "access plus 14 days"
            ExpiresByType image/gif "access plus 14 days"
            ExpiresByType image/jpeg "access plus 14 days"
            ExpiresByType application/x-shockwave-flash "access plus 30 days"
        </IfModule>
    </Virtualhost>

[!!] 注意在写入配置文件时把中文注释删除或保存为UTF-8编码

若您无法修改Apache的配置，可在系统目录下加入".htaccess"文件开启rewrite，内容如下：

    RewriteEngine On

    RewriteRule ^~([a-zA-Z0-9\-_]+)~(.*)$ "projects/$1/wwwroot/$2" [L]
    RewriteRule ^(?!(?:wwwroot/|index\.php|projects/[a-zA-Z0-9\-_]+/wwwroot/)).+$ wwwroot/$0 [PT,NS]
    
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-l
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule wwwroot/(.*)$ index.php/$1 [PT,L]




Nginx配置样例
------------
    server {
        set         $www /home/www/myqee;
        root        $www;
        index       index.html index.htm index.php;
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

SAE配置
---------
首先，建议您下载[MyQEE For SAE](http://www.myqee.com/download/)类库，然后解压缩到libraries目录。
在根目录的*config.php*里修改$config['autoload']，加入'SAE'类库，这样系统会自动加载SAE支持包。
使用此类库扩展后，您就可以像在普通环境里运行程序而无需考虑平台的差异，无需要修改您的代码。此类库主要兼容了包括：Database(Mysql),HttpGet,Memcached,Cache等，并集成了SAE SDK。

**SAE里开启rewrite方法:**
打开您部署版本目录里的config.yaml，例如：app/myqee/1/config.yaml
加入以下内容：

    handle:
      - rewrite: if (path ~ "^~([a-zA-Z0-9\-_]+)~(.*)$") goto "/projects/$1/wwwroot/$2"
      - rewrite: if(!is_dir() && !is_file()) goto "/index.php?%{QUERY_STRING}"

新浪SAE云计算平台URL：[http://sae.sina.com.cn/](http://sae.sina.com.cn/)