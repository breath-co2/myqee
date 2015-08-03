<?php
/**
 * Fluent日志处理核心类
 *
 * 配置根目录 `$config['log']['fluent'] = 'tcp://127.0.0.1:24224/'` 后
 * 使用 `Core::log('myapp.test.debug', $_SERVER)` 默认就可以调用本方法
 *
 *
 *      Fluent::instance('tcp://127.0.0.1:24224/')->push('xd.game.test', ["test"=>"hello"]);
 *
 *      Fluent::instance('unix:///full/path/to/my/socket.sock')->push('xd.game.test', ["test"=>"hello"]);
 *
 *
 * @see        https://github.com/fluent/fluent-logger-php
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Core
 * @package    Classes
 * @copyright  Copyright (c) 2008-2016 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Core_Fluent
{
    const CONNECTION_TIMEOUT = 3;
    const SOCKET_TIMEOUT     = 3;
    const MAX_WRITE_RETRY    = 5;

    /* 1000 means 0.001 sec */
    const USLEEP_WAIT = 1000;

    /**
     * backoff strategies: default usleep
     *
     * attempts | wait
     * 1        | 0.003 sec
     * 2        | 0.009 sec
     * 3        | 0.027 sec
     * 4        | 0.081 sec
     * 5        | 0.243 sec
     **/
    const BACKOFF_TYPE_EXPONENTIAL = 0x01;
    const BACKOFF_TYPE_USLEEP      = 0x02;

    /**
     * 服务器
     *
     * 例如 `tcp://127.0.0.1:24224`
     *
     * @var string
     */
    protected $transport;

    /* @var resource */
    protected $socket;

    protected $is_http = false;

    protected $options = array
    (
        "socket_timeout"     => self::SOCKET_TIMEOUT,
        "connection_timeout" => self::CONNECTION_TIMEOUT,
        "backoff_mode"       => self::BACKOFF_TYPE_USLEEP,
        "backoff_base"       => 3,
        "usleep_wait"        => self::USLEEP_WAIT,
        "persistent"         => true,
        "retry_socket"       => true,
        "max_write_retry"    => self::MAX_WRITE_RETRY,
    );

    /**
     * @var Fluent
     */
    protected static $instance = array();

    function __construct($server)
    {
        $this->transport = $server;

        if (($pos = strpos($server, '://')) !== false)
        {
            $protocol = substr($server, 0, $pos);

            if (!in_array($protocol, array('tcp', 'udp', 'unix', 'http')))
            {
                throw new Exception("transport `{$protocol}` does not support");
            }

            if ($protocol === 'http')
            {
                # 使用HTTP推送
                $this->is_http = true;
                $this->transport = rtrim($this->transport, '/ ');
            }
        }
        else
        {
            throw new Exception("fluent config error");
        }
    }

    /**
     * destruct objects and socket.
     *
     * @return void
     */
    public function __destruct()
    {
        if (!$this->get_option('persistent', false) && is_resource($this->socket))
        {
            @fclose($this->socket);
        }
    }

    /**
     * 返回Fluent处理对象
     *
     * @return Fluent
     */
    public static function instance($server)
    {
        if (!isset(Fluent::$instance[$server]))
        {
            Fluent::$instance[$server] = new Fluent($server);
        }

        return Fluent::$instance[$server];
    }

    /**
     * post implementation
     *
     * @param string $tag
     * @param array $data
     * @return bool
     * @throws Exception
     */
    public function push($tag, $data)
    {
        if ($this->is_http)
        {
            return $this->push_with_http($tag, $data);
        }
        else
        {
            return $this->push_with_socket($tag, $data);
        }
    }

    protected function push_with_http($tag, $data)
    {
        $packed  = Core::json_encode($data);
        $url     = $this->transport .'/'. $tag .'?json='. urlencode($packed);

        $ret = file_get_contents($url);

        return ($ret !== false && $ret === '');
    }

    protected function push_with_socket($tag, $data)
    {
        $buffer = $packed = Core::json_encode(array($tag, time(), $data));
        $length = strlen($packed);
        $retry  = $written = 0;

        try
        {
            $this->reconnect();
        }
        catch (Exception $e)
        {
            $this->close();
            $this->process_error($tag, $data, $e->getMessage());

            return false;
        }

        try
        {
            // PHP socket looks weired. we have to check the implementation.
            while ($written < $length)
            {
                $nwrite = $this->write($buffer);

                if ($nwrite === false)
                {
                    // could not write messages to the socket.
                    // e.g) Resource temporarily unavailable
                    throw new Exception("could not write message");
                }
                else if ($nwrite === '')
                {
                    // sometimes fwrite returns null string.
                    // probably connection aborted.
                    throw new Exception("connection aborted");
                }
                else if ($nwrite === 0)
                {
                    if (!$this->get_option("retry_socket", true))
                    {
                        $this->process_error($tag, $data, "could not send entities");

                        return false;
                    }

                    if ($retry > $this->get_option("max_write_retry", self::MAX_WRITE_RETRY))
                    {
                        throw new Exception("failed fwrite retry: retry count exceeds limit.");
                    }

                    $errors = error_get_last();
                    if ($errors)
                    {
                        if (isset($errors['message']) && strpos($errors['message'], 'errno=32 ') !== false)
                        {
                            /* breaking pipes: we have to close socket manually */
                            $this->close();
                            $this->reconnect();
                        }
                        else if (isset($errors['message']) && strpos($errors['message'], 'errno=11 ') !== false)
                        {
                            // we can ignore EAGAIN message. just retry.
                        }
                        else
                        {
                            error_log("unhandled error detected. please report this issue to http://github.com/fluent/fluent-logger-php/issues: ". var_export($errors, true));
                        }
                    }

                    if ($this->get_option('backoff_mode', self::BACKOFF_TYPE_EXPONENTIAL) == self::BACKOFF_TYPE_EXPONENTIAL)
                    {
                        $this->backoff_exponential(3, $retry);
                    }
                    else
                    {
                        usleep($this->get_option("usleep_wait", self::USLEEP_WAIT));
                    }
                    $retry++;
                    continue;
                }

                $written += $nwrite;
                $buffer   = substr($packed, $written);
            }
        }
        catch (Exception $e)
        {
            $this->close();
            $this->process_error($tag, $data, $e->getMessage());

            return false;
        }

        return true;
    }


    /**
     * write data
     *
     * @param string $data
     * @return mixed integer|false
     */
    protected function write($buffer)
    {
        // We handle fwrite error on postImpl block. ignore error message here.
        return @fwrite($this->socket, $buffer);
    }

    /**
     * create a connection to specified fluentd
     *
     * @throws \Exception
     */
    protected function connect()
    {
        $connect_options = STREAM_CLIENT_CONNECT;
        if ($this->get_option("persistent", false))
        {
            $connect_options |= STREAM_CLIENT_PERSISTENT;
        }

        // could not suppress warning without ini setting.
        // for now, we use error control operators.
        $socket = @stream_socket_client($this->transport, $errno, $errstr, $this->get_option("connection_timeout", self::CONNECTION_TIMEOUT), $connect_options);

        if (!$socket)
        {
            $errors = error_get_last();
            throw new Exception($errors['message']);
        }

        // set read / write timeout.
        stream_set_timeout($socket, $this->get_option("socket_timeout", self::SOCKET_TIMEOUT));

        $this->socket = $socket;
    }

    /**
     * create a connection if Fluent Logger hasn't a socket connection.
     *
     * @return void
     */
    protected function reconnect()
    {
        if (!is_resource($this->socket))
        {
            $this->connect();
        }
    }

    /**
     * close the socket
     *
     * @return void
     */
    public function close()
    {
        if (is_resource($this->socket))
        {
            fclose($this->socket);
        }

        $this->socket = null;
    }

    /**
     * get specified option's value
     *
     * @param      $key
     * @param null $default
     * @return mixed
     */
    protected function get_option($key, $default = null)
    {
        $result = $default;
        if (isset($this->options[$key]))
        {
            $result = $this->options[$key];
        }

        return $result;
    }

    /**
     * backoff exponential sleep
     *
     * @param $base int
     * @param $attempt int
     */
    protected function backoff_exponential($base, $attempt)
    {
        usleep(pow($base, $attempt) * 1000);
    }

    /**
     * 处理错误
     *
     * @param $tag
     * @param $data
     * @param $error
     */
    protected function process_error($tag, $data, $error)
    {
        error_log(sprintf("%s %s: %s", $error, $tag, json_encode($data)));
    }
}