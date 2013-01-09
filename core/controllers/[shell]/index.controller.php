<?php

class Controller_MyQEE_Index extends Controller_Shell
{
    public function action_default()
    {
        $right_strlen = strlen('.shell'.EXT);

        $default_controller = Core::config('core.default_controller');
        $default_action = Core::config('core.default_action');
        $default_controller_strlen = strlen($default_controller);

        echo 'use:'.CRLF;
        foreach ( Core::$include_path as $path )
        {
            $left_strlen = strlen($path.'shell' .DIRECTORY_SEPARATOR);
            $tmppath = $path.'shell' .DIRECTORY_SEPARATOR . '*.shell'.EXT;
            $files = glob( $tmppath, GLOB_NOSORT );
            if ($files)foreach ( $files as $file )
            {
                $controller = substr($file,$left_strlen,-$right_strlen);
                if ( $controller == 'shell' )
                {
                    continue;
                }

                $controller = str_replace( DIRECTORY_SEPARATOR, '_', $controller );

                if ( substr('_'.$controller, -$default_controller_strlen-2) == '_'.$default_controller )
                {
                    # 输出index.shell.php 里的方法
                    $ref = new ReflectionClass( 'Controller_'.$controller );
                    $m = $ref->getMethods();
                    if ($m)foreach ($m as $item)
                    {
                        $method = strtolower($item->name);
                        if ( substr($method,0,7)=='action_' )
                        {
                            $mm = substr($method,7);
                            if ($mm!=$default_action)
                            {
                                echo '    ' . str_replace( '_',' ', $mm ) . CRLF;
                            }
                        }
                    }
                    if ( $controller==$default_controller )
                    {
                        continue;
                    }
                }

                echo '    ' . str_replace( '_',' ', $controller ) . CRLF;
            }
        }
    }

}