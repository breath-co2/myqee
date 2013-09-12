<?php

/**
 * 数据库事务核心类
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   MyQEE
 * @package    Module
 * @subpackage Database
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
abstract class Module_Database_Transaction
{

    /**
     * 唯一ID
     * @var $id string
     */
    protected $id;

    /**
     * 数据库驱动
     * @var Database_Driver_MySQLI
     */
    protected $db_driver;

    public function __construct($db_driver)
    {
        $this->db_driver = $db_driver;
    }

    /**
     * 开启事务
     * @return boolean 是否开启成功
     * @throws Exception
     */
    abstract public function start();

    /**
     * 提交事务，支持子事务
     *
     * @return Boolean true:成功；false:失败
     */
    abstract public function commit();

    /**
     * 撤消事务，支持子事务
     *
     * @return Bollean true:成功；false:失败
     */
    abstract public function rollback();

    /**
     * 是否还在事务中
     * @return boolean true=是，false=否
     */
    abstract public function is_root();

    /**
     * 事务查询
     * @param string $sql
     */
    protected function _query($sql)
    {
        try
        {
            if ( $this->db_driver->query($sql, null, true) )
            {
                $status = true;
            }
            else
            {
                $status = false;
            }
        }
        catch ( Exception $e )
        {
            $status = false;
        }
        return $status;
    }
}