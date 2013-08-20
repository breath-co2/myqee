<?php

/**
 * Swift Storage驱动器
 *
 * 驱动类型为OpenStack Object Storage (Swift)
 *
 * @see http://www.openstack.org/software/openstack-storage/
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Core_Storage_Driver_Swift extends Storage_Driver
{
    /**
     * 授权信息
     *
     * @var string
     */
    protected $token;

    /**
     * 授权失效时间
     *
     * @var int
     */
    protected $token_timeout = 180;

    /**
     * Swift服务器版本
     *
     * @var string
     */
    protected $swift_version = 'v1.0';

    /**
     * Swift服务器IP或域名
     *
     * @var string
     */
    protected $host;

    /**
     * Swift服务器端口
     *
     * @var string
     */
    protected $port = 80;

    /**
     * Swift服务器账号
     *
     * @var string
     */
    protected $account;

    /**
     * Swift服务器密码
     *
     * @var string
     */
    protected $key;

    /**
     * storage url
     *
     * @var string
     */
    protected $storage_url;

    /**
     * storage host
     *
     * @var string
     */
    protected $storage_host;

    /**
     * storage port
     *
     * @var string
     */
    protected $storage_port;

    /**
     * storage path
     *
     * @var string
     */
    protected $storage_path;

    /**
     * storage 连接类型
     *
     * @var string http|https
     */
    protected $storage_protocol = 'http';

    /**
     * 连接超时时间,单位秒
     *
     * @var int
     */
    protected $timeout = 30;

    /**
     * HTTP协议版本号
     *
     * @var string
     */
    protected $protocol_version = 'HTTP/1.1';

    /**
     * 连接类型
     *
     * @var string http|https
     */
    protected $protocol = 'http';

    /**
     * 链接对象
     *
     * @var array
     */
    protected static $connections = array();

    /**
     * 最后连接时间
     *
     * @var array
     */
    protected static $last_used = array();

    /**
     * 请求次数
     *
     * @var array
     */
    protected static $requests_num = array();

    /**
     * @param string $config_name 配置名或数组
     */
    public function __construct($config_name = 'default')
    {
        if (is_array($config_name))
        {
            $config = $config_name;
            $config_name = md5(serialize($config_name));
        }
        else if (is_string($config_name) && strpos($config_name, '://')!==false)
        {
            $config = parse_url($config_name);
        }
        else
        {
            $config = Core::config('storage/swift.' . $config_name);

            if (is_string($config_name) && strpos($config_name, '://')!==false)
            {
                $config = parse_url($config_name);
            }
        }

        $this->account  = $config['user'];
        $this->key      = $config['pass'];
        $this->host     = $config['host'];
        $this->protocol = $config['scheme'];

        if (!$config['port'])
        {
            $this->port = $this->protocol=='https'?443:80;
        }

        if ($config['path'])
        {
            $this->set_prefix($config['path']);
        }

        if (!$this->host)
        {
            throw new Exception(__('The storage swift config :config does not exist', array(':config'=>$config_name)));
        }

        # 增加自动关闭连接列队
        Core::add_close_connect_class('Storage_Driver_Swift');
    }

    /**
     * 取得数据
     *
     * @param string/array $key
     * @return mixed
     */
    public function get($key)
    {
        if (is_array($key))
        {
            $rs = array();
            foreach ($key as $k)
            {
                $rs[$k] = $this->get($k);
            }

            return $rs;
        }

        $rs = $this->get_response($key, 'GET');

        if ($rs['code']>=200 && $rs['code']<300)
        {
            $this->_de_format_data($rs['body']);

            return $rs['body'];
        }

        if ($rs['code']==404)return null;

        throw new Exception(__('Swift get error, code: :code.', array(':code'=>$rs['code'])));
    }

    /**
     * 存数据
     *
     * @param string/array $key 支持多存
     * @param $data Value 多存时此项可空
     * @return boolean
     */
    public function set($key, $value = null)
    {
        if (is_array($key))
        {
            $rs = true;
            foreach ($key as $k=>$v)
            {
                if (!$this->set($k, $v))
                {
                    $rs = false;
                }
            }

            return $rs;
        }

        $this->_format_data($value);

        $rs = $this->get_response($key, 'PUT', null, null, $value);

        if ($rs['code']>=200 && $rs['code']<300)return true;

        throw new Exception(__('Swift get error, code: :code.', array(':code'=>$rs['code'])));
    }

    /**
     * 删除指定key的缓存，若$key===true则表示删除全部
     *
     * @param string $key
     */
    public function delete($key)
    {
        if (is_array($key))
        {
            $rs = true;
            foreach ($key as $k)
            {
                if (!$this->delete($k))
                {
                    $rs = false;
                }
            }

            return $rs;
        }

        $rs = $this->get_response('*', 'DELETE');

        if ($rs['code']>=200 && $rs['code']<300)return true;
        if ($rs['code']==404)return true;

        throw new Exception(__('Swift get error, code: :code.', array(':code'=>$rs['code'])));
    }

    /**
     * 删除全部
     *
     * @return boolean
     */
    public function delete_all()
    {
        //TODO 暂不支持

        return false;
    }

    /**
     * 设置前缀
     *
     * @param string $prefix
     * @return $this
     */
    public function set_prefix($prefix)
    {
        if ($prefix)
        {
            $this->prefix = trim($prefix, ' /_');
        }
        else
        {
            $prefix = 'default';
        }

        return $this;
    }

    /**
     * 获取一个请求
     *
     * @param string $url
     * @param string $method
     * @param array $headers
     * @param array $query
     * @param string $body
     * @param int $chunk_size
     * @return string
     */
    protected function get_response($uri, $method, $headers = array(), $query = array(), $body = null, $chunk_size = 10240)
    {
        $method = strtoupper($method);

        # 获取连接对象
        $fp = $this->connection();

        # 计数统计
        Storage_Driver_Swift::$requests_num[$this->storage_host]++;

        $metadata = stream_get_meta_data($fp);

        if ((Storage_Driver_Swift::$requests_num[$this->storage_host] % 100)===0 || true===$metadata['timed_out'])
        {
            unset($fp);

            $fp = $this->connection(true);
        }
        unset($metadata);

        if (!$headers)$headers = array();
        if (!$query)$query = array();

        # 处理头信息
        if (!isset($headers['Content-Length']))
        {
            if ($body)
            {
                if (is_resource($body))
                {
                    $headers['Transfer-Encoding'] = 'chunked';
                }
                else
                {
                    $headers['Content-Length'] = strlen($body);
                }
            }
            else
            {
                $headers['Content-Length'] = '0';
            }
        }

        $prepped_url = parse_url($this->storage_url);

        $host = $prepped_url['host'];
        $path = $prepped_url['path'];

        $uri = $path . '/' . $this->prefix . '/' . $uri;
        $headers['Host'] = $host;

        if (IS_DEBUG)Core::debug()->info($this->storage_protocol .'://'. $host . ($this->port!=80&&$this->port!=443?':'.$this->port:'') . $uri, 'Swift '.$method);

        # 拼接头信息
        $message = $this->build_request_line($method, $uri, $query) . $this->build_headers($headers);

        fwrite($fp, $message);

        # 输入内容
        if ($body)
        {
            if (is_resource($body))
            {
                while (!feof($body))
                {
                    $data = fread($body, $chunk_size);
                    $len  = dechex(strlen($data));
                    fwrite($fp, $len . "\n" . $data . "\r\n");
                }
                # 指针移回去
                rewind($body, 0);

                # HTTP结束符
                fwrite($fp, "0\r\n\r\n");
            }
            else
            {
                fwrite($fp, $body);
            }
        }

        $rs = $this->read($fp);

        if (IS_DEBUG)Core::debug()->info('Swift get code:' . $rs['code']);

        return $rs;
    }

    /**
     * 获取HTTP协议内容
     *
     * 将返回
     *
     *    array
     *    (
     *        'header' => array(),
     *        'code'   => 200,
     *        'proto'  => 'HTTP/1.1',
     *        'body'   => '',
     *    )
     *
     * @param fsockopen $fp
     * @return array
     */
    protected function read($fp)
    {
        $rs = array
        (
            'code'   => 0,
            'header' => array(),
            'body'   => '',
        );

        # 读取第一行
        $head_line = fgets($fp);

        if (preg_match('#^(HTTP/[0-9\.]+) ([0-9]+) ([a-z0-9 ]+)?$#i', trim($head_line), $m))
        {
            $rs['proto'] = $m[1];
            $rs['code']  = (int)$m[2];
        }
        else
        {
            throw new Exception(__('Swift get data error.Data: :data', array(':data'=>$head_line)));
        }

        # 是否分片读取
        $transfer_encoding = false;

        # 内容长度
        $content_length = 0;

        # 是否读取body部分
        $read_body = false;

        $switch = false;

        while (true)
        {
            if ($switch)
            {
                # 退出
                break;
            }

            if ($read_body)
            {
                if ($transfer_encoding)
                {
                    $chunk = trim(fgets($fp));
                    $chunk_length = hexdec($chunk);

                    if ($chunk_length>0)
                    {
                        $rs['body'] .= fread($fp, $chunk_length);
                    }
                    else
                    {
                        fread($fp, 2);    //读取最后的结束符 \r\n
                        $switch = true;
                    }
                }
                else
                {
                    if ($content_length>0)
                    {
                        $rs['body'] = fread($fp, $content_length);
                    }
                    $switch = true;
                }
            }
            else
            {
                $tmp = fgets($fp);
                if ($tmp=="\r\n")
                {
                    $read_body = true;
                }
                else
                {
                    list($k, $v) = explode(':', $tmp, 2);
                    $k = trim($k);
                    $v = trim($v);
                    $rs['header'][$k] = $v;

                    if ($k=='Content-Length')
                    {
                        $content_length = $v;
                    }
                    else if ($k=='Transfer-Encoding')
                    {
                        # 分片获取
                        $transfer_encoding = true;
                    }
                }
            }
        }

        return $rs;
    }

    /**
     * 返回连接对象
     *
     * @return resource
     */
    protected function connection($re_connect=false)
    {
        # 更新token
        $this->get_token();

        if (!$re_connect && !isset(Storage_Driver_Swift::$connections[$this->storage_host]))
        {
            Storage_Driver_Swift::$connections[$this->storage_host] = $this->fp($this->storage_protocol, $this->storage_host, $this->storage_port, $this->timeout);
            Storage_Driver_Swift::$requests_num[$this->storage_host] = 0;
        }
        else if ($re_connect || (time()-Storage_Driver_Swift::$last_used[$this->storage_host] >= $this->timeout))
        {
            # 超时的连接，销毁后重新连接
            @fclose(Storage_Driver_Swift::$connections[$this->storage_host]);
            unset(Storage_Driver_Swift::$connections[$this->storage_host]);

            Storage_Driver_Swift::$connections[$this->storage_host] = $this->fp($this->storage_protocol, $this->storage_host, $this->storage_port, $this->timeout);
            Storage_Driver_Swift::$requests_num[$this->storage_host] = 0;
        }

        Storage_Driver_Swift::$last_used[$this->storage_host] = time();

        return Storage_Driver_Swift::$connections[$this->storage_host];
    }

    /**
     * 连接服务器
     *
     * @param string $host
     * @param int $port
     * @param int $timeout
     * @throws Exception
     * @return boolean
     */
    protected function fp($protocol, $host, $port , $timeout)
    {
        try
        {
            if ($protocol == 'https')
            {
                $fp = fsockopen('tls://' . $host, $port, $errno, $errstr, $timeout);
            }
            else
            {
                $fp = fsockopen($host, $port, $errno, $errstr, $timeout);
            }
        }
        catch (Exception $e)
        {
            $errstr = $e->getMessage();
            $errno  = $e->getCode();
            $fp     = false;
        }

        if (!$fp)
        {
            throw new Exception('Unable to connect to: ' . $host . ':' . $port ."\nError: " . $errstr . ' : ' .$errno);
        }

        return $fp;
    }

    /**
     * 获取token
     *
     * @return string
     */
    protected function get_token()
    {
        # 缓存key
        $key = 'swift_storage_auth_' . $this->host . '_' . $this->account;

        # 获取缓存token
        $token = Cache::instance()->get($key);

        if ($token)
        {
            $this->token       = $token[0];
            $this->storage_url = $token[1];

            $h = parse_url($this->storage_url);

            $this->storage_host     = $h['host'];
            $this->storage_path     = $h['path'];
            $this->storage_protocol = $h['scheme'];
            $this->storage_port     = $h['port']?$h['port']:$h['scheme']=='https'?443:80;

            if (IS_DEBUG)Core::debug()->info($this->token, 'Swift Token From Cache');
        }
        else
        {
            # 获取token
            $this->get_real_token();

            # 设置缓存
            Cache::instance()->set($key, array($this->token, $this->storage_url), $this->token_timeout);
        }
    }

    /**
     * 获取token
     */
    protected function get_real_token()
    {
        $headers = array
        (
            'Host'           => $this->host,
            'X-Auth-User'    => $this->account,
            'X-Auth-Key'     => $this->key,
            'Content-Length' => 0,
        );

        if (IS_DEBUG)Core::debug()->info($this->protocol .'://'. $this->account . '@' . $this->host . ($this->port!=80&&$this->port!=443?':'.$this->port:'') . '/' . $this->swift_version, 'Swift get token url');

        $fp = $this->fp($this->protocol, $this->host, $this->port, $this->timeout);

        $message = $this->build_request_line('GET', '/'.$this->swift_version) . $this->build_headers($headers);

        fwrite($fp, $message);

        $rs = $this->read($fp);

        if ($rs['code']<200 || $rs['code']>=300)
        {
            throw new Exception(__('Swift get token error. Code: :code.', array(':code'=>$rs['code'])));
        }

        # 获取token
        if (isset($rs['header']['X-Auth-Token']))
        {
            $this->token = $rs['header']['X-Auth-Token'];
        }
        else
        {
            throw new Exception(__('Swift get token error.not found X-Auth-Token'));
        }

        # 获取Storage URL
        if (isset($rs['header']['X-Storage-Url']))
        {
            $this->storage_url = $rs['header']['X-Storage-Url'];
            $h = parse_url($this->storage_url);

            $this->storage_host     = $h['host'];
            $this->storage_path     = $h['path'];
            $this->storage_protocol = $h['scheme'];
            $this->storage_port     = $h['port']?$h['port']:$h['scheme']=='https'?443:80;
        }
        else
        {
            throw new Exception(__('Swift get token error.not found X-Storage-Url'));
        }
        if (IS_DEBUG)Core::debug()->info($this->token, 'Swift Token');

        if ($this->storage_url)
        {
            if ($this->storage_host==$this->host)
            {
                # 将连接加到$connections里复用
                Storage_Driver_Swift::$connections[$this->host] = $fp;
                Storage_Driver_Swift::$last_used[$this->host] = time();
                Storage_Driver_Swift::$requests_num[$this->host] = 0;
            }
            else
            {
                # 域名不一致，可以关闭token服务器
                fclose($fp);
            }
        }
    }

    protected function build_request_line($method, $uri, $query = null)
    {
        if ($uri!='/')
        {
            $url_array = array();
            foreach (explode('/', $uri) as $i)
            {
                $url_array[] = rawurlencode($i);
            }
            $uri = implode('/', $url_array);
        }

        if ($query)
        {
            if (is_array($query))
            {
                foreach ($query as $key => $value)
                {
                    $query_str .= '&' . rawurlencode($key) . '=' . rawurlencode($value) ;
                }
            }
            else
            {
                $query_str = $query;
            }

            $uri .= '?' . trim($query_str, '&');
        }

        $request_line = $method . ' ' . $uri . ' ' . $this->protocol_version . "\r\n";

        return $request_line;
    }

    /**
     * 将数组头信息转成字符
     *
     * @param array $headers
     * @return string
     */
    protected function build_headers(array $headers)
    {
        $headers['Connection'] = 'keep-alive';

        if (!isset($headers['X-Auth-Key']) && !isset($headers['X-Auth-User']))
        {
            # 加token
            $headers['X-Auth-Token'] = $this->token;
        }

        if (!isset($headers['Date']))
        {
            $headers['Date'] = gmdate('D, d M Y H:i:s \G\M\T');
        }

        $header_str = '';
        foreach ($headers as $key => $value)
        {
            $header_str .= trim($key) . ": " . trim($value) . "\r\n";
        }

        $header_str .= "\r\n";

        return $header_str;
    }

    /**
     * 关闭所有链接
     */
    public static function close_all_connect()
    {
        foreach (Storage_Driver_Swift::$connections as $host=>$obj)
        {
            try
            {
                fclose($obj);
            }
            catch (Exception $e)
            {
                Core::debug()->error('close swift storage connect error:' . $e->getMessage());
            }

            Storage_Driver_Swift::$connections[$host] = null;
        }

        # 重置全部数据
        Storage_Driver_Swift::$connections = array();
        Storage_Driver_Swift::$last_used = array();
        Storage_Driver_Swift::$requests_num = array();

        if (IS_DEBUG)Core::debug()->info('close all swift storage server.');
    }
}