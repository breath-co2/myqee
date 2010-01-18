<?php defined('MYQEEPATH') or die('No direct script access.');
/**
 * session library.
 *
 * $Id$
 *
 * @package    Core
 * @author     Myqee Team
 * @copyright  (c) 2007-2008 Myqee Team
 * @license    http://myqee.com/license.html
 */
class Session_Core {

	// session singleton
	protected static $instance;
	
	// Protected key names (cannot be set by the user)
	protected static $protect = array('session_id'=>1,'_qee_flash_'=>1);
	
	protected static $config;
	protected static $flash;

	/**
	 * Singleton instance of session.
	 */
	public static function instance()
	{
		if (Session::$instance == NULL)
		{
			// Create a new instance
			new Session;
		}
		return Session::$instance;
	}

	/**
	 * On first session instance creation, sets up the driver and creates session.
	 */
	public function __construct()
	{
		// This part only needs to be run once
		if (Session::$instance === NULL)
		{
			// Load config
			Session::$config = Myqee::config('core.session');

			// Configure garbage collection
			ini_set('session.gc_probability', (int) Session::$config['gc_probability']);
			ini_set('session.gc_divisor', 100);
			ini_set('session.gc_maxlifetime', (Session::$config['expiration'] == 0) ? 86400 : Session::$config['expiration']);

			// Create a new session
			$this->create();

			// Close the session just before sending the headers, so that
			// the session cookie(s) can be written.
			Event::add('system.send_headers', array($this, 'write_close'));

			// Make sure that sessions are closed before exiting
			register_shutdown_function(array($this, 'write_close'));

			// Singleton instance
			Session::$instance = $this;
		}
	}

	/**
	 * Get the session id.
	 *
	 * @return  string
	 */
	public function id()
	{
		return $_SESSION['session_id'];
	}

	/**
	 * Create a new session.
	 *
	 * @param   array  variables to set after creation
	 * @return  void
	 */
	public function create($vars = NULL)
	{
		// Destroy any current sessions
		$this->destroy();
		
		if ( preg_match('~^(?=.*[a-z])[a-z0-9_]++$~iD', Session::$config['name'])){
			// Name the session, this will also be the name of the cookie
			session_name(Session::$config['name']);
		}

		$cookieconfig = Myqee::config('core.cookie');
		// Set the session cookie parameters
		session_set_cookie_params
		(
			Session::$config['expiration'],
			$cookieconfig['path'],
			$cookieconfig['domain'],
			$cookieconfig['secure'],
			$cookieconfig['httponly']
		);

		// Start the session!
		session_start();
		
		// Put session_id in the session variable
		$_SESSION['session_id'] = session_id();

		// Set defaults
		if ( ! isset($_SESSION['_qee_flash_'])){
			$_SESSION['_qee_flash_'] = array();
		}
		
		// Set up flash variables
		Session::$flash =& $_SESSION['_qee_flash_'];
		
		$this->expire_flash();
		
		// Set the new data
		Session::set($vars);
	}

	/**
	 * Destroys the current session.
	 *
	 * @return  void
	 */
	public function destroy()
	{
		if (session_id() !== '')
		{
			// Get the session name
			$name = session_name();

			// Destroy the session
			session_destroy();

			// Re-initialize the array
			$_SESSION = array();

			// Delete the session cookie
			
			Myqee::delete_cookie($name);
		}
	}

	/**
	 * Runs the system.session_write event, then calls session_write_close.
	 *
	 * @return  void
	 */
	public function write_close()
	{
		static $run;

		if ($run === NULL)
		{
			$run = TRUE;

			// Run the events that depend on the session being open
			Event::run('system.session_write');

			// Expire flash keys
			$this->expire_flash();

			// Close the session
			session_write_close();
		}
	}

	/**
	 * Set a session variable.
	 *
	 * @param   string|array  key, or array of values
	 * @param   mixed         value (if keys is not an array)
	 * @return  void
	 */
	public function set($keys, $val = FALSE)
	{
		if (empty($keys))
			return FALSE;

		if ( ! is_array($keys))
		{
			$keys = array($keys => $val);
		}

		foreach ($keys as $key => $val)
		{
			if (isset(Session::$protect[$key]))
				continue;

			// Set the key
			$_SESSION[$key] = $val;
		}
	}

	/**
	 * Set a flash variable.
	 *
	 * @param   string|array  key, or array of values
	 * @param   mixed         value (if keys is not an array)
	 * @return  void
	 */
	public function set_flash($keys, $val = FALSE)
	{
		if (empty($keys))
			return FALSE;

		if ( ! is_array($keys))
		{
			$keys = array($keys => $val);
		}

		foreach ($keys as $key => $val)
		{
			if ($key == FALSE)
				continue;

			Session::$flash[$key] = 'new';
			Session::set($key, $val);
		}
	}

	/**
	 * Freshen one, multiple or all flash variables.
	 *
	 * @param   string  variable key(s)
	 * @return  void
	 */
	public function keep_flash($keys = NULL)
	{
		$keys = ($keys === NULL) ? array_keys(Session::$flash) : func_get_args();

		foreach ($keys as $key)
		{
			if (isset(Session::$flash[$key]))
			{
				Session::$flash[$key] = 'new';
			}
		}
	}

	/**
	 * Expires old flash data and removes it from the session.
	 *
	 * @return  void
	 */
	public function expire_flash()
	{
		static $run;

		// Method can only be run once
		if ($run === TRUE)
			return;

		if ( ! empty(Session::$flash))
		{
			foreach (Session::$flash as $key => $state)
			{
				if ($state === 'old')
				{
					// Flash has expired
					unset(Session::$flash[$key], $_SESSION[$key]);
				}
				else
				{
					// Flash will expire
					Session::$flash[$key] = 'old';
				}
			}
		}

		// Method has been run
		$run = TRUE;
	}
	
	/**
	 * Get a variable. Access to sub-arrays is supported with key.subkey.
	 *
	 * @param   string  variable key
	 * @param   mixed   default value returned if variable does not exist
	 * @return  mixed   Variable data if key specified, otherwise array containing all session data.
	 */
	public function get($key = FALSE, $default = FALSE)
	{
		if (empty($key))
			return $_SESSION;

		$result = isset($_SESSION[$key]) ? $_SESSION[$key] : Myqee::key_string($_SESSION, $key);

		return ($result === NULL) ? $default : $result;
	}

	/**
	 * Get a variable, and delete it.
	 *
	 * @param   string  variable key
	 * @param   mixed   default value returned if variable does not exist
	 * @return  mixed
	 */
	public function get_once($key, $default = FALSE)
	{
		$return = Session::get($key, $default);
		Session::delete($key);

		return $return;
	}

	/**
	 * Delete one or more variables.
	 *
	 * @param   string  variable key(s)
	 * @return  void
	 */
	public function delete()
	{
		$args = func_get_args();

		foreach ($args as $key)
		{
			if (isset(Session::$protect[$key]))
				continue;
				
			// Unset the key
			unset($_SESSION[$key]);
		}
	}

} // End session Class
