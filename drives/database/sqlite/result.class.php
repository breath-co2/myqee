<?php

/**
 * 数据库SQLite返回对象
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Drive
 * @package    Database
 * @subpackage SQLite
 * @copyright  Copyright (c) 2008-2016 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Drive_Database_Drive_SQLite_Result extends Database_Result
{
    protected function release_resource()
    {
        $this->_result = null;
    }

    protected function total_count()
    {
        if ($this->_result)
        {
            $count = @sqlite_num_rows($this->_result);
            if (!$count>0)$count = 0;
        }
        else
        {
            $count = count($this->_data);
        }

        return $count;
    }

    public function seek($offset)
    {
        if (isset($this->_data[$offset]))
        {
            return true;
        }
        elseif ($this->offsetExists($offset) && $this->_result && sqlite_seek($this->_result, $offset))
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