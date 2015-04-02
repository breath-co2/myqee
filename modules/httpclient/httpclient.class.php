<?php

/**
 * HTTP请求数据核心类
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Module
 * @package    HttpClient
 * @copyright  Copyright (c) 2008-2016 myqee.com
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
     * @var HttpClient_Drive_Curl
     */
    protected $drive;

    /**
     * 客户端信息
     *
     * @var string
     */
    protected static $agent = '';

    /**
     * 初始化
     */
    function __construct()
    {
        if (HttpClient::is_support_curl())
        {
            $this->type = HttpClient::TYPE_CURL;
        }
        else
        {
            $this->type = HttpClient::TYPE_FSOCK;
        }
    }

    /**
     * 获取实例化对象
     *
     * @return HttpClient
     */
    public static function factory()
    {
        return new HttpClient();
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
        $this->drive()->set_agent($agent);
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
        $this->drive()->set_cookies($cookies);
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
        $this->drive()->set_referer($referer);
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
        $this->drive()->set_ip($ip);
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
        $this->drive()->set_header($header);
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
        $this->drive()->set_option($key, $value);

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
        $this->drive()->set_multi_exec_num();
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
     * @return HttpClient_Result|Arr 单个URL返回当然内容对象，多个URL时将返回一个数组对象
     */
    public function get($url, $timeout = 10)
    {
        $this->drive()->get($url, $timeout);
        $data = $this->drive()->get_result_data();

        if (is_array($url))
        {
            # 如果是多个URL
            $result = new Arr();
            foreach ($data as $key => $item)
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
     * 获取一个JSON的URL并返回数组
     *
     * 失败则返回 `false`
     *
     * @param $url
     * @param int $timeout
     * @return bool|array
     */
    public function get_json($url, $timeout = 10)
    {
        $data = $this->get($url, $timeout)->data();

        if ($data)
        {
            return json_decode($data, true);
        }
        else
        {
            return false;
        }
    }

    /**
     * 获取一个XML的URL并返回数组
     *
     * 失败则返回 `false`
     *
     * 返回数组方法使用 `Text::xml_to_array` 方法转换
     *
     * @param $url
     * @param int $timeout
     * @use Text::xml_to_array
     * @return bool|array
     */
    public function get_xml($url, $timeout = 10)
    {
        $data = $this->get($url, $timeout)->data();

        if ($data)
        {
            return Text::xml_to_array($data);
        }
        else
        {
            return false;
        }
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
        $this->drive()->post($url, $data, $timeout);
        $time = microtime(true) - $time;
        $data = $this->drive()->get_result_data();
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
        $this->drive()->put($url, $data, $timeout);
        $time = microtime(true) - $time;
        $data = $this->drive()->get_result_data();
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
        $this->drive()->method('DELETE');

        return $this->get($url, $timeout);
    }

    /**
     * 上传文件
     *
     * 注意，使用 `add_file()` 上传文件时，必须使用post方式提交
     *
     *      HttpClient::factory()->upload('http://localhost/upload', '/tmp/test.jpg');
     *
     * @param $url
     * @param $name string 上传的文件的key，默认为 `file`
     * @param $file_name string
     * @param null $post
     * @param int $timeout
     * @return HttpClient_Result
     */
    public function upload($url, $file_name, $name = 'upload', $post = null, $timeout = 30)
    {
        return $this->add_file($file_name, $name)->post($url, $post, $timeout);
    }

    /**
     * 添加上传文件
     *
     *      HttpClient::factory()->add_file('/tmp/test.jpg', 'img');
     *
     * @param $file_name string 文件路径
     * @param $name string 名称
     * @return $this
     */
    public function add_file($file_name, $name = 'upload')
    {
        $this->drive()->add_file($file_name, $name?$name:'upload');

        return $this;
    }

    public function __call($method, $params)
    {
        if ( method_exists($this->drive(), $method) )
        {
            return call_user_func_array(array($this->drive(), $method), $params);
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
        if (null===$method)return $this->drive()->method();

        $this->drive()->method(strtoupper($method));

        return $this;
    }


    /**
     * 获取当前驱动
     *
     * @return HttpClient_Drive_Curl
     */
    public function drive()
    {
        if (null === $this->drive)
        {
            $f = 'HttpClient_Drive_' . $this->type;
            $this->drive = new $f();
        }
        return $this->drive;
    }
}