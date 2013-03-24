MyQEE命令行脚本工具
================


merge-assets
------------
合并项目静态文件输出到wwwroot/asstes/目录

OPTIONS:

    -r 全部重新生成
    -a 全部项目

EXAMPLE:

    生成全部项目assets文件: merge-assets -a
    全部项目重新生成文件   : merge-assets -a -r
    生成default项目文件   : merge-assets default


注意：此脚本依赖 recess 和 uglifyjs 分别处理css和js：

 * recess   https://github.com/twitter/recess
 * uglifyjs https://github.com/mishoo/UglifyJS

npm安装recess和uglifyjs：

    npm install recess -g
    npm install uglify-js@1




recreate-ide-helper-file
------------------------
重新生成类库目录中_ide_helper.php文件(此文件在生产环境中是不会用到的)，这个用于自行开发类库时批量生成类库映射文件，如果只是在project和team_library中开发代码则不需要生成ide helper

我参数，直接执行即可



svn-tools
---------
SVN合并、查看合并日志工具
