<?php

class Controller_Install extends Controller_MyQEE__Install
{
    public function action_default()
    {
        $view = new View('admin/install/index');
        $view->render();
    }

    public function action_step_1()
    {
        $view = new View('admin/install/step_1');
        $this->step = 1;
        $view->render();
    }

    public function action_step_2()
    {
        if (HttpIO::METHOD == 'POST')
        {
            try
                {
                $data = $_POST;
                if ( empty($data['hostname']) )
                {
                    throw new Exception('数据库服务器不能空。');
                }
                if ( empty($data['database']) )
                {
                    throw new Exception('数据库名不能空。');
                }
                if ( empty($data['username']) )
                {
                    throw new Exception('数据库用户名不能空。');
                }
                $database = Core::config('admin/core.database');
                if (!$database)$database = 'default';
                $type = function_exists('mysqli_close')?'MySQLI':'MySQL';

                if ( is_array($data['hostname']) )
                {
                    $master = trim($data['hostname']['master']);
                    $slaver = explode("\n",str_replace(array("\r",' '),'',$data['hostname']['slaver']));

                    if (!$slaver || trim($data['hostname']['slaver'])==$master)
                    {
                        $hostname = "'".$master."'";
                    }
                    else
                    {
                        $slaver_str = "array\r\n            (";
                        foreach ($slaver as $s)
                        {
                            $slaver_str .= "\r\n                '".$s.'\',';
                        }
                        $slaver_str .= "\r\n            ),";
                        $hostname = <<<EOF
array
        (
            'master' => '{$master}',
            'slaver' => {$slaver_str}
        )
EOF;
                    }
                }
                else
                {
                    $hostname = '\''.$data['hostname'].'\'';
                }

                $config_str = <<<EOF
<?php
\$config['{$database}'] = array
(
    'type'       => '{$type}',
    'connection' => array
    (
        //数据库地址
        'hostname'   => {$hostname},
        'port'       => '{$data['port']}',        //端口
        'database'   => '{$data['database']}',    //库名称
        'username'   => '{$data['username']}',    //用户名
        'password'   => '{$data['password']}',    //密码
    ),
    'table_prefix' => '{$data['table_prefix']}',  //表前缀
    'charset'      => 'utf8',
    'caching'      => false,
);
EOF;
                if ( $_POST['save']==1 )
                {
                    $w = File::create_file(DIR_PROJECT . Core::$project . '/config/database.config'.EXT, $config_str);
                    if ($w)
                    {
                        $this->redirect(Core::url('install/step_3/'));
                    }
                    else
                    {
                        throw new Exception('没有保存配置文件的权限，请手动保存。');
                    }
                }
            }
            catch (Exception $e)
            {
                $message = $e->getMessage();
            }
        }
        $view = new View('admin/install/step_2');
        $this->step = 2;
        if (isset($message))
        {
            $view->message = $message;
        }
        if (isset($config_str))
        {
            $view->config_str = $config_str;
        }
        $view->render();
    }

    public function action_step_3()
    {
        $view = new View('admin/install/step_3');
        $this->step = 3;
        try
        {
            $database = Core::config('admin/core.database');
            if (!$database)$database = 'default';
            $db_config = Core::config('database.'.$database);
            $database_name = Core::config('database.'.$database.'.connection.database');
            $db_config['connection']['database'] = null;
            $db = new Database($db_config);
            $databases = $db->query('show databases')->as_array('Database','Database');
            if ( isset($databases[$database_name]) )
            {
                # 库已经存在
                unset($db);
                $db = Database::instance($database);
                $tables = $db->query('show tables')->as_array();
                if ( count($tables) )
                {
                    try
                    {
                        $m = $db->select('id')->from('admin_member')->limit(1)->get()->as_array();
                        if ($m)
                        {
                             $view->info = '当前数据库“'.$database_name.'”已经安装过本系统，是否要继续安装？';
                        }
                    }
                    catch (Exception $e)
                    {
                        $view->info = '当前数据库“'.$database_name.'”为非空数据库，您可以选择重新配置数据库，也可继续安装。';
                    }
                }
                else
                {
                    $view->ok = '指定的数据库“'.$database_name.'”已存在，已选择此数据库。';
                }
            }
            elseif ( $db->create_database($database_name) )
            {
                # 创建成功
                $view->ok = '数据库“'.$database_name.'”创建成功。';
            }
        }
        catch (Exception $e)
        {
            $view->error = $e->getMessage();
        }
        $view->render();
    }

    public function action_step_4()
    {
        # 关闭ob_start
        Core::close_buffers(true);
        $this->step = 4;
        View::set_global('config',self::$config);
        View::set_global('step',$this->step);
        $view = new View( 'admin/install/header' );
        $view->page_title = $this->page_title;
        $view->location = $this->location;
        $view->admin_menu = Core::config('admin/menu/install');
        $view->render( true );

        $view = new View('admin/install/step_4');
        $view->render();

        $view = new View( 'admin/install/bottom' );
        $view->render( true );

        # 将页面内容输出
        flush();

        $sqlfile = Core::find_file('data', 'install','sql');

        if ( $sqlfile )
        {
            $sql = file_get_contents($sqlfile);
    		$sql = str_replace("\r\n", "\n", $sql);
            $database = Core::config('admin/core.database');
            if (!$database)$database = 'default';
            $db = Database::instance($database);
    		$this->run_query($db,$sql);
        }


        if ( $this->lock_install() )
        {
            $this->show_js_message('安装成功。');
        }
        else
        {
            $this->show_js_message('安装完毕。');
            $this->show_js_message('<br><font color="red">请注意：</font>系统无法创建安装锁文件，请在data目录“<font color="blue">'.Core::$project.'/</font>”下创建<font color="red">install.lock</font>文件，以防止再次安装，请手动创建此文件。');
        }
        $this->show_js_message('<br>安全起见，请删除本安装文件：'.__FILE__);
        $this->show_js_message('<br /><input type="button" value="进入后台" class="submit" onclick="document.location=\''.Core::url('/').'\'" /> 用户名:admin，密码:123456');

        # 强制结束
        exit;
    }
}