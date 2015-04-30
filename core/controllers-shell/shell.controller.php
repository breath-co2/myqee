<?php

/**
 * SHELL 脚本基础控制器
 *
 * @author 呼吸二氧化碳 <jonwang@myqee.com>
 *
 */
abstract class Core_Controller_Shell extends Controller
{
    public function action_default()
    {
        echo self::get_cli_help_str($this);
        echo "\nPlease enter action and parameters, separated by [space].\nExample: my_action param1 param2 param3\n";

        while (true)
        {
            $std_in     = trim(fgets(STDIN));
            $std_in_arr = explode(' ', $std_in);
            $action     = 'action_'. $std_in_arr[0];
            if (method_exists($this, $action))
            {
                array_shift($std_in_arr);
                call_user_func_array(array($this, $action), $std_in_arr);

                break;
            }
            else
            {
                echo "The controller {$action} not exist. please try again.\n";
            }
        }
    }

    protected static function _parse_doc_comment($comment)
    {
        // Normalize all new lines to \n
        $comment = str_replace(array("\r\n", "\n"), "\n", $comment);

        // Remove the phpdoc open/close tags and split
        $comment = array_slice(explode("\n", $comment), 1, -1);

        // Tag content
        $param = array();

        foreach ($comment as $i => $line)
        {
            // Remove all leading whitespace
            $line = preg_replace('/^\s*\* ?/m', '', $line);

            // Search this line for a tag
            if (preg_match('/^@(\S+)(?:\s*(.+))?$/', $line, $matches))
            {
                // This is a tag line
                unset($comment[$i]);

                $name = $matches[1];
                $text = isset($matches[2]) ? $matches[2] : '';
                if ($text && $name == 'param')
                {
                    // Add the tag
                    $param[] = $text;
                }
                else
                {
                    continue;
                }
            }
            else
            {
                // Overwrite the comment line
                $comment[$i] = (string)$line;
            }
        }

        return array('title' => $comment, 'param' => $param);
    }


    /**
     * 获取shell命令下参数
     *
     * 与 `getopt()` 具体相似的功能，区别：在命令行中如果执行 `php index.php default test -a=1 -b=c` 这样的命令时，通过 `getopt()` 会获取参数失败，而这个方法可以正确获得相应的参数
     *
     * **示例：**<br/>
     * 在默认项目Shell控制器中加入一个test控制器文件 ( `projects/default/controller_shell/test.controller.php` ) 内容为：
     *
     *     class Controller_Test extends Controller_Shell
     *     {
     *         public function action_run()
     *         {
     *             $shortopts  = "";
     *             $shortopts .= "f:";  // 必须有值， 比如：-f myvalue，将会得到f=myvalue
     *             $shortopts .= "v::"; // 可接受值， 比如：-v 则v=false,如果是-v=myvalue，则v=myvalue
     *             $shortopts .= "abc"; // 不接受值， 比如：-a -v 则获取a=false,v=false，注意，这个并不是接受-abc的参数，而是分别可接受-a,-b,-c
     *
     *             $longopts  = array
     *             (
     *                 "required:",     // 必须有值， 比如：--required=abc
     *                 "optional::",    // 可接受值， 比如：--optional 则获取 optional=false，--optional=abc，则是optional=abc
     *                 "option",        // 不接受值， 比如：--option 则获取 option=false
     *                 "opt",           // 同上
     *             );
     *
     *             $options = self::getopt($shortopts, $longopts);
     *             var_dump($options);
     *         }
     *     }
     *
     * **通过 `php index.php default test run -f "value for f" -v -a --required value --optional="optional value" --option` 运行以上脚本会输出**
     *
     *     array(6) {
     *       ["f"]=> string(11) "value for f"
     *       ["v"]=> bool(false)
     *       ["a"]=> bool(false)
     *       ["required"]=> string(5) "value"
     *       ["optional"]=> string(14) "optional value"
     *       ["option"]=> bool(false)
     *     }
     *
     *
     * @link   http://cn.php.net/getopt
     * @param  string $options 单字符参数，只接受[a-zA-Z0-9]的参数，比如 -a, -h, -v=myvalue, -4 这样
     * @param  array $global_options --参数，比如--test, --help, --v=abc 这样
     * @return array 返回获取到的参数的数组
     */
    public static function getopt($options, array $global_options = null)
    {
        $argv = $_SERVER['argv'];
        array_shift($argv);

        foreach($argv as $key => $item)
        {
            if ($item[0] === '-')
            {
                # 读取到第一个带-参数的值
                break;
            }
            unset($argv[$key]);
        }
        $argv = array_values($argv);


        $len = strlen($options);
        $my_options = array();

        $sl = 0;
        for($i = $len - 1; $i >= 0; $i--)
        {
            $key = $options[$i];
            if ($key === ':')
            {
                $sl++;
                continue;
            }

            # 只接受a-zA-Z0-9
            if (preg_match('#[^a-zA-Z0-9]+#', $key))continue;

            if ($sl === 0)
            {
                $my_options[$key] = 1;
            }
            elseif ($sl === 1)
            {
                $my_options[$key .':'] = 1;
            }
            else
            {
                $my_options[$key .'::'] = 1;
            }

            $sl = 0;
        }

        $my_global_options = array();
        foreach($global_options as $item)
        {
            $my_global_options[$item] = 1;
        }

        $rs = array();

        foreach($argv as $k => $item)
        {
            if (preg_match('#^\-(\-)?([a-z0-9\-]+)=(.*)$#i', $item, $m))
            {
                $key   = $m[2];
                $value = $m[3];
                if ($m[1] === '-')
                {
                    if (!isset($my_global_options[$key .'::']))
                    {
                        continue;
                    }
                }
                else
                {
                    if (!isset($my_options[$key .'::']))
                    {
                        continue;
                    }
                }
            }
            elseif (preg_match('#^\-(\-)?([a-z0-9\-]+)$#i', $item, $m))
            {
                $key  = $m[2];
                if ($m[1] === '-')
                {
                    if (isset($my_global_options[$key]))
                    {
                        $value = false;
                    }
                    elseif (isset($my_global_options[$key .':']))
                    {
                        $value = $argv[$k+1];
                    }
                    else
                    {
                        continue;
                    }
                }
                else
                {
                    if (isset($my_options[$key]))
                    {
                        $value = false;
                    }
                    elseif (isset($my_options[$key .':']))
                    {
                        $value = $argv[$k + 1];
                    }
                    elseif (isset($my_options[$key .'::']))
                    {
                        $value = false;
                    }
                    else
                    {
                        continue;
                    }
                }
            }
            else
            {
                continue;
            }

            if (isset($rs[$key]))
            {
                $rs[$key]   = (array)$rs[$key];
                $rs[$key][] = $value;
            }
            else
            {
                # 赋值
                $rs[$key] = $value;
            }
        }

        return $rs;
    }

    /**
     * 获取用户输入内容
     */
    public function input()
    {
        return trim(fgets(STDIN));
    }

    /**
     * 输出内容，会附加换行符
     */
    public function output($msg)
    {
        echo $msg . CRLF;
    }

    /**
     * 获取命令行帮助文件
     *
     * @param $class
     */
    public static function get_cli_help_str($class)
    {
        $examples = array_diff(get_class_methods($class), get_class_methods(__CLASS__));

        # 获取方法的字符串最大长度
        $methods = array();
        $name_max_len = 0;
        foreach ($examples as $method)
        {
            if ($method ==__FUNCTION__) continue;
            if (strtolower(substr($method, 0, 7)) === 'action_')
            {
                $m = substr($method, 7);
                $methods[$m]  = $m;
                $name_max_len = max(strlen($m), $name_max_len);
            }
        }

        $str = '';
        $str_usage = 'Usage: ';
        foreach ($methods as $method)
        {
            $ref_method = new ReflectionMethod($class, 'action_'. $method);

            $parameters = $ref_method->getParameters();

            $str_usage .= str_pad($method, $name_max_len, ' ', STR_PAD_RIGHT);
            $comment = self::_parse_doc_comment( $ref_method->getDocComment());
            $str .= "\n    \x1b[32m{$method}\x1b[39m\n       comment   : {$comment['title'][0]}\n       parameters: ";

            if ($parameters)
            {
                $tmpstr = array();
                $tmpparameter = array();
                $i = 0;
                $hava_l = 0;
                foreach ($parameters as $k => $parameter)
                {
                    $tmpstr[] = '                   $' . $parameter->name . ' ' . $comment['param'][$i];
                    $tmpparameter[$k] = '$' . $parameter->getName();
                    if ($parameter->isDefaultValueAvailable())
                    {
                        $hava_l ++;
                        $tmpparameter[$k] = '[' . $tmpparameter[$k] . ' = ' . $parameter->getDefaultValue();
                    }
                    $i ++;
                }
                $str .= trim(implode(CRLF, $tmpstr));
                $str_usage .= ' [options] ' . '[' . implode(', ', $tmpparameter) . ']';

                if ($hava_l)
                {
                    for($i = 0; $i < $hava_l; $i ++)
                    {
                        $str_usage .= ' ]';
                    }
                }
            }
            else
            {
                $str .= '[no parameter]' . CRLF;
            }
            $str_usage .= CRLF . '           ';

        }
        $str_usage = trim($str_usage) . CRLF;

        return $str_usage. $str. CRLF;
    }
}