数据库配置
==============
数据库的配置为 `$config['database']` 相关配置

配置举例
--------

    <?php
    //支持多个配置
    $config['database']['default'] = array
    (
        'type'       => 'MySQL',                //数据库为MySQL（目前还支持MySQLI）
        'connection' => array
        (
            'hostname'   => 'localhost',        //服务器，支持主从集群配置
            'port'       => 3306,               //端口，不设则默认3306
            'database'   => 'myqee',            //库名称
            'username'   => 'root',             //用户名
            'password'   => '123456',           //密码
            'persistent' => false,              //是否持久连接
        ),
        'table_prefix' => 'mq_',                //表前缀
        'charset'      => 'utf8',               //数据库编码
        'caching'      => false,                //是否自动缓存，暂不可用
        'profiling'    => true,                 //是否统计调试，暂不可用
    );

使用

    $db = Database::instance('default');
    //或    
    $db = Database::instance();  //若采用默认配置（default）则可不传参数
    
    //小提示：在Model中可用$this->db();获取数据库对象
    



主从集群配置
------------

    // $config['database']['default']['connection']['hostname'] 若配置成数组，则可实现主从功能，如下：
    array(
        // 主数据库，本系统只支持一主多从式集群，多主多从式由于稳定性差且难维护，所以本系统不支持
        'master' => 'm1.test.com',
        // 从数据库
        'slaver' => array
        (
            'm1.test.com',      // 主数据库仍会有1/5的几率被当做从数据库查询，删除本行则完全排除主数据库
            's1.test.com',      // s1有2/5几率
            's1.test.com',            
            's2.test.com',      // 1/5几率
            's3.test.com',      // 1/5几率
        ),
    ),

完整代码示例：

    $config['database']['default'] = array
    (
        'type'       => 'MySQL',               //数据库为MySQL（目前还支持MySQLI）
        'connection' => array
        (
            'hostname'   => array
            (
                // 主数据库，本系统只支持一主多从式集群，多主多从式由于稳定性差且难维护，所以本系统不支持
                'master' => 'm1.test.com',
                // 从数据库
                'slaver' => array
                (
                    'm1.test.com',
                    's1.test.com',
                    's1.test.com',
                    's2.test.com',
                    's3.test.com',
                ),
            ),
            'port'       => 3306,               //端口，不设则默认3306
            'database'   => 'myqee',            //库名称
            'username'   => 'root',             //用户名
            'password'   => '123456',           //密码
            'persistent' => false,              //是否持久连接
        ),
        'table_prefix' => 'mq_',                //表前缀
        'charset'      => 'utf8',               //数据库编码
        'caching'      => false,                //是否自动缓存，暂不可用
        'profiling'    => true,                 //是否统计调试，暂不可用
    );
