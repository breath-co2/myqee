<?php

/**
 * Wget Curl驱动核心
 *
 * @author     jonwang(jonwang@myqee.com)
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2012 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class MyQEE_HttpClient_Driver_Curl
{

    protected $http_data = array();

    protected $agent;

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

    /**
     * 默认连接超时时间，毫秒
     *
     * @var int
     */
    protected static $connecttimeout_ms = 3000;

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
     * @return HttpClient_Driver_Curl
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
     * @return HttpClient_Driver_Curl
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
     * @return HttpClient_Driver_Curl
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
     * @return HttpClient_Driver_Curl
     */
    public function set_ip($ip)
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * 设置curl参数
     *
     * @param string $key
     * @param value $value
     * @return HttpClient_Driver_Curl
     */
    public function set_option($key, $value)
    {
        if ( $key===CURLOPT_HTTPHEADER )
        {
            $this->header = array_merge($this->header,$value);
        }
        else
        {
            $this->_option[$key] = $value;
        }
        return $this;
    }

    /**
     * 设置多个列队默认排队数上限
     *
     * @param int $num
     * @return HttpClient_Driver_Curl
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
        $this->set_option( CURLOPT_HTTPHEADER, array('Expect:') );
        $this->set_option( CURLOPT_POST, true );

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

        return $this->get($url,$timeout);
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
     * @return curl_init()
     */
    protected function _create($url,$timeout)
    {
        if ( false===strpos($url, '://') )
        {
            preg_match('#^(http(?:s)?\://[^/]+/)#', $_SERVER["SCRIPT_URI"] , $m);
            $the_url = $m[1].ltrim($url,'/');
        }
        else
        {
            $the_url = $url;
        }

        if ($this->ip)
        {
            # 如果设置了IP，则把URL替换，然后设置Host的头即可
            if ( preg_match('#^(http(?:s)?)\://([^/\:]+)(\:[0-9]+)?/#', $the_url.'/',$m) )
            {
                $this->header[] = 'Host: '.$m[2];
                $the_url = $m[1].'://'.$this->ip.$m[3].'/'.substr($the_url,strlen($m[0]));
            }
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $the_url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, HttpClient_Driver_Curl::$connecttimeout_ms);

        if ( preg_match('#^https://#i', $the_url) )
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        if ( $this->cookies )
        {
            curl_setopt($ch, CURLOPT_COOKIE, http_build_query($this->cookies, '', ';'));
        }

        if ( $this->referer )
        {
            curl_setopt($ch, CURLOPT_REFERER, $this->referer);
        }

        if ( $this->agent )
        {
            curl_setopt($ch, CURLOPT_USERAGENT, $this->agent);
        }
        elseif ( array_key_exists('HTTP_USER_AGENT', $_SERVER) )
        {
            curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        }

        foreach ( $this->_option as $k => $v )
        {
            curl_setopt($ch, $k, $v);
        }

        if ( $this->header )
        {
            $header = array();
            foreach ($this->header as $item)
            {
                # 防止有重复的header
                if (preg_match('#(^[^:]*):.*$#', $item,$m))
                {
                    $header[$m[1]] = $item;
                }
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, array_values($header));
        }

        # 设置POST数据
        if (isset($this->_post_data[$url]))
        {
            curl_setopt($ch , CURLOPT_POSTFIELDS , $this->_post_data[$url]);
        }

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

        $mh = curl_multi_init();

        # 监听列表
        $listener_list = array();

        # 返回值
        $result = array();

        # 总列队数
        $list_num = 0;

        # 排队列表
        $multi_list = array();
        foreach ( $urls as $url )
        {
            # 创建一个curl对象
            $current = $this->_create($url, $timeout);

            if ( $this->multi_exec_num>0 && $list_num>=$this->multi_exec_num )
            {
                # 加入排队列表
                $multi_list[] = $url;
            }
            else
            {
                # 列队数控制
                curl_multi_add_handle($mh, $current);
                $listener_list[$url] = $current;
                $list_num++;
            }

            $result[$url] = null;
            $this->http_data[$url] = null;
        }
        unset($current);

        $running = null;

        # 已完成数
        $done_num = 0;

        do
        {
            while ( ($execrun = curl_multi_exec($mh, $running)) == CURLM_CALL_MULTI_PERFORM );
            if ( $execrun != CURLM_OK ) break;

            while ( true==($done = curl_multi_info_read($mh)) )
            {
                foreach ( $listener_list as $done_url=>$listener )
                {
                    if ( $listener === $done['handle'] )
                    {
                        # 获取内容
                        $this->http_data[$done_url] = $this->get_data(curl_multi_getcontent($done['handle']), $done['handle']);

                        if ( $this->http_data[$done_url]['code'] != 200 )
                        {
                            Core::debug()->error('URL:'.$done_url.' ERROR,TIME:' . $this->http_data[$done_url]['time'] . ',CODE:' . $this->http_data[$done_url]['code'] );
                            $result[$done_url] = false;
                        }
                        else
                        {
                            # 返回内容
                            $result[$done_url] = $this->http_data[$done_url]['data'];
                            Core::debug()->info('URL:'.$done_url.' OK.TIME:' . $this->http_data[$done_url]['time'] );
                        }

                        curl_close($done['handle']);

                        curl_multi_remove_handle($mh, $done['handle']);

                        # 把监听列表里移除
                        unset($listener_list[$done_url],$listener);
                        $done_num++;

                        # 如果还有排队列表，则继续加入
                        if ( $multi_list )
                        {
                            # 获取列队中的一条URL
                            $current_url = array_shift($multi_list);

                            # 创建CURL对象
                            $current = $this->_create($current_url, $timeout);

                            # 加入到列队
                            curl_multi_add_handle($mh, $current);

                            # 更新监听列队信息
                            $listener_list[$current_url] = $current;
                            unset($current);

                            # 更新列队数
                            $list_num++;
                        }

                        break;
                    }
                }
            }

            if ($done_num>=$list_num)break;

        } while (true);

        # 关闭列队
        curl_multi_close($mh);

        return $result;
    }

    public function get_resut_data()
    {
        return $this->http_data;
    }

    protected function get_data($data, $ch)
    {
        $header_size      = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $result['code']   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result['data']   = substr($data, $header_size);
        $result['header'] = explode("\r\n", substr($data, 0, $header_size));
        $result['time']   = curl_getinfo($ch, CURLINFO_TOTAL_TIME);

        return $result;
    }

    /**
     * 清理设置
     */
    protected function clear_set()
    {
        $this->_option = array();
        $this->header = array();
        $this->ip = null;
        $this->cookies = null;
        $this->referer = null;
        $this->_post_data = array();
    }
}