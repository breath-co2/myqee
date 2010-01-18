<?php defined('MYQEEPATH') or die('No direct script access.');
//2009-10-20 09:05:33
//it is saved by myqee system,please don't edit it.

$config['default'] = array (
    'name' => '默认',
    'benchmark' => true,
    'persistent' => false,
    'connection' => 
    array (
      'type' => 'mysql',
      'user' => 'root',
      'pass' => '123456',
      'host' => 'localhost',
      'port' => '3307',
      'socket' => false,
      'database' => 'myqee_web',
    ),
    'character_set' => 'utf8',
    'table_prefix' => 'mycms_',
    'object' => true,
    'cache' => false,
    'escape' => true,
  );
  
  
$config['bbs'] = array (
    'name' => '论坛',
    'benchmark' => true,
    'persistent' => false,
    'connection' => 
    array (
      'type' => 'mysql',
      'user' => 'root',
      'pass' => '',
      'host' => 'localhost',
      'port' => '3307',
      'socket' => false,
      'database' => 'web_myqee',
    ),
    'character_set' => 'utf8',
    'table_prefix' => 'mycms_',
    'object' => true,
    'cache' => false,
    'escape' => true,
);