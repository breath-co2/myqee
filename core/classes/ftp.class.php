<?php

/**
 * FTP核心类
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2016 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Core_Ftp
{
    /**
     * 当前FTP host
     *
     * @var string
     */
    protected $hostname = '';

    /**
     * 连接FTP用户名
     *
     * @var string
     */
    protected $username = '';

    /**
     * 连接FTP密码
     *
     * @var string
     */
    protected $password = '';

    /**
     * FTP根目录
     *
     * @var string
     */
    protected $path = '';

    /**
     * FTP端口
     *
     * @var int
     */
    protected $port = 21;

    /**
     * 是否被动模式
     *
     * @var boolean
     */
    protected $passive = true;

    protected $_conn_id = false;

    /**
     * @param string $ftp_dsn ftp://user:pass@localhost/
     */
    public function __construct($ftp_dsn)
    {
        $ftp = Ftp::parse_dsn($ftp_dsn);

        foreach($ftp as $k=>$v)
        {
            $this->$k = $v;
        }
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * 获取一个实例化后的FTP的对象
     *
     * @param string $ftp_dsn ftp://user:pass@localhost/
     * @return FTP
     */
    public static function factory($ftp_dsn)
    {
        return new Ftp($ftp_dsn);
    }

    /**
     * 解析FTP DSN
     *
     * @param  string $dsn DSN string
     * @return array
     */
    protected static function parse_dsn($dsn)
    {
        $ftp = array
        (
            'username'   => false,
            'password'   => false,
            'hostname'   => false,
            'port'       => false,
            'passive'    => false,
            'path'       => false,
        );

        // Get the protocol and arguments
        list ($type, $connection) = explode('://', $dsn, 2);

        if ($connection[0] === '/')
        {
            // Strip leading slash
            $ftp['dir'] = substr($connection, 1);
        }
        else
        {
            $connection = parse_url('http://' . $connection);

            if (isset($connection['user']))
            {
                $ftp['username'] = $connection['user'];
            }

            if (isset($connection['pass']))
            {
                $ftp['password'] = $connection['pass'];
            }

            if (isset($connection['port']))
            {
                $ftp['port'] = $connection['port'];
            }

            if (isset($connection['host']))
            {
                if ($connection['host'] === 'unix(')
                {
                    list ($ftp['passive'], $connection['path']) = explode(')', $connection['path'], 2);
                }
                else
                {
                    $ftp['hostname'] = $connection['host'];
                }
            }

            if (isset($connection['path']) && $connection['path'])
            {
                $ftp['path'] = substr($connection['path'], 1);
            }
        }

        return $ftp;
    }

    /**
     * 连接FTP服务器
     *
     * @return bool
     */
    protected function connect()
    {

        if (false === ($this->_conn_id = @ftp_connect($this->hostname, $this->port)))
        {
            if (IS_DEBUG)
            {
                Core::debug()->error(__('ftp unable to connect'));
            }
            return false;
        }

        if (!$this->_login())
        {
            if (IS_DEBUG)
            {
                Core::debug()->error(__('ftp unable to login'));
            }
            return false;
        }

        // 设置被动模式
        if (true === $this->passive)
        {
            ftp_pasv($this->_conn_id, true);
        }

        if ($this->path)
        {
            $this->changedir($this->path);
        }

        return true;
    }

    /**
     * 登录FTP
     *
     * @return bool
     */
    protected function _login()
    {
        return @ftp_login($this->_conn_id, $this->username, $this->password);
    }


    /**
     * 验证是否登录
     *
     * @return bool
     */
    protected function _is_conn()
    {
        if (!is_resource($this->_conn_id))
        {
            if (IS_DEBUG)
            {
                Core::debug()->error(__('ftp no connection'));
            }
            return false;
        }
        return true;
    }


    /**
     * 改变目录
     *
     * 可以用来测试文件夹的存在，相当于FTP上的is_dir()
     *
     * @param  string
     * @param  bool
     * @return bool
     */
    public function changedir($path = '')
    {
        if ($path === '' || !$this->_is_conn())
        {
            return false;
        }

        $result = @ftp_chdir($this->_conn_id, $path);

        if (false===$result)
        {
            if (IS_DEBUG)
            {
                Core::debug()->error(__('ftp unable to changedir'));
            }
            return false;
        }

        return true;
    }


    /**
     * 创建一个目录
     *
     * @param  string
     * @param  int
     * @return bool
     */
    public function mkdir($path = '', $permissions = null)
    {
        if ($path === '' || !$this->_is_conn())
        {
            return false;
        }

        $result = @ftp_mkdir($this->_conn_id, $path);

        if ($result === false)
        {
            if (IS_DEBUG)
            {
                Core::debug()->error(__('ftp unable to makdir'));
            }
            return false;
        }

        // 设置目录权限
        if (!is_null($permissions))
        {
            $this->chmod($path, (int)$permissions);
        }

        return true;
    }


    /**
     * 上传文件
     *
     * @param string
     * @param string
     * @param string
     * @param int
     * @return bool
     */
    public function upload($locpath, $rempath, $mode = 'auto', $permissions = null)
    {
        if (!$this->_is_conn())
        {
            return false;
        }

        if (!file_exists($locpath))
        {
            if (IS_DEBUG)
            {
                Core::debug()->error(__('ftp no source file'));
            }
            return false;
        }

        // 设置权限
        if ($mode === 'auto')
        {
            // 获取上传文件的类型
            $ext  = $this->_getext($locpath);
            $mode = $this->_settype($ext);
        }

        $mode = ($mode === 'ascii') ? FTP_ASCII : FTP_BINARY;

        $result = @ftp_put($this->_conn_id, $rempath, $locpath, $mode);

        if (false===$result)
        {
            if (IS_DEBUG)
            {
                Core::debug()->error(__('ftp unable to upload'));
            }
            return false;
        }

        // Set file permissions if needed
        if (!is_null($permissions))
        {
            $this->chmod($rempath, (int)$permissions);
        }

        return true;
    }


    /**
     * 从FTP上下载一个文件
     *
     * @param string
     * @param string
     * @param string
     * @return bool
     */
    public function download($rempath, $locpath, $mode = 'auto')
    {
        if (!$this->_is_conn())
        {
            return false;
        }

        // 设置权限
        if ($mode === 'auto')
        {
            // 获取类型
            $ext  = $this->_getext($rempath);
            $mode = $this->_settype($ext);
        }

        $mode = ($mode === 'ascii') ? FTP_ASCII : FTP_BINARY;

        $result = @ftp_get($this->_conn_id, $locpath, $rempath, $mode);

        if ($result === false)
        {
            if (IS_DEBUG)
            {
                Core::debug()->error(__('ftp unable to download'));
            }
            return false;
        }

        return true;
    }


    /**
     * 重命名或移动一个文件
     *
     * @param string
     * @param string
     * @param bool
     * @return bool
     */
    public function rename($old_file, $new_file, $move = false)
    {
        if (!$this->_is_conn())
        {
            return false;
        }

        $result = @ftp_rename($this->_conn_id, $old_file, $new_file);

        if (false===$result)
        {
            if (IS_DEBUG)
            {
                Core::debug()->error(__('ftp unable to ' . ($move === false ? 'rename' : 'move')));
            }
            return false;
        }

        return true;
    }


    /**
     * 移动一个文件
     *
     * @param string
     * @param string
     * @return bool
     */
    public function move($old_file, $new_file)
    {
        return $this->rename($old_file, $new_file, true);
    }


    /**
     * 删除一个文件
     *
     * @param string
     * @return bool
     */
    public function delete_file($filepath)
    {
        if (!$this->_is_conn())
        {
            return false;
        }

        $result = @ftp_delete($this->_conn_id, $filepath);

        if (false===$result)
        {
            if (IS_DEBUG)
            {
                Core::debug()->error(__('ftp unable to delete'));
            }
            return false;
        }

        return true;
    }


    /**
     * 删除一个目录（包括子目录）
     *
     * @param string
     * @return bool
     */
    public function delete_dir($filepath)
    {
        if (!$this->_is_conn())
        {
            return false;
        }

        $filepath = preg_replace('/(.+?)\/*$/', '\\1/',  $filepath);

        $list = $this->list_files($filepath);

        if (false!==$list && count($list) > 0)
        {
            foreach ($list as $item)
            {
                if (!@ftp_delete($this->_conn_id, $item))
                {
                    $this->delete_dir($item);
                }
            }
        }

        $result = @ftp_rmdir($this->_conn_id, $filepath);

        if (false===$result)
        {
            if (IS_DEBUG)
            {
                Core::debug()->error(__('ftp unable to delete'));
            }
            return false;
        }

        return true;
    }


    /**
     * 设置权限
     *
     * @param string $path   文件路径
     * @param string $perm   权限，比如 0755
     * @return bool
     */
    public function chmod($path, $perm)
    {
        if (!$this->_is_conn())
        {
            return false;
        }

        $result = @ftp_chmod($this->_conn_id, $perm, $path);

        if (false===$result)
        {
            if (IS_DEBUG)
            {
                Core::debug()->error(__('ftp unable to chmod'));
            }
            return false;
        }

        return true;
    }


    /**
     * 列出FTP上文件
     *
     * @return array
     */
    public function list_files($path = '.')
    {
        if (!$this->_is_conn())
        {
            return false;
        }

        return ftp_nlist($this->_conn_id, $path);
    }


    /**
     * 将本地路径目录文件上传同步到FTP指定目录
     *
     * @param string $locpath 本地完整路径
     * @param string $rempath 远程路径
     * @return bool
     */
    public function mirror($locpath, $rempath)
    {
        if (!$this->_is_conn())
        {
            return false;
        }

        if (true==($fp = @opendir($locpath)))
        {
            if (!$this->changedir($rempath, true) && (!$this->mkdir($rempath) || !$this->changedir($rempath)))
            {
                return false;
            }

            while (false !== ($file = readdir($fp)))
            {
                if (@is_dir($locpath.$file) && $file[0] !== '.')
                {
                    $this->mirror($locpath.$file.'/', $rempath.$file.'/');
                }
                elseif ($file[0] !== '.')
                {
                    $ext  = $this->_getext($file);
                    $mode = $this->_settype($ext);

                    $this->upload($locpath.$file, $rempath.$file, $mode);
                }
            }
            return true;
        }

        return false;
    }

    /**
     * 获取后缀
     *
     * @param string $filename 文件名
     * @return string
     */
    protected function _getext($filename)
    {
        if (false===strpos($filename, '.'))
        {
            return 'txt';
        }

        $x = explode('.', $filename);
        return end($x);
    }

    /**
     * 设置类型
     *
     * @param string $ext
     * @return string
     */
    protected function _settype($ext)
    {
        $text_types = array
        (
            'txt',
            'text',
            'php',
            'phps',
            'php4',
            'js',
            'css',
            'htm',
            'html',
            'phtml',
            'shtml',
            'log',
            'xml',
        );

        return in_array($ext, $text_types) ? 'ascii' : 'binary';
    }

    /**
     * 关闭连接
     *
     * @return bool
     */
    public function close()
    {
        if (!$this->_is_conn())
        {
            return false;
        }

        return @ftp_close($this->_conn_id);
    }
}