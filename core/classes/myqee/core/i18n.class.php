<?php

/**
 * 语言包核心类
 *
 * @author     jonwang(jonwang@myqee.com)
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2012 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class MyQEE_Core_I18n
{

    /**
     * 默认语言
     *
     * @var string
     */
    protected static $default_lang = 'zh-cn';

    /**
     * 当前语言
     *
     * @var string
     * @example zh-cn
     */
    protected $lang;

    protected static $data = array();

    public function __construct($lang = null)
    {
        if ( null === $lang )
        {
            $core_lang = Core::config('core.lang');

            if ( $core_lang=='auto' )
            {
                $core_lang = null;

                if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
                {
                    $accept = explode(';',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
                    foreach ( $accept as $item )
                    {
                        if ( false!==($pos=strpos($item,',')) )
                        {
                            $item = substr($item,$pos+1);
                        }
                        if (preg_match('#^[a-z\-]+$#', $item))
                        {
                            # 尝试寻找语言包
                            if ( Core::find_file('i18n',$item,'lang') )
                            {
                                $core_lang = $item;
                                break;
                            }
                        }
                    }
                }
            }

            if (!$core_lang)$core_lang = Core_I18n::$default_lang;

            $lang = $core_lang;
        }

        $this->lang = $lang;
    }

	/**
	 * 设置或获取语言设置
	 *
	 *     // 获取语言
	 *     $lang = $this->lang();
	 *
	 *     // 修改语言
	 *     $this->lang('zh-cn');
	 *
	 * @param   string $lang 新语言
	 * @return  string
	 */
	public function lang($lang = null)
	{
		if ($lang)
		{
			$this->lang = strtolower(str_replace(array(' ', '_'), '-',trim($lang)));
		}

		return $this->$lang;
	}

    /**
     *
     * @return I18n_En
     */
    public function get($string)
    {
        if ( ! isset(Core_I18n::$data[$this->lang]) )
        {
            Core_I18n::load($this->lang);
        }
        if ( isset(Core_I18n::$data[$this->lang][$string]) )
        {
            return Core_I18n::$data[$this->lang][$string];
        }
        return $string;
    }

    /**
     * 加载语言包文件
     *
     * @link http://www.php.net/manual/en/function.parse-ini-file.php
     * @return Core_I18n
     */
    public function load()
    {
        $langdata = array();
        $files = Core::find_file('i18n', $this->lang, '.lang');
        if ( $files )
        {
            $ext_len = strlen(EXT);
            if ( count($files) > 1 )
            {
                krsort($files); //逆向排序
            }
            foreach ( $files as $langfile )
            {
                if ( substr($langfile, - $ext_len) === EXT )
                {
                    $lang = Core_I18n::_include_lang_file($langfile);
                }
                else
                {
                    $lang = @parse_ini_file($langfile);
                }

                if ( is_array($lang) )
                {
                    $langdata = array_merge($langdata, $lang);
                }
            }
        }
        Core_I18n::$data[$this->lang] = $langdata;

        return $this;
    }

    protected static function _include_lang_file($file)
    {
        $lang = array();
        include $file;
        return $lang;
    }
}