<?php

/**
 * 超级管理员ORM
 *
 * @author jonwang
 *
 */
class Library_MyQEE_Administration_ORM_MyQEE_Admin_Member_Finder extends OOP_ORM_Finder_DB
{
    protected $database = Model_Admin::DATABASE;

    protected $tablename = 'admin_member';

    public function __construct()
    {
        $tablename = Core::config('admin/administrator.tablename');
        if ($tablename) $this->tablename = $tablename;

        parent::__construct();
    }

    /**
     * 获取指定组所有的用户
     *
     * @return ORM_Admin_Member_Result
     */
    public function get_all_members_by_group_id($group_id)
    {
        $orm_group = new ORM_Admin_MemberGroup_Finder();
        $this->db()
        ->select('m.*')
        ->from($this->tablename.' as m')
        ->join($orm_group->ids_tablename().' as ids')
        ->on('m.id', 'ids.admin_id')
        ->where('ids.group_id',$group_id)
        ;
        return $this->find();
    }


    public function get_by_name($name)
    {
        $this->db()->or_where_open();
        $this->db()->like('channel_ids','%'.Session::instance()->member()->channel_id().'%');
        $this->db()->or_where('channel_ids','');
        $this->db()->or_where_close();
        return $this
        ->where('nickname',$name)->find()->current();
    }
}

/**
 * 超级管理员ORM查询结果集
 *
 * @author jonwang
 *
 */
class Library_MyQEE_Administration_ORM_MyQEE_Admin_Member_Result extends OOP_ORM_Result
{

}


/**
 * 超管用户基础类
 *
 * @author	   jonwang(jonwang@myqee.com)
 * @category   MyQEE
 * @package	   System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2012 myqee.com
 * @license	   http://www.myqee.com/license.html
 */
class Library_MyQEE_Administration_ORM_MyQEE_Admin_Member_Data extends OOP_ORM_Data
{
    /**
     * 权限对象
     *
     * @var Permission
     */
    protected $_permission;

    /**
     * 用户ID
     *
     * @var int
     */
    public $id = array(
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
     * 昵称
     *
     * @var string
     */
    public $nickname;

    /**
     * 当前用户密码（通常都是加密后的内容）
     *
     * @var string
     */
    public $password;

    /**
     * 当前用户随机码
     *
     * @var string
     */
    public $rand_code;


    /**
     * 所属项目
     *
     * @var string
     */
    public $project;

    /**
     * 用户自定义权限
     *
     * 请使用$this->perm()方法获取对象
     *
     * @var array
     */
    public $perm_setting = array(
        'field_name' => 'perm_setting',
        'format' => array(
            'serialize',
        ),
    );

    /**
     * 是否超级管理员
     *
     * @var boolean
     */
    public $is_super_admin;

    /**
     * 最后登录时间
     *
     * @var int
     */
    public $last_login_time;

    /**
     * 最后登录IP
     *
     * @var string
     */
    public $last_login_ip;

    /**
     * 最后登录的SESSION ID
     *
     * @var string
     */
    public $last_login_session_id;

    /**
     * 登录数
     *
     * @var int
     */
    public $login_num;

    /**
     * 是否已被屏蔽
     *
     * @var int
     */
    public $shielded;

    /**
     * 设置
     *
     * @var array
     */
    public $setting = array(
        'field_name' => 'setting',
        'format' => array(
            'serialize',
        ),
    );

    /**
     * 所有组
     *
     * @var ORM_Admin_MemberGroup_Result
     */
    protected $_groups = null;

    /**
     * 返回所有组的对象集
     *
     * @return ORM_Admin_MemberGroup_Result
     */
    public function groups()
    {
        if ( null!==$this->_groups )return $this->_groups;

        $orm_group = new ORM_Admin_MemberGroup_Finder();
        $this->_groups = $orm_group->get_all_groups_by_member($this);

        return $this->_groups;
    }

    /**
     * 设置管理组
     *
     * @param ORM_Admin_MemberGroup_Result $rs
     * @return ORM_MyQEE_Admin_Member_Data
     */
    public function set_groups(ORM_Admin_MemberGroup_Result $member_group_result)
    {
        $this->_groups = $member_group_result;

        return $this;
    }

    /**
     * 所有组设置
     *
     * @var ORM_Admin_MemberGroup_Result
     */
    protected $_groups_setting = null;

    /**
     * 获取管理组设置
     *
     * @return array
     */
    public function groups_setting()
    {
        if ( null!==$this->_groups_setting )return $this->_groups_setting;

        $orm_group = new ORM_Admin_MemberGroup_Finder();
        $this->_groups_setting = $orm_group->get_all_groups_setting_by_member($this);

        return $this->_groups_setting;
    }

    /**
     * 设置管理组设置
     *
     * @param array $setting
     * @return ORM_MyQEE_Admin_Member_Data
     */
    public function set_groups_setting(array $setting)
    {
        $this->_groups_setting = $setting;

        return $this;
    }

    /**
     * 插入用户数据
     *
     * @see OOP_ORM_Data::insert()
     */
    public function insert()
    {
        # 生成一个加密随机码
        $this->rand_code = $this->_get_password_rand_code();

        # 加密密码
        $this->password = $this->_get_password_hash($this->password);

        return parent::insert();
    }

    /**
     * 检查密码是否正确
     *
     * @param string $password
     */
    public function check_password( $password )
    {
        if ( $this->_get_password_hash($password) == $this->password )
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
    protected function _get_password_hash( $password )
    {
        return md5($this->username . $this->rand_code . $password);
    }

    /**
     * 获取一个随机的密码加密码
     *
     * @return string
     */
    protected function _get_password_rand_code()
    {
        # 重新生成一个随机rand_code
        $str = '~!@#$%^&*()_+`1234567890-=QWERTYUIOP{}|ASDFGHJKL:"ZXCVBNM<>?qwertyuiop[]\\asdfghjkl;\'zxcvbnm,./';
        $count = strlen($str)-1;
        $rand_code = '';
        for ( $i=0;$i<16;$i++ )
        {
            $rand_code .= substr($str,mt_rand(0,$count),1);
        }

        return $rand_code;
    }

    /**
     * 修改密码
     *
     * @param string $new_password
     * @return array 失败返回false
     */
    public function change_password( $new_password )
    {
        # 更新随机码
        $this->rand_code = $this->_get_password_rand_code();

        # 修改密码
        $this->password = $this->_get_password_hash($new_password);

        return $this->update();
    }

    /**
     * 返回用户权限对象
     *
     * @return Permission
     */
    public function perm()
    {
        if ( null===$this->_permission )
        {
            if ( $this->is_super_admin )
            {
                # 超管
                $perm_setting = array(
                	'_super_admin'=>1
                );
            }
            else
            {
                $perm_setting = $this->perm_setting;

                $groups = $this->groups();
                if ( $groups->count() )
                {
                    foreach ( $groups as $group )
                    {
                        # 合并权限
                        $perm_setting = self::_merge_permission($perm_setting,$group->perm_setting);
                    }
                }

                if ( isset($this->setting['_group_admin']) )
                {
                    if ( $this->setting['_group_admin']['can_edit_group'] )
                    {
                        $perm_setting['administrator']['can_edit_group'] = 1;
                    }
                    if ( $this->setting['_group_admin']['can_edit_group'] )
                    {
                        $perm_setting['administrator']['is_group_manager'] = 1;
                    }
                }

                # 删除特殊权限
                if ( isset($perm_setting['_super_admin']) )unset($perm_setting['_super_admin']);
            }

            $this->_permission = new Permission($perm_setting);
        }

        return $this->_permission;
    }

    /**
     * 判断是否拥有的组的相应的操作权限
     *
     * $perm_str包括：
     * group_id 管理组
     * view_users 查看用户
     * edit_users 修改用户
     * edit_users_password 修改用户密码
     * add_user 添加用户
     * del_user 删除用户
     * shield_user 屏蔽用户
     * liftshield_user 解除屏蔽用户
     * edit_group 修改组设置
     *
     * @param string $perm_str 例如:view_users
     * @param int $group_id 例如:1
     * @return boolean
     */
    public function is_own_group_perm( $group_id,$perm_str )
    {
        if ( $this->is_super_admin )return true;

        $orm_group = new ORM_Admin_MemberGroup_Finder();
        return $orm_group->is_own_perm_by_member_id_and_group_id($this->id, $group_id, $perm_str);
    }

    /**
     * 获取拥有相应权限的所有的组
     *
     * $perm_str 参数同 $this->is_own_group_perm()
     *
     * @param string $perm_str
     * @return ORM_Admin_MemberGroup_Result
     */
    public function has_own_perm_groups($perm_str)
    {
        # 超管
        if ( $this->is_super_admin )return $this->groups();

        $orm_group = new ORM_Admin_MemberGroup_Finder();

        $own_groups = new ORM_Admin_MemberGroup_Result();
        foreach ($this->groups() as $item)
        {
            if ( $orm_group->is_own_perm_by_member_id_and_group_id($this->id,$item->id,$perm_str) )
            {
                $own_groups->append($item);
            }
        }

        return $own_groups;
    }

    /**
     * 获取当前用户后台菜单配置
     *
     * @return string
     */
    public function get_menu_config()
    {
        return 'default';

//        $config = $this->default_group->setting['menu_config'];
//        return $config?$config:'default';
    }


    /**
     * 删除当前管理员
     *
     * @see OOP_ORM_Data::delete()
     * @return integer 操作行数
     * @throws Exception
     */
    public function delete()
    {
        $id = $this->id;
        $rs = parent::delete();
        if ($rs)
        {
            # 删除管理员对应的用户组设置
            $orm_group = new ORM_Admin_MemberGroup_Finder();
            $orm_group->db()->where('admin_id',$id)->delete($orm_group->ids_tablename());
        }

        return $rs;
    }

    /**
     * 合并权限
     *
     * @return array
     */
    protected static function _merge_permission( $arr1 , $arr2 )
    {
        if (!is_array($arr1))$arr1 = array();
        if ( !$arr2 || !is_array($arr2) )return $arr1;

        foreach ( $arr2 as $k=>$v )
        {
            if ( is_array($v) )
            {
                $arr1[$k] = self::_merge_permission($arr1[$k],$v);
            }
            else
            {
                $arr1[$k] = $v?true:false;
            }
        }

        return $arr1;
    }
}