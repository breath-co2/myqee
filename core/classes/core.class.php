<?php

/**
 * 返回一个静态资源的URL路径. Core::url_assets() 的别名
 *
 *   echo url_assets('js/global.js');
 *
 * @param string $uri
 */
function url_assets($uri='')
{
    return Core::url_assets($uri);
}

/**
 * 返回一个URL. Core::url() 的别名
 *
 *   echo url();    //返回首页地址
 *
 * @param string $uri
 * @return string
 */
function url($uri='')
{
    return Core::url($uri);
}

/**
 * 读取配置数据. Core::config() 的别名
 *
 *   echo config('core');    //返回核心配置
 *
 * @param string $key
 * @return string
 */
function config($key=null)
{
    return Core::config($key);
}


/**
 * MyQEE 核心类
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
abstract class Core_Core extends Bootstrap
{
    /**
     * MyQEE版本号
     *
     * @var string
     */
    const VERSION = '3.1';

    /**
     * 版本发布状态
     *
     * stable, rc1, rc2, beta1, beta2, ...
     *
     * @var string
     */
    const RELEASE  = 'stable';

    /**
     * 项目开发者
     *
     * @var string
     */
    const CODER = '呼吸二氧化碳 <jonwang@myqee.com>';

    /**
     * 页面编码
     *
     * @var string
     */
    public static $charset = 'utf-8';

    /**
     * 页面传入的PATHINFO参数
     *
     * @var array
     */
    public static $arguments = array();

    /**
     * 页面输出内容
     *
     * @var string
     */
    public static $output = '';

    /**
     * 自动加载时各个文件夹后缀
     *
     * 例如，classes目录为.class，则文件实际后缀为.class.php
     * 注意，只有对EXT常理设置的后缀文件有效，默认情况下EXT=.php
     *
     * @var array
     */
    public static $autoload_dir_ext = array
    (
        'config'       => '.config',
        'classes'      => '.class',
        'controllers'  => '.controller',
        'models'       => '.model',
        'orm'          => '.orm',
        'views'        => '.view',
    );

    /**
     * 缓冲区包含数
     * @var int
     */
    protected static $buffer_level = 0;

    /**
     * 页面在关闭前需要执行的方法列队
     * 通过Core::register_shutdown_function()设置
     * @var array
     */
    protected static $shutdown_function = array();

    /**
     * getFactory获取的对象寄存器
     * @var array
     */
    protected static $instances = array();

    /**
     * 执行Core::close_all_connect()方法时会关闭链接的类和方法名的列队
     *
     * 可通过Core::add_close_connect_class()方法进行设置增加
     *
     *     array
     *     (
     *         'Database' => 'close_all_connect',
     *     );
     *
     * @var array
     */
    protected static $close_connect_class_list = array();

    /**
     * import_library回调函数列表
     *
     * @var array
     */
    protected static $import_library_callback = array();

    /**
     * change_project回调函数列表
     *
     * @var array
     */
    protected static $change_project_callback = array();

    /**
     * 使用 Core::url() 会附带的参数列表
     *
     * @var array
     */
    protected static $_url_auto_args = array();

    /**
     * 系统启动
     *
     * @param boolean $auto_execute 是否直接运行
     */
    public static function setup($auto_execute = true)
    {
        static $run = null;

        if (null===$run)
        {
            $run = true;

            Core::$charset = Core::$config['charset'];

            if (!IS_CLI)
            {
                # 输出powered by信息
                $x_powered_by = (isset(Core::$config['hide_x_powered_by_header']) && Core::$config['hide_x_powered_by_header']) ? Core::$config['hide_x_powered_by_header'] : false;

                if (is_string($x_powered_by))
                {
                    $str = 'X-Powered-By: ' . trim(str_replace(array("\r", "\n"), '', $x_powered_by));
                }
                else if (!$x_powered_by)
                {
                    $str = 'X-Powered-By: PHP/' . PHP_VERSION . ' MyQEE/' . Core::VERSION .'('. Core::RELEASE .')';
                }
                else
                {
                    $str = null;
                }

                if ($str)
                {
                    header($str);
                }
            }

            if (IS_DEBUG)
            {
                Core::debug()->info('SERVER IP:' . $_SERVER["SERVER_ADDR"] . (function_exists('php_uname')?'. SERVER NAME:' . php_uname('a') : ''));

                if (Core::$project)
                {
                    Core::debug()->info('project: ' . Core::$project);
                }

                if (IS_ADMIN_MODE)
                {
                    Core::debug()->info('admin mode');
                }

                if (IS_REST_MODE)
                {
                    Core::debug()->info('RESTFul mode');
                }

                Core::debug()->group('include path');
                foreach ( Core::include_path() as $value )
                {
                    Core::debug()->log(Core::debug_path($value));
                }
                Core::debug()->groupEnd();
            }

            if ((IS_CLI || IS_DEBUG) && class_exists('ErrException', true))
            {
                # 注册脚本
                register_shutdown_function(array('ErrException', 'shutdown_handler'));
                # 捕获错误
                set_exception_handler(array('ErrException', 'exception_handler'));
                set_error_handler(array('ErrException', 'error_handler'), error_reporting());
            }
            else
            {
                # 注册脚本
                register_shutdown_function(array('Core', 'shutdown_handler'));
                # 捕获错误
                set_exception_handler(array('Core', 'exception_handler'));
                set_error_handler(array('Core', 'error_handler'), error_reporting());
            }

            if (!IS_CLI)
            {
                # 初始化 HttpIO 对象
                HttpIO::setup();
            }

            # 注册输出函数
            register_shutdown_function(array('Core', '_output_body'));

            if (true===IS_SYSTEM_MODE)
            {
                if (false===Core::check_system_request_allow())
                {
                    # 内部请求验证不通过
                    Core::show_500('system request hash error');
                }
            }

            if (IS_DEBUG && isset($_REQUEST['debug']) && class_exists('Profiler', true))
            {
                Profiler::setup();
            }

            if (!defined('URL_ASSETS'))
            {
                /**
                 * 静态文件URL地址前缀
                 *
                 * @var string
                 */
                define('URL_ASSETS', rtrim(Core::config('url.assets', Core::url('/assets/')), '/') . '/');
            }
        }

        if ($auto_execute)
        {
            Core::run();
        }
    }

    protected static function run()
    {
        if (IS_CLI || IS_SYSTEM_MODE)
        {
            Core::execute(Core::$path_info);
        }
        else
        {
            ob_start();

            try
            {
                Core::execute(Core::$path_info);
            }
            catch (Exception $e)
            {
                $code = $e->getCode();

                if (404===$code || E_PAGE_NOT_FOUND===$code)
                {
                    Core::show_404($e->getMessage());
                }
                elseif (500===$code)
                {
                    Core::show_500($e->getMessage());
                }
                else
                {
                    Core::show_500($e);
                }
            }

            Core::$output = ob_get_clean();
        }
    }

    /**
     * 获取指定key的配置
     *
     * 若不传key，则返回Core_Config对象，可获取动态配置，例如Core::config()->get();
     *
     * @param string $key
     * @param mixed $default 默认值
     * @return Config
     * @return array
     */
    public static function config($key = null, $default = null)
    {
        if (null===$key)
        {
            return Core::factory('Config');
        }

        $c = explode('.', $key);
        $cname = array_shift($c);

        if (strtolower($cname)=='core')
        {
            $v = Core::$core_config;
        }
        elseif (isset(Core::$config[$cname]))
        {
            $v = Core::$config[$cname];
        }
        else
        {
            return $default;
        }

        if ($c)foreach ($c as $i)
        {
            if (!isset($v[$i]))return $default;
            $v = $v[$i];
        }

        return $v;
    }

    /**
     * Cookie
     * @return Core_Cookie
     */
    public static function cookie()
    {
        return Core::factory('Cookie');
    }

    /**
     * 路由处理
     *
     * @return Core_Route
     */
    public static function route()
    {
        return Core::factory('Route');
    }

    /**
     * 返回URL路径
     *
     * 自3.0起可用 url() 直接快速调用此方法
     *
     *     Core::url('test/');
     *     url('test/');
     *
     * @param string $url URL
     * @param true|string $isfullurl_or_project 若传true，则返回当前项目的完整url(http(s)://开头)，若传项目名，比如default，则返回指定项目的完整URL
     * @return string
     */
    public static function url($uri = '' , $isfullurl_or_project = false)
    {
        list($url, $query) = explode('?', $uri , 2);

        $url = Core::$base_url. ltrim($url, '/') . ($url!='' && substr($url,-1)!='/' && false===strpos($url, '.') && Core::$config['url_suffix']?Core::$config['url_suffix']:'') . ($query?'?'.$query:'');

        # 返回完整URL
        if (true===$isfullurl_or_project && !preg_match('#^http(s)?://#i', $url))
        {
            $url = HttpIO::PROTOCOL . $_SERVER["HTTP_HOST"] . $url;
        }

        # 添加自动追加的参数
        if (Core::$_url_auto_args)
        {
            list($url, $hash) = explode('#', $url, 2);
            list($u, $q)      = explode('?', $url, 2);
            if (!$q)
            {
                $q = '';
                parse_str($q, $q);
                $q += Core::$_url_auto_args;
            }
            else
            {
                $q = Core::$_url_auto_args;
            }

            $url = $u .'?'. http_build_query($q, '', '&') . ($hash?'#'.$hash:'');
        }

        return $url;
    }

    /**
     * 返回静态资源URL路径
     *
     * @param string $uri
     */
    public static function url_assets($uri = '')
    {
        $url = ltrim($uri, './ ');

        if (IS_DEBUG & 1)
        {
            # 本地调试环境
            $url_asstes = Core::url('/assets-dev/');
        }
        else
        {
            $url_asstes = URL_ASSETS . Core::$project . '/' . (IS_ADMIN_MODE?'~admin/':'');

            list($file, $query) = explode('?', $uri.'?', 2);

            $uri = $file . '?' . (strlen($query)>0?$query.'&':'') . Core::assets_hash($file);
        }

        return $url_asstes . $url;
    }

    /**
     * 增加URL默认参数
     *
     * 比如用在URL跟踪访客统计上，不支持Session的时候通过URL传送的SessionID等
     *
     * 增加的参数在所有使用 `Core::url()` 返回的内容里都会附带这个参数
     *
     *      Core::add_url_args('debug', 'test');
     *
     *      Core::add_url_args(array('debug'=>'test', 't2'=>'v'2));
     *
     * @param $key   参数名称
     * @param $value 参数值
     * @since v3.0
     */
    public static function add_url_args($key, $value)
    {
        if (is_array($key))
        {
            Core::$_url_auto_args += $key;
        }
        else
        {
            Core::$_url_auto_args[$key] = $value;
        }
    }

    /**
     * Include一个指定URI的控制器
     *
     * @param string $uri
     * @return class_name | false
     */
    public static function load_controller($uri)
    {
        $found = Core::find_controller($uri);

        if ($found)
        {
            # 返回类的名称
            return $found['class'];
        }
        else
        {
            return false;
        }
    }

    /**
     * 是否系统设置禁用文件写入功能
     *
     * 可在 `config.php` 中设置 `$config['file_write_mode'] = 'disable';` 如果disable则返回true,否则返回false
     *
     * @return boolean
     */
    public static function is_file_write_disabled()
    {
        if (Core::config('core.file_write_mode')=='disable')
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 记录日志
     *
     * @param string $msg 日志内容
     * @param string $type 类型，例如：log,error,debug 等
     * @param stirng $file 指定文件名，不指定则默认
     * @return boolean
     */
    public static function log($msg, $type = 'log', $file = null)
    {
        if (Core::is_file_write_disabled())return true;

        # log配置
        $log_config = Core::config('log');

        # 不记录日志
        if (isset($log_config['use']) && !$log_config['use'])
        {
            return true;
        }

        # 内容格式化
        if ($log_config['format'])
        {
            $format = $log_config['format'];
        }
        else
        {
            # 默认格式
            $format = ':time - :host::port - :url - :msg';
        }

        # 获取日志内容
        $data = Core::log_format($msg, $type, $format);

        if (IS_DEBUG)
        {
            # 如果有开启debug模式，输出到浏览器
            Core::debug()->log($data, $type);
        }

        # 保存日志
        return Core::write_log($data, $type, $file);
    }

    /**
     * 执行指定URI的控制器
     *
     * @param string $uri
     */
    public static function execute($uri)
    {
        $found = Core::find_controller($uri);

        if ($found)
        {
            if (isset($found['route']))
            {
                $class_name   = $found['class'];
                $class_exists = class_exists($class_name, true);
                $arguments    = array();
                if (isset($found['route']['action']) && $found['route']['action'])
                {
                    $arguments[] = $found['route']['action'];
                }
            }
            else
            {
                require $found['file'];

                if ($found['ns']=='team-library' || $found['ns']=='project')
                {
                    $class_name = $found['class'];
                }
                else
                {
                    $class_name = str_replace('.', '_', $found['ns']) . '_' . $found['class'];
                }

                $class_exists = class_exists($class_name, false);
            }

            if ($class_exists)
            {

                $controller = new $class_name();

                Controller::$controllers[] = $controller;

                # 是否有必要将action从$arguments中移出
                $need_shift_action = false;
                $arguments = $found['args'];
                if ($arguments)
                {
                    $action = current($arguments);
                    if (0===strlen($action))
                    {
                        $action = 'default';
                    }
                    else
                    {
                        $need_shift_action = true;
                    }
                }
                else
                {
                    $action = 'index';
                }

                $action_name = 'action_' . $action;

                if (!method_exists($controller, $action_name))
                {
                    if ($action_name!='action_default' && method_exists($controller, 'action_default'))
                    {
                        $action      = 'default';
                        $action_name = 'action_default';
                    }
                    elseif ($action_name!='' && (!$arguments || $arguments===array('')) && method_exists($controller, 'action_index'))
                    {
                        $action      = 'index';
                        $action_name = 'action_index';
                    }
                    elseif (method_exists($controller, '__call'))
                    {
                        $controller->__call($action_name, $arguments);

                        Core::rm_controoler($controller);
                        return;
                    }
                    else
                    {
                        Core::rm_controoler($controller);

                        throw new Exception(__('Page Not Found'), 404);
                    }
                }
                elseif ($need_shift_action)
                {
                    array_shift($arguments);
                }

                # 对后缀进行判断
                if ($found['suffix'])
                {
                    if (Core::config('url_suffix') && in_array($found['suffix'], explode('|', Core::config('url_suffix'))))
                    {
                        # 默认允许的后缀
                    }
                    elseif (is_array($controller->allow_suffix))
                    {
                        if (!isset($controller->allow_suffix[$action]) || !in_array($found['suffix'], explode('|', $controller->allow_suffix[$action])))
                        {
                            Core::rm_controoler($controller);
                            throw new Exception(__('Page Not Found'), 404);
                        }
                    }
                    elseif (!in_array($found['suffix'], explode('|', $controller->allow_suffix)))
                    {
                        Core::rm_controoler($controller);
                        throw new Exception(__('Page Not Found'), 404);
                    }

                    # 默认输出页面头信息
                    if (!in_array($found['suffix'], array('php', 'html', 'htm')))
                    {
                        @header('Content-Type: '. File::mime_by_ext($found['suffix']));
                    }
                }

                $ispublicmethod = new ReflectionMethod($controller, $action_name);

                if (!$ispublicmethod->isPublic())
                {
                    Core::rm_controoler($controller);
                    throw new Exception(__('Request Method Not Allowed.'), 405);
                }
                unset($ispublicmethod);

                # POST 方式，自动CSRF判断
                if (HttpIO::METHOD=='POST')
                {
                    $auto_check_post_method_referer = isset($controller->auto_check_post_method_referer)?$controller->auto_check_post_method_referer:Core::config('auto_check_post_method_referer', true);

                    if ($auto_check_post_method_referer && !HttpIO::csrf_check())
                    {
                        Core::rm_controoler($controller);
                        throw new Exception(__('Not Acceptable.'), 406);
                    }
                }

                if (isset($found['route']))
                {
                    # 设置Route参数
                    foreach ($found['route'] as $k => $v)
                    {
                        $controller->$k = $v;
                    }
                }
                else
                {
                    $controller->ids = $found['ids'];
                }

                # 将参数传递给控制器
                $controller->action     = $action_name;
                $controller->controller = $found['class'];
                $controller->uri        = $uri;
                $controller->directory  = $found['dir'];
                $controller->suffix     = $found['suffix'];


                if (IS_SYSTEM_MODE)
                {
                    # 系统内部调用参数
                    $controller->arguments = @unserialize(HttpIO::POST('data', HttpIO::PARAM_TYPE_OLDDATA));
                }
                else
                {
                    $controller->arguments = $arguments;
                }

                # 设置 HttpIO 参数
                HttpIO::set_params_controller($controller);

                # 前置方法
                if (method_exists($controller, 'before'))
                {
                    $controller->before();
                }

                # 执行方法
                $count_arguments = count($arguments);
                switch ($count_arguments)
                {
                    case 0:
                        $controller->$action_name();
                        break;
                    case 1:
                        $controller->$action_name($arguments[0]);
                        break;
                    case 2:
                        $controller->$action_name($arguments[0], $arguments[1]);
                        break;
                    case 3:
                        $controller->$action_name($arguments[0], $arguments[1], $arguments[2]);
                        break;
                    case 4:
                        $controller->$action_name($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
                        break;
                    default:
                        call_user_func_array(array($controller, $action_name), $arguments);
                        break;
                }

                # 后置方法
                if (method_exists($controller, 'after'))
                {
                    $controller->after();
                }

                # 移除控制器
                Core::rm_controoler($controller);

                unset($controller);
            }
            else
            {
                throw new Exception(__('Page Not Found'), 404);
            }
        }
        else
        {
            throw new Exception(__('Page Not Found'), 404);
        }
    }

    protected static function rm_controoler($controller)
    {
        foreach (Controller::$controllers as $k=>$c)
        {
            if ($c===$controller)unset(Controller::$controllers[$k]);
        }

        Controller::$controllers = array_values(Controller::$controllers);
    }

    /**
     * 寻找控制器
     *
     * @return array
     */
    protected static function find_controller($uri)
    {
        $uri = ltrim($uri, '/');

        if (preg_match('#^(.*)\.([a-z0-9]+)$#i', $uri, $m))
        {
            $suffix = strtolower($m[2]);
            $uri    = $m[1];
        }
        else
        {
            $suffix = '';
        }

        if (!IS_SYSTEM_MODE && isset(Core::$config['route']) && Core::$config['route'])
        {
            # 有路由配置，首先根据路由配置查询控制器
            $found_route = Route::get($uri);

            if ($found_route)
            {
                if (!isset($found_route['controller']) || !$found_route['controller'])
                {
                    if (IS_DEBUG)Core::debug()->error('The route not match controller');
                    Core::show_404();
                }

                return array
                (
                    'class'  => 'Controller_' . preg_replace('#[^a-zA-Z0-9_]#', '_', trim($found_route['controller'])),
                    'route'  => $found_route,
                    'suffix' => $suffix,
                );
            }
            else
            {
                unset($found_route);
            }
        }

        if ($uri!='')
        {
            $uri_arr = explode('/', strtolower($uri));
        }
        else
        {
            $uri_arr = array();
        }

        if (IS_DEBUG)
        {
            Core::debug()->log('/'. $uri, 'find controller uri');
        }

        $include_path = Core::$include_path;

        # log
        $find_log = $find_path_log = array();

        # 控制器目录
        $controller_dir = Core::$dir_setting['controller'][0];

        if (IS_SYSTEM_MODE)
        {
            $controller_dir .= '-system';
        }
        elseif (IS_ADMIN_MODE)
        {
            $controller_dir .= '-admin';
        }
        elseif (IS_REST_MODE)
        {
            $controller_dir .= '-rest';
        }
        elseif (IS_CLI)
        {
            $controller_dir .= '-shell';
        }

        # 首先找到存在的目录
        $found_path = array();
        foreach ($include_path as $ns => $ipath)
        {
            if($ipath)foreach ($ipath as $lib_ns => $path)
            {
                if ($ns==='library')
                {
                    $tmp_ns = 'library_' . str_replace('.', '_', $lib_ns);
                }
                else
                {
                    $tmp_ns = $ns;
                }

                $tmp_str  = $real_path = $real_class = '';
                $tmp_path = $path . $controller_dir . DS;
                $ids      = array();

                foreach ($uri_arr as $uri_path)
                {
                    $ds = DS;
                    if ($uri_path==='')
                    {
                        if (count($uri_arr)>1)
                        {
                            break;
                        }
                        $real_uri_path = '';
                        $ds = '';
                    }
                    elseif (is_numeric($uri_path))
                    {
                        $real_uri_path = '_id';
                        $ids[] = $uri_path;
                    }
                    elseif ($uri_path == '_id')
                    {
                        # 不允许直接在URL中使用_id
                        break;
                    }
                    elseif (preg_match('#[^a-z0-9_]#i', $uri_path))
                    {
                        # 不允许非a-z0-9_的字符在控制中
                        break;
                    }
                    else
                    {
                        $real_uri_path = $uri_path;
                    }

                    $tmpdir = $tmp_path . $real_path . $real_uri_path . $ds;
                    if (IS_DEBUG)
                    {
                        $find_path_log[] = Core::debug_path($tmpdir);
                    }
                    $real_path  .= $real_uri_path . DS;
                    $real_class .= $real_uri_path . '_';
                    $tmp_str    .= $uri_path . DS;

                    if (is_dir($tmpdir))
                    {
                        $found_path[$tmp_str][] = array
                        (
                            $tmp_ns,
                            $tmpdir,
                            ltrim($real_class, '_'),
                            $ids,
                        );
                    }
                    else
                    {
                        break;
                    }
                }

                // 根目录的
                if (is_dir($tmp_path))
                {
                    $found_path[''][] = array
                    (
                        $tmp_ns,
                        $tmp_path,
                        '',
                        array(),
                    );

                    if (IS_DEBUG)
                    {
                        $find_path_log[] = Core::debug_path($tmp_path);
                    }
                }
            }
        }

        unset($ids);
        $found = null;

        # 寻找可能的文件
        if ($found_path)
        {
            # 调整优先级
            krsort($found_path);

            foreach ($found_path as $path => $all_path)
            {
                $path_len = strlen($path);
                $tmp_p    = substr($uri, $path_len);

                if (strlen($tmp_p)>0)
                {
                    $args = explode('/', substr($uri, $path_len));
                }
                else
                {
                    $args = array();
                }

                $the_id    = array();
                $tmp_class = array_shift($args);
                $tmp_arg   = $tmp_class;
                $directory = rtrim('/'. substr($uri, 0, $path_len) . $tmp_class, '/');

                if (0===strlen($tmp_class))
                {
                    $tmp_class = 'index';
                }
                elseif (is_numeric($tmp_class))
                {
                    $the_id = array
                    (
                        $tmp_class
                    );
                    $tmp_class = '_id';
                }
                elseif ($tmp_class == '_id')
                {
                    continue;
                }

                $real_class = $tmp_class;
                $tmp_class  = strtolower($tmp_class);

                // 记录找到的index.controller.php
                $found_index_class = null;

                if (IS_DEBUG)
                {
                    $find_log2 = array();
                }

                foreach ($all_path as $tmp_arr)
                {
                    list($ns, $tmp_path, $real_path, $ids) = $tmp_arr;
                    $tmpfile = $tmp_path . $tmp_class . Core::$dir_setting['controller'][1] . EXT;

                    if (IS_DEBUG)
                    {
                        $find_log[] = Core::debug_path($tmpfile);
                    }

                    if (is_file($tmpfile))
                    {
                        if ($the_id)
                        {
                            $ids = array_merge($ids, $the_id);
                        }

                        if ($directory && substr($directory, -1-strlen($tmp_class))=='/'.$tmp_class)
                        {
                            $directory = substr($directory, 0, -1-strlen($tmp_class));
                        }

                        $found = array
                        (
                            'file'   => $tmpfile,
                            'dir'    => $directory,
                            'ns'     => $ns,
                            'class'  => 'Controller_' . $real_path . $real_class,
                            'args'   => $args,
                            'ids'    => $ids,
                        );

                        break 2;
                    }
                    elseif (!$found_index_class && $tmp_class!='default')
                    {
                        // 记录 index.controller.php 控制器
                        $tmpfile = $tmp_path . 'default' . Core::$dir_setting['controller'][1] . EXT;
                        if (IS_DEBUG)
                        {
                            $find_log2[] = Core::debug_path($tmpfile);
                        }

                        if (is_file($tmpfile))
                        {
                            if (null!==$tmp_arg)
                            {
                                array_unshift($args, $tmp_arg);

                                if (strlen($tmp_arg)>0)
                                {
                                    $directory = substr($directory, 0, -strlen($tmp_arg) - 1);
                                }
                            }

                            $found_index_class = array
                            (
                                'file'   => $tmpfile,
                                'dir'    => $directory,
                                'ns'     => $ns,
                                'class'  => 'Controller_' . $real_path . 'Default',
                                'args'   => $args,
                                'ids'    => $ids,
                            );
                        }
                    }
                }

                if (IS_DEBUG && $find_log2)
                {
                    $find_log = array_merge($find_log, $find_log2);
                }

                // index.controller.php 文件
                if (!$found && $found_index_class)
                {
                    $found = $found_index_class;
                    break;
                }
            }
        }

        if (IS_DEBUG)
        {
            Core::debug()->group('find controller path');
            foreach ($find_path_log as $value)
            {
                Core::debug()->log($value);
            }
            Core::debug()->groupEnd();

            Core::debug()->group('find controller files');
            foreach ($find_log as $value)
            {
                Core::debug()->log($value);
            }
            Core::debug()->groupEnd();

            if ($found)
            {
                $found2 = $found;
                $found2['file'] = Core::debug_path($found2['file']);
                Core::debug()->log($found2, 'found contoller');
            }
            else
            {
                Core::debug()->log('/'. $uri, 'not found contoller');
            }
        }

        if (is_array($found))
        {
            $found['suffix'] = $suffix;

            if (isset($found['class']))
            {
                $found['class'] = preg_replace('#[^a-zA-Z0-9_]#', '_', trim($found['class']));
            }
        }

        return $found;
    }


    /**
    * 写入日志
    *
    * 若有特殊写入需求，可以扩展本方法（比如调用数据库类克写到数据库里）
    *
    * @param string $data
    * @param string $type 日志类型
    * @param stirng $file 指定文件名，不指定则默认
    * @return boolean
    */
    protected static function write_log($data, $type = 'log', $file = null)
    {
        static $pro = null;

        if (!$type)$type = 'log';

        if (null===$pro)
        {
            if (preg_match('#^(db|cache)://([a-z0-9_]+)/([a-z0-9_]+)$#i', DIR_LOG , $m))
            {
                $pro = $m;
            }
            else
            {
                $pro = false;
            }
        }

        # Log目录采用文件目录
        if (false===$pro)
        {
            $write_mode = Core::config('core.file_write_mode');

            # 禁用写入
            if ($write_mode=='disable')return true;

            # 再判断是否有转换储存处理
            if (preg_match('#^(db|cache)://([a-z0-9_]+)/([a-z0-9_]+)$#i', $write_mode , $m))
            {
                $pro = $m;
            }
        }

        if (false===$pro)
        {
            # 以文件的形式保存

            $log_config = Core::config('log');

            if (!$file)
            {
                if ($log_config['file'])
                {
                    $file = date($log_config['file']);
                }
                else
                {
                    $file = date('Y/m/d/');
                }
                $file .= $type . '.log';
            }

            $dir = trim(dirname($file), '/');

            # 如果目录不存在，则创建
            if (!is_dir(DIR_LOG.$dir))
            {
                $temp = explode('/', str_replace('\\', '/', $dir) );
                $cur_dir = '';
                for($i=0; $i<count($temp); $i++)
                {
                    $cur_dir .= $temp[$i] . "/";
                    if (!is_dir(DIR_LOG.$cur_dir))
                    {
                        @mkdir(DIR_LOG.$cur_dir, 0755);
                    }
                }
            }

            return false===@file_put_contents(DIR_LOG . $file, $data . CRLF , FILE_APPEND)?false:true;
        }
        else
        {
            # 以db或cache方式保存

            if ($pro[1]=='db')
            {
                $db_data = array
                (
                    'key'    => md5($file),
                    'key_str'=> substr($file, 0, 255),
                    'type'   => $type,
                    'day'    => date('Ymd'),
                    'time'   => TIME,
                    'value'  => $data,
                );

                $obj = new Database($pro[2]);
                $status = $obj->insert($pro[3], $db_data) ? true:false;
            }
            else
            {
                if ($pro[3])
                {
                    $pro[1]['prefix'] = trim($pro[3]) . '_';
                }

                $pro[1]['prefix'] .= $type.'_';

                $obj = new Cache($pro[2]);

                $status = $obj->set(date('Ymd').'_'.md5($file), $data, 86400*30);        // 存1月
            }

            return $status;
        }
    }

    /**
    * 用于保存日志时格式化内容，如需要特殊格式可以自行扩展
    *
    * @param string $msg
    * @param string $format
    * @return string
    */
    protected static function log_format($msg,$type,$format)
    {
        $value = array
        (
            ':time'    => date('Y-m-d H:i:s'),             //当前时间
            ':url'     => $_SERVER['SCRIPT_URI'],          //请求的URL
            ':msg'     => $msg,                            //日志信息
            ':type'    => $type,                           //日志类型
            ':host'    => $_SERVER["SERVER_ADDR"],         //服务器
            ':port'    => $_SERVER["SERVER_PORT"],         //端口
            ':ip'      => HttpIO::IP,                      //请求的IP
            ':agent'   => $_SERVER["HTTP_USER_AGENT"],     //客户端信息
            ':referer' => $_SERVER["HTTP_REFERER"],        //来源页面
        );

        return strtr($format, $value);
    }

    /**
     * 获取debug对象
     * 可安全用于生产环境，在生产环境下将忽略所有debug信息
     * @return Debug
     */
    public static function debug()
    {
        static $debug = null;
        if (null === $debug)
        {
            if (!IS_CLI && ( IS_DEBUG || false!==strpos($_SERVER["HTTP_USER_AGENT"],'FirePHP') || isset($_SERVER["HTTP_X_FIREPHP_VERSION"]) ) && class_exists('Debug', true))
            {
                $debug = Debug::instance();
            }
            else
            {
                $debug = new __NoDebug();
            }
        }
        return $debug;
    }

    /**
     * 将真实路径地址输出为调试地址
     *
     * 显示结果类似 ./system/libraries/Database.class.php
     *
     * @param  string  path to debug
     * @param  boolean $highlight 是否返回高亮前缀，可以传字符颜色，比如#f00
     * @return string
     */
    public static function debug_path($file, $highlight=false)
    {
        if ($highlight)
        {
            if (IS_CLI)
            {
                # 命令行下输出带色彩的前缀
                $l = "\x1b[36m";
                $r = "\x1b[39m";
            }
            else
            {
                $l = '<span style="color:'.(is_string($highlight) && preg_match('/^[a-z0-9#\(\)\.,]+$/i',$highlight) ?$highlight:'#a00').'">';
                $r = '</span>';
            }
        }
        else
        {
            $l = $r = '';
        }

        $file = str_replace('\\', DS, $file);

        if (strpos($file, DIR_CORE) === 0)
        {
            $file = $l . './core/' . $r . substr($file, strlen(DIR_CORE));
        }
        elseif (strpos($file, DIR_TEAM_LIBRARY) === 0)
        {
            $file = $l . './team-library/' . $r . substr($file, strlen(DIR_TEAM_LIBRARY));
        }
        elseif (strpos($file, DIR_LIBRARY) === 0)
        {
            $file = $l . './libraries/' . $r . substr($file, strlen(DIR_LIBRARY));
        }
        elseif (strpos($file, DIR_MODULE) === 0)
        {
            $file = $l . './modules/' . $r . substr($file, strlen(DIR_MODULE));
        }
        elseif (strpos($file, DIR_DRIVER) === 0)
        {
            $file = $l . './drivers/' . $r . substr($file, strlen(DIR_DRIVER));
        }
        elseif (strpos($file, DIR_PROJECT) === 0)
        {
            $file = $l . './projects/' . $r . substr($file, strlen(DIR_PROJECT));
        }
        elseif (strpos($file, DIR_TEMP) === 0)
        {
            $file = $l . './data/temp/' . $r . substr($file, strlen(DIR_TEMP));
        }
        elseif (strpos($file, DIR_LOG) === 0)
        {
            $file = $l . './data/log/' . $r . substr($file, strlen(DIR_LOG));
        }
        elseif (strpos($file, DIR_CACHE) === 0)
        {
            $file = $l . './data/cache/' . $r . substr($file, strlen(DIR_CACHE));
        }
        elseif (strpos($file, DIR_DATA) === 0)
        {
            $file = $l . './data/' . $r . substr($file, strlen(DIR_DATA));
        }
        elseif (strpos($file, DIR_ASSETS) === 0)
        {
            $file = $l . './wwwroot/assets/' . $r . substr($file, strlen(DIR_ASSETS));
        }
        elseif (strpos($file, DIR_UPLOAD) === 0)
        {
            $file = $l . './wwwroot/upload/' . $r . substr($file, strlen(DIR_UPLOAD));
        }
        elseif (strpos($file, DIR_WWWROOT) === 0)
        {
            $file = $l . './wwwroot/' . $r . substr($file, strlen(DIR_WWWROOT));
        }
        elseif (strpos($file, DIR_SYSTEM) === 0)
        {
            $file = $l . './' . $r . substr($file, strlen(DIR_SYSTEM));
        }

        $file = str_replace('\\', '/', $file);

        return $file;
    }

    /**
     * 关闭缓冲区
     *
     * @param  boolean 是否输出缓冲数据
     * @return void
     */
    public static function close_buffers($flush = true)
    {
        if (ob_get_level() > Core::$buffer_level)
        {
            $close = ($flush === true) ? 'ob_end_flush' : 'ob_end_clean';
            while (ob_get_level() > Core::$buffer_level)
            {
                $close();
            }
            Core::$buffer_level = ob_get_level();
        }
    }

    /**
     * 404，可直接将Exception对象传给$msg
     *
     * @param string/Exception $msg
     */
    public static function show_404($msg = null)
    {
        Core::close_buffers(false);

        # 避免输出的CSS头试抛出页面无法显示
        @header('Content-Type: text/html;charset=' . Core::config('core.charset'), true);

        HttpIO::$status = 404;
        HttpIO::send_headers();

        if (null === $msg)
        {
            $msg = __('Page Not Found');
        }

        if (IS_DEBUG && class_exists('ErrException', false))
        {
            if ($msg instanceof Exception)
            {
                throw $msg;
            }
            else
            {
                throw new Exception($msg, 43);
            }
        }

        if (IS_CLI)
        {
            echo $msg . CRLF;
            exit();
        }

        try
        {
            $view = new View('error/404');
            $view->message = $msg;
            $view->render(true);
        }
        catch ( Exception $e )
        {
            list ( $REQUEST_URI ) = explode('?', $_SERVER['REQUEST_URI'], 2);
            $REQUEST_URI = htmlspecialchars(rawurldecode($REQUEST_URI));
            echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">' .
            CRLF . '<html>' .
            CRLF . '<head>' .
            CRLF . '<title>404 Not Found</title>' .
            CRLF . '</head>'.
            CRLF . '<body>' .
            CRLF . '<h1>Not Found</h1>' .
            CRLF . '<p>The requested URL ' . $REQUEST_URI . ' was not found on this server.</p>' .
            CRLF . '<hr />' .
            CRLF . $_SERVER['SERVER_SIGNATURE'] .
            CRLF . '</body>' .
            CRLF . '</html>';
        }

        exit();
    }

    /**
     * 系统错误，可直接将Exception对象传给$msg
     * @param string/Exception $msg
     */
    public static function show_500($msg = null)
    {
        Core::close_buffers(false);

        # 避免输出的CSS头试抛出页面无法显示
        @header('Content-Type: text/html;charset=' . Core::config('core.charset'), true);

        HttpIO::$status = 500;
        HttpIO::send_headers();

        if (null === $msg)
        {
            $msg = __('Internal Server Error');
        }

        if (IS_DEBUG && class_exists('ErrException', false))
        {
            if ($msg instanceof Exception)
            {
                throw $msg;
            }
            else
            {
                throw new Exception($msg, 0);
            }
        }

        if (IS_CLI)
        {
            echo "\x1b[36m";
            if ($msg instanceof Exception)
            {
                echo $msg->getMessage() . CRLF;
            }
            else
            {
                echo $msg . CRLF;
            }

            echo "\x1b[39m";
            echo CRLF;
            exit();
        }

        try
        {
            if ($msg instanceof Exception)
            {
                print_r($msg);exit;
                $error     = $msg->getMessage();
                $trace_obj = $msg;
            }
            else
            {
                $error     = $msg;
                $trace_obj = new Exception($msg);
            }

            $error_config = Core::config('core.error500');

            $view = new View('error/500');
            if ($error_config && isset($error_config['close']) && $error_config['close']==true)
            {
                # 不记录
                $view->error_saved = false;
            }
            else
            {
                $trace_array = array
                (
                    'project'     => Core::$project,
                    'uri'         => HttpIO::$uri,
                    'url'         => HttpIO::PROTOCOL . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"],
                    'post'        => HttpIO::POST(null, HttpIO::PARAM_TYPE_OLDDATA),
                    'get'         => $_SERVER['QUERY_STRING'],
                    'cookie'      => HttpIO::COOKIE(null, HttpIO::PARAM_TYPE_OLDDATA),
                    'client_ip'   => HttpIO::IP,
                    'user_agent'  => HttpIO::USER_AGENT,
                    'referrer'    => HttpIO::REFERRER,
                    'server_ip'   => $_SERVER["SERVER_ADDR"],
                    'trace'       => $trace_obj->__toString(),
                );

                $date     = @date('Y-m-d');
                $no       = strtoupper(substr(md5(serialize($trace_array)), 10, 10));
                $error_no = $date.'-'.$no;

                # 其它数据
                $trace_array['server_name'] = (function_exists('php_uname')? php_uname('a') : 'unknown');
                $trace_array['time']        = TIME;
                $trace_array['use_time']    = microtime(1) - START_TIME;
                $trace_array['trace']       = $trace_obj;

                $trace_data = base64_encode(gzcompress(serialize($trace_array), 9));
                unset($trace_array);

                $view->error_saved = true;

                # 记录错误日志
                try
                {
                    if (isset($error_config['save_type']) && $error_config['save_type'])
                    {
                        $save_type = $error_config['save_type'];
                    }
                    else
                    {
                        $save_type = 'file';
                    }

                    if ($save_type=='file')
                    {
                        # 文件模式
                        $write_mode = Core::config('core.file_write_mode');

                        if (preg_match('#^(db|cache)://([a-z0-9_]+)/([a-z0-9_]+)$#i', $write_mode , $m))
                        {
                            $save_type = $m[1];
                            $error_config['type_config'] = $m[2];
                        }
                    }

                    switch ($save_type)
                    {
                        case 'database':
                            $obj = $error_config['type_config']?new Database($error_config['type_config']) : new Database();
                            $data = array
                            (
                                'time'        => strtotime($date.' 00:00:00'),
                                'no'          => $no,
                                'log'         => $trace_data,
                                'expire_time' => TIME + 7*86400,
                            );
                            $obj->insert('error500_log', $data);
                            break;
                        case 'cache':
                            $obj = $error_config['type_config']?new Cache($error_config['type_config']) : new Cache();
                            if (!$obj->get($error_no))
                            {
                                $obj->set($error_no, $trace_data, 7*86400);
                            }
                            break;
                        default:
                            $file = DIR_LOG .'error500'. DS . str_replace('-', DS, $date) . DS . $no . '.log';
                            if (!is_file($file))
                            {
                                File::create_file($file, $trace_data, null, null, $error_config['type_config']?$error_config['type_config']:'default');
                            }
                            break;
                    }
                }
                catch (Exception $e)
                {

                }
            }

            $view->error_no = $error_no;
            $view->error = $error;
            $view->render(true);
        }
        catch (Exception $e)
        {
            list ($REQUEST_URI) = explode('?', $_SERVER['REQUEST_URI'], 2);
            $REQUEST_URI = htmlspecialchars(rawurldecode($REQUEST_URI));
            echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">' .
            CRLF . '<html>' .
            CRLF . '<head>' .
            CRLF . '<title>Internal Server Error</title>' .
            CRLF . '</head>' .
            CRLF . '<body>' .
            CRLF . '<h1>Internal Server Error</h1>' .
            CRLF . '<p>The requested URL ' . $REQUEST_URI . ' was error on this server.</p>' .
            CRLF . '<hr />' .
            CRLF . $_SERVER['SERVER_SIGNATURE'] .
            CRLF . '</body>' .
            CRLF . '</html>';
        }

        exit();
    }

    /**
     * 返回一个用.表示的字符串的key对应数组的内容
     *
     * 例如
     *
     *     $arr = array
     *     (
     *         'a' => array
     *         (
     *         	  'b' => 123,
     *             'c' => array
     *             (
     *                 456,
     *             ),
     *         ),
     *     );
     *     Core::key_string($arr,'a.b');  //返回123
     *
     *     Core::key_string($arr,'a');
     *     // 返回
     *     array
     *     (
     *        'b' => 123,
     *        'c' => array
     *        (
     *            456,
     *         ),
     *     );
     *
     *     Core::key_string($arr,'a.c.0');  //返回456
     *
     *     Core::key_string($arr,'a.d');  //返回null
     *
     * @param array $arr
     * @param string $key
     * @return fixed
     */
    public static function key_string($arr, $key, $default = null)
    {
        if (!is_array($arr)) return $default;
        $keyArr = explode('.', $key);
        foreach ( $keyArr as $key )
        {
            if ( isset($arr[$key]) )
            {
                $arr = $arr[$key];
            }
            else
            {
                return $default;
            }
        }
        return $arr;
    }

    /**
     * 添加页面在关闭前执行的列队
     * 将利用call_user_func或call_user_func_array回调
     * 类似 register_shutdown_function
     * @param array $function 方法名，可以是数组
     * @param array $param_arr 参数，可空
     */
    public static function register_shutdown_function($function, $param_arr = null)
    {
        Core::$shutdown_function[] = array($function, $param_arr);
    }

    public static function shutdown_handler()
    {
        $error = error_get_last();
        if ( $error )
        {
            static $run = null;
            if ( $run === true ) return;
            $run = true;
            if ( ((E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR) & $error['type']) !== 0 )
            {
                $error['file'] = Core::debug_path($error['file']);
                Core::show_500(var_export($error, true));
                exit();
            }
        }
    }

    public static function exception_handler(Exception $e)
    {
        $code = $e->getCode();
        if ( $code !== 8 )
        {
            Core::show_500($e);
            exit();
        }
    }

    public static function error_handler($code, $error, $file = null, $line = null)
    {
        if ( (error_reporting() & $code) !== 0 )
        {
            throw new ErrorException( $error, $code, 0, $file, $line );
        }
        return true;
    }

    /**
     * 根据$objName返回一个实例化并静态存储的对象
     *
     * @param string $obj_name
     * @param string $key
     */
    public static function factory($obj_name, $key = '')
    {
        if (!isset(Core::$instances[$obj_name][$key]))
        {
            Core::$instances[$obj_name][$key] = new $obj_name($key);
        }

        return Core::$instances[$obj_name][$key];
    }

    /**
     * 释放对象以释放内存
     *
     * 通常在批处理后操作，可有效的释放getFactory静态缓存的对象
     *
     * @param string $obj_name 对象名称 不传的话则清除全部
     * @param string $key 对象关键字 不传的话则清除$objName里的所有对象
     */
    public static function factory_release($obj_name = null, $key = null)
    {
        if (IS_CLI || IS_DEBUG)
        {
            $old_memory = memory_get_usage();
        }

        if  (null===$obj_name)
        {
            Core::$instances = array();
        }
        elseif (isset(Core::$instances[$obj_name]))
        {
            if (null===$key)
            {
                unset(Core::$instances[$obj_name]);
            }
            else
            {
                unset(Core::$instances[$obj_name][$key]);
            }
        }

        if (IS_CLI)
        {
            echo __('The release memory:') . ( memory_get_usage() - $old_memory ) . "\n";
        }
        else if (IS_DEBUG)
        {
            Core::debug()->info(__('The release memory:') . ( memory_get_usage() - $old_memory) );
        }
    }

    /**
     * 将项目切换回初始项目
     *
     * 当使用Core::change_project()设置切换过项目后，可使用此方法返回初始化时的项目
     */
    public static function reset_project()
    {
        if (defined('INITIAL_PROJECT_NAME') && INITIAL_PROJECT_NAME != Core::$project)
        {
            Core::change_project(INITIAL_PROJECT_NAME);
        }
    }

    /**
     * 切换到另外一个项目
     *
     * 切换其它项目后，相关的config,include_path等都将加载为设定项目的，但是已经加载的class等是不可能销毁的，所以需谨慎使用
     *
     * @param string $project
     * @return boolean
     * @throws Exception 失败则抛出异常（比如不存在指定的项目）
     */
    public static function change_project($project)
    {
        if (Core::$project==$project)
        {
            return true;
        }

        if ( !isset(Core::$core_config['projects'][$project] ) )
        {
            Core::show_500( __('not found the project: :project.', array(':project'=>$project) ) );
        }

        if ( !Core::$core_config['projects'][$project]['isuse'] )
        {
            Core::show_500( __('the project: :project is not open.', array(':project'=>$project) ) );
        }

        # 记录所有项目设置，当切换回项目时，使用此设置还原
        static $all_prjects_setting = array();

        if (Core::$project)
        {
            // 记录上一个项目设置
            $all_prjects_setting[Core::$project] = array
            (
                'config'        => Core::$config,
                'include_path'  => Core::$include_path,
                'file_list'     => Core::$file_list,
                'project_dir'   => Core::$project_dir,
                'base_url'      => Core::$base_url,
            );
        }

        # 原来的项目
        $old_project = Core::$project;

        if (isset($all_prjects_setting[$project]))
        {
            # 设为当前项目
            Core::$project = $project;

            # 还原配置
            Core::$config         = $all_prjects_setting[$project]['config'];
            Core::$include_path   = $all_prjects_setting[$project]['include_path'];
            Core::$file_list      = $all_prjects_setting[$project]['file_list'];
            Core::$project_dir    = $all_prjects_setting[$project]['project_dir'];
            Core::$base_url       = $all_prjects_setting[$project]['base_url'];

            # 清除缓存数据
            unset($all_prjects_setting[$project]);
        }
        else
        {
            $p_config = Core::$core_config['projects'][$project];

            if (!isset($p_config['dir']) || !$p_config['dir'])
            {
                Core::show_500(__('the project ":project" dir is not defined.', array(':project'=>$project)));
            }

            # 设置include path
            $project_dir = DIR_PROJECT . $project . DS;
            if (!is_dir($project_dir))
            {
                Core::show_500(__('not found the project: :project.', array(':project' => $project)));
            }

            # 项目路径
            $project_dir = realpath(DIR_PROJECT . $p_config['dir']);

            if (!$project_dir || !is_dir($project_dir))
            {
                Core::show_500(__('the project dir :dir is not exist.', array(':dir'=>$p_config['dir'])));
            }

            # 设为当前项目
            Core::$project = $project;

            $project_dir .= DS;

            Core::$project_dir = $project_dir;

            # 处理base_url
            if (isset($p_config['url']) && $p_config['url'])
            {
                $url = rtrim(current((array)$p_config['url']),'/');
            }
            else
            {
                $url = '/';
            }

            if (IS_ADMIN_MODE)
            {
                if (isset($p_config['url_admin']) && $p_config['url_admin'])
                {
                    $current_url = current((array)$p_config['url_admin']);
                    if (false===strpos($current_url[0],'://'))
                    {
                        $url .= trim($current_url, '/');
                        $url  = trim($url, '/') .'/';
                    }
                }
            }

            if (IS_REST_MODE)
            {
                if (isset($p_config['url_rest']) && $p_config['url_rest'])
                {
                    $current_url = current((array)$p_config['url_rest']);
                    if (false===strpos($current_url[0], '://'))
                    {
                        $url .= trim($current_url, '/');
                        $url  = trim($url,'/') .'/';
                    }
                }
            }

            Core::$base_url = $url;

            # 重置$include_path
            Core::$include_path['project'] = array
            (
                Core::$project => $project_dir
            );

            # 重新加载类库配置
            Core::reload_all_libraries();
        }

        # 记录debug信息
        if (IS_DEBUG)
        {
            Core::debug()->info($project, '程序已切换到了新项目');
        }

        # 回调callback
        if (Core::$change_project_callback)
        {
            foreach (Core::$change_project_callback as $fun)
            {
                call_user_func($fun, $old_project, $project);
            }
        }

        return true;
    }

    /**
     * 增加change_project回调事件
     *
     *     //将在每次执行 Core::change_project($new_project) 成功后执行 MyClass::myfun($old_project, $new_project) 方法,其中$old_project是原来的项目名
     *     Core::change_project_add_callback('MyClass::myfun');
     *
     * @param string|array $fun
     */
    public static function change_project_add_callback($fun)
    {
        Core::$change_project_callback[] = $fun;

        if (count(Core::$change_project_callback)>1)
        {
            # 移除重复的项目
            Core::$change_project_callback = array_unique(Core::$change_project_callback);
        }
    }

    /**
    * 移除import_library回调事件
    *
    * @param string|array $fun
    */
    public static function change_project_remove_callback($fun)
    {
        if (Core::$change_project_callback)
        {
            $new_arr = array();
            foreach (Core::$change_project_callback as $item)
            {
                if ($item!==$fun)
                {
                    $new_arr = $item;
                }
            }

            Core::$change_project_callback = $new_arr;
        }
    }

    /**
     * 导入指定类库
     *
     * 支持多个，当一次导入多个时，从数组最后一个开始导入
     *
     * 导入的格式必须是类似 com.a.b 的形式，否则会抛出异常，例如: com.myqee.test
     *
     *      Bootstrap::import_library('com.myqee.test');
     *      Bootstrap::import_library(array('com.myqee.test','com.myqee.cms'));
     *
     * @param string|array $library_name 指定类库 支持多个
     * @return boolean
     */
    public static function import_library($library_name)
    {
        $library_name = (array)$library_name;

        $status = parent::import_library($library_name);

        # 回调callback
        if ($status>0 && Core::$import_library_callback)
        {
            foreach (Core::$import_library_callback as $fun)
            {
                call_user_func($fun, $library_name);
            }
        }

        return $status;
    }

    /**
     * 增加import_library回调事件
     *
     *     //将在每次执行 Core::import_library($library_name) 成功后执行 MyClass::myfun((array)$library_name) 方法
     *     Core::add_import_library_callback('MyClass::myfun');
     *
     * @param string|array $fun
     */
    public static function import_library_add_callback($fun)
    {
        Core::$import_library_callback[] = $fun;

        if (count(Core::$import_library_callback)>1)
        {
            # 移除重复的项目
            Core::$import_library_callback = array_unique(Core::$import_library_callback);
        }
    }

    /**
     * 移除import_library回调事件
     *
     * @param string|array $fun
     */
    public static function import_library_remove_callback($fun)
    {
        if (Core::$import_library_callback)
        {
            $new_arr = array();
            foreach (Core::$import_library_callback as $item)
            {
                if ($item!==$fun)
                {
                    $new_arr = $item;
                }
            }

            Core::$import_library_callback = $new_arr;
        }
    }

    /**
     * 执行注册的关闭方法
     */
    protected static function run_shutdown_function()
    {
        static $run = null;
        if ( null!==$run )
        {
            return true;
        }
        $run = true;

        if (Core::$shutdown_function)
        {
            foreach (Core::$shutdown_function as $item)
            {
                try
                {
                    call_user_func_array($item[0], (array)$item[1]);
                }
                catch ( Exception $e )
                {

                }
            }
        }
    }

    /**
     * 特殊的合并项目配置
     *
     * 相当于一维数组之间相加，这里支持多维
     *
     * @param array $c1
     * @param array $c2
     * @return array
     */
    protected static function _merge_project_config($c1, $c2)
    {
        foreach ($c2 as $k=>$v)
        {
            if (!isset($c1[$k]))
            {
                $c1[$k] = $v;
            }
            elseif ( is_array($c1[$k]) && is_array($v) )
            {
                $c1[$k] = Core::_merge_project_config($c1[$k] , $v );
            }
            elseif (is_numeric($k) && is_array($c1[$k]))
            {
                $c1[$k][] = $v;
            }
        }
        return $c1;
    }

    /**
     * 关闭所有可能的外部链接，比如Database,Memcache等连接
     */
    public static function close_all_connect()
    {
        foreach ( Core::$close_connect_class_list as $class_name=>$fun )
        {
            try
            {
                call_user_func_array( array($class_name,$fun), array() );
            }
            catch (Exception $e)
            {
                Core::debug()->error('close_all_connect error:'.$e->getMessage());
            }
        }
    }

    /**
     * 增加执行Core::close_all_connect()时会去关闭的类
     *
     *     Core::add_close_connect_class('Database','close_all_connect');
     *     Core::add_close_connect_class('Cache_Driver_Memcache');
     *     Core::add_close_connect_class('TestClass','close');
     *     //当执行 Core::close_all_connect() 时会调用 Database::close_all_connect() 和 Cache_Driver_Memcache::close_all_connect() 和 TestClass::close() 方法
     *
     * @param string $class_name
     * @param string $fun
     */
    public static function add_close_connect_class($class_name, $fun='close_all_connect')
    {
        Core::$close_connect_class_list[$class_name] = $fun;
    }

    /**
     * 检查内部调用HASH是否有效
     *
     * @return boolean
     */
    protected static function check_system_request_allow()
    {
        $hash      = $_SERVER['HTTP_X_MYQEE_SYSTEM_HASH'];      // 请求验证HASH
        $time      = $_SERVER['HTTP_X_MYQEE_SYSTEM_TIME'];      // 请求验证时间
        $rstr      = $_SERVER['HTTP_X_MYQEE_SYSTEM_RSTR'];      // 请求随机字符串
        $project   = $_SERVER['HTTP_X_MYQEE_SYSTEM_PROJECT'];   // 请求的项目
        $path_info = $_SERVER['HTTP_X_MYQEE_SYSTEM_PATHINFO'];  // 请求的path_info
        $isadmin   = $_SERVER['HTTP_X_MYQEE_SYSTEM_ISADMIN'];   // 是否ADMIN
        $isrest    = $_SERVER['HTTP_X_MYQEE_SYSTEM_ISREST'];    // 是否RESTFul请求
        if (!$hash || !$time || !$rstr || !$project || !$path_info) return false;

        // 请求时效检查
        if (microtime(1) - $time > 600)
        {
            Core::log('system request timeout', 'system-request');
            return false;
        }

        // 验证IP
        if ('127.0.0.1'!=HttpIO::IP && HttpIO::IP != $_SERVER["SERVER_ADDR"])
        {
            $allow_ip = Core::config('core.system_exec_allow_ip');

            if (is_array($allow_ip) && $allow_ip)
            {
                $allow = false;
                foreach ($allow_ip as $ip)
                {
                    if (HttpIO::IP == $ip)
                    {
                        $allow = true;
                        break;
                    }

                    if (strpos($allow_ip, '*'))
                    {
                        // 对IP进行匹配
                        if (preg_match('#^' . str_replace('\\*', '[^\.]+', preg_quote($allow_ip, '#')) . '$#', HttpIO::IP))
                        {
                            $allow = true;
                            break;
                        }
                    }
                }

                if (!$allow)
                {
                    Core::log('system request not allow ip:' . HttpIO::IP, 'system-request');
                    return false;
                }
            }
        }

        $body = http_build_query(HttpIO::POST(null, HttpIO::PARAM_TYPE_OLDDATA));

        // 系统调用密钥
        $system_exec_pass = Core::config('system_exec_key');

        $key = Core::config()->get('system_exec_key', 'system', true);

        if (!$key || abs(TIME-$key['time'])>86400*10)
        {
            return false;
        }

        $other = $path_info .'_'. ($isadmin?1:0) .'_'. ($isrest?1:0) . $key['str'];

        if ($system_exec_pass && strlen($system_exec_pass) >= 10)
        {
            // 如果有则使用系统调用密钥
            $newhash = sha1($body . $time . $system_exec_pass . $rstr .'_'. $other);
        }
        else
        {
            // 没有，则用系统配置和数据库加密
            $newhash = sha1($body . $time . serialize(Core::config('core')) . serialize(Core::config('database')) . $rstr .'_'. $other);
        }

        if ($newhash==$hash)
        {
            return true;
        }
        else
        {
            Core::log('system request hash error', 'system-request');
            return false;
        }
    }

    /**
     * 获取asset文件MD5号
     *
     * @param string $file
     * @return md5
     */
    public static function assets_hash($file)
    {
        //TODO 须加入文件版本号
        return '';
    }


    /**
     * 返回客户端IP数组列表
     *
     * 也可直接用 `HttpIO::IP` 来获取到当前单个IP
     *
     * @return array
     */
    public static function ip()
    {
        $ip = array();

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            $ip = explode(',', str_replace(' ', '', $_SERVER['HTTP_X_FORWARDED_FOR']));
        }

        if(isset($_SERVER['HTTP_CLIENT_IP']))
        {
            $ip = array_merge($ip, explode(',', str_replace(' ', '', $_SERVER['HTTP_CLIENT_IP'])));
        }

        if (isset($_SERVER['REMOTE_ADDR']))
        {
            $ip = array_merge($ip, explode(',', str_replace(' ', '', $_SERVER['REMOTE_ADDR'])));
        }

        return $ip;
    }


    /**
     * 系统调用内容输出函数（请勿自行执行）
     */
    public static function _output_body()
    {
        # 发送header数据
        HttpIO::send_headers();

        if (IS_DEBUG && isset($_REQUEST['debug']) && class_exists('Profiler', true))
        {
            # 调试打开时不缓存页面
            HttpIO::set_cache_header(0);
        }

        # 执行注册的关闭方法
        ob_start();
        Core::run_shutdown_function();
        $output = ob_get_clean();

        # 在页面输出前关闭所有的连接
        Core::close_all_connect();

        # 输出内容
        echo Core::$output , $output;
    }
}


/**
 * 无调试对象
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Core
 * @package    Classes
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class __NoDebug
{
    public function __call($m, $v)
    {
        return $this;
    }

    public function log($i = null)
    {
        return $this;
    }

    public function info($i = null)
    {
        return $this;
    }

    public function error($i = null)
    {
        return $this;
    }

    public function group($i = null)
    {
        return $this;
    }

    public function groupEnd($i = null)
    {
        return $this;
    }

    public function table($Label = null, $Table = null)
    {
        return $this;
    }

    public function profiler($i = null)
    {
        return $this;
    }

    public function is_open()
    {
        return false;
    }
}

