<?php

/**
 * 字符串处理类
 *
 * @author     jonwang(jonwang@myqee.com)
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license	   http://www.myqee.com/license.html
 */
class Core_Str
{

    /**
     * 字符串
     *
     * @var string
     */
    protected $data;

    /**
     * 字符串对象
     *
     * @param string $data
     */
    public function __construct($data='')
    {
        $this->data = $data;
    }

    public function __toString()
    {
        return (string)$this->data;
    }

    /**
     * 返回实例化对象
     *
     * @param array $data
     * @return Str
     */
    public static function factory($data='')
    {
        return new Str($data);
    }

    /**
     * 获取字符串
     */
    public function toString()
    {
        return $this->__toString();
    }

    public function addcslashes($charlist)
    {
        return addcslashes($this->data, $charlist);
    }

    /**
     * 截取字段
     *
     * @param int $start
     * @param int $length
     * @param string $encoding
     */
    public function substr($start, $length = null, $encoding = 'UTF-8')
    {
        return Text::substr($this->data , $start , $length , $encoding);
    }

    /**
     * 文本转换为HTML
     * 将处理<>和换行符
     */
    public function text2html()
    {
        return str_replace(array('<', '>', "\r\n", "\r", "\n"), array('&lt;', '&gt;', '<br />', '<br />', '<br />'), $this->data);
    }

    /**
     * 字符串长度
     *
     * @param $encoding 字符编码，默认utf-8
     */
    public function strlen($encoding = 'utf-8')
    {
        return mb_strlen($this->data, $encoding);
    }

    /**
     * 字符串长度
     *
     * @param $encoding 字符编码，默认utf-8
     */
    public function count($encoding = 'utf-8')
    {
        return $this->strlen($encoding);
    }

    /**
     * 追加数据
     *
     * @param string $value
     */
    public function append($value)
    {
        $this->data .= $value;
        return $this;
    }

    public function is_empty()
    {
        return empty($this->data);
    }

    /**
     * 等同js脚本里的escape函数
     *
     * @param string $str
     * @param string $encode
     */
    public function escape($encode = 'UTF-8')
    {
        return Text::escape($this->data, $encode);
    }

    /**
     * 等同js脚本里的unescape函数
     *
     * @param string $encode
     */
    public function unescape($encode = 'UTF-8')
    {
        return Text::unescape($this->data, $encode);
    }

    /**
     * 返回当前字符的拼音
     *
     * @return string
     */
    public function pinyin()
    {
        return PinYin::get($this->data);
    }

    /**
     * 输出字节格式化
     *
     * @param string a definitive unit
     * @param string the return string format
     * @param boolean whether to use SI prefixes or IEC
     * @return string
     */
    public function byte($force_unit = null, $format = null, $si = true)
    {
        return Text::bytes((int)$this->data, $force_unit, $format, $si);
    }

    public static function __set_state($arr)
    {
        return $arr['data'];
    }

}