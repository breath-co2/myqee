<?php

/**
 * 超级管理员ORM
 *
 * @author jonwang
 *
 */
class Library_MyQEE_Administration_ORM_MyQEE_Admin_MemberGroup_Finder extends OOP_ORM_Finder_DB
{
    /**
     * 数据库
     *
     * @var string
     */
    protected $database = Model_Admin::DATABASE;

    /**
     * 群组表名称
     *
     * @var string
     */
    protected $tablename = 'admin_member_group';

    /**
     * 管理员所对应的群组ID表
     *
     * @var string
     */
    protected $ids_tablename = 'admin_member_group_ids';

//     protected static $group_data = array();

//     protected static $group_setting_data = array();

    public function __construct()
    {
        $group_config = Core::config('admin/administrator.group');

        if ($group_config['tablename'])     $this->tablename     = $group_config['tablename'];
        if ($group_config['ids_tablename']) $this->ids_tablename = $group_config['ids_tablename'];

        parent::__construct();
    }

    /**
     * 获取管理员所对应的群组ID表
     *
     * @return string
     */
    public function ids_tablename()
    {
        return $this->ids_tablename;
    }

//     protected static function get_group_data()
//     {
//         ORM_Admin_MemberGroup_Finder::$group_data = $this->db()->order_by('sort','DESC')->get()->as_array('id');
//         $gp_setting = $this->db()->get()->as_array();
//         $st = array();
//         foreach ( $gp_setting as $item )
//         {
//             $st[$item['admin_id']][] = $item;
//         }
//         ORM_Admin_MemberGroup_Finder::$group_setting_data = $st;
//     }

    /**
     * 根据管理员对象获取所在全部组
     *
     * @param ORM_Admin_Member_Data $member
     * @return ORM_Admin_Member_Data
     */
    public function get_all_groups_by_member(ORM_Admin_Member_Data $member)
    {
        if ( $member->is_super_admin )
        {
            # 当前用户为超管，直接返回所有组
            $this->db()->order_by('sort','DESC')->from($this->tablename);
            return $this->find();
        }

        $all_members = $member->get_group_data();
        $this->db()
        ->from     ($this->tablename.' as gp')
        ->select   ('gp.*')
        ->select   ('ids.admin_id as admin_id')
        ->join     ($this->ids_tablename.' as ids')
        ->on       ('ids.group_id', 'gp.id')
        ->order_by ('gp.sort','DESC');

        $ids = array();
        foreach ($all_members as $item)
        {
            $ids[] = $item->id;
        }
        $rs = $this->db()
        ->in('ids.admin_id',$ids)
        ->get()
        ->as_array();

        $rsArr = array();

        # 将获取的数据整理到数组中
        foreach ($rs as $item)
        {
            $admin_id = $item['admin_id'];
            unset($item['admin_id']);
            $rsArr[$admin_id][] = $item;
        }

        foreach ($all_members as $item)
        {
            $groups = $this->create_group_data((array)$rsArr[$item->id],true);
            $item->set_groups($groups);
            if ($item===$member)
            {
                $all_groups = $groups;
            }
        }

        return $all_groups;
    }

    /**
     * 根据管理员对象获取所在全部组
     *
     * @param ORM_Admin_Member_Data $member
     * @return ORM_Admin_Member_Data
     */
    public function get_all_groups_setting_by_member(ORM_Admin_Member_Data $member)
    {
        if ( $member->is_super_admin )
        {
            $rsArr = array();
            # 当前用户为超管，直接返回所有组
            foreach ( $member->groups() as $group )
            {
                $rsArr[$group->id] = array(
                    'view_users'          => 1,
                    'edit_users'          => 1,
                    'edit_users_password' => 1,
                    'add_user'            => 1,
                    'del_user'            => 1,
                    'remove_user'         => 1,
                    'shield_user'         => 1,
                    'liftshield_user'     => 1,
                    'edit_group'          => 1,
                );
            }
            return $rsArr;
        }

        $all_members = $member->get_group_data();
        $this->db()
        ->from     ($this->tablename.' as gp')
        ->select   ('ids.*')
        ->join     ($this->ids_tablename.' as ids')
        ->on       ('ids.group_id', 'gp.id')
        ;

        $ids = array();
        foreach ($all_members as $item)
        {
            $ids[] = $item->id;
        }
        $rs = $this->db()
        ->in('ids.admin_id',$ids)
        ->get()
        ->as_array();

        $rsArr = array();

        # 将获取的数据整理到数组中
        foreach ($rs as $item)
        {
            $admin_id = $item['admin_id'];
            $group_id = $item['group_id'];
            unset($item['admin_id']);
            unset($item['group_id']);
            $rsArr[$admin_id][$group_id] = $item;
        }

        foreach ($all_members as $item)
        {
            $item->set_groups_setting((array)$rsArr[$item->id]);
            if ($item===$member)
            {
                $all_groups_setting = (array)$rsArr[$item->id];
            }
        }

        return $all_groups_setting;
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
     * @param int $member_id
     * @param int $group_id 例如:1
     * @param string $perm_str 例如:view_users
     * @return boolean
     */
    public function is_own_perm_by_member_id_and_group_id($member_id, $group_id, $perm_str)
    {
        static $group_perm_setting = null;

        if ( null===$group_perm_setting )
        {
            $db = $this->db();
            $tmpdata = $db->from( $this->ids_tablename )->get()->as_array();
            $group_perm_setting = array();
            foreach ($tmpdata as $item)
            {
                $group_perm_setting[$item['admin_id'].'_'.$item['group_id']] = $item;
            }
        }

        if ( isset($group_perm_setting[$member_id.'_'.$group_id][$perm_str]) && $group_perm_setting[$member_id.'_'.$group_id][$perm_str]==1 )
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}

/**
 * 超级管理员ORM查询结果集
 *
 * @author jonwang
 *
 */
class Library_MyQEE_Administration_ORM_MyQEE_Admin_MemberGroup_Result extends OOP_ORM_Result
{
    /**
     * 获取所在组所有ID
     *
     * @return array
     */
    public function ids()
    {
        $ids = array();
        foreach ($this as $item)
        {
            $ids[] = $item->id;
        }
        return $ids;
    }

    /**
     * 返回用于select的数组
     *
     * @return array
     */
    public function get_array_for_select()
    {
        $arr = array();
        foreach ( $this as $item )
        {
            $arr[$item->id] = $item->group_name;
        }
        return $arr;
    }
}

/**
 * 超级管理员ORM数据
 *
 * @author jonwang
 *
 */
class Library_MyQEE_Administration_ORM_MyQEE_Admin_MemberGroup_Data extends OOP_ORM_Data
{
    /**
     * 权限对象
     *
     * @var Permission
     */
    protected $_permission;

    public $id = array(
        'is_id_field' => true,
    );

    /**
     * 组名称
     *
     * @var string
     */
    public $group_name;

    /**
     * 组说明
     *
     * @var string
     */
    public $group_desc;

    /**
     * 排序
     *
     * @var int
     */
    public $sort;

    /**
     * 所属项目
     *
     * @var string
     */
    public $project = array(
        'field_name' => 'project',
        'is_readonly' => true,
    );

    /**
     * 权限对象
     *
     * @var Permission
     */
    public $perm_setting = array(
        'field_name' => 'perm_setting',
        'format' => array(
            'serialize',
        ),
    );

    /**
     * 组设置
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
     * 创建时间
     *
     * @var string
     */
    public $created_time;

    /**
     * 返回用户权限对象
     *
     * @return Permission
     */
    public function perm()
    {
        if ( null===$this->_permission )
        {

            $this->_permission = new Permission($this->perm_setting);
        }

        return $this->_permission;
    }

    /**
     * 返回属于改组所有用户对象
     *
     * @return ORM_Admin_Member_Result
     */
    public function members()
    {
        $orm = new ORM_Admin_Member_Finder();
        return $orm->get_all_members_by_group_id($this->id);
    }
}
