<?php

if (!class_exists('ORM_Member_Data',true))
{
    class ORM_Member_Data extends OOP_ORM_Data
    {

    }
}

/**
 * 用户基础类
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2016 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Core_Member extends ORM_Member_Data
{
    /**
     * 定义此对象的ORM基础名称为Member
     *
     * @var string
     */
    protected $_orm_name = 'Member';

    /**
     * 用户权限对象
     *
     * @var Permission
     */
    protected $_permission;

    /**
     * 用户ID
     *
     * @var int
     */
    public $id = array
    (
        'field_name' => 'id',
        'is_id_field' => true,
    );

    /**
     * 用户名
     *
     * @var string
     */
    public $username;

    /**
     * 当前用户密码（通常都是加密后的内容）
     *
     * @var string
     */
    public $password;

    /**
     * 电子邮件
     *
     * @var string
     */
    public $email;

    /**
     * 当前用户随机码
     *
     * @var string
     */
    public $rand_code;

    /**
     * 用户自定义权限
     *
     * 请使用$this->perm()方法获取对象
     *
     * @var array
     */
    public $perm_setting = array
    (
        'field_name' => 'perm',
        'format' => array
        (
            'serialize',
        ),
    );

    /**
     * 检查密码是否正确
     *
     * @param string $password
     */
    public function check_password($password)
    {
        if ($this->get_password_hash($password) === $this->password)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 获取一个新的密码hash值
     *
     * @param string $password
     * @return string
     */
    protected function get_password_hash($password)
    {
        $rand_code = $this->rand_code ? $this->rand_code : '||$34#@_';

        return md5($this->username . $rand_code . $password);
    }

    /**
     * 修改密码
     *
     * @param string $new_password
     * @return $this
     */
    public function change_password($new_password)
    {
        $this->rand_code = Text::random('alnum', 32);
        $this->password  = $this->get_password_hash($new_password);

        return $this;
    }

    /**
     * 返回用户权限对象
     *
     * @return Permission
     */
    public function perm()
    {
        if (null===$this->_permission)
        {
            $this->_permission = new Permission($this->perm_setting);
        }

        return $this->_permission;
    }

    /**
     * 返回数值
     *
     * @return array
     */
    public function as_array()
    {
        $arr = parent::as_array();
        unset($arr['password']);
        unset($arr['rand_code']);
        unset($arr['perm_setting']);

        return $arr;
    }
}