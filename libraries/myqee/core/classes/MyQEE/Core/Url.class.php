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
    public function site($uri = '', $index = null )
    {
        return $this->base($index) . ltrim($uri, '/');
    }

    /**
     * 返回当前URL的BASE路径
     *
     * @param string $index
     * @return string
     */
    public function base($index = null)
    {
        if ( is_array(Core::$project_config['url']) )
        {
            if ( null===$index )
            {
                $index = Core::$curren_uri_index;
            }

            if (isset(Core::$project_config['url'][$index]))
            {
                $project_url = Core::$project_config['url'][$index];
            }
            else
            {
                $project_url = current(Core::$project_config['url']);
            }
        }
        else
        {
            $project_url = (string)Core::$project_config['url'];
        }

        $base = rtrim($project_url, '/') . '/';
        if ($base[0]=='/')
        {
            $base = Core::$base_url . $base;
        }

        return $base;
    }
}