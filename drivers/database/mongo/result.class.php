<?php
/**
 * 数据库MySQL返回对象
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Driver
 * @package    Database
 * @subpackage Mongo
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Driver_Database_Driver_Mongo_Result extends Database_Result
{
    protected function release_resource()
    {
        $this->_result = null;
    }

    protected function total_count()
    {
        if ($this->_result instanceof ArrayIterator)
        {
            $count = $this->_result->count();
        }
        elseif ($this->_result)
        {
            $count = $this->_result->count(true);
        }
        else
        {
            $count = count($this->_data);
        }

        if (!$count>0)$count = 0;

        return $count;
    }

    public function seek($offset)
    {
        if (isset($this->_data[$offset]))
        {
            return true;
        }
        elseif ($this->_result instanceof ArrayIterator)
        {
            if ($this->offsetExists($offset) && $this->_result->seek($offset))
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        if ($this->offsetExists($offset))
        {
            if ($this->_internal_row < $this->_current_row)
            {
                $c = $this->_internal_row - $this->_current_row;
                for( $i=0;$i<$c;$i++ )
                {
                    $this->_result->next();
                }
            }
            else
            {
                // 小于当前指针，则回退重新来过，因为目前 MongoCursor 还没有回退的功能
                $this->_result->rewind();
                $c = $this->_current_row - $this->_internal_row;
                for($i=0; $i<$c; $i++)
                {
                    $this->_result->next();
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
        if ($this->_result instanceof ArrayIterator)
        {
            $data = $this->_result->current();
            $this->_result->next();

            return $data;
        }

        $data = $this->_result->getNext();
        if (isset($data['_id']) && is_object($data['_id']) && $data['_id'] instanceof MongoId)
        {
            $data['_id'] = (string)$data['_id'];
        }

        if ( isset($this->_query['select_as']) )foreach ($this->_query['select_as'] as $key=>$value)
        {
            // 对查询出的数据做select as转换
            if (isset($data[$key]))
            {
                $data[$value] = $data[$key];
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * 使查询结果集不动态变化
     *
     * @return $this
     */
    public function snapshot()
    {
        if ($this->_result && !($this->_result instanceof ArrayIterator))
        {
            $this->_result->snapshot();
        }

        return $this;
    }
}