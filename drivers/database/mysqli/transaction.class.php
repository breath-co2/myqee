<?php

/**
 * MySQLI事务
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Driver
 * @package    Database
 * @subpackage MySQLI
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
abstract class Driver_Database_Driver_MySQLI_Transaction extends Database_Transaction
{
    /**
     * 当前连接ID
     * @var string
     */
    protected $_connection_id;

    protected static $transactions = array();

    /**
     * 开启事务
     * @return Database_Driver_MySQLI_Transaction
     */
    public function start()
    {
        if ($this->id)
        {
            throw new Exception('transaction has started');
        }
        # 推动连接主数据库
        $this->db_driver->connect(true);
        # 获取连接ID
        $this->_connection_id = $this->db_driver->connection_id();
        # 获取唯一ID
        $this->id = uniqid('TaId_' . rand());

        if (isset(Database_Driver_MySQLI_Transaction::$transactions[$this->_connection_id]))
        {
            # 已存在事务，则该事务为子事务
            if ($this->_set_save_point())
            {
                //保存事务点
                Database_Driver_MySQLI_Transaction::$transactions[$this->_connection_id][$this->id] = true;
            }
            else
            {
                $this->id = null;
                # 开启事务失败。
                throw new Exception('start sub transaction error');
            }
        }
        else
        {
            # 开启新事务
            $this->_query('SET AUTOCOMMIT=0;');
            if (true === $this->_query('START TRANSACTION;'))
            {
                # 如果没有建立到当前主服务器的连接，该操作会隐式的建立
                Database_Driver_MySQLI_Transaction::$transactions[$this->_connection_id] = array($this->id => true);
            }
            else
            {
                $this->id = null;
                # 开启事务失败。
                throw new Exception('start transaction error');
            }
        }

        return true;
    }

    /**
     * 提交事务，支持子事务
     *
     * @return Boolean true:成功
     * @throws Exception
     */
    public function commit()
    {
        if (!$this->id || ! $this->_haveid()) return false;

        if ($this->is_root())
        {
            # 父事务
            while (count(Database_Driver_MySQLI_Transaction::$transactions[$this->_connection_id]) > 1)
            {
                # 还有没有提交的子事务
                end(Database_Driver_MySQLI_Transaction::$transactions[$this->_connection_id]);
                $subid = key(Database_Driver_MySQLI_Transaction::$transactions[$this->_connection_id]);
                if (!$this->_release_save_point($subid))
                {
                    throw new Exception('commit error');
                }
            }
            $status = $this->_query('COMMIT;');
            $this->_query('SET AUTOCOMMIT=1;');
            if ($status) unset(Database_Driver_MySQLI_Transaction::$transactions[$this->_connection_id]);
        }
        else
        {
            # 子事务
            $status = $this->_release_save_point($this->id);
        }
        if ($status)
        {
            $this->id = null;
            return true;
        }
        else
        {
            throw new Exception('not commit transaction');
        }
    }

    /**
     * 撤消事务，支持子事务
     *
     * @return Bollean true:成功；false:失败
     */
    public function rollback()
    {
        if (!$this->id) return false;
        if (!$this->_haveid()) return false;

        if ($this->is_root())
        {
            //父事务
            $status = $this->_query('ROLLBACK;');
            $this->_query('SET AUTOCOMMIT=1;');
            if ($status)
            {
                unset(Database_Driver_MySQLI_Transaction::$transactions[$this->_connection_id]);
            }
        }
        else
        {
            //子事务
            $status = $this->_query("ROLLBACK TO SAVEPOINT {$this->id};");
            $this->_release_save_point($this->id);
        }
        if ($status)
        {
            $this->id = null;
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 是否父事务
     */
    public function is_root()
    {
        if (!$this->id) return false;
        return isset(Database_Driver_MySQLI_Transaction::$transactions[$this->_connection_id]) && key(Database_Driver_MySQLI_Transaction::$transactions[$this->_connection_id]) == $this->id;
    }

    /**
     * 设置子事务的保存点，用于支持子事务的回滚
     *
     * @return Boolean  true:成功；false:失败
     */
    protected function _set_save_point()
    {
        if (!$this->is_root())
        {
            //只有子事务才需要保存点
            if ( true === $this->_query("SAVEPOINT {$this->id};") )
            {
                return true;
            }
        }

        return false;
    }

    /**
     * 释放事务保存点
     * @return Boolean  true:成功；false:失败
     */
    protected function _release_save_point($id)
    {
        if (!$this->is_root())
        {
            if (true === $this->_query("RELEASE SAVEPOINT {$id};"))
            {
                unset(Database_Driver_MySQLI_Transaction::$transactions[$this->_connection_id][$id]);
                return true;
            }
        }
        return false;
    }

    /**
     * 在事务列表中是否存在
     * @return boolean
     */
    protected function _haveid()
    {
        return isset(Database_Driver_MySQLI_Transaction::$transactions[$this->_connection_id]) && isset(Database_Driver_MySQLI_Transaction::$transactions[$this->_connection_id][$this->id]);
    }

}