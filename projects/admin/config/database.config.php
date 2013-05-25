<?php
$config['admin'] = array
(
    'type'       => 'MySQLI',
    'connection' => array
    (
        'hostname'   => '127.0.0.1',    //数据库地址
        'port'       => '3306',         //端口
        'database'   => 'testmyqee',    //库名称
        'username'   => 'root',         //用户名
        'password'   => '123456',       //密码
    ),
    'table_prefix' => '',  //表前缀
    'charset'      => 'utf8',
    'caching'      => false,
    'profiling'    => false,
);