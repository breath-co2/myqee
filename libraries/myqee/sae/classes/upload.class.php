<?php

/**
 * 上传类 For SAE
 *
 * @author     jonwang(jonwang@myqee.com)
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2012 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Upload extends Ex_Upload
{

    /**
     * 保存上传文件
     *
     * 需要设置存放SAE的domain，可修改 Upload::$default_directory = 'newdomain';
     *
     * @param array $file
     * @param string $filename
     * @param string $directory
     * @param int $chmod
     */
    public static function save( array $file, $filename = NULL, $directory = NULL, $chmod = 0644 )
    {
        if (!isset($file['tmp_name']) or !is_uploaded_file($file['tmp_name']))
        {
            // Ignore corrupted uploads
            return false;
        }

        if ($filename===null)
        {
            // Use the default filename, with a timestamp pre-pended
            $filename = uniqid().$file['name'];
        }

        if (Upload::$remove_spaces===true)
        {
            // Remove spaces from the filename
            $filename = preg_replace('/\s+/', '_', $filename);
        }

        $obj = new SaeStorage();

        return $obj->upload(Upload::$default_directory, $filename, $file['tmp_name']);
    }
}