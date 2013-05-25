<style>
h1{font-size:16px;}
pre{font-family:Consolas,'Bitstream Vera Sans Mono','Courier New',Courier,monospace;color:#911;border:1px dotted #333;padding:4px;background:#feffd3;}
</style>
<table class="mainTable">
    <tr>
        <th>后台类库使用说明</th>
    </tr>
    <tr>
        <td>
            <p>
            后台类库不包含额外功能，仅仅包含登录登功能、管理员管理、权限组管理的功能，方便使用MyQEE框架进行二次开发。
            </p>
        </td>
    </tr>
    <tr>
        <td>
            <h1>如何配置后台菜单</h1>
            <p>后台菜单配置在 projects/admin/config/admin/menu/default.config.php 文件里，这是一个多维数组文件，打开此文件可以按照里面的例子编写。</p>
        </td>
    </tr>
    <tr>
        <td>
            <h1>增加、修改权限配置</h1>
            <p>
            权限配置存放在 projects/admin/config/admin/permission.config.php 文件里（不存在可创建）加入内容如下：
<pre>
&lt;?php
$config['test'] = array(
    'name' => '测试权限',
    'perm' => array(
        '分组一-----------------',
        'aa' => '权限一',
        'bb' => '权限二',
        '分组二-----------------',
        'cc' => '权限三',
        'dd' => '权限四',
        'ee' => '权限五',
    ),
);
</pre>

加好后将在“管理员管理”->“添加权限组”里可看到新增加的权限。
<br>
<br>
如果在程序里判断权限？
<br>
Session::instance()->member()->perm()->is_own('test.aa');
<br>
返回true则表示有“权限一”的权限，返回false则表示没权限
<br>
<br>
Session::instance()->member()->perm()->is_own('test.dd');
<br>
返回true则表示有“权限四”的权限，返回false则表示没权限
            </p>
        </td>
    </tr>

    <tr>
        <td>
        <h1>如何创建后台控制器？</h1>
        <p>
        后台控制器在projects/admin/admin/目录里，规则同普通控制器，但文件名后缀为.admin.php(前台控制器为.controller.php)
        </p>
        </td>
    </tr>

    <tr>
        <td>
        <h1>如何使后台可以使用Memcache作为Session的存储介质？</h1>
        <p>
        无须修改任何代码直接在config里配置就可以实现。具体配置方法如下：
        <br />
        首先，在projects/admin/config/目录里创建一个cache.config.php文件，内容如下：
<pre>
&lt;?php
$config['for_session'] = array(
    'driver'        => Cache::DRIVER_MEMCACHE,
    'driver_config' => 'default',           // 指定memcache配置为$config['default']的配置
    'prefix'        => 'session_',          // 前缀
);
</pre>
        <br>
        然后在projects/admin/config/cache/目录（不存在则创建）创建一个memcache.config.php文件，内容如下：
<pre>
&lt;?php
$config['default'] = array(
    array(
        'host' => '10.1.11.1',      // memcache服务器 IP
        'port' => 2001,             // 端口
        'weight' => 20              // 权重
    ),
    array(
        'host' => '10.1.11.2',
        'port' => 2001,
        'weight' => 20
    ),
);
</pre>

        <br>
        最后，在projects/admin/config/目录里创建session配置文件，内容如下：
<pre>
&lt;?php
# Session ID 名称，不设置则为默认
$config['name'] = 'sid';

# 驱动类型
$config['driver'] = 'Cache';

# 数据缓存时间
$config['expiration'] = 86400;

# 缓存驱动内配置
$config['driver_config']['driver_config'] = 'for_session';      //表示使用cache.config.php中$config['for_session']的配置
</pre>
        </p>
        </td>
    </tr>
</table>