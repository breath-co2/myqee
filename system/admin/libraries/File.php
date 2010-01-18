<?php
/**
 * File helper class.
 *
 * $Id$
 *
 * @package    Core
 * @author     Myqee Team
 * @copyright  (c) 2007-2008 Myqee Team
 * @license    http://myqee.com/license.html
 */
class file_Core {

	/**
	 * Attempt to get the mime type from a file. This method is horribly
	 * unreliable, due to PHP being horribly unreliable when it comes to
	 * determining the mime-type of a file.
	 *
	 * @param   string   filename
	 * @return  string   mime-type, if found
	 * @return  boolean  FALSE, if not found
	 */
	public static function mime($filename)
	{
		// Make sure the file is readable
		if ( ! (is_file($filename) AND is_readable($filename)))
			return FALSE;

		// Get the extension from the filename
		$extension = strtolower(substr(strrchr($filename, '.'), 1));

		if (preg_match('/^(?:jpe?g|png|[gt]if|bmp|swf)$/', $extension))
		{
			// Disable error reporting
			$ER = error_reporting(0);

			// Use getimagesize() to find the mime type on images
			$mime = getimagesize($filename);

			// Turn error reporting back on
			error_reporting($ER);

			// Return the mime type
			if (isset($mime['mime']))
				return $mime['mime'];
		}

		if (function_exists('finfo_open'))
		{
			// Use the fileinfo extension
			$finfo = finfo_open(FILEINFO_MIME);
			$mime  = finfo_file($finfo, $filename);
			finfo_close($finfo);

			// Return the mime type
			return $mime;
		}

		if (ini_get('mime_magic.magicfile') AND function_exists('mime_content_type'))
		{
			// Return the mime type using mime_content_type
			return mime_content_type($filename);
		}

		if ( ! empty($extension) AND is_array($mime = Myqee::config('mimes.'.$extension)))
		{
			// Return the mime-type guess, based on the extension
			return $mime[0];
		}

		// Unable to find the mime-type
		return FALSE;
	}

	/**
	 * Force a download of a file to the user's browser. This function is
	 * binary-safe and will work with any MIME type that Kohana is aware of.
	 *
	 * @param   string  a file path or file name
	 * @param   mixed   data to be sent if the filename does not exist
	 * @param   string  suggested filename to display in the download
	 * @return  void
	 */
	public static function download($filename = NULL, $data = NULL, $nicename = NULL)
	{
		if (empty($filename))
			return FALSE;

		if (is_file($filename))
		{
			// Get the real path
			$filepath = str_replace('\\', '/', realpath($filename));

			// Set filesize
			$filesize = filesize($filepath);

			// Get filename
			$filename = substr(strrchr('/'.$filepath, '/'), 1);

			// Get extension
			$extension = strtolower(substr(strrchr($filepath, '.'), 1));
		}
		else
		{
			// Get filesize
			$filesize = strlen($data);

			// Make sure the filename does not have directory info
			$filename = substr(strrchr('/'.$filename, '/'), 1);

			// Get extension
			$extension = strtolower(substr(strrchr($filename, '.'), 1));
		}

		// Get the mime type of the file
		$mime = Myqee::config('mimes.'.$extension);

		if (empty($mime))
		{
			// Set a default mime if none was found
			$mime = array('application/octet-stream');
		}

		// Generate the server headers
		header('Content-Type: '.$mime[0]);
		header('Content-Disposition: attachment; filename="'.(empty($nicename) ? $filename : $nicename).'"');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: '.sprintf('%d', $filesize));

		// More caching prevention
		header('Expires: 0');

		if (Kohana::user_agent('browser') === 'Internet Explorer')
		{
			// Send IE headers
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
		}
		else
		{
			// Send normal headers
			header('Pragma: no-cache');
		}

		// Clear the output buffer
		Kohana::close_buffers(FALSE);

		if (isset($filepath))
		{
			// Open the file
			$handle = fopen($filepath, 'rb');

			// Send the file data
			fpassthru($handle);

			// Close the file
			fclose($handle);
		}
		else
		{
			// Send the file data
			echo $data;
		}
	}

	/**
	 * Split a file into pieces matching a specific size.
	 *
	 * @param   string   file to be split
	 * @param   string   directory to output to, defaults to the same directory as the file
	 * @param   integer  size, in MB, for each chunk to be
	 * @return  integer  The number of pieces that were created.
	 */
	public static function split($filename, $output_dir = FALSE, $piece_size = 10)
	{
		// Find output dir
		$output_dir = ($output_dir == FALSE) ? pathinfo(str_replace('\\', '/', realpath($filename)), PATHINFO_DIRNAME) : str_replace('\\', '/', realpath($output_dir));
		$output_dir = rtrim($output_dir, '/').'/';

		// Open files for writing
		$input_file = fopen($filename, 'rb');

		// Change the piece size to bytes
		$piece_size = 1024 * 1024 * (int) $piece_size; // Size in bytes

		// Set up reading variables
		$read  = 0; // Number of bytes read
		$piece = 1; // Current piece
		$chunk = 1024 * 8; // Chunk size to read

		// Split the file
		while ( ! feof($input_file))
		{
			// Open a new piece
			$piece_name = $filename.'.'.str_pad($piece, 3, '0', STR_PAD_LEFT);
			$piece_open = @fopen($piece_name, 'wb+') or die('Could not write piece '.$piece_name);

			// Fill the current piece
			while ($read < $piece_size AND $data = fread($input_file, $chunk))
			{
				fwrite($piece_open, $data) or die('Could not write to open piece '.$piece_name);
				$read += $chunk;
			}

			// Close the current piece
			fclose($piece_open);

			// Prepare to open a new piece
			$read = 0;
			$piece++;

			// Make sure that piece is valid
			($piece < 999) or die('Maximum of 999 pieces exceeded, try a larger piece size');
		}

		// Close input file
		fclose($input_file);

		// Returns the number of pieces that were created
		return ($piece - 1);
	}

	/**
	 * Join a split file into a whole file.
	 *
	 * @param   string   split filename, without .000 extension
	 * @param   string   output filename, if different then an the filename
	 * @return  integer  The number of pieces that were joined.
	 */
	public static function join($filename, $output = FALSE)
	{
		if ($output == FALSE)
			$output = $filename;

		// Set up reading variables
		$piece = 1; // Current piece
		$chunk = 1024 * 8; // Chunk size to read

		// Open output file
		$output_file = @fopen($output, 'wb+') or die('Could not open output file '.$output);

		// Read each piece
		while ($piece_open = @fopen(($piece_name = $filename.'.'.str_pad($piece, 3, '0', STR_PAD_LEFT)), 'rb'))
		{
			// Write the piece into the output file
			while ( ! feof($piece_open))
			{
				fwrite($output_file, fread($piece_open, $chunk));
			}

			// Close the current piece
			fclose($piece_open);

			// Prepare for a new piece
			$piece++;

			// Make sure piece is valid
			($piece < 999) or die('Maximum of 999 pieces exceeded');
		}

		// Close the output file
		fclose($output_file);

		// Return the number of pieces joined
		return ($piece - 1);
	}

} // End file