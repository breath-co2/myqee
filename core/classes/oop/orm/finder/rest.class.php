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
     * 获取数据
     *
     * @param $query SQL OR Query_Builder
     * @return OOP_ORM_Result
     */
    public function find($query = null)
    {
        if ( is_array($query) )
        {
            $query = http_build_query($query, '', '&');
        }
        $url = $this->api_url . (strpos($this->api_url, '?') !== false ? '?' : '&') . $query;
        try
        {
            $data = (string)$this->driver()->get($url);
        }
        catch ( Exception $e )
        {
            Core::debug()->error('ORM获取数据失败,URL:' . $url);
            $data = '[]';
        }
        $this->last_query = $url;
        $data = @json_decode($data, true);

        return $this->create_group_data($data, true);
    }

    /**
     * HttpClient对象
     * @return HttpClient
     */
    public function driver()
    {
        if ( null === $this->_driver ) $this->_driver = HttpClient::factory();
        return $this->_driver;
    }
}