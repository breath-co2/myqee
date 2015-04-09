<?php

/**
 * ORM 数据库核心类
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Module
 * @package    ORM
 * @copyright  Copyright (c) 2008-2016 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Module_OOP_ORM_Finder_DB extends OOP_ORM
{

    /**
     * 数据库配置名
     * @var string
     */
    protected $database = Database::DEFAULT_CONFIG_NAME;

    /**
     * 表名称
     * @var string
     */
    protected $tablename;

    /**
     * 当前数据库对象
     *
     * @var Database
     */
    protected $_database_instance = null;

    function __construct($table_name = null, $database = null)
    {
        if (null !== $table_name)
        {
            $this->tablename = $table_name;
        }

        if (null !== $database)
        {
            $this->database = $database;
        }

        if (null === $this->tablename)
        {
            throw new Exception(__('ORM :orm tablename is not declared.', array(':orm' => get_class($this))));
        }

        parent::__construct();
    }

    /**
     * 获取数据
     *
     * @param string $sql 指定SQL语句，不传则使用QueryBuilder构造
     * @param boolean $use_master 查询是否用主数据库
     * @return OOP_ORM_Result
     */
    public function find($sql = null, $use_master = null)
    {
        if ($sql)
        {
            $rs        = $this->driver()->query($sql, false, $use_master);
            $cache_obj = null;
        }
        else
        {
            $builder = & $this->driver()->get_builder();
            if (!$builder['from'])
            {
                $this->driver()->from($this->tablename);
            }
            if ($this->_auto_where)
            {
                $this->driver()->where($this->_auto_where);
            }
            $cache_obj = null;

            # 优化主键数据查询
            $not_need_find = false;//$this->_get_id_field_cache_data($builder, $cache_obj);
            unset($builder);

            if (false === $not_need_find)
            {
                $rs = $this->driver()->get(false, $use_master);

                if (null !== $cache_obj)
                {
                    $rs += $cache_obj;
                }
            }
            else
            {
                $this->driver()->reset();
                $rs = $cache_obj;
            }

        }

        $opt = array
        (
            'is_field_key' => true,
            'sql'          => $this->last_query(),
            'database'     => $this->database(),
        );

        $obj = $this->create_group_data($rs, $opt);
        if ($cache_obj)
        {
//            $obj->megre($cache_obj);
        }

        return $obj;
    }

    /**
     * 插入数据
     * @param string $table
     * @param array $value
     * @param Database_Result
     * @return array(插入ID,作用行数)
     */
    public function insert($value = null)
    {
        return $this->driver()->insert($this->tablename(), $value);
    }

    /**
     * 更新数据
     * @param string $table
     * @param array $value
     * @param array $where
     * @return int 作用的行数
     */
    public function update($value = null, $where = null)
    {
        return $this->driver()->update($this->tablename(), $value, $where);
    }

    /**
     * 删除数据
     * @param string $table
     * @param array $where
     * @return int 作用的行数
     */
    public function delete($where = null)
    {
        return $this->driver()->delete($this->tablename(), $where);
    }

    /**
     * 获取数据库名称
     *
     * @return string
     */
    public function database()
    {
        return $this->database;
    }

    /**
     * 获取表名称
     *
     * @return string
     */
    public function tablename()
    {
        return $this->tablename;
    }

//    /**
//     * 根据$builder条件解析获取已缓存在PHP的数据
//     * @param array $builder
//     * @param array $id_cache_data
//     * @return boolean 是否需要再次读取
//     */
//    protected function _get_id_field_cache_data(& $builder, & $id_cache_data)
//    {
//        $not_need_find  = false;
//        # $builder条件中只有1个where，则尝试在数据ID缓存中获取
//        if (count($builder['where'])==1 && ($builder['where'][0]['AND'][1]=='=' || $builder['where'][0]['AND'][1]=='in'))
//        {
//            #
//            foreach ($builder['where'] as $k=>$v)
//            {
//                if ($v && $k!='where')
//                {
//                    return $not_need_find;
//                }
//            }
//
//            $field_name = $builder['where'][0]['AND'][0];
//
//            # 主键字段
//            if ($field_name==$this->pk_key_name())
//            {
//                $value = $builder['where'][0]['AND'][2];
////                $id_data = OOP_ORM_Data::id_field_cache_data($this->get_orm_name('Data'), $value);
//                if ($id_data)
//                {
//                    # 获取到数据
//                    if ($builder['where'][0]['AND'][1]=='in')
//                    {
//                        $new_value = array();
//                        foreach ($value as $v)
//                        {
//                            if (!isset($id_data[$v]))
//                            {
//                                $new_value[] = $v;
//                            }
//                        }
//
//                        if ($new_value)
//                        {
//                            # 更新where条件
//                            $builder['where'][0]['AND'][2] = $new_value;
//                        }
//                        else
//                        {
//                            $not_need_find  = true;
//                        }
//
//                        $id_cache_data = $id_data;
//                    }
//                    else
//                    {
//                        $not_need_find  = true;
//                        $id_cache_data = array($id_data);
//                    }
//                }
//            }
//        }
//
//        return $not_need_find;
//    }

    /**
     * 返回数据库对象
     *
     * @return Database
     */
    public function driver()
    {
        if (null === $this->_database_instance)
        {
            $this->_database_instance = new Database($this->database);
        }

        return $this->_database_instance;
    }

    /**
     * 返回数据库对象
     *
     * 兼容Model的写法
     *
     * @return Database
     */
    public function db()
    {
        return $this->driver();
    }

    /**
     * where条件
     *
     * @param   mixed  $column column name or array($column, $alias) or object
     * @param   string $value logic operator
     * @param   mixed  $op column value
     * @return  $this
     */
    public function where($column, $value = null, $op = '=')
    {
        $this->driver()->where($column, $value, $op);
        return $this;
    }

    /**
     * Creates a new "OR WHERE" condition for the query.
     *
     * @param  mixed  $column column name or array($column, $alias) or object
     * @param  string $value logic operator
     * @param  mixed  $op column value
     * @return $this
     */
    public function or_where($column, $value, $op = '=')
    {
        $this->driver()->or_where($column, $value, $op);
        return $this;
    }

    /**
     * select(c1, c2, c3,......)
     *
     * @param  mixed $columns column name or array($column, $alias) or object
     * @param  ...
     * @return $this
     */
    public function select($columns)
    {
        call_user_func_array(array($this->driver(), 'select'), func_get_args());
        return $this;
    }

    /**
     * Applies sorting with "ORDER BY ..."
     *
     * @param   mixed  $column column name or array($column, $alias) or object
     * @param   string $direction direction of sorting
     * @return  $this
     */
    public function order_by($column, $direction = 'ASC')
    {
        $this->driver()->order_by($column, $direction);
        return $this;
    }

    /**
     * Start returning results after "OFFSET ..."
     *
     * @param   integer $number starting result number
     * @return  $this
     */
    public function offset($number)
    {
        $this->driver()->offset($number);
        return $this;
    }

    /**
     * Return up to "LIMIT ..." results
     *
     * @param   integer $number maximum results to return
     * @return  $this
     */
    public function limit($number)
    {
        $this->driver()->limit($number);
        return $this;
    }

    /**
     * 设置 in
     *
     * @param string $key
     * @param array $value
     * @return $this
     */
    public function in($column, $value, $no_in = false)
    {
        $this->driver()->in($column, $value, $no_in);
        return $this;
    }

    /**
     * 设置 not in
     *
     * @param $column
     * @param $value
     * @return $this
     */
    public function notin($column, $value)
    {
        $this->driver()->notin($column, $value);
        return $this;
    }

    /**
     * group_by(c1,c2,c3,.....)
     *
     * @param   mixed $columns  column name or array($column, $alias) or object
     * @param   ...
     * @return  $this
     */
    public function group_by($columns)
    {
        call_user_func_array(array($this->driver(), 'group_by'), func_get_args());
        return $this;
    }

    /**
     * 构成生成 GROUP_CONCAT() 的语句
     *
     * @param $column
     * @param string $order_by
     * @param string $separator
     * @param bool $distinct
     * @return $this
     */
    public function group_concat($column, $order_by = null, $separator = null, $distinct = false)
    {
        $this->driver()->group_concat($column, $order_by, $separator, $distinct);
        return $this;
    }

    /**
     * Alias of and_having()
     *
     * @param  mixed $column column name or array($column, $alias) or object
     * @param  string $value logic operator
     * @param  mixed $op column value
     * @return $this
     */
    public function having($column, $value = null, $op = '=')
    {
        $this->driver()->having($column, $value, $op);
        return $this;
    }

    /**
     * Adds addition tables to "JOIN ...".
     *
     * @param  mixed  $table column name or array($column, $alias) or object
     * @param  string $type join type (LEFT, RIGHT, INNER, etc)
     * @return $this
     */
    public function join($table, $type = null)
    {
        $this->driver()->join($table, $type);
        return $this;
    }

    /**
     * Adds "ON ..." conditions for the last created JOIN statement.
     *
     * @param  mixed  $c1 column name or array($column, $alias) or object
     * @param  string $c2 logic operator
     * @param  mixed  $op column name or array($column, $alias) or object
     * @return $this
     */
    public function on($c1, $c2, $op = '=')
    {
        $this->on($c1, $c2, $op);
        return $this;
    }

    public function __call($method, $params)
    {
        if (method_exists($this->driver(), $method))
        {
            call_user_func_array(array($this->driver(), $method), $params);

            return $this;
        }

        throw new Exception('class '. get_class($this) .' not found method : '. $method);
    }
}

