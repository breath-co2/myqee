<?php

/**
 * 手册基础类
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2012 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class _Docs
{
    /**
     * @var  array  array of this classes constants
     */
    public $data;

    /**
     * 获取实例化对象
     * @param string $class
     * @return Docs_Class
     */
    public static function factory($class)
    {
        return new _Docs_Class($class);
    }

    /**
     * Parse a comment to extract the description and the tags
     *
     * @param   string  the comment retreived using ReflectionClass->getDocComment()
     * @return  array   array(string $description, array $tags)
     */
    public static function parse($comment, $only_comment = false)
    {
        // Normalize all new lines to \n
        $comment = str_replace(array("\r\n", "\n"), "\n", $comment);

        // Remove the phpdoc open/close tags and split
        $comment = array_slice(explode("\n", $comment), 1, -1);

        // Tag content
        $tags = array();

        foreach ($comment as $i => $line)
        {
            $line = preg_replace('/^\s*\* ?/m', '', $line);

            if (preg_match('/^@(\S+)(?:\s*(.+))?$/', $line, $matches))
            {
                if ($only_comment)continue;

                unset($comment[$i]);

                $name = $matches[1];
                $text = isset($matches[2]) ? $matches[2] : '';

                if ($name=='throws')
                {
                    if (preg_match('/^(\w+)\W(.*)$/', $text, $matches))
                    {
                        $class_name = $matches[1];
                    }
                    else
                    {
                        $class_name = $text;
                    }

                    $data = self::rf_tag_by_class($class_name);

                    if ($data)
                    {
                        if ($matches[2])
                        {
                            $data['comment'] = $matches[2];
                        }
                        $data['text']   = $text;
                        $text = $data;
                    }
                }
                elseif ($name=='uses'||$name=='see')
                {
                    if (preg_match('/^([a-z0-9_]+)::(\$)?([a-z0-9_]+)(?:\(\))?$/i', $text, $matches))
                    {
                        $data = self::rf_tag_by_class($matches[1], $matches[3] , $matches[2]?true:false);
                        if ($data)
                        {
                            $data['text'] = $text;
                            $text = $data;
                        }
                    }
                }
                elseif ($name=='access')
                {
                    continue;
                }

                $tags[$name][] = $text;
            }
            else
            {
                $comment[$i] = (string)$line;
            }
        }

        if ($only_comment)return $comment;

        return array($comment, $tags);
    }

    /**
     * Get the source of a function
     *
     * @param  string   the filename
     * @param  int      start line?
     * @param  int      end line?
     */
    public static function source($file, $start, $end)
    {
        if (!$file)
        {
            return false;
        }

        $file = file($file, FILE_IGNORE_NEW_LINES);

        $file = array_slice($file, $start - 1, $end - $start + 1);

        if (preg_match('/^(\s+)/', $file[0], $matches))
        {
            $padding = strlen($matches[1]);

            foreach ($file as & $line)
            {
                $line = substr($line, $padding);
            }
        }

        return implode("\n", $file);
    }

    /**
     * Test whether a class should be shown, based on the api_packages config option
     *
     * @param  Docs_Class  the class to test
     * @return  bool  whether this class should be shown
     */
    public static function show_class(_Docs_Class $class)
    {
        $api_packages = Core::config('userguide.api_packages');

        // If api_packages is true, all packages should be shown
        if ($api_packages === true) return true;

        // Get the package tags for this class (as an array)
        $packages = Arr::get($class->data['tags'], 'package', Array('None'));

        $show_this = false;

        // Loop through each package tag
        foreach ($packages as $package)
        {
            // If this package is in the allowed packages, set show this to true
            if (in_array($package, explode(',', $api_packages))) $show_this = true;
        }

        return $show_this;
    }

    /**
     * Returns an HTML string of debugging information about any number of
     * variables, each wrapped in a "pre" tag:
     *
     * // Displays the type and value of each variable
     * echo Kohana::debug($foo, $bar, $baz);
     *
     * @param   mixed   variable to debug
     * @param   ...
     * @return  string
     */
    public static function debug()
    {
        if (func_num_args() === 0) return;

        // Get all passed variables
        $variables = func_get_args();

        $output = array();
        foreach ($variables as $var)
        {
            $output[] = self::_dump($var, 1024);
        }

        return '<pre class="debug">' . implode("\n", $output) . '</pre>';
    }

    /**
     * Returns an HTML string of information about a single variable.
     *
     * Borrows heavily on concepts from the Debug class of [Nette](http://nettephp.com/).
     *
     * @param   mixed    variable to dump
     * @param   integer  maximum length of strings
     * @return  string
     */
    public static function dump($value, $length = 128)
    {
        return self::_dump($value, $length);
    }

    /**
     * Helper for Kohana::dump(), handles recursion in arrays and objects.
     *
     * @param   mixed    variable to dump
     * @param   integer  maximum length of strings
     * @param   integer  recursion level (internal)
     * @return  string
     */
    protected static function _dump(&$var, $length = 128, $level = 0)
    {
        if ($var === null)
        {
            return '<small>null</small>';
        }
        elseif (is_bool($var))
        {
            return '<small>bool</small> ' . ($var ? 'true' : 'false');
        }
        elseif (is_float($var))
        {
            return '<small>float</small> ' . $var;
        }
        elseif ($var ==='')
        {
            return '<small>empty</small>';
        }
        elseif (is_resource($var))
        {
            if (($type = get_resource_type($var)) === 'stream' and $meta = stream_get_meta_data($var))
            {
                $meta = stream_get_meta_data($var);

                if (isset($meta['uri']))
                {
                    $file = $meta['uri'];

                    if (function_exists('stream_is_local'))
                    {
                        // Only exists on PHP >= 5.2.4
                        if (stream_is_local($file))
                        {
                            $file = Core::debug_path($file);
                        }
                    }

                    return '<small>resource</small><span>(' . $type . ')</span> ' . htmlspecialchars($file, ENT_NOQUOTES, Core::$charset);
                }
            }
            else
            {
                return '<small>resource</small><span>(' . $type . ')</span>';
            }
        }
        elseif (is_string($var))
        {
            // Clean invalid multibyte characters. iconv is only invoked
            // if there are non ASCII characters in the string, so this
            // isn't too much of a hit.
            $var = UTF8::clean($var);

            if (UTF8::strlen($var) > $length)
            {
                // Encode the truncated string
                $str = htmlspecialchars(UTF8::substr($var, 0, $length), ENT_NOQUOTES, Core::$charset) . '&nbsp;&hellip;';
            }
            else
            {
                // Encode the string
                $str = htmlspecialchars($var, ENT_NOQUOTES, Core::$charset);
            }

            return '<small>string</small><span>(' . strlen($var) . ')</span> "' . $str . '"';
        }
        elseif (is_array($var))
        {
            $output = array();

            // Indentation for this variable
            $space = str_repeat($s = '    ', $level);

            static $marker = null;

            if ($marker === null)
            {
                // Make a unique marker
                $marker = uniqid("\x00");
            }

            if (empty($var))
            {
                // Do nothing
            }
            elseif (isset($var[$marker]))
            {
                $output[] = "(\n$space$s*RECURSION*\n$space)";
            }
            elseif ($level < 5)
            {
                $output[] = "<span>(";

                $var[$marker] = true;
                foreach ($var as $key => & $val)
                {
                    if ($key === $marker) continue;
                    if (!is_int($key))
                    {
                        $key = '"' . htmlspecialchars($key, ENT_NOQUOTES, Core::$charset) . '"';
                    }

                    $output[] = "$space$s$key => " . self::_dump($val, $length, $level + 1);
                }
                unset($var[$marker]);

                $output[] = "$space)</span>";
            }
            else
            {
                // Depth too great
                $output[] = "(\n$space$s...\n$space)";
            }

            return '<small>array</small><span>(' . count($var) . ')</span> ' . implode("\n", $output);
        }
        elseif (is_object($var))
        {
            // Copy the object as an array
            $array = (array)$var;

            $output = array();

            // Indentation for this variable
            $space = str_repeat($s = '    ', $level);

            $hash = spl_object_hash($var);

            // Objects that are being dumped
            static $objects = array();

            if (empty($var))
            {
                // Do nothing
            }
            elseif (isset($objects[$hash]))
            {
                $output[] = "{\n$space$s*RECURSION*\n$space}";
            }
            elseif ($level < 10)
            {
                $output[] = "<code>{";

                $objects[$hash] = true;
                foreach ($array as $key => & $val)
                {
                    if ($key[0] === "\x00")
                    {
                        // Determine if the access is protected or protected
                        $access = '<small>' . ($key[1] === '*' ? 'protected' : 'private') . '</small>';

                        // Remove the access level from the variable name
                        $key = substr($key, strrpos($key, "\x00") + 1);
                    }
                    else
                    {
                        $access = '<small>public</small>';
                    }

                    $output[] = "$space$s$access $key => " . self::_dump($val, $length, $level + 1);
                }
                unset($objects[$hash]);

                $output[] = "$space}</code>";
            }
            else
            {
                // Depth too great
                $output[] = "{\n$space$s...\n$space}";
            }

            return '<small>object</small> <span>' . get_class($var) . '(' . count($array) . ')</span> ' . implode("\n", $output);
        }
        else
        {
            return '<small>' . gettype($var) . '</small> ' . htmlspecialchars(print_r($var, true), ENT_NOQUOTES, Core::$charset);
        }
    }

    public static function url($class, $havedir = false, $haveext = false)
    {
        $url = '/'; // . ($havedir ? '' : '/' . self::_class2url( $class )) . '/' ;
        if ($havedir)
        {
            list ($dir) = explode('/', $class);
        }
        else
        {
            $dir = self::_class2url($class);
            $url .= $dir . '/';
        }
        if ($dir == 'classes')
        {
            $ext = '.class' . EXT;
            $class = str_replace('_', '/', $class);
        }
        elseif ($dir == 'models')
        {
            $ext = '.model' . EXT;
            $class = str_replace('_', '/', $class);
        }
        elseif ($dir == 'shell')
        {
            $ext = '.shell' . EXT;
            $class = strtolower($class);
        }
        elseif ($dir == 'admin')
        {
            $ext = '.admin' . EXT;
            $class = strtolower($class);
        }
        elseif ($dir == 'controllers')
        {
            $ext = '.controller' . EXT;
            $class = strtolower($class);
        }
        elseif ($dir == 'config')
        {
            $ext = '.config' . EXT;
        }
        elseif ($dir == 'i18n')
        {
            $ext = '.lang';
        }
        if ($haveext)
        {
            $url .= $class;
        }
        else
        {
            $url .= $class . $ext;
        }

        return Core::url($url);
    }

    protected static function _class2url(&$class)
    {
        $strpos = strpos($class, '_');
        $prefix = '';
        if ($strpos !== false)
        {
            $prefix = strtolower(substr($class, 0, $strpos));
        }
        if ($prefix == 'i18n')
        {
            $dir = 'i18n';
            $class = substr($class, 5);
        }
        elseif ($prefix == 'model')
        {
            $dir = 'models';
            $class = substr($class, 6);
        }
        elseif ($prefix == 'orm')
        {
            $dir = 'orm';
            # 对ORM做些特殊处理
            # 将ORM_Test_Finder转化成Test_Test.Finder
            # 将ORM_Test_Test2_Finder转化成Test_Test_Test2.Finder
            $class = ltrim(preg_replace('#^orm(?:_(.*))?_([a-z0-9]+)_([a-z0-9]+)$#i', '$1_$2', $class), '_');
        }
        elseif ($prefix == 'controller')
        {
            $dir = HttpIO::current_controller()->dir;
            $class = substr($class, 11);
        }
        elseif ($prefix == 'shell')
        {
            $dir = 'shell';
            $class = substr($class, 5);
        }
        else
        {
            $dir = 'classes';
        }

        return $dir;
    }


    /**
     * 获取Tag分析需要用到的数据
     *
     * @param string $class_name
     * @param string $f
     * @param boolean $is_poperty
     */
    protected static function rf_tag_by_class($class_name, $f = null, $is_poperty = false)
    {
        try
        {
            if (class_exists($class_name, true))
            {
                if ($f)
                {
                    if ($is_poperty)
                    {
                        $rf = new _Docs_Property($class_name, $f);
                    }
                    else
                    {
                        $rf = new _Docs_Method($class_name, $f);
                    }
                    $data = $rf->getArrayCopy();

                    return array
                    (
                        'class_name'   => $data['class_name'],
                        'f'            => $f,
                        'is_php_class' => $data['is_php_class'],
                        'comment'      => $data['title'],
                    );
                }
                else
                {
                    $rf = new ReflectionClass($class_name);

                    $f = $rf->getFileName();
                    if (substr($f, -13)=='ev'.'al()\'d code' && !is_file($f))
                    {
                        $parent = $rf;
                        # 程序生成的代码
                        while ($parent = $parent->getParentClass())
                        {
                            if (substr(strtolower($parent->name), 0, 3)=='ex_')
                            {
                                # 扩展类或略
                                continue;
                            }

                            $rf2 = new ReflectionClass($parent->name);
                            $f = $rf2->getFileName();
                            if (substr($f, -13)!='ev'.'al()\'d code' && is_file($f))
                            {
                                return array
                                (
                                    'class_name'   => $rf2->name,
                                    'is_php_class' => $rf2->getStartLine()?0:1,
                                    'comment'      => current(self::parse($rf2->getDocComment(), true)),
                                );
                            }

                            unset($rf2);
                        }
                    }
                    else
                    {
                        return array
                        (
                            'class_name'   => $rf->name,
                            'is_php_class' => $rf->getStartLine()?0:1,
                            'comment'      => current(self::parse($rf->getDocComment(), true)),
                        );
                    }
                }
            }
            else
            {
                return false;
            }
        }
        catch (Exception $e)
        {
            return false;
        }
    }


    public function getArrayCopy()
    {
        return $this->data;
    }
}