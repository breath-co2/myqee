<?php
/**
 * 语言包处理核心类
 *
 * @author     jonwang(jonwang@myqee.com)
 * @category   Core
 * @package    Classes
 * @subpackage Database
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
abstract class Core_I18n
{
    /**
     * 缓存配置
     *
     * @var string
     */
    public static $cache_config = 'default';

    protected static $is_setup = array();

    protected static $lang = array();

    public static function setup()
    {
        # 获取用户语言
        $accept_language = I18n::accept_language();

        $lang_key = implode('_',$accept_language);

        $key = 'lang_cache_by_' . Core::$project . '_mode_' . (IS_ADMIN_MODE?'admin':'default') . '_for_' . $lang_key;

        # 获取缓存数据
        $lang = Cache::instance(I18n::$cache_config)->get($key);

        if ($lang)
        {
            I18n::$lang[Core::$project]     = $lang;
            I18n::$is_setup[Core::$project] = true;
            return;
        }

        # 逆向排序，调整优先级
        $accept_language = array_reverse($accept_language);

        # 记录各个类库的解析后的内容
        static $static_lib_array = array();

        # 获取语言文件
        $lang = array();

        foreach (array_reverse(Core::$include_path) as $ns=>$libs)
        {
            $libs = array_reverse($libs);
            foreach ($libs as $path)
            {
                foreach($accept_language as $l)
                {
                    if (isset($static_lib_array[$ns][$l]))
                    {
                        $lang = array_merge($lang, $static_lib_array[$ns][$l]);
                    }
                    else
                    {
                        $file = $path . 'i18n' . DS . $l . '.lang';

                        if (is_file($file))
                        {
                            $static_lib_array[$ns][$l] = (array)@parse_ini_file($file);
                        }
                        else
                        {
                            $static_lib_array[$ns][$l] = array();
                        }

                        # 合并语言包
                        if ($static_lib_array[$ns][$l])
                        {
                            $lang = array_merge($lang, $static_lib_array[$ns][$l]);
                        }
                    }
                }
            }
        }

        I18n::$lang[Core::$project]     = $lang;
        I18n::$is_setup[Core::$project] = true;

        # 写缓存
        Cache::instance(I18n::$cache_config)->set($key, $lang, '86400~172800,1/1000', Cache::TYPE_ADV_AGE);
    }

    /**
     * 返回一个语言包语句
     *
     * @param string $string
     * @return string
     */
    public static function get($string)
    {
        $string = trim($string);

        if (isset(I18n::$lang[Core::$project][$string]))
        {
            return I18n::$lang[Core::$project][$string];
        }

        # 初始化
        if (!isset(I18n::$is_setup[Core::$project]))
        {
            I18n::setup();
        }

        return isset(I18n::$lang[Core::$project][$string])?I18n::$lang[Core::$project][$string]:$string;
    }

    /**
     * 获取$accept_language
     *
     * @return array
     */
    protected static function accept_language()
    {
        # 客户端语言包
        $accept_language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])?$_SERVER['HTTP_ACCEPT_LANGUAGE']:null;

        $lang_config = Core::config('core.lang');

        # 匹配语言设置
        if ($accept_language && preg_match_all('#,([a-z]+-[a-z]+);#i', $accept_language, $matches))
        {
            $accept_language = $matches[1];
            $accept_language =  array_slice($accept_language, 0, 2);    //只取前2个语言设置
            array_map('strtolower', $accept_language);

            if ($lang_config && !in_array($lang_config, $accept_language))
            {
                $accept_language[] = $lang_config;
            }
        }
        else
        {
            if ($lang_config)
            {
                $accept_language = array($lang_config);
            }
            else
            {
                $accept_language = array('zh-cn');
            }
        }

        return $accept_language;
    }
}