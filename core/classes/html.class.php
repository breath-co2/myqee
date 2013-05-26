<?php

/**
 * HTML输出核心类
 *
 * @author     jonwang(jonwang@myqee.com)
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Core_HTML
{

    /**
     * @var  array  preferred order of attributes
     */
    public static $attribute_order = array
    (
        'action',
        'method',
        'type',
        'id',
        'name',
        'value',
        'href',
        'src',
        'width',
        'height',
        'cols',
        'rows',
        'size',
        'maxlength',
        'rel',
        'media',
        'accept-charset',
        'accept',
        'tabindex',
        'accesskey',
        'alt',
        'title',
        'class',
        'style',
        'selected',
        'checked',
        'readonly',
        'disabled'
    );

    /**
     * @var  boolean  automatically target external URLs to a new window?
     */
    public static $windowed_urls = false;

    /**
     * Convert special characters to HTML entities. All untrusted content
     * should be passed through this method to prevent XSS injections.
     *
     *     echo HTML::chars($username);
     *
     * @param   string   string to convert
     * @param   boolean  encode existing entities
     * @return  string
     */
    public static function chars($value, $double_encode = true)
    {
        return @htmlspecialchars((string)$value, ENT_QUOTES, Core::$charset, $double_encode);
    }

    /**
     * Convert all applicable characters to HTML entities. All characters
     * that cannot be represented in HTML with the current character set
     * will be converted to entities.
     *
     *     echo HTML::entities($username);
     *
     * @param   string   string to convert
     * @param   boolean  encode existing entities
     * @return  string
     */
    public static function entities($value, $double_encode = true)
    {
        return @htmlentities((string)$value, ENT_QUOTES, Core::$charset, $double_encode);
    }

    /**
     * Create HTML link anchors. Note that the title is not escaped, to allow
     * HTML elements within links (images, etc).
     *
     *     echo HTML::anchor('/user/profile', 'My Profile');
     *
     * @param   string  URL or URI string
     * @param   string  link text
     * @param   array   HTML anchor attributes
     * @param   string  use a specific protocol
     * @return  string
     * @uses	URL::base
     * @uses	URL::site
     * @uses	HTML::attributes
     */
    public static function anchor($uri, $title = null, array $attributes = null, $protocol = null)
    {
        if ( $title === null )
        {
            // Use the URI as the title
            $title = $uri;
        }
        if ( $uri === '' )
        {
            // Only use the base URL
            $uri = Core::url()->base(false, $protocol);
        }
        else
        {
            if ( strpos($uri, '://') !== false )
            {
                if ( HTML::$windowed_urls === true && empty($attributes['target']) )
                {
                    // Make the link open in a new window
                    $attributes['target'] = '_blank';
                }
            }
            elseif ( $uri[0] !== '#' && $uri[0] != '/' )
            {
                // Make the URI absolute for non-id anchors
                $uri = Core::url($uri, $protocol);
            }
        }
        // Add the sanitized link to the attributes
        $attributes['href'] = $uri;
        return '<a' . HTML::attributes($attributes) . '>' . $title . '</a>';
    }

    /**
     * Creates an HTML anchor to a file. Note that the title is not escaped,
     * to allow HTML elements within links (images, etc).
     *
     *     echo HTML::file_anchor('media/doc/user_guide.pdf', 'User Guide');
     *
     * @param   string  name of file to link to
     * @param   string  link text
     * @param   array   HTML anchor attributes
     * @param   string  non-default protocol, eg: ftp
     * @return  string
     * @uses	URL::base
     * @uses	HTML::attributes
     */
    public static function file_anchor($file, $title = null, array $attributes = null, $protocol = null)
    {
        if ( $title === null )
        {
            // Use the file name as the title
            $title = basename($file);
        }
        // Add the file link to the attributes
        $attributes['href'] = Core::url()->base(false, $protocol) . $file;
        return '<a' . HTML::attributes($attributes) . '>' . $title . '</a>';
    }

    /**
     * Generates an obfuscated version of a string. Text passed through this
     * method is less likely to be read by web crawlers and robots, which can
     * be helpful for spam prevention, but can prevent legitimate robots from
     * reading your content.
     *
     *     echo HTML::obfuscate($text);
     *
     * @param   string  string to obfuscate
     * @return  string
     * @since   3.0.3
     */
    public static function obfuscate($string)
    {
        $safe = '';
        foreach ( str_split($string) as $letter )
        {
            switch ( rand(1, 3) )
            {
                // HTML entity code
                case 1 :
                    $safe .= '&#' . ord($letter) . ';';
                    break;
                // Hex character code
                case 2 :
                    $safe .= '&#x' . dechex(ord($letter)) . ';';
                    break;
                // Raw (no) encoding
                case 3 :
                    $safe .= $letter;
            }
        }
        return $safe;
    }

    /**
     * Generates an obfuscated version of an email address. Helps prevent spam
     * robots from finding email addresses.
     *
     *     echo HTML::email($address);
     *
     * @param   string  email address
     * @return  string
     * @uses	HTML::obfuscate
     */
    public static function email($email)
    {
        // Make sure the at sign is always obfuscated
        return str_replace('@', '&#64;', HTML::obfuscate($email));
    }

    /**
     * Creates an email (mailto:) anchor. Note that the title is not escaped,
     * to allow HTML elements within links (images, etc).
     *
     *     echo HTML::mailto($address);
     *
     * @param   string  email address to send to
     * @param   string  link text
     * @param   array   HTML anchor attributes
     * @return  string
     * @uses	HTML::email
     * @uses	HTML::attributes
     */
    public static function mailto($email, $title = null, array $attributes = null)
    {
        // Obfuscate email address
        $email = HTML::email($email);
        if ( $title === null )
        {
            // Use the email address as the title
            $title = $email;
        }
        return '<a href="&#109;&#097;&#105;&#108;&#116;&#111;&#058;' . $email . '"' . HTML::attributes($attributes) . '>' . $title . '</a>';
    }

    /**
     * Creates a style sheet link element.
     *
     *     echo HTML::style('media/css/screen.css');
     *
     * @param   string  file name
     * @param   array   default attributes
     * @param   boolean  include the index page
     * @return  string
     * @uses	URL::base
     * @uses	HTML::attributes
     */
    public static function style($file, array $attributes = null, $index = false)
    {
        if ( strpos($file, '://') === false )
        {
            // Add the base URL
            $file = Core::url()->base($index) . $file;
        }
        // Set the stylesheet link
        $attributes['href'] = $file;
        // Set the stylesheet rel
        $attributes['rel'] = 'stylesheet';
        // Set the stylesheet type
        $attributes['type'] = 'text/css';
        return '<link' . HTML::attributes($attributes) . ' />';
    }

    /**
     * Creates a script link.
     *
     *     echo HTML::script('media/js/jquery.min.js');
     *
     * @param   string   file name
     * @param   array	default attributes
     * @param   boolean  include the index page
     * @return  string
     * @uses	URL::base
     * @uses	HTML::attributes
     */
    public static function script($file, array $attributes = null, $index = false)
    {
        if ( strpos($file, '://') === false )
        {
            // Add the base URL
            $file = Core::url()->base($index) . $file;
        }
        // Set the script link
        $attributes['src'] = $file;
        // Set the script type
        $attributes['type'] = 'text/javascript';
        return '<script' . HTML::attributes($attributes) . '></script>';
    }

    /**
     * Creates a image link.
     *
     *     echo HTML::image('media/img/logo.png', array('alt' => 'My Company'));
     *
     * @param   string   file name
     * @param   array	default attributes
     * @return  string
     * @uses	URL::base
     * @uses	HTML::attributes
     */
    public static function image($file, array $attributes = null, $index = false)
    {
        if ( strpos($file, '://') === false )
        {
            // Add the base URL
            $file = Core::url()->base($index) . $file;
        }
        // Add the image link
        $attributes['src'] = $file;
        return '<img' . HTML::attributes($attributes) . ' />';
    }

    /**
     * Compiles an array of HTML attributes into an attribute string.
     * Attributes will be sorted using HTML::$attribute_order for consistency.
     *
     *     echo '<div'.HTML::attributes($attrs).'>'.$content.'</div>';
     *
     * @param   array   attribute list
     * @return  string
     */
    public static function attributes(array $attributes = null)
    {
        if ( empty($attributes) ) return '';
        $sorted = array();
        foreach ( HTML::$attribute_order as $key )
        {
            if ( isset($attributes[$key]) )
            {
                // Add the attribute to the sorted list
                $sorted[$key] = $attributes[$key];
            }
        }
        // Combine the sorted attributes
        $attributes = $sorted + $attributes;
        $compiled = '';
        foreach ( $attributes as $key => $value )
        {
            if ( $value === null )
            {
                // Skip attributes that have null values
                continue;
            }
            // Add the attribute value
            $compiled .= ' ' . $key . '="' . str_replace('"', '&quot;', $value) . '"';
        }
        return $compiled;
    }
}