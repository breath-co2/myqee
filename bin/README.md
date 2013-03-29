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

支持的功能

 * 合并版本
 * 将一个目录文件全部合并到一个带svn版本控制的目录，多余的文件会执行svn delete命令清除Index
 * 将一个目录的文件同步到一个带svn版本控制的目录

#### 关于版本合并功能 
通常我们会在working版本(或dev版本)中开发代码，然后把修改的内容合并到trunk中，这个工具就是自动合并指定版本到trunk目录用的
可自动将开发版本文件合并到trunk文件中

#### 关于查看LOG功能
这个功能是配合trunk合并功能用的，可查看未合并的log信息


#### 使用方法

命令   |  说明
------|-------
`svn-tools merge -v 1033:1036`     |         把1034,1035,1036版本合并进trunk（注意不含1033版本）
`svn-tools merge -v 1055  `        |         把1055版本合并进trunk
`svn-tools log`                    |         查看没有合并的版本记录
`svn-tools sync-dir -f ~/Sites/test1/ -t ~/Sites/test2/`  |  将test1目录同步到test2目录


#### 参数说明

`svn-tools merge`参数  |  说明
----------------------|--------
`--dry-run`           |  输出执行命令模式，而不是直接直接，加上后执行程序将不做实际合并操作
`--no-comment`        |  只做合并，不做自动提交工作，加上后可仔细提交合结果

`svn-tools log`参数    |  说明
----------------------|------
`--trunk-url=***`     |  trunk目录的SVN的URL路径
`--working-url=***`   |  workding目录的SVN的URL路径
`-h` 或 `--help`      |  帮助
`-p` 或 `--paht=***`  |  指定trunk目录的路径
`-v` 或 `--verbose`   |  查看Log的详细信息

`svn-tools sync-dir`参数  | 说明
-------------------------|---------
`-f` 或 `--from=***`     | 源目录，必须参数
`-t` 或 `--to=***`       | 目标目录，必须参数
`-a` 或 `--all`          | 是否完整模式，可选参数<br />将完整同步到目标目录，如果某个文件或文件夹在from目录中不存在但在目标文件中存在，则会用svn delete命令删除掉目标文件中多余的文件

