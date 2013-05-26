<?php
/**
 * Http请求Fsock驱动核心类
 *
 * @author     jonwang(jonwang@myqee.com)
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Core_HttpClient_Driver_Fsock
{
    protected $http_data = array();

    protected $agent = '';

    protected $cookies;

    protected $referer;

    protected $ip;

    protected $header = array();

    protected $_option = array();

    protected $_post_data = array();

    /**
     * 多列队任务进程数，0表示不限制
     *
     * @var int
     */
    protected $multi_exec_num = 100;

    protected $method = 'GET';

    const ERROR_HOST = '请求的URL错误';

    const ERROR_GET = 'GET请求错误';

    const ERROR_POST = 'POST请求错误';

    function __construct()
    {

    }

    /**
     * 设置$cookie
     *
     * @param $agent
     * @return HttpClient_Driver_Fsock
     */
    public function set_agent($agent)
    {
        $this->agent = $agent;
        return $this;
    }

    /**
     * 设置$cookie
     *
     * @param string $cookie
     * @return HttpClient_Driver_Fsock
     */
    public function set_cookies($cookies)
    {
        $this->cookies = $cookies;
        return $this;
    }

    /**
     * 设置$referer
     *
     * @param string $referer
     * @return HttpClient_Driver_Fsock
     */
    public function set_referer($referer)
    {
        $this->referer = $referer;
        return $this;
    }

    /**
     * 设置IP
     *
     * @param string $ip
     * @return HttpClient_Driver_Fsock
     */
    public function set_ip($ip)
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * 设置Header
     *
     * @param string $header
     * @return HttpClient_Driver_Curl
     */
    public function set_header($header)
    {
        $this->header = array_merge($this->header, (array)$header);
        return $this;
    }

    /**
     * 设置curl参数
     *
     * //TODO 不支持自定义参数
     *
     * @param string $key
     * @param value $value
     * @return HttpClient_Driver_Fsock
     */
    public function set_option($key, $value)
    {
        return $this;
    }

    /**
     * 设置多个列队默认排队数上限
     *
     * @param int $num
     * @return HttpClient_Driver_Fsock
     */
    public function set_multi_max_num($num=0)
    {
        $this->multi_exec_num = (int)$num;
        return $this;
    }

    /**
     * 用POST方式提交，支持多个URL
     *
     *   $urls = array
     *   (
     *     'http://www.baidu.com/',
     *     'http://mytest.com/url',
     *     'http://www.abc.com/post',
     *   );
     *   $data = array
     *   (
     *      array('k1'=>'v1','k2'=>'v2'),
     *      array('a'=>1,'b'=>2),
     *      'aa=1&bb=3&cc=3',
     *   );
     *   HttpClient::factory()->post($url,$data);
     *
     * @param $url
     * @param string/array $vars
     * @param $timeout 超时时间，默认120秒
     * @return string, false on failure
     */
    public function post($url, $vars, $timeout = 60)
    {
        # POST模式
        $this->method = 'POST';

        if (is_array($url))
        {
            $myvars = array();
            foreach ($url as $k=>$url)
            {
                if (isset($vars[$k]))
                {
                    if (is_array($vars[$k]))
                    {
                        $myvars[$url] = http_build_query($vars[$k]);
                    }
                    else
                    {
                        $myvars[$url] = $vars[$k];
                    }
                }
            }
        }
        else
        {
            $myvars = array($url=>$vars);
        }
        $this->_post_data = $myvars;

        return $this->get($url, $timeout);
    }

    /**
     * GET方式获取数据，支持多个URL
     *
     * @param string/array $url
     * @param $timeout
     * @return string, false on failure
     */
    public function get($url, $timeout = 10)
    {
        if ( is_array($url) )
        {
            $getone = false;
            $urls = $url;
        }
        else
        {
            $getone = true;
            $urls = array($url);
        }

        $data = $this->request_urls($urls, $timeout);

        $this->clear_set();

        if ( $getone )
        {
            $this->http_data = $this->http_data[$url];
            return $data[$url];
        }
        else
        {
            return $data;
        }
    }

    /**
     * 创建一个CURL对象
     *
     * @param string $url URL地址
     * @param int $timeout 超时时间
     * @return fsockopen()
     */
    protected function _create($url,$timeout)
    {
        if (false===strpos($url, '://'))
        {
            preg_match('#^(http(?:s)?\://[^/]+/)#', $_SERVER["SCRIPT_URI"] , $m);
            $the_url = $m[1] . ltrim($url,'/');
        }
        else
        {
            $the_url = $url;
        }

        preg_match('#^(http(?:s)?)\://([^/]+)(/.*)$#', $the_url , $m);
        $hostname = $m[2];
        $uri      = $m[3];

        list($host, $port) = explode(':', $hostname, 2);

        if ($this->ip)
        {
            $host = $this->ip;
        }

        if ($m[1]=='https')
        {
            $host = 'tls://' . $host;
        }

        if (!$port)
        {
            if ($m[1]=='https')
            {
                $port = 443;
            }
            else
            {
                $port = 80;
            }
        }

        $ch = fsockopen($host, $port, $errno, $errstr, $timeout);

        $header = array
        (
            'Host'       => $hostname ,
            'Accept'     => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Connection' => 'close',
        );

        if ($this->cookies)
        {
            $header['Cookie'] = is_array($this->cookies)?http_build_query($this->cookies, '', ';'):$this->cookies;
        }

        if ($this->referer)
        {
            $header['Referer'] = $this->referer;
        }

        if ($this->agent)
        {
            $header['User-Agent'] = $this->agent;
        }
        elseif (array_key_exists('HTTP_USER_AGENT', $_SERVER))
        {
            $header['User-Agent'] = $_SERVER['HTTP_USER_AGENT'];
        }

        if ($this->header)
        {
            $header = array();
            foreach ($this->header as $item)
            {
                # 防止有重复的header
                if (preg_match('#(^[^:]*):(.*)$#', $item,$m))
                {
                    $header[trim($m[1])] = trim($m[2]);
                }
            }
        }

        # 设置POST数据
        if ($this->method=='POST')
        {
            $vars = (string)$this->_post_data[$the_url];
            $header['Content-Length'] = strlen($vars);
            $header['Content-Type']   = 'application/x-www-form-urlencoded';
        }

        $str = $this->method . ' ' . $uri . ' HTTP/1.1'."\r\n";
        foreach ($header as $k=>$v)
        {
            $str .= $k .' :' . str_replace(array("\r","\n"),'',$v) . "\r\n";
        }
        $str .= "\r\n";

        if ($this->method=='POST')
        {
            // 追加POST数据
            $str .= $vars;
        }

        fwrite($ch,$str);

        return $ch;
    }

    /**
     * 支持多线程获取网页
     *
     * @see http://cn.php.net/manual/en/function.curl-multi-exec.php#88453
     * @param Array/string $urls
     * @param Int $timeout
     * @return Array
     */
    protected function request_urls($urls, $timeout = 10)
    {
        # 去重
        $urls = array_unique($urls);

        if (!$urls)return array();

        # 监听列表
        $listener_list = array();

        # 返回值
        $result = array();

        # 总列队数
        $list_num = 0;

        # 记录页面跳转数据
        $redirect_list = array();

        # 排队列表
        $multi_list = array();
        foreach ( $urls as $url )
        {
            if ( $this->multi_exec_num>0 && $list_num>=$this->multi_exec_num )
            {
                # 加入排队列表
                $multi_list[] = $url;
            }
            else
            {
                # 列队数控制
                $listener_list[] = array($url,$this->_create($url, $timeout));
                $list_num++;
            }

            $result[$url] = null;
            $this->http_data[$url] = null;
        }

        # 已完成数
        $done_num = 0;

        while($listener_list)
        {
            list($done_url, $f) = array_shift($listener_list);

            $time = microtime(1);
            $str = '';
            while (!feof($f))
            {
                $str .= fgets($f);
            }

            fclose($f);
            $time = microtime(1)-$time;

            list($header, $body) = explode("\r\n\r\n", $str, 2);

            $header_arr = explode("\r\n", $header);
            $first_line = array_shift($header_arr);

            if ( preg_match('#^HTTP/1.1 ([0-9]+) #', $first_line, $m) )
            {
                $code = $m[1];
            }
            else
            {
                $code = 0;
            }

            if( strpos($header, 'Transfer-Encoding: chunked') )
            {
                $body = explode("\r\n", $body);
                $body = array_slice($body, 1, -1);
                $body = implode('', $body);
            }

            if ( preg_match('#Location(?:[ ]*):([^\r]+)\r\n#Uis', $header , $m) )
            {
                if (count($redirect_list[$done_url])>=10)
                {
                    # 防止跳转次数太大
                    $body = $header = '';
                    $code = 0;
                }
                else
                {
                    # 302 跳转
                    $new_url = trim($m[1]);
                    $redirect_list[$done_url][] = $new_url;

                    // 插入列队
                    if (preg_match('#Set-Cookie(?:[ ]*):([^\r+])\r\n#is', $header , $m2))
                    {
                        // 把cookie传递过去
                        $old_cookie    = $this->cookies;
                        $this->cookies = $m2[1];
                    }

                    array_unshift($listener_list, array($done_url , $this->_create($new_url, $timeout)));

                    if (isset($old_cookie))
                    {
                        $this->cookies = $old_cookie;
                    }
                    continue;
                }
            }

            $rs = array
            (
                'code'   => $code,
                'data'   => $body,
                'header' => $header_arr,
                'time'   => $time,
            );

            $this->http_data[$done_url] = $rs;

            if ($rs['code']!=200)
            {
                Core::debug()->error('URL:'.$done_url.' ERROR,TIME:' . $this->http_data[$done_url]['time'] . ',CODE:' . $this->http_data[$done_url]['code'] );
                $result[$done_url] = false;
            }
            else
            {
                Core::debug()->info('URL:'.$done_url.' OK.TIME:' . $this->http_data[$done_url]['time'] );
                $result[$done_url] = $rs['data'];
            }

            $done_num++;

            if ( $multi_list )
            {
                # 获取列队中的一条URL
                $current_url = array_shift($multi_list);

                # 更新监听列队信息
                $listener_list[] = array($current_url, $this->_create($current_url, $timeout));

                # 更新列队数
                $list_num++;
            }

            if ($done_num>=$list_num)break;
        }

        return $result;
    }

    public function get_resut_data()
    {
        return $this->http_data;
    }

    /**
     * 清理设置
     */
    protected function clear_set()
    {
        $this->_option    = array();
        $this->header     = array();
        $this->ip         = null;
        $this->cookies    = null;
        $this->referer    = null;
        $this->_post_data = array();
        $this->method     = 'GET';
    }
}