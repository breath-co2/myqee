#!/usr/bin/env php
<?php
$args = $_SERVER['argv'];
array_shift($args);

if (count($args)<3)
{
    echo '参数错误';
    exit;
}

define('DIR_MANUAL', realpath(dirname(__FILE__).'/../../').'/');

list($ns, $type, $fun) = $args;

$auto_run = false;
include(dirname(__FILE__).'/../../../index.php');

require_once(dirname(__FILE__).'/docs.php');
require_once(dirname(__FILE__).'/docs_method.php');
require_once(dirname(__FILE__).'/docs_method_param.php');
require_once(dirname(__FILE__).'/markdown.php');
require_once(dirname(__FILE__).'/markdown_extra.php');

function get_function_info()
{
    $class_name  = 'Database';
    $method_name = 'query';
    $method      = new _Docs_Method($class_name, $method_name);

    include(DIR_MANUAL.'tpl/api_method.html');
    $html = ob_get_clean();
}


get_function_info();
