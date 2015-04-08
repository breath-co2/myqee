<?php
// Define 404 error constant
define('E_PAGE_NOT_FOUND', 43);

// Define database error constant
define('E_DATABASE_ERROR', 44);

/**
 * ErrException.
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2016 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Library_MyQEE_Develop_ErrException extends Exception
{

    // Template file
    protected static $template = 'debug/expcption';

    // Header
    protected $header = FALSE;

    // Error code
    protected $code = E_CORE_ERROR;

    /**
     * @var  array  PHP error code => human readable name
     */
    public static $php_errors = array
    (
        E_PAGE_NOT_FOUND    => '404 Error',
        E_DATABASE_ERROR    => 'Database Error',
        E_ERROR             => 'Fatal Error',
        E_USER_ERROR        => 'User Error',
        E_PARSE             => 'Parse Error',
        E_WARNING           => 'Warning',
        E_USER_WARNING      => 'User Warning',
        E_USER_NOTICE       => 'User Notice',
        E_STRICT            => 'Strict',
        E_NOTICE            => 'Notice',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
   );

    /**
     * Set exception message.
     *
     * @param  string $message i18n language key for the message
     * @param  array  $code addition line parameters
     */
    public function __construct($message, $code = null)
    {
        if ($code !== null) $this->code = $code;
        parent::__construct($message);
    }

    /**
     * Magic method for converting an object to a string.
     *
     * @return  string  i18n message
     */
    public function __toString()
    {
        return (string) $this->message;
    }

    /**
     * Sends an Internal Server Error header.
     *
     * @return  void
     */
    public function sendHeaders()
    {
        // Send the 500 header
        header('HTTP/1.1 500 Internal Server Error');
    }

    /**
     * PHP error handler, converts all errors into ErrorExceptions. This handler
     * respects error_reporting settings.
     *
     * @throws  ErrorException
     * @return  TRUE
     */
    public static function error_handler($code, $error, $file = null, $line = null)
    {
        if ((error_reporting() & $code) !== 0)
        {
            // This error is not suppressed by current error reporting settings
            // Convert the error into an ErrorException
            throw new ErrorException($error, $code, 0, $file, $line);
        }

        // Do not execute the PHP error handler
        return TRUE;
    }

    /**
     * Inline exception handler, displays the error message, source of the
     * exception, and the stack trace of the error.
     *
     * @uses	Kohana::exception_text
     * @param   object   exception object
     * @return  boolean
     */
    public static function exception_handler(Exception $e, $return = false)
    {
        try
        {
            // Get the exception information
            $type    = get_class($e);
            $code    = $e->getCode();
            $message = $e->getMessage();
            $file    = $e->getFile();
            $line    = $e->getLine();

            if (isset(self::$php_errors[$code]))
            {
                $code = self::$php_errors[$code];
            }

            // Create a text version of the exception
            $error = self::exception_text($e);

            if (IS_CLI)
            {
                $str = "\n" . $e->__toString() . "\n";

                if ($return)
                {
                    return $str;
                }
                else
                {
                    echo $str;
                }
                return true;
            }

            // Get the exception backtrace
            $trace = $e->getTrace();

            if ($e instanceof ErrorException)
            {
                if (version_compare(PHP_VERSION, '5.3', '<'))
                {
                    // Workaround for a bug in ErrorException::getTrace() that exists in
                    // all PHP 5.2 versions. @see http://bugs.php.net/bug.php?id=45895
                    for($i = count($trace) - 1; $i > 0; -- $i)
                    {
                        if (isset($trace[$i - 1]['args']))
                        {
                            // Re-position the args
                            $trace[$i]['args'] = $trace[$i - 1]['args'];

                            // Remove the args
                            unset($trace[$i - 1]['args']);
                        }
                    }
                }
            }
            if ($return !== true)
            {
                Core::close_buffers(false);
            }

            if ($e->getCode() == 43)
            {
                HttpIO::$status = 404;
            }
            else
            {
                HttpIO::$status = 500;
            }
            HttpIO::send_headers();

            // Start an output buffer
            ob_start();
            // Include the exception HTML
            include Core::find_file('views', self::$template);

            // Display the contents of the output buffer
            $string = ob_get_clean();
            if ($return)
            {
                return $string;
            }
            else
            {
                echo $string;
            }
            return TRUE;
        }
        catch (Exception $e)
        {
            // Clean the output buffer if one exists
            ob_get_level() and ob_clean();

            // Display the exception text
            echo self::exception_text($e), "\n";

            exit(1);
        }
    }

    /**
     * Get a single line of text representing the exception:
     *
     * Error [ Code ]: Message ~ File [ Line ]
     *
     * @param   object  Exception
     * @return  string
     */
    public static function exception_text(Exception $e)
    {
        return sprintf('%s [ %s ]: %s ~ %s [ %d ]', get_class($e), $e->getCode(), strip_tags($e->getMessage()), Core::debug_path($e->getFile()), $e->getLine());
    }

    /**
     * Returns an array of HTML strings that represent each step in the backtrace.
     *
     * // Displays the entire current backtrace
     * echo implode('<br/>', Kohana::trace());
     *
     * @param   string  path to debug
     * @return  string
     */
    public static function trace(array $trace = NULL)
    {
        if ($trace === null)
        {
            // Start a new trace
            $trace = debug_backtrace();
        }

        // Non-standard function calls
        $statements = array('include', 'include_once', 'require', 'require_once');

        $output = array();
        foreach ($trace as $step)
        {
            if (!isset($step['function']))
            {
                // Invalid trace step
                continue;
            }

            if (isset($step['file']) and isset($step['line']))
            {
                // Include the source of this step
                $source = self::debug_source($step['file'], $step['line']);
            }

            if (isset($step['file']))
            {
                $file = $step['file'];

                if (isset($step['line']))
                {
                    $line = $step['line'];
                }
            }

            // function()
            $function = $step['function'];

            if (in_array($step['function'], $statements))
            {
                if (empty($step['args']))
                {
                    // No arguments
                    $args = array();
                }
                else
                {
                    // Sanitize the file path
                    $args = array($step['args'][0]);
                }
            }
            elseif (isset($step['args']))
            {
                if (isset($step['class']))
                {
                    if (method_exists($step['class'], $step['function']))
                    {
                        $reflection = new ReflectionMethod($step['class'], $step['function']);
                    }
                    else
                    {
                        $reflection = new ReflectionMethod($step['class'], '__call');
                    }
                }
                else
                {
                    $reflection = new ReflectionFunction($step['function']);
                }

                // Get the function parameters
                $params = $reflection->getParameters();

                $args = array();

                foreach ($step['args'] as $i => $arg)
                {
                    if (isset($params[$i]))
                    {
                        // Assign the argument by the parameter name
                        $args[$params[$i]->name] = $arg;
                    }
                    else
                    {
                        // Assign the argument by number
                        $args[$i] = $arg;
                    }
                }
            }

            if (isset($step['class']))
            {
                // Class->method() or Class::method()
                $function = $step['class'] . $step['type'] . $step['function'];
            }

            $output[] = array('function' => $function, 'args' => isset($args) ? $args : NULL, 'file' => isset($file) ? $file : NULL, 'line' => isset($line) ? $line : NULL, 'source' => isset($source) ? $source : NULL);

            unset($function, $args, $file, $line, $source);
        }

        return $output;
    }

    /**
     * Returns an HTML string, highlighting a specific line of a file, with some
     * number of lines padded above and below.
     *
     * // Highlights the current line of the current file
     * echo Kohana::debug_source(__FILE__, __LINE__);
     *
     * @param   string   file to open
     * @param   integer  line number to highlight
     * @param   integer  number of padding lines
     * @return  string
     */
    public static function debug_source($file, $line_number, $padding = 5)
    {
        // Open the file and set the line position
        $file = @fopen($file, 'r');
        $line = 0;

        // Set the reading range
        $range = array('start' => $line_number - $padding, 'end' => $line_number + $padding);

        // Set the zero-padding amount for line numbers
        $format = '% ' . strlen($range['end']) . 'd';

        $source = '';
        while (($row = @fgets($file)) !== FALSE)
        {
            // Increment the line number
            if (++ $line > $range['end']) break;

            if ($line >= $range['start'])
            {
                // Make the row safe for output
                $row = @htmlspecialchars($row, ENT_NOQUOTES, Core::$charset);

                // Trim whitespace and sanitize the row
                $row = '<span class="number">' . sprintf($format, $line) . '</span> ' . $row;

                if ($line === $line_number)
                {
                    // Apply highlighting to this row
                    $row = '<span class="line highlight">' . $row . '</span>';
                }
                else
                {
                    $row = '<span class="line">' . $row . '</span>';
                }

                // Add to the captured source
                $source .= $row;
            }
        }

        // Close the file
        @fclose($file);

        return '<pre class="source"><code>' . $source . '</code></pre>';
    }

    /**
     * Returns an HTML string of information about a single variable.
     *
     * Borrows heavily on concepts from the Debug class of [Nette](http://nettephp.com/).
     *
     * @param mixed $value variable to dump
     * @param integer $length maximum length of strings
     * @return string
     */
    public static function dump($value, $length = 128)
    {
        return self::_dump($value, $length);
    }

    /**
     * Helper for Kohana::dump(), handles recursion in arrays and objects.
     *
     * @param mixed $varvariable to dump
     * @param integer $length maximum length of strings
     * @param integer $level recursion level (internal)
     * @return  string
     */
    private static function _dump(& $var, $length = 128, $level = 0)
    {
        if ($var === null)
        {
            return '<small>NULL</small>';
        }
        elseif (is_bool($var))
        {
            return '<small>bool</small> ' . ($var ? 'TRUE' : 'FALSE');
        }
        elseif (is_float($var))
        {
            return '<small>float</small> ' . $var;
        }
        elseif (is_resource($var))
        {
            if (($type = get_resource_type($var)) === 'stream' and $meta = stream_get_meta_data($var))
            {
                $meta = stream_get_meta_data($var);

                if (isset($meta['uri']))
                {
                    $file = $meta['uri'];

                    if (function_exists('stream_is_local'))
                    {
                        // Only exists on PHP >= 5.2.4
                        if (stream_is_local($file))
                        {
                            $file = Core::debug_path($file);
                        }
                    }

                    return '<small>resource</small><span>(' . $type . ')</span> ' . htmlspecialchars($file, ENT_NOQUOTES, Core::$charset);
                }
            }
            else
            {
                return '<small>resource</small><span>(' . $type . ')</span>';
            }
        }
        elseif (is_string($var))
        {
            if (strlen($var) > $length)
            {
                // Encode the truncated string
                $str = htmlspecialchars(substr($var, 0, $length), ENT_NOQUOTES, Core::$charset) . '&nbsp;&hellip;';
            }
            else
            {
                // Encode the string
                $str = @htmlspecialchars($var, ENT_NOQUOTES, Core::$charset);
            }

            return '<small>string</small><span>(' . strlen($var) . ')</span> "' . $str . '"';
        }
        elseif (is_array($var))
        {
            $output = array();

            // Indentation for this variable
            $space = str_repeat($s = '	', $level);

            static $marker = null;

            if ($marker === null)
            {
                // Make a unique marker
                $marker = uniqid("\x00");
            }

            if (empty($var))
            {
                // Do nothing
            }
            elseif (isset($var[$marker]))
            {
                $output[] = "(\n$space$s*RECURSION*\n$space)";
            }
            elseif ($level < 5)
            {
                $output[] = "<span>(";

                $var[$marker] = TRUE;
                foreach ($var as $key => & $val)
                {
                    if ($key === $marker) continue;
                    if (! is_int($key))
                    {
                        $key = '"' . $key . '"';
                    }

                    $output[] = "$space$s$key => " . self::_dump($val, $length, $level + 1);
                }
                unset($var[$marker]);

                $output[] = "$space)</span>";
            }
            else
            {
                // Depth too great
                $output[] = "(\n$space$s...\n$space)";
            }

            return '<small>array</small><span>(' . count($var) . ')</span> ' . implode("\n", $output);
        }
        elseif (is_object($var))
        {
            // Copy the object as an array
            $array = (array) $var;

            $output = array();

            // Indentation for this variable
            $space = str_repeat($s = '	', $level);

            $hash = spl_object_hash($var);

            // Objects that are being dumped
            static $objects = array();

            if (empty($var))
            {
                // Do nothing
            }
            elseif (isset($objects[$hash]))
            {
                $output[] = "{\n$space$s*RECURSION*\n$space}";
            }
            elseif ($level < 5)
            {
                $output[] = "<code>{";

                $objects[$hash] = TRUE;
                foreach ($array as $key => & $val)
                {
                    if ($key[0] === "\x00")
                    {
                        // Determine if the access is private or protected
                        $access = '<small>' . ($key[1] === '*' ? 'protected' : 'private') . '</small>';

                        // Remove the access level from the variable name
                        $key = substr($key, strrpos($key, "\x00") + 1);
                    }
                    else
                    {
                        $access = '<small>public</small>';
                    }

                    $output[] = "$space$s$access $key => " . self::_dump($val, $length, $level + 1);
                }
                unset($objects[$hash]);

                $output[] = "$space}</code>";
            }
            else
            {
                // Depth too great
                $output[] = "{\n$space$s...\n$space}";
            }

            return '<small>object</small> <span>' . get_class($var) . '(' . count($array) . ')</span> ' . implode("\n", $output);
        }
        else
        {
            return '<small>' . gettype($var) . '</small> ' . htmlspecialchars(print_r($var, TRUE), ENT_NOQUOTES, Core::$charset);
        }
    }

    /**
     * Catches errors that are not caught by the error handler, such as E_PARSE.
     *
     * @uses	Kohana::exception_handler
     * @return  void
     */
    public static function shutdown_handler()
    {
        $error = error_get_last();
        if ($error)
        {
            static $run = null;
            if (true === $run) return;
            $run = true;
            if (((E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR) & $error['type']) !== 0)
            {
                $run = true;
                self::exception_handler(new ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));
                exit(1);
            }
        }

        if (isset($_REQUEST['debug']))
        {
            if (Core::debug()->profiler('output')->is_open())
            {
                Core::debug()->profiler('output')->start('Views', 'Global Data');
                Core::debug()->profiler('output')->stop(array('Global Data' => View::get_global_data()));
            }

            // 输出debug信息
            $file = Core::find_file('views', IS_CLI ? 'debug/profiler_cli' : 'debug/profiler');
            if ($file)
            {
                ob_start();
                include $file;
                $out = ob_get_clean();
                if (stripos(Core::$output, '</body>') !== false)
                {

                    Core::$output = str_ireplace('</body>', $out . '</body>', Core::$output);
                }
                else
                {
                    Core::$output .= $out;
                }
            }
        }
    }
}
