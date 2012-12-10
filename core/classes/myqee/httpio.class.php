<?php

if (!defined('_HTTPIO_METHOD'))
{
    define('_HTTPIO_METHOD',$_SERVER["REQUEST_METHOD"]);

    if ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 'xmlhttprequest'===strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) )
    {
        $is_ajax = true;
    }
    elseif ( isset($_GET['_ajax']) && $_GET['_ajax']=='true' )
    {
        $is_ajax = true;
    }
    else
    {
        $is_ajax = false;
    }
    define('_HTTPIO_IS_AJAX',$is_ajax);
    unset($is_ajax);

    define('_HTTPIO_PROTOCOL',Core::protocol());

    if ( isset($_SERVER['HTTP_X_FORWARDED_FOR']) )
    {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    elseif ( isset($_SERVER['HTTP_CLIENT_IP']) )
    {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    elseif ( isset($_SERVER['REMOTE_ADDR']) )
    {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    else
    {
        $ip = '';
    }

    list($ip) = explode(',', $ip);    //启用代理时多IP中取第一个
    define('_HTTPIO_IP',trim($ip));
    unset($ip);

    if ( isset($_SERVER['HTTP_REFERER']) )
    {
        $referrer = $_SERVER['HTTP_REFERER'];
    }
    else
    {
        $referrer = null;
    }
    define('_HTTPIO_REFERRER',$referrer);
    unset($referrer);

    if ( isset($_SERVER['HTTP_USER_AGENT']) )
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
    }
    else
    {
        $user_agent = '';
    }

    define('_HTTPIO_USER_AGENT',$user_agent);
    unset($user_agent);
}


/**
 * MyQEE HTTP INPUT AND OUTPUT
 *
 * @author     jonwang(jonwang@myqee.com)
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2012 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class MyQEE_HttpIO
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
     * @var  string  protocol: http, https, ftp, cli, etc
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
     * @var array
     */
    public static $headers = array();

    /**
     * 页码状态
     * @var int
     */
    public static $status = 200;

    /**
     * 当前页面URI
     * @var string
     */
    public static $uri;

    /**
     * 当前页码参数
     * @var array
     */
    public static $params;

    /**
     * 控制器对象寄存器
     * @var array
     */
    protected static $controlers = array();

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

    public function __construct()
    {

    }

    /**
     * 执行初始化，只执行一次
     */
    public static function setup()
    {
        static $run = null;
        if ( null === $run )
        {
            $run = true;
            if ( !IS_CLI )
            {
                # 记录一个正真的原始拷贝数据
                HttpIO::$_GET_OLD     = $_GET;
                HttpIO::$_POST_OLD    = $_POST;
                HttpIO::$_COOKIE_OLD  = $_COOKIE;
                HttpIO::$_REQUEST_OLD = $_REQUEST;

                # XSS安全处理
                $_GET     = HttpIO::htmlspecialchars($_GET);
                $_POST    = HttpIO::htmlspecialchars($_POST);
                $_COOKIE  = HttpIO::htmlspecialchars($_COOKIE);
                $_REQUEST = HttpIO::htmlspecialchars($_REQUEST);

                # 隐射
                HttpIO::$_GET     = & $_GET;
                HttpIO::$_POST    = & $_POST;
                HttpIO::$_COOKIE  = & $_COOKIE;
                HttpIO::$_REQUEST = & $_REQUEST;

                HttpIO::$uri = Core::$path_info;
            }
        }
    }

    /**
     * 获取$_GET数据
     *
     * 		// 获取原始数据
     * 		$get_array = HttpIO::GET(null,HttpIO::PARAM_TYPE_OLDDATA);
     *
     * 		// 获取原始数据为URL格式
     * 		$url = HttpIO::GET('url',HttpIO::PARAM_TYPE_URL);
     *
     * @param string $key
     * @param string 返回类型，false或不传，则返回原始数据 例：HttpIO::PARAM_TYPE_URL
     */
    public static function GET($key = null, $type = null)
    {
        return HttpIO::_get_format_data('_GET', $key, $type);
    }

    /**
     * 获取$_POST数据
     *
     * @param string $key
     * @param string 返回类型，false或不传，则返回原始数据 例：HttpIO::PARAM_TYPE_URL
     */
    public static function POST($key = null, $type = null)
    {
        return HttpIO::_get_format_data('_POST', $key, $type);
    }

    /**
     * 获取$_COOKIE数据
     *
     * @param string $key
     * @param string 返回类型，false或不传，则返回原始数据 例：HttpIO::PARAM_TYPE_URL
     */
    public static function COOKIE($key = null, $type = null)
    {
        return HttpIO::_get_format_data('_COOKIE', $key, $type);
    }

    /**
     * 获取$_REQUEST数据
     *
     * @param string $key
     * @param string 返回类型，false或不传，则返回原始数据 例：HttpIO::PARAM_TYPE_URL
     */
    public static function REQUEST($key = null, $type = null)
    {
        return HttpIO::_get_format_data('_REQUEST', $key, $type);
    }

    protected static function _get_format_data($datatype, $key, $type)
    {
        if ( $type == HttpIO::PARAM_TYPE_OLDDATA )
        {
            # 如果是要拿原始拷贝，则加后缀
            $datatype .= '_OLD';
        }
        $data = HttpIO::_key_string(HttpIO::$$datatype, $key);
        if ( null === $data ) return null;

        if ( ! $type )
        {
            # 未安全过滤的数据
            $data = HttpIO::htmlspecialchars_decode($data);
        }
        elseif ( $type == HttpIO::PARAM_TYPE_URL )
        {
            # URL 格式数据
            $data = HttpIO::htmlspecialchars_decode($data);
            $data = str_replace(array('<', '>', '\'', "\"", '\''), array('%3C', '%3E', '%27', '%22', '%5C'), $data);
        }
        return $data;
    }

    /**
     * 对字符串进行安全处理
     *
     * @param $str
     */
    public static function htmlspecialchars($str)
    {
        if ( null === $str ) return null;
        if ( is_array($str) || is_object($str) )
        {
            $data = array();
            foreach ( $str as $k => $v )
            {
                $data[$k] = HttpIO::htmlspecialchars($v);
            }
        }
        else
        {
            $str = trim($str);
            if ( strpos($str, "\r") !== false )
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
    public static function htmlspecialchars_decode($str)
    {
        if ( null === $str ) return null;
        if ( is_array($str) || is_object($str) )
        {
            foreach ( $str as $k => $v )
            {
                $str[$k] = HttpIO::htmlspecialchars_decode($v);
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
     * @param   string   redirect location
     * @param   integer  status code: 301, 302, etc
     * @return  void
     * @uses    Core_url::site
     * @uses    HttpIO::send_headers
     */
    public static function redirect($url, $code = 302)
    {
        if ( strpos($url, '://') === true )
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
            @header('Last-Modified: ' . date( 'D, d M Y H:i:s \G\M\T' ));
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
        if ( ! is_array($arr) ) return null;
        if ( $key === null || $key === false || ! strlen($key) > 0 )
        {
            return $arr;
        }
        $keyArr = explode('.', $key);
        foreach ( $keyArr as $key )
        {
            if ( is_array($arr) && isset($arr[$key]) )
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
        if ( ! headers_sent() )
        {
            if ( isset($_SERVER['SERVER_PROTOCOL']) )
            {
                // Use the default server protocol
                $protocol = $_SERVER['SERVER_PROTOCOL'];
            }
            else
            {
                // Default to using newer protocol
                $protocol = 'HTTP/1.1';
            }

            if ( HttpIO::$status != 200 )
            {
                // HTTP status line
                header($protocol . ' ' . HttpIO::$status . ' ' . HttpIO::$messages[HttpIO::$status]);
            }

            foreach ( HttpIO::$headers as $name => $value )
            {
                if ( is_string($name) )
                {
                    // Combine the name and value to make a raw header
                    $value = "{$name}: {$value}";
                }

                // Send the raw header
                header($value, TRUE);
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
     * 执行请求，并将输出结果返回
     *
     * @param string $path_info 路径信息
     * @param boolean $print 是否直接输出
     * @param boolean $use_route 是否尝试在路由中搜索
     * @param boolean $is_internal 是否内部调用，默认：否
     * @param string $controller_dir 指定控制器目录，命令行下默认为shell，网站运行为controllers
     * @return string
     */
    public static function execute($uri, $print = true, $use_route = true, $is_internal = false)
    {
        $ob_open = false;
        if ( ! $print && ! IS_CLI )
        {
            ob_start();
            $ob_open = true;
        }
        $params = false;
        # 路由设置
        if ( IS_CLI != true && true === $use_route && Core::config('core.route') && ($route = Core::route()->get($uri)) )
        {
            $params = $route;
            # 默认控制器
            if ( $params['controller'] )
            {
                $params['controller'] = str_replace('/', '_', $params['controller']);
            }
            else
            {
                $params['controller'] = Core::config('core.default_controller');
            }

            $dir = 'controllers';

            if ( IS_SYSTEM_MODE )
            {
                $file = '[system]/'.$params['controller'];
            }
            elseif ( IS_CLI )
            {
                $file = '[shell]/'.$params['controller'];
            }
            elseif ( IS_ADMIN_MODE )
            {
                $file = '[admin]/'.$params['controller'];
            }
            else
            {
                $file = $params['controller'];
            }

            if ( !Core::find_file($dir, $file, null, true) )
            {
                Core::debug()->error($params['controller'],'controller not found');
                if($ob_open)ob_end_clean();
                return false;
            }
            $is_use_route = true;
            if ( Core_Route::$last_route )
            {
                Core_Route::$current_route = Core_Route::$last_route;
                Core_Route::$route_list[] = Core_Route::$current_route;
            }
        }
        else
        {
            $params = HttpIO::find_controller($uri,$is_internal);

            if ( !IS_CLI && null===HttpIO::$uri && HttpIO::METHOD=='GET' && !$is_internal && isset($params['need_redirect']) && $params['need_redirect']==true)
            {
                # 页面结尾自动加/
                $request = explode('?',$_SERVER['REQUEST_URI'],2);
                Core::close_buffers(false);
                HttpIO::redirect($request[0].'/'.(isset($request[1])?'?'.$request[1]:''),301);
                exit;
            }

            $is_use_route = false;
        }
        if ( false === $params )
        {
            Core::debug()->error('page not found');
            if ($ob_open)ob_end_clean();
            return false;
        }

        # 初始化$uri
        if ( null === HttpIO::$uri ) HttpIO::$uri = $uri;
        if ( null === HttpIO::$params ) HttpIO::$params = $params;

        # 控制器名称
        $controller_name = 'Controller_' . $params['controller'];

        # 参数
        $arguments = isset($params['arguments']) ? $params['arguments'] : array();

        if (IS_SYSTEM_MODE)
        {
            $params['arguments'] = @unserialize(HttpIO::POST('data',HttpIO::PARAM_TYPE_OLDDATA));
        }

        if ( $is_internal )
        {
            $prefix = 'sub_action';
        }
        else
        {
            $prefix = 'action';
        }

        # 方法
        $action_name = $params['action'];
        if ( ! $action_name )
        {
            $action_name = $prefix . '_' . Core::config('core.default_action');
        }
        else
        {
            $action_name = $prefix . '_' . $action_name;
        }

        # 如果不存在控制器类则抛404页面
        if ( !class_exists($controller_name, false) )
        {
            Core::debug()->error('controller ' . $controller_name . ' not exists');
            if ($ob_open)ob_end_clean();
            return false;
        }

        # 构造新控制器
        if ( ! isset(HttpIO::$controlers[$controller_name]) )
        {
            $ref_class = new ReflectionClass($controller_name);
            if ( $ref_class->isInstantiable() )
            {
                HttpIO::$controlers[$controller_name] = new $controller_name();
            }
            else
            {
                if (IS_DEBUG)Core::debug()->error('controller ' . $controller_name . ' can not instantiable.');
                return false;
            }
        }
        $old_current_controller = HttpIO::$current_controller;
        HttpIO::$current_controller = $controller = HttpIO::$controlers[$controller_name];

        # 存控制器的数据
        static $obj_params = array();
        if ( ! isset($obj_params[$controller_name]) || ! is_array($obj_params[$controller_name]) )
        {
            $obj_params[$controller_name] = array();
        }
        if ( method_exists($controller, '_callback_get_vars') )
        {
            # 将控制器参数记录下来
            $obj_params[$controller_name][] = $controller->_callback_get_vars();
        }
        if ( method_exists($controller, '_callback_set_vars') )
        {
            # 将参数传递给控制器
            $controller->_callback_set_vars($params);
        }

        if ( ! $is_internal && ! method_exists($controller, $action_name) )
        {
            $action_name = $prefix . '_default';
            if ( ! method_exists($controller, $action_name) )
            {
                $action_name = '__call';
                $arguments = array($action_name, $arguments);
                if ( ! method_exists($controller, $action_name) )
                {
                    if (IS_DEBUG)Core::debug()->error('controller ' . $controller_name . ' action ' . $action_name . ' not exists');
                    if ($ob_open)ob_end_clean();
                    return false;
                }
            }
        }

        # Method is Public?
        $ispublicmethod = new ReflectionMethod($controller, $action_name);
        if ( ! $ispublicmethod->isPublic() )
        {
            if (IS_DEBUG)Core::debug()->error('controller ' . $controller_name . ' action ' . $action_name . ' is not public');
            if ($ob_open)ob_end_clean();
            return false;
        }

        if ( IS_DEBUG && Core::debug()->profiler()->is_open() )
        {
            //执行一个
            $benchmark = Core::debug()->profiler()->start('Controller Execute',$uri);
        }

        if ( ! $is_internal )
        {
            if ( method_exists($controller, 'before') )
            {
                $controller->before();
            }
        }

        # 执行方法
        $count_arguments = count($arguments);
        switch ( $count_arguments )
        {
            case 0 :
                $controller->$action_name();
                break;
            case 1 :
                $controller->$action_name($arguments[0]);
                break;
            case 2 :
                $controller->$action_name($arguments[0], $arguments[1]);
                break;
            case 3 :
                $controller->$action_name($arguments[0], $arguments[1], $arguments[2]);
                break;
            case 4 :
                $controller->$action_name($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
                break;
            default :
                # Resort to using call_user_func_array for many segments
                call_user_func_array(array($controller, $action_name), $arguments);
                break;
        }

        if ( ! $is_internal )
        {
            if ( method_exists($controller, 'after') )
            {
                $controller->after();
            }
        }

        if ( IS_DEBUG && isset($benchmark) )
        {
            //执行一个
            Core::debug()->profiler()->stop();
        }

        # 将原来的数据重新设置回去
        if ( method_exists($controller, '_callback_set_vars') )
        {
            if ( is_array($obj_params[$controller_name]) )
            {
                $tmp_params = array_pop($obj_params[$controller_name]);
                $controller->_callback_set_vars($tmp_params);
            }
        }

        HttpIO::$current_controller = $old_current_controller;
        unset($old_current_controller);
        unset($controller);
        if ( ! count($obj_params[$controller_name]) )
        {
            unset(HttpIO::$controlers[$controller_name]);
        }

        if ( true == $is_use_route )
        {
            # 路由列队
            array_pop(Core_Route::$route_list);
            if ( Core_Route::$route_list )
            {
                end(Core_Route::$route_list);
                $key = key(Core_Route::$route_list);
                Core_Route::$last_route = Core_Route::$current_route = Core_Route::$route_list[$key];
            }
            else
            {
                Core_Route::$route_list = null;
            }
        }

        if ( !$print && !IS_CLI )
        {
            $output = ob_get_clean();
            return $output;
        }
        else
        {
            if ($ob_open)ob_end_clean();
            return '';
        }
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
     * 查找控制器文件
     *
     * @param string $path_info
     * @return array 返回控制器后的参数数组
     */
    protected static function find_controller($uri,$is_internal =false)
    {
        # 包含目录
        if ( isset(Core::$file_list[Core::$project]) )
        {
            $includepath = array(DIR_BULIDER . DS . Core::$project . DS);
        }
        else
        {
            $includepath = Core::$include_path;
        }

        $ext = '.controller' . EXT;

        if ( IS_SYSTEM_MODE )
        {
            # 系统调用目录
            $confolder = 'controllers'.DS.'[system]';
        }
        else if ( IS_CLI )
        {
            $confolder = 'controllers'.DS.'[shell]';
        }
        else if ( IS_ADMIN_MODE )
        {
            $confolder = 'controllers'.DS.'[admin]';
        }
        else
        {
            $confolder = 'controllers';
        }

        # 处理掉2边可能有/的URI
        $new_uri = trim('/'.trim($uri,'/'));

        # 分割参数
        $arguments = explode('/', $new_uri);

        # 默认控制器
        $default_controller = strtolower(Core::config('core.default_controller'));

        # 记录存在的目录
        $pathArr = array();

        # 控制器目录
        $controller_path = '';

        # 临时包含文件
        $tmp_includepath = $includepath;

        # 寻找可能存在的文件夹
        foreach ( $arguments as $argument )
        {
            $controller_path .= $argument . '/';
            foreach ( $tmp_includepath as $k=>$path )
            {
                # 获取完整路径
                $tmppath = rtrim($path . $confolder . DS . str_replace('/',DS,ltrim($controller_path,'/')) , DS) . DS;
                if ( is_dir($tmppath) )
                {
                    $pathArr['/'.trim($controller_path,'/')][] = $tmppath;
                }
                else
                {
                    # 没有目录则排除
                    unset($tmp_includepath[$k]);
                }
            }
        }

        if (!$pathArr)return false;

        # 倒序，因为最深目录优先级最高
        $pathArr = array_reverse($pathArr);

        // $pathArr 格式类似：
        // Array
        // (
        //    [/test/] => Array
        //        (
        //            [0] => D:\php\myqee_v2\projects\myqee\controllers\test\
        //        )
        //    [/] => Array
        //        (
        //            [0] => D:\php\myqee_v2\projects\myqee\controllers\
        //            [1] => D:\php\myqee_v2\libraries\MyQEE\Core\controllers\
        //        )
        // )
        //
        // 例1
        // URI:/test/abc 筛选控制器优先级排列如下：
        // [no] [file]                                  [action]
        // ----------------------------------------------------------------------------------------
        // 1    test/abc/index.controller.php           default        ->  此时若url结尾没有/且为GET方式则跳转到：/test/abc/
        // ↓
        // 2    test/abc.controller.php                 index          ->  此时若url结尾没有/且为GET方式则跳转到：/test/abc/
        // ↓
        // 3    test/index.controller.php               abc
        // ↓
        // 4    test.controller.php                     abc
        // 5    index.controller.php                    test
        // ↓
        // 6    test/abc.controller.php                 default
        // 7    test.controler.php                      default
        //
        //
        // 例2
        // URL:/test/abc/def 筛选控制器优先级排列如下：
        // [file]                                  [action]
        // ----------------------------------------------------------------------------------------
        // test/abc/def/index.controller.php       default        ->  此时若url结尾没有/且为GET方式则跳转到：/test/abc/def/
        // ↓
        // test/abc/def.controller.php             index          ->  此时若url结尾没有/且为GET方式则跳转到：/test/abc/def/
        // ↓
        // test/abc/index.controller.php           def
        // ↓
        // test/abc.controller.php                 def
        // test/index.controller.php               abc
        // ↓
        // test.controller.php                     abc
        // index.controller.php                    test
        // ↓
        // test/abc/def.controller.php             default
        // test/abc.controller.php                 default
        // test.controller.php                     default

        # 处理根目录下index.controller.php文件的default控制器，即例1中no=1部分
        if ( key($pathArr)===$new_uri )
        {
            # 当前的URI存在最深的根目录
            $uripaths = current($pathArr);
            foreach ( $uripaths as $item)
            {
                $tmp_file = $item . $default_controller . $ext;
                if (is_file($tmp_file))
                {
                    $data = array
                    (
                        'file'       => $tmp_file,
                        'controller' => trim(str_replace('/','_',trim($new_uri,'/')).'_'.$default_controller,'_'),
                        'function'   => 'default',
                    );

                    if ( HttpIO::check_controller_method($data,$is_internal) )
                    {
                        return array
                        (
                            'controller'    => $data['controller'],
                            'action'        => '',
                            'arguments'     => array(),
                            'uri'           => $new_uri,
                            'need_redirect' => substr($uri,-1)=='/'?false:true,
                        );
                    }
                }
            }
            exit;
        }

        # 处理例1中no=2部分
        $new_uri_sub2 = substr($new_uri,0,strrpos($new_uri,'/'));
        if ($new_uri_sub2=='')$new_uri_sub2='/';
        if ( key($pathArr)===$new_uri_sub2 )
        {
            $con_name = substr($new_uri, strrpos($new_uri,'/')+1);
            # 当前的URI存在最深的根目录
            $uripaths = current($pathArr);
            foreach ( $uripaths as $item)
            {
                $tmp_file = $item . $con_name . $ext;
                if (is_file($tmp_file))
                {
                    $data = array
                    (
                        'file'       => $tmp_file,
                        'controller' => trim(str_replace('/','_',trim($new_uri_sub2,'/')).'_'.$con_name,'_'),
                        'function'   => 'index',
                    );

                    if ( HttpIO::check_controller_method($data,$is_internal) )
                    {
                        return array
                        (
                            'controller'    => $data['controller'],
                            'action'        => 'index',
                            'arguments'     => array(),
                            'uri'           => $new_uri,
                            'need_redirect' => substr($uri,-1)=='/'?false:true,
                        );
                    }
                }
            }
        }

        # 处理例1中3-5部分，寻找可能存在的控制器文件
        if($pathArr)foreach ( $pathArr as $left_uri=>$all_path )
        {
            $tmpuri = trim(substr($new_uri,strlen($left_uri)),'/');
            $arguments_arr = explode('/',$tmpuri);
            $ar_1 = array_shift($arguments_arr);
            foreach ($all_path as $path)
            {
                if ( $arguments_arr )
                {
                    $arguments_arr2 = $arguments_arr;
                    $ar_2 = array_shift($arguments_arr2);
                    $tmp_file = $path . $ar_1 . $ext;

                    if ( is_file($tmp_file) )
                    {
                        $data = array
                        (
                            'file'       => $tmp_file,
                            'controller' => str_replace('/','_',ltrim($left_uri.'/','/') . $ar_1),
                            'function'   => $ar_2,
                        );

                        if ( HttpIO::check_controller_method($data,$is_internal) )
                        {
                            return array
                            (
                                'controller'    => $data['controller'],
                                'action'        => $ar_2,
                                'arguments'     => $arguments_arr2,
                                'uri'           => rtrim($left_uri,'/') .'/'.$ar_1.'/'.$ar_2,
                            );
                        }

                    }
                }

                $tmp_file = $path . $default_controller . $ext;
                if ( is_file($tmp_file) )
                {
                    $data = array
                    (
                        'file'       => $tmp_file,
                        'controller' => str_replace('/','_',ltrim($left_uri.'/','/') . $default_controller),
                        'function'   => $ar_1,
                    );
                    if ( HttpIO::check_controller_method($data,$is_internal) )
                    {
                        return array
                        (
                            'controller'    => $data['controller'],
                            'action'        => $ar_1,
                            'arguments'     => $arguments_arr,
                            'uri'           => rtrim($left_uri,'/') .'/'.$ar_1.'/',
                        );
                    }

                }

            }
        }

        # 处理例1中6-7部分，寻找是否含有默认控制
        if($pathArr)foreach ( $pathArr as $left_uri=>$all_path )
        {
            $tmpuri = trim(substr($new_uri,strlen($left_uri)),'/');
            $arguments_arr = explode('/',$tmpuri);
            $ar_1 = array_shift($arguments_arr);
            foreach ($all_path as $path)
            {
                $tmp_file = $path . $ar_1 . $ext;
                if ( is_file($tmp_file) )
                {
                    $data = array
                    (
                        'file'       => $tmp_file,
                        'controller' => str_replace('/','_',ltrim($left_uri.'/','/') . $ar_1),
                        'function'   => 'default',
                    );
                    if ( HttpIO::check_controller_method($data,$is_internal) )
                    {
                        return array
                        (
                            'controller'    => $data['controller'],
                            'action'        => '',
                            'arguments'     => $arguments_arr,
                            'uri'           => rtrim($left_uri,'/') .'/'.$ar_1.'/',
                        );
                    }

                    break;
                }
            }
        }

        return false;
    }

    /**
     * 检查控制器方法
     * @param array $data
     * @param boolean $is_internal 是否内部调用
     */
    protected static function check_controller_method($data,$is_internal)
    {
        /**
        $data = Array
        (
            [file] => D:\php\myqee_v2\projects\myqee\controllers\test\abc\index.controller.php
            [controller] => test_abc_index
            [function] => def
        )
         */
        $class_name = 'Controller_'.$data['controller'];
        if ( !class_exists($class_name,false) )
        {
            # 防止加载已经存在的对象
            require_once ($data['file']);
        }

        $action_name = ($is_internal?'sub_action':'action').'_'.$data['function'];

        if ( !class_exists($class_name,false) )
        {
            return false;
        }
        if ( method_exists($class_name, $action_name ) )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 获取当前页指定参数
     *
     * $id = $request->param('id');
     *
     * @param   string  key of the value
     * @param   mixed    default value if the key is not set
     * @return  mixed
     */
    public static function param($key = null, $default = null)
    {
        if ( $key === null )
        {
            // Return the full array
            return HttpIO::$params;
        }

        return isset(HttpIO::$params[$key]) ? HttpIO::$params[$key] : $default;
    }

    /**
     * 返回query构造参数
     *
     * @param   array   array of GET parameters
     * @return  string
     */
    public static function query(array $params = null)
    {
        if ( $params === null )
        {
            // Use only the current parameters
            $params = HttpIO::GET(null, false);
        }
        else
        {
            // Merge the current and new parameters
            $params = array_merge(HttpIO::GET(null, false), $params);
        }

        if ( empty($params) )
        {
            // No query parameters
            return '';
        }

        $query = http_build_query($params, '', '&');

        // Don't prepend '?' to an empty string
        return ($query === '') ? '' : '?' . $query;
    }

    /**
     * 获取新的URI
     *
     * @param   array   additional route parameters
     * @return  string
     * @uses    Route::uri
     */
    public function uri(array $params = null)
    {
        if ( null === Core_Route::$current_route )
        {
            $tmpstr = array();
            if ( HttpIO::$params['directory'] )
            {
                $tmpstr[] = HttpIO::$params['directory'];
            }
            if ( HttpIO::$params['controller'] )
            {
                $tmpstr[] = str_replace('_','/',HttpIO::$params['controller']);
            }
            if ( HttpIO::$params['action'] )
            {
                $tmpstr[] = HttpIO::$params['action'];
            }
            $tmp_params = HttpIO::$params;
            //            if (!HttpIO::$params['action']){
            //                $tmp_params['action'] = array_shift($params);
            //                if (!HttpIO::$params['controller']){
            //                    $tmp_params['controller'] = array_shift($params);
            //                }
            //            }
            if ( count($tmp_params['arguments']) > count($params) )
            {
                $tmp_params['arguments'] = array_slice($tmp_params['arguments'], 0, - count($params)) + $params;
            }
            else
            {
                $tmp_params['arguments'] = $params;
            }
            if ( $tmp_params['arguments'] )
            {
                foreach ( $tmp_params['arguments'] as $v )
                {
                    $tmpstr[] = $v;
                }
            }
            return implode('/', $tmpstr);
        }
        else
        {
            if ( ! isset($params['directory']) )
            {
                // Add the current directory
                $params['directory'] = HttpIO::$params['directory'];
            }

            if ( ! isset($params['controller']) )
            {
                // Add the current controller
                $params['controller'] = HttpIO::$params['controller'];
            }

            if ( ! isset($params['action']) )
            {
                // Add the current action
                $params['action'] = HttpIO::$params['action'];
            }

            // Add the current parameters
            $params += HttpIO::$params;

            return Core::route()->uri($params);
        }
    }

    /**
     * Create a URL from the current request. This is a shortcut for:
     *
     * echo URL::site($this->request->uri($params), $protocol);
     *
     * @param   string   route name
     * @param   array    URI parameters
     * @param   mixed    protocol string or boolean, adds protocol and domain
     * @return  string
     * @since   3.0.7
     * @uses    URL::site
     */
    public static function url(array $params = null, $protocol = null)
    {
        // Create a URI with the current route and convert it to a URL
        return Core::url(HttpIO::uri($params), $protocol);
    }

}