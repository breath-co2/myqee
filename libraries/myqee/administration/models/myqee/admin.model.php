<?php
if (!defined('MODEL_ADMIN_DATABASE'))
{
    /**
     * 定义后台默认数据库配置
     *
     * @var string
     */
    define('MODEL_ADMIN_DATABASE', Core::config('admin/core.database') );
}

/**
 * 后台管理基础模块
 *
 * @author jonwang
 *
 */
class Model_MyQEE_Admin extends Model
{
    /**
     * 数据库配置名
     *
     * @var string
     */
    const DATABASE = MODEL_ADMIN_DATABASE;

    /**
     * 数据库配置名
     *
     * @var string
     */
    protected $database = MODEL_ADMIN_DATABASE;

}