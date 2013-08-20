<?php

/**
 * Cookie
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Core_Cookie
{

    /**
     * cookie的配置
     * @var array
     */
    protected static $config = array();

    public function __construct()
    {
        if (null===Cookie::$config)
        {
            Cookie::$config = Core::config('cookie');
        }
    }

    /**
     * 获取指定key的Cookie内容
     *
     * @param string $name
     * @return mixed
     */
    public static function get($name)
    {
        if (isset(Cookie::$config['prefix']) && Cookie::$config['prefix'])$name = Cookie::$config['prefix'] . $name;
        return $_COOKIE[$name];
    }

    /**
     * 创建cookie 详细请参考setcookie函数参数
     *
     * @param string/array $name
     * @param string $value
     * @param number $expire
     * @param string $path
     * @param string $domain
     * @param boolean $secure
     * @param boolean $httponly
     * @return boolean true/false
     */
    public static function set($name, $value = null, $expire = null, $path = '/', $domain = null, $secure = null, $httponly = null)
    {
        if ( headers_sent() ) return false;

        // If the name param is an array, we import it
        is_array($name) and extract($name, EXTR_OVERWRITE);

        // Fetch default options

        foreach ( array('value', 'expire', 'domain', 'path', 'secure', 'httponly', 'prefix') as $item )
        {
            if ( $$item === null && isset(Cookie::$config[$item]) )
            {
                $$item = Cookie::$config[$item];
            }
        }
        Cookie::$config['prefix'] && $name = Cookie::$config['prefix'] . $name;

        // Expiration timestamp
        $expire = ($expire == 0) ? 0 : $_SERVER['REQUEST_TIME'] + (int)$expire;

        Cookie::check_domain($domain);

        return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }

    /**
     * 删除cookie
     *
     * @param string $name cookie名称
     * @param string $path cookie路径
     * @param string $domain cookie作用域
     * @return boolean true/false
     */
    public static function delete($name, $path = '/', $domain = null)
    {
        Cookie::check_domain($domain);

        return Cookie::set($name, '', -864000, $path, $domain, false, false);
    }

    /**
     * 校验domain
     *
     * 处理当domain为IP+端口的形式
     *
     * @param string $domain
     */
    protected static function check_domain(&$domain)
    {
        if ($domain)
        {
            # 这里对IP+非80端口的需要特殊处理下，经试验，当这种情况下，设置session id的cookie的话会失败
            if (preg_match('#^([0-9]+.[0-9]+.[0-9]+.[0-9]+):[0-9]+$#',$domain,$m))
            {
                # IP:PORT 方式
                $domain = $m[1];
            }
        }
    }
}