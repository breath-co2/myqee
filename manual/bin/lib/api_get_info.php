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

# 大目录类型
$type       = strtolower($args[0]);
# 类名称
$class_name = strtolower($args[1]);
# 文件类型，主要用于控制器读取处理是否shell还是system还是admin
$dir_type   = $args[2];
# 文件名
$file       = isset($args[3])?$args[3]:null;



if ($type=='core' || $type=='module' || $type=='drive')
{
    # 不用特别处理，已经加载
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

define('_DOC_DIR_TYPE', $dir_type);

if ($dir_type=='controller_system')
{
    define('IS_SYSTEM_MODE', 1);
    define('IS_CLI', false);
}
elseif ($dir_type=='controller_admin')
{
    define('IS_SYSTEM_ADMIN', true);
    define('IS_CLI', false);
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

# 没有项目的话，移除项目类库目录
if (!$project)
{
    Core::$include_path['project'] = array();
}


# 包含文档类库
require_once($dirname.'/docs.php');
require_once($dirname.'/docs_class.php');
require_once($dirname.'/docs_method.php');
require_once($dirname.'/docs_method_param.php');
require_once($dirname.'/docs_roperty.php');


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




function get_function_info($class_name, $dir_type)
{
    if ($dir_type=='orm')
    {
        $arr = array();
        foreach (array('finder', 'data', 'result') as $type)
        {
            $orm_class_name = $class_name.'_'.$type;
            if (class_exists($orm_class_name))
            {
                $class = new _Docs_Class($orm_class_name);
                $arr[$type] = $class->getArrayCopy();
            }
        }
    }
    else
    {
        $class = new _Docs_Class($class_name);

        $arr = $class->getArrayCopy();
    }

    echo "ok\n";
    echo serialize($arr);
}


get_function_info($class_name, $dir_type);
