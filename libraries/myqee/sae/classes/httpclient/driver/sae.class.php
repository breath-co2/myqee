<?php

/**
 * HttpClient For SEA 驱动
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2012 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Library_MyQEE_SAE_HttpClient_Driver_SAE extends SaeFetchurl
{

    protected $http_data = array();

    /**
     * 设置$cookie
     * @param $agent
     */
    public function set_agent($agent)
    {
        $this->setOpt('useragent', $agent);
    }

    /**
     * 设置$cookie
     * @param string $cookie
     */
    public function set_cookies($cookies)
    {
        foreach ( $cookies as $k => $v )
        {
            $this->setCookie($k, $v);
        }
    }

    /**
     * 设置$referer
     * @param string $referer
     */
    public function set_referer($referer)
    {
        # 不支持
    }

    /**
     * 用POST方式提交
     * @param $url
     * @param string/array $vars
     * @param $timeout 超时时间，默认120秒
     * @return string, false on failure
     */
    public function post($url, $vars, $timeout = 120)
    {
        $this->setMethod('post');
        $this->setPostData($vars);
        $data = $this->fetch($url);
        unset($this->opt_['post']);
        $this->http_data = array
        (
            'code'   => $this->httpCode(),
            'header' => $this->responseHeaders(false),
            'data'   => $data
        );
        return $data;
    }

    /**
     * GET方式获取数据
     * @param $url
     * @param $timeout
     * @return string, false on failure
     */
    public function get($url, $timeout = 60)
    {
        $this->setMethod('get');
        $this->setReadTimeout($timeout);

        $data = $this->fetch($url);
        $this->http_data = array
        (
            'code'   => $this->httpCode(),
            'header' => $this->responseHeaders(false),
            'data'   => $data
        );
        return $data;
    }

    public function get_resut_data()
    {
        return $this->http_data;
    }
}