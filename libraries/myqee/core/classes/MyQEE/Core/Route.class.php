<?php

/**
 * MyQEE 路由核心类
 *
 * @author     jonwang(jonwang@myqee.com)
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2012 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class MyQEE_Core_Route
{

    // Defines the pattern of a <segment>
    const REGEX_KEY = '<([a-zA-Z0-9_]++)>';

    // What can be part of a <segment> value
    const REGEX_SEGMENT = '[^/.,;?\n]++';

    // What must be escaped in the route regex
    const REGEX_ESCAPE = '[.\\+*?[^\\]${}=!|]';

    public static $defaults = array('action' => 'index');

    /**
     * 当前项目路由配置
     * @var array
     */
    public static $route;

    /**
     * 当前路由名
     * @var string
     */
    public static $current_route;

    /**
     * 记录最后一个路由名
     * @var string
     */
    public static $last_route;

    /**
     * 路由列队
     * @var array
     */
    public static $route_list = array();

    /**
     * 正则
     * @var array
     */
    public static $regex = array();

    public function __construct()
    {

    }

    protected function _comp()
    {
        Core_Route::$route = Core::$project_config['route'];
        if ( Core::$project_config['route'] )
        {
            # 构造路由正则
            foreach ( Core::$project_config['route'] as $k => $v )
            {
                Core_Route::$regex[Core::$project][$k] = Core_Route::_compile($v);
            }
        }
    }

    /**
     * 根据路径信息获取路由配置
     * @param string $pathinfo
     */
    public function get($pathinfo)
    {
        if ( ! isset(Core_Route::$regex[Core::$project][Core::$project]) )
        {
            # 构造路由正则
            $this->_comp();
        }
        foreach ( Core_Route::$regex[Core::$project] as $k => $v )
        {
            if ( isset(Core_Route::$route[$k]['for']) )
            {
                if ( Core_Route::$route[$k]['for'] != Core::$project_url )
                {
                    # 如果路由不是为当前url设置的则或略
                    continue;
                }
            }
            $preg = Core_Route::_matches($pathinfo, $k);
            if ( $preg )
            {
                Core_Route::$last_route = $k;
                return $preg;
            }
        }
        return false;
    }

    protected static function _matches($uri, $route)
    {
        if ( ! $route || ! (isset(Core_Route::$regex[Core::$project][$route])) )
        {
            return false;
        }

        Core::debug()->group('路由匹配');
        Core::debug()->info('匹配：');
        Core::debug()->log(array('URI:' => $uri, 'Route:' => Core_Route::$regex[Core::$project][$route]));
        if ( ! preg_match(Core_Route::$regex[Core::$project][$route], $uri, $matches) )
        {
            Core::debug()->error('↑未匹配到当前路由。');
            Core::debug()->groupEnd();
            return false;
        }

        $params = array();
        foreach ( $matches as $key => $value )
        {
            if ( is_int($key) )
            {
                // Skip all unnamed keys
                continue;
            }

            // Set the value for all matched keys
            $params[$key] = $value;
        }

        if ( isset(Core::$project_config['route'][$route]) && is_array(Core::$project_config['route'][$route]['default']) )
        {
            foreach ( Core::$project_config['route'][$route]['default'] as $key => $value )
            {
                if ( ! isset($params[$key]) or $params[$key] === '' )
                {
                    // Set default values for any key that was not matched
                    $params[$key] = $value;
                }
            }
        }

        Core::debug()->info('获取参数：');
        Core::debug()->log($params);
        Core::debug()->groupEnd();
        return $params;
    }

    protected static function _compile($route)
    {
        // The URI should be considered literal except for keys and optional parts
        // Escape everything preg_quote would escape except for : ( ) < >
        $regex = preg_replace('#' . Core_Route::REGEX_ESCAPE . '#', '\\\\$0', $route['uri']);

        if ( strpos($regex, '(') !== FALSE )
        {
            // Make optional parts of the URI non-capturing and optional
            $regex = str_replace(array('(', ')'), array('(?:', ')?'), $regex);
        }

        // Insert default regex for keys
        $regex = str_replace(array('<', '>'), array('(?P<', '>' . Core_Route::REGEX_SEGMENT . ')'), $regex);

        if ( ! empty($route['preg']) )
        {
            $search = $replace = array();
            foreach ( $route['preg'] as $key => $value )
            {
                $search[] = "<$key>" . Core_Route::REGEX_SEGMENT;
                $replace[] = "<$key>$value";
            }

            // Replace the default regex with the user-specified regex
            $regex = str_replace($search, $replace, $regex);
        }

        return '#^' . $regex . '$#uD';
    }

    /**
     * 根据路由获取uri
     *
     * @param   array   URI parameters
     * @return  string
     * @throws  Exception
     * @uses    Core_Route::REGEX_Key
     */
    public function uri(array $params = NULL)
    {
        if ( $params === NULL )
        {
            // Use the default parameters
            $params = Core_Route::$defaults;
        }
        else
        {
            // Add the default parameters
            $params += Core_Route::$defaults;
        }

        // Start with the routed URI
        $uri = Core_Route::$route[Core_Route::$current_route]['uri'];

        if ( strpos($uri, '<') === FALSE and strpos($uri, '(') === FALSE )
        {
            // This is a static route, no need to replace anything
            return $uri;
        }

        while ( preg_match('#\([^()]++\)#', $uri, $match) )
        {
            // Search for the matched value
            $search = $match[0];

            // Remove the parenthesis from the match as the replace
            $replace = substr($match[0], 1, - 1);

            while ( preg_match('#' . Core_Route::REGEX_KEY . '#', $replace, $match) )
            {
                list ( $key, $param ) = $match;

                if ( isset($params[$param]) )
                {
                    // Replace the key with the parameter value
                    $replace = str_replace($key, $params[$param], $replace);
                }
                else
                {
                    // This group has missing parameters
                    $replace = '';
                    break;
                }
            }

            // Replace the group in the URI
            $uri = str_replace($search, $replace, $uri);
        }

        while ( preg_match('#' . Core_Route::REGEX_KEY . '#', $uri, $match) )
        {
            list ( $key, $param ) = $match;

            if ( ! isset($params[$param]) )
            {
                // Ungrouped parameters are required
                throw new Exception('参数错误：' . $param);
            }

            $uri = str_replace($key, $params[$param], $uri);
        }

        return $uri;
    }

}