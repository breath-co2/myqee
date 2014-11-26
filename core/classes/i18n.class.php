<?php
/**
 * 语言包处理核心类
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Core
 * @package    Classes
 * @subpackage Database
 * @copyright  Copyright (c) 2008-2013 myqee.com
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
    protected static $cache_config = Cache::DEFAULT_CONFIG_NAME;

    /**
     * 语言包缓存时间
     *
     * @var string
     */
    protected static $cache_time = '86400~172800,1/1000';

    /**
     * 语言包缓存类型
     *
     * @var string
     */
    protected static $cache_type = Cache::TYPE_ADV_AGE;

    /**
     * 由系统回调执行
     *
     * @param array $lib
     */
    public static function import_lib_callback(array $libs)
    {
        # 清除数据，以便重新获取
        unset(I18n::$lang[Core::$project]);
        unset(I18n::$is_setup[Core::$project]);
    }

    public static function setup()
    {
        $lang_cache_key = null;
        $lang_files     = null;

        # 增加回调
        Core::import_library_add_callback('I18n::import_lib_callback');

        # 未初始化则获取数据
        if (!isset(I18n::$is_setup[Core::$project]))
        {
            # 根据类库加载信息获取key

            $lang_cache_key = I18n::get_lang_cache_key();

            try
            {
                $lang_cache = Cache::instance(I18n::$cache_config)->get($lang_cache_key);
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

                if ($lang_cache['mtime'] == $lang_files['last_mtime'])
                {
                    # 时间相同才使用
                    I18n::$lang[Core::$project]     = $lang_cache['lang'];
                    I18n::$is_setup[Core::$project] = true;
                    return;
                }
            }
        }

        # 标记为已初始化
        I18n::$is_setup[Core::$project] = true;


        # 记录各个类库的解析后的内容
        static $static_lang_array = array();

        if (null===$lang_files)
        {
            # 语言包文件
            $lang_files = I18n::find_lang_files();
        }

        # 获取语言文件
        $lang = array();

        if ($lang_files && $lang_files['files'])foreach($lang_files['files'] as $file)
        {
            if (!isset($static_lang_array[$file]))
            {
                $static_lang_array[$file] = I18n::parse_lang($file);
            }

            # 合并语言包
            if (is_array($static_lang_array[$file]) && $static_lang_array[$file])
            {
                $lang = array_merge($lang, $static_lang_array[$file]);
            }
        }

        I18n::$lang[Core::$project] = $lang;

        if ($lang_files['last_mtime'])
        {
            if (null===$lang_cache_key)
            {
                $lang_cache_key = I18n::get_lang_cache_key();
            }

            # 写缓存
            Cache::instance(I18n::$cache_config)->set($lang_cache_key, array('lang'=>$lang, 'mtime'=>$lang_files['last_mtime']), I18n::$cache_time, I18n::$cache_type);
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
        $string = strtolower(trim($string));

        if (isset(I18n::$lang[Core::$project][$string]))
        {
            return I18n::$lang[Core::$project][$string];
        }

        # 初始化
        if (!isset(I18n::$is_setup[Core::$project]))
        {
            I18n::setup();
        }
        else
        {
            return $string;
        }

        return isset(I18n::$lang[Core::$project][$string])?I18n::$lang[Core::$project][$string]:$string;
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
            unset(I18n::$is_setup[$project]);
            unset(I18n::$lang[$project]);
        }
        else
        {
            I18n::$is_setup        = array();
            I18n::$lang            = array();
            I18n::$accept_language = null;
        }
    }

    /**
     * 获取$accept_language
     *
     * @return array
     */
    protected static function accept_language()
    {
        if (null!==I18n::$accept_language)
        {
            return I18n::$accept_language;
        }

        if (($local_cookie_name = Core::config('local_lang_cookie_name')) && isset($_COOKIE[$local_cookie_name]) && preg_match('#[a-z0-9\-_]+#i', $_COOKIE[$local_cookie_name]))
        {
            # 读取COOKIE中的语言包设置
            $accept_language = (string)$_COOKIE[$local_cookie_name];
        }
        elseif (($lang_config = Core::config('lang')) && $lang_config!='auto')
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

            if (($default_lang = Core::config('default_lang')) && !in_array($default_lang, $accept_language))
            {
                $accept_language[] = $default_lang;
            }
        }

        I18n::$accept_language = $accept_language;

        return I18n::$accept_language;
    }

    /**
     * 获取语言包文件和修改时间
     *
     * @return array array('files'=>array(), 'last_mtime'=>0);
     */
    protected static function find_lang_files()
    {
        $accept_language = I18n::accept_language();

        $found = array
        (
            'files'      => array(),    // 文件列表
            'last_mtime' => 0,          // 最后修改时间
        );

        foreach (array_reverse(Core::$include_path) as $libs)
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
        $lang_key = implode('_', I18n::accept_language());
        $libs_key = array();
        foreach (Core::$include_path as $libs)
        {
            foreach ($libs as $path)
            {
                $libs_key[] = $path;
            }
        }

        $libs_key = md5(implode(',', $libs_key));
        $key      = 'lang_cache_by_' . $libs_key .'_for_'. $lang_key;

        return $key;
    }
}