<?php
/**
 * 文件处理核心类
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Core
 * @package    Classes
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Core_File
{
    /**
     * 已知的系统目录
     *
     * @var array
     */
    protected static $sys_dir = array
    (
        DIR_CORE,
        DIR_DATA,
        DIR_CACHE,
        DIR_LIBRARY,
        DIR_LOG,
        DIR_TEMP,
        DIR_WWWROOT,
        DIR_TEAM_LIBRARY,
        DIR_PROJECT,
        DIR_MODULE,
        DIR_DRIVER,
        DIR_SYSTEM,
        DIR_ASSETS,
    );

    /**
     * 允许读写操作的目录
     *
     * @var array
     */
    public static $dir = array
    (
        'data'    => DIR_DATA,
        'cache'   => DIR_CACHE,
        'wwwroot' => DIR_WWWROOT,
        'log'     => DIR_LOG,
        'temp'    => DIR_TEMP,
        'assets'  => DIR_ASSETS,
    );

    /**
     * 文件是否存在
     *
     * @param string $file
     */
    public static function is_file($file, $storage = 'default')
    {

    }

    /**
     * 创建一个文件，多服务器可以自动同步
     *
     * @param string $file
     * @param $data
     * @param $flags
     * @param $context
     * @param string $storage 物理存储组，不传则为默认
     * @return boolean
     */
    public static function create_file($file, $data ,$flags = null, $context = null, $storage = 'default')
    {
        $info = File::check_and_get_path($file);

        # 系统禁用了写入功能
        if (Core::is_file_write_disabled())return false;

        if (File::can_do_run($storage))
        {
            # 系统内部运行时或小于等于1台服时执行

            $dir = substr($file, 0, (int)strrpos(str_replace('\\', '/', $file), '/'));

            if ($dir && !is_dir($dir))
            {
                # 没有文件夹先则创建
                File::create_dir($dir);
            }

            if (false!==@file_put_contents($file, $data , $flags , $context))
            {
                @chmod($file, 0666);

                return true;
            }
            else
            {
                if (IS_DEBUG)Core::debug()->error('create file error:'.Core::debug_path($file));
                return false;
            }
        }
        else
        {
            return File::call_http_host($storage, 'file/create_file', $info[0], $info[1], $data, $flags, $context);
        }
    }

    /**
     * 循环建立目录，多服务器可以自动同步
     *
     * @param string $dir 待创建的文件夹
     * @param boolean $auto_create_default_file 新创建的文件夹，是否自动创建空默认页
     * @param string $storage 物理存储组，不传则为默认
     * @return boolean true/false
     */
    public static function create_dir($dir, $auto_create_default_file = true , $storage = 'default')
    {
        $info = File::check_and_get_path($dir);

        if (File::can_do_run($storage))
        {
            if (!is_dir($dir))
            {
                if ( substr($dir,0,strlen(DIR_SYSTEM))==DIR_SYSTEM )
                {
                    $temp = explode('/', str_replace('\\', '/', substr($dir,strlen(DIR_SYSTEM)) ) );
                    $cur_dir = DIR_SYSTEM;
                }
                else
                {
                    $temp = explode('/', str_replace('\\', '/', $dir) );
                    $cur_dir = '';
                }
                for($i = 0; $i < count($temp); $i++)
                {
                    $cur_dir .= $temp[$i] . '/';
                    if (!is_dir($cur_dir))
                    {
                        if (@mkdir($cur_dir, 0755))
                        {
                            if ($auto_create_default_file)File::create_file($cur_dir.'index.html', ' ');
                        }
                        else
                        {
                            return false;
                        }
                    }
                }
            }

            return true;
        }
        else
        {
            return File::call_http_host($storage, 'file/create_dir', $info[0], $info[1], $auto_create_default_file);
        }
    }

    /**
     * 删除文件，支持多个文件，多服务器可以自动同步
     *
     * @param string/array $file
     * @param string $storage 物理存储组，不传则为默认
     * @return boolean
     */
    public static function unlink($file, $storage='default')
    {
        $info = File::check_and_get_path($file);

        if (File::can_do_run($storage))
        {
            try
            {
                if (is_array($file))
                {
                    $rs = true;
                    foreach ($file as $f)
                    {
                        if (is_file($f))
                        {
                            if (!unlink($f))
                            {
                                $rs = false;
                                break;
                            }
                        }
                    }
                    return $rs;
                }
                else
                {
                    if (is_file($file))
                    {
                        return unlink($file);
                    }
                    elseif (is_dir($file))
                    {
                        return false;
                    }
                    else
                    {
                        return true;
                    }
                }
            }
            catch (Exception $e)
            {
                return false;
            }
        }
        else
        {
            return File::call_http_host($storage, 'file/unlink', $info[0], $info[1]);
        }
    }

    /**
     * 循环删除目录下的所有目录和文件，多服务器可以自动同步
     *
     * @param string $dirName
     * @param string $storage 物理存储组，不传则为默认
     * @return boolean
     */
    public static function remove_dir($dir , $storage = 'default')
    {
        $info = File::check_and_get_path($dir);

        if (File::can_do_run($storage))
        {
            if (!is_dir($dir))
            {
                return true;
            }

            $realpath = realpath($dir);

            if (!$realpath || in_array($realpath.DS, File::$sys_dir))
            {
                return true;
            }

            $handle = opendir($dir);
            while (($file = readdir($handle)) !== false)
            {
                if ( $file != '.' && $file != '..' )
                {
                    $tmp_dir = $dir . DS . $file;
                    is_dir($tmp_dir) ? File::remove_dir($tmp_dir) : @unlink($tmp_dir);
                }
            }

            closedir($handle);

            return @rmdir($dir);
        }
        else
        {
            return File::call_http_host($storage, 'file/remove_dir', $info[0], $info[1] );
        }
    }

    /**
     * 转移目录下的所有目录和文件，多服务器可以自动同步，可操作非空目录
     *
     * @param string $fromdir  源文文件目录
     * @param string $todir  目标文件目录
     * @param boolean $autocoverageold 是否覆盖已有文件，true覆盖，false跳过
     * @param string $storage 物理存储组，不传则为默认
     * @return array($dook,$doerror)
     */
    public static function move_dir($fromdir, $todir, $autocoverageold = true , $storage = 'default')
    {
        $fromdir = rtrim($fromdir,'\\/').DS;
        $todir   = rtrim($todir,'\\/')  .DS;

        if ( $fromdir==$todir ) return array(0,0);

        $info1 = File::check_and_get_path($fromdir);
        $info2 = File::check_and_get_path($todir);

        if (File::can_do_run($storage))
        {
            if (!is_dir($fromdir)) return array(0,0);

            # 完成数
            $donum = array(0, 0);

            if (!is_dir($todir))
            {
                # 创建目标目录
                File::create_dir($todir,false,$storage);
            }

            # 列出目录中当前级别的目录和文件
            $files = glob($fromdir . '*');

            foreach ($files as $file)
            {
                # 目标文件
                $tofile = $todir . basename($file);

                if (is_dir($file))
                {
                    # 如果当前是目录，则移动目录

                    # 移动目录
                    $donum2 = File::move_dir($file, $tofile, $autocoverageold, $storage);
                    if ($donum2)
                    {
                        $donum[0] += $donum2[0];
                        $donum[1] += $donum2[1];
                    }
                }
                else
                {
                    # 文件
                    if ($autocoverageold && file_exists($tofile))
                    {
                        //覆盖已有文件
                        @unlink($tofile);
                    }

                    if ( @rename($file, $tofile) )
                    {
                        $donum[0]++;
                    }
                    else
                    {
                        $donum[1]++;
                    }
                }
            }

            //移除旧目录
            File::remove_dir($fromdir);

            return $donum;
        }
        else
        {
            return File::call_http_host($storage,'file/move_dir', $info1[0], $info1[1], $info2[0], $info2[1], $autocoverageold);
        }
    }


    /**
     * 复制目录下的所有目录和文件到另外一个目录
     *
     * @param string $fromdir  源文文件目录
     * @param string $todir  目标文件目录
     * @param boolean $autocoverageold 是否覆盖已有文件，true覆盖，false跳过
     * @param string $storage 物理存储组，不传则为默认
     * @return array($dook,$doerror)
     */
    public static function copy_dir($fromdir, $todir, $autocoverageold = true , $storage = 'default')
    {
        $fromdir = rtrim($fromdir,'\\/').DS;
        $todir   = rtrim($todir,'\\/')  .DS;

        if ( $fromdir==$todir ) return array(0,0);

        $info1 = File::check_and_get_path($fromdir);
        $info2 = File::check_and_get_path($todir);

        if (File::can_do_run($storage))
        {
            if (!is_dir($fromdir)) return array(0, 0);

            # 完成数
            $donum = array(0, 0);

            if (!is_dir($todir))
            {
                # 创建目标目录
                File::create_dir($todir,false,$storage);
            }

            # 列出目录中当前级别的目录和文件
            $files = glob($fromdir . '*');

            foreach ($files as $file)
            {
                # 目标文件
                $tofile = $todir . basename($file);

                if (is_dir($file))
                {
                    # 如果当前是目录，则移动目录

                    # 移动目录
                    $donum2 = File::copy_dir($file, $tofile, $autocoverageold, $storage);
                    if ($donum2)
                    {
                        $donum[0] += $donum2[0];
                        $donum[1] += $donum2[1];
                    }
                }
                else
                {
                    # 文件
                    if ($autocoverageold && file_exists($tofile))
                    {
                        //覆盖已有文件
                        @unlink($tofile);
                    }

                    if (@copy($file, $tofile))
                    {
                        $donum[0]++;
                    }
                    else
                    {
                        $donum[1]++;
                    }
                }
            }

            return $donum;
        }
        else
        {
            return File::call_http_host($storage,'file/copy_dir', $info1[0], $info1[1], $info2[0], $info2[1], $autocoverageold);
        }
    }

    /**
     * 将指定的文件同步到其它服务器
     *
     * @param string $filename
     * @return boolean
     */
    public static function sync($file, $storage = 'default')
    {
        $info = File::check_and_get_path($file);

        if (File::can_do_run($storage))
        {
            # 本机无需操作
            return true;
        }
        else
        {
            if (is_file($file))
            {
                # 通过create_file把文件再创建一次
                return File::create_file($file, file_get_contents($file), null, null, $storage);
            }
        }
    }

	/**
	 * Split a file into pieces matching a specific size. Used when you need to
	 * split large files into smaller pieces for easy transmission.
	 *
	 *     $count = File::split($file);
	 *
	 * @param string   file to be split
	 * @param string   directory to output to, defaults to the same directory as the file
	 * @param integer  size, in MB, for each piece to be
     * @param string $storage 物理存储组，不传则为默认
	 * @return integer  The number of pieces that were created
	 */
	public static function split($filename, $piece_size = 10 , $storage = 'default')
	{
        $info = File::check_and_get_path($filename);

        if (File::can_do_run($storage))
        {
    		// Open the input file
    		$file = fopen($filename, 'rb');

    		// Change the piece size to bytes
    		$piece_size = floor($piece_size * 1024 * 1024);

    		// Write files in 8k blocks
    		$block_size = 1024 * 8;

    		// Total number of peices
    		$peices = 0;

    		while (!feof($file))
    		{
    			// Create another piece
    			$peices += 1;

    			// Create a new file piece
    			$piece = str_pad($peices, 3, '0', STR_PAD_LEFT);
    			$piece = fopen($filename.'.'.$piece, 'wb+');

    			// Number of bytes read
    			$read = 0;

    			do
    			{
    				// Transfer the data in blocks
    				fwrite($piece, fread($file, $block_size));

    				// Another block has been read
    				$read += $block_size;
    			}
    			while ($read < $piece_size);

    			// Close the piece
    			fclose($piece);
    		}

    		// Close the file
    		fclose($file);

    		return $peices;
        }
        else
        {
            return File::call_http_host($storage,'file/split',$info[0], $info[1], $piece_size);
        }
	}

	/**
	 * Join a split file into a whole file. Does the reverse of [File::split].
	 *
	 *     $count = File::join($file);
	 *
	 * @param string   split filename, without .000 extension
	 * @param string   output filename, if different then an the filename
     * @param string $storage 物理存储组，不传则为默认
	 * @return integer  The number of pieces that were joined.
	 */
	public static function join($filename , $storage = 'default')
	{
        $info = File::check_and_get_path($filename);

        if (File::can_do_run($storage))
        {
    		// Open the file
    		$file = fopen($filename, 'wb+');

    		// Read files in 8k blocks
    		$block_size = 1024 * 8;

    		// Total number of peices
    		$pieces = 0;

    		while (is_file($piece = $filename.'.'.str_pad($pieces + 1, 3, '0', STR_PAD_LEFT)))
    		{
    			// Read another piece
    			$pieces += 1;

    			// Open the piece for reading
    			$piece = fopen($piece, 'rb');

    			while (!feof($piece))
    			{
    				// Transfer the data in blocks
    				fwrite($file, fread($piece, $block_size));
    			}

    			// Close the peice
    			fclose($piece);
    		}

    		return $pieces;
        }
        else
        {
            return File::call_http_host($storage,'file/join', $info[0], $info[1]);
        }
	}

	/**
	 * 返回指定文件类型
	 *
	 *     $mime = File::mime($file);
	 *
	 * @param   string  file name or path
	 * @return  string  mime type on success
	 * @return  FALSE   on failure
	 */
	public static function mime($filename)
	{
		// Get the complete path to the file
		$filename = realpath($filename);

		// Get the extension from the filename
		$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

		if (preg_match('/^(?:jpe?g|png|[gt]if|bmp|swf)$/', $extension) && function_exists('getimagesize'))
		{
			// Use getimagesize() to find the mime type on images
			$file = getimagesize($filename);

			if (isset($file['mime']))
			{
				return $file['mime'];
			}
		}

		if (class_exists('finfo', false))
		{
		    $info = new finfo(defined('FILEINFO_MIME_TYPE') ? FILEINFO_MIME_TYPE : FILEINFO_MIME);
			if ($info)
			{
				return $info->file($filename);
			}
		}

		if (function_exists('mime_content_type'))
		{
			return mime_content_type($filename);
		}

		if (!empty($extension))
		{
			return File::mime_by_ext($extension);
		}

		return false;
	}

	/**
	 * Return the mime type of an extension.
	 *
	 *     $mime = File::mime_by_ext('png'); // "image/png"
	 *
	 * @param   string  extension: php, pdf, txt, etc
	 * @return  string  mime type on success
	 * @return  FALSE   on failure
	 */
	public static function mime_by_ext($extension)
	{
	    $mimes = Core::config('mimes');

		return isset($mimes[$extension])?$mimes[$extension][0]:false;
	}

	/**
	 * Lookup MIME types for a file
	 *
	 * @see Kohana_File::mime_by_ext()
	 * @param string $extension Extension to lookup
	 * @return array Array of MIMEs associated with the specified extension
	 */
	public static function mimes_by_ext($extension)
	{
	    $mimes = Core::config('mimes');

		return isset($mimes[$extension])?((array)$mimes[$extension]):array();
	}

	/**
	 * Lookup file extensions by MIME type
	 *
	 * @param   string  $type File MIME type
	 * @return  array   File extensions matching MIME type
	 */
	public static function exts_by_mime($type)
	{
		static $types = array();

		// Fill the static array
		if (empty($types))
		{
	        $mimes = Core::config('mimes');
			foreach ($mimes as $ext => $ms)
			{
				foreach ($ms as $mime)
				{
					if ($mime == 'application/octet-stream')
					{
						// octet-stream is a generic binary
						continue;
					}

					if (!isset($types[$mime]))
					{
						$types[$mime] = array( (string)$ext );
					}
					elseif (!in_array($ext, $types[$mime]))
					{
						$types[$mime][] = (string)$ext;
					}
				}
			}
		}

		return isset($types[$type])?$types[$type]:false;
	}

	/**
	 * Lookup a single file extension by MIME type.
	 *
	 * @param   string  $type  MIME type to lookup
	 * @return  mixed          First file extension matching or false
	 */
	public static function ext_by_mime($type)
	{
		return current(File::exts_by_mime($type));
	}

    /**
     * 调用HttpServer执行
     *
     * @param string $storage
     * @param string $uri
     * @param mixed $arg1
     * @param mixed $arg2
     * @return boolean mixed
     */
    protected static function call_http_host($storage, $uri, $arg1 = null, $arg2 = null)
    {
        $param_arr = func_get_args();
        array_shift($param_arr); // 把 $storage 移除

        $sync_mode = File::sync_mode();

        if ( $sync_mode == 'rsync' )
        {
            // rsync 模式，调用主服执行
            $action = 'master_exec';
        }
        else
        {
            // 全部同步执行
            $action = 'sync_exec';
        }

        $rs = call_user_func_array(array(HttpCall::factory($storage), $action), $param_arr);

        if (IS_DEBUG)Core::debug()->log($rs);

        if (is_array($rs))
        {
            $i = 0;
            foreach ($rs as $item)
            {
                $i++;

                if ($item!=='success')
                {
                    if (IS_DEBUG)
                    {
                        Core::debug()->error($i.'/'.count($rs), 'call_http_host rs status');
                    }
                    return false;
                }
            }
        }
        else
        {
            if ($rs==='success')
            {
                return true;
            }
            else
            {
                Core::debug()->error('call_http_host error.');
                return false;
            }
        }

        return $rs;
    }

    /**
     * 获取同步模式
     *
     * @return string
     */
    protected static function sync_mode()
    {
        $mode = Core::config('file_sync_mode');
        if (null===$mode)$mode = 'default';

        return $mode;
    }

    /**
     * 是否可以直接执行
     *
     * @param string $storage
     * @return boolean
     */
    protected static function can_do_run($storage)
    {
        return (IS_SYSTEM_MODE || 'none' == File::sync_mode() ||count(Core::config('web_server_list.' . $storage)) <= 1) ? true : false;
    }

	/**
	 * 检查并且获取系统允许读写路径信息，如果不合法则返回false
	 *
	 * @param string/array $file
	 * @return array($key,$path)
	 * @throws Exception
	 */
	protected static function check_and_get_path($file)
	{
	    if (is_array($file))
	    {
	        $array_mode = true;
	    }
	    else
	    {
	        $array_mode = false;
	        $file = (array)$file;
	    }

	    $data = array();
	    foreach (File::$dir as $key=>$path)
	    {
	        $len = strlen($path);
	        foreach ($file as $f)
	        {
    	        $s = substr($f,0,$len);
    	        if ($s == $path)
    	        {
    	            if (!$array_mode)
    	            {
    	                # 单文件模式，直接返回
    	                return array($key,substr($f,$len));
    	            }
    	            else
    	            {
    	                # 多文件，加入列表
        	            $data[0][] = $key;
        	            $data[1][] = substr($f,$len);
    	            }
    	        }
	        }
	    }

	    if ($array_mode && $data)return $data;

	    throw new Exception(__('Not allowed to operate the corresponding directory'));
	}


	/**
	 * 初始化文件对象
	 *
	 * @param string $file 文件名
	 */
	public function __construct($file)
	{

	}
}