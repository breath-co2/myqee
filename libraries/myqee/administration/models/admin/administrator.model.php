<?php
/**
 * 超级管理员模块
 *
 * @author jonwang
 *
 */
class Library_MyQEE_Administration_Model_Admin_Administrator extends Model_Admin
{
    /**
     * 管理员设置
     *
     * @var array
     */
    protected $config;

    protected $group_tablename;

    public function __construct()
    {
        $this->config = Core::config('admin/administrator');
        parent::__construct();
    }

    /**
     * 获取管理员数目
     *
     * @param string $project
     * @param array $list_groups 可列出的组，不传则全部
     * @param int $group_id
     * @param int $list_type null=默认，1=管理员，2=组长，3=自定义权限用户
     * @return int
     */
    public function total_count($project = null , $list_groups = null , $list_type=null )
    {
        if ($project)
        {
            $this->db()->where('m.project',Core::$project);
        }

        if ( $list_type==1 )
        {
            $this->db()->where('m.is_super_admin',1);
        }
        elseif ( $list_type==3 )
        {
            $this->db()->where('m.perm_setting','N;','<>');
        }

        $this->db()->from($this->config['tablename'].' as m');
        if ( $list_groups || $list_type==2 )
        {
            if ( $list_groups )
            {
                $this->db()->in('ids.group_id',$list_groups);
            }
            if ( $list_type==2 )
            {
                # 列出组长
                $this->db()->where('ids.view_users',1);
            }

            $data = $this->db()
            ->join( $this->config['group']['ids_tablename'] .' as ids')
            ->on('ids.admin_id', 'm.id')
            ->select( Database::expr_value('count(DISTINCT(m.id)) as `count`') )
            ->get()
            ->current();
            return $data['count'];
        }
        else
        {
            return $this->db()->count_records();
        }
    }


    /**
     * 获取管理员列表数据
     *
     * @param string $project
     * @param array $list_groups 可列出的组，不传则全部
     * @param int $offset
     * @param int $limit
     * @param int $group_id
     * @param int $list_type null=默认，1=管理员，2=组长
     * @return ORM_Admin_Aministrator_Result
     */
    public function get_aministrator_list( $project = null  , $list_groups = null , $offset = 0 , $limit = 20 ,$list_type=null )
    {
        $orm_member = new ORM_Admin_Member_Finder();

        $orm_member->driver()
        ->select('m.*')
        ->from($orm_member->tablename() .' as m')
        ->order_by('m.id','DESC')
        ;

        if ( $project )
        {
            $orm_member->driver()->where('m.project',$project);
        }

        if ( $list_type==1 )
        {
            $this->db()->where('m.is_super_admin',1);
        }
        elseif ( $list_type==3 )
        {
            $this->db()->where('m.perm_setting','N;','<>');
        }

        if ( $list_groups || $list_type==2 )
        {
            if ($list_groups)
            {
                $orm_member->driver()->in('ids.group_id', $list_groups);
            }

            if ( $list_type==2 )
            {
                # 列出组长
                $this->db()->where('ids.view_users',1);
            }

            $orm_member->driver()
            ->distinct(true)
            ->join( $this->config['group']['ids_tablename'] .' as ids')
            ->on('ids.admin_id', 'm.id')
            ;
        }

        if ( $limit )
        {
            $orm_member->driver()->limit($limit)->offset($offset);
        }

        return $orm_member->find();
    }

    /**
     * 搜索用户
     *
     * @param string $project
     * @param array $list_groups
     * @param string $keyword
     */
    public function search_aministrator_list($project, $list_groups ,$keyword)
    {
        $keyword = '%'.$keyword.'%';
        $orm_member = new ORM_Admin_Member_Finder();

        if ( $project )
        {
            $orm_member->driver()->where('m.project',$project);
        }

        if ( $list_groups )
        {
            $orm_member->driver()
            ->in('ids.group_id', $list_groups)
            ->distinct(true)
            ->join( $this->config['group']['ids_tablename'] .' as ids')
            ->on('ids.admin_id', 'm.id')
            ;
        }

        $orm_member->driver()
        ->select('m.*')
        ->from($orm_member->tablename() .' as m')
        ->order_by('m.id','DESC')
        ->limit(100)
        ->and_where_open()
        ->like('m.username',$keyword)
        ->or_like('m.nickname',$keyword)
        ->and_where_close()
        ;

        return $orm_member->find();
    }

    /**
     * 根据用户ID获取用户数据
     *
     * @param int $member_id
     * @return Member
     */
    public function get_by_id($member_id)
    {
        $orm_member = new ORM_Admin_Member_Finder();
        return $orm_member->get_by_id($member_id);
    }

    /**
     * 根据用户名获取用户对象
     *
     * @param string $username
     * @return Member
     */
    public function get_by_username( $username )
    {
        $orm_member = new ORM_Admin_Member_Finder();
        return $orm_member->where('username',$username)->find(null,true)->current();
    }

    /**
     * 获取管理员数目
     */
    public function total_group_count($project=null)
    {
        if ($project)
        {
            $this->db()->where('project',Core::$project);
        }
        return $this->db()->count_records( $this->config['group']['tablename'] );
    }

    /**
     * 获取管理组列表数据
     *
     * @param int $offset
     * @param int $limit
     * @return ORM_Admin_MemberGroup_Result
     */
    public function get_group_list( $project = null , $offset = 0 , $limit = 20)
    {
        $orm_group = new ORM_Admin_MemberGroup_Finder();

        if ($project)
        {
            $orm_group->driver()->where('project',$project);
        }
        if ( $limit )
        {
            $orm_group->driver()
            ->limit($limit)
            ->offset($offset);
        }

        $orm_group->driver()->from($this->config['group']['tablename'])->order_by('sort','DESC');

        return $orm_group->find();
    }

}