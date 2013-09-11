<?php

/**
 * HttpClient返回对象
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Module
 * @package    HttpClient
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Module_HttpClient_Result
{

    protected $data;

    protected $code = 0;

    protected $headers = array();

    protected $cookies = array();

    protected $time = 0;

    public function __construct($data)
    {
        if ( isset($data['code']) ) $this->code = $data['code'];
        if ( isset($data['time']) ) $this->time = $data['time'];
        if ( isset($data['data']) ) $this->data = $data['data'];

        if ( isset($data['header']) && is_array($data['header']) ) foreach ( $data['header'] as $item )
        {
            if ( preg_match('#^([a-zA-Z0-9\-]+): (.*)$#', $item, $m) )
            {
                if ( $m[1] == 'Set-Cookie' )
                {
                    if ( preg_match('#^([a-zA-Z0-9\-_]+)=(.*)$#', $m[2], $m2) )
                    {
                        $this->cookies[$m2[1]] = $m2[2];
                    }
                }
                else
                {
                    $this->headers[$m[1]] = $m[2];
                }
            }
        }
    }

    public function __toString()
    {
        return (string)$this->data();
    }

    public function code()
    {
        return $this->code;
    }

    public function data()
    {
        return $this->data;
    }

    public function time()
    {
        return $this->time;
    }

    public function header($key = null)
    {
        if ( null === $key )
        {
            return $this->headers;
        }
        else
        {
            return $this->headers[$key];
        }
    }

    public function cookie($key = null)
    {
        if ( null === $key )
        {
            return $this->cookies;
        }
        else
        {
            return $this->cookies[$key];
        }
    }
}