<?php

/**
 * ORM 数据库核心类
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Module
 * @package    ORM
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Module_OOP_ORM_Finder_DB extends OOP_ORM
{

    /**
     * 数据库配置名
     * @var string
     */
    protected $database = 'default';

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

    function __construct()
    {
        if ( null === $this->tablename )
        {
            throw new Exception(__('orm tablename is not declared.'));
        }
        parent::__construct();
    }

    /**
     * 获取数据库名称
     */
    public function database()
    {
        return $this->database;
    }

    /**
     * 获取表名称
     */
    public function tablename()
    {
        return $this->tablename;
    }

    /**
     * 获取数据
     *
     * @param string $sql 指定SQL语句，不传则使用QueryBulider构造
     * @param boolean $use_master 查询是否用主数据库
     * @return OOP_ORM_Result
     */
    public function find($sql = null , $use_master = null)
    {
        if ( $sql )
        {
            $data = $this->driver()->query($sql , false , $use_master)->as_array();
        }
        else
        {
            $bulider = & $this->driver()->get_builder();
            if ( !$bulider['from'] )
            {
                $this->driver()->from($this->tablename);
            }
            if ( $this->_auto_where )
            {
                $this->driver()->where($this->_auto_where);
            }
            $id_cache_data = null;
            # 优化主键数据查询
            $not_need_find = $this->_get_id_field_cache_data($bulider , $id_cache_data);
            unset($bulider);

            if (false===$not_need_find)
            {
                $data = $this->driver()->get(false , $use_master)->as_array();
                if ( null!==$id_cache_data )
                {
                    $data += $id_cache_data;
                }
            }
            else
            {
                $this->driver()->reset();
                $data = $id_cache_data;
            }

        }

        # 最后查询SQL
        $this->last_query = $this->driver()->last_query();

        $obj = $this->create_group_data($data, true);

        return $obj;
    }

    /**
     * 根据$bulider条件解析获取已缓存在PHP的数据
     * @param array $bulider
     * @param array $id_cache_data
     * @return boolean 是否需要再次读取
     */
    protected function _get_id_field_cache_data( &$bulider , &$id_cache_data )
    {
        $not_need_find  = false;
        # $bulider条件中只有1个where，则尝试在数据ID缓存中获取
        if ( count($bulider['where'])==1 && ($bulider['where'][0]['AND'][1]=='=' || $bulider['where'][0]['AND'][1]=='in') )
        {
            #
            foreach ($bulider['where'] as $k=>$v)
            {
                if ( $v && $k!='where' )
                {
                    return $not_need_find;
                }
            }
            $field_name = $bulider['where'][0]['AND'][0];
            if ( $field_name==$this->id_field_name() )
            {
                $value = $bulider['where'][0]['AND'][2];
                $id_data = OOP_ORM_Data::id_field_cache_data($this->get_orm_name('Data'), $value);
                if ($id_data)
                {
                    # 获取到数据
                    if ( $bulider['where'][0]['AND'][1]=='in' )
                    {
                        $new_value = array();
                        foreach ($value as $v)
                        {
                            if ( !isset($id_data[$v]) )
                            {
                                $new_value[] = $v;
                            }
                        }
                        if ( $new_value )
                        {
                            # 更新where条件
                            $bulider['where'][0]['AND'][2] = $new_value;
                        }
                        else
                        {
                            $not_need_find  = true;
                        }
                        $id_cache_data = $id_data;
                    }
                    else
                    {
                        $not_need_find  = true;
                        $id_cache_data = array($id_data);
                    }
                }
            }
        }

        return $not_need_find;
    }

    /**
     * 返回数据库对象
     *
     * @return Database
     */
    public function driver()
    {
        if (null===$this->_database_instance)
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
     *
     * @param   mixed   column name or array($column, $alias) or object
     * @param   string  logic operator
     * @param   mixed   column value
     * @return  OOP_ORM_Finder_DB
     */
    public function where($column, $value = null, $op = '=')
    {
        $this->driver()->where($column, $value, $op);
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
        $this->driver()->order_by($column, $direction);
        return $this;
    }

    /**
     * Start returning results after "OFFSET ..."
     *
     * @param   integer   starting result number
     * @return  OOP_ORM_Finder_DB
     */
    public function offset($number)
    {
        $this->driver()->offset($number);
        return $this;
    }

    /**
     * Return up to "LIMIT ..." results
     *
     * @param   integer  maximum results to return
     * @return  OOP_ORM_Finder_DB
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
     * @return Database
     */
    public function in($column, $value, $no_in = false)
    {
        $this->driver()->in($column, $value, $no_in);
        return $this;
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
}

