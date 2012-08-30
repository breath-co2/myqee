如何使用
====





后台类库相关配置
====

修改欢迎语、菜单配置等后台类库配置
-------------------------------------
将文件

	libraries/MyQEE/Administration/config/admin/core.config.php 

复制到 

	projects/your_project/config/admin/core.config.php

文件(**your_project**替换为你实际的项目目录)，然后修改里面的内容

比如：将$config['index_title']的内容修改为'欢迎使用***管理后台';

增加菜单组：你可以在projects/admin/config/admin/menu/目录里增加一个配置文件，例如：text.config.php然后修改上面配置文件中的$config['menu']增加'test' => '测试菜单'这样，你再到后台修改管理员时，会发现多了一个选择菜单配置的项目，修改后，此用户重新登录即可采用新菜单了。


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
*非控制器里用 Session::instance() 代替 $this->session() 即可。*

