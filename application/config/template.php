<?php defined('MYQEEPATH') or die('No direct script access.');
//2009-11-05 09:10:12
//it is saved by myqee system,please don't edit it.

$config['group'] = array (
  'default' => 
  array (
    'name' => '默认模板组',
    'engine' => '',
    'suffix' => '.php',
    'allsuffix' => '.php',
  ),
  'smarty' => 
  array (
    'name' => 'Smatty测试',
    'engine' => 'smarty',
    'suffix' => '.tpl',
    'allsuffix' => '.tpl|.htm|.conf|.txt',
  ),
  'test2' => 
  array (
    'name' => '测试系统模板',
    'engine' => 'default',
    'suffix' => '.tpl',
    'allsuffix' => '.html|.txt|.tpl',
  ),
);
$config['default'] = 'default';
