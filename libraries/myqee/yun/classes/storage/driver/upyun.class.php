<?php

/**
 * 又拍云Storage驱动器
 *
 * @see        http://www.upyun.com/
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2016 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Library_MyQEE_Yun_Storage_Driver_UpYun extends Storage_Driver
{
    /**
     * UpYun实例化对象
     *
     * @var UpYun
     */
    protected $upyun;

    /**
     * @param string $config_name 配置名或数组
     */
    public function __construct($config_name = 'default')
    {
        static $run = null;
        if (null === $run)
        {
            $this->load_upyun();

            if (!class_exists('UpYun', false))
            {
                throw new Exception(__('Can not found UpYun SDK'));
            }
        }

        if (is_array($config_name))
        {
            $config = $config_name;
        }
        else
        {
            $config = Core::config('storage/upyun.' . $config_name);
        }

        $this->upyun = new UpYun($config['bucket'], $config['username'], $config['password']);
    }

    /**
     * 加载upyun SDK
     */
    protected function load_upyun()
    {
        Core::find_file('sdk/upyun', 'upyun', 'class.php', true);
    }

    /**
     * 取得数据
     *
     * @param string/array $key
     * @return mixed
     */
    public function get($key)
    {
        if (is_array($key))
        {
            $rs = array();
            foreach ($key as $k)
            {
                $rs[$k] = $this->get($k);
            }

            return $rs;
        }

        $rs = $this->get_response($key, 'GET');

        if ($rs['code']>=200 && $rs['code']<300)
        {
            $this->_de_format_data($rs['body']);

            return $rs['body'];
        }

        if ($rs['code']==404)return null;

        throw new Exception(__('UpYun get error, code: :code.', array(':code'=>$rs['code'])));
    }

    /**
     * 存数据
     *
     * @param string/array $key 支持多存
     * @param string $value 多存时此项可空
     * @return boolean
     */
    public function set($key, $value = null)
    {
        if (is_array($key))
        {
            $rs = true;
            foreach ($key as $k=>$v)
            {
                if (!$this->set($k, $v))
                {
                    $rs = false;
                }
            }

            return $rs;
        }

        $this->_format_data($value);

        $rs = $this->get_response($key, 'PUT', null, null, $value);

        if ($rs['code']>=200 && $rs['code']<300)return true;

        throw new Exception(__('UpYun get error, code: :code.', array(':code'=>$rs['code'])));
    }

    /**
     * 删除指定key的缓存，若$key===true则表示删除全部
     *
     * @param string $key
     */
    public function delete($key)
    {
        if (is_array($key))
        {
            $rs = true;
            foreach ($key as $k)
            {
                if (!$this->delete($k))
                {
                    $rs = false;
                }
            }

            return $rs;
        }

        $rs = $this->get_response('*', 'DELETE');

        if ($rs['code']>=200 && $rs['code']<300)return true;
        if ($rs['code']==404)return true;

        throw new Exception(__('UpYun get error, code: :code.', array(':code'=>$rs['code'])));
    }

    /**
     * 删除全部
     *
     * @return boolean
     */
    public function delete_all()
    {
        //TODO 暂不支持

        return false;
    }

    /**
     * 设置前缀
     *
     * @param string $prefix
     * @return $this
     */
    public function set_prefix($prefix)
    {
        if ($prefix)
        {
            $this->prefix = trim($prefix, ' /_');
        }
        else
        {
            $this->prefix = 'default';
        }

        return $this;
    }
}