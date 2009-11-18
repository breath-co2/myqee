安装：
站点根路径请设置到wwwroot目录下

请将数据库文件手动导入到数据库呢

需要配置数据库设置，请到
application/config/database.php文件修改

后台地址http://localhost/admin/
若你根目录不是设置在wwwroot下，你可能需要修改wwwroot/admin.php文件中页面路径地址



系统虽然不是用的kohana系统，但是绝大部分模式都是使用kohana2.3.4版本，包括自动加载方式等等。
细节上会有些区别，这个以后我会详细说明
Kohana的手册地址http://docs.kohanaphp.com/



注意事项：
读取config请使用Myqee::config('test')而不要使用Kohana::config('test')
Database和Kohana的方法一样
很多工具都在Tools类里


本系统可以再次加载Kohana，使用方法：在需要用到Kohana前Kohana::setup();即可加载，
常本人不赞成加载Kohana，如果你确实有需要（比如你比较喜欢用ORM），那么你就需要加载Kohana了。



目录结构：
本目录结构和Kohana类似但也有不少区别
用户扩展文件都在application目录
后台扩展文件都在admin目录
本系统全部在system目录，系统后台文件都在system/admin/目录

网站前后台可以分离使用2个不同的域名，只要把wwwroot/admin.php放到别的地方，然后修改下里面的配置即可。



目前使用了.htaccess文件，也有直接配置在apache里面的，或者不用rewrite也可以，但要修改一下后台文件的配置
