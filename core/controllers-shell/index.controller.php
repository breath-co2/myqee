<?php

class Core_Controller_Index extends Controller_Shell
{
    public function action_default()
    {
        $right_str_len = strlen('.controller'. EXT);

        $default_controller         = (string)Core::config('default_controller', 'index');
        $default_action             = Core::config('default_action');
        $default_controller_str_len = strlen($default_controller);

        $c = array();
        echo 'use:'. CRLF;
        foreach (Core::include_path() as $path)
        {
            $left_str_len = strlen($path .'controllers-shell' . DS);
            $tmp_path     = $path .'controllers-shell'. DS .'*.controller'. EXT;
            $files        = glob($tmp_path, GLOB_NOSORT);

            if ($files)foreach ($files as $file)
            {
                $controller = substr($file, $left_str_len, - $right_str_len);
                if ($controller == 'shell' || $controller == 'index')
                {
                    continue;
                }
                $c[$controller] = 1;

                $controller = str_replace(DS, '_', $controller);

                $ref = new ReflectionClass('Controller_'. $controller);

                if (substr('_'. $controller, - $default_controller_str_len - 2) === '_'. $default_controller)
                {
                    # 输出index.controller.php 里的方法
                    $m = $ref->getMethods();
                    if ($m)foreach ($m as $item)
                    {
                        $method = strtolower($item->name);

                        if (substr($method, 0, 7) === 'action_')
                        {
                            $mm = substr($method, 7);
                            if ($mm !== $default_action)
                            {
                                echo "    \x1b[33m{$mm}\x1b[39m";
                                $arr = explode("\n", str_replace(array("\r\n", "\r"), "\n", trim($ref->getDocComment(), "/\*\r\n ")));

                                if (strlen($arr[0]))
                                {
                                    echo '   - ', $arr[0];
                                }

                                echo "\n";
                            }
                        }
                    }
                    if ($controller === $default_controller)
                    {
                        continue;
                    }
                }
                else
                {
                    echo "    \x1b[32m{$controller}\x1b[39m";
                    $arr = explode("\n", str_replace(array("\r\n", "\r"), "\n", trim($ref->getDocComment(), "/\*\r\n ")));

                    if (strlen($arr[0]))
                    {
                        echo '   - ', $arr[0];
                    }

                    echo "\n";
                }
            }
        }

        while (true)
        {
            $std_in     = trim(fgets(STDIN));
            $std_in_arr = explode(' ', $std_in);
            $controller = $std_in_arr[0];
            if (isset($c[$controller]))
            {
                echo "Now use the controller: {$controller}\n\n";

                Core::execute($std_in);

                break;
            }
            else
            {
                echo "The controller {$controller} not exist. please try again.\n";
            }
        }
    }
}