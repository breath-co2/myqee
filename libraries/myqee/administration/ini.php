<?php
if ( !defined('MODEL_ADMIN_DATABASE') )
{
    /**
     * 定义后台默认数据库配置
     *
     * @var string
     */
    define('MODEL_ADMIN_DATABASE', Core::config('admin/core.database') );
}