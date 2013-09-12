<?php

/**
 * 数据库SQLite返回对象
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Driver
 * @package    Database
 * @subpackage SQLite
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Driver_Database_Driver_SQLite_Result extends Database_Result
{
    public function __destruct()
    {

    }

    protected function total_count()
    {
        $count = @sqlite_num_rows($this->_result);
        if (!$count>0)$count = 0;

        return $count;
    }

    public function seek($offset)
    {
        if ( $this->offsetExists($offset) && sqlite_seek($this->_result, $offset) )
        {
            $this->_current_row = $this->_internal_row = $offset;

            return true;
        }
        else
        {
            return false;
        }
    }

    protected function fetch_assoc()
    {
        return sqlite_fetch_array($this->_result, SQLITE_ASSOC);
    }
}