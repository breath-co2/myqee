<?php

abstract class Library_MyQEE_Administration_Controller_Install extends Controller
{
    /**
     * 当前安装配置
     * @var array
     */
    protected static $config;
    /**
     * 当前步骤
     * @var int
     */
    protected $step = 0;

    public function before()
    {
        $have_installed_file = DIR_DATA . Core::$project . '/install.lock';
        self::$config = Core::config('admin/install');
        if ( !self::$config )
        {
            echo '<br><br>';
            echo __('do not have install config.');
            echo '<br><br><br>';
            $this->after();
            exit;
        }
        if ( file_exists($have_installed_file) )
        {
            echo '<br><br>';
            echo '安装锁定。程序已经安装过了，如果您确定要重新安装，请到服务器上删除下面文件：';
            echo '<br>';
            echo '<br>';
            echo 'Data目录：'.Core::$project.'/install.lock';
            echo '<br><br><br>';
            $this->after();
            exit;
        }
        ob_start();
    }

    public function after()
    {
        $output = ob_get_clean();

        if ( !HttpIO::IS_AJAX )
        {
            View::set_global('config',self::$config);
            View::set_global('step',$this->step);
            $view = new View( 'admin/install/header' );
            $view->page_title = $this->page_title;
            $view->location = $this->location;
            $view->admin_menu = Core::config('admin/menu/install');
            $view->render( true );
            echo $output;
            $view = new View( 'admin/install/bottom' );
            $view->render( true );
        }
        else
        {
            echo $output;
        }
    }

    protected function run_query(Database $db , $sql)
    {
        if (!$sql)
        {
            return;
        }
        $table_prefix = $db->table_prefix();

        $sql = str_replace ( "\r", "\n", str_replace ( ' ' . ORIG_TABLEPRE, ' ' . $table_prefix, $sql ) );
        $sql = str_replace ( "\r", "\n", str_replace ( ' `' . ORIG_TABLEPRE, ' `' . $table_prefix, $sql ) );
        $ret = array ();
        $num = 0;
        foreach ( explode ( ";\n", trim ($sql) ) as $query ) {
            $ret [$num] = '';
            $queries = explode ( "\n", trim($query) );
            foreach ( $queries as $query ) {
                $ret[$num] .= (isset ( $query[0] ) && $query[0] == '#') || (isset ( $query [1] ) && isset ( $query [1] ) && $query [0] . $query [1] == '--') ? '' : $query;
            }
            # 表前缀
            $ret[$num] = str_replace('{{table_prefix}}', $table_prefix, $ret[$num]);
            $num ++;
        }
        unset ($sql);

        foreach ( $ret as $query )
        {
            $query = trim ( $query );
            if ($query)
            {
                if ( strtoupper(substr($query, 0, 12 )) == 'CREATE TABLE' )
                {
                    $name = preg_replace ( '#CREATE TABLE `([a-z0-9_]+)` .*#is', "\\1", $query );
                    if ($db->query($query)->result())
                    {
                        $status = 'succeed';
                    }
                    else
                    {
                        $status = 'failed';
                    }
                    $this->show_js_message ( __('create_table' ) . ' ' . $name . ' ... ' . __($status) );
                }
                else
                {
                    $db->query($query);
                }
                usleep(10000);
            }
        }
    }

    protected function show_js_message($msg)
    {
        echo '<script type="text/javascript">'.CRLF;
        echo 'showmessage('.str_replace(array("\n","\r"),array('\\n','\\r'),var_export($msg,true)).');';
        echo '</script>'.CRLF;

        # 将内容推送输出到浏览器
        flush();
    }

    /**
     * 锁定安装
     */
    protected function lock_install()
    {
        $file = DIR_DATA . Core::$project . '/install.lock';
        return File::create_file($file, ' ');
    }
}