创建一个新项目
====================
建立项目目录
------------
在projects目录中新建一个文件夹
注意：文件夹必须为英文字母数字下滑线或横线的组合，推荐全小写，例如my_new_project。

然后打开根目录的config.php文件（没有的话则把config.new.php复制为config.php），在文件的后半段找到$config['projects']项。
添加一个key为my_new_project的数组，结构类似：
<pre><code>'my_new_project' => array(
    'name'  => '新项目',
    'isuse' => true,
    'dir'   => 'my_new_project',
    'url'   => '/test',
),</code></pre>
* name：项目名称
* isuse：是否启用
* dir：项目目录
* url：项目url路径前缀，支持多个，用数组配置，url也支持http://开头
  例如：array('/test','http://www.mytest.com/')

上例中，目录为my_new_project，url前缀为/test

则类似：
http://www.abc.com/test/123
http://www.abc.com/test/abc
这样的URL都会被认为是这个项目的。

这时您可以在项目目录里建立 controllers,classes,models,views,orm,wwwroot 等目录，创建属于项目自己的文件。

可参考：

* 创建控制器 (controller)
* 创建模块 (model)
* 创建视图 (view)
* 创建类文件
* 创建ORM

配置项目类库
---------------
每一个项目都会用到根目录的config.php配置数据，但如果这个项目需要特殊的配置（比如需要加载另外一个类库）时怎么办呢？

此时，您可以在项目目录中建立一个config.php文件。
比如上例中，建立的文件路径为：/projects/my_new_project/config.php

此项目中的配置即可覆盖主config.php中的配置。

比如，my_new_project项目需要加载第三方类库MyTestLib则就在/projects/my_new_project/config.php文件中加入：

    $config['autoload'] = array(
        'MyTestLib',
    );

即可，当然，支持加载N个类库，类库是可以重复使用的。