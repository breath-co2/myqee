系统目录
====================

* config.php            *全局配置文件*
* index.php             *统一入口文件*
* assets/				*静态文件目录*
* data/                 *站点部分数据存储目录*
  * log/                *Log目录* 
  * cache/              *缓存目录* 
  * temp/              *临时文件目录* 
* libraries/            *类库目录*
  * bootstrap.php       *系统启动文件*
  * MyQEE/
     * Core/
         * classes/
         * config/
         * controllers/
         * i18n/
         * modules/
         * shell/
         * views/
  * 其它类库...
     * ...
* projects/                 *项目目录*
  * defult/
     * classes/
     * config/
     * controllers/
     * i18n/
     * modules/
     * orm/
     * shell/
     * views/
     * wwwroot/             *项目根目录静态文件*
     * config.php           *项目配置文件，可无*
  * 其它项目...
     * ...
* wwwroot/                  *目录下静态文件存放目录*



目录说明
------------
根目录包括：libraries,projects,wwwroot,bulider,data,temp这几个目录：

* libraries 存放各种类库，包括系统提供的和第三方的
* projects  存放你的项目，默认项目目录为default
* wwwroot   存放一些静态文件
* bulider   系统构建优化合并后的文件存放目录
* data      产品运营中产生的一些数据存放目录
* data/temp 临时目录，也可配置到系统临时目录中
* data/log  LOG目录
* data/cache 缓存目录

您下载的原始包中，libraries目录中已包含MyQEE类库，projects目录中已包含default项目目录，其它均为空

