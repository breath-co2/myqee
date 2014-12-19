<?php
/**********************************************************\
|                                                          |
| The implementation of PHPRPC Protocol 3.0                |
|                                                          |
| Release 3.0.1                                            |
| Copyright by Team-PHPRPC                                 |
|                                                          |
| WebSite:  http://www.phprpc.org/                         |
|           http://www.phprpc.net/                         |
|           http://www.phprpc.com/                         |
|           http://sourceforge.net/projects/php-rpc/       |
|                                                          |
| Authors:  Ma Bingyao <andot@ujn.edu.cn>                  |
|                                                          |
| This file may be distributed and/or modified under the   |
| terms of the GNU General Public License (GPL) version    |
| 2.0 as published by the Free Software Foundation and     |
| appearing in the included file LICENSE.                  |
|                                                          |
\**********************************************************/

class PHPRPC
{
    /**
     * 返回Server
     *
     * @return PHPRPC_Server
     */
    public static function server()
    {
        return new PHPRPC_Server();
    }

    /**
     * 返回Client
     *
     * @return PHPRPC_Client
     */
    public static function client()
    {
        return new PHPRPC_Client();
    }

    public static function init()
    {
        return true;
    }

    /**
     * 返回一个DHParams对象
     *
     * @param $key_len
     * @return PHPRPC_DHParams
     */
    public static function DHParams($key_len)
    {
        return new PHPRPC_DHParams($key_len);
    }

    public static function xxtea()
    {
        if (!function_exists('xxtea_encrypt'))
        {
            require(dirname(__FILE__).'/xxtea.php');
        }
    }

    public static function serialize_fix($v)
    {
        return serialize($v);
    }
}