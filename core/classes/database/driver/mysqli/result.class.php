<?php

/**
 * 数据库MySQLI返回类
 *
 * @author      jonwang(jonwang@myqee.com)
 * @category    MyQEE
 * @package     System
 * @subpackage  Core
 * @copyright   Copyright (c) 2008-2011 myqee.com
 * @license     http://www.myqee.com/license.html
 */
class Core_Database_Driver_MySQLI_Result extends Database_Result
{
    public function __destruct()
    {
        if ( is_resource($this->_result) )
        {
            mysqli_free_result($this->_result);
        }
    }

    protected function total_count()
    {
        $count = @mysqli_num_rows($this->_result);
        if (!$count>0)$count = 0;

        return $count;
    }

    public function seek($offset)
    {
        if ( $this->offsetExists($offset) && mysqli_data_seek($this->_result, $offset) )
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
        return mysqli_fetch_assoc($this->_result);
    }
}