<?php defined('MYQEEPATH') or die('No direct script access.');
$config['72'] = array (
    'name' => '内网',
    'benchmark' => true,
    'persistent' => false,
    'connection' => 
    array (
      'type' => 'mysql',
      'user' => 'root',
      'pass' => 'verycdoffice',
      'host' => '192.168.1.72',
      'port' => '3306',
      'socket' => false,
      'database' => 'VeryCD',
    ),
    'character_set' => 'utf8',
    'table_prefix' => 'ipb_',
    'object' => true,
    'cache' => false,
    'escape' => true,
);

$config['mysql55'] = array (
    'name' => '5.5',
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
      'database' => 'mytest',
    ),
    'character_set' => 'utf8',
    'table_prefix' => '',
    'object' => true,
    'cache' => false,
    'escape' => true,
);