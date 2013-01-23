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
 * @author     jonwang(jonwang@myqee.com)
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2012 myqee.com
 * @license    http://www.myqee.com/license.html
 */
abstract class Core_Core extends Bootstrap
{
    /**
     * MyQEE版本号
     * @var string
     */
    const VERSION = '3.1.1dev';

    /**
     * 项目开发者
     * @var string
     */
    const CODER = 'jonwang(jonwang@myqee.com)';

    /**
     * 页面编码
     * @var string
     */
    public static $charset;

    /**
     * 页面传入的PATHINFO参数
     * @var array
     */
    public static $arguments;

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
     * 执行Core::close_all_connect()方法时会关闭链接的类和方法名的列队，可通过Core::add_close_connect_class()方法进行设置增加
     *
     *   array(
     *       'Database' => 'close_all_connect',
     *   );
     *
     * @var array
     */
    protected static $close_connect_class_list = array();

    /**
     * 系统启动
     * @param string $pathinfo
     */
    public static function setup($auto_run = true)
    {
        static $run = null;
        if ( true === $run )
        {
            if ($auto_run)
            {
                Core::run();
            }
            return;
        }
        $run = true;

        Core::$charset = Core::$config['charset'];

        if (!IS_CLI)
        {
            # 输出powered by信息
            header('X-Powered-By: PHP/' . PHP_VERSION . ' MyQEE/' . Core::VERSION );
        }

        if (IS_DEBUG)
        {
            Core::debug()->info('SERVER IP:' . $_SERVER["SERVER_ADDR"] . (function_exists('php_uname')?'. SERVER NAME:' . php_uname('a') : ''));

            if (Core::$project)
            {
                Core::debug()->info('project: '.Core::$project);
            }
            else
            {
                Core::debug()->info('default application');
            }

            if (IS_ADMIN_MODE)
            {
                Core::debug()->info('admin mode');
            }

            Core::debug()->group('include path');
            foreach ( Core::include_path() as $value )
            {
                Core::debug()->log(Core::debug_path($value));
            }
            Core::debug()->groupEnd();
        }

        if ( (IS_CLI || IS_DEBUG) && class_exists('ErrException', true) )
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
        register_shutdown_function(array('Core','_output_body'));

        if ( true===IS_SYSTEM_MODE )
        {
            if (false===Core::check_system_request_allow() )
            {
                # 内部请求验证不通过
                Core::show_500('system request hash error');
            }
        }

        if ( IS_DEBUG && isset($_REQUEST['debug']) && class_exists('Profiler', true) )
        {
            Profiler::setup();
        }
    }

    /**
     * 获取指定key的配置
     *
     * 若不传key，则返回Core_Config对象，可获取动态配置，例如Core::config()->get();
     *
     * @param string $key
     * @param string $project 跨项目读取配置，若本项目内的不需要传
     * @return Config
     * @return array
     */
    public static function config($key = null)
    {
        if ( null===$key )
        {
            return Core::factory('Config');
        }

        $c = explode('.', $key);
        $cname = array_shift($c);
        if ( !array_key_exists($cname, Core::$config) )
        {
            $config = array();
            $thefiles = Core::find_file('config', $cname);
            if ( is_array($thefiles) )
            {
                if ( count($thefiles) > 1 )
                {
                    krsort($thefiles); //逆向排序
                }
                foreach ( $thefiles as $thefile )
                {
                    if ( $thefile )
                    {
                        __include_config_file($config, $thefile);
                    }
                }
            }
            if ( !isset(Core::$config[$cname]) )
            {
                Core::$config[$cname] = $config;
            }
        }

        $v = Core::$config[$cname];

        foreach ($c as $i)
        {
            if (!isset($v[$i]))return null;
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
     * @param bool $need_full_url 返回完整的URL，带http(s)://开头
     * @return string
     */
    public static function url($url = '' , $need_full_url = false)
    {
        list($url,$query) = explode('?', $url , 2);

        $url = Bootstrap::$base_url. ltrim($url,'/') . ($url!='' && substr($url,-1)!='/' && false===strpos($url,'.') && Core::$config['url_suffix']?Core::$config['url_suffix']:'') . ($query?'?'.$query:'');

        // 返回完整URL
        if ( $need_full_url && !preg_match('#^http(s)?://#i', $url) )
        {
            $url = HttpIO::PROTOCOL . $_SERVER["HTTP_HOST"] . $url;
        }

        return $url;
    }

    /**
     * 返回静态资源URL路径
     * @param unknown_type $uri
     */
    public static function url_assets($uri = '')
    {
        $url = ltrim($uri,'./ ');

        if (IS_DEBUG & 1)
        {
            # 本地调试环境
            $url_asstes = '/assets/devmode/'.Core::$project.'/';
        }
        else
        {
            $url_asstes = URL_ASSETS;

            list($file,$query) = explode('?', $uri.'?',2);

            $uri = $file .'?'.(strlen($query)>0?$query.'&':'').Core::get_asset_hash($file);
        }

        return $url_asstes . $url;
    }

    /**
     * 记录日志
     *
     * @param string $msg 日志内容
     * @param string $type 类型，例如：log,error,debug 等
     * @return boolean
     */
    public static function log($msg , $type = 'log')
    {
        # log配置
        $log_config = Core::config('log');

        # 不记录日志
        if ( isset($log_config['use']) && !$log_config['use'] )return true;

        if ($log_config['file'])
        {
        $file = date($log_config['file']);
        }
        else
        {
            $file = date('Y/m/d/');
        }
        $file .= $type.'.log';

        $dir = trim(dirname($file),'/');

        # 如果目录不存在，则创建
        if (!is_dir(DIR_LOG.$dir))
        {
            $temp = explode('/', str_replace('\\', '/', $dir) );
            $cur_dir = '';
            for( $i=0; $i<count($temp); $i++ )
            {
                $cur_dir .= $temp[$i] . "/";
                if ( !is_dir(DIR_LOG.$cur_dir) )
                {
                    @mkdir(DIR_LOG.$cur_dir,0755);
                }
            }
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
        $data = Core::log_format($msg,$type,$format);

        if (IS_DEBUG)
        {
            # 如果有开启debug模式，输出到浏览器
            Core::debug()->log($data,$type);
        }

        # 保存日志
        return Core::write_log($file, $data, $type);
    }

    /**
     * Include一个指定URI的控制器
     *
     * @param string $uri
     * @return boolean
     */
    public static function load_controller($uri)
    {
        $found = self::find_controller($uri);
    }

    /**
    * 写入日志
    *
    * 若有特殊写入需求，可以扩展本方法（比如调用数据库类克写到数据库里）
    *
    * @param string $file
    * @param string $data
    * @param string $type 日志类型
    * @return boolean
    */
    protected static function write_log($file , $data , $type = 'log')
    {
        return File::create_file(DIR_LOG.$file, $data.CRLF , FILE_APPEND);
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

        return strtr($format,$value);
    }

    /**
     * 获取debug对象
     * 可安全用于生产环境，在生产环境下将忽略所有debug信息
     * @return Debug
     */
    public static function debug()
    {
        static $debug = null;
        if ( null === $debug )
        {
            if ( !IS_CLI && ( IS_DEBUG || false!==strpos($_SERVER["HTTP_USER_AGENT"],'FirePHP') || isset($_SERVER["HTTP_X_FIREPHP_VERSION"]) ) && class_exists('Debug', true) )
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
    public static function debug_path($file,$highlight=false)
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

        if ( strpos($file, DIR_CORE) === 0 )
        {
            $file = $l . './core/' . $r . substr($file, strlen(DIR_CORE));
        }
        elseif ( strpos($file, DIR_TEAM_LIB) === 0 )
        {
            $file = $l . './global/' . $r . substr($file, strlen(DIR_TEAM_LIB));
        }
        elseif ( strpos($file, DIR_LIBRARY) === 0 )
        {
            $file = $l . './libraries/' . $r . substr($file, strlen(DIR_LIBRARY));
        }
        elseif ( strpos($file, DIR_PROJECT) === 0 )
        {
            $file = $l . './projects/' . $r . substr($file, strlen(DIR_PROJECT));
        }
        elseif ( strpos($file, DIR_BULIDER) === 0 )
        {
            $file = $l . './data/bulider/' . $r . substr($file, strlen(DIR_BULIDER));
        }
        elseif ( strpos($file, DIR_LOG) === 0 )
        {
            $file = $l . './data/log/' . $r . substr($file, strlen(DIR_LOG));
        }
        elseif ( strpos($file, DIR_TEMP) === 0 )
        {
            $file = $l . './data/temp/' . $r . substr($file, strlen(DIR_TEMP));
        }
        elseif ( strpos($file, DIR_CACHE) === 0 )
        {
            $file = $l . './data/cache/' . $r . substr($file, strlen(DIR_CACHE));
        }
        elseif ( strpos($file, DIR_DATA) === 0 )
        {
            $file = $l . './data/' . $r . substr($file, strlen(DIR_DATA));
        }
        elseif ( strpos($file, DIR_ASSETS) === 0 )
        {
            $file = $l . './wwwroot/assets/' . $r . substr($file, strlen(DIR_ASSETS));
        }
        elseif ( strpos($file, DIR_WWWROOT) === 0 )
        {
            $file = $l . './wwwroot/' . $r . substr($file, strlen(DIR_WWWROOT));
        }
        elseif ( strpos($file, DIR_SYSTEM) === 0 )
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
        if ( ob_get_level() > Core::$buffer_level )
        {
            $close = ($flush === true) ? 'ob_end_flush' : 'ob_end_clean';
            while ( ob_get_level() > Core::$buffer_level )
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
        @header('Content-Type: text/html;charset=' . Core::config('core.charset') ,true);

        HttpIO::$status = 404;
        HttpIO::send_headers();

        if ( null === $msg )
        {
            $msg = __('Page Not Found');
        }

        if ( IS_DEBUG && class_exists('ErrException', false) )
        {
            if ( $msg instanceof Exception )
            {
                throw $msg;
            }
            else
            {
                throw new Exception($msg, 43);
            }
        }

        if ( IS_CLI )
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
            CRLF . '<hr>' .
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
        @header('Content-Type: text/html;charset=' . Core::config('core.charset') , true);

        HttpIO::$status = 500;
        HttpIO::send_headers();

        if ( null === $msg )
        {
            $msg = __('Internal Server Error');
        }

        if ( IS_DEBUG && class_exists('ErrException', false) )
        {
            if ( $msg instanceof Exception )
            {
                throw $msg;
            }
            else
            {
                throw new Exception($msg, 0);
            }
        }

        if ( IS_CLI )
        {
            echo "\x1b[36m";
            if ( $msg instanceof Exception )
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
            if ( $msg instanceof Exception )
            {
                $error = $msg->getMessage();
                $trace_obj = $msg;
            }
            else
            {
                $error = $msg;
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
                    'url'         => HttpIO::PROTOCOL.'://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"],
                    'post'        => HttpIO::POST(null,HttpIO::PARAM_TYPE_OLDDATA),
                    'get'         => $_SERVER['QUERY_STRING'],
                    'cookie'      => HttpIO::COOKIE(null,HttpIO::PARAM_TYPE_OLDDATA),
                    'client_ip'   => HttpIO::IP,
                    'user_agent'  => HttpIO::USER_AGENT,
                    'referrer'    => HttpIO::REFERRER,
                    'server_ip'   => $_SERVER["SERVER_ADDR"],
                    'trace'       => $trace_obj->__toString(),
                );

                $date     = date('Y-m-d');
                $no       = strtoupper(substr(md5(serialize($trace_array)),10,10));
                $error_no = $date.'-'.$no;

                # 其它数据
                $trace_array['server_name'] = (function_exists('php_uname')? php_uname('a') : 'unknown');
                $trace_array['time']        = TIME;
                $trace_array['use_time']    = microtime(1) - START_TIME;
                $trace_array['trace']       = $trace_obj;

                $trace_data = base64_encode(gzcompress(serialize($trace_array),9));
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

                    switch ($save_type)
                    {
                        case 'database':
                            $obj = new Database($error_config['type_config']?$error_config['type_config']:'default');
                            $where = array
                            (
                                'time'  => strtotime($date.' 00:00:00'),
                                'no'    => $no,
                            );

                            if ( !$obj->from('error500_log')->where($where)->get(false,true)->current() )
                            {
                                $where['log'] = $trace_data;
                                $obj->insert('error500_log',$where);
                            }
                            break;
                        case 'cache':
                            $obj = new Cache($error_config['type_config']?$error_config['type_config']:'default');
                            if ( !$obj->get($error_no) )
                            {
                                $obj->set($error_no,$trace_data,7*86400);
                            }
                            break;
                        default:
                            $file = DIR_LOG.'error500'.DS.str_replace('-',DS,$date).DS.$no.'.log';
                            if (!is_file($file))
                            {
                                File::create_file($file, $trace_data,null,null,$error_config['type_config']?$error_config['type_config']:'default');
                            }
                            break;
                    }
                }
                catch (Exception $e){}
            }

            $view->error_no = $error_no;
            $view->error = $error;
            $view->render(true);
        }
        catch ( Exception $e )
        {
            list ( $REQUEST_URI ) = explode('?', $_SERVER['REQUEST_URI'], 2);
            $REQUEST_URI = htmlspecialchars(rawurldecode($REQUEST_URI));
            echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">' .
            CRLF . '<html>' .
            CRLF . '<head>' .
            CRLF . '<title>Internal Server Error</title>' .
            CRLF . '</head>' .
            CRLF . '<body>' .
            CRLF . '<h1>'.__('Internal Server Error').'</h1>' .
            CRLF . '<p>The requested URL ' . $REQUEST_URI . ' was error on this server.</p>' .
            CRLF . '<hr>' .
            CRLF . $_SERVER['SERVER_SIGNATURE'] .
            CRLF . '</body>' .
            CRLF . '</html>';
    }

        # 执行注册的shutdown方法，并忽略输出的内容
        ob_start();
        Core::run_shutdown_function();
        ob_end_clean();

        exit();
    }

    /**
     * 返回一个用.表示的字符串的key对应数组的内容
     *
     * 例如
     *    $arr = array(
     *        'a' => array(
     *        	  'b' => 123,
     *            'c' => array(
     *                456,
     *            ),
     *        ),
     *    );
     *    Core::key_string($arr,'a.b');  //返回123
     *
     *    Core::key_string($arr,'a');
     *    // 返回
     *    array(
     *       'b' => 123,
     *       'c' => array(
     *          456,
     *        ),
     *    );
     *
     *    Core::key_string($arr,'a.c.0');  //返回456
     *
     *    Core::key_string($arr,'a.d');  //返回null
     *
     * @param array $arr
     * @param string $key
     * @return fixed
     */
    public static function key_string($arr, $key)
    {
        if ( !is_array($arr) ) return null;
        $keyArr = explode('.', $key);
        foreach ( $keyArr as $key )
        {
            if ( isset($arr[$key]) )
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
     * @param string $objName
     * @param string $key
     */
    public static function factory($objName, $key = '')
    {
        if ( !isset(Core::$instances[$objName][$key]) )
        {
            Core::$instances[$objName][$key] = new $objName($key);
        }

        return Core::$instances[$objName][$key];
    }

    /**
     * 释放对象以释放内存
     *
     * 通常在批处理后操作，可有效的释放getFactory静态缓存的对象
     *
     * @param string $objNamen 对象名称 不传的话则清除全部
     * @param string $key 对象关键字 不传的话则清除$objName里的所有对象
     */
    public static function factory_release($objName = null, $key = null)
    {
        if (IS_CLI || IS_DEBUG)
        {
            $old_memory = memory_get_usage();
        }

        if  (null===$objName)
        {
            Core::$instances = array();
        }
        elseif (isset(Core::$instances[$objName]))
        {
            if (null===$key)
            {
                unset(Core::$instances[$objName]);
            }
            else
            {
                unset(Core::$instances[$objName][$key]);
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
     * 当使用Core::set_project()设置切换过项目后，可使用此方法返回初始化时的项目
     */
    public static function reset_project()
    {
        if ( defined('INITIAL_PROJECT_NAME') && INITIAL_PROJECT_NAME != Core::$project )
        {
            Core::set_project( INITIAL_PROJECT_NAME );
        }
    }

    /**
     * 设置项目
     *
     * 可重新设置新项目已实现程序内项目切换，但需谨慎使用
     * @param string $project
     */
    public static function set_project($project)
    {
        if ( Core::$project == $project )
        {
            return true;
        }

        if ( !isset(Core::$config['projects'][$project] ) )
        {
            Core::show_500( __('not found the project: :project.',array(':project'=>$project) ) );
        }

        if ( !Core::$config['projects'][$project]['isuse'] )
        {
            Core::show_500( __('the project: :project is not open.' , array(':project'=>$project) ) );
        }

        static $core_config = null;

        if (null===$core_config)
        {
            # 记录原始Core配置
            $core_config = Core::$config;
        }

        # 记录所有项目设置，当切换回项目时，使用此设置还原
        static $all_prjects_setting = array();

        if ( Core::$project )
        {
            // 记录上一个项目设置
            $all_prjects_setting[Core::$project] = array
            (
                'config'         => Core::$config,
                'include_path'   => Core::$include_path,
                'file_list'      => Core::$file_list,
                'project_dir'    => Core::$project_dir,
                'base_url'       => Core::$base_url,
            );
        }

        # 设为当前项目
        Bootstrap::$project = Core::$project = $project;

        # 记录debug信息
        if (IS_DEBUG)
        {
            Core::debug()->info($project, '程序已切换到了新项目');
        }

        if ( isset($all_prjects_setting[$project]) )
        {
            # 还原配置
            Core::$config         = $all_prjects_setting[$project]['config'];
            Core::$include_path   = $all_prjects_setting[$project]['include_path'];
            Core::$file_list      = $all_prjects_setting[$project]['file_list'];
            Core::$project_dir    = $all_prjects_setting[$project]['project_dir'];
            Core::$base_url       = $all_prjects_setting[$project]['base_url'];
        }
        else
        {
            Core::$config = array
            (
                'core' => $core_config,
            );

            if (!isset($core_config['projects'][$project]['dir']) || !$core_config['projects'][$project]['dir'])
            {
                Core::show_500( __('the project ":project" dir is not defined.' , array(':project'=>$project)) );
            }

            # 项目路径
            $project_dir = realpath( DIR_PROJECT . $core_config['projects'][$project]['dir'] );

            if (!$project_dir || !is_dir($project_dir))
            {
                Core::show_500( __('the project dir :dir is not exist.' , array(':dir'=>$core_config['projects'][$project]['dir'])) );
            }

            $project_dir .= DS;
            Core::$project_dir = $project_dir;

            # 读取项目配置
            if (is_file($project_dir.'config'.EXT))
            {
                __include_config_file( Core::$config, $project_dir.'config'.EXT );
            }

            # 读取DEBUG配置
            if ( isset($core_config['debug_config']) && $core_config['debug_config'] && is_file($project_dir.'debug.config'.EXT) )
            {
                __include_config_file( Core::$config , $project_dir.'debug.config'.EXT );
            }

            # 处理base_url
            if ( isset($core_config['projects'][$project]['url']) && $core_config['projects'][$project]['url'] )
            {
                $url = rtrim(current((array)$core_config['projects'][$project]['url']),'/');
            }
            else
            {
                $url = '/';
            }

            if (IS_ADMIN_MODE)
            {
                if (isset($core_config['projects'][$project]['url_admin']) && $core_config['projects'][$project]['url_admin'])
                {
                    $admin_url = current((array)$core_config['projects'][$project]['url_admin']);
                    if (false===strpos($admin_url[0],'://'))
                    {
                        $url .= trim($admin_url,'/');
                        $url  = trim($url,'/').'/';
                    }
                }
            }
            Core::$base_url = $url;

            # 设置include path
            $project_dir = DIR_PROJECT . $project . DS;
            if (!is_dir($project_dir))
            {
                Core::show_500('not found the project: :project', array(':project' => $project));
            }
            Bootstrap::$include_path['project'] = array($project_dir);
            Bootstrap::$include_path['library'] = array();

            # 重新加载类库配置
            Bootstrap::load_all_libraries();
        }

        return true;
    }


    /**
     * 输出执行跟踪信息
     * 注意：本方法仅用于本地跟踪代码使用，调试完毕后请移除相关调用
     *
     * @param string $msg
     * @param int $code
     */
    public static function trace($msg = 'Trace Tree', $code = E_NOTICE)
    {
        if (IS_DEBUG)
        {
            throw new Exception($msg, $code);
            exit();
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

        if ( Core::$shutdown_function )
        {
            foreach ( Core::$shutdown_function as $item )
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
    protected static function _merge_project_config( $c1, $c2 )
    {
        foreach ( $c2 as $k=>$v )
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
     *    Core::add_close_connect_class('Database','close_all_connect');
     *    Core::add_close_connect_class('Cache_Driver_Memcache');
     *    Core::add_close_connect_class('TestClass','close');
     *    //当执行 Core::close_all_connect() 时会调用 Database::close_all_connect() 和 Cache_Driver_Memcache::close_all_connect() 和 TestClass::close() 方法
     *
     * @param string $class_name
     * @param string $fun
     */
    public static function add_close_connect_class($class_name,$fun='close_all_connect')
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
        $hash = $_SERVER['HTTP_X_MYQEE_SYSTEM_HASH']; // 请求验证HASH
        $time = $_SERVER['HTTP_X_MYQEE_SYSTEM_TIME']; // 请求验证时间
        $rstr = $_SERVER['HTTP_X_MYQEE_SYSTEM_RSTR']; // 请求随机字符串
        if ( !$hash || !$time || !$rstr ) return false;

        // 请求时效检查
        if ( microtime(1) - $time > 600 )
        {
            Core::log('system request timeout', 'system-request');
            return false;
        }

        // 验证IP
        if ( '127.0.0.1' != HttpIO::IP && HttpIO::IP != $_SERVER["SERVER_ADDR"] )
        {
            $allow_ip = Core::config('core.system_exec_allow_ip');

            if ( is_array($allow_ip) && $allow_ip )
            {
                $allow = false;
                foreach ( $allow_ip as $ip )
                {
                    if ( HttpIO::IP == $ip )
                    {
                        $allow = true;
                        break;
                    }

                    if ( strpos($allow_ip, '*') )
                    {
                        // 对IP进行匹配
                        if ( preg_match('#^' . str_replace('\\*', '[^\.]+', preg_quote($allow_ip, '#')) . '$#', HttpIO::IP) )
                        {
                            $allow = true;
                            break;
                        }
                    }
                }

                if ( !$allow )
                {
                    Core::log('system request not allow ip:' . HttpIO::IP, 'system-request');
                    return false;
                }
            }
        }

        $body = http_build_query(HttpIO::POST(null, HttpIO::PARAM_TYPE_OLDDATA));

        // 系统调用密钥
        $system_exec_pass = Core::config('core.system_exec_key');

        if ( $system_exec_pass && strlen($system_exec_pass) >= 10 )
        {
            // 如果有则使用系统调用密钥
            $newhash = sha1($body . $time . $system_exec_pass . $rstr);
        }
        else
        {
            // 没有，则用系统配置和数据库加密
            $newhash = sha1($body . $time . serialize(Core::config('core')) . serialize(Core::config('database')) . $rstr);
        }

        if ( $newhash == $hash )
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
    public static function get_asset_hash($file)
    {
        return '';
    }

    /**
     * 系统调用内容输出函数（请勿自行执行）
     */
    public static function _output_body()
    {
        # 发送header数据
        HttpIO::send_headers();

        if ( IS_DEBUG && isset($_REQUEST['debug']) && class_exists('Profiler', true) )
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
 * @author     jonwang(jonwang@myqee.com)
 * @category   Core
 * @package    Classes
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2012 myqee.com
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

