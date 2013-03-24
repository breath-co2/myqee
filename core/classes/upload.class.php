<?php

/**
 * 文件上传核心类
 *
 * @author     jonwang(jonwang@myqee.com)
 * @author     marlboro(zxf1984@gmail.com)
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */

class Core_Upload
{
    /**
     * 是否严谨验证mime
     * @var bool
     */
    protected $mimes_check          = false;

    /**
     * @var int 最大上传
     */
    protected $max_size				= 0;

    /**
     * 图片的最大宽度
     * @var int
     */
    protected $max_width            = 0;

    /**
     * 图片的最大宽度
     * @var int
     */
    protected $max_height           = 0;

    /**
     * 文件名的最大长度
     * @var int
     */
    protected $max_filename         = 0;

    /**
     * 允许的上传的文件类型
     * @var string
     */
    protected $allowed_types        = "";

    /**
     * 文件temp
     * @var string
     */
    protected $file_temp            = "";

    /**
     * 上传后的文件名
     * @var string
     */
    protected $file_name            = "";

    /**
     * 文件上传后最初的文件名，只有开启重命名（encrypt_name）时才有效，默认与$file_name相同
     * @var string
     */
    protected $orig_name            = "";

    /**
     * 文件类型 mimes
     * @var string
     */
    protected $file_type            = "";

    /**
     * 文件大小
     * @var string
     */
    protected $file_size            = "";

    /**
     * 文件后缀
     * @var string
     */
    protected $file_ext             = "";

    /**
     * 文件路径
     * @var string
     */
    protected $upload_path          = "";

    /**
     * 遇到相同文件名是否覆盖，默认false
     * @var bool
     */
    protected $overwrite            = false;

    /**
     * 是否重命名，默认否。开启时将经过加密命名处理
     * @var bool
     */
    protected $encrypt_name         = false;

    /**
     * 是否是图片
     * @var bool
     */
    protected $is_image             = false;

    /**
     * 图片宽度
     * @var string
     */
    protected $image_width          = '';

    /**
     * 图片高度
     * @var string
     */
    protected $image_height         = '';

    /**
     * 图片类型 mime
     * @var string
     */
    protected $image_type           = '';

    /**
     * 图片高宽组合信息
     * @var string
     */
    protected $image_size_str       = '';

    /**
     * 错误信息
     * @var array
     */
    protected $error_msg            = array();

    /**
     * mimes
     * @var array
     */
    protected $mimes                = array();

    /**
     * 是否取出空格
     * @var bool
     */
    protected $remove_spaces        = true;
    protected $temp_prefix          = "temp_file_";

    /**
     * 客户端的文件名
     * @var string
     */
    protected $client_name          = '';

    protected $_file_name_override	= '';

    public function __construct($props = array())
    {
        if (count($props) > 0)
        {
            $this->initialize($props);
        }
    }


    public static function factory($props = array())
    {
        return new Upload($props);
    }

    /**
     * 初始化参数
     *
     * @param	array
     * @return	void
     */
    public function initialize($config = array())
    {
        $defaults = array(
            'mimes_check'       => false,
            'max_size'			=> 0,
            'max_width'			=> 0,
            'max_height'		=> 0,
            'max_filename'		=> 0,
            'allowed_types'		=> "",
            'file_temp'			=> "",
            'file_name'			=> "",
            'orig_name'			=> "",
            'file_type'			=> "",
            'file_size'			=> "",
            'file_ext'			=> "",
            'upload_path'		=> "",
            'overwrite'			=> false,
            'encrypt_name'		=> false,
            'is_image'			=> false,
            'image_width'		=> '',
            'image_height'		=> '',
            'image_type'		=> '',
            'image_size_str'	=> '',
            'error_msg'			=> array(),
            'mimes'				=> array(),
            'remove_spaces'		=> true,
            'temp_prefix'		=> "temp_file_",
            'client_name'		=> ''
        );


        foreach ($defaults as $key => $val)
        {
            if (isset($config[$key]))
            {
                $method = 'set_'.$key;
                if (method_exists($this, $method))
                {
                    $this->$method($config[$key]);
                }
                else
                {
                    $this->$key = $config[$key];
                }
            }
            else
            {
                $this->$key = $val;
            }
        }

        $this->_file_name_override = $this->file_name;
    }

    public function do_upload($field = 'userfile')
    {
        if ( ! isset($_FILES[$field]))
        {
            $this->set_error('upload_userfile_not_set');
            return false;
        }

        //验证上传路径
        if ( ! $this->validate_upload_path())
        {
            return false;
        }


        if( ! is_uploaded_file($_FILES[$field]['tmp_name']) )
        {
            $error = (! isset($_FILES[$field]['error'])) ? 4 : $_FILES[$field]['error'];

            switch($error)
            {
                case 1:	// UPLOAD_ERR_INI_SIZE
                    $this->set_error('upload_file_exceeds_limit');
                    break;
                case 2: // UPLOAD_ERR_FORM_SIZE
                    $this->set_error('upload_file_exceeds_form_limit');
                    break;
                case 3: // UPLOAD_ERR_PARTIAL
                    $this->set_error('upload_file_partial');
                    break;
                case 4: // UPLOAD_ERR_NO_FILE
                    $this->set_error('upload_no_file_selected');
                    break;
                case 6: // UPLOAD_ERR_NO_TMP_DIR
                    $this->set_error('upload_no_temp_directory');
                    break;
                case 7: // UPLOAD_ERR_CANT_WRITE
                    $this->set_error('upload_unable_to_write_file');
                    break;
                case 8: // UPLOAD_ERR_EXTENSION
                    $this->set_error('upload_stopped_by_extension');
                    break;
                default :   $this->set_error('upload_no_file_selected');
                break;
            }
            return false;
        }

        //上传文件的缓存名
        $this->file_temp = $_FILES[$field]['tmp_name'];
        //文件size
        $this->file_size = $_FILES[$field]['size'];

        //$this->file_type 如果不是严谨验证($this->mimes_check === false)，则返回$_FILES['filedname']['type']，此值由浏览器提供;
        $this->_file_mime_type($_FILES[$field]);
        $this->file_type = preg_replace("/^(.+?);.*$/", "\\1", $this->file_type);
        $this->file_type = strtolower(trim(stripslashes($this->file_type), '"'));

        //获取原始文件名，如果有多个.分割，做严谨验证
        $this->file_name = $this->_prep_filename($_FILES[$field]['name']);

        //文件后缀
        $this->file_ext	 = $this->get_extension($this->file_name);

        //客户端的原始文件名
        $this->client_name = $this->file_name;

        //是否允许上传
        if ( ! $this->is_allowed_filetype())
        {
            $this->set_error('upload_invalid_filetype');
            return false;
        }

        // 检测新文件名，补全后缀，或者检查后缀是否一致，后缀是否允许
        if ($this->_file_name_override != '')
        {
            $this->file_name = $this->_prep_filename($this->_file_name_override);

            // 如果重命名文件无后缀，则将原文件后缀补上
            if (strpos($this->_file_name_override, '.') === false)
            {
                $this->file_name .= $this->file_ext;
            }
            else
            {
                $this->file_ext	 = $this->get_extension($this->_file_name_override);
            }

            //再次验证后缀是否允许
            if ( ! $this->is_allowed_filetype(true))
            {
                $this->set_error('upload_invalid_filetype');
                return false;
            }
        }

        // 转换size单位为k
        if ($this->file_size > 0)
        {
            $this->file_size = round($this->file_size/1024, 2);
        }

        // 是否在允许大小内
        if ( ! $this->is_allowed_filesize())
        {
            $this->set_error('upload_invalid_filesize');
            return false;
        }

        // 如果是图片，且限制长宽，则验证
        if ( ! $this->is_allowed_dimensions())
        {
            $this->set_error('upload_invalid_dimensions');
            return false;
        }

        // 对文件名做安全过滤
        $this->file_name = $this->clean_file_name($this->file_name);

        // 如果限制文件名长度，做截取
        if ($this->max_filename > 0)
        {
            $this->file_name = $this->limit_filename_length($this->file_name, $this->max_filename);
        }

        // 替换文件名中的空格为下划线_
        if ($this->remove_spaces == true)
        {
            $this->file_name = preg_replace("/\s+/", "_", $this->file_name);
        }

        //最初的文件名，仅仅设置上传文件重命名（encrypt_name）时才有效，否则与$file_name文件名同名
        $this->orig_name = $this->file_name;

        if ($this->overwrite == false)
        {
            $this->file_name = $this->set_filename($this->upload_path, $this->file_name);

            if ($this->file_name === false)
            {
                return false;
            }
        }

        /*
         * 移动文件至指定目录
         * 考虑到多种线上环境，默认使用copy()做移动，如果失败再使用 move_uploaded_file()
         */
        if ( ! @copy($this->file_temp, $this->upload_path.$this->file_name))
        {
            if ( ! @move_uploaded_file($this->file_temp, $this->upload_path.$this->file_name))
            {
                $this->set_error('upload_destination_error');
                return false;
            }
        }

        /*
         * 如果是图片做一系列赋值处理，长 宽 等
         */
        $this->set_image_properties($this->upload_path.$this->file_name);

        return true;

    }


    /**
     * 对文件名做赋值处理
     *
     * @param	string
     * @param	string
     * @return	string
     */
    public function set_filename($path, $filename)
    {

        if ($this->encrypt_name == true)
        {
            mt_srand();
            $filename = md5(uniqid(mt_rand())).$this->file_ext;
        }

        if ( ! file_exists($path.$filename))
        {
            return $filename;
        }

        $filename = str_replace($this->file_ext, '', $filename);

        $new_filename = '';
        for ($i = 1; $i < 100; $i++)
        {
            if ( ! file_exists($path.$filename.$i.$this->file_ext))
            {
                $new_filename = $filename.$i.$this->file_ext;
                break;
            }
        }

        if ($new_filename == '')
        {
            $this->set_error('upload_bad_filename');
            return false;
        }
        else
        {
            return $new_filename;
        }
    }

    /**
     * 赋值mimes_check
     * @param $v
     */
    public function set_mimes_check($v)
    {
        $this->mimes_check = ($v === true)? true:false;
    }


    /**
     * 赋值file_name
     * @param $v
     */
    public function set_file_name($v)
    {
        $this->file_name = $v;
    }



    /**
     * 赋值upload_path
     * @param $v
     */
    public function set_upload_path($v)
    {
        $this->upload_path = rtrim($v, '/').'/';
    }

    /**
     * 赋值encrypt_name
     * @param $v
     */
    public function set_encrypt_name($v)
    {
        $this->encrypt_name = ($v === true)? true:false;
    }


    /**
     * 赋值remove_spaces
     * @param $v
     */
    public function set_remove_spaces($v)
    {
        $this->remove_spaces = ($v === false)? false:true;
    }

    /**
     * 赋值最大文件大小，验证数值类型
     *
     * @param	integer
     * @return	void
     */
    public function set_max_filesize($n)
    {
        $this->max_size = ((int) $n < 0) ? 0: (int) $n;
    }

    /**
     * 赋值最小文件大小，验证数值类型
     *
     * @param	integer
     * @return	void
     */
    public function set_max_filename($n)
    {
        $this->max_filename = ((int) $n < 0) ? 0: (int) $n;
    }

    /**
     * 赋值最大图片宽度，验证数值类型
     *
     * @param	integer
     * @return	void
     */
    public function set_max_width($n)
    {
        $this->max_width = ((int) $n < 0) ? 0: (int) $n;
    }


    /**
     * 赋值最大图片宽度，验证数值类型
     *
     * @param	integer
     * @return	void
     */
    public function set_max_height($n)
    {
        $this->max_height = ((int) $n < 0) ? 0: (int) $n;
    }



    /**
     * 赋值允许的上传文件类型
     *
     * @param	string
     * @return	void
     */
    public function set_allowed_types($types)
    {
        if ( ! is_array($types) && $types == '*')
        {
            $this->allowed_types = '*';
            return;
        }
        $this->allowed_types = explode('|', $types);
    }



    /**
     * 如果是图片做一系列赋值处理
     *
     * @param	string
     * @return	void
     */
    public function set_image_properties($path = '')
    {
        if ( ! $this->is_image())
        {
            return;
        }

        if (function_exists('getimagesize'))
        {
            if (false !== ($D = @getimagesize($path)))
            {
                $types = array(1 => 'gif', 2 => 'jpeg', 3 => 'png');

                $this->image_width		= $D['0'];
                $this->image_height		= $D['1'];
                $this->image_type		= ( ! isset($types[$D['2']])) ? 'unknown' : $types[$D['2']];
                $this->image_size_str	= $D['3'];  // string containing height and width
            }
        }
    }


    /**
     * 验证路径是否存在，目录是否可写，规范路径名
     * @return bool
     */
    public function validate_upload_path()
    {
        if ($this->upload_path == '')
        {
            $this->set_error('upload_no_filepath');
            return false;
        }

        //返回规范化的路径
        if ( function_exists('realpath') && @realpath($this->upload_path) !== false )
        {
            $this->upload_path =  str_replace ('\\','/',realpath($this->upload_path));
        }

        if ( !is_dir($this->upload_path) )
        {
            return false;
        }

        if ( !$this->is_really_writable($this->upload_path) )
        {
            $this->set_error('upload_not_writable');
            return false;
        }

        $this->upload_path = preg_replace("/(.+?)\/*$/", "\\1/",  $this->upload_path);
        return true;

    }




    /**
     * 验证文件大小是否在允许范围内
     *
     * @return	bool
     */
    public function is_allowed_filesize()
    {
        if ($this->max_size != 0  AND  $this->file_size > $this->max_size)
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    /**
     * 验证图片是否在允许的长宽内
     *
     * @return	bool
     */
    public function is_allowed_dimensions()
    {
        if ( ! $this->is_image())
        {
            return true;
        }

        if (function_exists('getimagesize'))
        {
            $D = @getimagesize($this->file_temp);

            if ($this->max_width > 0 AND $D['0'] > $this->max_width)
            {
                return false;
            }

            if ($this->max_height > 0 AND $D['1'] > $this->max_height)
            {
                return false;
            }

            return true;
        }

        return true;
    }

    /**
     * 文件是否被允许上传
     * 判断后缀是否是允许范围
     * 如果是图片后缀的文件会判断是否是真实图片
     * 如果是严谨判断mimes，会检查文件mimes与后缀是否一致。如 example.jpg 为example.png改名而来，则会返回false
     *
     * @return	bool
     */
    public function is_allowed_filetype($ignore_mime = false)
    {
        if ($this->allowed_types == '*')
        {
            return true;
        }

        if (count($this->allowed_types) == 0 OR ! is_array($this->allowed_types))
        {
            $this->set_error('upload_no_file_types');
            return false;
        }

        $ext = strtolower(ltrim($this->file_ext, '.'));

        if ( ! in_array($ext, $this->allowed_types))
        {
            return false;
        }

        // 如果是图片，会确认是否为真实图片
        $image_types = array('gif', 'jpg', 'jpeg', 'png', 'jpe');

        if (in_array($ext, $image_types))
        {
            if (getimagesize($this->file_temp) === false)
            {
                return false;
            }
        }

        //如果忽略检查mimes；
        //仅在确认重命名的文件名后缀是否允许时，为true
        if ($ignore_mime === true)
        {
            return true;
        }

        if ($this->mimes_check === false)
        {
            return true;
        }

        $mime = $this->mimes_types($ext);

        if (is_array($mime))
        {
            if (in_array($this->file_type, $mime, true))
            {
                return true;
            }
        }
        elseif ($mime == $this->file_type)
        {
            return true;
        }

        return false;
    }

    /**
     * 验证图片
     *
     * @return	bool
     */
    public function is_image()
    {
        $png_mimes  = array('image/x-png');
        $jpeg_mimes = array('image/jpg', 'image/jpe', 'image/jpeg', 'image/pjpeg');

        if (in_array($this->file_type, $png_mimes))
        {
            $this->file_type = 'image/png';
        }

        if (in_array($this->file_type, $jpeg_mimes))
        {
            $this->file_type = 'image/jpeg';
        }

        $img_mimes = array(
            'image/gif',
            'image/jpeg',
            'image/png',
        );

        return (in_array($this->file_type, $img_mimes, true)) ? true : false;
    }


    /**
     * 提取文件扩展名包含 '.'
     *
     * @param	string
     * @return	string
     */
    public function get_extension($filename)
    {
        $x = explode('.', $filename);
        return '.'.end($x);
    }

    /**
     * 文件名安全过滤
     *
     * @param	string
     * @return	string
     */
    public function clean_file_name($filename)
    {
        $bad = array(
            "<!--",
            "-->",
            "'",
            "<",
            ">",
            '"',
            '&',
            '$',
            '=',
            ';',
            '?',
            '/',
            "%20",
            "%22",
            "%3c",		// <
            "%253c",	// <
            "%3e",		// >
            "%0e",		// >
            "%28",		// (
            "%29",		// )
            "%2528",	// (
            "%26",		// &
            "%24",		// $
            "%3f",		// ?
            "%3b",		// ;
            "%3d"		// =
        );

        $filename = str_replace($bad, '', $filename);

        return stripslashes($filename);
    }


    /**
     * 过滤文件名的最大长度
     *
     * @param	string
     * @return	string
     */
    public function limit_filename_length($filename, $length)
    {
        if (strlen($filename) < $length)
        {
            return $filename;
        }

        $ext = '';
        if (strpos($filename, '.') !== false)
        {
            $parts		= explode('.', $filename);
            $ext		= '.'.array_pop($parts);
            $filename	= implode('.', $parts);
        }

        return substr($filename, 0, ($length - strlen($ext))).$ext;
    }


    /**
     * 重组文件名
     *
     * @param	string
     * @return	string
     */
    protected function _prep_filename($filename)
    {
        if (strpos($filename, '.') === false OR $this->allowed_types == '*')
        {
            return $filename;
        }

        $parts		= explode('.', $filename);
        $ext		= array_pop($parts);
        $filename	= array_shift($parts);

        foreach ($parts as $part)
        {
            if ( ! in_array(strtolower($part), $this->allowed_types) OR $this->mimes_types(strtolower($part)) === false)
            {
                $filename .= '.'.$part.'_';
            }
            else
            {
                $filename .= '.'.$part;
            }
        }

        $filename .= '.'.$ext;

        return $filename;
    }


    /**
     * 处理错误信息
     *
     * @param	string
     * @return	void
     */
    public function set_error($msg)
    {

        if (is_array($msg))
        {
            foreach ($msg as $val)
            {
                $msg = __($val);
                $this->error_msg[] = $msg;
            }
        }
        else
        {
            $msg = __($msg);
            $this->error_msg[] = $msg;
        }
    }

    /**
     * 格式化错误信息
     *
     * @param	string
     * @param	string
     * @return	string
     */
    public function display_errors($open = '<p>', $close = '</p>')
    {
        $str = '';
        foreach ($this->error_msg as $val)
        {
            $str .= $open.$val.$close;
        }

        return $str;
    }

    /**
     * 输出数据
     * @return array
     */
    public function data()
    {
        return array (
            'file_name'			=> $this->file_name,
            'file_type'			=> $this->file_type,
            'file_path'			=> $this->upload_path,
            'full_path'			=> $this->upload_path.$this->file_name,
            'raw_name'			=> str_replace($this->file_ext, '', $this->file_name),
            'orig_name'			=> $this->orig_name,
            'client_name'		=> $this->client_name,
            'file_ext'			=> $this->file_ext,
            'file_size'			=> $this->file_size,
            'is_image'			=> $this->is_image(),
            'image_width'		=> $this->image_width,
            'image_height'		=> $this->image_height,
            'image_type'		=> $this->image_type,
            'image_size_str'	=> $this->image_size_str,
        );
    }

    /**
     * 根据文件后缀，检索文件mime
     *
     * @param	string
     * @return	string
     */
    public function mimes_types($mime)
    {
        if (count($this->mimes) == 0)
        {
            $this->mimes = Core::config('mimes');
        }

        return ( ! isset($this->mimes[$mime])) ? false : $this->mimes[$mime];
    }

    /**
     * 检查文件的真实mime
     * @param	array
     * @return	void
     */
    protected function _file_mime_type($file)
    {
        $this->file_type = $file['type'];

        //如果不是严谨mimes_check，返回$_FILES['filedname']['type'];
        if($this->mimes_check === false )
        {
            return;
        }

        // We'll need this to validate the MIME info string (e.g. text/plain; charset=us-ascii)
        $regexp = '/^([a-z\-]+\/[a-z0-9\-\.\+]+)(;\s.+)?$/';

        /* Fileinfo extension - most reliable method
         *
         * Unfortunately, prior to PHP 5.3 - it's only available as a PECL extension and the
         * more convenient FILEINFO_MIME_TYPE flag doesn't exist.
         */
        if (function_exists('finfo_file'))
        {
            $finfo = finfo_open(FILEINFO_MIME);
            if (is_resource($finfo)) // It is possible that a false value is returned, if there is no magic MIME database file found on the system
            {
                $mime = @finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                /* According to the comments section of the PHP manual page,
                 * it is possible that this function returns an empty string
                 * for some files (e.g. if they don't exist in the magic MIME database)
                 */
                if (is_string($mime) && preg_match($regexp, $mime, $matches))
                {
                    $this->file_type = $matches[1];
                    return;
                }
            }
        }

        /* This is an ugly hack, but UNIX-type systems provide a "native" way to detect the file type,
         * which is still more secure than depending on the value of $_FILES[$field]['type'], and as it
         * was reported in issue #750 (https://github.com/EllisLab/CodeIgniter/issues/750) - it's better
         * than mime_content_type() as well, hence the attempts to try calling the command line with
         * three different functions.
         *
         * Notes:
         *	- the DIRECTORY_SEPARATOR comparison ensures that we're not on a Windows system
         *	- many system admins would disable the exec(), shell_exec(), popen() and similar functions
         *	  due to security concerns, hence the function_exists() checks
         */
        if (DIRECTORY_SEPARATOR !== '\\')
        {
            $cmd = 'file --brief --mime ' . escapeshellarg($file['tmp_name']) . ' 2>&1';

            if (function_exists('exec'))
            {
                /* This might look confusing, as $mime is being populated with all of the output when set in the second parameter.
                 * However, we only neeed the last line, which is the actual return value of exec(), and as such - it overwrites
                 * anything that could already be set for $mime previously. This effectively makes the second parameter a dummy
                 * value, which is only put to allow us to get the return status code.
                 */
                $mime = @exec($cmd, $mime, $return_status);
                if ($return_status === 0 && is_string($mime) && preg_match($regexp, $mime, $matches))
                {
                    $this->file_type = $matches[1];
                    return;
                }
            }

            if ( (bool) @ini_get('safe_mode') === false && function_exists('shell_exec'))
            {
                $mime = @shell_exec($cmd);
                if (strlen($mime) > 0)
                {
                    $mime = explode("\n", trim($mime));
                    if (preg_match($regexp, $mime[(count($mime) - 1)], $matches))
                    {
                        $this->file_type = $matches[1];
                        return;
                    }
                }
            }

            if (function_exists('popen'))
            {
                $proc = @popen($cmd, 'r');
                if (is_resource($proc))
                {
                    $mime = @fread($proc, 512);
                    @pclose($proc);
                    if ($mime !== false)
                    {
                        $mime = explode("\n", trim($mime));
                        if (preg_match($regexp, $mime[(count($mime) - 1)], $matches))
                        {
                            $this->file_type = $matches[1];
                            return;
                        }
                    }
                }
            }
        }

        // Fall back to the deprecated mime_content_type(), if available (still better than $_FILES[$field]['type'])
        if (function_exists('mime_content_type'))
        {
            $this->file_type = @mime_content_type($file['tmp_name']);
            if (strlen($this->file_type) > 0) // It's possible that mime_content_type() returns false or an empty string
            {
                return;
            }
        }

        $this->file_type = $file['type'];
    }

    /**
     * 验证目录是否可写
     * @param $file
     * @return bool
     */
    public function is_really_writable($file)
    {
        // If we're on a Unix server with safe_mode off we call is_writable
        if (DIRECTORY_SEPARATOR == '/' AND @ini_get("safe_mode") == false)
        {
            return is_writable($file);
        }

        // For windows servers and safe_mode "on" installations we'll actually
        // write a file then read it.  Bah...
        if (is_dir($file))
        {
            $file = rtrim($file, '/').'/'.md5(mt_rand(1,100).mt_rand(1,100));

            if (($fp = @fopen($file, FOPEN_WRITE_CREATE)) === false)
            {
                return false;
            }

            fclose($fp);
            @chmod($file, DIR_WRITE_MODE);
            @unlink($file);
            return true;
        }
        elseif ( ! is_file($file) OR ($fp = @fopen($file, FOPEN_WRITE_CREATE)) === false)
        {
            return false;
        }

        fclose($fp);
        return true;
    }
}