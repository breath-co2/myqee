<?php

/**
 * MyQEE ORM 核心类
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Module
 * @package    ORM
 * @copyright  Copyright (c) 2008-2015 myqee.com
 * @license    http://www.myqee.com/license.html
 */
abstract class Module_OOP
{
    /**
     * 获取一个ORM对象
     *
     * 如果要获取 `ORM_Test_Finder` 对象，只需要使用 `OOP::ORM('Test')` 即可
     * 如果 `ORM_Test_Finder` 对象不存在，系统将返回默认的 `OOP_ORM_Finder_DB` Finder对象，并且数据库名称为 `Test`
     *
     * @param string $orm_name ORM名称
     * @param string $database 数据库配置，动态设置数据库配置
     * @return OOP_ORM_Finder_DB
     */
    public static function ORM($orm_name, $database = null)
    {
        if (preg_match('#^http(s)?://#', $orm_name))
        {
            # REST
            return new OOP_ORM_Finder_REST($orm_name);
        }
        else
        {
            $finder_class_name = 'ORM_'. $orm_name .'_Finder';

            if (class_exists($finder_class_name, true))
            {
                return new $finder_class_name();
            }
            else
            {
                return new OOP_ORM_Finder_DB($orm_name, $database ? $database : Database::DEFAULT_CONFIG_NAME);
            }
        }
    }
}