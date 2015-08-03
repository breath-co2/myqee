<?php

if (!defined('_HTTPIO_METHOD'))
{
    define('_HTTPIO_METHOD', isset($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"] : '');

    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 'xmlhttprequest'===strtolower($_SERVER['HTTP_X_REQUESTED_WITH']))
    {
        $is_ajax = true;
    }
    elseif (isset($_GET['_ajax']) && ($_GET['_ajax']=='true'||$_GET['_ajax']=='json'))
    {
        $is_ajax = true;
    }
    else
    {
        $is_ajax = false;
    }
    define('_HTTPIO_IS_AJAX', $is_ajax);
    unset($is_ajax);


    define('_HTTPIO_PROTOCOL', Core::protocol());


    $ip = Core::ip();
    define('_HTTPIO_IP', $ip[0]);
    unset($ip);


    if (isset($_SERVER['HTTP_REFERER']))
    {
        $referrer = $_SERVER['HTTP_REFERER'];
    }
    else
    {
        $referrer = null;
    }
    define('_HTTPIO_REFERRER', $referrer);
    unset($referrer);


    if (isset($_SERVER['HTTP_USER_AGENT']))
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
    }
    else
    {
        $user_agent = '';
    }


    define('_HTTPIO_USER_AGENT', $user_agent);
    unset($user_agent);
}


/**
 * Http输入输出
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2016 myqee.com
 * @license    http://www.myqee.com/license.html
 */
abstract class Core_HttpIO
{

    const PARAM_TYPE_URL = 'url';

    const PARAM_TYPE_OLDDATA = 'old';

    // HTTP status codes and messages
    protected static $messages = array
    (
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',

        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',

        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Moved Temporarily',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        // 306 is deprecated but reserved
        307 => 'Temporary Redirect',

        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',

        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        507 => 'Insufficient Storage',
        509 => 'Bandwidth Limit Exceeded'
    );

    /**
     * 请求类型
     *
     * @var  string  method: GET, POST, PUT, DELETE, etc
     */
    const METHOD = _HTTPIO_METHOD;

    /**
     * 协议类型
     *
     * @var  string http://, https:// 等
     */
    const PROTOCOL = _HTTPIO_PROTOCOL;

    /**
     * 引用页
     *
     * @var  string  referring URL
     */
    const REFERRER = _HTTPIO_REFERRER;

    /**
     * 用户浏览器信息
     *
     * @var  string  client user agent
     */
    const USER_AGENT = _HTTPIO_USER_AGENT;

    /**
     * 用户IP
     *
     * @var  string  client IP address
     */
    const IP = _HTTPIO_IP;

    /**
     * 是否AJAX请求
     *
     * @var  boolean  AJAX-generated request
     */
    const IS_AJAX = _HTTPIO_IS_AJAX;

    /**
     * 将输出的header列表
     *
     * @var array
     */
    public static $headers = array();

    /**
     * 页码状态
     *
     * @var int
     */
    public static $status = 200;

    /**
     * 当前页面URI
     *
     * @var string
     */
    public static $uri;

    /**
     * 当前页码参数
     *
     * @var array
     */
    public static $params;

    /**
     * 控制器对象寄存器
     *
     * @var array
     */
    protected static $controlers = array();

    /**
     * 是否开启了分块输出
     *
     * @var bool
     */
    protected static $IS_CHUNK_START = false;

    /**
     * 当前控制器
     */
    protected static $current_controller;

    protected static $_GET;

    protected static $_POST;

    protected static $_REQUEST;

    protected static $_COOKIE;

    protected static $_GET_OLD;

    protected static $_POST_OLD;

    protected static $_REQUEST_OLD;

    protected static $_COOKIE_OLD;

    protected static $_INPUT;

    protected static $_INPUT_OLD;

    public function __construct()
    {

    }

    /**
     * 执行初始化，只执行一次
     */
    public static function setup()
    {
        static $run = null;
        if (null===$run)
        {
            $run = true;
            if (!IS_CLI)
            {
                # 记录一个正真的原始拷贝数据
                HttpIO::$_GET_OLD     = $_GET;
                HttpIO::$_POST_OLD    = $_POST;
                HttpIO::$_COOKIE_OLD  = $_COOKIE;
                HttpIO::$_REQUEST_OLD = $_REQUEST;

                # XSS安全处理
                $_GET     = HttpIO::sanitize($_GET);
                $_POST    = HttpIO::sanitize($_POST);
                $_COOKIE  = HttpIO::sanitize($_COOKIE);
                $_REQUEST = HttpIO::sanitize($_REQUEST);

                # 隐射
                HttpIO::$_GET     =& $_GET;
                HttpIO::$_POST    =& $_POST;
                HttpIO::$_COOKIE  =& $_COOKIE;
                HttpIO::$_REQUEST =& $_REQUEST;

                HttpIO::$uri =& Core::$path_info;

                HttpIO::$_INPUT_OLD = file_get_contents('php://input');
                if (HttpIO::$_INPUT_OLD)HttpIO::$_INPUT = @json_decode(HttpIO::$_INPUT_OLD, true);
                if (!HttpIO::$_INPUT)HttpIO::$_INPUT = array();
            }

            // 自动支持子域名AJAX请求
            if (HttpIO::IS_AJAX && isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'])
            {
                HttpIO::auto_add_ajax_control_allow_origin();
            }
        }
    }

    /**
     * 自动添加HTML5的AJAX跨越支持
     */
    protected static function auto_add_ajax_control_allow_origin()
    {
        $ajax_cross_domain = Core::config('ajax_cross_domain');

        if (false!==$ajax_cross_domain)
        {
            if ('none'==$ajax_cross_domain)return ;

            $info = parse_url($_SERVER['HTTP_REFERER']);
            $host = $info['host'];

            $add_allow_origin = false;

            if (is_array($ajax_cross_domain))
            {
                foreach ($ajax_cross_domain as $item)
                {
                    if (strpos($item, '*')!==false)
                    {
                        $preg = '#^'. str_replace('\\*', '*', preg_quote($item)) .'#$i';
                        if (preg_match($preg, $host))
                        {
                            $add_allow_origin = true;
                            break;
                        }
                    }
                    elseif ($host==$item)
                    {
                        $add_allow_origin = true;
                        break;
                    }
                }
            }
            elseif ($ajax_cross_domain)
            {
                if ($_SERVER['HTTP_HOST']!=$host && HttpIO::get_primary_domain($_SERVER['HTTP_HOST']) == HttpIO::get_primary_domain($host))
                {
                    $add_allow_origin = true;
                }
            }

            if ($add_allow_origin)
            {
                header('Access-Control-Allow-Origin: '. HttpIO::PROTOCOL . $host . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']?($_SERVER['SERVER_PORT']==443?'':':'.$_SERVER['SERVER_PORT']):($_SERVER['SERVER_PORT']==80?'':':'.$_SERVER['SERVER_PORT'])) . '/');
            }
        }
    }

    /**
     * 获取$_GET数据
     *
     * 		// 获取原始数据
     * 		$get_array = HttpIO::GET(null, HttpIO::PARAM_TYPE_OLDDATA);
     *
     * 		// 获取原始数据为URL格式
     * 		$url = HttpIO::GET('url', HttpIO::PARAM_TYPE_URL);
     *
     * @param string $key
     * @param string $type 返回类型，false或不传，则返回原始数据 例：HttpIO::PARAM_TYPE_URL
     */
    public static function GET($key = null, $type = null)
    {
        return HttpIO::_get_format_data('_GET', $key, $type);
    }

    /**
     * 获取$_POST数据
     *
     * @param string $key
     * @param string $type 返回类型，false或不传，则返回原始数据 例：HttpIO::PARAM_TYPE_URL
     */
    public static function POST($key = null, $type = null)
    {
        return HttpIO::_get_format_data('_POST', $key, $type);
    }

    /**
     * 获取$_COOKIE数据
     *
     * @param string $key
     * @param string $type 返回类型，false或不传，则返回原始数据 例：HttpIO::PARAM_TYPE_URL
     */
    public static function COOKIE($key = null, $type = null)
    {
        return HttpIO::_get_format_data('_COOKIE', $key, $type);
    }

    /**
     * 获取$_REQUEST数据
     *
     * @param string $key
     * @param string $type 返回类型，false或不传，则返回原始数据 例：HttpIO::PARAM_TYPE_URL
     */
    public static function REQUEST($key = null, $type = null)
    {
        return HttpIO::_get_format_data('_REQUEST', $key, $type);
    }

    /**
     * 获取 `php://input` 数据
     *
     * 		$url = HttpIO::INPUT('url');
     *
     * @param string $key
     * @param string $type 返回类型
     */
    public static function INPUT($key = null, $type = null)
    {
        return HttpIO::_get_format_data('_INPUT', $key, $type);
    }

    /**
     * 设备是否移动端请求
     *
     * !!! 不支持老式手机浏览器的判断
     *
     * @return bool
     */
    public static function is_mobile()
    {
        $user_agent = strtolower(HttpIO::USER_AGENT);

        if (strpos($user_agent, 'mobile') || strpos($user_agent, 'phone'))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 设备是否支持Touch
     *
     * @use HttpIO::is_mobile
     * @return bool
     */
    public static function is_support_touch()
    {
        if (HttpIO::is_mobile())
        {
            return true;
        }

        $user_agent = strtolower(HttpIO::USER_AGENT);

        if (strpos($user_agent, 'touch'))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 设备是否Iphone
     *
     * @param bool $include_ipod 是否包含iPod设备也算
     * @return bool
     */
    public static function is_iphone($include_ipod = false)
    {
        $user_agent = strtolower(HttpIO::USER_AGENT);

        if (strpos($user_agent, 'iphone') || ($include_ipod && strpos($user_agent, 'ipod')))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 设备是否Ipod
     *
     * @return bool
     */
    public static function is_ipod()
    {
        $user_agent = strtolower(HttpIO::USER_AGENT);

        if (strpos($user_agent, 'ipod'))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 设备是否Ipad
     *
     * @return bool
     */
    public static function is_ipad()
    {
        $user_agent = strtolower(HttpIO::USER_AGENT);

        if (strpos($user_agent, 'ipad'))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 设备是否Apple Watch
     *
     * @return bool
     */
    public static function is_apple_watch()
    {
        $user_agent = strtolower(HttpIO::USER_AGENT);

        if (strpos($user_agent, 'apple watch'))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    protected static function _get_format_data($data_type, $key, $type)
    {
        if ($type == HttpIO::PARAM_TYPE_OLDDATA)
        {
            # 如果是要拿原始拷贝，则加后缀
            $data_type .= '_OLD';
        }
        $data = HttpIO::_key_string(HttpIO::$$data_type, $key);
        if (null===$data) return null;

        if (!$type)
        {
            # 未安全过滤的数据
            $data = HttpIO::sanitize_decode($data);
        }
        elseif ($type==HttpIO::PARAM_TYPE_URL)
        {
            # URL 格式数据
            $data = HttpIO::sanitize_decode($data);
            $data = str_replace(array('<', '>', '\'', "\"", '\''), array('%3C', '%3E', '%27', '%22', '%5C'), $data);
        }
        return $data;
    }

    /**
     * 对字符串进行安全处理
     *
     * @param $str
     */
    public static function sanitize($str)
    {
        if (null===$str)return null;
        if (is_array($str) || is_object($str))
        {
            $data = array();
            foreach ($str as $k => $v)
            {
                $data[$k] = HttpIO::sanitize($v);
            }
        }
        else
        {
            $str = trim($str);
            if (strpos($str, "\r")!==false)
            {
                $str = str_replace(array("\r\n", "\r"), "\n", $str);
            }
            $data = htmlspecialchars($str);
        }
        return $data;
    }

    /**
     * 对字符串进行反向安全处理
     *
     * @param $str
     */
    public static function sanitize_decode($str)
    {
        if (null===$str)return null;
        if (is_array($str) || is_object($str))
        {
            foreach ($str as $k => $v)
            {
                $str[$k] = HttpIO::sanitize_decode($v);
            }
        }
        else
        {
            $str = htmlspecialchars_decode($str);
        }

        return $str;
    }

    /**
     * 页面跳转
     *
     * @param  string  $url 跳转的URL
     * @param  integer $code 状态 : 301, 302, etc
     * @return void
     * @uses   Core::url
     * @uses   HttpIO::send_headers
     */
    public static function redirect($url, $code = 302)
    {
        if (strpos($url, '://')===true)
        {
            // Make the URI into a URL
            $url = Core::url($url);
        }

        // Set the response status
        HttpIO::$status = $code;

        // Set the location header
        HttpIO::$headers['Location'] = $url;

        // Send headers
        HttpIO::send_headers();

        // Stop execution
        exit();
    }


    /**
     * 页面输出header缓存
     *
     * 0表示不缓存
     *
     * @param int $time 缓存时间，单位秒
     */
    public static function set_cache_header($time = 86400)
    {
        $time = (int)$time;

        if ($time>0)
        {
            @header('Cache-Control: max-age='.$time);
            @header('Last-Modified: ' . date('D, d M Y H:i:s \G\M\T'));
            @header('Expires: ' . date('D, d M Y H:i:s \G\M\T', TIME + $time));
            @header('Pragma: cache');
        }
        else
        {
            @header('Cache-Control: private, no-cache, must-revalidate');
            @header('Cache-Control: post-check=0, pre-check=0', false);
            @header('Pragma: no-cache');
            @header("Expires: 0");
        }
    }


    protected static function _key_string($arr, $key)
    {
        if (!is_array($arr))return null;
        if ($key===null || $key===false || !strlen($key) > 0)
        {
            return $arr;
        }
        $keyArr = explode('.', $key);
        foreach ($keyArr as $key)
        {
            if (is_array($arr) && isset($arr[$key]))
            {
                $arr = $arr[$key];
            }
            else
            {
                return null;
            }
        }
        return $arr;
    }

    /**
     * 发送header数据
     */
    public static function send_headers()
    {
        if (!headers_sent())
        {
            if (isset($_SERVER['SERVER_PROTOCOL']))
            {
                // Use the default server protocol
                $protocol = $_SERVER['SERVER_PROTOCOL'];
            }
            else
            {
                // Default to using newer protocol
                $protocol = 'HTTP/1.1';
            }

            if (HttpIO::$status != 200)
            {
                // HTTP status line
                header($protocol . ' ' . HttpIO::$status . ' ' . HttpIO::$messages[HttpIO::$status]);
            }

            foreach (HttpIO::$headers as $name => $value)
            {
                if (is_string($name))
                {
                    // Combine the name and value to make a raw header
                    $value = "{$name}: {$value}";
                }

                // Send the raw header
                header($value, true);
            }
        }
    }

    /**
     * 添加header头信息
     *
     * @param string $key
     * @param string $value
     */
    public static function add_header($key, $value)
    {
        HttpIO::$headers[$key] = $value;
    }

    /**
     * 设置HTTP的状态
     *
     * @param int $status
     */
    public static function status($status=null)
    {
        if (HttpIO::$status)
        {
            HttpIO::$status = $status;
        }

        return HttpIO::$status;
    }

    /**
     * 获取当前控制器
     *
     * @return Controller
     */
    public static function current_controller()
    {
        return HttpIO::$current_controller;
    }

    /**
     * 获取当前页指定参数
     *
     * $id = $request->param('id');
     *
     * @param  string $key key of the value
     * @param  mixed  $default default value if the key is not set
     * @return mixed
     */
    public static function param($key = null, $default = null)
    {
        if (null===$key)
        {
            return HttpIO::$params;
        }

        return isset(HttpIO::$params[$key]) ? HttpIO::$params[$key] : $default;
    }

    /**
     * 返回query构造参数
     *
     * @param  array $params  array of GET parameters
     * @return string
     */
    public static function query(array $params = null)
    {
        if ($params===null)
        {
            // Use only the current parameters
            $params = HttpIO::GET(null, false);
        }
        else
        {
            // Merge the current and new parameters
            $params = array_merge(HttpIO::GET(null, false), $params);
        }

        if (empty($params))
        {
            // No query parameters
            return '';
        }

        $query = http_build_query($params, '', '&');

        // Don't prepend '?' to an empty string
        return ($query==='') ? '' : '?' . $query;
    }

    /**
     * 获取新的URI
     *
     * @param  array $params additional route parameters
     * @return string
     * @uses   HttpIO::param
     * @uses   Route::uri
     */
    public static function uri(array $params = null)
    {
        $params_array = HttpIO::param();

        if (null===Core_Route::$current_route)
        {
            $tmpstr = array();
            if ($params_array['directory'])
            {
                $tmpstr[] = $params_array['directory'];
            }

            # 记录控制器和action的序号
            $controller_index = $action_index = null;
            if ($params_array['controller'])
            {
                $controller = strtolower($params_array['controller']);

                if (preg_match('#^Library_[a-z0-9]+_[a-z0-9]+_([a-z0-9_]+)$#i', $params_array['controller'], $m))
                {
                    # 类库中的控制器
                    $controller = $m[1];
                }

                $pos = strrpos($controller, '_');
                if (false!==$pos)
                {
                    $controller = substr($controller, $pos + 1);
                }

                $controller_index = count($tmpstr);
                if ($controller!='default')
                {
                    $tmpstr[] = $controller;
                }
            }

            $action = strtolower(substr($params_array['action'] , 7));     // Action_Abc -> abc
            if ($action && $action!='default')
            {
                $action_index = count($tmpstr);
                $tmpstr[] = $action;
            }

            if ($params)
            {
                foreach ($params as $k=>$v)
                {
                    $params_array['arguments'][$k] = $v;
                }
            }

            if ($params_array['arguments'])
            {
                foreach ($params_array['arguments'] as $v)
                {
                    $tmpstr[] = $v;
                }
            }

            $count = count($tmpstr);
            for($i=$count-1; $i>=0; $i--)
            {
                if ($tmpstr[$i]==='' || null===$tmpstr[$i])
                {
                    unset($tmpstr[$i]);
                }
                elseif ($tmpstr[$i]=='index')
                {
                    if ($i===$action_index)
                    {
                        unset($tmpstr[$i]);
                    }
                    elseif ($i===$controller_index)
                    {
                        unset($tmpstr[$i]);
                    }
                    else
                    {
                        break;
                    }
                }
                else
                {
                    break;
                }
            }

            return rtrim(implode('/', $tmpstr), '/') . '/';
        }
        else
        {
            if (!isset($params['directory']))
            {
                // Add the current directory
                $params['directory'] = $params_array['directory'];
            }

            if (!isset($params['controller']))
            {
                // Add the current controller
                $params['controller'] = $params_array['controller'];
            }

            if (!isset($params['action']))
            {
                // Add the current action
                $params['action'] = $params_array['action'];
            }

            // Add the current parameters
            $params += $params_array;

            return Core::route()->uri($params);
        }
    }

    /**
     * Create a URL from the current request. This is a shortcut for:
     *
     * echo URL::site($this->request->uri($params), $protocol);
     *
     * @param  array  $protocol  URI parameters
     * @param  mixed  $protocol  protocol string or boolean, adds protocol and domain
     * @return string
     * @uses   URL::site
     * @uses   HttpIO::uri
     */
    public static function url(array $params = null, $protocol = null)
    {
        // Create a URI with the current route and convert it to a URL
        return Core::url(HttpIO::uri($params), $protocol);
    }


    /**
     * CSRF 检测
     *
     * 同一个主域名下的请求将返回 `true` 否则返回 `false`
     *
     * @return boolean
     */
    public static function csrf_check()
    {
        if (!$_SERVER['HTTP_REFERER'])
        {
            return false;
        }

        $info = @parse_url($_SERVER['HTTP_REFERER']);
        if (!$info)return false;

        $host = $info['host'];

        if ($_SERVER['HTTP_HOST']==$host)return true;

        if (HttpIO::get_primary_domain($_SERVER['HTTP_HOST']) == HttpIO::get_primary_domain($host))
        {
            return true;
        }
        else
        {
            return false;
        }
    }


    /**
     * 根据控制器设置参数
     *
     * @param Controller $controller
     */
    public static function set_params_controller($controller)
    {
        if (is_object($controller))
        {
            HttpIO::$params = get_object_vars($controller);
        }
    }

    /**
     * 获取一个域名的主域名
     *
     * 支持传入URL
     *
     *      HttpIO::get_primary_domain('test.myqee.com');              //myqee.com
     *
     *      HttpIO::get_primary_domain('http://v3.myqee.com/docs/');   //myqee.com
     *
     * @param string $host
     * @return string
     */
    public static function get_primary_domain($host)
    {
        $host = strtolower($host);
        if(false!==strpos($host, '/'))
        {
            $parse = @parse_url($host);
            $host  = $parse['host'];
        }

        $top_level_domain = array
        (
            'com',
            'edu',
            'gov',
            'int',
            'mil',
            'net',
            'org',
            'biz',
            'info',
            'pro',
            'name',
            'museum',
            'coop',
            'aero',
            'asia',
            'xxx',
            'idv',
            'mobi',
        );

        $str='';
        foreach($top_level_domain as $v)
        {
            $str .= ($str ? '|' : '') . $v;
        }

        $matchstr='[^\.]+\.(?:('.$str.')|\w{2}|(('.$str.')\.\w{2}))$';
        if(preg_match("/". $matchstr ."/ies", $host, $matchs))
        {
            $domain = $matchs['0'];
        }
        else
        {
            $host_arr = explode('.', $host);
            $host_c   = count($host_arr) - 1;
            if (strlen($host_arr[$host_c])==2)
            {
                # 2个字母的后缀，比如 t.tt, net.cn
                $domain = $host_arr[$host_c-1] .'.'. $host_arr[$host_c];
            }
            else
            {
                $domain = $host;
            }
        }

        return $domain;
    }

    /**
     * 分块输出
     *
     * @param $msg
     */
    public static function push_chunk($msg)
    {
        if (!HttpIO::$IS_CHUNK_START)
        {
            HttpIO::chunk_start();
        }

        if (is_array($msg))
        {
            if (defined('JSON_UNESCAPED_UNICODE'))
            {
                $msg = json_encode($msg, JSON_UNESCAPED_UNICODE);
            }
            else
            {
                $msg = json_encode($msg);
            }
        }
        else
        {
            $msg = trim((string)$msg);
        }

        echo dechex(strlen($msg)), "\r\n", $msg, "\r\n";

        flush();
    }

    /**
     * 开始分开输出
     *
     * @param int $time_limit
     */
    public static function chunk_start($time_limit = 0)
    {
        if (true === HttpIO::$IS_CHUNK_START)return;

        HttpIO::$IS_CHUNK_START = true;

        set_time_limit($time_limit);
        Core::close_buffers(false);
        header('Content-Type: text/plain');

        echo str_pad('', 1024), "\r\n";
        flush();
    }

    /**
     * 分开输出结束，页面结束
     *
     * !!! 执行此方法后将执行 `exit()`，程序将结束运行
     */
    public static function chunk_end()
    {
        echo "0\r\n\r\n";
        exit;
    }
}