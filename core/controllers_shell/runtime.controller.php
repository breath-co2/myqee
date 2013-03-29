<?php

/**
 * runtime config 更新脚本
 *
 * @author jonwang
 *
 */
class Core_Controller_RunTime extends Controller_Shell
{
    /**
     * 检查当前服务器的可用性，包括MySQL,MongoDB,Memcache等
     */
    public function action_check_server()
    {
        $config = array
        (
            'time'=>TIME,
        );

        $config['mysql'] = $this->check_mysql();

        if ( $this->save_runtime_config($config) )
        {
            $this->output('save runtime config success.');
        }
        else
        {
            $this->output('save runtime config fail.');
        }
    }

    /**
     * 保存runtime配置
     *
     * @param array $config
     */
    protected function save_runtime_config($config)
    {
        return File::create_file(DIR_DATA.Core::$project.DS.'config_runtime.txt', serialize($config));
    }

    /**
     * 检查MySQL连接的可用性，包括读写速度
     *
     * 每隔5分钟检测一次即可
     */
    protected function check_mysql()
    {
        // 获取所有需要检测的服务器列表
        $servers = $this->mysql_get_servers();

        // 获取所有slave同步状态
        $status = $this->mysql_get_slave_status($servers);

        // 获取权重
        return $this->mysql_get_weight($status);
    }

    /**
     * 获取需要检测的mysql服务器列表
     *
     * 返回的内容类似
     *
     *     array
     *     (
     *         '127.0.0.1:3307' => array
     *         (
     *             'database' => 'test',
     *             'username' => 'root',
     *             'password' => '123456,
     *         ),
     *         '127.0.0.1:3308' => array
     *         (
     *             'database' => 'test',
     *             'username' => 'root',
     *             'password' => '123456,
     *         ),
     *     )
     *
     * @return array
     */
    protected function mysql_get_servers()
    {
        // 获取所有database的配置
        $all_config = Core::config('database');

        // runtime数据库相关设置
        /*
            // 例如：
            $runtime_config = array
            (
                //相关设置，比如可以设定不同的用户名和密码和库
                'servers' => array
                (
                    '127.0.0.1:3306' => array('username'=>'root','password'=>'123456'),
                ),
                'ignore'  => array('127.0.0.1:3306'),        //忽略的服务器
            );
        */
        $runtime_config = Core::config('core.server_runtime.database');

        $mysql_servers = array();
        foreach ($all_config as $name => $config)
        {
            if ($config['connection'])
            {

                // 解析connection，比如 mysql::test:123@127.0.0.1:3360/test
                if ( !is_array($config['connection']) )
                {
                    $config['connection'] = Database::parse_dsn($config['connection']);
                }

                // 端口
                $port = $config['port'] ? $config['port'] : 3306;

                if ( is_array($config['connection']['hostname']) )
                {
                    foreach ($config['connection']['hostname'] as $hostname)
                    {
                        if ( is_array($hostname) )
                        {
                            foreach ($hostname as $s)
                            {
                                $mysql_servers[$s.':'.$port] = array
                                (
                                    'database'     => $config['database'],
                                    'username'     => $config['username'],
                                    'password'     => $config['password'],
                                );
                            }
                        }
                    }
                }
            }
        }

        // 处理忽略不检查的数据库
        if ( isset($runtime_config['ignore']) && $runtime_config['ignore'] )
        {
            if ( is_array($runtime_config['ignore']) )
            {
                foreach ($runtime_config['ignore'] as $ignore_server)
                {
                    if (false===strpos($ignore_server,':'))$ignore_server .= ':3306';
                    unset($mysql_servers[$ignore_server]);
                }
            }
            else
            {
                if (false===strpos($runtime_config['ignore'],':'))$runtime_config['ignore'] .= ':3306';
                unset($mysql_servers[$runtime_config['ignore']]);
            }
        }

        if ( isset($runtime_config['servers']) && is_array($runtime_config['servers']) && $runtime_config['servers'] )foreach ($runtime_config['servers'] as $s=>$c)
        {
            if (!is_array($c))
            {
                $this->output('runtime servers config error,key='.$s.'. this value need an array like :'."array('username'=>'root','password'=>'123456')");
                continue;
            }

            if (false===strpos($s,':'))$s .= ':3306';

            if ( isset($mysql_servers[$s]) ) $mysql_servers[$s] = array_merge($mysql_servers[$s],$c);
        }

        $mysql_servers = array
        (
            '127.0.0.1:3306' => array('username'=>'root','password'=>123456),
            '127.0.0.1:3309' => array('username'=>'root','password'=>123456),
        );

        if (!$mysql_servers)
        {
            $this->output('no server.');
        }
        else
        {
            $this->output('server list:');
            print_r($mysql_servers);
        }

        return $mysql_servers;
    }

    /**
     * 获取slave的性能
     *
     * @param array $mysql_servers
     * @return array
     */
    protected function mysql_get_slave_status($mysql_servers)
    {

        // 记录所有服务器的相应时间
        $link_status = array();

        // 循环所有的MySQL数据库
        foreach ($mysql_servers as $host => $mysql_config)
        {
            $link_status[$host]['status'] = false;

            try
            {
                $time = microtime(1);
                $link = mysql_connect( $host, $mysql_config['username'], $mysql_config['password'] , true );

                if (!$link)
                {
                    // 输出错误信息
                    $this->output('mysql connect error.hoss='.$host.'.error msg='.mysql_error());
                    continue;
                }

                $result = mysql_query('show slave status',$link);
                $row = mysql_fetch_array($result);

                // 进程数，越小越好,至少会是1
                $result = mysql_query('show global status like \'Threads_running\'',$link);
                $threads_running = mysql_fetch_array($result);
                $threads_running = $threads_running['Threads_running'];

                // 已连接数，越小越好
                $result = mysql_query('show global status like \'Threads_connected\'',$link);
                $threads_connected = mysql_fetch_array($result);
                $threads_connected = $threads_connected['Threads_connected'];

                // 关闭数据库连接
                mysql_close($link);

                // 获取执行时间，可反应出连接数据
                $time = (microtime(1)-$time);

                $link_status[$host]['status'] = true;

                // 同步状态
                if ($row['Slave_IO_Running']=='No')
                {
                    // 不同步
                    $link_status[$host]['status'] = false;
                    $this->output($host.' slave io not running.');
                    continue;
                }

                if ($row['Slave_SQL_Running']=='No')
                {
                    // 没运行
                    $link_status[$host]['status'] = false;
                    $this->output($host.' slave sql not running.');
                    continue;
                }

                $link_status[$host]['delay'] = (int)$row['Seconds_Behind_Master'];
                $this->output($host.' delay '.(int)$row['Seconds_Behind_Master'].'s.');

                $link_status[$host]['running']   = (int)$threads_running;
                $link_status[$host]['connected'] = (int)$threads_connected;

                $link_status[$host]['time'] = $time;

                $this->output($host.' check successfully. total time '.$time);

                // 清除变量
                unset($link,$result,$time,$threads_running,$threads_connected);
            }
            catch (Exception $e)
            {
                $this->output( 'mysql connect error.hoss='.$host.'.error msg='.$e->getMessage() );
            }
        }

        $this->output('status:');
        print_r($link_status);

        return $link_status;
    }

    /**
     * 获取相应的权重
     *
     * @param array $status
     * @return array
     */
    protected function mysql_get_weight($status)
    {

        $data = array();

        // 处理weight
        foreach ($status as $s => $v)
        {
            if ( false===$v['status'] )
            {
                // 服务器出问题
                $data[$s] = false;
                continue;
            }

            // 运行数 通常在1-20之间
            if (!$v['running']>0)$v['running']=1;
            $weight = 100/$v['running'];

            // 连接数，通常在1-100之间
            if (!$v['connected']>0)$v['connected']=1;
            $weight += 50/$v['connected'];


            // 执行时间
            if ($v['time']<=0.1)
            {
                $weight += sqrt(1/$v['time'])*0.6;
            }
            elseif ($v['time']<=1)
            {
                $weight = $weight * 0.6;
            }
            elseif ($v['time']<=2)
            {
                $weight = $weight * 0.1;
            }
            else
            {
                $weight = 1;
            }


            // 从数据库延迟
            if ( $v['delay']<=3 )
            {
                $weight = $weight * 1.5;
            }
            elseif ( $v['delay']<=600 )
            {
                $weight = $weight / sqrt($v['delay']);
            }
            else
            {
                // 超过600秒的weight=0
                $weight = 1;
            }

            $data[$s] = (int)$weight;
        }

        $this->output('=====================weight=================');
        print_r($data);

        return $data;
    }
}