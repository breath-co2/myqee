<?php

/**
 * Memcache For SAE 缓存驱动器
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   MyQEE
 * @package    Sea
 * @subpackage Sea
 * @copyright  Copyright (c) 2008-2012 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Library_MyQEE_SAE_Cache_Driver_Memcache extends Ex_Cache_Driver_Memcache
{

    /**
     * Memcache缓存驱动器
     * @param $config_name 配置名或数组
     */
    public function __construct()
    {
        $config_name = 'default';
        if ( ! isset( Cache_Driver_Memcache::$memcaches[$config_name] ) )
        {
            $memcache = 'memcache_init';
            Cache_Driver_Memcache::$memcaches[$config_name] = new $memcache();
            Cache_Driver_Memcache::$memcaches_num[$config_name] = 0;
        }

        $this->backend = & Cache_Driver_Memcache::$memcaches[$config_name];
        Cache_Driver_Memcache::$memcaches_num[$config_name] ++;
    }

}