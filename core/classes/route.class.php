<?php

/**
 * MyQEE 路由核心类
 *
 * @author     jonwang(jonwang@myqee.com)
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Core_Route
{

    // Defines the pattern of a <segment>
    const REGEX_KEY = '<([a-zA-Z0-9_]++)>';

    // What can be part of a <segment> value
    const REGEX_SEGMENT = '[^/.,;?\n]++';

    // What must be escaped in the route regex
    const REGEX_ESCAPE = '[.\\+*?[^\\]${}=!|]';

    /**
     * 当前路由名
     *
     * @var string
     */
    public static $current_route;

    /**
     * 各个项目的路由正则
     *
     * @var array
     */
    protected static $regex = array();

    public function __construct()
    {

    }

    /**
     * 根据路径获取路由配置
     *
     * @param string $pathinfo
     */
    public function get($pathinfo)
    {
        if (!isset(Route::$regex[Core::$project]))
        {
            # 构造路由正则
            $this->init_regex();
        }

        # 当前Route
        $route = Core::config('route');

        foreach (Route::$regex[Core::$project] as $k => $v)
        {
            if (isset($route[$k]['for']))
            {
                if ($route[$k]['for'] != Core::$project_url)
                {
                    # 如果路由不是为当前url设置的则或略
                    continue;
                }
            }

            $preg = Route::_matches($pathinfo, $k);
            if ($preg)
            {
                return $preg;
            }
        }

        return false;
    }

    /**
     * 根据路由获取URI，为get方法相反操作
     *
     * @param   array URI parameters
     * @return  string
     * @throws  Exception
     * @uses    Route::REGEX_Key
     */
    public static function uri(array $params = null)
    {
        if (!isset(Route::$regex[Core::$project]))
        {
            # 构造路由正则
            $this->init_regex();
        }

        $current_route = Core::config('route.'.Route::$current_route);

        if (!isset($current_route['default']) || !is_array($current_route['default']))$current_route['default'] = array();

        if (null===$params)
        {
            // 使用默认参数
            $params = $current_route['default'];
        }
        else
        {
            // 覆盖默认参数
            $params += $current_route['default'];
        }

        // 获取URI
        $uri = $current_route['uri'];

        if (strpos($uri, '<') === false && strpos($uri, '(') === false)
        {
            // This is a static route, no need to replace anything
            return $uri;
        }

        $provided_optional = false;

        while (preg_match('#\([^()]++\)#', $uri, $match))
        {
            // Search for the matched value
            $search = $match[0];

            // Remove the parenthesis from the match as the replace
            $replace = substr($match[0], 1, -1);

            while (preg_match('#' . Route::REGEX_KEY . '#', $replace, $match))
            {
                list ($key, $param) = $match;

                if (isset($params[$param]) && ($params[$param]!==Route::arr_get($current_route['default'],$param)))
                {
                    $provided_optional = true;

                    // Replace the key with the parameter value
                    $replace = str_replace($key, $params[$param], $replace);
                }
                elseif ($provided_optional)
                {
                    // Look for a default
                    if (isset($current_route['default'][$param]))
                    {
                        $replace = str_replace($key, $current_route['default'][$param], $replace);
                    }
                    else
                    {
                        // Ungrouped parameters are required
                        throw new Exception(__('Required route parameter not passed: :param', array(':param' => $param)));
                    }
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

        while (preg_match('#' . Route::REGEX_KEY . '#', $uri, $match))
        {
            list($key, $param) = $match;

            if ( !isset($params[$param]) )
            {
                if (isset($current_route['default'][$param]))
                {
                    $params[$param] = $current_route['default'][$param];
                }
                else
                {
                    // Ungrouped parameters are required
                    throw new Exception(__('Required route parameter not passed: :param', array(':param' => $param)));
                }
            }

            $uri = str_replace($key, $params[$param], $uri);
        }

        // Trim all extra slashes from the URI
        $uri = preg_replace('#//+#', '/', rtrim($uri, '/'));

        if (isset($current_route['defaults']['host']))
        {
            if (false===strpos($current_route['defaults']['host'], '://'))
            {
                $host = HttpIO::PROTOCOL.$host;
            }

            $uri = rtrim($host, '/') . '/' . $uri;
        }

        return $uri;
    }

    /**
     * 初始化当前项目的路由
     */
    protected static function init_regex()
    {
        if (isset(Route::$regex[Core::$project]))return null;

        Route::$regex[Core::$project] = array();

        # 获取路由配置
        $route = Core::config('route');

        # 如果有
        if ($route)
        {
            # 构造路由正则
            foreach ( $route as $k => $v )
            {
                Route::$regex[Core::$project][$k] = Route::_compile($v);
            }
        }
    }

    /**
     * 匹配路由
     *
     * @param string $uri 请求的URI
     * @param string $route_name 使用路由的名称
     * @return boolean|Ambigous <boolean, mixed, multitype:unknown >
     */
    protected static function _matches($uri, $route_name)
    {
        if (!$route_name || !(isset(Route::$regex[Core::$project][$route_name])))
        {
            return false;
        }

        if (IS_DEBUG)
        {
            Core::debug()->group('路由匹配');
            Core::debug()->info(array('URI:' => $uri, 'Route:' => Route::$regex[Core::$project][$route_name]),'匹配');
        }

        if (!preg_match(Route::$regex[Core::$project][$route_name], $uri, $matches))
        {
            if (IS_DEBUG)
            {
                Core::debug()->info('↑未匹配到当前路由');
                Core::debug()->groupEnd();
            }

            return false;
        }

        $params = array();
        foreach ($matches as $key => $value)
        {
            if (is_int($key))
            {
                // 跳过
                continue;
            }

            $params[$key] = $value;
        }

        $route_config = Core::config('route.'.$route_name);
        if ($route_config)
        {
            if (isset($route_config['default']) && is_array($route_config['default']))foreach($route_config['default'] as $key => $value)
            {
                if (!isset($params[$key]) || $params[$key] === '')
                {
                    $params[$key] = $value;
                }
            }

            // 处理回调过滤
            if (isset($route_config['filters']) && is_array($route_config['filters']))foreach($route_config['filters'] as $callback)
            {
                $return = call_user_func($callback, $params);

                if (false===$return)
                {
                    $params = false;
                }
                elseif (is_array($return))
                {
                    $params = $return;
                }
            }
        }

        if (IS_DEBUG)
        {
            Core::debug()->info($params,'获取参数');
            Core::debug()->groupEnd();
        }

        return $params;
    }

    protected static function _compile($route)
    {
        // The URI should be considered literal except for keys and optional parts
        // Escape everything preg_quote would escape except for : ( ) < >
        $regex = preg_replace('#' . Route::REGEX_ESCAPE . '#', '\\\\$0', $route['uri']);

        if (strpos($regex, '(') !== false)
        {
            // Make optional parts of the URI non-capturing and optional
            $regex = str_replace(array('(', ')'), array('(?:', ')?'), $regex);
        }

        // Insert default regex for keys
        $regex = str_replace(array('<', '>'), array('(?P<', '>' . Route::REGEX_SEGMENT . ')'), $regex);

        if (!empty($route['preg']))
        {
            $search = $replace = array();
            foreach ($route['preg'] as $key => $value)
            {
                $search[]  = "<$key>" . Route::REGEX_SEGMENT;
                $replace[] = "<$key>$value";
            }

            // Replace the default regex with the user-specified regex
            $regex = str_replace($search, $replace, $regex);
        }

        return '#^' . $regex . '$#uD';
    }

    protected static function arr_get(array $array,$key,$default=null)
    {
        return isset($array[$key]) ? $array[$key] : $default;
    }
}