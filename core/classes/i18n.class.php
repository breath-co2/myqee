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
     * 缓存配置
     *
     * @var string
     */
    public static $cache_config = 'default';

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
     * 由系统回调执行
     *
     * @param array $lib
     */
    public static function import_lib_callback(array $libs)
    {
        # 清楚数据，以便重新获取
        unset(I18n::$lang[Core::$project]);
        unset(I18n::$is_setup[Core::$project]);
    }

    public static function setup()
    {
        # 增加回调
        Core::import_library_add_callback('I18n::import_lib_callback');

        # 获取用户语言
        $accept_language = I18n::accept_language();

        $lang_key = implode('_', $accept_language);

        try
        {
            static $run = 0;
            $run++;

            # 获取缓存数据
            if ($run==1)
            {
                # 根据类库加载信息获取key
                $libs_key = array();
                foreach (array_reverse(Core::$include_path) as $libs)
                {
                    $libs = array_reverse($libs);
                    foreach ($libs as $k=>$path)
                    {
                        $libs_key[] = $libs.'.'.$k;
                    }
                }

                $libs_key = md5(implode(',', $libs_key));
                $key      = 'lang_cache_by_' . $libs_key . '_for_' . $lang_key;
                $lang     = Cache::instance(I18n::$cache_config)->get($key);

                if ($lang)
                {
                    I18n::$lang[Core::$project]     = $lang;
                    I18n::$is_setup[Core::$project] = true;
                    return;
                }
            }
        }
        catch(Exception $e)
        {
            # 避免在Exception中再次调用__()方法后陷入死循环
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
            foreach ($libs as $lib=>$path)
            {
                $nslib = $ns.'.'.$lib;
                foreach($accept_language as $l)
                {
                    if (isset($static_lib_array[$nslib][$l]))
                    {
                        $lang = array_merge($lang, $static_lib_array[$nslib][$l]);
                    }
                    else
                    {
                        $file = $path . 'i18n' . DS . $l . '.lang';

                        if (is_file($file))
                        {
                            $static_lib_array[$nslib][$l] = (array)@parse_ini_file($file);
                        }
                        else
                        {
                            $static_lib_array[$nslib][$l] = array();
                        }

                        # 合并语言包
                        if ($static_lib_array[$nslib][$l])
                        {
                            $lang = array_merge($lang, $static_lib_array[$nslib][$l]);
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
        if (null!==I18n::$accept_language)
        {
            return I18n::$accept_language;
        }

        # 客户端语言包
        $accept_language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])?$_SERVER['HTTP_ACCEPT_LANGUAGE']:null;

        $lang_config = Core::config('lang');

        # 匹配语言设置
        # zh-CN,zh;q=0.8,zh-TW;q=0.6
        if ($accept_language && preg_match_all('#([a-z]+\-[a-z]+),|([a-z]+\-[a-z]+);#i', $accept_language, $matches))
        {
            $accept_language    = $matches[0];
            $accept_language    = array_values(array_slice($accept_language, 0, 2));    //只取前2个语言设置
            $accept_language[0] = strtolower(rtrim($accept_language[0], ';,'));
            if (isset($accept_language[1]))
            {
                $accept_language[1] = strtolower(rtrim($accept_language[1], ';,'));
            }

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

        I18n::$accept_language = $accept_language;

        return $accept_language;
    }
}