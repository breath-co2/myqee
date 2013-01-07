<?php
$config = array
(
    'default' => array
    (
        'type' => Database::TYPE_MySQLI,
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
    )
);