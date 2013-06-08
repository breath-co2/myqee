<?php

/**
 * 文件上传核心类
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @author     marlboro <zxf1984@gmail.com>
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */

class Core_Upload
{
    const ERR_OK         = UPLOAD_ERR_OK;
    const ERR_INI_SIZE   = UPLOAD_ERR_INI_SIZE;
    const ERR_FORM_SIZE  = UPLOAD_ERR_FORM_SIZE;
    const ERR_PARTIAL    = UPLOAD_ERR_PARTIAL;
    const ERR_NO_FILE    = UPLOAD_ERR_NO_FILE;
    const ERR_NO_TMP_DIR = UPLOAD_ERR_NO_TMP_DIR;
    const ERR_CANT_WRITE = UPLOAD_ERR_CANT_WRITE;
    const ERR_EXTENSION  = UPLOAD_ERR_EXTENSION;

    /**
     * 文件保存错误
     *
     * @var number
     */
    const ERR_SAVE_FILE  = 11;

    /**
     * 文件名中过滤的字符
     *
     * @var string
     */
    protected $bad_string = array
    (
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
        "%3c",      // <
        "%253c",    // <
        "%3e",      // >
        "%0e",      // >
        "%28",      // (
        "%29",      // )
        "%2528",    // (
        "%26",      // &
        "%24",      // $
        "%3f",      // ?
        "%3b",      // ;
        "%3d",      // =
        "%",
    );

    /**
     * 上传的文件对象
     *
     *      array
     *      (
     *          'name'     => 'index_06.jpg',
     *          'type'     => 'image/jpeg',
     *          'tmp_name' => '/private/var/tmp/phpM9k9ln',
     *          'error'    => 0,
     *          'size'     => 1410,
     *      )
     *
     * @var array
     */
    protected $file = array();

    /**
     * 配置
     *
     * @var array
     */
    protected $config = array();

    /**
     * 设定的文件名
     *
     * @var string
     */
    protected $filename;


    /**
     * 错误号
     *
     * @var int
     */
    protected static $errno = 0;

    /**
     * 错误信息
     *
     * @var string
     */
    protected static $error = '';

    public function __construct($file_key = null)
    {
        if (null===$file_key)
        {
            $file = $_FILES;
            $file = current($file);
        }
        else
        {
            $file = $_FILES[$file_key];
        }

        if (!$file)
        {
            throw new Exception('Upload userfile not set.', UPLOAD::ERR_NO_FILE);
        }

        $this->file = $file;
    }


    /**
     * 返回上传实例化对象
     *
     * @param array $props
     * @return Upload
     */
    public static function factory($file_key = null)
    {
        return new Upload($file_key);
    }


    /**
     * 保存上传的文件
     *
     * 如果返回失败，可通过 `Upload::error()` 获取错误内容, `Upload::errno()` 获取错误编号
     *
     * @return bool
     */
    public function save()
    {
        try
        {
            // 判断是否上传文件
            $this->check_is_upload_file();

            // 检查真实文件类型
            $this->check_real_mime();

            // 整理文件名
            $this->format_name();

            if (!$this->is_allowed_filesize())
            {
                throw new Exception('Upload invalid filesize.', UPLOAD::ERR_FORM_SIZE);
            }

            // 设置图片相关参数
            $this->set_image_properties();


            // 保存上传文件
            if ($this->config['driver'] == 'default')
            {
                $this->do_save_file();

                return true;
            }
            else
            {
                // 通过扩展驱动来实现文件保存
                $this->save_by_driver();
            }
        }
        catch (Exception $e)
        {
            $this->set_error($e->getMessage(), $e->getCode());
            return false;
        }
    }


    /**
     * 执行文件保存操作
     *
     * @throws Exception
     */
    protected function do_save_file()
    {
        // 默认文件上传形式
        // 考虑到多种线上环境，默认使用copy()做移动，如果失败再使用 move_uploaded_file()

        $filename = $this->configp['upload_path'] . $this->file['name'];

        if (!@copy($this->file['temp'], $filename))
        {
            if (!@move_uploaded_file($this->file['temp'], $filename))
            {
                throw new Exception('Upload destination error.', Upload::ERR_SAVE_FILE);
            }
        }

        // 设置文件权限
        @chmod('0644', $filename);
    }

    /**
     * 通过扩展驱动来实现文件保存
     */
    protected function save_by_driver()
    {
        $this->driver()->save();
    }


    /**
     * 设置新文件名(包括文件后缀)
     *
     * @param string $new_filename
     * @return Upload
     */
    public function set_filename($new_filename)
    {
        $this->filename = $new_filename;
        return $this;
    }


    /**
     * 判断是否上传的文件
     *
     * @return boolean
     */
    protected function check_is_upload_file()
    {
        // 非上传的文件做抛错处理
        if(!is_uploaded_file($this->file['tmp_name']))
        {
            $error = (!isset($this->file['error'])) ? 4 : $this->file['error'];

            switch($error)
            {
                case UPLOAD::ERR_INI_SIZE :
                    throw new Exception('Upload file exceeds limit', $error);
                    break;
                case UPLOAD::ERR_FORM_SIZE :
                    throw new Exception('Upload file exceeds form limit', $error);
                    break;
                case UPLOAD::ERR_PARTIAL :
                    throw new Exception('Upload file partial', $error);
                    break;
                case UPLOAD::ERR_NO_FILE :
                    throw new Exception('Upload no file selected', $error);
                    break;
                case UPLOAD::ERR_NO_TMP_DIR :
                    throw new Exception('Upload no temp directory', $error);
                    break;
                case UPLOAD::ERR_CANT_WRITE :
                    throw new Exception('Upload unable to write file', $error);
                    break;
                case UPLOAD::ERR_EXTENSION :
                    throw new Exception('Upload stopped by extension', $error);
                    break;
                default :
                    throw new Exception('Upload no file selected', $error);
                    break;
            }

            return false;
        }
        else
        {
            return true;
        }
    }

    /**
     * 设置真实的文件类型
     *
     * @return boolean|Core_Upload
     */
    protected function check_real_mime()
    {
        // 真实类型检测
        if ($this->config['mimes_check'])
        {
            $type = File::mime($this->file['tmp_name']);

            if (false===$type)
            {
                throw new Exception('Upload error type', Upload::ERR_EXTENSION);
            }
            else
            {
                $this->file['type'] = $type;
            }
        }

        return $this;
    }

    /**
     * 文件名安全过滤
     *
     * @return Upload
     */
    protected function format_name()
    {
        if ($this->filename)
        {
            $this->file['name'] = $this->filename;
        }
        else if ($this->config['rename'])
        {
            // 重命名文件
            $ext = File::ext_by_mime($this->file['type']);      // 获取文件后缀
            $this->file['name'] = Text::random() . $ext;
        }
        else
        {
            // 对文件名做特殊字符过滤处理
            $this->clean_filename();

            if ($this->config['remove_spaces'])
            {
                $this->file['name'] = preg_replace('#\s+#', '_', $this->file['name']);
            }
        }

        return $this;
    }


    /**
     * 过滤文件名中的空格
     *
     * @return Upload
     */
    protected function clean_filename()
    {
        $filename = str_replace($this->bad_string, '', $this->file['name']);

        $this->file['name'] = stripslashes($filename);

        return $this;
    }


    /**
     * 验证文件大小是否在允许范围内
     *
     * @return bool
     */
    protected function is_allowed_filesize()
    {
        if ($this->config['max_size'] > 0  &&  $this->file['size'] > $this->config['max_size'])
        {
            return false;
        }
        else
        {
            return true;
        }
    }


    /**
     * 文件是否被允许上传
     *
     * 判断后缀是否是允许范围
     * 如果是图片后缀的文件会判断是否是真实图片
     * 如果是严谨判断mimes，会检查文件mimes与后缀是否一致。如 example.jpg 为example.png改名而来，则会返回false
     *
     * @return bool
     */
    public function is_allowed_filetype($ignore_mime = false)
    {
        if ($this->config['allowed_types'] == '*')
        {
            return true;
        }

        $ext = strtolower(ltrim($this->file_ext, '.'));

        if (!in_array($ext, $this->config['allowed_types']))
        {
            return false;
        }

        // 如果是图片，会确认是否为真实图片
        $image_types = array('gif', 'jpg', 'jpeg', 'png', 'jpe', 'bmp');

        if (in_array($ext, $image_types))
        {
            if (getimagesize($this->file['temp']) === false)
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
     * 验证是否图片
     *
     * @return bool
     */
    public function is_image()
    {
        $png_mimes  = array('image/x-png');
        $bmp_mimes  = array('image/bmp');
        $jpeg_mimes = array('image/jpg', 'image/jpe', 'image/jpeg', 'image/pjpeg');

        if (in_array($this->file['type'], $bmp_mimes))
        {
            $this->file['type'] = 'image/bmp';
        }

        if (in_array($this->file['type'], $png_mimes))
        {
            $this->file['type'] = 'image/png';
        }

        if (in_array($this->file['type'], $jpeg_mimes))
        {
            $this->file['type'] = 'image/jpeg';
        }

        $img_mimes = array
        (
            'image/gif',
            'image/jpeg',
            'image/png',
            'image/bmp',
        );

        return (in_array($this->file_type, $img_mimes, true)) ? true : false;
    }


    /**
     * 如果是图片则获取图片相关参数
     *
     * @param string
     * @return Upload
     */
    public function set_image_properties()
    {
        if ($this->is_image() && function_exists('getimagesize'))
        {
            if (false !== ($D = @getimagesize($this->file['temp'])))
            {
                $types = array
                (
                    1 => 'gif',
                    2 => 'jpeg',
                    3 => 'png',
                    4 => 'bmp',
                );

                $this->file['image']['width']    = $D['0'];
                $this->file['image']['height']   = $D['1'];
                $this->file['image']['type']     = (!isset($types[$D['2']])) ? 'unknown' : $types[$D['2']];
                $this->file['image']['size_str'] = $D['3'];
            }
        }

        return $this;
    }


    /**
     * 设置错误信息
     *
     * @param string $error_msg
     * @param number $error_no
     * @return Upload
     */
    protected static function set_error($error_msg, $error_no = 0)
    {
        Upload::$errno = $error_msg;
        Upload::$error  = $error_msg;
    }

    /**
     * 返回错误号
     *
     * @return number
     */
    public static function errno()
    {
        return Upload::$errno;
    }

    /**
     * 返回错误内容
     *
     * @return string
     */
    public static function error()
    {
        return Upload::$error;
    }

}