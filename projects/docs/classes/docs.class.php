<?php
/**
 * 手册基础类
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Docs
{
    /**
     * 获取实例化对象
     * @param string $class
     * @return Docs_Class
     */
    public static function factory( $class )
    {
        return new Docs_Class( $class );
    }

    /**
     * Creates an html list of all classes sorted by category (or package if no category)<br>
     * [aaa](http://www.baid.com)
     * asdf
     *
     * @return   string   the html for the menu
     */
    public static function menu()
    {
        $classes = Docs::classes();

        foreach ( $classes as $class )
        {
            if ( isset( $classes['kohana_' . $class] ) )
            {
                // Remove extended classes
                unset( $classes['kohana_' . $class] );
            }
        }

        ksort( $classes );

        $menu = array();

        $route = Core_Route::get( 'docs/api' );

        foreach ( $classes as $class )
        {
            $class = Docs_Class::factory( $class );

            // Test if we should show this class
            if ( ! Docs::show_class( $class ) ) continue;

            $link = HTML::anchor( $route->uri( array( 'class' => $class->class->name ) ), $class->class->name );

            if ( isset( $class->tags['package'] ) )
            {
                foreach ( $class->tags['package'] as $package )
                {
                    if ( isset( $class->tags['category'] ) )
                    {
                        foreach ( $class->tags['category'] as $category )
                        {
                            $menu[$package][$category][] = $link;
                        }
                    }
                    else
                    {
                        $menu[$package]['Base'][] = $link;
                    }
                }
            }
            else
            {
                $menu['[Unknown]']['Base'][] = $link;
            }
        }

        // Sort the packages
        ksort( $menu );

        return View::factory( 'userguide/api/menu' )->bind( 'menu', $menu );
    }

    /**
     * Get all classes and methods of files in a list.
     *
     * >  I personally don't like this as it was used on the index page.  Way too much stuff on one page.  It has potential for a package index page though.
     * >  For example:  class_methods( Kohana::list_files('classes/sprig') ) could make a nice index page for the sprig package in the api browser
     * >     ~bluehawk
     *
     */
    public static function class_methods( array $list = NULL )
    {
        $list = Docs::classes( $list );

        $classes = array();

        foreach ( $list as $class )
        {
            $_class = new ReflectionClass( $class );

            if ( stripos( $_class->name, 'MyQEE' ) === 0 )
            {
                // Skip the extension stuff stuff
                continue;
            }

            $methods = array();

            foreach ( $_class->getMethods() as $_method )
            {
                $declares = $_method->getDeclaringClass()->name;

                if ( stripos( $declares, 'MyQEE' ) === 0 )
                {
                    // Remove "Kohana_"
                    $declares = substr( $declares, 7 );
                }

                if ( $declares === $_class->name )
                {
                    $methods[] = $_method->name;
                }
            }

            sort( $methods );

            $classes[$_class->name] = $methods;
        }

        return $classes;
    }

    /**
     * Parse a comment to extract the description and the tags
     *
     * @param   string  the comment retreived using ReflectionClass->getDocComment()
     * @return  array   array(string $description, array $tags)
     */
    public static function parse( $comment )
    {
        // Normalize all new lines to \n
        $comment = str_replace( array( "\r\n", "\n" ), "\n", $comment );

        // Remove the phpdoc open/close tags and split
        $comment = array_slice( explode( "\n", $comment ), 1, - 1 );

        // Tag content
        $tags = array();

        foreach ( $comment as $i => $line )
        {
            // Remove all leading whitespace
            $line = preg_replace( '/^\s*\* ?/m', '', $line );

            // Search this line for a tag
            if ( preg_match( '/^@(\S+)(?:\s*(.+))?$/', $line, $matches ) )
            {
                // This is a tag line
                unset( $comment[$i] );

                $name = $matches[1];
                $text = isset( $matches[2] ) ? $matches[2] : '';

                switch ( $name )
                {
                    case 'license' :
                        if ( strpos( $text, '://' ) !== FALSE )
                        {
                            // Convert the lincense into a link
                            $text = HTML::anchor( $text );
                        }
                        break;
                    case 'link' :
                        $text = preg_split( '/\s+/', $text, 2 );
                        $text = HTML::anchor( $text[0], isset( $text[1] ) ? $text[1] : $text[0] );
                        break;
                    case 'copyright' :
                        if ( strpos( $text, '(c)' ) !== FALSE )
                        {
                            // Convert the copyright sign
                            $text = str_replace( '(c)', '&copy;', $text );
                        }
                        break;
                    case 'throws' :
                        if ( preg_match( '/^(\w+)\W(.*)$/', $text, $matches ) )
                        {
                            $text = HTML::anchor( Docs::url( $matches[1] ), $matches[1] ) . ' ' . $matches[2];
                        }
                        else
                        {
                            $text = HTML::anchor( Docs::url( $text ), $text );
                        }
                        break;
                    case 'uses' :
                        if ( preg_match( '/^([a-z_]+)::([a-z_]+)$/i', $text, $matches ) )
                        {
                            // Make a class#method API link
                            $text = HTML::anchor( Docs::url( $matches[1] ) . '#' . $matches[2], $text ); //->uri(array('class' => $matches[1])).'#'.$matches[2], $text);
                        }
                        break;
                    // Don't show @access lines, they are shown elsewhere
                    case 'access' :
                        continue 2;
                }

                // Add the tag
                $tags[$name][] = $text;
            }
            else
            {
                // Overwrite the comment line
                $comment[$i] = ( string ) $line;
            }
        }

        // Concat the comment lines back to a block of text
        if ( $comment = trim( implode( "\n", $comment ) ) )
        {
            // Parse the comment with Markdown
            $comment = Markdown( $comment );
        }

        return array( $comment, $tags );
    }

    /**
     * Get the source of a function
     *
     * @param  string   the filename
     * @param  int      start line?
     * @param  int      end line?
     */
    public static function source( $file, $start, $end )
    {
        if ( ! $file )
        {
            return FALSE;
        }

        $file = file( $file, FILE_IGNORE_NEW_LINES );

        $file = array_slice( $file, $start - 1, $end - $start + 1 );

        if ( preg_match( '/^(\s+)/', $file[0], $matches ) )
        {
            $padding = strlen( $matches[1] );

            foreach ( $file as & $line )
            {
                $line = substr( $line, $padding );
            }
        }

        return implode( "\n", $file );
    }

    /**
     * Test whether a class should be shown, based on the api_packages config option
     *
     * @param  Docs_Class  the class to test
     * @return  bool  whether this class should be shown
     */
    public static function show_class( Docs_Class $class )
    {
        $api_packages = Core::config( 'userguide.api_packages' );

        // If api_packages is true, all packages should be shown
        if ( $api_packages === TRUE ) return TRUE;

        // Get the package tags for this class (as an array)
        $packages = Arr::get( $class->tags, 'package', Array( 'None' ) );

        $show_this = FALSE;

        // Loop through each package tag
        foreach ( $packages as $package )
        {
            // If this package is in the allowed packages, set show this to true
            if ( in_array( $package, explode( ',', $api_packages ) ) ) $show_this = TRUE;
        }

        return $show_this;
    }

    /**
     * Returns an HTML string of debugging information about any number of
     * variables, each wrapped in a "pre" tag:
     *
     * // Displays the type and value of each variable
     * echo Kohana::debug($foo, $bar, $baz);
     *
     * @param   mixed   variable to debug
     * @param   ...
     * @return  string
     */
    public static function debug()
    {
        if ( func_num_args() === 0 ) return;

        // Get all passed variables
        $variables = func_get_args();

        $output = array();
        foreach ( $variables as $var )
        {
            $output[] = Docs::_dump( $var, 1024 );
        }

        return '<pre class="debug">' . implode( "\n", $output ) . '</pre>';
    }

    /**
     * Returns an HTML string of information about a single variable.
     *
     * Borrows heavily on concepts from the Debug class of [Nette](http://nettephp.com/).
     *
     * @param   mixed    variable to dump
     * @param   integer  maximum length of strings
     * @return  string
     */
    public static function dump( $value, $length = 128 )
    {
        return Docs::_dump( $value, $length );
    }

    /**
     * Helper for Kohana::dump(), handles recursion in arrays and objects.
     *
     * @param   mixed    variable to dump
     * @param   integer  maximum length of strings
     * @param   integer  recursion level (internal)
     * @return  string
     */
    protected static function _dump( & $var, $length = 128, $level = 0 )
    {
        if ( $var === NULL )
        {
            return '<small>NULL</small>';
        }
        elseif ( is_bool( $var ) )
        {
            return '<small>bool</small> ' . ($var ? 'TRUE' : 'FALSE');
        }
        elseif ( is_float( $var ) )
        {
            return '<small>float</small> ' . $var;
        }
        elseif ( is_resource( $var ) )
        {
            if ( ($type = get_resource_type( $var )) === 'stream' and $meta = stream_get_meta_data( $var ) )
            {
                $meta = stream_get_meta_data( $var );

                if ( isset( $meta['uri'] ) )
                {
                    $file = $meta['uri'];

                    if ( function_exists( 'stream_is_local' ) )
                    {
                        // Only exists on PHP >= 5.2.4
                        if ( stream_is_local( $file ) )
                        {
                            $file = Core::debug_path( $file );
                        }
                    }

                    return '<small>resource</small><span>(' . $type . ')</span> ' . htmlspecialchars( $file, ENT_NOQUOTES, Core::$charset );
                }
            }
            else
            {
                return '<small>resource</small><span>(' . $type . ')</span>';
            }
        }
        elseif ( is_string( $var ) )
        {
            // Clean invalid multibyte characters. iconv is only invoked
            // if there are non ASCII characters in the string, so this
            // isn't too much of a hit.
            $var = UTF8::clean( $var );

            if ( UTF8::strlen( $var ) > $length )
            {
                // Encode the truncated string
                $str = htmlspecialchars( UTF8::substr( $var, 0, $length ), ENT_NOQUOTES, Core::$charset ) . '&nbsp;&hellip;';
            }
            else
            {
                // Encode the string
                $str = htmlspecialchars( $var, ENT_NOQUOTES, Core::$charset );
            }

            return '<small>string</small><span>(' . strlen( $var ) . ')</span> "' . $str . '"';
        }
        elseif ( is_array( $var ) )
        {
            $output = array();

            // Indentation for this variable
            $space = str_repeat( $s = '    ', $level );

            static $marker = null;

            if ( $marker === null )
            {
                // Make a unique marker
                $marker = uniqid( "\x00" );
            }

            if ( empty( $var ) )
            {
                // Do nothing
            }
            elseif ( isset( $var[$marker] ) )
            {
                $output[] = "(\n$space$s*RECURSION*\n$space)";
            }
            elseif ( $level < 5 )
            {
                $output[] = "<span>(";

                $var[$marker] = TRUE;
                foreach ( $var as $key => & $val )
                {
                    if ( $key === $marker ) continue;
                    if ( ! is_int( $key ) )
                    {
                        $key = '"' . htmlspecialchars( $key, ENT_NOQUOTES, Core::$charset ) . '"';
                    }

                    $output[] = "$space$s$key => " . Docs::_dump( $val, $length, $level + 1 );
                }
                unset( $var[$marker] );

                $output[] = "$space)</span>";
            }
            else
            {
                // Depth too great
                $output[] = "(\n$space$s...\n$space)";
            }

            return '<small>array</small><span>(' . count( $var ) . ')</span> ' . implode( "\n", $output );
        }
        elseif ( is_object( $var ) )
        {
            // Copy the object as an array
            $array = ( array ) $var;

            $output = array();

            // Indentation for this variable
            $space = str_repeat( $s = '    ', $level );

            $hash = spl_object_hash( $var );

            // Objects that are being dumped
            static $objects = array();

            if ( empty( $var ) )
            {
                // Do nothing
            }
            elseif ( isset( $objects[$hash] ) )
            {
                $output[] = "{\n$space$s*RECURSION*\n$space}";
            }
            elseif ( $level < 10 )
            {
                $output[] = "<code>{";

                $objects[$hash] = TRUE;
                foreach ( $array as $key => & $val )
                {
                    if ( $key[0] === "\x00" )
                    {
                        // Determine if the access is protected or protected
                        $access = '<small>' . ($key[1] === '*' ? 'protected' : 'private') . '</small>';

                        // Remove the access level from the variable name
                        $key = substr( $key, strrpos( $key, "\x00" ) + 1 );
                    }
                    else
                    {
                        $access = '<small>public</small>';
                    }

                    $output[] = "$space$s$access $key => " . Docs::_dump( $val, $length, $level + 1 );
                }
                unset( $objects[$hash] );

                $output[] = "$space}</code>";
            }
            else
            {
                // Depth too great
                $output[] = "{\n$space$s...\n$space}";
            }

            return '<small>object</small> <span>' . get_class( $var ) . '(' . count( $array ) . ')</span> ' . implode( "\n", $output );
        }
        else
        {
            return '<small>' . gettype( $var ) . '</small> ' . htmlspecialchars( print_r( $var, TRUE ), ENT_NOQUOTES, Core::$charset );
        }
    }

    /**
     * Removes application, system, modpath, or docroot from a filename,
     * replacing them with the plain text equivalents. Useful for debugging
     * when you want to display a shorter path.
     *
     * // Displays SYSPATH/classes/kohana.php
     * echo Kohana::debug_path(Kohana::find_file('classes', 'kohana'));
     *
     * @param   string  path to debug
     * @return  string
     */
    public static function debug_path( $file )
    {
        if ( strpos( $file, APPPATH ) === 0 )
        {
            $file = 'APPPATH' . DIRECTORY_SEPARATOR . substr( $file, strlen( APPPATH ) );
        }
        elseif ( strpos( $file, SYSPATH ) === 0 )
        {
            $file = 'SYSPATH' . DIRECTORY_SEPARATOR . substr( $file, strlen( SYSPATH ) );
        }
        elseif ( strpos( $file, MODPATH ) === 0 )
        {
            $file = 'MODPATH' . DIRECTORY_SEPARATOR . substr( $file, strlen( MODPATH ) );
        }
        elseif ( strpos( $file, DOCROOT ) === 0 )
        {
            $file = 'DOCROOT' . DIRECTORY_SEPARATOR . substr( $file, strlen( DOCROOT ) );
        }

        return $file;
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
     * @return  string   source of file
     * @return  FALSE    file is unreadable
     */
    public static function debug_source( $file, $line_number, $padding = 5 )
    {
        if ( ! $file or ! is_readable( $file ) )
        {
            // Continuing will cause errors
            return FALSE;
        }

        // Open the file and set the line position
        $file = fopen( $file, 'r' );
        $line = 0;

        // Set the reading range
        $range = array( 'start' => $line_number - $padding, 'end' => $line_number + $padding );

        // Set the zero-padding amount for line numbers
        $format = '% ' . strlen( $range['end'] ) . 'd';

        $source = '';
        while ( ($row = fgets( $file )) !== FALSE )
        {
            // Increment the line number
            if ( ++ $line > $range['end'] ) break;

            if ( $line >= $range['start'] )
            {
                // Make the row safe for output
                $row = htmlspecialchars( $row, ENT_NOQUOTES, Docs::$charset );

                // Trim whitespace and sanitize the row
                $row = '<span class="number">' . sprintf( $format, $line ) . '</span> ' . $row;

                if ( $line === $line_number )
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
        fclose( $file );

        return '<pre class="source"><code>' . $source . '</code></pre>';
    }

    public static function url( $class, $havedir = false, $haveext = false )
    {
        $url = 'api/' . DOCS_PROJECT .'/';// . ($havedir ? '' : '/' . Docs::_class2url( $class )) . '/' ;
        if ( $havedir )
        {
            list($dir) = explode('/', $class);
        }
        else
        {
            $dir = Docs::_class2url($class);
            $url .= $dir .'/';
        }
        if ( $dir == 'classes' )
        {
            $ext = '.class'.EXT;
            $class = str_replace('_', '/', $class);
        }
        elseif ( $dir == 'models' )
        {
            $ext = '.model'.EXT;
            $class = str_replace('_', '/', $class);
        }
        elseif ( $dir == 'shell' )
        {
            $ext = '.shell'.EXT;
            $class = strtolower($class);
        }
        elseif ( $dir == 'admin' )
        {
            $ext = '.admin'.EXT;
            $class = strtolower($class);
        }
        elseif ( $dir == 'controllers' )
        {
            $ext = '.controller'.EXT;
            $class = strtolower($class);
        }
        elseif ( $dir == 'config' )
        {
            $ext = '.config'.EXT;
        }
        elseif ( $dir == 'i18n' )
        {
            $ext = '.lang';
        }
        if ( $haveext )
        {
            $url .= $class;
        }
        else
        {
            $url .= $class . $ext;
        }
        return Core::url($url);
    }

    protected static function _class2url( &$class )
    {
        $strpos = strpos( $class, '_' );
        $prefix = '';
        if ( $strpos !== false )
        {
            $prefix = strtolower( substr( $class, 0, $strpos ) );
        }
        if ( $prefix == 'i18n' )
        {
            $dir = 'i18n';
            $class = substr( $class, 5 );
        }
        elseif ( $prefix == 'model' )
        {
            $dir = 'models';
            $class = substr( $class, 6 );
        }
        elseif ( $prefix == 'orm' )
        {
            $dir = 'orm';
            # 对ORM做些特殊处理
            # 将ORM_Test_Finder转化成Test_Test.Finder
            # 将ORM_Test_Test2_Finder转化成Test_Test_Test2.Finder
            $class = ltrim( preg_replace( '#^orm(?:_(.*))?_([a-z0-9]+)_([a-z0-9]+)$#i', '$1_$2', $class ), '_' );
        }
        elseif ( $prefix == 'controller' )
        {
            $dir = HttpIO::current_controller()->dir;
            $class = substr( $class, 11 );
        }
        elseif ( $prefix == 'shell' )
        {
            $dir = 'shell';
            $class = substr( $class, 5 );
        }
        else
        {
            $dir = 'classes';
        }

        return $dir;
    }

    public function get_modifier()
    {
        if ( strpos( $this->modifiers, 'protected' ) !== false )
        {
            return 'protected';
        }
        elseif ( strpos( $this->modifiers, 'private' ) !== false )
        {
            return 'private';
        }
        else
        {
            return 'public';
        }
    }

    public function is_static()
    {
        return strpos( $this->modifiers, 'static' ) === false ? false : true;
    }
}