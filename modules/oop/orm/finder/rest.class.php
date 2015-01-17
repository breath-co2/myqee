<?php

/**
 * MyQEE ORM REST核心类
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Module
 * @package    ORM
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Module_OOP_ORM_Finder_REST extends OOP_ORM
{
    /**
     * API接口地址
     * @var string
     */
    protected $api_url;

    /**
     * 接口的method
     *
     * GET|POST|DELETE|PUT 等
     *
     * @var string
     */
    protected $method = 'GET';

    /**
     * 请求接口的参数
     *
     * @var array
     */
    protected $arguments = array();

    /**
     * POST,PUT提交的参数
     *
     * @var array
     */
    protected $post_data = array();

    /**
     * @var HttpClient
     */
    protected $_driver = null;

    /**
     * 当设置method后记录默认的method，用于在请求完毕后重置method
     *
     * @var string
     */
    protected $_default_method = null;

    function __construct($api_url = null)
    {
        if (null!==$api_url)
        {
            $this->api_url = $api_url;
        }

        if (null === $this->api_url)
        {
            throw new Exception(__('orm api_url is not declared.'));
        }

        parent::__construct();
    }

    /**
     * 设置，获取REST的类型
     *
     * @param string $method GET|POST|DELETE|PUT 等，不传则返回当前method
     *
     * @return string
     * @return $this
     */
    public function method($method = null)
    {
        if (null===$method)return $this->method;

        if (null===$this->_default_method)
        {
            $this->_default_method = $this->method;
        }

        $this->method = strtoupper($method);

        return $this;
    }


    /**
     * 获取数据
     *
     * @param string $query 请求的参数
     * @return OOP_ORM_Result
     */
    public function find($query = null)
    {
        if (!$this->api_url)
        {
            throw new Exception(__('orm api url is not declared.'));
        }

        $url = $this->parse_api_full_url($query);

        try
        {
            if ($this->method=='POST')
            {
                $rs = (string)$this->driver()->post($url, $this->parse_api_post_data($query));
            }
            else if ($this->method=='PUT')
            {
                $rs = (string)$this->driver()->put($url, $this->parse_api_post_data($query));
            }
            else if ($this->method=='DELETE')
            {
                $rs = (string)$this->driver()->delete($url);
            }
            else
            {
                if ($this->method!='GET')
                {
                    $this->driver()->method($this->method);
                }

                $rs = (string)$this->driver()->get($url);
            }
        }
        catch (Exception $e)
        {
            Core::debug()->error($url, 'ORM获取数据失败');
            $rs = '[]';
        }

        $this->last_query = $url;

        // 处理解析数据
        $this->parse_result_data($rs);

        // 重置数据
        $this->reset();

        return $this->create_group_data($rs, true);
    }

    /**
     * 解析返回完善的请求的URL
     *
     * @param string $query
     * @param string
     */
    protected function parse_api_full_url($query = null)
    {
        $url = $this->api_url;

        if ($query)
        {
            if (is_array($query))
            {
                $query = http_build_query($query, '', '&');
            }

            $url .= (strpos($this->api_url, '?') === false ? '?' : '&') . $query;
        }
        else if ($this->method!='POST' && $this->method!='PUT')
        {
            $url .= (strpos($this->api_url, '?') === false ? '?' : '&') . http_build_query($this->arguments, '', '&');
        }

        return $url;
    }


    /**
     * 解析用于POST、PUT提交数据的数据
     *
     * @param string $query
     * @param string
     */
    protected function parse_api_post_data($query = null)
    {
        if ($query)
        {
            if (is_array($query))
            {
                return http_build_query($query, '', '&');
            }
            else
            {
                return $query;
            }
        }
        else
        {
            return http_build_query($this->arguments, '', '&');
        }
    }


    /**
     * HttpClient对象
     *
     * @return HttpClient
     */
    public function driver()
    {
        if (null===$this->_driver)$this->_driver = HttpClient::factory();
        return $this->_driver;
    }


    /**
     * 设置查询条件
     *
     * @param   mixed  $column column name or array($column, $alias) or object
     * @param   string $value logic operator
     * @param   mixed  $op column value
     * @return  $this
     */
    public function where($column, $value = null, $op = '=')
    {
        $this->arguments['where'][] = array($column, $value, $op);

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
        $this->arguments['in'][] = array($column, $value, $no_in);
        return $this;
    }

    /**
     * 排序
     *
     * @param   mixed  $column column name or array($column, $alias) or object
     * @param   string $direction direction of sorting
     * @return  $this
     */
    public function order_by($column, $direction = 'ASC')
    {
        $this->arguments['order'][] = array($column, $direction);
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
        if (!$this->arguments['group_by'])
        {
            $this->arguments['group_by'] = func_get_args();
        }
        else
        {
            $this->arguments['group_by'] = array_merge($this->arguments['group_by'], func_get_args());
        }

        return $this;
    }


    /**
     * 解析请求回来的数据
     *
     * @param string $data
     * @return array
     */
    protected function parse_result_data(&$data)
    {
        $data = @json_decode($data, true);
    }

    /**
     * 重置请求数据
     *
     * @return $this
     */
    protected function reset()
    {
        if ($this->_default_method)
        {
            $this->method = $this->_default_method;
            $this->_default_method = null;
        }

        $this->arguments = array();
    }
}