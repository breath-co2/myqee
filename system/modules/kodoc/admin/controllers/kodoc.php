<?php
class Kodoc_Controller extends Template_Controllers {

	// Do not allow to run in production
	const ALLOW_PRODUCTION = FALSE;

	public $template = 'kodoc/template';

	// Kodoc instance
	protected $kodoc;

	public function __construct()
	{
		parent::__construct();
		$active = Myqee::segment(1) ? Myqee::segment(1) : 'core';
		
		define('SYSPATH',MYQEEPATH.'api/kohana/');
		// Add the menu to the template
		$this->template->menu = new View('kodoc/menu', array('active' => $active));
	}

	public function index()
	{
		$this->template->content = 'hi';
	}

	public function media()
	{
		if (isset($this->profiler)) $this->profiler->disable();

		// Get the filename
		$file = implode('/', $this->uri->segment_array(1));
		$ext = strrchr($file, '.');

		if ($ext !== FALSE)
		{
			$file = substr($file, 0, -strlen($ext));
			$ext = substr($ext, 1);
		}

		// Disable auto-rendering
		$this->auto_render = FALSE;

		try
		{
			// Attempt to display the output
			echo new View('kodoc/'.$file, NULL, $ext);
		}
		catch (Kohana_Exception $e)
		{
			Event::run('system.404');
		}
	}

	public function _default()
	{
		if (count($segments = Myqee::segment_array(0)) > 1)
		{
			// Find directory (type) and filename
			$type = array_shift($segments);
			$file = implode('/', $segments);

			if (substr($file, -(strlen(EXT))) === EXT)
			{
				// Remove extension
				$file = substr($file, 0, -(strlen(EXT)));
			}

			if ($type === 'config')
			{
				if ($file === 'config')
				{
					// This file can only exist in one location
					$file = MYAPPPATH.$type.'/config'.EXT;
				}
				else
				{
					foreach (array_reverse(Kohana::include_paths()) as $path)
					{
						if (is_file($path.$type.'/'.$file.EXT))
						{
							// Found the file
							$file = $path.$type.'/'.$file.EXT;
							break;
						}
					}
				}
			}
			else
			{
				// Get absolute path to file
				$file = Myqee::find_file($type, $file);
			}

			if (in_array($type, Kodoc::get_types()))
			{
				// Load Kodoc
				$this->kodoc = new Kodoc($type, $file);

				// Set the title
				$this->template->title = implode('/', $this->uri->segment_array(0));

				// Load documentation for this file
				$this->template->content = new View('kodoc/documentation');

				// Exit this method
				return;
			}
		}

		// Nothing to document
		$this->_redirect('modules/kodoc');
	}
	
	
	private function _redirect($uri = '', $method = '302')
	{
		if (Event::has_run('system.send_headers'))
		{
			return FALSE;
		}

		$codes = array
		(
			'refresh' => 'Refresh',
			'300' => 'Multiple Choices',
			'301' => 'Moved Permanently',
			'302' => 'Found',
			'303' => 'See Other',
			'304' => 'Not Modified',
			'305' => 'Use Proxy',
			'307' => 'Temporary Redirect'
		);

		// Validate the method and default to 302
		$method = isset($codes[$method]) ? (string) $method : '302';

		if ($method === '300')
		{
			$uri = (array) $uri;

			$output = '<ul>';
			foreach ($uri as $link)
			{
				$output .= '<li>'.html::anchor($link).'</li>';
			}
			$output .= '</ul>';

			// The first URI will be used for the Location header
			$uri = $uri[0];
		}
		else
		{
			$output = '<p>'.html::anchor($uri).'</p>';
		}

		// Run the redirect event
		Event::run('system.redirect', $uri);

		if (strpos($uri, '://') === FALSE)
		{
			// HTTP headers expect absolute URLs
			$uri = Myqee::url($uri, $this->_protocol());
		}

		if ($method === 'refresh')
		{
			header('Refresh: 0; url='.$uri);
		}
		else
		{
			header('HTTP/1.1 '.$method.' '.$codes[$method]);
			header('Location: '.$uri);
		}

		// We are about to exit, so run the send_headers event
		Event::run('system.send_headers');

		exit('<h1>'.$method.' - '.$codes[$method].'</h1>'.$output);
	}
	
	
	private function _protocol()
	{
		if (PHP_SAPI === 'cli')
		{
			return NULL;
		}
		elseif ( ! empty($_SERVER['HTTPS']) AND $_SERVER['HTTPS'] === 'on')
		{
			return 'https';
		}
		else
		{
			return 'http';
		}
	}

} // End Kodoc Controller