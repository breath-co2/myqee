<?php

/**
 * 后台Session类
 *
 * @author     jonwang(jonwang@myqee.com)
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Library_MyQEE_Administration_Session extends Core_Session
{
    /**
     * @var ORM_Admin_Member_Data
     */
    protected static $member;

    /**
     * 设置用户
     *
     * @param ORM_Admin_Member_Data $member
     * @return Session
     */
    public function set_member(ORM_Admin_Member_Data $member)
    {
        Session::$member = $member;

        if ($member->id>0)
        {
            # 设置用户数据
            $_SESSION['member']['id']       = $member->id;
            $_SESSION['member']['password'] = $member->password;
        }
        else
        {
            # 游客数据则清空
            unset($_SESSION['member']);
        }

        return $this;
    }

    /**
     * 获取用户对象
     *
     * @return ORM_Admin_Member_Data
     */
    public function member()
    {
        if (null===Session::$member)
        {
            # 创建一个空的用户对象
            Session::$member = new ORM_Admin_Member_Data();
        }
        return Session::$member;
    }

    /**
     * Session在加载时读取用户数据
     */
    protected static function load_member_data()
    {
        if (null===Session::$member && isset($_SESSION['member']['id']) && $_SESSION['member']['id']>0)
        {
            $orm_member = new ORM_Admin_Member_Finder();
            $member = $orm_member->get_by_id($_SESSION['member']['id']);

            if ($member)
            {
                if ($_SESSION['member']['password']!=$member->password)
                {
                    // 在别处修改过密码
                    unset($_SESSION['member']);
                }
                else
                {
                    Session::$member = $member;
                }
            }
        }
    }

    /**
     * Session在关闭时写入用户session数据
     */
    protected static function write_member_data()
    {
        if (Session::$member && Session::$member->id>0)
        {
            # 设置用户数据
            $_SESSION['member']['id']       = Session::$member->id;
            $_SESSION['member']['password'] = Session::$member->password;
        }
    }
}