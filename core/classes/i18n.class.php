<?php
/**
 * 语言包处理核心类
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Core
 * @package    Classes
 * @subpackage Database
 * @copyright  Copyright (c) 2008-2016 myqee.com
 * @license    http://www.myqee.com/license.html
 */
abstract class Core_I18n
{
    /**
     * 是否安装了语言包
     *
     * @var array
     */
    protected static $is_setup = array();

    /**
     * 语言包缓存数据
     *
     * @var string
     */
    protected static $lang = array();

    /**
     * 当前用户的接受的语言
     *
     * @var string
     */
    protected static $accept_language = null;

    /**
     * 缓存配置
     *
     * @var string
     */
    protected static $cache_config = null;

    /**
     * 系统 Core 类库是否加载完毕
     *
     * @var bool
     */
    private static $core_initialized = false;

    /**
     * 由系统回调执行
     *
     * @param array $lib
     */
    public static function import_lib_callback(array $libs)
    {
        # 清除数据，以便重新获取
        unset(self::$lang[Core::$project]);
        unset(self::$is_setup[Core::$project]);
    }

    public static function setup()
    {
        # 记录各个类库的解析后的内容
        static $static_lang_array = array();

        $lang_cache_key = null;
        $lang_files     = null;

        if (self::$core_initialized)
        {
            if (isset(self::$lang['..core..']))
            {
                unset(self::$lang['..core..']);
            }

            # 增加回调
            Core::event_add('system.import_library', array('I18n', 'import_lib_callback'));

            # 未初始化则获取数据
            if (!isset(self::$is_setup[Core::$project]))
            {
                # 根据类库加载信息获取key

                $lang_cache_key = I18n::get_lang_cache_key();

                try
                {
                    $lang_cache = Cache::instance(self::$cache_config)->get($lang_cache_key);
                }
                catch(Exception $e)
                {
                    # 避免在Exception中调用__()方法后导致程序陷入死循环
                    $lang_cache = null;
                }

                if ($lang_cache)
                {
                    # 语言包文件
                    $lang_files = I18n::find_lang_files();

                    if ($lang_cache['mtime'] === $lang_files['last_mtime'])
                    {
                        # 时间相同才使用
                        self::$lang[Core::$project]     = $lang_cache['lang'];
                        self::$is_setup[Core::$project] = true;
                        return;
                    }
                }
            }

            # 标记为已初始化
            self::$is_setup[Core::$project] = true;
        }

        if (null === $lang_files)
        {
            # 语言包文件
            $lang_files = self::$core_initialized ? I18n::find_lang_files() : self::find_lang_files();
        }

        # 获取语言文件
        $lang = array();

        if ($lang_files && $lang_files['files'])foreach($lang_files['files'] as $file)
        {
            if (!isset($static_lang_array[$file]))
            {
                $static_lang_array[$file] = self::$core_initialized ? I18n::parse_lang($file) : self::parse_lang($file);
            }

            # 合并语言包
            if (is_array($static_lang_array[$file]) && $static_lang_array[$file])
            {
                $lang = array_merge($lang, $static_lang_array[$file]);
            }
        }

        if (self::$core_initialized)
        {
            self::$lang[Core::$project] = $lang;

            if ($lang_files['last_mtime'])
            {
                if (null === $lang_cache_key)
                {
                    $lang_cache_key = I18n::get_lang_cache_key();
                }

                # 写缓存
                Cache::instance(self::$cache_config)->set($lang_cache_key, array('lang'=>$lang, 'mtime'=>$lang_files['last_mtime']), Core::config('lang_cache_time', '2592000~5184000,1/10000'), Cache::TYPE_ADV_AGE);
            }
        }
        else
        {
            self::$lang['..core..'] = $lang;
        }
    }

    /**
     * 返回一个语言包语句
     *
     * @param string $string
     * @return string
     */
    public static function get($string)
    {
        $string    = trim($string);
        $lower_str = strtolower($string);

        if (false === self::$core_initialized)
        {
            if (class_exists('Core', false))
            {
                self::$core_initialized = true;
            }
        }

        if (self::$core_initialized)
        {
            $p = Core::$project;
        }
        else
        {
            $p = '..core..';
        }

        if (isset(self::$lang[$p][$lower_str]))
        {
            return self::$lang[$p][$lower_str];
        }

        # 初始化
        if (!isset(self::$is_setup[$p]))
        {
            self::setup();
        }
        else
        {
            return $string;
        }

        return isset(self::$lang[$p][$lower_str]) ? self::$lang[$p][$lower_str] : $string;
    }

    /**
     * 重置所有设置
     *
     * 将会清理掉所有已读取的语言包
     *
     * @param null $project 指定项目，不设置则清理全部
     */
    public static function reset($project = null)
    {
        if ($project)
        {
            unset(self::$is_setup[$project]);
            unset(self::$lang[$project]);
        }
        else
        {
            self::$is_setup        = array();
            self::$lang            = array();
            self::$accept_language = null;
        }
    }

    /**
     * 获取$accept_language
     *
     * @return array
     */
    protected static function accept_language()
    {
        if (null !== self::$accept_language)
        {
            return self::$accept_language;
        }

        if (self::$core_initialized && ($local_cookie_name = Core::config('local_lang_cookie_name')) && isset($_COOKIE[$local_cookie_name]) && preg_match('#[a-z0-9\-_]+#i', $_COOKIE[$local_cookie_name]))
        {
            # 读取COOKIE中的语言包设置
            $accept_language = (string)$_COOKIE[$local_cookie_name];
        }
        elseif (self::$core_initialized && ($lang_config = Core::config('lang')) && $lang_config !== 'auto')
        {
            # 系统设置的语言包
            $accept_language = explode(',', $lang_config);
        }
        else
        {
            # 客户端语言包
            $language        = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : null;
            $accept_language = array();

            # 匹配语言设置
            # zh-CN,zh;q=0.8,zh-TW;q=0.6
            if ($language && preg_match_all('#([a-z]+(?:\-[a-z]+)?),|([a-z]+\-[a-z]+);#i', $language, $matches))
            {
                $accept_language    = $matches[0];
                $accept_language    = array_values(array_slice($accept_language, 0, 2));    //只取前2个语言设置
                $accept_language[0] = strtolower(rtrim($accept_language[0], ';,'));
                if (isset($accept_language[1]))
                {
                    $accept_language[1] = strtolower(rtrim($accept_language[1], ';,'));
                }
            }

            if (self::$core_initialized && ($default_lang = Core::config('default_lang')) && !in_array($default_lang, $accept_language))
            {
                $accept_language[] = $default_lang;
            }

            /*
            $accept_language 整理之前
            Array
            (
                [0] => ko-kr
                [1] => en-us
                [2] => zh-cn
            )
            $accept_language 整理之后
            Array
            (
                [0] => ko-kr
                [1] => ko
                [2] => en-us
                [3] => en
                [4] => zh-cn
                [5] => zh
            )
            */
            $renew_accept_language = array();
            foreach($accept_language as $item)
            {
                $sub_lang = explode('-', $item);

                $renew_accept_language[] = $item;
                if (count($sub_lang) > 1)
                {
                    $renew_accept_language[] = $sub_lang[0];
                }
            }
            $accept_language = array_unique($renew_accept_language);
        }

        self::$accept_language = $accept_language;

        return self::$accept_language;
    }

    /**
     * 获取语言包文件和修改时间
     *
     * @return array array('files'=>array(), 'last_mtime'=>0);
     */
    protected static function find_lang_files()
    {
        if (self::$core_initialized)
        {
            $accept_language = I18n::accept_language();
            $include_path    = array_reverse(Core::$include_path);
        }
        else
        {
            $accept_language = self::accept_language();
            $include_path    = array_reverse(Bootstrap::$include_path);
        }

        $found = array
        (
            'files'      => array(),    // 文件列表
            'last_mtime' => 0,          // 最后修改时间
        );

        foreach ($include_path as $libs)
        {
            $libs = array_reverse($libs);
            foreach ($libs as $path)
            {
                foreach($accept_language as $lang)
                {
                    $file = $path .'i18n'. DS . $lang .'.lang';

                    if (is_file($file))
                    {
                        $found['files'][]    = $file;
                        $found['last_mtime'] = max($found['last_mtime'], filemtime($file));
                    }
                }
            }
        }

        return $found;
    }

    /**
     * 解析语言包文件
     *
     * @param $file
     * @return array
     */
    protected static function parse_lang($file)
    {
        $str = file_get_contents($file);

        $str = explode("\n", $str);

        $rs = array();

        foreach ($str as $item)
        {
            $item = trim($item);
            if (!$item)continue;

            $item0 = $item[0];
            if ($item0=='[' || $item0==';' || $item0=='#')continue;

            $item = explode('=', str_replace(array('\\n', "\\'", '\\"'), array("\n", "'", '"'),$item), 2);

            $rs[strtolower(trim($item[0]))] = trim($item[1]);
        }

        return $rs;
    }

    protected static function get_lang_cache_key()
    {
        # 当前语言key
        $libs_key = array();
        foreach (Core::$include_path as $libs)
        {
            foreach ($libs as $path)
            {
                $libs_key[] = $path;
            }
        }

        $libs_key = md5(implode(',', $libs_key));
        $key      = 'lang_cache_by_' . $libs_key .'_for_'. implode('_', I18n::accept_language());

        return $key;
    }
}