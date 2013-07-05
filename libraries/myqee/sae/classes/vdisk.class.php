<?php
/**
 * vDisk sdk for SAE and Standard PHP
 *
 * @version $Id$
 * @package sae
 * @author Bruce Chen
 */


/**
 * @ignore
 */
define('URL_GET_TOKEN', 'http://openapi.vdisk.me/?m=auth&a=get_token');
/**
 * @ignore
 */
define('URL_KEEP_TOKEN', 'http://openapi.vdisk.me/?m=user&a=keep_token');
/**
 * @ignore
 */
define('URL_UPLOAD_FILE', 'http://openapi.vdisk.me/?m=file&a=upload_file');
/**
 * @ignore
 */
define('URL_UPLOAD_SHARE_FILE', 'http://openapi.vdisk.me/?m=file&a=upload_share_file');
/**
 * @ignore
 */
define('URL_CREATE_DIR', 'http://openapi.vdisk.me/?m=dir&a=create_dir');
/**
 * @ignore
 */
define('URL_GET_LIST', 'http://openapi.vdisk.me/?m=dir&a=get_list');
/**
 * @ignore
 */
define('URL_GET_QUOTA', 'http://openapi.vdisk.me/?m=file&a=get_quota');
/**
 * @ignore
 */
define('URL_UPLOAD_WITH_SHA1', 'http://openapi.vdisk.me/?m=file&a=upload_with_sha1');
/**
 * @ignore
 */
define('URL_GET_FILE_INFO', 'http://openapi.vdisk.me/?m=file&a=get_file_info');
/**
 * @ignore
 */
define('URL_DELETE_DIR', 'http://openapi.vdisk.me/?m=dir&a=delete_dir');
/**
 * @ignore
 */
define('URL_DELETE_FILE', 'http://openapi.vdisk.me/?m=file&a=delete_file');
/**
 * @ignore
 */
define('URL_COPY_FILE', 'http://openapi.vdisk.me/?m=file&a=copy_file');
/**
 * @ignore
 */
define('URL_MOVE_FILE', 'http://openapi.vdisk.me/?m=file&a=move_file');
/**
 * @ignore
 */
define('URL_RENAME_FILE', 'http://openapi.vdisk.me/?m=file&a=rename_file');
/**
 * @ignore
 */
define('URL_RENAME_DIR', 'http://openapi.vdisk.me/?m=dir&a=rename_dir');
/**
 * @ignore
 */
define('URL_MOVE_DIR', 'http://openapi.vdisk.me/?m=dir&a=move_dir');
/**
 * @ignore
 */
define('URL_SHARE_FILE', 'http://openapi.vdisk.me/?m=file&a=share_file');
/**
 * @ignore
 */
define('URL_CANCEL_SHARE_FILE', 'http://openapi.vdisk.me/?m=file&a=cancel_share_file');
/**
 * @ignore
 */
define('URL_RECYCLE_GET_LIST', 'http://openapi.vdisk.me/?m=recycle&a=get_list');
/**
 * @ignore
 */
define('URL_TRUNCATE_RECYCLE_GET', 'http://openapi.vdisk.me/?m=recycle&a=truncate_recycle');
/**
 * @ignore
 */
define('URL_RECYCLE_DELETE_FILE', 'http://openapi.vdisk.me/?m=recycle&a=delete_file');
/**
 * @ignore
 */
define('URL_RECYCLE_DELETE_DIR', 'http://openapi.vdisk.me/?m=recycle&a=delete_dir');
/**
 * @ignore
 */
define('URL_RECYCLE_RESTORE_FILE', 'http://openapi.vdisk.me/?m=recycle&a=restore_file');
/**
 * @ignore
 */
define('URL_RECYCLE_RESTORE_DIR', 'http://openapi.vdisk.me/?m=recycle&a=restore_dir');
/**
 * @ignore
 */
define('URL_GET_DIRID_WITH_PATH', 'http://openapi.vdisk.me/?m=dir&a=get_dirid_with_path');
/**
 * @ignore
 */
define('URL_EMAIL_SHARE_FILE', 'http://openapi.vdisk.me/?m=file&a=email_share_file');



/**
 * 微盘API
 *
 * 微盘Open API是为第三方开发者提供的一套REST接口,通过该接口任何第三方的公司和个人都可以创建基于微盘的应用.更多{@link http://vdisk.me/api 相关说明} .
 *
 * 使用方法:
 * <code>
 * include_once('vdisk.ex.class.php');
 * $appkey = 1234567;
 * $appsecret = '123456739cc20556637a576ea1234567';
 * $username = 'username@gmail.com';
 * $password = '123456';
 *
 * $vdisk = new vDisk($appkey, $appsecret);
 *
 * $vdisk->get_token($username, $password);
 * $_SESSION['token'] = $vdisk->token;
 *
 * $vdisk->keep_token();
 *
 * $r = $vdisk->upload_share_file('文件.txt', 0);
 * $r = $vdisk->get_list(0);
 * $r = $vdisk->get_quota();
 * $r = $vdisk->upload_with_md5('测试.pdf', '03d5717869bb075e3bad73b527fabc8a');
 * $r = $vdisk->get_file_info(219379);
 * $r = $vdisk->create_dir('测试一下');
 * $r = $vdisk->delete_dir(35647);
 * $r = $vdisk->delete_file(123);
 * $r = $vdisk->copy_file(219379, 0, '副本.txt');
 * $r = $vdisk->move_file(219379, 0, '副本.txt');
 * $r = $vdisk->rename_file(219379, '新的新的新的.z');
 * $r = $vdisk->rename_dir(3929, '新的新的新的');
 * $r = $vdisk->move_dir(3929, "我的图片们", 0);
 * print_r($r);
 * </code>
 *
 * @link http://vdisk.me/api
 * @package sae
 * @version $Id$
 * @author Bruce Chen
 */
class vDisk
{

    /**
     * @ignore
     */
    public $appkey;
    /**
     * @ignore
     */
    public $appsecret;
    /**
     * @ignore
     */
    public $username;
    /**
     * @ignore
     */
    public $password;
    /**
     * @ignore
     */
    public $token;

    private $_errno;
    private $_error;


    /**
     * 构造函数
     *
     * @param string $app_key 分配给你的appkey
     * @param string $app_secret 分配给你的appsecret
     *
     * @return void
     *
     * @author Bruce Chen
     *
     */
    public function __construct($app_key, $app_secret)
    {
        if(!($app_key && $app_secret))
        {
            $this->set_error(-2, 'app_key or app_secret empty');
            return;
        }

        $this->appkey = $app_key;
        $this->appsecret = $app_secret;
        $this->set_error(-1, 'empty');
    }


    /**
     * 获得token
     *
     * @param string $username
     * @param string $password
     * @param string $app_type 可选参数, 如:$app_type=sinat (注意: 目前支持微博帐号)
     *
     * @return array
     *
     * @author Bruce Chen
     *
     */
    public function get_token($username, $password, $app_type=null)
    {

        $this->username = $username;
        $this->password = $password;

        $time = time();
        $param = array(

                'account' => $username,
                'password' => $password,
                'time' => $time,
                'appkey' => $this->appkey,
                'signature' => hash_hmac('sha256', "account={$username}&appkey={$this->appkey}&password={$password}&time={$time}", $this->appsecret, false)

                );

        if($app_type) $param['app_type'] = $app_type;

        $data = $this->_request(URL_GET_TOKEN, $param);

        if($data && $data['err_code'] == 0)
        {
            $this->token = $data['data']['token'];
        }

        return $data;
    }


    /**
     * 保持token
     *
     * @param string $token 可选参数
     *
     * @return array
     *
     * @author Bruce Chen
     *
     */
    public function keep_token($token=null)
    {
        if($token)
        {
            $this->token = $token;
        }

        if($this->token)
        {
            $param = array(

                    'token' => $this->token
                    );

            $data = $this->_request(URL_KEEP_TOKEN, $param);

            return $data;
        }
        else
        {
            return false;
        }
    }


    /**
     * 上传文件(10M以下)
     *
     * @param string $file_path 本地文件真实路径
     *
     * @param int $dir_id 目录id
     *
     * @param string $cover 可选参数, yes:覆盖; no:如有重名返回错误信息
     *
     * @return array
     *
     * @author Bruce Chen
     *
     */
    public function upload_file($file_path, $dir_id, $cover='yes')
    {

        if($this->token)
        {
            $param = array(

                    'file' => '@'.$file_path,
                    'token' => $this->token,
                    'dir_id' => $dir_id,
                    'cover' => $cover
                    );

            $data = $this->_request(URL_UPLOAD_FILE, $param);

            return $data;
        }
        else
        {
            return false;
        }

    }


    /**
     * 上传并分享文件(10M以下)
     *
     * @param string $file_path 本地文件真实路径
     *
     * @param int $dir_id 目录id
     *
     * @param string $cover 可选参数, yes:覆盖; no:如有重名返回错误信息
     *
     * @return array 包含分享后的url
     *
     * @author Bruce Chen
     *
     */
    public function upload_share_file($file_path, $dir_id, $cover='yes')
    {
        if($this->token)
        {
            $param = array(

                    'file' => '@'.$file_path,
                    'token' => $this->token,
                    'dir_id' => $dir_id,
                    'cover' => $cover
                    );

            $data = $this->_request(URL_UPLOAD_SHARE_FILE, $param);

            return $data;
        }
        else
        {
            return false;
        }
    }


    /**
     * 创建目录
     *
     * @param string $create_name 目录的名称
     *
     * @param int $parent_id 父目录的id
     *
     * @return array
     *
     * @author Bruce Chen
     *
     */
    public function create_dir($create_name, $parent_id=0)
    {
        if($this->token)
        {
            $param = array(

                    'token' => $this->token,
                    'create_name' => $create_name,
                    'parent_id' => $parent_id
                    );

            $data = $this->_request(URL_CREATE_DIR, $param);

            return $data;
        }
        else
        {
            return false;
        }

    }


    /**
     * 获得列表(包括文件和子目录)
     *
     * @param int $dir_id 目录的id
     *
     * @return array
     *
     * @author Bruce Chen
     *
     */
    public function get_list($dir_id)
    {
        if($this->token)
        {
            $param = array(

                    'token' => $this->token,
                    'dir_id' => $dir_id
                    );

            $data = $this->_request(URL_GET_LIST, $param);

            return $data;
        }
        else
        {
            return false;
        }
    }


    /**
     * 获得容量信息
     *
     * @return array
     *
     * @author Bruce Chen
     *
     */
    public function get_quota()
    {
        if($this->token)
        {
            $param = array(

                    'token' => $this->token
                    );

            $data = $this->_request(URL_GET_QUOTA, $param);

            return $data;
        }
        else
        {
            return false;
        }
    }


    /**
     * 无文件上传(md5)
     *
     * @param string $file_name 上传以后的文件名
     * @param string $md5 要上传文件的md5值
     * @param int $dir_id 目标目录的id, 0为根目录
     *
     * @return array
     *
     * @author Bruce Chen
     *
     */
    public function upload_with_sha1($file_name, $sha1, $dir_id=0)
    {
        if($this->token)
        {
            $param = array(

                    'token' => $this->token,
                    'file_name' => $file_name,
                    'sha1' => $sha1,
                    'dir_id' => $dir_id
                    );

            $data = $this->_request(URL_UPLOAD_WITH_SHA1, $param);

            return $data;
        }
        else
        {
            return false;
        }
    }


    /**
     * 获得文件的信息
     *
     * @param int $fid 文件的id
     *
     * @return array
     *
     * @author Bruce Chen
     *
     */
    public function get_file_info($fid)
    {
        if($this->token)
        {
            $param = array(

                    'token' => $this->token,
                    'fid' => $fid,
                    );

            $data = $this->_request(URL_GET_FILE_INFO, $param);

            return $data;
        }
        else
        {
            return false;
        }
    }


    /**
     * 删除目录
     *
     * @param int $dir_id 目录的id
     *
     * @return array
     *
     * @author Bruce Chen
     *
     */
    public function delete_dir($dir_id)
    {
        if($this->token)
        {
            $param = array(

                    'token' => $this->token,
                    'dir_id' => $dir_id,
                    );

            $data = $this->_request(URL_DELETE_DIR, $param);

            return $data;
        }
        else
        {
            return false;
        }
    }


    /**
     * 删除文件
     *
     * @param int $fid 文件的id
     *
     * @return array
     *
     * @author Bruce Chen
     *
     */
    public function delete_file($fid)
    {
        if($this->token)
        {
            $param = array(

                    'token' => $this->token,
                    'fid' => $fid,
                    );

            $data = $this->_request(URL_DELETE_FILE, $param);

            return $data;
        }
        else
        {
            return false;
        }
    }


    /**
     * 复制文件
     *
     * @param int $fid 要复制文件的id
     * @param int $to_dir_id 目标目录的id
     * @param string $new_name 副本文件的名称
     *
     * @return array
     *
     * @author Bruce Chen
     *
     */
    public function copy_file($fid, $to_dir_id, $new_name)
    {
        if($this->token)
        {
            $param = array(

                    'token' => $this->token,
                    'fid' => $fid,
                    'to_dir_id' => $to_dir_id,
                    'new_name' => $new_name
                    );

            $data = $this->_request(URL_COPY_FILE, $param);

            return $data;
        }
        else
        {
            return false;
        }
    }


    /**
     * 移动文件
     *
     * @param int $fid 要移动文件的id
     * @param int $to_dir_id 目标目录的id
     * @param string $new_name 移动后的文件名称
     *
     * @return array
     *
     * @author Bruce Chen
     *
     */
    public function move_file($fid, $to_dir_id, $new_name)
    {
        if($this->token)
        {
            $param = array(

                    'token' => $this->token,
                    'fid' => $fid,
                    'to_dir_id' => $to_dir_id,
                    'new_name' => $new_name
                    );

            $data = $this->_request(URL_MOVE_FILE, $param);

            return $data;
        }
        else
        {
            return false;
        }
    }


    /**
     * 重命名文件
     *
     * @param int $fid 文件的id
     * @param string $new_name 新文件名称
     *
     * @return array
     *
     * @author Bruce Chen
     *
     */
    public function rename_file($fid, $new_name)
    {
        if($this->token)
        {
            $param = array(

                    'token' => $this->token,
                    'fid' => $fid,
                    'new_name' => $new_name
                    );

            $data = $this->_request(URL_RENAME_FILE, $param);

            return $data;
        }
        else
        {
            return false;
        }
    }


    /**
     * 重命名目录
     *
     * @param int $dir_id 目录的id
     * @param string $new_name 新名称
     *
     * @return array
     *
     * @author Bruce Chen
     *
     */
    public function rename_dir($dir_id, $new_name)
    {
        if($this->token)
        {
            $param = array(

                    'token' => $this->token,
                    'dir_id' => $dir_id,
                    'new_name' => $new_name
                    );

            $data = $this->_request(URL_RENAME_DIR, $param);

            return $data;
        }
        else
        {
            return false;
        }
    }


    /**
     * 移动目录
     *
     * @param int $dir_id 目录的id
     * @param string $new_name 移动后的名称
     * @param int $to_parent_id 目标目录的id
     *
     * @return array
     *
     * @author Bruce Chen
     *
     */
    public function move_dir($dir_id, $new_name, $to_parent_id)
    {
        if($this->token)
        {
            $param = array(

                    'token' => $this->token,
                    'dir_id' => $dir_id,
                    'new_name' => $new_name,
                    'to_parent_id' => $to_parent_id
                    );

            $data = $this->_request(URL_MOVE_DIR, $param);

            return $data;
        }
        else
        {
            return false;
        }
    }


    /**
     * 分享文件
     *
     * @param int $fid 文件的id
     *
     * @return array
     *
     * @author Bruce Chen
     *
     */
    public function share_file($fid)
    {
        if($this->token)
        {
            $param = array(

                    'token' => $this->token,
                    'fid' => $fid
                    );

            $data = $this->_request(URL_SHARE_FILE, $param);

            return $data;
        }
        else
        {
            return false;
        }
    }


    /**
     * 取消分享
     *
     * @param int $fid 文件的id
     *
     * @return array
     *
     * @author Bruce Chen
     *
     */
    public function cancel_share_file($fid)
    {
        if($this->token)
        {
            $param = array(

                    'token' => $this->token,
                    'fid' => $fid
                    );

            $data = $this->_request(URL_CANCEL_SHARE_FILE, $param);

            return $data;
        }
        else
        {
            return false;
        }
    }


    /**
     * 获得回收站列表
     *
     * @param int $page 第几页
     * @param int $page_size 每页显示条数
     *
     * @return array
     *
     * @author Bruce Chen
     *
     */
    public function recycle_get_list($page=1, $page_size=25)
    {
        if($this->token)
        {
            $param = array(

                    'token' => $this->token,
                    'page' => $page,
                    'page_size' => $page_size
                    );

            $data = $this->_request(URL_RECYCLE_GET_LIST, $param);

            return $data;
        }
        else
        {
            return false;
        }
    }


    /**
     * 清空回收站
     *
     * @return array
     *
     * @author Bruce Chen
     *
     */
    public function truncate_recycle()
    {
        if($this->token)
        {
            $param = array(

                    'token' => $this->token
                    );

            $data = $this->_request(URL_TRUNCATE_RECYCLE_GET, $param);

            return $data;
        }
        else
        {
            return false;
        }
    }

    /**
     * 从回收站中彻底删除一个文件
     *
     * @param int $fid 文件id
     *
     * @return array
     *
     * @author Bruce Chen
     *
     */
    public function recycle_delete_file($fid)
    {
        if($this->token)
        {
            $param = array(

                    'token' => $this->token,
                    'fid' => $fid
                    );

            $data = $this->_request(URL_RECYCLE_DELETE_FILE, $param);

            return $data;
        }
        else
        {
            return false;
        }
    }

    /**
     * 从回收站中彻底删除一个目录
     *
     * @param int $dir_id 目录的id
     *
     * @return array
     *
     * @author Bruce Chen
     *
     */
    public function recycle_delete_dir($dir_id)
    {
        if($this->token)
        {
            $param = array(

                    'token' => $this->token,
                    'dir_id' => $dir_id
                    );

            $data = $this->_request(URL_RECYCLE_DELETE_DIR, $param);

            return $data;
        }
        else
        {
            return false;
        }
    }



    /**
     * 从回收站中还原一个文件
     *
     * @param int $fid 文件id
     *
     * @return array
     *
     * @author Bruce Chen
     *
     */
    public function restore_file($fid)
    {
        if($this->token)
        {
            $param = array(

                    'token' => $this->token,
                    'fid' => $fid
                    );

            $data = $this->_request(URL_RECYCLE_RESTORE_FILE, $param);

            return $data;
        }
        else
        {
            return false;
        }
    }


    /**
     * 从回收站中还原一个目录
     *
     * @param int $dir_id 目录的id
     *
     * @return array
     *
     * @author Bruce Chen
     *
     */
    public function restore_dir($dir_id)
    {
        if($this->token)
        {
            $param = array(

                    'token' => $this->token,
                    'dir_id' => $dir_id
                    );

            $data = $this->_request(URL_RECYCLE_RESTORE_DIR, $param);

            return $data;
        }
        else
        {
            return false;
        }
    }

    /**
     * 通过路径得到目录
     *
     * @param string $path 路径
     *
     * @return array
     *
     * @author Bruce Chen
     *
     */
    public function get_dirid_with_path($path)
    {
        if($this->token)
        {
            $param = array(

                    'token' => $this->token,
                    'path' => $path
                    );

            $data = $this->_request(URL_GET_DIRID_WITH_PATH, $param);

            return $data;
        }
        else
        {
            return false;
        }
    }


    /**
     * 通过邮件发送文件链接
     *
     * @param int $fid
     * @param string $to_email
     *
     * @return array
     *
     * @author Bruce Chen
     *
     */
    public function email_share_file($fid, $to_email)
    {
        if($this->token)
        {
            $param = array(

                    'token' => $this->token,
                    'fid' => $fid,
                    'to_email' => $to_email
                    );

            $data = $this->_request(URL_EMAIL_SHARE_FILE, $param);

            return $data;
        }
        else
        {
            return false;
        }
    }


    /**
     * 发送http请求, 兼容SEA的Fetchurl, 私有方法
     *
     * @param string $url
     * @param array $array  POST DATA ($array=null为GET方式)
     *
     * @return array
     *
     * @author Bruce Chen
     *
     */
    private function _request($url, $array=null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        if($array != null)
        {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $array);
        }
        $data = curl_exec($curl);
        curl_close($curl);
        if($arr = json_decode($data, true))
        {
            $this->set_error($arr['err_code'], $arr['err_msg']);
            return $arr;
        }
        else
        {
            $this->set_error(-1, 'empty');
            return false;
        }
    }

    /**
     * 返回SaeFetchurl object, 保证只new一次
     *
     * @return SaeFetchurl object
     *
     * @author Bruce Chen
     *
     */
    private function _f()
    {
        if(!isset($this->_f))
            $this->_f = new SaeFetchurl();

        return $this->_f;
    }


    private function set_error($errno, $error)
    {
        $this->_errno = $errno;
        $this->_error = $error;
    }

    public function errno()
    {
        return $this->_errno;
    }

    public function error()
    {
        return $this->_error;
    }
}