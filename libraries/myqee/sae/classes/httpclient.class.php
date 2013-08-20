<?php

/**
 * HttpClient For SAE
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2012 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class HttpClient extends Ex_HttpClient
{

    /**
     * fsockopen操作类型
     * @var fsock
     */
    const TYPE_SAE = 'SAE';

    /**
     * 默认操作类型
     * @var string $default_type
     */
    protected static $default_type = HttpClient::TYPE_SAE;

}