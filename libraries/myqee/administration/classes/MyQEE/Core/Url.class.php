<?php

/**
 * URL类
 *
 * @author     jonwang(jonwang@myqee.com)
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2012 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class MyQEE_Core_Url
{
    public function __construct()
    {

    }

    /**
     * 获取站点URL
     *
     * @return string
     */
    public function site($uri = '' , $index = 0 )
    {
        return $this->base($index) . ltrim($uri, '/');
    }

    /**
     * 返回当前URL的BASE路径
     *
     * @param string $index
     * @return string
     */
    public function base( $index = 0 )
    {
        if ( strpos(Core::$project_config['url_admin'],'://') )
        {
            return rtrim(Core::$project_config['url_admin'], '/') . '/';
        }
        else if ( defined('IN_ADMIN') )
        {
            # 构造特殊的URL形式
            $project_url = Core::$project_url.Core::config('core.projects.'.INITIAL_PROJECT_NAME.'.url_admin').'p/'.Core::$project.'/';
            $base = rtrim($project_url, '/') . '/';
        }
        else
        {
            if ( is_array(Core::$project_config['url']) )
            {
                $project_url = Core::$project_config['url'][$index];
            }
            else
            {
                $project_url = (string)Core::$project_config['url'];
            }
            $base = rtrim($project_url, '/') . rtrim(Core::$project_config['url_admin'], '/') . '/';
        }
        if ($base[0]=='/')
        {
            $base = Core::$base_url . $base;
        }
        return $base;
    }
}