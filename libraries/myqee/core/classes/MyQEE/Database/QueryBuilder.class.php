<?php

/**
 * SQL构造核心类
 *
 * @author     jonwang(jonwang@myqee.com)
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2012 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class MyQEE_Database_QueryBuilder
{

    // Builder ...
    protected $_builder;

    protected $_last_join = null;

    public function __construct()
    {
        # 初始化数据
        $this->reset();
    }

    /**
     * 解析为SQL语句
     *
     * @param   object  Database instance
     * @return  string
     */
    public function compile(Database $db)
    {
        return $db->driver()->compile($this->_builder);
    }

    /**
     * 获取Builder配置
     *
     * 可用 $builder = & $obj->get_builder(); 获取到内存指针
     *
     * @return array
     */
    public function & get_builder()
    {
        return $this->_builder;
    }

    /**
     * Enables or disables selecting only unique columns using "SELECT DISTINCT"
     *
     * @param   boolean  enable or disable distinct columns
     * @return  Database
     */
    public function distinct($value = true)
    {
        $this->_builder['distinct'] = $value;

        return $this;
    }

    /**
     * select(c1,c2,c3,......)
     *
     * @param   mixed  column name or array($column, $alias) or object
     * @param   ...
     * @return  Database
     */
    public function select($columns = NULL)
    {
        if ( func_num_args() > 1 )
        {
            $columns = func_get_args();
        }
        elseif ( is_string($columns) )
        {
            $columns = explode(',', $columns);
        }
        elseif ( ! is_array($columns) )
        {
            $columns = array($columns);
        }

        $this->_builder['select'] = array_merge($this->_builder['select'], $columns);

        return $this;
    }

    /**
     * Choose the columns to select from, using an array.
     *
     * @param   array  list of column names or aliases
     * @return  Database
     */
    public function select_array(array $columns)
    {
        $this->_builder['select'] = array_merge($this->_builder['select'], $columns);

        return $this;
    }

    /**
     * Set the columns that will be inserted.
     *
     * @param   array  column names
     * @return  Database
     */
    public function columns(array $columns)
    {
        $this->_builder['columns'] = $columns;

        return $this;
    }

    /**
     * Adds or overwrites values. Multiple value sets can be added.
     *
     * @param   array   values list
     * @param   ...
     * @return  Database
     */
    public function values(array $values)
    {
        if ( ! is_array($this->_builder['values']) )
        {
            throw new Exception('INSERT INTO ... SELECT statements cannot be combined with INSERT INTO ... VALUES');
        }

        // Get all of the passed values
        $values = func_get_args();

        $this->_builder['values'] = array_merge($this->_builder['values'], $values);

        return $this;
    }

    /**
     * 为update,insert设置数据
     *
     * @param   array   associative (column => value) list
     * @return  Database
     */
    public function set(array $pairs)
    {
        foreach ( $pairs as $column => $value )
        {
            $column = trim($column);

            if ( preg_match('#^(.*)(\+|\-)$#', $column , $m) )
            {
                $column = $m[1];
                $op = $m[2];
            }
            else
            {
                $op = '=';
            }
            $this->_builder['set'][] = array($column, $value , $op);
        }

        return $this;
    }

    /**
     * Set the value of a single column.
     *
     * @param  mixed  table name or array($table, $alias) or object
     * @param  mixed  column value
     * @param  string =|+|-
     * @return Database
     */
    public function value($column, $value , $op = '=')
    {
        $this->_builder['set'][] = array($column, $value , $op);

        return $this;
    }

    /**
     * 数据递增
     *
     * @param string $column
     * @param int $value
     * @return Database
     */
    public function value_increment($column, $value)
    {
        return $this->value($column, abs($value) , $value>0?'+':'-');
    }

    /**
     * 数据递减
     *
     * @param string $column
     * @param int $value
     * @return Database
     */
    public function value_decrement($column, $value)
    {
        return $this->value_increment($column , -$value );
    }

    /**
     * Sets the table to update.
     *
     * @param mixed  table name or array($table, $alias) or object
     * @return Database
     */
    public function table($table)
    {
        $this->_builder['table'] = $table;

        return $this;
    }

    /**
     * from(tableA,tableB,...)
     *
     * @param   mixed  table name or array($table, $alias) or object
     * @param   ...
     * @return  Database
     */
    public function from($tables)
    {
        if ( func_num_args() > 1 )
        {
            $tables = func_get_args();
        }
        elseif ( is_string($tables) )
        {
            $tables = explode(',', $tables);
        }
        elseif ( ! is_array($tables) )
        {
            $tables = array($tables);
        }

        $this->_builder['from'] = array_merge($this->_builder['from'], $tables);

        return $this;
    }

    /**
     * Adds addition tables to "JOIN ...".
     *
     * @param   mixed   column name or array($column, $alias) or object
     * @param   string  join type (LEFT, RIGHT, INNER, etc)
     * @return  Database
     */
    public function join($table, $type = NULL)
    {
        $this->_builder['join'][] = array('table' => $table, 'type' => $type, 'on' => array());
        end($this->_builder['join']);
        $k = key($this->_builder['join']);
        $this->_last_join = & $this->_builder['join'][$k];

        return $this;
    }

    /**
     * Adds "ON ..." conditions for the last created JOIN statement.
     *
     * @param   mixed   column name or array($column, $alias) or object
     * @param   string  logic operator
     * @param   mixed   column name or array($column, $alias) or object
     * @return  Database
     */
    public function on($c1, $c2, $op = '=')
    {
        $this->_last_join['on'][] = array($c1, $op, $c2);

        return $this;
    }

    /**
     * group_by(c1,c2,c3,.....)
     *
     * @param   mixed   column name or array($column, $alias) or object
     * @param   ...
     * @return  Database
     */
    public function group_by($columns)
    {
        $columns = func_get_args();

        $this->_builder['group_by'] = array_merge($this->_builder['group_by'], $columns);

        return $this;
    }

    /**
     * Alias of and_having()
     *
     * @param   mixed   column name or array($column, $alias) or object
     * @param   string  logic operator
     * @param   mixed   column value
     * @return  Database
     */
    public function having($column, $value = NULL, $op = '=')
    {
        return $this->and_having($column, $value, $op);
    }

    /**
     * Creates a new "AND HAVING" condition for the query.
     *
     * @param   mixed   column name or array($column, $alias) or object
     * @param   string  logic operator
     * @param   mixed   column value
     * @return  Database
     */
    public function and_having($column, $value = NULL, $op = '=')
    {
        $this->_builder['having'][] = array('AND' => array($column, $op, $value));

        return $this;
    }

    /**
     * Creates a new "OR HAVING" condition for the query.
     *
     * @param   mixed   column name or array($column, $alias) or object
     * @param   string  logic operator
     * @param   mixed   column value
     * @return  Database
     */
    public function or_having($column, $value = NULL, $op = '=')
    {
        $this->_builder['having'][] = array('OR' => array($column, $op, $value));

        return $this;
    }

    /**
     * Alias of and_having_open()
     *
     * @return  Database
     */
    public function having_open()
    {
        return $this->and_having_open();
    }

    /**
     * Opens a new "AND HAVING (...)" grouping.
     *
     * @return  Database
     */
    public function and_having_open()
    {
        $this->_builder['having'][] = array('AND' => '(');

        return $this;
    }

    /**
     * Opens a new "OR HAVING (...)" grouping.
     *
     * @return  Database
     */
    public function or_having_open()
    {
        $this->_builder['having'][] = array('OR' => '(');

        return $this;
    }

    /**
     * Closes an open "AND HAVING (...)" grouping.
     *
     * @return  Database
     */
    public function having_close()
    {
        return $this->and_having_close();
    }

    /**
     * Closes an open "AND HAVING (...)" grouping.
     *
     * @return  Database
     */
    public function and_having_close()
    {
        $this->_builder['having'][] = array('AND' => ')');

        return $this;
    }

    /**
     * Closes an open "OR HAVING (...)" grouping.
     *
     * @return  Database
     */
    public function or_having_close()
    {
        $this->_builder['having'][] = array('OR' => ')');

        return $this;
    }

    /**
     * Start returning results after "OFFSET ..."
     *
     * @param   integer   starting result number
     * @return  Database
     */
    public function offset($number)
    {
        $this->_builder['offset'] = (int)$number;

        return $this;
    }

    /**
     * 重设数据
     * @return $this
     */
    public function reset()
    {
        $this->_builder['select']     =
        $this->_builder['from']       =
        $this->_builder['join']       =
        $this->_builder['where']      =
        $this->_builder['group_by']   =
        $this->_builder['having']     =
        $this->_builder['parameters'] =
        $this->_builder['set']        =
        $this->_builder['columns']    =
        $this->_builder['values']     =
        $this->_builder['where']      =
        $this->_builder['index']      =
        $this->_builder['order_by']   = array();

        $this->_builder['distinct']   = false;

        $this->_builder['limit']      =
        $this->_builder['offset']     =
        $this->_builder['table']      =
        $this->_builder['last_join']  = null;

        return $this;
    }

    /**
     *
     * @param string $key
     * @param array $value
     * @return Database
     */
    public function in($column, $value, $no_in = false)
    {
        return $this->and_where($column, $value, $no_in ? 'not in' : 'in');
    }

    public function notin($column, $value)
    {
        return $this->and_where($column, $value, 'not in');
    }

    /**
     * Alias of and_where()
     *
     * @param   mixed   column name or array($column, $alias) or object
     * @param   string  logic operator
     * @param   mixed   column value
     * @return  Database
     */
    public function where($column, $value = null, $op = '=')
    {
        if ( is_array($column) )
        {
            foreach ( $column as $c => $value )
            {
                $this->and_where($c, $value, $op);
            }
            return $this;
        }
        return $this->and_where($column, $value, $op);
    }

    /**
     * Creates a new "AND WHERE" condition for the query.
     *
     * @param   mixed   column name or array($column, $alias) or object
     * @param   string  logic operator
     * @param   mixed   column value
     * @return  Database
     */
    public function and_where($column, $value, $op = '=')
    {
        if ( !is_object($column) )
        {
            $column = trim($column);
            if ( preg_match('#^(.*)(>|<|>=|<=)$#', $column , $m) )
            {
                $column = $m[1];
                $op = $m[2];
            }
        }
        $this->_builder['where'][] = array('AND' => array($column, $op, $value));

        return $this;
    }

    /**
     * Creates a new "OR WHERE" condition for the query.
     *
     * @param   mixed   column name or array($column, $alias) or object
     * @param   string  logic operator
     * @param   mixed   column value
     * @return  Database
     */
    public function or_where($column, $value, $op = '=')
    {
        $this->_builder['where'][] = array('OR' => array($column, $op, $value));

        return $this;
    }

    /**
     * Alias of and_where_open()
     *
     * @return  Database
     */
    public function where_open()
    {
        return $this->and_where_open();
    }

    /**
     * Opens a new "AND WHERE (...)" grouping.
     *
     * @return  Database
     */
    public function and_where_open()
    {
        $this->_builder['where'][] = array('AND' => '(');

        return $this;
    }

    /**
     * Opens a new "OR WHERE (...)" grouping.
     *
     * @return  Database
     */
    public function or_where_open()
    {
        $this->_builder['where'][] = array('OR' => '(');

        return $this;
    }

    /**
     * Closes an open "AND WHERE (...)" grouping.
     *
     * @return  Database
     */
    public function where_close()
    {
        return $this->and_where_close();
    }

    /**
     * Closes an open "AND WHERE (...)" grouping.
     *
     * @return  Database
     */
    public function and_where_close()
    {
        $this->_builder['where'][] = array('AND' => ')');

        return $this;
    }

    /**
     * Closes an open "OR WHERE (...)" grouping.
     *
     * @return  Database
     */
    public function or_where_close()
    {
        $this->_builder['where'][] = array('OR' => ')');

        return $this;
    }

    /**
     * Applies sorting with "ORDER BY ..."
     *
     * @param   mixed   column name or array($column, $alias) or object
     * @param   string  direction of sorting
     * @return  Database
     */
    public function order_by($column, $direction = 'ASC')
    {
        $this->_builder['order_by'][] = array($column, $direction);

        return $this;
    }

    /**
     * Return up to "LIMIT ..." results
     *
     * @param   integer  maximum results to return
     * @param   integer  maximum results from offset
     * @return  Database
     */
    public function limit($number, $offset = null)
    {
        $this->_builder['limit'] = (int)$number;

        if ( null !== $offset )
        {
            $this->offset($offset);
        }

        return $this;
    }

    /**
     * 返回 "LIKE ..."
     *
     * @param string $column
     * @param string $value
     * @return  Database
     */
    public function like($column, $value = null)
    {
        return $this->where($column, $value, 'like');
    }

    /**
     * 返回 "OR LIKE ..."
     *
     * @param string $column
     * @param string $value
     * @return  Database
     */
    public function or_like($column, $value = null)
    {
        return $this->or_where($column, $value, 'like');
    }

    /**
     * 返回 "$column MOD $mod = $value"
     *
     * @param string $column
     * @param int $mod_dig
     * @param int $value
     * @return  Database
     */
    public function mod($column, $mod_dig , $value)
    {
        return $this->and_where($column, array($mod_dig,$value) , 'mod' );
    }

    /**
     * 返回 "OR $column MOD $mod = $value"
     *
     * @param string $column
     * @param int $mod_dig
     * @param int $value
     * @return  Database
     */
    public function or_mod($column, $mod_dig , $value , $op = '=')
    {
        return $this->or_where($column, array($mod_dig,$value,$op) , 'mod' );
    }

    /**
     * 使用指定索引
     *
     * @param string
     * @return  Database
     */
    public function use_index($index)
    {
        $this->_builder['index'][] = array($index,'use');

        return $this;
    }

    /**
     * 强制使用指定索引
     *
     * @param string
     * @return  Database
     */
    public function force_index($index)
    {
        $this->_builder['index'][] = array($index,'force');

        return $this;
    }

    /**
     * 或略指定索引
     *
     * @param string
     * @return  Database
     */
    public function ignore_index($index)
    {
        $this->_builder['index'][] = array($index,'ignore');

        return $this;
    }

    /**
     * 创建一个不会被过滤处理的字符串
     *
     * @param string expression
     * @return Database_Expression
     */
    public static function expr_value($string)
    {
        return new Database_Expression($string);
    }

}