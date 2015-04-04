<?php

/**
 * PHPRPC 默认控制器
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   MyQEE
 * @package    Core
 * @subpackage Controller
 * @copyright  Copyright (c) 2008-2016 myqee.com
 * @license    http://www.myqee.com/license.html
 */
abstract class Core_Controller_PHPRPC extends Controller_Admin
{
    /**
     * @var PHPRPC_Server
     */
    protected $phprpc;

    public function __call($m, $f)
    {
        if (!$this->phprpc)
        {
            $this->before();
        }
    }

    public function before()
    {
        Core::close_buffers(false);

        $this->phprpc = PHPRPC::server();

        $this->auto_add_action();
        $this->before_start();

        if (IS_DEBUG)
        {
            $this->phprpc->setDebugMode(true);
        }

        $this->phprpc->start();

        parent::before();
    }

    public function show_message($msg, $code = 0, $data = array())
    {

    }

    /**
     * 添加接口
     */
    protected function auto_add_action()
    {
        $examples = array_diff(get_class_methods($this), get_class_methods(__CLASS__));

        foreach ($examples as $method)
        {
            if (strtolower(substr($method, 0, 7)) === 'action_')
            {
                $m = substr($method, 7);
                $this->phprpc->add($method, $this, $m);
            }
        }
    }

    /**
     * 在启动 PHPRPC 服务前会执行的方法
     */
    protected function before_start()
    {

    }
}
