<?php

/**
 * 字符处理对象
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
abstract class Core_Text
{

    /**
     * @var  array   number units and text equivalents
     */
    public static $units = array
    (
        1000000000 => 'billion',
        1000000    => 'million',
        1000       => 'thousand',
        100        => 'hundred',
        90         => 'ninety',
        80         => 'eighty',
        70         => 'seventy',
        60         => 'sixty',
        50         => 'fifty',
        40         => 'fourty',
        30         => 'thirty',
        20         => 'twenty',
        19         => 'nineteen',
        18         => 'eighteen',
        17         => 'seventeen',
        16         => 'sixteen',
        15         => 'fifteen',
        14         => 'fourteen',
        13         => 'thirteen',
        12         => 'twelve',
        11         => 'eleven',
        10         => 'ten',
        9          => 'nine',
        8          => 'eight',
        7          => 'seven',
        6          => 'six',
        5          => 'five',
        4          => 'four',
        3          => 'three',
        2          => 'two',
        1          => 'one',
    );

    /**
     * Limits a phrase to a given number of words.
     *
     * $text = Text::limit_words($text);
     *
     * @param   string   phrase to limit words of
     * @param   integer  number of words to limit to
     * @param   string   end character or entity
     * @return  string
     */
    public static function limit_words($str, $limit = 100, $end_char = NULL)
    {
        $limit = (int)$limit;
        $end_char = (null===$end_char) ? '…' : $end_char;

        if (trim($str) === '')return $str;

        if ($limit <= 0)return $end_char;

        preg_match('/^\s*+(?:\S++\s*+){1,' . $limit . '}/u', $str, $matches);

        // Only attach the end character if the matched string is shorter
        // than the starting string.
        return rtrim($matches[0]) . ((strlen($matches[0]) === strlen($str)) ? '' : $end_char);
    }

    /**
     * Limits a phrase to a given number of characters.
     *
     * $text = Text::limit_chars($text);
     *
     * @param   string   phrase to limit characters of
     * @param   integer  number of characters to limit to
     * @param   string   end character or entity
     * @param   boolean  enable or disable the preservation of words while limiting
     * @return  string
     * @uses    UTF8::strlen
     */
    public static function limit_chars($str, $limit = 100, $end_char = NULL, $preserve_words = FALSE)
    {
        $end_char = (null===$end_char) ? '…' : $end_char;

        $limit = (int)$limit;

        if (trim($str) === '' || UTF8::strlen($str) <= $limit)return $str;

        if ($limit <= 0)return $end_char;

        if (false===$preserve_words)return rtrim(UTF8::substr($str, 0, $limit)) . $end_char;

        // Don't preserve words. The limit is considered the top limit.
        // No strings with a length longer than $limit should be returned.
        if (!preg_match('/^.{0,' . $limit . '}\s/us', $str, $matches))return $end_char;

        return rtrim($matches[0]) . ((strlen($matches[0]) === strlen($str)) ? '' : $end_char);
    }

    /**
     * Alternates between two or more strings.
     *
     * echo Text::alternate('one', 'two'); // "one"
     * echo Text::alternate('one', 'two'); // "two"
     * echo Text::alternate('one', 'two'); // "one"
     *
     * Note that using multiple iterations of different strings may produce
     * unexpected results.
     *
     * @param   string  strings to alternate between
     * @return  string
     */
    public static function alternate()
    {
        static $i = null;

        if (func_num_args() === 0)
        {
            $i = 0;
            return '';
        }

        $args = func_get_args();
        return $args[($i ++ % count($args))];
    }

    /**
     * Generates a random string of a given type and length.
     *
     *
     * $str = Text::random(); // 8 character random string
     *
     * The following types are supported:
     *
     * alnum
     * :  Upper and lower case a-z, 0-9 (default)
     *
     * alpha
     * :  Upper and lower case a-z
     *
     * hexdec
     * :  Hexadecimal characters a-f, 0-9
     *
     * distinct
     * :  Uppercase characters and numbers that cannot be confused
     *
     * You can also create a custom type by providing the "pool" of characters
     * as the type.
     *
     * @param   string   a type of pool, or a string of characters to use as the pool
     * @param   integer  length of string to return
     * @return  string
     * @uses    UTF8::split
     */
    public static function random($type = NULL, $length = 8)
    {
        if (null===$type)
        {
            // Default is to generate an alphanumeric string
            $type = 'alnum';
        }

        $utf8 = FALSE;

        switch ( $type )
        {
            case 'alnum' :
                $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'alpha' :
                $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'hexdec' :
                $pool = '0123456789abcdef';
                break;
            case 'numeric' :
                $pool = '0123456789';
                break;
            case 'nozero' :
                $pool = '123456789';
                break;
            case 'distinct' :
                $pool = '2345679ACDEFHJKLMNPRSTUVWXYZ';
                break;
            default :
                $pool = (string)$type;
                $utf8 = !UTF8::is_ascii($pool);
                break;
        }

        // Split the pool into an array of characters
        $pool = ($utf8 === true) ? UTF8::str_split($pool, 1) : str_split($pool, 1);

        // Largest pool key
        $max = count($pool) - 1;

        $str = '';
        for($i = 0; $i < $length; $i++)
        {
            // Select a random character from the pool and add it to the string
            $str .= $pool[mt_rand(0, $max)];
        }

        // Make sure alnum strings contain at least one letter and one digit
        if ($type === 'alnum' && $length > 1)
        {
            if (ctype_alpha($str))
            {
                // Add a random digit
                $str[mt_rand(0, $length - 1)] = chr(mt_rand(48, 57));
            }
            elseif ( ctype_digit($str) )
            {
                // Add a random letter
                $str[mt_rand(0, $length - 1)] = chr(mt_rand(65, 90));
            }
        }

        return $str;
    }

    /**
     * Reduces multiple slashes in a string to single slashes.
     *
     * $str = Text::reduce_slashes('foo//bar/baz'); // "foo/bar/baz"
     *
     * @param   string  string to reduce slashes of
     * @return  string
     */
    public static function reduce_slashes($str)
    {
        return preg_replace('#(?<!:)//+#', '/', $str);
    }

    /**
     * Replaces the given words with a string.
     *
     * // Displays "What the #####, man!"
     * echo Text::censor('What the frick, man!', array(
     * 'frick' => '#####',
     * ));
     *
     * @param   string   phrase to replace words in
     * @param   array    words to replace
     * @param   string   replacement string
     * @param   boolean  replace words across word boundries (space, period, etc)
     * @return  string
     * @uses    UTF8::strlen
     */
    public static function censor($str, $badwords, $replacement = '#', $replace_partial_words = TRUE)
    {
        foreach ( (array)$badwords as $key => $badword )
        {
            $badwords[$key] = str_replace('\*', '\S*?', preg_quote((string)$badword));
        }

        $regex = '(' . implode('|', $badwords) . ')';

        if ( $replace_partial_words === FALSE )
        {
            // Just using \b isn't sufficient when we need to replace a badword that already contains word boundaries itself
            $regex = '(?<=\b|\s|^)' . $regex . '(?=\b|\s|$)';
        }

        $regex = '!' . $regex . '!ui';

        if (UTF8::strlen($replacement) == 1)
        {
            $regex .= 'e';
            return preg_replace($regex, 'str_repeat($replacement, UTF8::strlen(\'$1\'))', $str);
        }

        return preg_replace($regex, $replacement, $str);
    }

    /**
     * Finds the text that is similar between a set of words.
     *
     * $match = Text::similar(array('fred', 'fran', 'free'); // "fr"
     *
     * @param   array   words to find similar text of
     * @return  string
     */
    public static function similar(array $words)
    {
        // First word is the word to match against
        $word = current($words);

        for($i = 0, $max = strlen($word); $i < $max; ++$i)
        {
            foreach ($words as $w)
            {
                // Once a difference is found, break out of the loops
                if ( !isset($w[$i]) || $w[$i] !== $word[$i] ) break 2;
            }
        }

        // Return the similar text
        return substr($word, 0, $i);
    }

    /**
     * Converts text email addresses and anchors into links. Existing links
     * will not be altered.
     *
     * echo Text::auto_link($text);
     *
     * [!!] This method is not foolproof since it uses regex to parse HTML.
     *
     * @param   string   text to auto link
     * @return  string
     * @uses    Text::auto_link_urls
     * @uses    Text::auto_link_emails
     */
    public static function auto_link($text)
    {
        // Auto link emails first to prevent problems with "www.domain.com@example.com"
        return Text::auto_link_urls(Text::auto_link_emails($text));
    }

    /**
     * Converts text anchors into links. Existing links will not be altered.
     *
     * echo Text::auto_link_urls($text);
     *
     * [!!] This method is not foolproof since it uses regex to parse HTML.
     *
     * @param   string   text to auto link
     * @return  string
     * @uses    HTML::anchor
     */
    public static function auto_link_urls($text)
    {
        // Find and replace all http/https/ftp/ftps links that are not part of an existing html anchor
        $text = preg_replace_callback('~\b(?<!href="|">)(?:ht|f)tps?://\S+(?:/|\b)~i', 'Text::_auto_link_urls_callback1', $text);

        // Find and replace all naked www.links.com (without http://)
        return preg_replace_callback('~\b(?<!://|">)www(?:\.[a-z0-9][-a-z0-9]*+)+\.[a-z]{2,6}\b~i', 'Text::_auto_link_urls_callback2', $text);
    }

    protected static function _auto_link_urls_callback1($matches)
    {
        return HTML::anchor($matches[0]);
    }

    protected static function _auto_link_urls_callback2($matches)
    {
        return HTML::anchor('http://' . $matches[0], $matches[0]);
    }

    /**
     * Converts text email addresses into links. Existing links will not
     * be altered.
     *
     * echo Text::auto_link_emails($text);
     *
     * [!!] This method is not foolproof since it uses regex to parse HTML.
     *
     * @param   string   text to auto link
     * @return  string
     * @uses    HTML::mailto
     */
    public static function auto_link_emails($text)
    {
        // Find and replace all email addresses that are not part of an existing html mailto anchor
        // Note: The "58;" negative lookbehind prevents matching of existing encoded html mailto anchors
        //       The html entity for a colon (:) is &#58; or &#058; or &#0058; etc.
        return preg_replace_callback('~\b(?<!href="mailto:|58;)(?!\.)[-+_a-z0-9.]++(?<!\.)@(?![-.])[-a-z0-9.]+(?<!\.)\.[a-z]{2,6}\b(?!</a>)~i', 'Text::_auto_link_emails_callback', $text);
    }

    protected static function _auto_link_emails_callback($matches)
    {
        return HTML::mailto($matches[0]);
    }

    /**
     * Automatically applies "p" and "br" markup to text.
     * Basically [nl2br](http://php.net/nl2br) on steroids.
     *
     * echo Text::auto_p($text);
     *
     * [!!] This method is not foolproof since it uses regex to parse HTML.
     *
     * @param   string   subject
     * @param   boolean  convert single linebreaks to <br />
     * @return  string
     */
    public static function auto_p($str, $br = TRUE)
    {
        // Trim whitespace
        if ( ($str = trim($str)) === '' ) return '';

        // Standardize newlines
        $str = str_replace(array("\r\n", "\r"), "\n", $str);

        // Trim whitespace on each line
        $str = preg_replace('~^[ \t]+~m', '', $str);
        $str = preg_replace('~[ \t]+$~m', '', $str);

        // The following regexes only need to be executed if the string contains html
        if ( $html_found = (strpos($str, '<') !== FALSE) )
        {
            // Elements that should not be surrounded by p tags
            $no_p = '(?:p|div|h[1-6r]|ul|ol|li|blockquote|d[dlt]|pre|t[dhr]|t(?:able|body|foot|head)|c(?:aption|olgroup)|form|s(?:elect|tyle)|a(?:ddress|rea)|ma(?:p|th))';

            // Put at least two linebreaks before and after $no_p elements
            $str = preg_replace('~^<' . $no_p . '[^>]*+>~im', "\n$0", $str);
            $str = preg_replace('~</' . $no_p . '\s*+>$~im', "$0\n", $str);
        }

        // Do the <p> magic!
        $str = '<p>' . trim($str) . '</p>';
        $str = preg_replace('~\n{2,}~', "</p>\n\n<p>", $str);

        // The following regexes only need to be executed if the string contains html
        if ( $html_found !== FALSE )
        {
            // Remove p tags around $no_p elements
            $str = preg_replace('~<p>(?=</?' . $no_p . '[^>]*+>)~i', '', $str);
            $str = preg_replace('~(</?' . $no_p . '[^>]*+>)</p>~i', '$1', $str);
        }

        // Convert single linebreaks to <br />
        if ( $br === TRUE )
        {
            $str = preg_replace('~(?<!\n)\n(?!\n)~', "<br />\n", $str);
        }

        return $str;
    }

    /**
     * Returns human readable sizes. Based on original functions written by
     * [Aidan Lister](http://aidanlister.com/repos/v/function.size_readable.php)
     * and [Quentin Zervaas](http://www.phpriot.com/d/code/strings/filesize-format/).
     *
     * echo Text::bytes(filesize($file));
     *
     * @param   integer  size in bytes
     * @param   string   a definitive unit
     * @param   string   the return string format
     * @param   boolean  whether to use SI prefixes or IEC
     * @return  string
     */
    public static function bytes($bytes, $force_unit = NULL, $format = NULL, $si = TRUE)
    {
        // Format string
        $format = (null===$format) ? '%01.2f %s' : (string)$format;

        // IEC prefixes (binary)
        if ( false==$si || false!==strpos($force_unit, 'i'))
        {
            $units = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
            $mod = 1024;
        }
        // SI prefixes (decimal)
        else
        {
            $units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB');
            $mod = 1000;
        }

        // Determine unit to use
        if ( ($power = array_search((string)$force_unit, $units)) === FALSE )
        {
            $power = ($bytes > 0) ? floor(log($bytes, $mod)) : 0;
        }

        return sprintf($format, $bytes / pow($mod, $power), $units[$power]);
    }

    /**
     * Format a number to human-readable text.
     *
     * // Display: one thousand and twenty-four
     * echo Text::number(1024);
     *
     * // Display: five million, six hundred and thirty-two
     * echo Text::number(5000632);
     *
     * @param   integer   number to format
     * @return  string
     * @since   3.0.8
     */
    public static function number($number)
    {
        // The number must always be an integer
        $number = (int)$number;

        // Uncompiled text version
        $text = array();

        // Last matched unit within the loop
        $last_unit = NULL;

        // The last matched item within the loop
        $last_item = '';

        foreach (Text::$units as $unit => $name)
        {
            if ( $number / $unit >= 1 )
            {
                // $value = the number of times the number is divisble by unit
                $number -= $unit * ($value = (int)floor($number / $unit));
                // Temporary var for textifying the current unit
                $item = '';

                if ( $unit < 100 )
                {
                    if ( $last_unit < 100 and $last_unit >= 20 )
                    {
                        $last_item .= '-' . $name;
                    }
                    else
                    {
                        $item = $name;
                    }
                }
                else
                {
                    $item = Text::number($value) . ' ' . $name;
                }

                // In the situation that we need to make a composite number (i.e. twenty-three)
                // then we need to modify the previous entry
                if (empty($item))
                {
                    array_pop($text);

                    $item = $last_item;
                }

                $last_item = $text[] = $item;
                $last_unit = $unit;
            }
        }

        if (count($text) > 1)
        {
            $and = array_pop($text);
        }

        $text = implode(', ', $text);

        if ( isset($and) )
        {
            $text .= ' and ' . $and;
        }

        return $text;
    }

    /**
     * Prevents [widow words](http://www.shauninman.com/archive/2006/08/22/widont_wordpress_plugin)
     * by inserting a non-breaking space between the last two words.
     *
     * echo Text::widont($text);
     *
     * @param   string  text to remove widows from
     * @return  string
     */
    public static function widont($str)
    {
        $str = rtrim($str);
        $space = strrpos($str, ' ');

        if ( $space !== FALSE )
        {
            $str = substr($str, 0, $space) . '&nbsp;' . substr($str, $space + 1);
        }

        return $str;
    }



    /**
     * 等同js脚本里的escape函数
     *
     * @param string $str
     * @param string $encode
     */
    public static function escape($str, $encode = 'UTF-8')
    {
        $encode = strtoupper($encode);
        if ( $encode == 'UTF-8' )
        {
            preg_match_all("/[\xC0-\xE0].|[\xE0-\xF0]..|[\x01-\x7f]+/", $str, $r);
        }
        else
        {
            preg_match_all("/[\x80-\xff].|[\x01-\x7f]+/", $str, $r);
        }
        //prt($r);
        $ar = $r[0];
        foreach ( $ar as $k => $v )
        {
            $ord = ord($v[0]);
            if ( $ord <= 128 )
            {
                $ar[$k] = rawurlencode($v);
            }
            else
            {
                $ar[$k] = "%u" . bin2hex(iconv($encode, "UCS-2BE", $v));
            }
        } //foreach
        return join("", $ar);
    }

    /**
     * 等同js脚本里的unescape函数
     *
     * @param string $str
     * @param string $encode
     */
    public static function unescape($str, $encode = 'UTF-8')
    {
        $encode = strtoupper($encode);
        if ( $encode == 'GBK' || $encode == 'GB2312' )
        {
            $substrStrNum = 2;
        }
        else
        {
            $substrStrNum = 3;
        }
        $str = rawurldecode($str);
        preg_match_all('#%u.{4}|&#x.{4};|&#\d+;|&#\d+?|.+#U', $str, $r);
        $ar = $r[0];
        foreach ($ar as $k => $v)
        {
            if (substr($v, 0, 2) == "%u")
            {
                $ar[$k] = iconv("UCS-2BE", $encode, pack("H4", substr($v, -4)));
            }
            elseif (substr($v, 0, 3) == "&#x")
            {
                $ar[$k] = iconv("UCS-2BE", $encode, pack("H4", substr($v, $substrStrNum, -1)));
            }
            elseif (substr($v, 0, 2) == "&#")
            {
                $ar [$k] = iconv ( "UCS-2BE", $encode, pack ( "n", preg_replace ( '#[^\d]#', '', $v ) ) );
            }
        }

        return join ('', $ar);
    }

    /**
     * 截取文件
     *
     * @param string $str
     * @param int $start
     * @param int $length
     * @param string $encoding
     */
    public static function substr($str, $start, $length = null, $encoding = 'UTF-8')
    {
        if (IS_MBSTRING)
        {
            if (null===$length)
            {
                return mb_substr((string)$str, $start, null, $encoding);
            }
            else
            {
                return mb_substr((string)$str, $start, $length, $encoding);
            }
        }
        else
        {
            if (UTF8::is_ascii($str))return (null===$length) ? substr($str, $start) : substr($str, $start, $length);

            // Normalize params
            $str = (string)$str;
            $strlen = UTF8::strlen($str);
            $start = (int)($start < 0) ? max(0, $strlen + $start) : $start; // Normalize to positive offset
            $length = (null===$length) ? null : (int)$length;

            // Impossible
            if ( $length === 0 or $start >= $strlen or ($length < 0 and $length <= $start - $strlen) ) return '';

            // Whole string
            if ( $start == 0 and ($length === NULL or $length >= $strlen) ) return $str;

            // Build regex
            $regex = '^';

            // Create an offset expression
            if ( $start > 0 )
            {
                // PCRE repeating quantifiers must be less than 65536, so repeat when necessary
                $x = (int)($start / 65535);
                $y = (int)($start % 65535);
                $regex .= ($x == 0) ? '' : '(?:.{65535}){' . $x . '}';
                $regex .= ($y == 0) ? '' : '.{' . $y . '}';
            }

            // Create a length expression
            if ( $length === NULL )
            {
                $regex .= '(.*)'; // No length set, grab it all
            } // Find length from the left (positive length)
            elseif ( $length > 0 )
            {
                // Reduce length so that it can't go beyond the end of the string
                $length = min($strlen - $start, $length);

                $x = (int)($length / 65535);
                $y = (int)($length % 65535);
                $regex .= '(';
                $regex .= ($x == 0) ? '' : '(?:.{65535}){' . $x . '}';
                $regex .= '.{' . $y . '})';
            } // Find length from the right (negative length)
            else
            {
                $x = (int)(- $length / 65535);
                $y = (int)(- $length % 65535);
                $regex .= '(.*)';
                $regex .= ($x == 0) ? '' : '(?:.{65535}){' . $x . '}';
                $regex .= '.{' . $y . '}';
            }

            preg_match('/' . $regex . '/us', $str, $matches);
            return $matches[1];
        }
    }

    /**
     * 使用rc4优化版加密字符串
     *
     * @param string $string
     * @param string $key 不传或null则用默认值
     * @param int $expiry 设置有效期
     */
    public static function rc4_encrypt($string, $key = null, $expiry = 0)
    {
        return Text::rc4($string, null, $key);
    }


    /**
     * 解密使用rc4优化版加密的字符串
     *
     * @param string $string
     */
    public static function rc4_decryption($string, $key = null)
    {
        return Text::rc4($string, true, $key);
    }

    /**
     * 优化版rc4加密解密
     *
     * @param string $string
     * @param string $is_decode
     * @param string $key
     * @param number $expiry
     * @return string
     */
    protected static function rc4($string, $is_decode = true, $key = null, $expiry = 0)
    {
        $ckey_length = 4;
        $key  = md5($key?$key:serialize(Core::config('database')).serialize(Core::config('cache')));
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ($is_decode == true ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

        $cryptkey = $keya.md5($keya.$keyc);
        $key_length = strlen($cryptkey);

        $string = $is_decode == true ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string.$keyb), 0, 16) . $string;
        $string_length = strlen($string);

        $result = '';
        $box = range(0, 255);

        $rndkey = array();
        for($i = 0; $i <= 255; $i++)
        {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }

        for($j = $i = 0; $i < 256; $i++)
        {
            $j       = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp     = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for($a = $j = $i = 0; $i < $string_length; $i++)
        {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        if($is_decode == true)
        {
            if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16))
            {
                return substr($result, 26);
            }
            else
            {
                return '';
            }
        }
        else
        {
            return $keyc . str_replace('=', '', base64_encode($result));
        }
    }

    /**
     * 将一个XML字符串解析成一个数组
     *
     * 如果需要将数组转换成XML字符串，可使用 `Arr::to_xml($arr)` 方法
     *
     * ** 特殊的key **
     *
     *  key            | 说明
     * ----------------|-------------------------------
     *  `@attributes`  | XML里所有的 attributes 都存放在 `@attributes` key里，可自定义参数 `$attribute_key` 修改，设置成true则和标签里的内容合并
     *  `@name`        | 循环数组XML的标签(tag)存放在 `@name` 的key里
     *  `@tdata`       | CDATA内容存放在 `@tdata` 的key里
     *  `@data`        | 如果本来的值是字符串，但是又有 attributes，则内容被转移至 `@data` 的key里
     *
     *
     * **`$url_xml_setting` 参数说明**
     *
     * `$url_xml_setting` 只有当 `$xml_string` 是URL时才生效，可以为一个数字表示缓存时间（秒），
     * 也可以是一个数组，此数组接受4个key，分别是 `timeout`, `config`, `expire` 和 `expire_type`
     *
     *  参数名        |   说明
     * --------------|----------------
     * `timeout`     | 如果没有缓存，直接请求URL时超时时间，默认10秒
     * `config`      | 缓存配置，具体设置请参考 `new Cache($config)' 中 `$config` 配置方法
     * `expire`      | 缓存超时时长
     * `expire_type` | 缓存超时类型
     *
     * 举例：
     *     // 使用默认缓存配置缓存1800秒
     *     print_r(Text::xml_to_array('http://flash.weather.com.cn/wmaps/xml/china.xml', null, null, 1800));
     *
     *     // 使用自定义配置
     *     $st = array
     *     (
     *         'timeout'     => 30,                   // 如果没有缓存，直接请求URL时超时时间
     *         'config'      => 'my_config',          // 缓存配置
     *         'expire'      => 1800,                 // 1800秒
     *         'expire_type' => Cache::TYPE_MAX_AGE,  // 表示使用命中时间类型
     *     );
     *     print_r(Text::xml_to_array('http://flash.weather.com.cn/wmaps/xml/china.xml', null, null, $st));
     *
     * @since 3.0
     * @param string|SimpleXMLElement $xml_string XML字符串，支持http的XML路径，接受 SimpleXMLElement 对象
     * @param string $attribute_key attributes所使用的key，默认 @attributes，设置成 true 则和内容自动合并
     * @param int $max_recursion_depth 解析最高层次，默认25
     * @param int|array $url_xml_setting 如果传入的 `$xml_string` 是URL，则允许缓存的时间或者是缓存配置的array，默认不缓存
     * @return array | false 失败则返回false
     */
    public static function xml_to_array($xml_string, $attribute_key = '@attributes', $max_recursion_depth = 25, $url_xml_setting = 0)
    {
        if (is_string($xml_string))
        {
            if (preg_match('#^http(s)?://#i', $xml_string))
            {
                $timeout = 10;

                if ($url_xml_setting)
                {
                    # 缓存xml
                    $config = Cache::DEFAULT_CONFIG_NAME;

                    if (is_array($url_xml_setting))
                    {
                        $config = $url_xml_setting['config'];
                        if (isset($url_xml_setting['timeout']) && (int)$url_xml_setting['timeout']>0)
                        {
                            $timeout = (int)$url_xml_setting['timeout'];
                        }
                    }

                    $cache    = new Cache($config);
                    $key      = '_url_xml_cache_by_url_' . md5($xml_string);
                    $xml_data = $cache->get($key);
                }
                else
                {
                    $xml_data = null;
                }

                if ($xml_data)
                {
                    $xml_string = $xml_data;
                }
                else
                {
                    $xml_string = HttpClient::factory()->get($xml_string, $timeout)->data();
                    if (!$xml_string)
                    {
                        return false;
                    }

                    if ($url_xml_setting)
                    {
                        # 保存缓存
                        if (is_numeric($url_xml_setting))
                        {
                            $expire      = $url_xml_setting;
                            $expire_type = null;
                        }
                        elseif (is_array($url_xml_setting))
                        {
                            $expire      = $url_xml_setting['expire'];
                            $expire_type = $url_xml_setting['expire_type'];
                        }
                        else
                        {
                            $expire      = 3600;
                            $expire_type = null;
                        }

                        if (isset($cache) && isset($key))
                        {
                            $cache->set($key, $xml_string, $expire, $expire_type);
                        }
                    }
                }
            }
            $xml_object = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);
        }
        elseif (is_object($xml_string) && $xml_string instanceof SimpleXMLElement)
        {
            $xml_object = $xml_string;
        }
        else
        {
            return false;
        }

        if (!$attribute_key)$attribute_key = '@attributes';
        if (null === $max_recursion_depth || false === $max_recursion_depth)$max_recursion_depth = 25;

        return Text::_exec_xml_to_array($xml_object, $attribute_key, 0, $max_recursion_depth);
    }

    protected static function _exec_xml_to_array($xml_object, $attribute_key, $recursion_depth, $max_recursion_depth)
    {
        /**
         * @var $xml_object SimpleXMLElement
         * @var $value SimpleXMLElement
         */
        $rs = array
        (
            '@name' => $xml_object->getName(),
        );

        $attr = get_object_vars($xml_object->attributes());

        if ($attr)
        {
            foreach($attr['@attributes'] as &$tmp_value)
            {
                Text::_format_attribute_value($tmp_value);
            }
            unset($tmp_value);

            if (true===$attribute_key)
            {
                # 合并到一起
                $rs += $attr['@attributes'];
            }
            else
            {
                $rs[$attribute_key] = $attr['@attributes'];
            }
        }
        $tdata = trim("$xml_object");
        if (strlen($tdata)>0)
        {
            $rs['@tdata'] = $tdata;
        }

        $xml_object_var = get_object_vars($xml_object);

        foreach($xml_object as $key => $value)
        {
            $obj_value = $xml_object_var[$key];

            $attr = null;
            if (is_object($value))
            {
                $attr = get_object_vars($value->attributes());

                if ($attr)
                {
                    foreach($attr['@attributes'] as &$tmp_value)
                    {
                        Text::_format_attribute_value($tmp_value);
                    }
                    unset($tmp_value);
                    $attr = $attr['@attributes'];
                }
            }

            if (is_string($obj_value))
            {
                Text::_format_attribute_value($obj_value);

                if ($attr)
                {
                    if (true===$attribute_key)
                    {
                        # 合并到一起
                        $rs[$key] = $attr + array('@data' => $obj_value);
                    }
                    else
                    {
                        $rs[$key] = array
                        (
                            $attribute_key => $attr,
                            '@data'        => $obj_value,
                        );
                    }
                }
                else
                {
                    $rs[$key] = $obj_value;
                }
            }
            else
            {
                if (is_array($obj_value))
                {
                    if ($recursion_depth>0)unset($rs['@name']);
                    $rs[] = Text::_exec_xml_to_array($value, $attribute_key, $recursion_depth + 1, $max_recursion_depth);
                }
                else
                {
                    $rs[$key] = Text::_exec_xml_to_array($value, $attribute_key, $recursion_depth + 1, $max_recursion_depth);
                    if (is_array($rs[$key]) && !isset($rs[$key][0]))
                    {
                        unset($rs[$key]['@name']);
                    }
                }
            }
        }

        return $rs;
    }

    protected static function _format_attribute_value(& $tmp_value)
    {
        switch ($tmp_value)
        {
            case 'true':
                $tmp_value = true;
                break;
            case 'false':
                $tmp_value = false;
                break;
            case 'null':
                $tmp_value = null;
                break;
            default:
                $tmp_value = trim($tmp_value);
        }
    }

    /**
     * 获取一个google身份验证器数字
     *
     * @param $key
     * @param int $otp_length
     * @return int
     */
    public static function google_auth_code($key, $counter = null, $otp_length = 6)
    {
        if (!$counter)
        {
            $counter = self::_get_timestamp();
        }
        else
        {
            $counter = (int)$counter;
        }

        return self::_oath_hotp(self::_base32_decode(str_replace(' ', '', $key)), $counter, $otp_length);
    }

    /**
     * 验证google身份验证器代码
     *
     * @param $b32seed
     * @param $key
     * @param int $window 左右浮动窗口期，如果设置成0则表示必须当前窗口期内的验证成功
     * @param bool $counter 0 表示基于时间验证，如果设置成>0的数字表示基于计数验证
     * @return bool
     */
    public static function google_auth_code_verify($b32seed, $key, $window = 4, $counter = null)
    {
        if ($counter)
        {
            $time_stamp = (int)$counter;
        }
        else
        {
            $time_stamp = self::_get_timestamp();
        }

        $binary_seed = self::_base32_decode(str_replace(' ', '', $b32seed));

        for ($ts = $time_stamp - $window; $ts <= $time_stamp + $window; $ts++)
        {
            if (self::_oath_hotp($binary_seed, $ts, strlen($key)) == $key)
            {
                return true;
            }
        }

        return false;
    }

    protected static function _oath_hotp($key, $counter, $otp_length)
    {
        if (strlen($key) < 8)
        {
            throw new Exception('Secret key is too short. Must be at least 16 base 32 characters');
        }

        $bin_counter = pack('N*', 0) . pack('N*', $counter);        // Counter must be 64-bit int
        $hash        = hash_hmac ('sha1', $bin_counter, $key, true);

        $offset = ord($hash[19]) & 0xf;
        $truncate = (
                ((ord($hash[$offset+0]) & 0x7f) << 24 ) |
                ((ord($hash[$offset+1]) & 0xff) << 16 ) |
                ((ord($hash[$offset+2]) & 0xff) << 8 ) |
                (ord($hash[$offset+3]) & 0xff)
            ) % pow(10, $otp_length);

        return str_pad($truncate, $otp_length, '0', STR_PAD_LEFT);
    }

    protected static function _get_timestamp()
    {
        return floor(microtime(true)/30);
    }

    protected static function _base32_decode($b32)
    {
        $b32 = strtoupper($b32);

        if (!preg_match('/^[ABCDEFGHIJKLMNOPQRSTUVWXYZ234567]+$/', $b32, $match))
        {
            throw new Exception('Invalid characters in the base32 string.');
        }

        $l      = strlen($b32);
        $n      = 0;
        $j      = 0;
        $binary = '';
        $lut    = array
        (
            'A' => 0,  'B' => 1,
            'C' => 2,  'D' => 3,
            'E' => 4,  'F' => 5,
            'G' => 6,  'H' => 7,
            'I' => 8,  'J' => 9,
            'K' => 10, 'L' => 11,
            'M' => 12, 'N' => 13,
            'O' => 14, 'P' => 15,
            'Q' => 16, 'R' => 17,
            'S' => 18, 'T' => 19,
            'U' => 20, 'V' => 21,
            'W' => 22, 'X' => 23,
            'Y' => 24, 'Z' => 25,
            '2' => 26, '3' => 27,
            '4' => 28, '5' => 29,
            '6' => 30, '7' => 31
        );

        for ($i = 0; $i < $l; $i++)
        {
            $n = $n << 5;
            $n = $n + $lut[$b32[$i]];
            $j = $j + 5;
            if ($j >= 8)
            {
                $j = $j - 8;
                $binary .= chr(($n & (0xFF << $j)) >> $j);
            }
        }

        return $binary;
    }
}
