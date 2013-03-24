#!/usr/bin/env php
<?php
# 获取知道类库的相关API信息，以serialize方式直接页面输出

$args = $_SERVER['argv'];
array_shift($args);

if (count($args)<2)
{
    echo "缺少参数\n";
    exit;
}

$dirname = dirname(__FILE__);
# 文档目录
define('DIR_MANUAL', realpath($dirname.'/../../').'/');

$type       = strtolower($args[0]);
$class_name = strtolower($args[1]);
$file       = $args[2];
if ($type=='core')
{
    # 核心类库，不用特别处理，已经加载
}
else if (substr($type, 0, 8)=='project:')
{
    # 设置项目
    $project = substr($type, 8);
}
else if (substr($type, 0, 8)=='library:')
{
    # 设置项目
    $library = substr($type, 8);
}
else
{
    echo "参数错误\n";
    exit;
}

# 载入MyQEE程序，不自动运行，只加载autoload
$auto_run = false;
include($dirname.'/../../../index.php');

Core::$path_info = '/';

if ($library)
{
    # 载入类库
    Core::import_library('com.'.$library);
}


# 包含文档类库
require_once($dirname.'/docs.php');
require_once($dirname.'/docs_class.php');
require_once($dirname.'/docs_method.php');
require_once($dirname.'/docs_method_param.php');
require_once($dirname.'/docs_roperty.php');
require_once($dirname.'/markdown.php');


if ($file)
{
    if (is_file($file))
    {
        require_once($file);
    }
    else
    {
        echo "指定的文件".$file."不存在\n";
        exit;
    }
}




function get_function_info($class_name)
{
    $class = new _Docs_Class($class_name);

    $str = $class->getArrayCopy();

    echo "ok\n";
    echo serialize($str);
}


get_function_info($class_name);
