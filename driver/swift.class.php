<?php

/**
 * Swift Storage驱动器
 *
 * 驱动类型为OpenStack Object Storage (Swift)
 *
 * @see http://www.openstack.org/software/openstack-storage/
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Module
 * @package    Storage
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Module_Storage_Driver_Swift extends Storage_Driver
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
     * Token 服务版本
     *
     * @var string
     */
    protected $token_api_version = 'v2.0';

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
     * storage 仓库
     *
     * @var string
     */
    protected $warehouses = 'default';

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
     * tenant name
     *
     * @var string
     */
    protected $tenant_name = '';

    /**
     * region
     *
     * @var string
     */
    protected $region = '';

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
     * 初始化对象
     *
     * 配置可以是一个数值也可以是一个字符串，例如
     *
     *      $config = array
     *      (
     *          'driver'        => 'Swift',
     *          'driver_config' => 'https://username:password@localhost:8080/v2.0?region=test&tenant_name=default&warehouses=mytest&prefix=test',
     *      );
     *      $storage = new Storage($config);
     *
     * 数值形式
     *
     *      $config = array
     *      (
     *          'driver' => 'Swift',
     *          'driver_config' => array
     *          (
     *              'host'              => 'localhost',     // 服务器IP或域名
     *              'user'              => 'username',      // 用户名
     *              'pass'              => 'password',      // 密码(key)
     *              'warehouses'        => 'mytest',        // 储存仓库，类似数据库的库，可不设置，默认为 default
     *              // 以下是token接口为v2.0的时候必须
     *              'tenant_name'       => 'default',       // Tenant 名称
     *              'region'            => 'test',          // Region
     *              // 以下为可选参数
     *              'https'             => true,            // true || false , 默认 false
     *              'token_api_version' => 'v2.0',          // 版本，不设置则默认 v2.0
     *              'port'              => 8080,            // 端口，默认http 为 80，https 为 443
     *              'prefix'            => 'test',          // key的前缀，默认为空
     *          ),
     *      );
     *      $storage = new Storage($config);
     *
     * @param string | array $config_name 配置名或数组
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
            if (isset($config['query']) && $config['query'])
            {
                parse_str($config['query'], $query);
                if ($query && is_array($query))
                {
                    $config += $query;
                }
            }

            $path = trim($config['path'], '/');
            if ($path)
            {
                $config['token_api_version'] = $path;
                unset($path);
            }


            $config['https'] = $config['scheme']=='https'?true:false;
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

        if (isset($config['https']) && $config['https'])
        {
            $this->protocol = 'https';
        }

        if (isset($config['token_api_version']) && $config['token_api_version'])
        {
            $this->token_api_version = $config['token_api_version'];
        }

        if (isset($config['tenant_name']) && $config['tenant_name'])
        {
            $this->tenant_name = $config['tenant_name'];
        }

        if (isset($config['region']) && $config['region'])
        {
            $this->region = $config['region'];
        }

        if (isset($config['warehouses']) && $config['warehouses'])
        {
            $this->warehouses = $config['warehouses'];
        }

        if (!isset($config['port']) || !$config['port'])
        {
            $this->port = $this->protocol=='https'?443:80;
        }

        if (isset($config['prefix']) && $config['prefix'])
        {
            $this->set_prefix($config['prefix']);
        }

        if (!$this->host)
        {
            throw new Exception(__('The storage swift config does not exist'));
        }

        $this->config = $config;

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
        //TODO 暂不支持删除Swift存储全部对象

        return false;
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
        # 获取连接对象
        $fp = $this->connection();

        # 获取连接HASH
        $connection_hash = $this->get_connection_hash();

        # 计数统计
        Storage_Driver_Swift::$requests_num[$connection_hash]++;

        $metadata = stream_get_meta_data($fp);

        if ((Storage_Driver_Swift::$requests_num[$connection_hash] % 100)===0 || true===$metadata['timed_out'])
        {
            unset($fp);

            $fp = $this->connection(true);
        }
        unset($metadata);


        $method = strtoupper($method);

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

        $uri = $path . '/'. $this->warehouses . '/' . ($this->prefix?$this->prefix.'/':'') . $uri;
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
                    fwrite($fp, $len . "\r\n" . $data . "\r\n");
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

        $connection_hash = $this->get_connection_hash();

        if (!$re_connect && !isset(Storage_Driver_Swift::$connections[$connection_hash]))
        {
            $fp = $this->fp($this->storage_protocol, $this->storage_host, $this->storage_port, $this->timeout);

            Storage_Driver_Swift::set_connection($connection_hash, $fp);
        }
        else if ($re_connect || (isset(Storage_Driver_Swift::$last_used[$this->storage_host]) && time()-Storage_Driver_Swift::$last_used[$this->storage_host] >= $this->timeout))
        {
            # 超时的连接，销毁后重新连接
            Storage_Driver_Swift::unset_connection($connection_hash);

            return $this->connection($re_connect);
        }

        return Storage_Driver_Swift::$connections[$connection_hash];
    }

    /**
     * 获取连接HASH
     *
     * @param string $is_token_host
     * @return string
     */
    protected function get_connection_hash($is_token_host=false)
    {
        if ($is_token_host)
        {
            return $this->host .':'. $this->port;
        }
        else
        {
            return $this->storage_host .':'. $this->storage_port;
        }
    }

    /**
     * 连接服务器
     *
     * @param string $host
     * @param int $port
     * @param int $timeout
     * @throws Exception
     * @return resource fsockopen returns a file pointer which may be used
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
            $expires           = $token[2];

            if (time()<$expires)
            {
                $need_get = false;

                $h = parse_url($this->storage_url);

                $this->storage_host     = $h['host'];
                $this->storage_path     = $h['path'];
                $this->storage_protocol = $h['scheme'];
                $this->storage_port     = $h['port']?$h['port']:$h['scheme']=='https'?443:80;

                if (IS_DEBUG)Core::debug()->info($this->token, 'Swift Token From Cache');
            }
            else
            {
                $need_get = true;
            }
        }
        else
        {
            $need_get = true;
        }

        if ($need_get)
        {
            if ($this->token_api_version=='v1.0')
            {
                # 获取token
                $this->get_real_token_v1();
            }
            elseif ($this->token_api_version=='v2.0')
            {
                $this->get_real_token_v2();
            }

            # 设置缓存
            Cache::instance()->set($key, array($this->token, $this->storage_url, time()+$this->token_timeout), $this->token_timeout);
        }
    }

    /**
     * 获取v1的swift的token
     */
    protected function get_real_token_v1()
    {
        $headers = array
        (
            'Host'           => $this->host,
            'X-Auth-User'    => $this->account,
            'X-Auth-Key'     => $this->key,
            'Content-Length' => 0,
        );

        if (IS_DEBUG)Core::debug()->info($this->protocol .'://'. $this->account . '@' . $this->host . ($this->port!=80 && $this->port!=443?':'.$this->port:'') . '/' . $this->token_api_version, 'Swift get token url');

        $fp = $this->fp($this->protocol, $this->host, $this->port, $this->timeout);

        $message = $this->build_request_line('GET', '/'. $this->token_api_version) . $this->build_headers($headers);

        fwrite($fp, $message);

        $rs = $this->read($fp);

        try
        {
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


                if ($this->storage_url)
                {
                    $conection_hash1 = $this->get_connection_hash();
                    $conection_hash2 = $this->get_connection_hash(true);

                    if ($conection_hash1==$conection_hash2)
                    {
                        # 将连接加到$connections里复用
                        Storage_Driver_Swift::set_connection($conection_hash1, $fp);
                    }
                    else
                    {
                        # 不是同一个服务器，关闭token服务器连接
                        fclose($fp);
                    }
                }
            }
            else
            {
                throw new Exception(__('Swift get token error.not found X-Storage-Url'));
            }

            if (IS_DEBUG)Core::debug()->info($this->token, 'Swift Token');
        }
        catch (Exception $e)
        {
            fclose($fp);

            throw $e;
        }
    }

    /**
     * 获取v2的swift的token
     */
    protected function get_real_token_v2()
    {
        if (IS_DEBUG)Core::debug()->info($this->protocol .'://'. $this->account . '@' . $this->host . ($this->port!=80&&$this->port!=443?':'.$this->port:'') . '/' . $this->token_api_version .'/tokens', 'Swift get token url');

        $fp = $this->fp($this->protocol, $this->host, $this->port, $this->timeout);

        $body = json_encode(array
        (
            "auth" => array
            (
                "passwordCredentials" => array
                (
                    'username' => $this->account,
                    'password' => $this->key,
                ),
                "tenantName" => $this->tenant_name,
            ),
        ));

        $headers = array
        (
            'Host'           => $this->host,
            'Content-Length' => strlen($body),
            'Content-Type'   => 'application/json',
            'Accept'         => 'application/json',
        );

        $message = $this->build_request_line('POST', '/'.$this->token_api_version .'/tokens') . $this->build_headers($headers) . $body;

        fwrite($fp, $message);

        $rs = $this->read($fp);

        try
        {
            if ($rs['code']<200 || $rs['code']>=300)
            {
                throw new Exception(__('Swift get token error. Code: :code.', array(':code'=>$rs['code'])));
            }

            $body = @json_decode($rs['body'], true);

            if (!$body || !is_array($body))
            {
                throw new Exception(__('Swift get token error. error body content.'));
            }

            # 获取token
            if (isset($body['access']['token']))
            {
                $this->token = $body['access']['token']['id'];
                $expires     = strtotime($body['access']['token']['expires']);
                if ($expires)
                {
                    $expires = $expires - 20;
                    $this->token_timeout = $expires - time();
                }
            }
            else
            {
                throw new Exception(__('Swift get token error.not found X-Auth-Token'));
            }

            if (IS_DEBUG)Core::debug()->info($this->token, 'Swift Token');

            foreach ($body['access']['serviceCatalog'] as $item)
            {
                if ($item['type'] == 'object-store')
                {
                    foreach ($item['endpoints'] as $item2)
                    {
                        if (!$this->region || $item2['region']==$this->region)
                        {
                            $this->storage_url = $item2['internalURL'];

                            $h = parse_url($this->storage_url);

                            $this->storage_host     = $h['host'];
                            $this->storage_path     = $h['path'];
                            $this->storage_protocol = $h['scheme'];
                            $this->storage_port     = $h['port']?$h['port']:$h['scheme']=='https'?443:80;

                            break 2;
                        }
                    }
                }
            }

            if ($this->storage_url)
            {
                $conection_hash1 = $this->get_connection_hash();
                $conection_hash2 = $this->get_connection_hash(true);
                if ($conection_hash1==$conection_hash2)
                {
                    # 将连接加到$connections里复用
                    Storage_Driver_Swift::set_connection($conection_hash1, $fp);
                }
                else
                {
                    # 不是同一个服务器，关闭token服务器连接
                    fclose($fp);
                }
            }
        }
        catch (Exception $e)
        {
            fclose($fp);

            throw $e;
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
                $query_str = '';
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

        $request_line = $method .' '. $uri .' '. $this->protocol_version . "\r\n";

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
        Storage_Driver_Swift::$connections  = array();
        Storage_Driver_Swift::$last_used    = array();
        Storage_Driver_Swift::$requests_num = array();

        if (IS_DEBUG)Core::debug()->info('close all swift storage server.');
    }


    /**
     * 设置可服用的连接
     *
     * @param string $hash 连接hash
     * @param $fp resource fsockopen returns a file pointer which may be used
     */
    protected static function set_connection($hash, $fp)
    {

        if (isset(Storage_Driver_Swift::$connections[$hash]))
        {
            unset(Storage_Driver_Swift::$connections[$hash]);
        }

        Storage_Driver_Swift::$connections[$hash]  = $fp;
        Storage_Driver_Swift::$last_used[$hash]    = time();
        Storage_Driver_Swift::$requests_num[$hash] = 0;
    }

    /**
     * 移除连接
     *
     * @param string $hash 连接hash
     * @param $fp resource fsockopen returns a file pointer which may be used
     */
    protected static function unset_connection($hash)
    {
        if (isset(Storage_Driver_Swift::$connections[$hash]))
        {
            @fclose(Storage_Driver_Swift::$connections[$hash]);
            unset(Storage_Driver_Swift::$connections[$hash]);
        }

        unset(Storage_Driver_Swift::$last_used[$hash]);
        unset(Storage_Driver_Swift::$requests_num[$hash]);
    }
}