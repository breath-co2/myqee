<?php
/**
 * 系统内部调用核心类
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Core
 * @package    Classes
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Core_HttpCall
{
    protected $group;

    protected $hosts = array();

    /**
     * 默认连接超时时间，毫秒
     *
     * @var int
     */
    protected static $connecttimeout_ms = 10000;

    /**
     * 最后返回的内容
     *
     * @var array
     */
    protected static $last_result = array();

    public function __construct($group=null)
    {
        if (!$group)$group = 'default';

        $this->group = $group;
        $this->hosts = Core::config('web_server_list.'.$group);

        if (!$this->hosts)
        {
            $this->hosts = array
            (
                $_SERVER["REMOTE_ADDR"].':'.$_SERVER["SERVER_PORT"],
            );
        }
    }

    /**
     * 返回HttpServer实例化对象
     *
     * @param string $group 分组，不传则为默认default
     * @return HttpCall
     */
    public static function factory($group = null)
    {
        return new HttpCall($group);
    }

    /**
     * 调用系统内部请求
     *
     * HttpCall::sync_exec('uri');
     * HttpCall::sync_exec('test/abc','arg1','arg2','arg3');
     *
     * @param string $uri
     * @param mixed $arg1
     * @param mixed $arg2
     */
    public function sync_exec($uri, $arg1=null, $arg2=null)
    {
        # 参数
        $param_arr = func_get_args();
        array_shift($param_arr);

        return $this->exec($uri, $this->hosts, $param_arr);
    }

    /**
     * 调用系统内部请求主服务器
     *
     * HttpCall::master_exec('uri');
     * HttpCall::master_exec('test/abc','arg1','arg2','arg3');
     *
     * @param string $uri
     * @param mixed $arg1
     * @param mixed $arg2
     */
    public function master_exec($uri,$arg1=null,$arg2=null)
    {
        # 参数
        $param_arr = func_get_args();
        array_shift($param_arr);

        return $this->exec($uri, current($this->hosts), $param_arr);
    }

    /**
     * 指定Server执行系统内部调用
     *
     *     //指定多个服务器执行
     *     HttpServer::exec('test/abc',array('192.168.1.11:8080','192.168.1.12:80'),array('a','b','c'));
     *
     *     //指定一个服务器执行
     *     HttpServer::exec('test/abc','192.168.1.11:8080'array('a','b','c'));
     *
     * @param string $uri
     * @param array $hosts
     * @param array $param_arr
     * @return array
     */
    public static function exec($uri, $hosts , array $param_arr = array())
    {
        $single = false;

        if (is_string($hosts))
        {
            $hosts = array($hosts);
            $single = true;
        }

        # 是否支持CURL
        static $curl_supper = null;
        if (null===$curl_supper)$curl_supper = function_exists('curl_init');

        if (IS_CLI)
        {
            $url_site = Core::config('core.url.site');
            if (!$url_site)
            {
                throw new Exception(__('your core config $config[\'url\'][\'site\'] is not defined.check config:ext', array(':ext'=>EXT)));
            }

            $script = $url_site;
        }
        else
        {
            $script = $_SERVER["SCRIPT_URI"];
        }

        $url = Core::url($uri);

        if (false === strpos($url, '://'))
        {
            preg_match('#^(http(?:s)?\://[^/]+/)#', $script , $m);
            $url = $m[1].ltrim($url, '/');
        }

        # http://host/uri
        $uri_arr = explode('/', $url, 3);
        $scr_arr = explode('/', $script, 3);

        $uri_arr[0] = $scr_arr[0];       // 替换 http://
        $uri_arr[2] = $scr_arr[2];       // 替换 域名部分
        $url        = implode('/', $uri_arr);

        # 加入系统参数
        $data = array
        (
            'data' => serialize($param_arr),
        );

        $time = microtime(1);
        if ($curl_supper)
        {
            # 调用CURL请求
            HttpCall::$last_result = HttpCall::exec_by_curl($hosts, $url, '/'.ltrim($uri, '/'), $data);
        }
        else
        {
            # 调用socket进行连接
            HttpCall::$last_result = HttpCall::exec_by_socket($hosts, $url, '/'.ltrim($uri, '/'), $data);
        }

        # 单条记录
        if ($single)
        {
            $result = current(HttpCall::$last_result);
        }
        else
        {
            $result = HttpCall::$last_result;
        }

        if (IS_DEBUG)
        {
            Core::debug()->log('system exec time:'.(microtime(1)-$time));
            Core::debug()->info($result, 'system exec result');
        }

        return $result;
    }

    /**
     * 返回最后请求返回的内容
     *
     * @return array
     */
    public static function last_result()
    {
        return HttpCall::$last_result;
    }

    /**
     * 通过CURL执行
     *
     * @param array $hosts 请求的所有服务器列表
     * @param string $url 请求的URL
     * @param string $path_info 待请求的 path_info 参数
     * @param array $param_arr 请求的参数
     * @return array
     */
    protected static function exec_by_curl($hosts, $url, $path_info, array $param_arr = null)
    {
        $mh = curl_multi_init();

        # 监听列表
        $listener_list = array();

        $vars = http_build_query($param_arr);

        # 创建列队
        foreach ($hosts as $h)
        {
            # 排除重复HOST
            if (isset($listener_list[$h]))continue;

            list($host,$port) = explode(':',$h,2);
            if (!$port)
            {
                # 默认端口
                $port = $_SERVER["SERVER_PORT"];
            }

            # 一个mictime
            $mictime = microtime(1);

            # 生成一个随机字符串
            $rstr = Text::random();

            # 创建一个curl对象
            $current = HttpCall::_create_curl($host, $port, $url, $path_info, 10, $vars, $mictime, $rstr);

            # 列队数控制
            curl_multi_add_handle($mh, $current);

            $listener_list[$h] = $current;
        }
        unset($current);

        $running = null;

        $result = array();

        # 已完成数
        $done_num = 0;

        # 待处理数
        $list_num = count($listener_list);

        do
        {
            while (($execrun = curl_multi_exec($mh, $running)) == CURLM_CALL_MULTI_PERFORM);
            if ($execrun!=CURLM_OK)break;

            while (true==($done = curl_multi_info_read($mh)))
            {
                foreach ($listener_list as $done_host=>$listener)
                {
                    if ($listener === $done['handle'])
                    {
                        # 获取内容
                        $result[$done_host] = curl_multi_getcontent($done['handle']);

                        $code = curl_getinfo($done['handle'], CURLINFO_HTTP_CODE);

                        if ($code!=200)
                        {
                            Core::debug()->error('system exec:'.$done_host.' ERROR,CODE:' . $code );
//                             $result[$done_host] = false;
                        }
                        else
                        {
                            # 返回内容
                            Core::debug()->info('system exec:'.$done_host.' OK.');
                        }

                        curl_close($done['handle']);

                        curl_multi_remove_handle($mh, $done['handle']);

                        unset($listener_list[$done_host], $listener);

                        $done_num++;

                        $time = microtime(1);

                        break;
                    }
                }

            }

            if ($done_num>=$list_num) break;

            if (!$running) break;

        } while (true);


        # 关闭列队
        curl_multi_close($mh);

        return $result;
    }

    /**
     * 创建一个CURL对象
     *
     * @param string $url URL地址
     * @param int $timeout 超时时间
     * @return resource a cURL handle on success, false on errors.
     */
    protected static function _create_curl($host, $port, $url, $path_info, $timeout, $vars, $mictime, $rstr)
    {
        if (preg_match('#^(http(?:s)?)\://([^/\:]+)(\:[0-9]+)?/#', $url .'/', $m))
        {
            $url = $m[1] .'://'. $host.$m[3] .'/'. substr($url, strlen($m[0]));
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        try
        {
            # 发现安全模式开启情况会报错
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        }
        catch(Exception $e){}

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, max(HttpCall::$connecttimeout_ms, $timeout));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, HttpCall::$connecttimeout_ms);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
        curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 86400);

        if (preg_match('#^https://#i', $url))
        {
            if (!$port)$port = 443;
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        else
        {
            if (!$port)$port = 80;
        }

        # 生成一个HASH
        $hash = self::get_hash($vars, $rstr, $mictime, $path_info .'_'. (IS_ADMIN_MODE?1:0) .'_'. (IS_REST_MODE?1:0));

        $header = array
        (
            'Expect:',
            'Host: '.$m[2],
            'X-Myqee-System-Hash: '.$hash,
            'X-Myqee-System-Time: '.$mictime,
            'X-Myqee-System-Rstr: '.$rstr,
            'X-Myqee-System-Pathinfo: '.$path_info,
            'X-Myqee-System-Project: '.Core::$project,
            'X-Myqee-System-Isadmin: '.(IS_ADMIN_MODE?1:0),
            'X-Myqee-System-Isrest: '.(IS_REST_MODE?1:0),
            'X-Myqee-System-Debug: '.(IS_DEBUG?1:0),
        );

        curl_setopt($ch, CURLOPT_PORT, $port);
        curl_setopt($ch, CURLOPT_USERAGENT, 'MyQEE System Call');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        return $ch;
    }

    /**
     * 通过Socket执行
     *
     * @param array $hosts 请求的所有服务器列表
     * @param string $url 请求的URL
     * @param string $path_info 待请求的 path_info 参数
     * @param array $param_arr 请求的参数
     * @return array
     */
    protected static function exec_by_socket($hosts, $url, $path_info, array $param_arr = null)
    {
        $vars = http_build_query($param_arr);

        if (preg_match('#^(http(?:s)?)\://([^/\:]+)(\:[0-9]+)?/(.*)$#', $url, $m))
        {
            $uri = '/'.ltrim($m[4],'/');     //获取到URI部分
            $h = $m[2];                      //获取到HOST
        }
        else
        {
           throw new Exception('error url: '. $url);
        }

        $fs = $errno = $errstr = $rs = array();

        foreach ($hosts as $host)
        {
            list($hostname, $port) = explode(':', $host, 2);
            if (!$port)
            {
                $port = $_SERVER["SERVER_PORT"];
            }

            if ($m[1]=='https')$hostname = 'tls://' . $hostname;

            # 一个mictime
            $mictime = microtime(1);

            # 生成一个随机字符串
            $rstr = Text::random();

            # 生成一个HASH
            $hash = self::get_hash($vars, $rstr, $mictime, $path_info .'_'. (IS_ADMIN_MODE?1:0) .'_'. (IS_REST_MODE?1:0));

            # 使用HTTP协议请求数据
            $str = 'POST ' . $uri . ' HTTP/1.0' . CRLF
            . 'Host: ' . $h . CRLF
            . 'User-Agent: MyQEE System Call' . CRLF
            . 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' . CRLF
            . 'Connection: close' . CRLF
            . 'X-Myqee-System-Hash: ' . $hash . CRLF
            . 'X-Myqee-System-Time: ' . $mictime . CRLF
            . 'X-Myqee-System-Rstr: ' . $rstr . CRLF
            . 'X-Myqee-System-Pathinfo: '.$path_info . CRLF
            . 'X-Myqee-System-Project: '.Core::$project . CRLF
            . 'X-Myqee-System-Isadmin: '.(IS_ADMIN_MODE?1:0) . CRLF
            . 'X-Myqee-System-Isrest: '.(IS_REST_MODE?1:0) . CRLF
            . 'X-Myqee-System-Debug: ' . (IS_DEBUG?1:0) . CRLF
            . 'Content-Length: ' . strlen($vars) . CRLF
            . 'Content-Type: application/x-www-form-urlencoded' . CRLF
            . CRLF . $vars;

            // 尝试2次
            for($i=1 ;$i<3 ;$i++)
            {
                if (isset($fs[$host]))break;

                # 尝试连接服务器
                $ns = fsockopen($hostname, $port, $errno[$host], $errstr[$host], 1);
                if ($ns)
                {
                    $fs[$host] = $ns;
                    break;
                }
                elseif ($i==2)
                {
                    $rs[$host] = false;
                }
                else
                {
                    usleep(2000);    //等待2毫秒
                }
            }
            unset($ns);

            if ($fs[$host])
            {
                for($i=0; $i<3; $i++)
                {
                    # 写入HTTP协议内容
                    if (strlen($str) === fwrite($fs[$host], $str))
                    {
                        # 成功
                        break;
                    }
                    elseif ($i==2)
                    {
                        # 写入失败，将此移除
                        unset($fs[$host]);
                        $rs[$host] = false;
                        break;
                    }
                    else
                    {
                        usleep(2000);    //等待2毫秒
                    }
                }
            }
        }

        foreach ($fs as $host=>$f)
        {
            $str = '';
            while (!feof($f))
            {
                $str .= fgets($f);
            }
            fclose($f);

            list($header, $body) = explode("\r\n\r\n", $str, 2);

            $rs[$host] = $body;
        }

        return $rs;
    }

    /**
     * 根据参数获取内部请求的HASH
     *
     * @param string $vars
     * @param string $rstr
     * @param int $port
     * @return string
     */
    private static function get_hash($vars, $rstr, $mictime, $other)
    {
        # 系统调用密钥
        $system_exec_pass = Core::config('system_exec_key');

        $key = Core::config()->get('system_exec_key', 'system', true);

        // 每天更好动态密码
        if (!$key || abs(TIME - $key['time'])> 86400)
        {
            $key = array
            (
                'str'  => Text::random(null, 32),
                'time' => TIME,
            );

            // 直接保存但不更新缓存，避免造成File操作死循环
            if (!Core::config()->set('system_exec_key', $key, 'system', false))
            {
                throw new Exception(__('Updated dynamic password fails, check the server database and configuration'));
            }
        }

        $other .= $key['str'];

        if ($system_exec_pass && strlen($system_exec_pass) >= 10)
        {
            # 如果有则使用系统调用密钥
            $hash = sha1($vars.$mictime.$system_exec_pass.$rstr.'_'.$other);
        }
        else
        {
            # 没有，则用系统配置和数据库加密
            $hash = sha1($vars.$mictime.serialize(Core::config('core')).serialize(Core::config('database')).$rstr.'_'.$other);
        }

        return $hash;
    }
}