<?php

/**
 * 权限类
 *
 * @author	   jonwang(jonwang@myqee.com)
 * @category   MyQEE
 * @package	   System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license	   http://www.myqee.com/license.html
 */
class Core_Permission
{
    protected $setting = array();

    public function __construct( $perm_setting )
    {
        if ( is_array($perm_setting) )
        {
            $this->setting = $perm_setting;
        }
    }

    /**
     * 判断是否拥有指定权限，支持复合权限
     *
     *   //检查单个权限
     * 	 $this->is_own('perm2');
     *
     *   //检查复合权限
     *   $this->is_own('perm2','perm2');
     *
     * @param string $key
     * @param ...
     * @return boolean
     */
    public function is_own( $key1 , $key2 = null , $key3 = null )
    {
        if ( $this->is_super_perm() )
        {
            # 超级管理员
            return true;
        }

        $keys = func_get_args();
        $is_own = true;
        foreach ($keys as $key)
        {
            $key = trim($key);
            if ( !Core::key_string($this->setting, $key) )return false;
        }
        return true;
    }

    /**
     * 是否超级管理权限
     *
     * @return boolean
     */
    public function is_super_perm()
    {
        if ( isset($this->setting['_super_admin']) && $this->setting['_super_admin'] )
        {
            # 超级管理员
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 获取配置
     *
     * @return array
     */
    public function get_setting()
    {
        return $this->setting;
    }
}