详细文档请阅读 http://www.myqee.com/docs/

升级方法及注意事项请看UPGRADE.TXT文件


Apache配置样例：
<Virtualhost *>
	ServerName www.myqee.com
	ServerAlias myqee.com
	DocumentRoot "D:/php/myqee_v2/"
	RewriteEngine On
	RewriteRule .*/\..* - [F,L]
	
    RewriteCond %{HTTP_HOST} !^www\.myqee\.com [NC]
    RewriteCond %{HTTP_HOST} !^$
    RewriteRule ^/(.*)       http://www.myqee.com/$1 [L,R]
    
	RewriteRule ^/~([a-zA-Z0-9\-_]+)~(.*)$ "/projects/$1/wwwroot/$2" [L]

    RewriteCond %{DOCUMENT_ROOT}wwwroot%{REQUEST_FILENAME} -f [OR]
    RewriteCond %{DOCUMENT_ROOT}wwwroot%{REQUEST_FILENAME} -d [OR]
    RewriteCond %{DOCUMENT_ROOT}wwwroot%{REQUEST_FILENAME} -l
    RewriteRule ^/(.*)$ /wwwroot/$1 [L]
    
    RewriteRule .* /index.php [PT,L]
    
    <IfModule expires_module>
    	ExpiresActive On
    	ExpiresByType text/css "access plus 3 days"
    	ExpiresByType image/png "access plus 14 days"
    	ExpiresByType image/gif "access plus 14 days"
    	ExpiresByType image/jpeg "access plus 14 days"
    	ExpiresByType application/x-shockwave-flash "access plus 28 days"
	</IfModule>
</Virtualhost>

若无Apache的管理权限或怕麻烦，可在系统目录example.htaccess文件重命名为".htaccess"文件开启rewrite
（window下可以直接双击根目录的 “将example.htaccess修改为.htaccess文件” 文件修改后缀）


Nginx配置样例：
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










下面是一些管理类库高级配置：

配置后台菜单：
-------------------------------------
打开projects/admin/config/admin/menu/default.config.php 文件 。这是一个多维数组文件，可以按照里面的例子编写。


配置权限：
-------------------------------------
在projects/admin/config/admin/目录中建立permission.config.php文件，内容如下：
<?php
$config['test'] = array
(
    'name' => '测试权限',
    'perm' => array(
        'test_perm1' => '权限一',
        'test_perm2' => '权限二',
        'test_perm3' => '权限三',
        'perm'       => '权限四',
    ),
),
$config['test2'] = array
(
    'name' => '测试权限2',
    'perm' => array(
        '分组一---------------------',
        'test_perm1' => '权限一',
        'test_perm2' => '权限二',
        '分组一---------------------',
        'test_perm3' => '权限三',
        'perm'       => '权限四',
    ),
),


保存后再打开后台的权限管理即可看到新的权限被列出来了。

程序如何调用？很简单，在控制器里执行
    $is_own = $this->session()->member()->perm()->is_own('test.test_perm1');
    var_dump($is_own);
返回true则表示有test的test_perm1权限，返回false表示用户无此权限

非控制器里用 Session::instance() 代替 $this->session() 即可。



修改欢迎语、菜单配置等后台类库配置
-------------------------------------
将libraries/MyQEE/Administration/config/admin/core.config.php复制到projects/admin/config/admin/core.config.php文件，然后修改里面的内容。
比如：将$config['index_title']的内容修改为'欢迎使用***管理后台';
增加菜单组：你可以在projects/admin/config/admin/menu/目录里增加一个配置文件，例如：text.config.php然后修改上面配置文件中的$config['menu']增加'test' => '测试菜单'这样，你再到后台修改管理员时，会发现多了一个选择菜单配置的项目，修改后，此用户重新登录即可采用新菜单了。
