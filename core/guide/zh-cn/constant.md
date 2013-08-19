# 系统常量

## START_TIME
(int) 启动时间

## START_MEMORY
(int) 启动时使用掉的内存

## MYQEE_VERSION
(string) MyQEE 大版本号

## EXT
(string) PHP文件后缀，即 `.php`

## TIME
(int) 请求时间，在同一个程序里建议用TIME取代 `time()` 除非有特殊需求。

## DS
(string) 常量 `DIRECTORY_SEPARATOR` 的简写，window下为`\`, 其它系统为 `/`

## IS_WIN
(boolean) 是否window服务器

## CRLF
(boolean) 换行符: `\r\n`

## IS_MBSTRING
(boolean) 系统是否支持mbstring类库函数


## IS_DEBUG
(int) 是否开启Debug模式，有3种模式

    if (IS_DEBUG>>1)
    {
        //开启了在线调试
    }
   
    if (IS_DEBUG & 1)
    {
        //本地调试打开
    }
    
    if (IS_DEBUG)
    {
        // 开启了调试
    }


## HAVE_NS
(boolean) 系统是否支持php5.3的命名空间

## IS_CLI
(boolean) 是否命令行下执行，当true时，控制器将从 `controllers-shell` 目录中读取而不是 `controllers`目录

## IS_SYSTEM_MODE
(boolean) 是否系统内部调用，这个会发生在开启文件同步模式的情况下的服务器间内部通讯的时候，正常用户请求下均为false，当true时，控制器将从 `controllers-system` 目录中读取而不是 `controllers`目录

## IS_ADMIN_MODE
(boolean) 是否后台模式，当true时，控制器将从 `controllers-admin` 目录中读取而不是 `controllers`目录

## DIR_SYSTEM
(string) 系统目录

## DIR_CORE
(string) 核心类库目录

## DIR_PROJECT
(string) 项目目录

## DIR_TEAM_LIBRARY
(string) 团队公用类库目录

## DIR_LIBRARY
(string) 第三方类库目录

## DIR_WWWROOT
(string) wwwroot目录

## DIR_ASSETS
(string) 静态文件目录

## DIR_UPLOAD
(stirng) 文件上传目录，默认为 `wwwroot/upload/` 目录

## DIR_DATA
(stirng) 网站数据目录，默认为 `data/` 目录

## DIR_TEMP
(stirng) 网站临时数据目录，默认为 `sys_get_temp_dir()` 返回的目录

## DIR_LOG
(stirng) 程序LOG存放目录，默认为 `data/log/` 目录

## DIR_CACHE
(string) 缓存目录，默认为 `data/cache/` 目录

## DIR_UPLOAD
(stirng) 文件上传目录，默认为 `wwwroot/upload/` 目录

## DIR_UPLOAD
(stirng) 文件上传目录，默认为 `wwwroot/upload/` 目录


## INITIAL_PROJECT_NAME
(string) 初始项目名，比如 default

## URL_ASSETS
(string) 静态文件目录对应的URL路径

