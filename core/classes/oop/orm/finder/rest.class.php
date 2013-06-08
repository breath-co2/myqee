<?php

/**
 * MyQEE ORM REST核心类
 *
 * @author     jonwang(jonwang@myqee.com)
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Core_OOP_ORM_Finder_REST extends OOP_ORM
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
     * @var HttpClient
     */
    protected $_driver = null;

    function __construct()
    {
        if ( null === $this->api_url )
        {
            throw new Exception(__('orm api url is not declared.'));
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

        $this->method = strtoupper($method);

        return $this;
    }

    /**
     * 获取数据
     *
     * @param $query 请求的参数
     * @return OOP_ORM_Result
     */
    public function find($query = null)
    {
        if (is_array($query))
        {
            $query = http_build_query($query, '', '&');
        }
        $url = $this->api_url . (strpos($this->api_url, '?') !== false ? '?' : '&') . $query;
        try
        {
            if ($this->method=='GET')
            {
                $data = (string)$this->driver()->get($url);
            }
            else if ($this->method=='PUT')
            {
                $data = (string)$this->driver()->put($this->method)->get($url);
            }
            else
            {
                $data = (string)$this->driver()->post($url, $this->data);
            }
        }
        catch (Exception $e)
        {
            Core::debug()->error('ORM获取数据失败,URL:' . $url);
            $data = '[]';
        }
        $this->last_query = $url;

        // 处理解析数据
        $this->parse_find_data($data);

        return $this->create_group_data($data, true);
    }

    /**
     * 解析请求回来的数据
     *
     * @param string $data
     * @return array
     */
    protected function parse_find_data(&$data)
    {
        @json_decode($data, true);
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
     *
     * @param   mixed   column name or array($column, $alias) or object
     * @param   string  logic operator
     * @param   mixed   column value
     * @return  MyQEE_OOP_ORM_Finder_DB
     */
    public function where($column, $value = null, $op = '=')
    {

        return $this;
    }
}