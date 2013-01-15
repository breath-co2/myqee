<?php
/**
 * 当前系统启动时间
 *
 * @var int
 */
define('START_TIME', microtime(1));

/**
 * 启动内存
 *
 * @var int 启动所用内存
 */
define('START_MEMORY', memory_get_usage());

/**
 * PHP文件后缀
 *
 * @var string
 */
define('EXT', '.php');

/**
 * 系统当前时间
 *
 * @var int
 */
define('TIME', time());

/**
 * 目录分隔符简写
 *
 * @var string
 */
define('DS', DIRECTORY_SEPARATOR);

/**
 * 是否WIN系统
 *
 * @var boolean
 */
define('IS_WIN', DS==='\\'?true:false);

/**
 * CRLF换行符
 *
 * @var string
 */
define('CRLF', "\r\n");

/**
 * 服务器是否支持mbstring
 *
 * @var boolean
 */
define('IS_MBSTRING', extension_loaded('mbstring')?true:false);

/**
 * 是否命令行执行
 *
 * @var boolean
 */
define('IS_CLI', (PHP_SAPI==='cli'));

/**
 * 是否系统调用模式
 *
 * @var boolean
 */
define('IS_SYSTEM_MODE', !IS_CLI && isset($_SERVER['HTTP_X_MYQEE_SYSTEM_HASH']) ? true : false);

/**
 * 站点目录
 *
 * @var string
 */
define('DIR_SYSTEM', realpath(dirname(__FILE__).DS.'..'.DS).DS);

/**
 * Core目录
 *
 * @var string
 */
define('DIR_CORE', DIR_SYSTEM.'core'.DS);

/**
 * 项目目录
 *
 * @var string
*/
define('DIR_PROJECT', DIR_SYSTEM.'projects'.DS);

/**
 * Global公用类库目录
 *
 * @var string
*/
define('DIR_GLOBAL', DIR_SYSTEM.'global'.DS);

/**
 * 模块目录
 *
 * @var string
 */
define('DIR_LIBRARY', DIR_SYSTEM.'libraries'.DS);

/**
 * WWW目录
 *
 * @var string
*/
define('DIR_WWWROOT', DIR_SYSTEM.'wwwroot'.DS);

/**
 * WWW目录
 *
 * @var string
*/
define('DIR_ASSETS', DIR_WWWROOT.'assets'.DS);


if (!isset($dir_data )) $dir_data  = DIR_SYSTEM . 'data/';
if (!isset($dir_temp )) $dir_temp  = $dir_data  . 'temp/';
if (!isset($dir_log  )) $dir_log   = $dir_data  . 'log/';
if (!isset($dir_cache)) $dir_cache = $dir_data  . 'cache/';

/**
 * Data目录
 *
 * @var string
 */
define('DIR_DATA', DIR_SYSTEM.'data'.DS);

/**
 * 数据目录
 * @var string
 */
define('DIR_DATA', strpos($dir_data,'://')!==false ? $dir_data : (realpath($dir_data)?realpath($dir_data):DIR_SYSTEM.'data').DS);

/**
 * Cache目录
 *
 * @var string
*/
define('DIR_CACHE', strpos($dir_cache,'://')!==false ? $dir_cache : (realpath($dir_cache)?realpath($dir_cache):DIR_DATA.'cache').DS);

/**
 * Temp目录
 *
 * @var string
*/
define('DIR_TEMP', strpos($dir_temp,'://')!==false ? $dir_temp : (realpath($dir_temp)?realpath($dir_temp):DIR_DATA.'temp').DS);

/**
 * Log目录
 *
 * @var string
*/
define('DIR_LOG', strpos($dir_log,'://')!==false ? $dir_log : (realpath($dir_log)?realpath($dir_log):DIR_DATA.'log').DS);


unset($dir_data,$dir_cache,$dir_log,$dir_temp);


/**
 * 输出语言包
 *
 * [strtr](http://php.net/strtr) is used for replacing parameters.
 *
 * __('Welcome back, :user', array(':user' => $username));
 *
 * @uses	I18n::get
 * @param	string  text to translate
 * @param	array   values to replace in the translated text
 * @param	string  target language
 * @return	string
 */
function __( $string, array $values = null )
{
    static $have_i18n_class = false;

    if ( false===$have_i18n_class )
    {
        $have_i18n_class = (boolean)class_exists('I18n',true);
    }

    if ($have_i18n_class)
    {
        $string = I18n::get($string);
    }

    return empty($values)?$string:strtr($string,$values);
}

/**
 * 是否对传入参数转义，建议系统关闭自动转义功能
 * @var boolean
 */
define( 'MAGIC_QUOTES_GPC', get_magic_quotes_gpc() );

if ( !IS_CLI && MAGIC_QUOTES_GPC )
{

    function _stripcslashes( $string )
    {
        if ( is_array( $string ) )
        {
            foreach ( $string as $key => $val )
            {
                $string[$key] = _stripcslashes( $val );
            }
        }
        else
        {
            $string = stripcslashes( $string );
        }
        return $string;
    }
    $_GET     = _stripcslashes( $_GET );
    $_POST    = _stripcslashes( $_POST );
    $_COOKIE  = _stripcslashes( $_COOKIE );
    $_REQUEST = _stripcslashes( $_REQUEST );
}


/**
 * Include指定config文件的数据
 *
 * @param array $config
 * @param string|array $files
 */
function _include_config_file( & $config , $files )
{
    $files = (array)$files;
    foreach ($files as $file)
    {
        include $file;
    }
}


/**
 * Bootstrap
 *
 * @author     jonwang(jonwang@myqee.com)
 * @category   Core
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
abstract class Bootstrap
{
    /**
     * 包含目录
     *
     * @var array
     */
    public static $include_path = array
    (
        'project'  => array(),                   // 项目类库
        'global'   => array(DIR_GLOBAL),         // Global公共类库
        'library'  => array(),                   // 类库包
        'core'     => array('core'=>DIR_CORE),   // 核心类库
    );

    /**
     * 系统配置
     *
     * @var array
     */
    public static $config = array();

    /**
     * 当前URL的根路径
     *
     * @var string
     */
    public static $base_url = null;

    /**
     * 当前URL的PATH_INFO
     *
     * @var string
     */
    public static $path_info = null;

    /**
     * 当前项目
     *
     * @var string
     */
    public static $project;

    /**
     * 当前项目目录
     *
     * @var string
     */
    public static $project_dir;

    /**
     * 当前项目的URL
     *
     * @var string
     */
    public static $project_url;

    /**
     * 系统文件列表
     *
     * @var array('project_name'=>array(...))
     */
    public static $file_list = array();

    /**
     * 目录设置
     *
     * @var array
     */
    private static $dir_setting = array
    (
        'class'      => array('classes'     , '.class'),
        'controller' => array('controllers' , '.controller'),
        'model'      => array('models'      , '.model'),
        'orm'        => array('orm'         , '.orm'),
    );

    /**
     * 所有项目的config配置
     *
     * array('projectName'=>array(...))
     * @var array
     */
    private static $config_projects = array();

    /**
     * 系统初始化
     *
     * @param boolean $auto_execute 是否自动运行控制器
     */
    public static function setup($auto_execute = true)
    {
        static $run = null;

        if (!$run)
        {
            $run = true;

            # 注册自动加载类
            spl_autoload_register(array('Bootstrap','auto_load'));

            # 读取配置
            if ( !is_file(DIR_SYSTEM . 'config' . EXT) )
            {
                self::_show_error( __('Please rename the file config.new:EXT to config:EXT' , array(':EXT'=>EXT)) );
            }

            self::$config = array
            (
                'core' => array()
            );

            _include_config_file( self::$config['core'] , DIR_SYSTEM.'config'.EXT );

            # 本地调试模式
            if ( isset( self::$config['core']['local_debug_cfg'] ) && self::$config['core']['local_debug_cfg'] )
            {
                # 判断是否开启了本地调试
                if ( function_exists('get_cfg_var') )
                {
                    $open_debug = get_cfg_var( self::$config['core']['local_debug_cfg'] ) ? 1 : 0;
                }
                else
                {
                    $open_debug = 0;
                }
            }
            else
            {
                $open_debug = 0;
            }

            # 读Debug配置
            if ( $open_debug && is_file(DIR_SYSTEM.'debug.config'.EXT) )
            {
                # 本地debug配置打开
                _include_config_file( self::$config['core'] , DIR_SYSTEM.'debug.config'.EXT );
            }

            # 在线调试
            if ( self::_is_online_debug() )
            {
                $open_debug = 1<<1 | $open_debug;
            }

            /**
             * 是否开启DEBUG模式
             *
             *     if (IS_DEBUG>>1)
             *     {
             *         //开启了在线调试
             *     }
             *
             *     if (IS_DEBUG & 1)
             *     {
             *         //本地调试打开
             *     }
             *
             *     if (IS_DEBUG)
             *     {
             *         // 开启了调试
             *     }
             *
             * @var int
             */
            define('IS_DEBUG', $open_debug);

            if (!IS_CLI)
            {
                # 输出文件头
                header('Content-Type: text/html;charset='.self::$config['core']['charset']);
            }

            // 设置错误等级
            if (isset(self::$config['core']['error_reporting']))
            {
                @error_reporting(self::$config['core']['error_reporting']);
            }

            // 时区设置
            if (isset(self::$config['core']['timezone']))
            {
                @date_default_timezone_set(self::$config['core']['timezone']);
            }

            //获取全局$project变量
            global $project, $admin_mode;

            if (isset($project) && $project)
            {
                $project = (string)$project;

                if (!isset(self::$config['core']['projects'][$project]))
                {
                    self::_show_error( __('not found the project: :project',array(':project'=>$project)) );
                }

                // 如果有设置项目
                self::$project = $project;

                // 管理员模式
                if ( isset($admin_mode) && true===$admin_mode )
                {
                    $request_mode = 'admin';
                }
            }
            else
            {
                if (IS_CLI)
                {
                    if (!isset($_SERVER["argv"]))
                    {
                        exit('Err Argv');
                    }

                    if (isset($_SERVER['OS']) && $_SERVER['OS']=='Windows_NT')
                    {
                        # 切换到UTF-8编码显示状态
                        exec('chcp 65001');
                    }

                    $argv = $_SERVER["argv"];

                    //$argv[0]为文件名
                    if (isset($argv[1]) && $argv[1] && isset(self::$config['core']['projects'][$argv[1]]))
                    {
                        self::$project = $argv[1];
                    }

                    array_shift($argv); //将文件名移除
                    array_shift($argv); //将项目名移除

                    self::$path_info = trim(implode('/', $argv));

                    unset($argv);
                }
                else
                {
                    $request_mode = '';
                    self::setup_by_url($request_mode);
                }
            }

            /**
             * 是否后台模式
             *
             * @var boolean
             */
            define('IS_ADMIN_MODE',(!IS_CLI && $request_mode=='admin')?true:false);

            # 加载类库
            foreach (array('autoload', 'cli', 'admin', 'debug') as $type)
            {
                if (!isset(self::$config['core']['libraries'][$type]) || !self::$config['core']['libraries'][$type])continue;

                if ($type=='cli')
                {
                    if (!IS_DEBUG)continue;
                }
                else if ($type=='admin')
                {
                    if (!IS_ADMIN_MODE)continue;
                }
                else if ($type=='debug')
                {
                    if (!IS_DEBUG)continue;
                }

                $libs = array_reverse((array)self::$config['core']['libraries'][$type]);
                foreach ($libs as $lib)
                {
                    self::_add_include_path_lib($lib);
                }
            }

            # 处理 library 的config
            $config_files = array();

            # 反向排序，从最后一个开始导入
            $include_path = array_reverse(self::include_path());
            foreach ($include_path as $path)
            {
                $config_file = $path.'config'.EXT;

                if ( is_file($config_file) )
                {
                    $config_files[] = $config_file;
                }
            }

            if ($config_files)
            {
                # 记录Core配置
                $core_config =& self::$config['core'];
                unset(self::$config['core']);

                # 导入config
                _include_config_file(self::$config, $config_files);

                # 避免Core配置被修改
                self::$config['core'] =& $core_config;
            }
            unset($include_path, $core_config , $config_file);


            Core::setup();
        }

        # 直接执行
        if ($auto_execute)
        {
            if (IS_CLI || IS_SYSTEM_MODE)
            {
                self::execute(self::$path_info);
            }
            else
            {
                ob_start();

                try
                {
                    self::execute(self::$path_info);
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
                        Core::show_500($e->getMessage(),$code);
                    }
                }

                Core::$output = ob_get_clean();
            }
        }
    }

    /**
     * 自动加载类
     *
     * @param string $class_name
     * @return boolean
     */
    public static function auto_load($class_name)
    {
        if ( class_exists($class_name,false) )return true;

        # 移除两边的
        $class_name = strtolower(trim(trim($class_name),'\\'));

        if (substr($class_name,0,5)=='core_')
        {
            # 系统类库
            $ns = 'core';
            $new_class_name = substr($class_name,5);
        }
        else if (preg_match('#^library_((?:[a-z0-9]+)_(?:[a-z0-9]+))_([a-z0-9_]+)$#', $class_name,$m))
        {
            $ns = 'library/'.str_replace('_','/',$m[1]);
            $new_class_name = $m[2];
        }
        else
        {
            $ns = '';
            $new_class_name = $class_name;
        }

        $pos = strpos($new_class_name, '_');
        if (false!==$pos)
        {
            $type = substr($new_class_name,0,$pos);
        }
        else
        {
            $type = 'class';
        }

        if (isset(self::$dir_setting[$type]))
        {
            $dir_setting = self::$dir_setting[$type];

            if ($type=='controller')
            {
                if ( IS_SYSTEM_MODE )
                {
                    $dir_setting[0] .= '/[system]';
                }
                elseif ( IS_CLI )
                {
                    $dir_setting[0] .= '/[shell]';
                }
                elseif ( IS_ADMIN_MODE )
                {
                    $dir_setting[0] .= '/[admin]';
                }
            }

        }
        else
        {
            $dir_setting = self::$dir_setting['class'];
        }

        if ($ns)
        {
            $file = ($ns=='core' ? DIR_CORE : DIR_LIBRARY . str_replace('_',DS,$m[1]) . DS ) . $dir_setting[0] . DS . str_replace('_',DS,$new_class_name) . $dir_setting[1] . EXT;

            if (is_file($file))
            {
                require $file;
            }
        }
        else
        {
            # 在include path中找
            # $found = self::find_file($dir_setting[0], $new_class_name , null , true);

            # 没有找到文件且为项目类库，尝试在某个命名空间的类库中寻找

            foreach (array('library','core') as $type)
            {
                foreach (self::$include_path[$type] as $lib_ns=>$path)
                {
                    $ns_class_name = ($type=='library'?'library_':'').str_replace('.','_',$lib_ns).'_'.$class_name;

                    if ( self::auto_load($ns_class_name) )
                    {
                        if ( class_exists($class_name,false) )
                        {
                            # 在加载$ns_class_name时，当前需要的类库有可能被加载了，直接返回true
                            return true;
                        }
                        else
                        {
                            # 是否禁用eval方式加载
                            static $disable_eval = null;
                            if (null===$disable_eval)$disable_eval = isset(self::$config['core']['disable_eval']) && self::$config['core']['disable_eval'] ? true:false;

                            if ($disable_eval)
                            {
                                $file = $dir_setting[0].DS.str_replace('_',DS,$class_name).$dir_setting[1].EXT;
                                if ($type=='core')
                                {
                                    $tmp_file = DIR_CORE .'empty_extend_files' . DS . $file;
                                }
                                else
                                {
                                    $tmp_file = DIR_LIBRARY . str_replace('.',DS,$lib_ns) . DS .'empty_extend_files' . DS . $file;
                                }

                                if (is_file($tmp_file))
                                {
                                    include $tmp_file;
                                }
                            }
                            else
                            {
                                $rf = new ReflectionClass($ns_class_name);
                                if ( $rf->isAbstract() )
                                {
                                    $abstract = 'abstract ';
                                }
                                else
                                {
                                    $abstract = '';
                                }
                                unset($rf);

                                $str = $abstract.'class '.$class_name.' extends '.$ns_class_name.'{}';

                                # 动态执行
                                eval($str);
                            }
                        }

                        break;
                    }
                }
            }
        }

        if ( class_exists($class_name,false) )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 获取包含目录，返回一个一维的数组
     *
     * ! 注意 Bootstrap::$include_path 为一个2维数组
     *
     * @return array
     */
    public static function include_path()
    {
        $arr = array();
        foreach (self::$include_path as $v)
        {
            foreach ($v as $p)
            {
                $arr[] = $p;
            }
        }

        return $arr;
    }

    /**
     * 获取指定项目的include_path
     *
     * @param string $project
     * @return array
     */
    protected static function get_project_include_path( $project )
    {
        # 项目目录排第一个
        $library_dir = array( self::$project_dir );

        if ( IS_DEBUG )
        {
            # 调试类库目录
            $debug_libraries = null;
            if ( isset( self::$config['core']['libraries']['debug'] ) )
            {
                $debug_libraries = self::$config['core']['libraries']['debug'];
            }
            elseif ( isset( self::$config['core']['libraries']['debug'] ) )
            {
                $debug_libraries = self::$config['core']['libraries']['debug'];
            }
            if ( $debug_libraries )
            {
                if ( !is_array( $debug_libraries ) )
                {
                    $debug_libraries = array( (string) $debug_libraries );
                }
                $debug_path = array();
                foreach ( $debug_libraries as $path )
                {
                    $path = str_replace('.','/',substr($path,4));
                    if ( $path[0] == '/' || preg_match( '#^[a-z]:(\\|/).*$#', $path ) )
                    {
                        $path = realpath( $path );
                    }
                    else
                    {
                        $path = realpath( DIR_LIBRARY . $path );
                    }
                    if ( $path )
                    {
                        $debug_path[] = $path . DS;
                    }
                }
                if ( $debug_path )
                {
                    # 合并
                    $library_dir = array_merge( $library_dir , $debug_path );
                }
            }
        }

        # 自动加载类库
        if ( isset( self::$config['core']['libraries']['autoload'] ) )
        {
            $included = (array)self::$config['core']['libraries']['autoload'];
        }
        else
        {
        	$included = array();
        }

        if ( IS_ADMIN_MODE )
        {
            # 后台管理加载项
            if ( isset( self::$config['core']['libraries']['admin'] ) && is_array( self::$config['core']['libraries']['admin'] ) )
            {
                $included = array_merge($included,self::$config['core']['libraries']['admin']);
            }
        }

        # 扩展配置
        if ( isset( self::$config['core']['excluded'] ) && self::$config['core']['excluded'] )
        {
            # 排除的目录
            if ( !is_array( self::$config['core']['excluded'] ) )
            {
                self::$config['core']['excluded'] = array( self::$config['core']['excluded'] );
            }
            $included = array_diff( $included, self::$config['core']['excluded'] );
        }

        foreach ( $included as $path )
        {
            $path = str_replace('.','/',substr($path,4));
            if ( $path[0] == '/' || preg_match( '#^[a-z]:(\\|/).*$#', $path ) )
            {
                $path = realpath( $path );
            }
            else
            {
                $path = realpath( DIR_LIBRARY . $path );
            }
            if ( $path )
            {
                $library_dir[] = $path . DS;
            }
        }

        # 系统核心库
        $library_dir[] = DIR_CORE;

        # 排除重复路径
        $library_dir = array_values( array_unique($library_dir ) );

        return $library_dir;
    }

    /**
     * 执行指定URI的控制器
     *
     * @param string $uri
     */
    public static function execute($uri)
    {
        $found = self::find_controller($uri);

        if ($found)
        {
            require $found['file'];

            if ($found['ns']=='global'||$found['ns']=='project')
            {
                $class_name = $found['class'];
            }
            else
            {
                $class_name = str_replace('.','_',$found['ns']).'_'.$found['class'];
            }

            if (class_exists($class_name,false))
            {

                $controller = new $class_name();

                Controller::$controllers[] = $controller;

                $arguments = $found['args'];
                if ($arguments)
                {
                    $action = current($arguments);
                    if (0===strlen($action))
                    {
                        $action = 'default';
                    }
                }
                else
                {
                    $action = 'index';
                }

                $action_name = 'action_'.$action;

                if (!method_exists($controller,$action_name))
                {
                    if ($action_name!='action_default' && method_exists($controller,'action_default'))
                    {
                        $action_name='action_default';
                    }
                    elseif (method_exists($controller,'__call'))
                    {
                        $controller->__call($action_name,$arguments);

                        self::rm_controoler($controller);
                        return;
                    }
                    else
                    {
                        self::rm_controoler($controller);

                        throw new Exception(__('Page Not Found'),404);
                    }
                }
                else
                {
                    array_shift($arguments);
                }

                $ispublicmethod = new ReflectionMethod($controller,$action_name);
                if (!$ispublicmethod->isPublic())
                {
                    self::rm_controoler($controller);

                    throw new Exception(__('Request Method Not Allowed.'),405);
                }
                unset($ispublicmethod);

                # 将参数传递给控制器
                $controller->action = $action_name;
                $controller->controller = $found['class'];
                $controller->ids = $found['ids'];

                if (IS_SYSTEM_MODE)
                {
                    # 系统内部调用参数
                    $controller->arguments = @unserialize(HttpIO::POST('data',HttpIO::PARAM_TYPE_OLDDATA));
                }
                else
                {
                    $controller->arguments = $arguments;
                }

                # 前置方法
                if (method_exists($controller,'before'))
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
                if (method_exists($controller,'after'))
                {
                    $controller->after();
                }

                # 移除控制器
                self::rm_controoler($controller);
            }
            else
            {
                throw new Exception(__('Page Not Found'),404);
            }
        }
        else
        {
            throw new Exception(__('Page Not Found'),404);
        }
    }


    private static function rm_controoler ($controller)
    {
        foreach (Controller::$controllers as $k=>$c)
        {
            if ($c===$controller)unset(Controller::$controllers[$k]);
        }

        Controller::$controllers = array_values(Controller::$controllers);
    }

    /**
     * 查找文件
     *
     *   //查找一个视图文件
     *   Bootstrap::find_file('views','test',EXT);
     *
     * @param string $dir 目录
     * @param string $file 文件
     * @param string $ext 后缀 例如：.html，不指定(null)的话则自动设置后缀
     * @param boolean $auto_require 是否自动加载上来，对config,i18n无效
     * @return string
     */
    public static function find_file($dir, $file, $ext=null, $auto_require=false)
    {
        # 处理后缀
        if ( null===$ext )
        {
            $the_ext = EXT;
        }
        elseif ( false===$ext || ''===$ext )
        {
            $the_ext = '';
        }
        elseif ( $ext[0]!='.' )
        {
            $the_ext = '.'.$ext;
        }

        # 是否只需要寻找到第一个文件
        $only_need_one_file = true;

        if ($dir == 'classes')
        {
            $file = strtolower(str_replace('_', '/', $file));
            if (null===$ext)$the_ext = '.class'.EXT;
        }
        else if ( $dir == 'models' )
        {
            $file = strtolower(str_replace('_', '/', $file));
            if (null===$ext)$the_ext = '.model'.EXT;
        }
        else if ( $dir == 'controllers' )
        {
            $file = strtolower(str_replace('_', '/', $file));
            if (null===$ext)$the_ext = '.controller'.EXT;

            if ( IS_SYSTEM_MODE )
            {
                $dir .= '/[system]';
            }
            elseif ( IS_CLI )
            {
                $dir .= '/[shell]';
            }
            elseif ( IS_ADMIN_MODE )
            {
                $dir .= '/[admin]';
            }
        }
        elseif ( $dir=='i18n' || $dir=='config' )
        {
            if (null===$ext)$the_ext = '.lang';
            $only_need_one_file = false;
        }
        elseif ( $dir=='views' )
        {
            if (null===$ext)$the_ext = '.view'.EXT;
            $file = strtolower($file);
        }
        elseif ( $dir == 'orm' )
        {
            if (null===$ext)$the_ext = '.orm'.EXT;
            #orm
            $file = preg_replace('#^(.*)_[a-z0-9]+$#i', '$1', $file);
            $file = strtolower(str_replace('_', '/', $file));
        }


        # 寻找到的文件
        $found_files = array();

        # 采用当前项目目录
        $include_path = self::$include_path;

        foreach ( $include_path as $the_path )
        {
            foreach ($the_path as $path)
            {
                if ( $dir=='config' && self::$config['core']['debug_config'] )
                {
                    # config 在 debug开启的情况下读取debug
                    $tmpfile_debug = $path . $dir . DS . $file . '.debug' . $the_ext;
                    if ( is_file($tmpfile_debug) )
                    {
                        $found_files[] = $tmpfile_debug;
                    }
                }

                $tmpfile = $path . $dir . DS . $file . $the_ext;

                if (is_file($tmpfile))
                {
                    $found_files[] = $tmpfile;
                    if ($only_need_one_file) break;
                }
            }
        }

        if ($found_files)
        {
            if ($only_need_one_file)
            {
                if ($auto_require)
                {
                    require $found_files[0];
                }
                return $found_files[0];
            }
            else
            {
                return $found_files;
            }
        }

    }

    /**
     * 导入指定类库
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
        if (!$library_name) return false;

        $library_name = (array)$library_name;

        # 反向排序，从最后一个开始导入
        $library_name = array_reverse($library_name);

        $config_files = array();
        foreach ($library_name as $lib)
        {
            $set = self::_add_include_path_lib($lib);

            $config_file = $set[1].'config'.EXT;

            if ( is_file($config_file) )
            {
                $config_files[] = $config_file;
            }

            if ( IS_DEBUG && class_exists('Core',false) && class_exists('Debug',false) )Core::debug()->info('import a new library: '.Core::debug_path($lib));
        }

        if ($config_files)
        {
            # 记录Core配置
            $core_config =& self::$config['core'];
            unset(self::$config['core']);

            # 导入config
            _include_config_file(self::$config, $config_files);

            # 避免Core配置被修改
            self::$config['core'] =& $core_config;

            unset($core_config);
        }

        return true;
    }

    /**
     * 加入include_path类库
     *
     * @param string $lib
     * @throws Exception
     * @return array($ns,$dir)
     */
    private static function _add_include_path_lib($lib)
    {
        $lib = strtolower(trim($lib));

        $lib_arr = explode('.',$lib);
        if (count($lib_arr)!=3 || $lib_arr[0]!='com')
        {
            throw new Exception(__('Library name (:lib) error',array(':lib'=>$lib)));
        }

        $dir = DIR_LIBRARY . $lib_arr[1] . DS . $lib_arr[2] . DS;
        $ns = preg_replace('#[^a-z0-9\.]#','',$lib_arr[1].'.'.$lib_arr[2]);

        if (isset(self::$include_path[$type][$ns]))
        {
            return array($ns,$dir);
        }

        if (!is_dir($dir))
        {
            throw new Exception(__('Library :lib not exist.',array(':lib'=>$lib)));
        }

        # 合并目录
        self::$include_path['library'] = array_merge( array($ns=>$dir), self::$include_path['library']);

        return array($ns,$dir);
    }

    /**
     * 根据用户名和密码获取一个hash
     *
     * @param string $username
     * @param string $password
     * @return string
     */
    public static function get_debug_hash( $username , $password )
    {
        $config_str = var_export(self::$config['core']['debug_open_password'], true);

        return sha1($config_str . '_open$&*@debug' . $password . '_' . $username );
    }


    /**
     * 将项目切换回初始项目
     *
     * 当使用Core::set_project()设置切换过项目后，可使用此方法返回初始化时的项目
     */
    public static function reset_project()
    {
        if ( defined('INITIAL_PROJECT_NAME') && INITIAL_PROJECT_NAME != self::$project )
        {
            self::set_project( INITIAL_PROJECT_NAME );
        }
    }

    /**
     * 返回协议类型
     *
     * 当在命令行里执行，则返回null
     *
     * @return null | http:// | https://
     */
    public static function protocol()
    {
        static $protocol = null;

        if (null===$protocol)
        {
            if ( IS_CLI )
            {
                return null;
            }
            else
            {
                $https_key = Core::config('core.server_httpson_key');
                if ($https_key)
                {
                    $https_key = strtoupper($https_key);
                }
                else
                {
                    $https_key = 'HTTPS';
                }
                if ( !empty($_SERVER[$https_key]) && filter_var($_SERVER[$https_key], FILTER_VALIDATE_BOOLEAN) )
                {

                    $protocol = 'https://';
                }
                else
                {
                    $protocol = 'http://';
                }
            }
        }

        return $protocol;
    }

    /**
     * 寻找控制器
     *
     * @return array
     */
    private function find_controller($uri)
    {
        $uri = '/' . trim($uri, ' /');

        if (self::$config['core']['url_suffix'] && substr(strtolower($uri),-strlen(self::$config['core']['url_suffix']))==self::$config['core']['url_suffix'])
        {
            $uri = substr($uri,0,-strlen(self::$config['core']['url_suffix']));
        }

        if ($uri != '/')
        {
            $uri_arr = explode('/', strtolower($uri));
        }
        else
        {
            $uri_arr = array('');
        }

        if (IS_DEBUG)
        {
            Core::debug()->log($uri,'find controller uri');
        }

        $include_path = self::$include_path;

        # log
        $find_log = $find_path_log = array();

        # 控制器目录
        $controller_dir = 'controllers';

        # 首先找到存在的目录
        $found_path = array();
        foreach ( $include_path as $ns => $ipath )
        {
            foreach ($ipath as $path)
            {
                $tmp_str = $real_path = $real_class = '';
                $tmp_path = $path . self::$dir_setting['controller'][0];
                $ids = array();
                foreach ( $uri_arr as $uri_path )
                {
                    if (is_numeric($uri_path))
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

                    $tmpdir = $tmp_path . $real_path . $real_uri_path . DS;
                    if (IS_DEBUG)
                    {
                        $find_path_log[] = Core::debug_path($tmpdir);
                    }
                    $real_path .= $real_uri_path . DS;
                    $real_class .= $real_uri_path . '_';
                    $tmp_str .= $uri_path . DS;

                    if (is_dir($tmpdir))
                    {
                        $found_path[$tmp_str][] = array
                        (
                            $ns,
                            $tmpdir,
                            ltrim($real_class,'_'),
                            $ids
                        );
                    }
                    else
                    {
                        break;
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

            foreach ( $found_path as $path => $all_path )
            {
                $tmp_p = substr($uri, strlen($path));
                if ($tmp_p)
                {
                    $args = explode('/', substr($uri, strlen($path)));
                }
                else
                {
                    $args = array();
                }

                $the_id = array();
                $tmp_class = array_shift($args);

                if (0 === strlen($tmp_class))
                {
                    $tmp_class = 'index';
                }
                elseif (is_numeric($tmp_class))
                {
                    $the_id = array(
                        $tmp_class
                    );
                    $tmp_class = '_id';
                }
                elseif ($tmp_class == '_id')
                {
                    continue;
                }

                $real_class = $tmp_class;

                foreach ( $all_path as $tmp_arr )
                {
                    list($ns, $tmp_path, $real_path, $ids) = $tmp_arr;
                    $path_str = $real_path;
                    $tmpfile = $tmp_path . strtolower($tmp_class) . self::$dir_setting['controller'][1] . EXT;
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
                        $found = array
                        (
                            'file'   => $tmpfile,
                            'ns'     => $ns,
                            'class'  => 'Controller_' . $path_str . $real_class,
                            'args'   => $args,
                            'ids'    => $ids,
                        );

                        break 2;
                    }
                }
            }
        }

        if (IS_DEBUG)
        {
            Core::debug()->group('find controller path');
            foreach ( $find_path_log as $value )
            {
                Core::debug()->log($value);
            }
            Core::debug()->groupEnd();

            Core::debug()->group('find controller file');
            foreach ( $find_log as $value )
            {
                Core::debug()->log($value);
            }
            Core::debug()->groupEnd();

            if ($found)
            {
                $found2 = $found;
                $found2['file'] = Core::debug_path($found2['file']);
                Core::debug()->log($found2,'found contoller');
            }
            else
            {
                Core::debug()->log($uri,'not found contoller');
            }
        }

        return $found;
    }

    /**
     * 根据URL初始化
     */
    private static function setup_by_url( & $request_mode )
    {
        # 当没有$_SERVER["SCRIPT_URL"] 时拼接起来
        if ( !isset($_SERVER['SCRIPT_URL']) )
        {
            $tmp_uri = explode('?', $_SERVER['REQUEST_URI'] ,2);
            $_SERVER['SCRIPT_URL'] = $tmp_uri[0];
        }

        # 当没有$_SERVER["SCRIPT_URI"] 时拼接起来
        if ( !isset($_SERVER['SCRIPT_URI']) )
        {
            $_SERVER['SCRIPT_URI'] = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on'?'https':'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_URL'];
        }

        # 处理BASE_URL
        if (isset(self::$config['core']['root_path']) && self::$config['core']['root_path'])
        {
            self::$base_url = rtrim(self::$config['core']['root_path'],'/');
        }
        else if (null === self::$base_url && isset($_SERVER["SCRIPT_NAME"]) && $_SERVER["SCRIPT_NAME"])
        {
            $base_url_len = strrpos($_SERVER["SCRIPT_NAME"], '/');
            if ($base_url_len)
            {
                $base_url = substr($_SERVER["SCRIPT_NAME"], 0, $base_url_len);
                if (preg_match('#^(.*)/wwwroot$#', $base_url, $m))
                {
                # 特殊处理wwwroot目录
                    $base_url     = $m[1];
                    $base_url_len = strlen($base_url);
                }

                if (strtolower(substr($_SERVER['REQUEST_URI'], 0, $base_url_len)) == strtolower($base_url))
                {
                    self::$base_url = $base_url;
                }
            }
        }

        if ( isset($_SERVER['PATH_INFO']) )
        {
            $pathinfo = $_SERVER['PATH_INFO'];
        }
        else
        {
            if ( isset($_SERVER['REQUEST_URI']) )
            {
                $request_uri = $_SERVER['REQUEST_URI'];
                $root_uri = '/'.substr($_SERVER['SCRIPT_FILENAME'],strlen($_SERVER['DOCUMENT_ROOT']));
                $index_file = 'index'.EXT;
                if (substr($root_uri,-strlen($index_file))==$index_file)
                {
                    $root_uri = substr($root_uri,0,-strlen($index_file));
                }
                if ($root_uri && $root_uri!='/')
                {
                    $request_uri = substr($request_uri, strlen($root_uri));
                }
                list($pathinfo) = explode('?', $request_uri, 2);
            }
            elseif ( isset($_SERVER['PHP_SELF']) )
            {
                $pathinfo = $_SERVER['PHP_SELF'];
            }
            elseif ( isset($_SERVER['REDIRECT_URL']) )
            {
                $pathinfo = $_SERVER['REDIRECT_URL'];
            }
            else
            {
                $pathinfo = false;
            }
        }

        # 过滤pathinfo传入进来的服务器默认页
        if (false !== $pathinfo && ($indexpagelen = strlen(self::$config['core']['server_index_page'])) && substr($pathinfo, -1 - $indexpagelen) == '/' . self::$config['core']['server_index_page'])
        {
            $pathinfo = substr($pathinfo, 0, -$indexpagelen);
        }
        $pathinfo = trim($pathinfo);

        if (!isset($_SERVER["PATH_INFO"]))
        {
            $_SERVER["PATH_INFO"] = $pathinfo;
        }

        self::$path_info = $pathinfo;

        # 项目相关设置
        if (isset(self::$config['core']['projects']) && is_array(self::$config['core']['projects']) && self::$config['core']['projects'])
        {
            # 处理项目
            foreach ( self::$config['core']['projects'] as $project => $item )
            {
                if (!preg_match('#^[a-z0-9_]+$#i', $project)) continue;

                $admin_url = array();
                if (isset($item['admin_url']) && $item['admin_url'])
                {
                    if (!is_array($item['admin_url'])) $item['admin_url'] = array
                    (
                        $item['admin_url']
                    );

                    foreach ( $item['admin_url'] as $tmp_url )
                    {
                        if (preg_match('#^http(s)?\://#i', $tmp_url))
                        {
                            if (($path_info_admin = self::_get_pathinfo($tmp_url)) !== false)
                            {
                                self::$project   = $project;
                                self::$path_info = $path_info_admin;
                                self::$base_url  = $tmp_url;
                                $request_mode    = 'admin';

                                break 2;
                            }
                        }
                        else
                        {
                            # /开头的后台URL
                            $admin_url[] = $tmp_url;
                        }
                    }
                }

                if ($item['url'])
                {
                    if (!is_array($item['url']))$item['url'] = array
                    (
                        $item['url']
                    );

                    foreach ( $item['url'] as $url )
                    {
                        if (($path_info = self::_get_pathinfo($url)) !== false)
                        {
                            self::$project   = $project;
                            self::$path_info = $path_info;
                            self::$base_url  = $url;

                            if ($admin_url)
                            {
                                foreach ( $admin_url as $url2 )
                                {
                                    # 处理后台URL不是 http:// 或 https:// 开头的形式
                                    if (($path_info_admin = self::_get_pathinfo($url2)) !== false)
                                    {
                                        self::$path_info = $path_info_admin;
                                        self::$base_url .= ltrim($url2, '/');
                                        $request_mode    = 'admin';

                                        break 3;
                                    }
                                }
                            }

                            break 2;
                        }
                    }
                }
            }
        }


        if (self::$project)
        {
            $project_dir = DIR_PROJECT . self::$project . DS;
            if (!is_dir($project_dir))
            {
                self::_show_error('not found the project: :project', array(':project' => self::$project));
            }

            # 根据URL寻找到了项目
            self::$include_path['project'] = array($project_dir);
        }
        else
        {
            if (isset(self::$config['core']['url']['admin']) && self::$config['core']['url']['admin'] && ($path_info = self::_get_pathinfo(self::$config['core']['url']['admin'])) != false)
            {
                self::$path_info = $path_info;
                self::$base_url  = self::$config['core']['url']['admin'];
                $request_mode    = 'admin';
            }
            else
            {
                if (isset(self::$config['core']['apps_url']) && is_array(self::$config['core']['apps_url']) && self::$config['core']['apps_url'])
                {
                    foreach ( self::$config['core']['apps_url'] as $app => $urls )
                    {
                        if (!$urls) continue;
                        if (!preg_match('#^[a-z0-9_]+//[a-z0-9]+$#i', $app)) continue;

                        if (!is_array($urls)) $urls = array(
                            $urls
                        );
                        foreach ( $urls as $url )
                        {
                            if (($path_info = self::_get_pathinfo($url)) != false)
                            {
                                self::$app = $app;
                                self::$path_info = $path_info;
                                self::$base_url = $url;

                                break 2;
                            }
                        }
                    }
                }

                if (null===self::$app)
                {
                    # 没有相关应用
                    if (isset(self::$config['core']['url']['apps']) && self::$config['core']['url']['apps'])
                    {
                        if (($path_info = self::_get_pathinfo(self::$config['core']['url']['apps'])) != false)
                        {
                            # 匹配到应用默认目录
                            $path_info = trim($path_info, '/');

                            self::$app = true;
                            if ($path_info)
                            {
                                $path_info_arr = explode('/', $path_info);

                                if (count($path_info_arr) >= 2)
                                {
                                    $app = array_shift($path_info_arr) . '/' . array_shift($path_info_arr);
                                    if (preg_match('#^[a-z0-9_]+//[a-z0-9]+$#i', $app))
                                    {
                                        $path_info = '/' . implode('/', $path_info_arr);
                                        self::$app = $app;
                                    }
                                }
                            }
                            self::$path_info = $path_info;
                            self::$base_url  = self::$config['core']['url']['apps'];

                            $request_mode = 'app';
                        }
                    }
                }

                if (self::$app && true!==self::$app)
                {
                    # 已获取到APP
                    $app_dir = DIR_APPS . self::$app . DS;

                    if (!is_dir($app_dir))
                    {
                        self::_show_error('can not found the app: :app', array(':app' => self::$app));
                    }

                    $request_mode = 'app';
                }
            }
        }

        # 更新BASE URL
        if ( isset(self::$config['core']['root_path']) && self::$config['core']['root_path'] && self::$base_url[0]=='/' )
        {
            self::$base_url = rtrim(self::$config['core']['root_path'],'/').'/'.ltrim(self::$base_url,'/');
        }

    }

    /**
     * 抛出系统启动时错误信息
     *
     * @param string $msg
     */
    private static function _show_error( $msg )
    {
    # 尝试加载Core类
        if ( class_exists('Core',true) )
        {
            Core::show_500($msg);
        }

        header( 'Content-Type: text/html;charset=utf-8' );

        if ( isset( $_SERVER['SERVER_PROTOCOL'] ) )
        {
            $protocol = $_SERVER['SERVER_PROTOCOL'];
        }
        else
        {
            $protocol = 'HTTP/1.1';
        }

            // HTTP status line
        header( $protocol . ' 500 Internal Server Error' );

        echo $msg;

        exit();
    }

    /**
     * 判断是否开启了在线调试
     *
     * @return boolean
     */
    private static function _is_online_debug()
    {
        if ( IS_SYSTEM_MODE )
        {
            if ( isset($_SERVER['HTTP_X_MYQEE_SYSTEM_DEBUG']) && $_SERVER['HTTP_X_MYQEE_SYSTEM_DEBUG']=='1' )
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        if ( !isset($_COOKIE['_debug_open']) ) return false;
        if ( !isset(self::$config['core']['debug_open_password']) ) return false;
        if ( !is_array( self::$config['core']['debug_open_password']) ) self::$config['core']['debug_open_password'] = array( (string) self::$config['core']['debug_open_password'] );
        foreach ( self::$config['core']['debug_open_password'] as $username => $password )
        {
            if ( $_COOKIE['_debug_open'] == self::get_debug_hash( $username , $password ) )
            {
                return true;
            }
        }

        return false;
    }

    /**
     * 获取path_info
     *
     * @return string
     */
    private static function _get_pathinfo(& $url)
    {
        $protocol = Bootstrap::protocol();
        $protocol_len = strlen($protocol);

        $url = strtolower($url);

        # 结尾补/
        if (substr($url, -1) != '/') $url .= '/';

        if (substr($url, 0, $protocol_len) == $protocol)
        {
            $len = strlen($url);
            if ( strtolower(substr($_SERVER["SCRIPT_URI"], 0, $len)) == $url )
            {
                # 匹配到项目
                return '/' . substr($_SERVER["SCRIPT_URI"], $len);
            }
        }
        else
        {
            # 开头补/
            if (substr($url, 0, 1) != '/') $url = '/' . $url;
            $len = strlen($url);

            if (strtolower(substr(Bootstrap::$path_info, 0, $len)) == $url)
            {
                # 匹配到项目
                return '/' . substr(Bootstrap::$path_info, $len);
            }
        }

        return false;
    }

    /**
     * 检查给定的pathinfo是否属于给的的项目内的URL
     *
     * @param string $u 项目的URL路径
     * @param string $pathinfo 给定的Pathinfo
     * @return boolean
     */
    private static function _check_is_this_url( $u, & $pathinfo )
    {
        if ( $u=='/' )
        {
            return true;
        }
        $u = rtrim( $u, '/' );
        if ( strpos( $u, '://' ) )
        {
            $tmppath = self::protocol() . '://' . $_SERVER["HTTP_HOST"] . '/' . ltrim( $pathinfo, '/' );
        }
        else
        {
            $tmppath = $pathinfo;
        }
        $len = strlen( $u );
        if ( $len > 0 && substr( $tmppath, 0, $len ) == $u )
        {
            $pathinfo = substr( $tmppath, $len );
            return true;
        }
        else
        {
            return false;
        }
    }

    // end
}
