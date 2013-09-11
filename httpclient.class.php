<?php

/**
 * HTTP请求数据核心类
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Module
 * @package    HttpClient
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Module_HttpClient
{

    /**
     * curl操作类型
     *
     * @var curl
     */
    const TYPE_CURL = 'Curl';

    /**
     * fsockopen操作类型
     *
     * @var string
     */
    const TYPE_FSOCK = 'Fsock';

    /**
     * 默认操作类型
     *
     * @var string $default_type
     */
    protected static $default_type = null;

    /**
     * 当前使用操作类型
     *
     * @var string
     */
    protected $type;

    /**
     * 驱动
     *
     * @var HttpClient_Driver_Curl
     */
    protected $driver;

    /**
     * 客户端信息
     *
     * @var string
     */
    protected static $agent = '';

    /**
     * @param string $type 指定驱动类型
     */
    function __construct($type = null)
    {
        if ($type)
        {
            $this->type = $type;
        }
        elseif (HttpClient::$default_type)
        {
            $this->type = HttpClient::$default_type;
        }
        elseif ( HttpClient::is_support_curl() )
        {
            $this->type = HttpClient::TYPE_CURL;
        }
        else
        {
            $this->type = HttpClient::TYPE_Fsock;
        }
    }

    /**
     * 获取实例化对象
     *
     * @param string $type
     * @return HttpClient
     */
    public static function factory($type = null)
    {
        return new HttpClient($type);
    }

    /**
     * 是否支持CURL
     *
     * @return boolean
     */
    protected static function is_support_curl()
    {
        static $s = null;
        if (null===$s)$s = function_exists('curl_init');
        return $s;
    }

    /**
     * 设置$agent
     *
     * @param string $agent
     * @return HttpClient
     */
    public function set_agent($agent = null)
    {
        $this->driver()->set_agent($agent);
        return $this;
    }

    /**
     * 设置$cookie
     *
     * @param string $cookie
     * @return HttpClient
     */
    public function set_cookies($cookies)
    {
        $this->driver()->set_cookies($cookies);
        return $this;
    }

    /**
     * 设置$referer
     *
     * @param string $referer
     * @return HttpClient
     */
    public function set_referer($referer)
    {
        $this->driver()->set_referer($referer);
        return $this;
    }

    /**
     * 设置请求页面的IP地址
     *
     * @param string $ip
     * @return HttpClient
     */
    public function set_ip($ip)
    {
        $this->driver()->set_ip($ip);
        return $this;
    }

    /**
     * 设置请求页面的Header信息
     *
     *     $this->set_header('X-Auth-User: test');
     *
     * @param string $header
     * @return HttpClient
     */
    public function set_header($header)
    {
        $this->driver()->set_header($header);
        return $this;
    }

    /**
     * 设置参数
     *
     * @param $key
     * @param $value
     * @return HttpClient
     */
    public function set_option($key, $value)
    {
        $this->driver()->set_option($key, $value);

        return $this;
    }

    /**
     * 设置多个列队默认排队数上限
     *
     * @param int $num
     * @return HttpClient
     */
    public function set_multi_max_num($num=0)
    {
        $this->driver()->set_multi_exec_num();
        return $this;
    }

    /**
     * HTTP GET方式请求
     *
     * 支持多并发进程，这样可以大大缩短API请求时间
     *
     * @param string/array $url 支持多个URL
     * @param array $data
     * @param $timeout
     * @return string
     * @return HttpClient_Result 但个URL返回当然内容对象
     * @return Arr 多个URL时将返回一个数组对象
     */
    public function get($url, $timeout = 10)
    {
        $this->driver()->get($url, $timeout);
        $data = $this->driver()->get_resut_data();

        if ( is_array($url) )
        {
            # 如果是多个URL
            $result = new Arr();
            foreach ( $data as $key => $item )
            {
                $result[$key] = new HttpClient_Result($item);
            }
        }
        else
        {
            $result = new HttpClient_Result($data);
        }

        return $result;
    }

    /**
     * POST方式请求
     *
     * @param $url
     * @param $data
     * @param $timeout
     * @return HttpClient_Result
     */
    public function post($url, $data, $timeout = 30)
    {
        $time = microtime(true);
        $this->driver()->post($url, $data, $timeout);
        $time = microtime(true) - $time;
        $data = $this->driver()->get_resut_data();
        $data['total_time'] = $time;

        return new HttpClient_Result($data);
    }

    /**
     * PUT方式请求
     *
     * @param $url
     * @param $data
     * @param $timeout
     * @return HttpClient_Result
     */
    public function put($url, $data, $timeout = 30)
    {
        $time = microtime(true);
        $this->driver()->put($url, $data, $timeout);
        $time = microtime(true) - $time;
        $data = $this->driver()->get_resut_data();
        $data['total_time'] = $time;

        return new HttpClient_Result($data);
    }

    /**
     * DELETE方式请求
     *
     * @param $url
     * @param $data
     * @param $timeout
     * @return HttpClient_Result
     */
    public function delete($url, $timeout = 30)
    {
        $this->driver()->method('DELETE');

        return $this->get($url, $timeout);
    }

    public function __call($method, $params)
    {
        if ( method_exists($this->driver(), $method) )
        {
            return call_user_func_array(array($this->driver(), $method), $params);
        }
    }


    /**
     * 设置，获取REST的类型
     *
     * @param string $method GET|POST|DELETE|PUT 等，不传则返回当前method
     *
     * @return string
     * @return HttpClient_Result
     */
    public function method($method = null)
    {
        if (null===$method)return $this->driver()->method();

        $this->driver()->method(strtoupper($method));

        return $this;
    }


    /**
     * 获取当前驱动
     *
     * @return HttpClient_Driver_Curl
     */
    public function driver()
    {
        if ( null === $this->driver )
        {
            $f = 'HttpClient_Driver_' . $this->type;
            $this->driver = new $f();
        }
        return $this->driver;
    }
}