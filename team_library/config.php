<?php

/**
 * 数据库配置
 */
$config['database']['default'] = array
(
    'type' => 'MySQLI',
    'connection' => array
    (
        'hostname'   => '127.0.0.1',
        'database'   => 'myqee',
        'username'   => 'root',
        'password'   => '123456',
        'persistent' => false,
    ),
    'table_prefix' => '',
    'charset'      => 'utf8',
    'caching'      => false,
    'profiling'    => true,
);


/**
 * Session名称
 *
 * @var string
 */
$config['session']['name'] = 'SID';


/**
 * Session校验字符串，为安全起见，建议更换
 *
 * @var string
 */
$config['session']['check_string'] = '$@de23#$%@.dG2.p4Ad';


