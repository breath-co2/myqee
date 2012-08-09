<?php

/**
 * 文件上传核心类
 *
 * @author     jonwang(jonwang@myqee.com)
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2012 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class MyQEE_Upload
{

    public function __construct()
    {

    }

    /**
     * @var  boolean  remove spaces in uploaded files
     */
    public static $remove_spaces = true;

    /**
     * @var  string  default upload directory
     */
    public static $default_directory = 'upload';

    /**
     * 将上传的文件保存
     *
     * if ($array->check())
     * {
     * // Upload is valid, save it
     * Upload::save($_FILES['file']);
     * }
     *
     * @param   array	uploaded file data
     * @param   string   new filename
     * @param   string   new directory
     * @param   integer  chmod mask
     * @return  string   on success, full path to new file
     * @return  false	on failure
     */
    public static function save(array $file, $filename = null, $directory = null, $chmod = 0644)
    {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name']))
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

        if ($directory===null)
        {
            // Use the pre-configured upload directory
            $directory = Upload::$default_directory;
        }
        $directory = DIR_WWWROOT.$directory;

        if (!is_dir($directory) || !is_writable(realpath($directory)))
        {
            throw new Exception('Directory '.$directory.' must be writable');
        }

        // Make the filename into a complete path
        $filename = realpath($directory).DIRECTORY_SEPARATOR.$filename;
        $filename = str_replace('\\', '/', $filename);
        File::create_dir(substr($filename, 0, strrpos($filename, '/')));

        if (move_uploaded_file($file['tmp_name'], $filename))
        {
            if ($chmod!==false)
            {
                // Set permissions on filename
                chmod($filename, $chmod);
            }

            // Return new file path
            return $filename;
        }

        return false;
    }

    /**
     * Tests if upload data is valid, even if no file was uploaded. If you
     * _do_ require a file to be uploaded, add the [Upload::not_empty] rule
     * before this rule.
     *
     * $array->rule('file', 'Upload::valid')
     *
     * @param   array  $_FILES item
     * @return  bool
     */
    public static function valid($file)
    {
        return (isset($file['error']) && isset($file['name']) && isset($file['type']) && isset($file['tmp_name']) && isset($file['size']));
    }

    /**
     * Tests if a successful upload has been made.
     *
     * $array->rule('file', 'Upload::not_empty');
     *
     * @param   array	$_FILES item
     * @return  bool
     */
    public static function not_empty(array $file)
    {
        return (isset($file['error']) && isset($file['tmp_name']) && $file['error']===UPLOAD_ERR_OK && is_uploaded_file($file['tmp_name']));
    }

    /**
     * Test if an uploaded file is an allowed file type, by extension.
     *
     * $array->rule('file', 'Upload::type', array(array('jpg', 'png', 'gif')));
     *
     * @param   array	$_FILES item
     * @param   array	allowed file extensions
     * @return  bool
     */
    public static function type(array $file, array $allowed)
    {
        if ($file['error']!==UPLOAD_ERR_OK) return true;

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        return in_array($ext, $allowed);
    }

    /**
     * 获取文件或者
     *
     * $array->rule('file', 'Upload::type', array(array('jpg', 'png', 'gif')));
     *
     * @param   array	$_FILES item
     * @param   array	allowed file extensions
     * @return  bool
     */
    public static function suffix(array $file)
    {
        return '.'.strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    }
}