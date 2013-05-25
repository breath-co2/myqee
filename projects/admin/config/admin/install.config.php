<?php
/**
 * 系统安装标题
 * @var string
 */
$config['title'] = '通用后台安装向导';

/**
 * 头部右侧说明
 * @var string
 */
$config['desc'] = 'MyQEE 简体中文 UTF8 版 '.Core::VERSION;

/**
 * 安装步骤配置
 * @var array
 */
$config['step'] = array(
    'step_1' => array(
        'title' => '检查安装环境',
        'desc'  => '环境以及文件目录权限检查',
    ),
    'step_2' => array(
        'title' => '配置数据库连接',
        'desc'  => '根据服务器情况设置数据库配置',
    ),
    'step_3' => array(
        'title' => '创建数据库',
        'desc'  => '正在执行数据库安装',
    ),
    'step_4' => array(
        'title' => '安装',
        'desc'  => '正在执行SQL语句',
    ),
);