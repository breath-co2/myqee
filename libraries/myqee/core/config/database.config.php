<?php
$config = array(
    'default' => array(
        'type' => Database::TYPE_MySQLI,
        'connection' => array( /**
             * The following options are available for MySQL:
             *
             * string   hostname     server hostname, or socket
             * string   database     database name
             * string   username     database username
             * string   password     database password
             * boolean  persistent   use persistent connections?
             *
             * Ports and sockets may be appended to the hostname.
             */
            'hostname'   => '127.0.0.1',
        	'database'   => 'myqee',
        	'username'   => 'root',
        	'password'   => '123456',
        	'persistent' => false
        ),
        'table_prefix' => '',
        'charset' => 'utf8',
        'caching' => false,
        'profiling' => true
    )
);