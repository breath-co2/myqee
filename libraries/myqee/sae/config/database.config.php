<?php
$config['default'] = array
(
    'type'       => 'MySQL',
    'connection' => array
    (
        'hostname'   => array
        (
            'master' => SAE_MYSQL_HOST_M,
            'slaver' => array
            (
                SAE_MYSQL_HOST_S,
            ),
        ),
        'database'   => SAE_MYSQL_DB,
        'username'   => SAE_MYSQL_USER,
        'password'   => SAE_MYSQL_PASS,
        'persistent' => false,
        'port'       => SAE_MYSQL_PORT,
    ),
    'table_prefix' => '',
    'charset'      => 'utf8',
    'caching'      => false,
);