<?php
/**
 * 语言包处理核心类
 *
 * @author     jonwang(jonwang@myqee.com)
 * @category   Core
 * @package    Classes
 * @subpackage Database
 * @copyright  Copyright (c) 2008-2012 myqee.com
 * @license    http://www.myqee.com/license.html
 */
abstract class Core_I18n
{
    protected static $is_setup = false;

    private static $_cache = array();

    protected static $lang = array();

    public static function setup()
    {
        if (!IS_CLI)
        {
            # 客户端语言包
            $accept_language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];

            $lang_config = Core::config('core.lang');

            # 匹配语言设置
            if (preg_match_all('#,([a-z]+-[a-z]+);#i',$accept_language,$matches))
            {
                $accept_language = $matches[1];
                $accept_language =  array_slice($accept_language,0,2);    //只取前2个语言设置
                array_map('strtolower',$accept_language);

                if ($lang_config && !in_array($lang_config,$accept_language))
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

            # 包含目录
            $include_path = Core::include_path();

            # 逆向排序，调整优先级
            krsort($include_path);

            $lang_key = implode('_',$accept_language);
            $cache_file = DIR_CACHE.'lang_serialized_cache_by_'.Core::$project.'_for_'.$lang_key;

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
                            $file = $path.'i18n'.DS.$lang.'.lang';
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
                    I18n::$lang = (array)@unserialize(file_get_contents($cache_file));
                    return;
                }
            }

            # 获取语言文件
            $lang = array();
            foreach($accept_language as $l)
            {
                foreach ($include_path as $path)
                {
                    $file = $path.'i18n'.DS.$l.'.lang';
                    if (is_file($file))
                    {
                        $tmp_arr = @parse_ini_file($file);
                        if ($tmp_arr)
                        {
                            $lang = array_merge($lang,$tmp_arr);
                        }
                    }
                }
            }

            File::create_file($cache_file, serialize($lang));

            I18n::$lang = $lang;
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
        if (isset(I18n::$lang[$string]))
        {
            return I18n::$lang[$string];
        }

        # 初始化
        if (!I18n::$is_setup)
        {
            I18n::setup();
        }

        return isset(I18n::$lang[$string])?I18n::$lang[$string]:$string;
    }
}