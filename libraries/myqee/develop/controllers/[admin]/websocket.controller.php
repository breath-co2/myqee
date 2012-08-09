<?php

class Controller_WebSocket extends Controller_Admin
{

    protected static $host = 'localhost';

    protected static $port = 11101;

    public function action_default()
    {
        $this->page_title = '站点控制台';
        $view = new View('develop/websocket');
        $view->render();
    }

    public function action_start()
    {
        try
        {
            $socket = self::get_socket();
            $this->message('当前服务已启动');
        }
        catch (Exception $e)
        {
            if ( $e->getCode==-2 )
            {
                $this->message('指定端口已被其它服务占用');
            }
        }

        shell_exec('php '.DIR_SYSTEM.'shell/websocket/server.php -s localhost -p 11101 > /dev/null &' );

        # 停1秒钟后检查是否成功启动
        sleep(1);
        try
        {
            $socket = self::get_socket();
            $this->message('启动成功',1);
            return ;
        }
        catch (Exception $e)
        {
            throw $e;
            $this->message('启动失败');
        }
    }

    public function action_stop()
    {
        try
        {
            $socket = self::get_socket();
            $status = self::fwrite( $socket , 'stop' );
            if ( $status=='ok' )
            {
                $this->message('服务关闭成功',1);
            }
            else
            {
                $this->message('操作失败');
            }
        }
        catch (Exception $e)
        {
            if ( $e->getCode==-2 )
            {
                $this->message('当前服务无效');
            }
            else
            {
                $this->message('服务未启动');
            }
        }
    }

    public function action_restart()
    {
        try
        {
            $socket = self::get_socket();
            $status = self::fwrite( $socket , 'stop' );
        }
        catch (Exception $e)
        {
        }
        usleep(100000);
        $this->action_start();
    }

    /**
     * 获取一个连接
     */
    public static function get_socket()
    {
        $key = 'abcd';
        $value = array(
            'action' => 'login',
            'time'   => TIME,
            'hash'   => md5(TIME.'_'.$key),
        );
        $socket = @fsockopen(self::$host , self::$port , $error);

        if (!$socket)
        {
            throw new Exception('无连接',-1);
        }

        fputs($socket,json_encode($value));
        $get = self::fread($socket);

        if ( $get!='ok' )
        {
            throw new Exception('校验失败', -2);
        }

        return $socket;
    }

    /**
     * 发送消息
     *
     * @param $socket 连接对象
     * @param string $action 动作
     * @return string 返回结果
     */
    protected static function fwrite( $socket , $action )
    {
        $data = array(
            'action' => $action,
        );
        fputs($socket,json_encode($data));
        $get = self::fread($socket);
        return $get;
    }

    protected static function fread( $handle )
    {
        return trim(trim(fread($handle,99999999),chr(0)),chr(255));
    }
}