# 使用 Composer 开源软件包

## 为什么使用软件包

在 PHP 包管理上面，PHP 发展的很缓慢，导致的结果就是很少发现程序员会使用像 PEAR 这样的工具，而 [Composer](http://getcomposer.org/) 的出现改善了这一情况，它是时下正流行的 PHP 包管理器，拥有非常活跃的社区和丰富的PHP类库包，因为它使用了命名空间，所以只能运行在 PHP5.3以上的环境里。

[Packagist](https://packagist.org/) 是为 Composer 提供包数据的管理网站，在里面提供了非常丰富的包，你可以非常轻松的使用这些包来开发相应的功能，Composer 会帮你维护和更新软件包之间的依赖包，你也可以轻松的发布自己的软件包（比如你在 github 上的项目）。

> 不管你是一名 PHP 新手还是一名具有丰富经验的 PHP 开发人员，我们都极力的推荐你花一点点时间了解下 Composer 和 Packagist。

MyQEE 安装 Composer 后，MyQEE 将会自动载入 `libraries/autoload.php`（Composer 生成的自动加载程序），这样你就可以在 MyQEE 里直接使用这些已经安装好的软件包了，软件包的升级也很简单，`composer update` 就可以了，如果你希望安装 MyQEE 提供的其它类库，也可以在 [Packagist](https://packagist.org/) 里轻松找到，搜索 `MyQEE` 获取全部包。


## 安装 Composer 命令

``` bash
cd /path/to/your/project
curl -s http://getcomposer.org/installer| php 
sudo mv composer.phar /usr/local/bin/composer
```

代码说明：执行 `curl -s http://getcomposer.org/installer| php` 后会在当前目录生成一个 `composer.phar` 文件，然后通过 `sudo mv composer.phar /usr/local/bin/composer` 将此文件移动到 `/usr/local/bin/composer` 这样，就可以直接使用 `composer` 命令了


## 定义使用自己的软件包

Composer 的包配置文件是根目录下的 `composer.json` 文件，默认 MyQEE 并未加载什么包，你可以自行添加需要的软件包，比如安装 `monolog/monolog` 包，只需要加入：

``` javascript
    "require": {
        "monolog/monolog": "1.0.*"
    }
```

保存后执行 `composer install` 即可，如果已经安装过可执行 `composer update` 更新。

## 注意事项

MyQEE 加载的并不是 `vendor/autoload.php` 文件，而是 `vendor/autoload-for-myqee.php`， 当 composer 安装完毕后，安装程序会判断是否依赖非 MyQEE 生态圈的包，如果不存在则不会创建 `vendor/autoload-for-myqee.php`，系统也就不会加载 composer 的 autoload，这样可以节约更多资源。

只有判断存在不是 MyQEE 生态圈的包，安装脚本才会创建 `vendor/autoload-for-myqee.php` 软连接，有了这个文件，MyQEE 就会载入 composer 的 autoload 了。

