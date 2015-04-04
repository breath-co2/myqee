<?php

/**
 * 数据库MySQLI返回类
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Drive
 * @package    Database
 * @subpackage MySQLI
 * @copyright  Copyright (c) 2008-2016 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Drive_Database_Drive_PDO_Result extends Database_Result
{
    protected function release_resource()
    {
        $this->_result = null;
    }

    protected function total_count()
    {
        if ($this->_total_rows)
        {
            return $this->_total_rows;
        }
        elseif ($this->_result)
        {
            return $this->_total_rows = $this->_result->rowCount();
        }
        else
        {
            return count($this->_data);
        }
    }

    public function seek($offset)
    {
        if (isset($this->_data[$offset]))
        {
            return true;
        }
        elseif ($this->offsetExists($offset) && $this->_result)
        {
            $num = $offset - $this->_current_row;

            if ($num > 1)
            {
                if ($this->_cursor_mode)
                {
                    throw new Exception('current pdo database drive not supper cursor mode.');
                }

                for ($i = $this->_current_row; $i < $num; $i ++)
                {

                    $this->current();

                    $this->_current_row ++;
                }
            }

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
        return $this->_result->fetch(PDO::FETCH_ASSOC);
    }
}