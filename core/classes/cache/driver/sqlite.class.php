<?php

/**
 * SQLite缓存驱动器
 *
 * @author     jonwang(jonwang@myqee.com)
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license	   http://www.myqee.com/license.html
 */
class Core_Cache_Driver_SQLite extends Cache_Driver_Database
{
    /**
     * 默认缓存时间
     *
     * @var int
     */
    const DEFAULT_CACHE_TIME = 3600;

    /**
     * Memcache缓存驱动器
     * @param $config_name 配置名或数组
     */
    public function __construct($config_name = 'default')
    {
        $connection = array
        (
            'db'         => ':memory:',
            'table'      => 'sharedmemory',
            'expire'     => Cache_Driver_SQLite::DEFAULT_CACHE_TIME,
            'persistent' => false,
            'length'     => 0,
        );

        if ( is_array($config_name) )
        {
            $connection += $config_name;
            $config_name = md5(serialize($config_name));
        }
        else
        {
            $connection += (array)Core::config('cache/sqlite.' . $config_name);
        }

        if ( Cache_Driver_SQLite::DATA_COMPRESS && function_exists('gzcompress') )
        {
            $this->_compress = true;
        }

        $this->_handler = new Database(array('type'=>'SQLite', 'connection'=>$connection));

        $this->tablename = $connection['tablename'];
    }
}