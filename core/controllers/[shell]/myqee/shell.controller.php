<?php

/**
 * SHELL 脚本基础控制器
 *
 * @author jonwang
 *
 */
abstract class Controller_MyQEE_Shell extends Controller
{

    public function action_default()
    {
        $examples = array_diff( get_class_methods( $this ), get_class_methods( __CLASS__ ) );

        # 获取方法的字符串最大长度
        $methods = array();
        $name_max_len = 0;
        foreach ( $examples as $method )
        {
            if ( $method == __FUNCTION__ ) continue;
            if ( strtolower( substr( $method, 0, 7 ) ) == 'action_' )
            {
                $m = substr( $method, 7 );
                $methods[$m] = $m;
                $name_max_len = max( strlen( $m ), $name_max_len );
            }
        }

        $str = '';
        $str_usage = 'Usage: ';
        foreach ( $methods as $method )
        {
            $ref_method = new ReflectionMethod( $this, 'action_' . $method );

            $parameters = $ref_method->getParameters();

            $str_usage .= str_pad( $method, $name_max_len, ' ', STR_PAD_RIGHT );
            $comment = self::_parse_doc_comment( $ref_method->getDocComment() );
            $str .= CRLF . CRLF . '   ' . $method . CRLF . '       comment   : ' . $comment['title'][0] . CRLF . '       parameters: ';

            if ( $parameters )
            {
                $tmpstr = array();
                $tmpparameter = array();
                $i = 0;
                $hava_l = 0;
                foreach ( $parameters as $k => $parameter )
                {
                    $tmpstr[] = '                   $' . $parameter->name . ' ' . $comment['param'][$i];
                    $tmpparameter[$k] = '$' . $parameter->getName();
                    if ( $parameter->isDefaultValueAvailable() )
                    {
                        $hava_l ++;
                        $tmpparameter[$k] = '[' . $tmpparameter[$k] . ' = ' . $parameter->getDefaultValue();
                    }
                    $i ++;
                }
                $str .= trim( implode( CRLF, $tmpstr ) );
                $str_usage .= ' [options] ' . '[' . implode( ', ', $tmpparameter ) . ']';

                if ( $hava_l )
                {
                    for( $i = 0; $i < $hava_l; $i ++ )
                    {
                        $str_usage .= ' ]';
                    }
                }
            }
            else
            {
                $str .= '[no parameter]' . CRLF;
            }
            $str_usage .= CRLF . '           ';

        }
        $str_usage = trim( $str_usage ) . CRLF;

        echo $str_usage, $str;
    }

    protected static function _parse_doc_comment( $comment )
    {
        // Normalize all new lines to \n
        $comment = str_replace( array( "\r\n", "\n" ), "\n", $comment );

        // Remove the phpdoc open/close tags and split
        $comment = array_slice( explode( "\n", $comment ), 1, - 1 );

        // Tag content
        $param = array();

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
                if ( $text && $name == 'param' )
                {
                    // Add the tag
                    $param[] = $text;
                }
                else
                {
                    continue;
                }
            }
            else
            {
                // Overwrite the comment line
                $comment[$i] = ( string ) $line;
            }
        }

        return array( 'title' => $comment, 'param' => $param );
    }

    /**
     * 获取shell命令下参数
     *
     * 与getopt()相似，window下支持
     *
     * @param string $options
     * @param array $global_options
     * @return array
     */
    public static function getopt($options, array $global_options = null)
    {
        $argv = array_slice($_SERVER['argv'], 4);

        $opts_array = explode(':', trim($options,':'));

        $result = array();
        foreach ( $opts_array as $opt )
        {
            $found = false;
            foreach ( $argv as $v )
            {
                if ( $found )
                {
                    if ( isset($result[$opt]) )
                    {
                        if ( !is_array($result[$opt]) )
                        {
                            $result[$opt] = array($result[$opt]);
                        }
                        $result[$opt][] = trim($v);
                    }
                    else
                    {
                        $result[$opt] = trim($v);
                    }
                    $found = false;
                }
                elseif ( $v=='-'.$opt )
                {
                    $found = true;
                }
            }
        }

        if ($global_options)foreach ($global_options as $item)
        {
            if ( in_array('--'.$item, $argv) )
            {
                $result[$item] = false;
            }
        }

        return $result;
    }

    /**
     * 获取用户输入内容
     */
    public function input()
    {
        return trim(fgets(STDIN));
    }

    /**
     * 输出内容，会附加换行符
     */
    public function output($msg)
    {
        echo $msg . CRLF;
    }
}