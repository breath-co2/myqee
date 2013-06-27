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
 * 定义MyQEE大版本
 *
 * @var string
 */
define('MyQEE_VERSION', 'v3');

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
if (!defined('IS_CLI'))define('IS_CLI', (PHP_SAPI==='cli'));

/**
 * 是否有NameSpace（PHP5.3及以上则为true,以下为False）
 *
 * @var boolean
 */
define('HAVE_NS', version_compare(PHP_VERSION,'5.3','>=')?true:false);

/**
 * 是否系统调用模式
 *
 * @var boolean
 */
if (!defined('IS_SYSTEM_MODE'))define('IS_SYSTEM_MODE', !IS_CLI&&isset($_SERVER['HTTP_X_MYQEE_SYSTEM_HASH'])?true:false);

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
define('DIR_TEAM_LIBRARY', DIR_SYSTEM.'team_library'.DS);

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


if (!isset($dir_data  )) $dir_data   = DIR_SYSTEM  . 'data/';
if (!isset($dir_log   )) $dir_log    = $dir_data   . 'log/';
if (!isset($dir_cache )) $dir_cache  = $dir_data   . 'cache/';
if (!isset($dir_upload)) $dir_upload = DIR_WWWROOT . 'upload/';
if (!isset($dir_temp  )) $dir_temp   = null;

/**
 * 文件上传目录
 *
 * @var string
 */
define('DIR_UPLOAD', strpos($dir_upload,'://')!==false ? $dir_upload : (realpath($dir_upload)?realpath($dir_upload):DIR_WWWROOT.'upload').DS);

/**
 * 数据目录
 *
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
 * 默认用系统Temp目录
 *
 * @var string
*/
define('DIR_TEMP', strpos($dir_temp,'://')!==false ? $dir_temp : ($dir_temp && realpath($dir_temp)?realpath($dir_temp).DS:sys_get_temp_dir()));

/**
 * Log目录
 *
 * @var string
*/
define('DIR_LOG', strpos($dir_log,'://')!==false ? $dir_log : (realpath($dir_log)?realpath($dir_log):DIR_DATA.'log').DS);


unset($dir_data, $dir_cache, $dir_log, $dir_temp);


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
function __($string, array $values = null)
{
    static $have_i18n_class = false;

    if ( false===$have_i18n_class )
    {
        $have_i18n_class = (boolean)class_exists('I18n', true);
    }

    if ($have_i18n_class)
    {
        $string = I18n::get($string);
    }

    return empty($values)?$string:strtr($string, $values);
}

/**
 * 是否对传入参数转义，建议系统关闭自动转义功能
 * @var boolean
 */
define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());

if (!IS_CLI && MAGIC_QUOTES_GPC)
{

    function __stripcslashes($string)
    {
        if (is_array($string))
        {
            foreach ($string as $key => $val)
            {
                $string[$key] = __stripcslashes($val);
            }
        }
        else
        {
            $string = stripcslashes($string);
        }
        return $string;
    }
    $_GET     = __stripcslashes($_GET);
    $_POST    = __stripcslashes($_POST);
    $_COOKIE  = __stripcslashes($_COOKIE);
    $_REQUEST = __stripcslashes($_REQUEST);
}


/**
 * Include指定config文件的数据
 *
 * @param array $config
 * @param string|array $files
 */
function __include_config_file(&$config, $__files__)
{
    $__files__ = (array)$__files__;
    foreach ($__files__ as $__file__)
    {
        include $__file__;
    }
}


if (!function_exists('class_alias'))
{
    /**
     * 为一个类创建别名，模拟php5.3以后的 class_alias() 方法
     *
     * @param string $original
     * @param string $alias
     * @return boolean
     */
    function class_alias($original, $alias)
    {
        if (!class_exists($original,true))
        {
            trigger_error("Class '{$original}' not found", E_USER_WARNING);
            return false;
        }

        if (class_exists($alias,false))
        {
            trigger_error('First argument "'.$alias.'" of class_alias() must be a name of user defined class', E_USER_WARNING);
            return false;
        }

        $rf = new ReflectionClass($original);
        if ( $rf->isAbstract() )
        {
            $abs = 'abstract ';
        }
        else
        {
            $abs = '';
        }
        unset($rf);

        eval($abs . 'class ' . $alias . ' extends ' . $original . ' {}');

        return true;
    }
}

/**
 * Bootstrap
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
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
        'project'      => array(),                                   // 项目类库
        'team_library' => array('default'=>DIR_TEAM_LIBRARY),        // Team公共类库
        'library'      => array(),                                   // 类库包
        'core'         => array('core'=>DIR_CORE),                   // 核心类库
    );

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
    public static $path_info = '/';

    /**
     * 当前项目
     *
     * @var string
     */
    public static $project = 'default';

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
     * 当前项目环境配置
     *
     * !!! 此配置会继承Core总配置，除projects和core节点
     *
     * @var array
     */
    protected static $config = array();

    /**
     * Core总配置
     *
     * @var array
    */
    protected static $core_config = array();

    /**
     * 目录设置
     *
     * @var array
     */
    protected static $dir_setting = array
    (
        'class'      => array('classes'     , '.class'),
        'controller' => array('controllers' , '.controller'),
        'model'      => array('models'      , '.model'),
        'orm'        => array('orm'         , '.orm'),
    );

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
            spl_autoload_register(array('Bootstrap', 'auto_load'));

            # 读取配置
            if (!is_file(DIR_SYSTEM .'config'. EXT))
            {
                self::_show_error(__('Please rename the file config.new:EXT to config:EXT', array(':EXT'=>EXT)));
            }

            __include_config_file(self::$core_config, DIR_SYSTEM .'config'. EXT);

            # 本地调试模式
            if (isset(self::$core_config['local_debug_cfg']) && self::$core_config['local_debug_cfg'])
            {
                # 判断是否开启了本地调试
                if (function_exists('get_cfg_var'))
                {
                    $open_debug = get_cfg_var(self::$core_config['local_debug_cfg'])?1:0;
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

            # 在线调试
            if (self::_is_online_debug())
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


            # Runtime配置
            if (!isset(self::$core_config['runtime_config']))
            {
                self::$core_config['runtime_config'] = '';
            }
            else if (self::$core_config['runtime_config'] && !preg_match('#^[a-z0-9_]+$#', self::$core_config['runtime_config']))
            {
                self::$core_config['runtime_config'] = '';
            }

            if (self::$core_config['runtime_config'])
            {
                $runtime_file = DIR_SYSTEM .'config.'. self::$core_config['runtime_config'] .'.runtime'. EXT;

                # 读取配置
                if (is_file($runtime_file))
                {
                    __include_config_file(self::$core_config, $runtime_file);
                }
            }

            if (!IS_CLI)
            {
                # 输出文件头
                header('Content-Type: text/html;charset=' . self::$core_config['charset']);
            }

            // 设置错误等级
            if (isset(self::$core_config['error_reporting']))
            {
                @error_reporting(self::$core_config['error_reporting']);
            }

            // 时区设置
            if (isset(self::$core_config['timezone']))
            {
                @date_default_timezone_set(self::$core_config['timezone']);
            }


            //获取全局$project变量
            global $project, $admin_mode;

            if (isset($project) && $project)
            {
                $project = (string)$project;

                if (!isset(self::$core_config['projects'][$project]))
                {
                    self::_show_error(__('not found the project: :project', array(':project'=>$project)));
                }

                // 如果有设置项目
                self::$project = $project;

                // 管理员模式
                if (isset($admin_mode) && true===$admin_mode)
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
                    if (isset($argv[1]) && $argv[1] && isset(self::$core_config['projects'][$argv[1]]))
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

            if ( isset(self::$core_config['projects'][self::$project]['isuse']) && !self::$core_config['projects'][self::$project]['isuse'] )
            {
                self::_show_error(__('the project: :project is not open.', array(':project'=>$project)));
            }

            /**
             * 初始项目名
             *
             * @var string
             */
            define('INITIAL_PROJECT_NAME', self::$project);

            /**
             * 是否后台模式
             *
             * @var boolean
             */
            if (!defined('IS_ADMIN_MODE'))define('IS_ADMIN_MODE', (!IS_CLI && $request_mode=='admin')?true:false);

            /**
             * 静态文件URL地址前缀
             *
             * @var string
             */
            define('URL_ASSETS', isset(self::$core_config['url']['assets']) && self::$core_config['url']['assets']?self::$core_config['url']['assets']:'/assets/' );


            $project_dir = DIR_PROJECT . self::$project . DS;

            if (!is_dir($project_dir))
            {
                self::_show_error(__('not found the project: :project', array(':project' => self::$project)));
            }

            self::$include_path['project'] = array(self::$project=>$project_dir);

            # 加载类库
            self::reload_all_libraries();
        }

        Core::setup($auto_execute);
    }

    /**
     * 自动加载类
     *
     * @param string $class_name
     * @return boolean
     */
    public static function auto_load($class_name)
    {
        if (class_exists($class_name, false))return true;

        # 移除两边的
        $class_name = strtolower(trim($class_name, '\\ '));

        $class_name_array = explode('_', $class_name, 2);
        $is_alias = false;

        if ($class_name_array[0]=='core' && count($class_name_array)==2)
        {
            # 系统类库
            $ns = 'core';
            $new_class_name = $class_name_array[1];
        }
        else if ($class_name_array[0]=='ex')
        {
            # 扩展别名
            $is_alias = true;
            $new_class_name = $class_name_array[1];
        }
        else if (preg_match('#^library_((?:[a-z0-9]+)_(?:[a-z0-9]+))_([a-z0-9_]+)$#', $class_name, $m))
        {
            $ns = 'library/' . str_replace('_', '/', $m[1]);
            $new_class_name = $m[2];
        }
        else
        {
            $ns = '';
            $new_class_name = $class_name;
        }

        # 获取类的前缀
        $prefix = '';
        $new_class_arr = explode('_', $new_class_name, 2);

        if (2===count($new_class_arr))
        {
            $prefix = $new_class_arr[0];
        }

        if ($prefix && isset(self::$dir_setting[$prefix]))
        {
            $dir_setting     = self::$dir_setting[$prefix];
            $class_file_name = $new_class_arr[1];

            if ($prefix=='controller')
            {
                if (IS_SYSTEM_MODE)
                {
                    $dir_setting[0] .= '_system';
                }
                elseif (IS_CLI)
                {
                    $dir_setting[0] .= '_shell';
                }
                elseif (IS_ADMIN_MODE)
                {
                    $dir_setting[0] .= '_admin';
                }
            }

        }
        else
        {
            $dir_setting     = self::$dir_setting['class'];
            $class_file_name = $new_class_name;
        }

        if ($ns)
        {
            $file = ($ns=='core' ? DIR_CORE : DIR_LIBRARY . str_replace('_', DS,$m[1]) . DS ) . $dir_setting[0] . DS . str_replace('_', DS, $class_file_name) . $dir_setting[1] . EXT;

            if (is_file($file))
            {
                require $file;
            }
        }
        else
        {
            if (!$is_alias)
            {
                # 在include path中找
                foreach (array('project', 'team_library') as $type)
                {
                    foreach (self::$include_path[$type] as $path)
                    {
                        $tmp_file = $path . $dir_setting[0] . DS . $class_file_name . $dir_setting[1] . EXT;

                        if (is_file($tmp_file))
                        {
                            require $tmp_file;
                            if (class_exists($class_name, false))return true;
                        }
                    }
                }
            }

            # 没有找到文件且为项目类库，尝试在某个命名空间的类库中寻找
            foreach (array('library', 'core') as $type)
            {
                foreach (self::$include_path[$type] as $lib_ns=>$path)
                {
                    $ns_class_name = ($type=='library'?'library_':'') . str_replace('.', '_', $lib_ns) . '_' . $new_class_name;

                    if ( self::auto_load($ns_class_name) )
                    {
                        if (!$is_alias && class_exists($class_name, false))
                        {
                            # 在加载$ns_class_name时，当前需要的类库有可能被加载了，直接返回true
                            return true;
                        }
                        else
                        {
                            class_alias($ns_class_name, $class_name);
                        }

                        break;
                    }
                }
            }
        }

        if (class_exists($class_name, false))
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
        if (null===$ext)
        {
            $the_ext = EXT;
        }
        elseif (false===$ext || ''===$ext)
        {
            $the_ext = '';
        }
        elseif ($ext[0]!='.')
        {
            $the_ext = '.'.$ext;
        }

        # 是否只需要寻找到第一个文件
        $only_need_one_file = true;

        if ($dir == 'classes')
        {
            $file = strtolower(str_replace('_', '/', $file));
            if (null===$ext)$the_ext = '.class' . EXT;
        }
        else if ($dir == 'models')
        {
            $file = strtolower(str_replace('_', '/', $file));
            if (null===$ext)$the_ext = '.model' . EXT;
        }
        else if ( $dir == 'controllers' )
        {
            $file = strtolower(str_replace('_', '/', $file));
            if (null===$ext)$the_ext = '.controller' . EXT;

            if (IS_SYSTEM_MODE)
            {
                $dir .= '_system';
            }
            elseif (IS_CLI)
            {
                $dir .= '_shell';
            }
            elseif (IS_ADMIN_MODE)
            {
                $dir .= '_admin';
            }
        }
        elseif ($dir=='i18n' || $dir=='config')
        {
            if (null===$ext)$the_ext = '.lang';
            $only_need_one_file = false;
        }
        elseif ($dir=='views')
        {
            if (null===$ext)$the_ext = '.view' . EXT;
            $file = strtolower($file);
        }
        elseif ($dir == 'orm')
        {
            if (null===$ext)$the_ext = '.orm' . EXT;
            #orm
            $file = preg_replace('#^(.*)_[a-z0-9]+$#i', '$1', $file);
            $file = strtolower(str_replace('_', '/', $file));
        }


        # 寻找到的文件
        $found_files = array();

        # 采用当前项目目录
        $include_path = self::$include_path;

        foreach ($include_path as $the_path)
        {
            foreach ($the_path as $path)
            {
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
     * @return num 返回新加载的类库总数
     */
    public static function import_library($library_name)
    {
        if (!$library_name)return false;

        $library_name = (array)$library_name;

        # 反向排序，从最后一个开始导入
        $library_name = array_reverse($library_name);

        $config_files = array();

        $load_num = 0;
        foreach ($library_name as $lib)
        {
            $set = self::_add_include_path_lib($lib);

            if (true===$set[2])
            {
                # 已加载过
                continue;
            }

            $config_file = $set[1] .'config'. EXT;

            if (is_file($config_file))
            {
                $config_files[] = $config_file;
            }

            $load_num++;

            if (IS_DEBUG && class_exists('Core', false) && class_exists('Debug', false))Core::debug()->info('import a new library: '.Core::debug_path($lib));
        }

        if ($config_files)
        {
            __include_config_file(self::$config, $config_files);
        }

        return $load_num;
    }

    /**
     * 加入include_path类库
     *
     * @param string $lib
     * @throws Exception
     * @return array($ns, $dir, $is_already_loaded)
     */
    protected static function _add_include_path_lib($lib)
    {
        $lib = strtolower(trim($lib));
        $lib_arr = explode('.', $lib);

        if (count($lib_arr)!=3 || $lib_arr[0]!='com')
        {
            throw new Exception(__('Library name :lib error', array(':lib'=>$lib)));
        }

        $dir = DIR_LIBRARY . $lib_arr[1] . DS . $lib_arr[2] . DS;
        $ns = preg_replace('#[^a-z0-9\.]#', '', $lib_arr[1] . '.' . $lib_arr[2]);

        if (isset(self::$include_path['library'][$ns]))
        {
            return array($ns, $dir, true);
        }

        if (!is_dir($dir))
        {
            throw new Exception(__('Library :lib not exist.', array(':lib'=>$lib)));
        }

        # 合并目录
        self::$include_path['library'] = array_merge( array($ns=>$dir), self::$include_path['library']);

        return array($ns, $dir , false);
    }

    /**
     * 根据用户名和密码获取一个hash
     *
     * @param string $username
     * @param string $password
     * @return string
     */
    public static function get_debug_hash($username, $password)
    {
        $config_str = var_export(self::$core_config['debug_open_password'], true);

        return sha1($config_str . '_open$&*@debug' . $password . '_' . $username );
    }

    /**
     * 返回协议类型
     *
     * 当在命令行里执行，则返回null
     *
     * @return string null|http://|https://
     */
    public static function protocol()
    {
        static $protocol = null;

        if (null===$protocol)
        {
            if (IS_CLI)
            {
                return null;
            }
            else
            {
                $https_key = self::$core_config['server_https_on_key'];
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
     * 重新加载类库
     *
     */
    protected static function reload_all_libraries()
    {
        # 加载类库
        $lib_config = self::$core_config['libraries'];

        # 重置
        self::$include_path['library'] = array();

        foreach (array('autoload', 'cli', 'admin', 'debug') as $type)
        {
            if (!isset($lib_config[$type]) || !$lib_config[$type])continue;

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

            $libs = array_reverse((array)$lib_config[$type]);
            foreach ($libs as $lib)
            {
                self::_add_include_path_lib($lib);
            }
        }

        # 处理 library 的config
        $config_files = array();

        self::get_config_file_by_path($config_files, self::$include_path['project'],      true );
        self::get_config_file_by_path($config_files, self::$include_path['team_library'], true );
        self::get_config_file_by_path($config_files, self::$include_path['library'],      false);
        self::get_config_file_by_path($config_files, self::$include_path['core'],         false);

        if ($config_files)
        {
            # 反向排序，从最后一个开始导入
            $config_files = array_reverse($config_files);

            # 导入config
            self::$config = self::$core_config;

            # 移除特殊的key
            unset(self::$config['core']);
            unset(self::$config['projects']);

            # 载入config
            __include_config_file(self::$config, $config_files);
        }
    }

    protected static function get_config_file_by_path(&$config_files, $paths, $runtime = false)
    {
        foreach ($paths as $path)
        {
            $config_file = $path . 'config' . EXT;

            if (is_file($config_file))
            {
                $config_files[] = $config_file;
            }

            if ($runtime && self::$core_config['runtime_config'])
            {
                $config_file = $path . 'config.'. self::$core_config['runtime_config'] .'.runtime'. EXT;

                if (is_file($config_file))
                {
                    $config_files[] = $config_file;
                }
            }
        }
    }

    /**
     * 根据URL初始化
     */
    private static function setup_by_url(&$request_mode)
    {
        # 当没有$_SERVER["SCRIPT_URL"] 时拼接起来
        if (!isset($_SERVER['SCRIPT_URL']))
        {
            $tmp_uri = explode('?', $_SERVER['REQUEST_URI'] ,2);
            $_SERVER['SCRIPT_URL'] = $tmp_uri[0];
        }

        # 处理BASE_URL
        if (isset(self::$core_config['root_path']) && self::$core_config['root_path'])
        {
            self::$base_url = rtrim(self::$core_config['root_path'], '/');
        }
        else if (null === self::$base_url && isset($_SERVER["SCRIPT_NAME"]) && $_SERVER["SCRIPT_NAME"])
        {

            $base_url_len = strrpos($_SERVER["SCRIPT_NAME"], '/');
            if (false!==$base_url_len)
            {
                $base_url_len += 1;

                # 截取到最后一个/的位置
                $base_url = substr($_SERVER["SCRIPT_NAME"], 0, $base_url_len);

                if (preg_match('#^(.*)/wwwroot/$#', $base_url, $m))
                {
                    # 特殊处理wwwroot目录
                    $base_url     = $m[1] . '/';
                    $base_url_len = strlen($base_url);
                }

                if (strtolower(substr($_SERVER['REQUEST_URI'], 0, $base_url_len)) == strtolower($base_url))
                {
                    self::$base_url = $base_url;
                }
            }
        }

        # 当没有$_SERVER["SCRIPT_URI"] 时拼接起来
        if (!isset($_SERVER['SCRIPT_URI']))
        {
            $_SERVER['SCRIPT_URI'] = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on'?'https':'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_URL'];
        }

        if (isset($_SERVER['PATH_INFO']))
        {
            $pathinfo = $_SERVER['PATH_INFO'];
        }
        else
        {
            if (isset($_SERVER['REQUEST_URI']))
            {
                $request_uri = str_replace('\\', '/', $_SERVER['REQUEST_URI']);
                $root_uri    = '/'. ltrim(str_replace('\\', '/', substr($_SERVER['SCRIPT_FILENAME'], strlen($_SERVER['DOCUMENT_ROOT']))), '/');
                $index_file  = 'index'. EXT;

                if (substr($root_uri, -strlen($index_file))==$index_file)
                {
                    $root_uri = substr($root_uri, 0, -strlen($index_file));
                }

                if ($root_uri && $root_uri!='/')
                {
                    $request_uri = substr($request_uri, strlen($root_uri));
                }

                list($pathinfo) = explode('?', $request_uri, 2);
                $pathinfo = '/'. ltrim($pathinfo, '/');
            }
            elseif (isset($_SERVER['PHP_SELF']))
            {
                $pathinfo = $_SERVER['PHP_SELF'];
            }
            elseif (isset($_SERVER['REDIRECT_URL']))
            {
                $pathinfo = $_SERVER['REDIRECT_URL'];
            }
            else
            {
                $pathinfo = false;
            }
        }

        # 过滤pathinfo传入进来的服务器默认页
        if (false !== $pathinfo && ($indexpagelen = strlen(self::$core_config['server_index_page'])) && substr($pathinfo, -1 - $indexpagelen) == '/' . self::$core_config['server_index_page'])
        {
            $pathinfo = substr($pathinfo, 0, -$indexpagelen);
        }
        $pathinfo = trim($pathinfo);

        if (!isset($_SERVER["PATH_INFO"]))
        {
            $_SERVER["PATH_INFO"] = $pathinfo;
        }

        self::$path_info = $pathinfo;

        # 处理项目
        foreach (self::$core_config['projects'] as $project => $item)
        {
            if (!preg_match('#^[a-z0-9_]+$#i', $project)) continue;

            $admin_url = array();
            if (isset($item['admin_url']) && $item['admin_url'])
            {
                foreach ((array)$item['admin_url'] as $tmp_url)
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
                foreach ((array)$item['url'] as $url)
                {
                    if (($path_info = self::_get_pathinfo($url)) !== false)
                    {
                        self::$project   = $project;
                        self::$path_info = $path_info;
                        self::$base_url  = $url;

                        if ($admin_url)
                        {
                            foreach ($admin_url as $url2)
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

    /**
     * 抛出系统启动时错误信息
     *
     * @param string $msg
     */
    private static function _show_error($msg)
    {
        # 尝试加载Core类
        if (class_exists('Core', true))
        {
            Core::show_500($msg);
        }

        header('Content-Type: text/html;charset=utf-8');

        if (isset($_SERVER['SERVER_PROTOCOL']))
        {
            $protocol = $_SERVER['SERVER_PROTOCOL'];
        }
        else
        {
            $protocol = 'HTTP/1.1';
        }

            // HTTP status line
        header($protocol.' 500 Internal Server Error');

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
        if (IS_SYSTEM_MODE)
        {
            if (isset($_SERVER['HTTP_X_MYQEE_SYSTEM_DEBUG']) && $_SERVER['HTTP_X_MYQEE_SYSTEM_DEBUG']=='1')
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        if (!isset($_COOKIE['_debug_open'])) return false;
        if (!isset(self::$core_config['debug_open_password']) || !is_array(self::$core_config['debug_open_password'])) return false;
        foreach (self::$core_config['debug_open_password'] as $username => $password)
        {
            if ($_COOKIE['_debug_open'] == self::get_debug_hash($username, $password))
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
        $protocol = self::protocol();
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

            if (strtolower(substr(self::$path_info, 0, $len)) == $url)
            {
                # 匹配到项目
                return '/' . substr(self::$path_info, $len);
            }
        }

        return false;
    }
}
