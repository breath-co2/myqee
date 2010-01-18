<?php defined('MYQEEPATH') or die('No direct script access.');
/**
 * This FTP library is a simple OOP abstraction layer
 * for PHP's procedural FTP library.
 *
 * $Id$
 *
 * @package		Standard
 * @subpackage	Libraries
 * @category	FTP Abstraction
 * @author		Parnell Springmeyer <parnell@rastermedia.com>
 * @todo 		Nada
 */

class Ftp_Core
{
	protected $errors		= Null;
	protected $connection	= Null;
	
	/**
	 * Setup the FTP connection upon
	 * instantiation!
	 * 
	 * Host, username, password are the required
	 * parameters - if you want an SSL connection
	 * you must pass True to the SSL parameter. The
	 * final two are self-explanatory.
	 *
	 * @param	varchar	$host
	 * @param	varchar	$user
	 * @param	varchar	$pass
	 * @param	boolean	$ssl
	 * @param	integer	$port
	 * @param	integer	$timeout
	 */
	public function __construct($host, $user, $pass, $ssl=False, $port=21, $timeout=90)
	{
		// Connect - SSL?
		$this->connection	= $ssl ? ftp_ssl_connect($host, $port, $timeout) : ftp_connect($host, $port, $timeout);
		$login_result		= ftp_login($this->connection, $user, $pass);
		
		// Check connection
		if(!$this->connection)
			throw new Kohana_User_Exception('FTP Library Error', 'Connection failed!');
		
		// Check login
		if(!$login_result)
			throw new Kohana_User_Exception('FTP Library Error', 'Login failed!');
	}
	
	/**
	 * Passed an array of constants => values
	 * they will be set as FTP options.
	 * 
	 * @param	array	$config
	 * @return	object (chainable)
	 */
	public function setOptions($config)
	{
		if(!is_array($config))
			throw new Kohana_User_Exception('FTP Library Error', 'The config parameter must be passed an array!');
		
		// Loop through configuration array
		foreach($config as $key => $value)
		{
			// Set the options and test to see if they did so successfully - throw an exception if it failed
			if(!ftp_set_option($this->connection, $key, $value))
				throw new Kohana_User_Exception('FTP Library Error', 'The system failed to set the FTP option: "'.$key.'" with the value: "'.$value.'"');
		}
		
		return $this;
	}
	
	/**
	 * Execute a remote command on the FTP server.
	 * 
	 * @see		http://us2.php.net/manual/en/function.ftp-exec.php
	 * @param	varchar	$command
	 * @return	boolean
	 */
	public function execute($command)
	{
		// Execute command
		if(ftp_exec($this->connection, $command))
		{
		    return True;
		} else {
		    return False;
		}
	}
	
	/**
	 * Get executes a get command on the remote
	 * FTP server.
	 *
	 * @param	varchar $local
	 * @param	varchar $remote
	 * @param	const	$mode
	 * @return	boolean
	 */
	public function get($local, $remote, $mode=FTP_ASCII)
	{
		// Get the requested file
		if(ftp_get($this->connection, $local, $remote, $mode))
		{
			// If successful, return the path to the downloaded file...
			return $remote;
		} else {
			return False;
		}
	}
	
	/**
	 * Put executes a put command on the remote
	 * FTP server.
	 *
	 * @param	varchar $local
	 * @param	varchar $remote
	 * @param	const	$mode
	 * @return	boolean
	 */
	public function put($local, $remote, $mode=FTP_ASCII)
	{
		// Upload the local file to the remote location specified
		if(ftp_put($this->connection, $local, $remote, $mode))
		{
			return True;
		} else {
			return False;
		}
	}
	
	/**
	 * Rename executes a rename command on the remote
	 * FTP server.
	 *
	 * @param	varchar $old
	 * @param	varchar $new
	 * @return	boolean
	 */
	public function rename($old, $new)
	{
		// Rename the file
		if(ftp_rename($this->connection, $old, $new))
		{
			return True;
		} else {
			return False;
		}
	}
	
	/**
	 * Rmdir executes an rmdir (remove directory) command
	 * on the remote FTP server.
	 *
	 * @param	varchar $dir
	 * @return	boolean
	 */
	public function rmdir($dir)
	{
		// Remove the directory
		if(ftp_rmdir($this->connection, $dir))
		{
			return True;
		} else {
			return False;
		}
	}
	
	/**
	 * Closes the current FTP connection.
	 *
	 * @return	boolean
	 */
	public function close()
	{
		// Close the connection
		if(ftp_close($this->connection))
		{
			return True;
		} else {
			return False;
		}
	}
	
	/**
	 * Remove executes a delete command on the remote
	 * FTP server.
	 *
	 * @param	varchar $file
	 * @return	boolean
	 */
	public function remove($file)
	{
		// Delete the specified file
		if(ftp_delete($this->connection, $file))
		{
			return True;
		} else {
			return False;
		}
	}
	
	/**
	 * Change the current working directory on the remote
	 * FTP server.
	 *
	 * @param	varchar $dir
	 * @return	boolean
	 */
	public function directory($dir)
	{
		// Change directory
		if(ftp_chdir($this->connection, $dir))
		{
			return True;
		} else {
			return False;
		}
	}
	
	/**
	 * Changes to the parent directory on the remote
	 * FTP server.
	 *
	 * @return	boolean
	 */
	public function parentDir()
	{
		// Move up!
		if(ftp_cdup($this->connection))
		{
			return True;
		} else {
			return False;
		}
	}
	
	/**
	 * Returns the name of the current working directory.
	 *
	 * @return	varchar
	 */
	public function currentDir()
	{
		return ftp_pwd($this->connection);
	}
	
	/**
	 * Permissions executes a chmod command on the remote
	 * FTP server.
	 *
	 * @param	varchar $file
	 * @param	mixed	$mode
	 * @return	boolean
	 */
	public function permissions($file, $mode)
	{
		// Change the desired file's permissions
		if(ftp_chmod($this->connection, $mode, $file))
		{
			return True;
		} else {
			return False;
		}
	}
	
	/**
	 * ListFiles executes a nlist command on the remote
	 * FTP server, returns an array of file names, false
	 * on failure.
	 *
	 * @param	varchar	$directory
	 * @return	mixed
	 */
	public function listFiles($directory)
	{
		return ftp_nlist($this->connection, $directory);
	}
	
	/**
	 * Close the FTP connection if the object is
	 * destroyed.
	 *
	 * @return	boolean
	 */
	public function __destruct()
	{
		return $this->close();
	}
}