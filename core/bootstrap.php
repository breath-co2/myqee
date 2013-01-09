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
define( 'START_MEMORY', memory_get_usage() );

/**
 * PHP文件后缀
 *
 * @var string
 */
define( 'EXT', '.php' );

/**
 * 系统当前时间
 *
 * @var int
 */
define( 'TIME', time() );

/**
 * 目录分隔符简写
 *
 * @var string
 */
define( 'DS', DIRECTORY_SEPARATOR );

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
define('IS_MBSTRING',extension_loaded('mbstring')?true:false);

/**
 * 是否命令行执行
 *
 * @var boolean
 */
define('IS_CLI',(PHP_SAPI==='cli'));

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
define('DIR_SYSTEM', realpath(__DIR__.DS.'..'.DS).DS);

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
 * Data目录
 *
 * @var string
*/
define('DIR_DATA', DIR_SYSTEM.'data'.DS);

/**
 * Cache目录
 *
 * @var string
*/
define('DIR_CACHE', DIR_DATA.'cache'.DS);

/**
 * Temp目录
 *
 * @var string
*/
define('DIR_TEMP', DIR_DATA.'temp'.DS);

/**
 * Log目录
 *
 * @var string
*/
define('DIR_LOG', DIR_DATA.'log'.DS);

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


function __load_boot__()
{
    if ( !Bootstrap::$include_path )
    {
        # 当在项目初始化之前发生错误（比如项目不存在），调用系统Core类库
        Bootstrap::$include_path = array
        (
        	DIR_CORE,
    	);

    	# 注册自动加载类
        spl_autoload_register( array( 'Bootstrap', 'auto_load' ) );
    }
}


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

if ( MAGIC_QUOTES_GPC )
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
 * Bootstrap
 *
 * @author     jonwang(jonwang@myqee.com)
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2012 myqee.com
 * @license    http://www.myqee.com/license.html
 */
abstract class Bootstrap
{

    /**
     * 版本号
     *
     * @var float
     */
    const VERSION = '1.9.3';

    /**
     * 包含目录
     *
     * array(
     * 	 'test1',
     * 	 'test2',
     * )
     *
     * @var array
     */
    public static $include_path;

    /**
     * 系统所在的根目录
     *
     * @var string
     */
    public static $base_url = null;

    /**
     * 系统配置
     *
     * @var array
     */
    public static $config = array();

    /**
     * 所有项目的config配置
     *
     * array('projectName'=>array(...))
     * @var array
     */
    protected static $config_projects = array();

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
     * 自动加载类
     * @param string $class 类名称
     */
    public static function auto_load( $class )
    {
        if ( class_exists($class, false) ) return true;
        static $core_loaded = false;
        if ( !$core_loaded )
        {
            $core_loaded = class_exists( 'Core', false );
        }
        if ( $core_loaded )
        {
            # 如果Core已加载则采用Core的auto_load方法
            return Core::auto_load( $class );
        }

        $file = 'classes/' . str_replace( '_','/', strtolower($class) ) . '.class' . EXT;
        if ( isset(self::$file_list[self::$project]) )
        {
            # 读取优化文件列表
            if ( isset( self::$file_list[self::$project][$file] ) )
            {
                require self::$file_list[self::$project][$file];
                return true;
            }
            else
            {
                return false;
            }
        }

        foreach ( self::$include_path as $path )
        {
            $tmpfile = $path . $file;
            if ( is_file($tmpfile) )
            {
                require $tmpfile;
                return true;
            }
        }
        return false;
    }

    /**
     * 系统启动函数
     */
    public static function setup( $auto_run = true )
    {
        static $run = null;
        if ( true === $run )
        {
            if ( true === $auto_run )
            {
                Core::setup( true );
            }
            return true;
        }
        $run = true;

        # 读取系统配置
        if ( !is_file(DIR_SYSTEM . 'config' . EXT) )
        {
            self::_throw_sys_error_msg( __('Please rename the file config.new:EXT to config:EXT' , array(':EXT'=>EXT)) );
        }
        self::_include_config_file( self::$config['core'] , DIR_SYSTEM.'config'.EXT );

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
            self::_include_config_file( self::$config['core'] , DIR_SYSTEM.'debug.config'.EXT );
        }

        # 在线调试
        if ( self::is_online_debug() )
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


        if ( !IS_CLI )
        {
            # 输出文件头
            header( 'Content-Type: text/html;charset=' . self::$config['core']['charset'] );
        }

        if ( !isset( self::$config['core']['projects'] ) || !self::$config['core']['projects'] )
        {
            self::_throw_sys_error_msg( __('Please create a new project.') );
        }

        if ( isset(self::$config['core']['url']['site']) && null!==self::$config['core']['url']['site'] )
        {
            define('URL_SITE', self::$config['core']['url']['site']);
        }
        elseif ( !IS_CLI )
        {
            $script_arr = explode('/',$_SERVER['SCRIPT_URI']);
            define('URL_SITE', $script_arr[0].'//'.$script_arr[2].'/');
        }
        else
        {
            define('URL_SITE', '/');
        }

        if ( isset(self::$config['core']['url']['assets']) )
        {
            $assets_url = self::$config['core']['url']['assets'];
        }
        else
        {
            $assets_url = '/asstes/';
        }
        define('URL_ASSETS', $assets_url);
        unset($assets_url);

        // 设置错误等级
        if ( isset(self::$config['core']['error_reporting'] ) )
        {
            @error_reporting( self::$config['core']['error_reporting'] );
        }

        // 时区设置
        if ( isset( self::$config['core']['timezone'] ) )
        {
            @date_default_timezone_set( self::$config['core']['timezone'] );
        }

        //获取全局$project变量
        global $project,$admin_mode;

        if ( isset($project) && $project )
        {
            $project = (string)$project;

            if (!isset(self::$config['core']['projects'][$project]))
            {
                self::_throw_sys_error_msg( __('not found the project: :project',array(':project'=>$project)) );
            }

            // 如果有设置项目
            $now_project = $project;

            // 管理员模式
            if ( isset($admin_mode) && true===$admin_mode )
            {
                $is_admin_url = true;
            }
        }
        else
        {
            $is_admin_url = false;
            $now_project = null;

            if (IS_CLI)
            {
                if ( isset($_SERVER['OS']) && $_SERVER['OS']=='Windows_NT' )
                {
                    # 切换到UTF-8编码显示状态
                    exec('chcp 65001');
                }

                if ( !isset( $_SERVER["argv"] ) )
                {
                    exit( 'Err Argv' );
                }
                $argv = $_SERVER["argv"];
                //$argv[0]为文件名
                if ( isset( $argv[1] ) )
                {
                    $project = $argv[1];
                    if ( isset(self::$config['core']['projects'][$project]) )
                    {
                        $now_project = $project;
                        $project = self::$config['core']['projects'][$project];
                    }
                    else
                    {
                        $project = false;
                    }
                }
                else
                {
                    $project = false;
                }

                array_shift( $argv ); //将文件名移除
                array_shift( $argv ); //将项目名移除
                self::$path_info = trim( implode( '/', $argv ) );
            }
            else
            {
                self::$path_info = self::_get_pathinfo();
                $project_url = false;
                foreach ( self::$config['core']['projects'] as $k => &$item )
                {
                    if (!isset($item['url']))$item['url'] = array();
                    if ( !is_array($item['url']) )
                    {
                        $item['url'] = array((string)$item['url']);
                    }
                    $tmp_pathinfo = self::$path_info;
                    foreach ( $item['url'] as $index=>$u )
                    {
                        if ( self::_check_is_this_url( $u, self::$path_info ) )
                        {
                            $project_url = $u;
                            break;
                        }
                    }

                    if ( false !== $project_url )
                    {
                        if ( isset($item['url_admin']) && $item['url_admin'] )
                        {
                            if ( !strpos($item['url_admin'],'://') )
                            {
                                $tmp_pathinfo = self::$path_info;
                            }
                            if ( self::_check_is_this_url( $item['url_admin'], $tmp_pathinfo ) )
                            {
                                self::$path_info = $tmp_pathinfo;
                                $is_admin_url = true;
                            }
                        }

                        self::$project_url = $project_url;
                        $now_project = $k;
                        break;
                    }
                }
            }

            if ( !$now_project )
            {
                if ( IS_CLI )
                {
                    # 命令行下执行
                    echo 'use:'.CRLF;
                    foreach ( self::$config['core']['projects'] as $k=>$item )
                    {
                        if ( isset($item['isuse']) && !$item['isuse'] )continue;
                        echo "    ".$k.CRLF;
                    }
                    return true;
                }

                if ( isset( self::$config['core']['projects']['default'] ) )
                {
                    $now_project = 'default';
                }
                else
                {
                    self::_throw_sys_error_msg( __('not found the project: :project',array(':project'=>$now_project)) );
                }
            }

        }

        /**
         * 是否后台管理模式
         *
         * @var boolean
         */
        define('IS_ADMIN_MODE', $is_admin_url);

        /**
         * 初始项目名
         * @var string
         */
        define( 'INITIAL_PROJECT_NAME', $now_project );

        // 设置项目
        self::set_project( $now_project );

        # 注册自动加载类
        spl_autoload_register( array( 'Bootstrap', 'auto_load' ) );

        # 加载系统核心
        Core::setup( $auto_run );
    }

    /**
     * 设置项目
     * 可重新设置新项目已实现程序内项目切换，但需谨慎使用
     * @param string $project
     */
    public static function set_project( $project )
    {
        if ( self::$project == $project )
        {
            return true;
        }

        static $core_config = null;

        if (null===$core_config)
        {
            # 记录原始Core配置
            $core_config = self::$config['core'];
        }

        if ( !isset($core_config['projects'][$project] ) )
        {
            self::_throw_sys_error_msg( __('not found the project: :project.',array(':project'=>$project) ) );
        }
        if ( !$core_config['projects'][$project]['isuse'] )
        {
            self::_throw_sys_error_msg( __('the project: :project is not open.' , array(':project'=>$project) ) );
        }

        # 记录所有项目设置，当切换回项目时，使用此设置还原
        static $all_prjects_setting = array();

        if ( self::$project )
        {
            // 记录上一个项目设置
            $all_prjects_setting[self::$project] = array
            (
                'config'         => self::$config,
                'include_path'   => self::$include_path,
                'file_list'      => self::$file_list,
                'project_dir'    => self::$project_dir,
                'base_url'       => self::$base_url,
            );
        }

        # 设为当前项目
        self::$project = $project;

        # 记录debug信息
        if ( IS_DEBUG && class_exists( 'Core', false ) )
        {
            Core::debug()->info( '程序已切换到了新项目：' . $project );
        }

        if ( isset($all_prjects_setting[$project]) )
        {
            # 还原配置
            self::$config         = $all_prjects_setting[$project]['config'];
            self::$include_path   = $all_prjects_setting[$project]['include_path'];
            self::$file_list      = $all_prjects_setting[$project]['file_list'];
            self::$project_dir    = $all_prjects_setting[$project]['project_dir'];
            self::$base_url       = $all_prjects_setting[$project]['base_url'];
        }
        else
        {
            self::$config = array
            (
                'core' => $core_config,
            );

            if (!isset($core_config['projects'][$project]['dir']) || !$core_config['projects'][$project]['dir'])
            {
                self::_throw_sys_error_msg( __('the project ":project" dir is not defined.' , array(':project'=>$project)) );
            }

            # 项目路径
            $project_dir = realpath( DIR_PROJECT . $core_config['projects'][$project]['dir'] );
            if ( !$project_dir || !is_dir( $project_dir ) )
            {
                self::_throw_sys_error_msg( __('the project dir :dir is not exist.' , array(':dir'=>$core_config['projects'][$project]['dir'])) );
            }
            $project_dir .= DS;
            self::$project_dir = $project_dir;

            # 读取项目配置
            if ( is_file( $project_dir . 'config' . EXT ) )
            {
                self::_include_config_file( self::$config['core'], $project_dir . 'config' . EXT );
            }

            # 读取DEBUG配置
            if ( isset($core_config['debug_config']) && $core_config['debug_config'] && is_file($project_dir.'debug.config'.EXT) )
            {
                self::_include_config_file( self::$config['core'] , $project_dir.'debug.config'.EXT );
            }

            # 设置包含目录
            self::$include_path = self::get_project_include_path($project);

            # 处理base_url
            if ( isset($core_config['projects'][$project]['url']) && $core_config['projects'][$project]['url'] )
            {
                self::$base_url = current((array)$core_config['projects'][$project]['url']);
                foreach ((array)$core_config['projects'][$project]['url'] as $u)
                {
                    if ( preg_match('#^http(s)?://(.*)$#i', $u) )
                    {
                        if ( strtolower(substr($_SERVER['SCRIPT_URI'], 0, strlen($u)))==strtolower($u) )
                        {
                            self::$base_url = $u;
                            break;
                        }
                    }
                    else if (substr($u,0,1)=='/')
                    {
                        self::$base_url = $u;
                        break;
                    }
                }
            }

            if (IS_ADMIN_MODE)
            {
                if (isset($core_config['projects'][$project]['url_admin']) && $core_config['projects'][$project]['url_admin'])
                {
                    $url = null;
                    foreach ((array)$core_config['projects'][$project]['url_admin'] as $u)
                    {
                        if ( preg_match('#^http(s)?://(.*)$#i', $u) )
                        {
                            if ( strtolower(substr($_SERVER['SCRIPT_URI'], 0, strlen($u)))==strtolower($u) )
                            {
                                $url = $u;
                                break;
                            }
                        }
                        else if (substr($u,0,1)=='/')
                        {
                            // 拼接后台地址
                            $url = rtrim(self::$base_url,'/').$u;
                            break;
                        }
                    }

                    if ($url)
                    {
                        self::$base_url = $url;
                    }
                    else
                    {
                        self::$base_url = rtrim(self::$base_url,'/').current((array)$core_config['projects'][$project]['url_admin']);
                    }
                }
            }
        }

        if ( class_exists('Core',false) )
        {
            # 输出调试信息
            if ( IS_DEBUG )
            {
                Core::debug()->group( '当前加载目录' );
                foreach ( self::$include_path as $value )
                {
                    Core::debug()->log( Core::debug_path($value) );
                }
                Core::debug()->groupEnd();
            }

            Core::ini_library();
        }
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
     * 将项目切换回初始项目
     *
     * 当使用Core::set_project()设置切换过项目后，可使用此方法返回初始化时的项目
     */
    public static function reset_project()
    {
        if ( defined( 'INITIAL_PROJECT_NAME' ) && INITIAL_PROJECT_NAME != self::$project )
        {
            self::set_project( INITIAL_PROJECT_NAME );
        }
    }

    /**
     * 返回协议类型
     *
     * 当在命令行里执行，则返回null
     *
     * @return null/http/https
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

                    $protocol = 'https';
                }
                else
                {
                    $protocol = 'http';
                }
            }
        }

        return $protocol;
    }

    /**
     * 判断是否开启了在线调试
     *
     * @return boolean
     */
    public static function is_online_debug()
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
     * 根据密码获取一个hash
     *
     * @param string $password
     * @return string
     */
    public static function get_debug_hash($username,$password)
    {
        static $config_str = null;
        if (null===$config_str)$config_str = var_export(self::$config['core']['debug_open_password'],true);
        return md5($config_str.'_open$&*@debug'.$username.'_'.$password);
    }

    /**
     * 获取指定config文件的数据
     *
     * @param string $file
     * @return array $config
     */
    protected static function _include_config_file( &$config , $file )
    {
        include $file;

        return $config;
    }

    /**
     * 获取path_info
     *
     * @return string
     */
    private static function _get_pathinfo()
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
        if ( false !== $pathinfo && ($indexpagelen = strlen( self::$config['core']['server_index_page'] )) && substr( $pathinfo, -1-$indexpagelen ) == '/' . self::$config['core']['server_index_page'] )
        {
            $pathinfo = substr( $pathinfo, 0, - $indexpagelen );
        }
        if ( !isset($_SERVER['PATH_INFO']) )
        {
            $_SERVER['PATH_INFO'] = $pathinfo;
        }

        $pathinfo = trim($pathinfo);

        return $pathinfo;
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

    /**
     * 抛出系统启动时错误信息
     * @param string $msg
     */
    private static function _throw_sys_error_msg( $msg )
    {
        __load_boot__();

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
}
