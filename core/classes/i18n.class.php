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
    protected static $is_setup = false;

    protected static $lang = array();

    public static function setup()
    {
        if (!IS_CLI)
        {
            # 包含目录
            $include_path = Core::include_path();

            # 逆向排序，调整优先级
            $include_path = array_reverse($include_path);

            $accept_language = I18n::accept_language();

            $lang_key = implode('_',$accept_language);
            $cache_file = DIR_CACHE . 'lang_cache_by_' . Core::$project . '_mode_' . (IS_ADMIN_MODE?'admin':'default') . '_for_' . $lang_key;

            if (is_file($cache_file))
            {
                $changed = false;
                $last_mtime = filemtime($cache_file);
                if ($last_mtime)
                {
                    foreach($accept_language as $lang)
                    {
                        foreach ($include_path as $path)
                        {
                            $file = $path . 'i18n' . DS . $lang . '.lang';
                            if (is_file($file))
                            {
                                if ($last_mtime<filemtime($file))
                                {
                                    $changed = true;
                                    break 2;
                                }
                            }
                        }
                    }
                }

                # 没有修改过
                if (!$changed)
                {
                    I18n::$lang[Core::$project] = (array)@unserialize(file_get_contents($cache_file));
                    return;
                }
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

                            if ($static_lib_array[$ns][$l])
                            {
                                $lang = array_merge($lang, $static_lib_array[$ns][$l]);
                            }
                        }
                    }
                }
            }

            File::create_file($cache_file, serialize($lang));

            I18n::$lang[Core::$project] = $lang;
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
        if (isset(I18n::$lang[Core::$project][$string]))
        {
            return I18n::$lang[Core::$project][$string];
        }

        # 初始化
        if (!I18n::$is_setup)
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
        $accept_language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];

        $lang_config = Core::config('core.lang');

        # 匹配语言设置
        if (preg_match_all('#,([a-z]+-[a-z]+);#i', $accept_language, $matches))
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