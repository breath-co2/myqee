<?php
/**
 * 管理员控制器
 *
 * @author jonwang
 *
 */
abstract class Library_MyQEE_Administration_Controller_Administrator_Index extends Controller_Admin
{
    public function action_default()
    {
        $this->quick_menu = array(
            'administrator/add'       => '添加管理员',
            'administrator/group/'    => '权限组列表',
            'administrator/group/add' => '添加权限组',
        );
        $view = new View('admin/administrator/list');

        $model_administrator = new Model_Admin_Administrator();

        $only_list_self_group_user = false;
        $is_super = $this->session()->member()->perm()->is_super_perm();
        if ( $is_super )
        {
            $project = null;
            $list_groups = null;
        }
        else
        {
            $project = Core::$project;
            if ( !$this->session()->member()->perm()->is_own('administrator.view_user_info') )
            {
                $list_groups = $this->session()->member()->has_own_perm_groups('view_users')->ids();
                if ( !count($list_groups) )
                {
                    $this->message('您不具备此权限',-1);
                }
            }
        }

        if ( $_GET['k'] )
        {
            # 搜索模式
            $list = $model_administrator->search_aministrator_list($project, $list_groups , $_GET['k'] );
            $view->list = $list;
        }
        else
        {
            $list_type = null;
            if ( $is_super && $_GET['list'] )
            {
                if ( $_GET['list']=='supadmin' )
                {
                    $list_type = 1;
                }
                elseif ($_GET['list']=='groupmanager')
                {
                    $list_type = 2;
                }
                elseif ($_GET['list']=='zdy')
                {
                    $list_type = 3;
                }
            }
            else
            {
                if (isset($_GET['group_id']))$group_id = (int)$_GET['group_id'];
                if (!$group_id>0)$group_id=0;
                if ( $group_id>0 )
                {
                    if ( !$this->session()->member()->is_own_group_perm($group_id,'view_users') )
                    {
                        $this->message('您不具备查看此权限组用户的权限',-1);
                    }
                    $list_groups = array($group_id);
                }
            }

            $count = $model_administrator->total_count($project , $list_groups , $list_type);

            if ( $count )
            {
                $pageconfig = Core::config('admin/pagination');
                $pageconfig['total_items'] = $count;

                $pagination = new Pagination($pageconfig);

                $list = $model_administrator->get_aministrator_list($project, $list_groups, $pagination->get_offset() , $pagination->get_items_per_page() , $list_type );
                $view->list = $list;
                $view->pagehtml = $pagination->render();
            }
        }

        $view->is_super = $is_super;
        $view->render();
    }

    public function action_change_password( $member_id = 0)
    {
        $this->page_title = '修改密码';

        $member_id = (int)$member_id;

        $msg = '';
        if (HttpIO::METHOD=='POST')
        {
            # 提交数据
            try {

                $this->check_password($_POST);

                $member = $this->session()->member();
                if ( $member_id>0 && $member->id!=$member_id )
                {
                    $model_admin = new Model_Admin_Administrator();
                    $member = $model_admin->get_by_id($member_id);
                    if (!$member)
                    {
                        throw new Exception('指定的用户ID不存在');
                    }
                }

                $this->check_auth_for_edit_password($member);

                if ( $this->do_change_password($member,$_POST) )
                {
                    $msg = '密码修改成功';
                    $code = 1;
                }
            }
            catch (Exception $e)
            {
                $code = $e->getCode();
                $msg = $e->getMessage();
            }

            $this->message($msg,$code);
        }

        try
        {
            if ( !$member_id>0 )
            {
                $member_id = $this->session()->member()->id;
                $member = $this->session()->member();
            }
            else
            {
                $model_admin = new Model_Admin_Administrator();
                $member = $model_admin->get_by_id($member_id);
                if (!$member)
                {
                    throw new Exception('指定的用户ID不存在');
                }
            }

            $this->check_auth_for_edit_password($member);
        }
        catch (Exception $e)
        {
            $code = $e->getCode();
            $msg = $e->getMessage();
            $this->message($msg,$code);
        }

        $view = new View('admin/administrator/change_password');
        $view->member_id = $member_id;
        $view->msg = $msg;
        $view->render();
    }

    public function action_add()
    {
        $this->action_edit();
    }

    public function action_edit( $member_id=0 )
    {
        $this->quick_menu = array(
            'administrator/'		  => '管理员列表',
            'administrator/group/'    => '权限组列表',
            'administrator/group/add' => '添加权限组',
        );
        if ( $member_id>0 )
        {
            $this->page_title = '修改管理员';
        }

        $orm_member = new ORM_Admin_Member_Finder();
        if ( $member_id>0 )
        {
            $member = $orm_member->get_by_id($member_id);

            try
            {
                if (!$member)
                {
                    throw new Exception('指定的ID的用户不存在', -1);
                }

                $this->check_auth_for_edit($member);
            }
            catch (Exception $e)
            {
                $this->message($e->getMessage(),$e->getCode());
            }

            $groups = $this->session()->member()->groups();
        }
        else
        {
            $member = $orm_member->create();

            try
            {
                $this->check_auth_for_add($_POST['group_ids']);
            }
            catch (Exception $e)
            {
                $this->message($e->getMessage(),$e->getCode());
            }

            # 只列出有添加权限的组
            $groups = $this->session()->member()->has_own_perm_groups('add_user');
        }
        if (false)$member = new ORM_Admin_Member_Data();

        if ( $member->id>0 && $member->id==$this->session()->member()->id )
        {
            # 自己
            $this->show_edit_perm = false;
        }
        else if ( $this->session()->member()->perm()->is_super_perm() )
        {
            # 超管
            $this->show_edit_perm = true;
        }
        else
        {
            if ( $member->id>0 )
            {
                if ( $this->check_is_over_perm( $member ) )
                {
                    $over_perm = true;
                }
                else
                {
                    $over_perm = false;
                }
            }
            else
            {
                $over_perm = true;
            }

            if ( $over_perm )
            {
                if ( $member->id>0 && $member->id!=$this->session()->member()->id )
                {
                    if ( !array_diff( $member->groups()->ids(),$groups->ids() ) )
                    {
                        # 超管或者该用户所在的权限组操作者也都在
                        $this->show_edit_perm = true;
                    }
                    else
                    {
                        $this->show_edit_perm = false;
                    }
                }
                else
                {
                    $this->show_edit_perm = true;
                }
            }
            else
            {
                $this->show_edit_perm = false;
            }
        }

        if ( HttpIO::METHOD=='POST' )
        {
            $this->save($member);
        }

        $view = new View('admin/administrator/form');

        $view->member = $member;
        $view->title = $member_id>0?'修改管理员':'添加管理员';
        $view->groups = $groups;
        $view->show_edit_perm = $this->show_edit_perm;

        $view->render();
    }

    public function action_edit_perm_form( $member_id=0 )
    {
        $group_ids = explode( ',', $_GET['group_ids'] );
        $orm_member = new ORM_Admin_Member_Finder();
        if ( $member_id>0 )
        {
            $member = $orm_member->get_by_id($member_id);
        }
        else
        {
            $member = $orm_member->create();
        }

        $orm_group = new ORM_Admin_MemberGroup_Finder();
        $groups = $orm_group->in('id', $group_ids)->find();


        $perm_setting = array();
        foreach ($groups as $item)
        {
            # 合并权限
            $perm_setting = array_merge_recursive($perm_setting,$item->perm_setting);
        }

        $perm = new Permission($perm_setting);

        View::factory('/admin/administrator/perm_form',array('perm'=>$perm,'member'=>$member))->render();
    }

    /**
     * 屏蔽用户
     *
     * @param int $member_id
     */
    public function action_shield( $member_id )
    {
        $orm_member = new ORM_Admin_Member_Finder();
        $member = $orm_member->get_by_id($member_id);

        if (!$member)
        {
            $this->message('指定的用户不存在');
        }

        try {
            $this->check_auth_for_shield( $member );
        }
        catch(Exception $e)
        {
            $this->message($e->getMessage(),$e->getCode());
        }

        $member->shielded = 1;

        $status = $member->update();
        if ($status)
        {
            $this->message('保存成功',1);
        }
        else
        {
            $this->message('未更新数据',0);
        }
    }

    /**
     * 解除屏蔽用户
     *
     * @param int $member_id
     */
    public function action_liftshield( $member_id )
    {
        $orm_member = new ORM_Admin_Member_Finder();
        $member = $orm_member->get_by_id($member_id);

        if (!$member)
        {
            $this->message('指定的用户不存在');
        }

        try
        {
            $this->check_auth_for_liftshield( $member );
        }
        catch(Exception $e)
        {
            $this->message($e->getMessage(),$e->getCode());
        }

        $member->shielded = 0;

        $status = $member->update();
        if ($status)
        {
            $this->message('保存成功',1);
        }
        else
        {
            $this->message('未更新数据',0);
        }
    }

    public function action_delete( $member_id )
    {
        $member_id = (int)$member_id;
        if (!$member_id>0)
        {
            $this->message('参数错误');
        }

        $orm_member = new ORM_Admin_Member_Finder();
        $member = $orm_member->get_by_id($member_id);

        if (!$member)
        {
            $this->message('指定的用户不存在或已被删除');
            if (false)$member = new ORM_Admin_Member_Data();
        }

        try
        {
            $this->check_auth_for_delete( $member );
        }
        catch(Exception $e)
        {
            $this->message($e->getMessage(),$e->getCode());
        }

        $status = $member->delete();

        if ($status)
        {
            $this->message('删除成功' , 1);
        }
        else
        {
            $this->message('未删除数据' , 0);
        }
    }

    /**
     * 检查秘密是否符合要求
     *
     * @param array $data 通常是POST的数据
     */
    protected function check_password($data)
    {
        if (empty($data['new_password']))
        {
            throw new Exception('新密码不能空',-1);
        }
        if (strlen($data['new_password'])<6)
        {
            throw new Exception('新密码太短',-1);
        }
        if ( $data['new_password'] != $data['new_password_2'] )
        {
            throw new Exception('两次输入的密码不一致',-1);
        }
        return true;
    }

    protected function do_change_password(ORM_Admin_Member_Data $member,$data )
    {
        if ( $member->id>0 && $member->id==$this->session()->member()->id )
        {
            # 修改自己的密码
            if ( !$this->session()->member()->check_password($data['old_password']) )
            {
                throw new Exception('旧密码验证失败',-1);
            }
        }

        $status = $member->change_password($data['new_password']);

        if ( !$status )
        {
            throw new Exception('未修改密码');
        }
        else
        {
            return true;
        }
    }

    protected function save(ORM_Admin_Member_Data $member)
    {
        try {

            if (!$member->id>0)
            {
                # 创建新用户
                if ( !$_POST['username'] )
                {
                    throw new Exception('用户名不能空', -1);
                }
                if ( !$_POST['new_password'] )
                {
                    throw new Exception('密码不能空', -1);
                }
                if (  $_POST['new_password'] != $_POST['new_password_2'] )
                {
                    throw new Exception('两次输入的密码不一致，请重新确认', -1);
                }

                $model_admin = new Model_Admin_Administrator();
                if ( $model_admin->get_by_username($_POST['username']) )
                {
                    throw new Exception('此用户名已存在，请换一个', -1);
                }
            }

            $member->nickname = $_POST['nickname'];
            $setting = HttpIO::POST('setting');

            # 修改权限模式
            if ( $this->show_edit_perm )
            {
                # _group_admin 保留项
                if ( isset($setting['_group_admin']) )unset($setting['_group_admin']);
                # 修改权限
                $this->change_member_perm($member);
            }
            elseif ( !$member->id>0 )
            {
                $member->perm_setting = null;
            }

            if ( $setting )
            {
                if ( $member->setting )
                {
                    $member->setting = array_merge($member->setting,$setting);
                }
                else
                {
                    $member->setting = $setting;
                }
            }

            # 修改用户其它信息
            $this->change_member_other_info($member);

            $tr = $member->orm()->db()->transaction();
            $tr->start();
            try
            {
                # 保存数据
                if ( $member->id>0 )
                {
                    $is_add = false;
                    # 修改用户
                    $member->update();
                }
                else
                {
                    $is_add = true;

                    # 设置用户名
                    $member->username = $_POST['username'];

                    # 密码，在更新数据时会由ORM进行加密处理
                    $member->password = $_POST['new_password'];

                    # 所属项目
                    $member->project = Core::$project;

                    # 锁定=否
                    $member->shielded = 0;

                    # 插入用户数据
                    $member->insert();
                }

                if ($this->show_edit_perm)
                {
                    # 保存组权限设置
                    $this->save_member_group_perm($member,$is_add);
                }

                $tr->commit();

                $msg = '操作成功';
                $code = 1;
            }

            catch (Exception $e)
            {
                $tr->rollback();
                throw $e;
            }
        }
        catch (Exception $e)
        {
            $code = $e->getCode();
            $msg = $e->getMessage();
        }

        $this->message($msg,$code);
    }

    /**
     * 修改其他信息（用于第三方扩展）
     *
     * @param ORM_Admin_Member_Data $member
     * @throws Exception
     */
    protected function change_member_other_info(ORM_Admin_Member_Data $member)
    {
        return true;
    }

    /**
     * 修改权限
     *
     * @param ORM_Admin_Member_Data $member
     * @throws Exception
     */
    protected function change_member_perm(ORM_Admin_Member_Data $member)
    {
        if ( $member->id>0 && $member->id==$this->session()->member() )throw new Exception('系统不允许管理员操作自己的权限', -1);

        # 不是自定义的权限，全部清理掉
        if ( $_POST['zdy_perm']!=1 )
        {
            unset($_POST['perm_setting']);
        }

        if ( $this->session()->member()->perm()->is_super_perm() )
        {
            # 超管

            if ( $member->is_super_admin!=$_POST['is_super_admin'] )
            {
                $member->is_super_admin = $_POST['is_super_admin']?1:0;
                if ( $member->is_super_admin )
                {
                    # 标记为设置为超级管理员
                    $this->change_to_super_admin = true;
                }
            }

            $perm_setting = $_POST['perm_setting'];

            if ( isset($member->setting['_group_admin']) )unset($member->setting['_group_admin']);
            if ($_POST['manage_groups'])foreach ($_POST['manage_groups'] as $g=>$setting)
            {
                $gid = substr($g,1);

                if ( $setting['edit_group'] )
                {
                    $member->setting['_group_admin']['can_edit_group'] = 1;
                }

                foreach ($setting as $k=>$s)
                {
                    if ($s)
                    {
                        $member->setting['_group_admin']['is_group_manager'] = 1;
                        break;
                    }
                }
            }

        }
        else
        {
            # 非超管处理

            if ( $member->is_super_admin )
            {
                throw new Exception('您不具备操作此管理员的权限', -1);
            }

            if ( $_POST['is_super_admin'] )
            {
                throw new Exception('您不具备提升管理员为超管的权限', -1);
            }

            if ( $member>0 )
            {
                # 旧组
                $old_groups = $member->groups()->ids();
                asort($old_groups);
            }
            else
            {
                $old_groups = array();
            }

            # 新组
            $new_groups = $_POST['group_ids']?(array)$_POST['group_ids']:array();
            asort($new_groups);

            # 添加的权限
            $new_diff_group = array_diff($new_groups , $old_groups);

            # 删除掉的权限
            $del_diff_group = array_diff($old_groups , $new_groups);

            # 差别的权限
            $diff_group = array_merge($new_diff_group , $del_diff_group);

            # 当前用户的组权限设置
            $my_groups_setting = $this->session()->member()->groups_setting();

            $my_groups = $this->session()->member()->groups()->ids();
            asort($my_groups);

            if ( array_diff($old_groups,$my_groups) )
            {
                throw new Exception('您不在此用户某个权限组中，所以您不能对此用户进行操作', -1);
            }

            if ( array_diff($new_groups,$my_groups) )
            {
                throw new Exception('您为此用户设定的新权限组不在您的权限范围内', -1);
            }

            if ( !$this->check_auth_for_perm($member) )
            {
                if ( !$new_groups )
                {
                    if ( $old_groups )
                    {
                        foreach ( $old_groups as $gid )
                        {
                            if ( !($my_groups_setting[$gid]['del_user']==1||$my_groups_setting[$gid]['remove_user']==1) )
                            {
                                throw new Exception('您不具备当前组相应权限', -1);
                            }
                        }
                    }
                    else
                    {
                        throw new Exception($member->id>0?'您不具备相应权限':'创建新成员时必须选择一个组', -1);
                    }
                }
                else
                {
                    foreach ( $new_groups as $gid )
                    {
                        if ($member->id)
                        {
                            if ( $my_groups_setting[$gid]['edit_users']!=1 )
                            {
                                throw new Exception('您不具备当前组相应权限', -1);
                            }
                        }
                        else
                        {
                            if ( $my_groups_setting[$gid]['add_user']!=1 )
                            {
                                throw new Exception('您不具备当前组相应权限', -1);
                            }
                        }
                    }
                }
            }

            if ( $new_diff_group )foreach ( $new_diff_group as $gourp_id )
            {
                # 将用户添加相应的组
                if ( $my_groups_setting[$gourp_id]['add_user']!=1 )
                {
                    throw new Exception('您不具备添加当前组成员的权限',-1);
                }
            }

            if ( $del_diff_group )foreach ( $del_diff_group as $gourp_id )
            {
                # 将用户从此组移除/删除
                if ( !($my_groups_setting[$gourp_id]['del_user']==1 || $my_groups_setting[$gourp_id]['remove_user']==1) )
                {
                    throw new Exception('您不具备移除当前组成员的权限',-1);
                }
            }

            if ( $diff_group )
            {
                # 标志为修改组
                $this->is_change_group = true;

                # 新旧管理组不一样
                $orm_group = new ORM_Admin_MemberGroup_Finder();
                $groups = $orm_group->in('id', $new_groups)->find(null,true);

                $new_group_perm_setting = array();
                foreach ($groups as $item)
                {
                    # 合并权限
                    $new_group_perm_setting = Arr::merge($new_group_perm_setting,$item->perm_setting);
                }

                # 修改权限组需要验证一下新权限
                Controller_Administrator_Index::check_perm_data( $new_group_perm_setting );
            }
            if ( $_POST['perm_setting'] && is_array($_POST['perm_setting']) )
            {
                # 检查提交的额外权限
                $perm_setting = Controller_Administrator_Index::check_perm_data($_POST['perm_setting']);
            }

            if ( isset($member->setting['_group_admin']) )unset($member->setting['_group_admin']);
            if ($_POST['manage_groups'])foreach ($_POST['manage_groups'] as $g=>$setting)
            {
                /*
                    $_POST['manage_groups'] = array(
                        'g1' => array(...),
                        'g2' => array(...),
                    );
                */
                # 需要截取掉g
                $gid = substr($g,1);

                if ( $setting['edit_group']==1 )
                {
                    $member->setting['_group_admin']['can_edit_group'] = 1;
                }

                foreach ($setting as $k=>$s)
                {
                    if ($s)
                    {
                        $member->setting['_group_admin']['is_group_manager'] = 1;
                        if ( $my_groups_setting[$gid][$k]!=1 )
                        {
                            throw new Exception('设定的组权限超出您的组权限',-1);
                        }
                    }
                }
            }
        }

        # 设置数据
        $member->perm_setting = $perm_setting?$perm_setting:null;
    }

    /**
     * 保存用户组设置
     *
     * @param ORM_Admin_Member_Data $member
     * @param boolean $is_add
     */
    protected function save_member_group_perm(ORM_Admin_Member_Data $member,$is_add)
    {
        $orm_group = new ORM_Admin_MemberGroup_Finder();

        if ( $this->change_to_super_admin )
        {
            # 超管不需要设置组，将提升为超管的用户的组给清理掉
            $orm_group->db()->where('admin_id',$member->id)->delete( $orm_group->ids_tablename() );
        }
        else
        {
            # 新组
            $new_groups = $_POST['group_ids']?(array)$_POST['group_ids']:array();
            asort($new_groups);

            if ( $is_add )
            {
                # 新创建的用户

                # 添加的权限
                $new_diff_group = $new_groups;

                # 删除掉的权限
                $del_diff_group = array();
            }
            else
            {
                # 旧组
                $old_groups = $member->groups()->ids();
                asort($old_groups);

                # 添加的权限
                $new_diff_group = array_diff($new_groups , $old_groups);

                # 删除掉的权限
                $del_diff_group = array_diff($old_groups , $new_groups);

                # 相同的组
                $same_group = array_intersect($old_groups , $new_groups);

                if ( $same_group )
                {
                    # 更新
                    foreach ( $same_group as $group_id )
                    {
                        $where = array(
                            'admin_id'             => $member->id,
                            'group_id'             => $group_id,
                        );
                        $data = array(
                            'admin_id'             => $member->id,
                            'group_id'             => $group_id,
                            'view_users'           => $_POST['manage_groups']['g'.$group_id]['view_users']?1:0,
                            'edit_users'           => $_POST['manage_groups']['g'.$group_id]['edit_users']?1:0,
                            'edit_users_password'  => $_POST['manage_groups']['g'.$group_id]['edit_users_password']?1:0,
                            'add_user'             => $_POST['manage_groups']['g'.$group_id]['add_user']?1:0,
                            'del_user'             => $_POST['manage_groups']['g'.$group_id]['del_user']?1:0,
                            'remove_user'          => $_POST['manage_groups']['g'.$group_id]['remove_user']?1:0,
                            'shield_user'          => $_POST['manage_groups']['g'.$group_id]['shield_user']?1:0,
                            'liftshield_user'      => $_POST['manage_groups']['g'.$group_id]['liftshield_user']?1:0,
                            'edit_group'           => $_POST['manage_groups']['g'.$group_id]['edit_group']?1:0,
                        );
                        $orm_group->db()->update( $orm_group->ids_tablename() , $data , $where );
                    }
                }
            }

            if ( $new_diff_group )
            {
                foreach ( $new_diff_group as $group_id )
                {
                    $data = array(
                            'admin_id'             => $member->id,
                            'group_id'             => $group_id,
                            'view_users'           => $_POST['manage_groups']['g'.$group_id]['view_users']?1:0,
                            'edit_users'           => $_POST['manage_groups']['g'.$group_id]['edit_users']?1:0,
                            'edit_users_password'  => $_POST['manage_groups']['g'.$group_id]['edit_users_password']?1:0,
                            'add_user'             => $_POST['manage_groups']['g'.$group_id]['add_user']?1:0,
                            'del_user'             => $_POST['manage_groups']['g'.$group_id]['del_user']?1:0,
                            'remove_user'          => $_POST['manage_groups']['g'.$group_id]['remove_user']?1:0,
                            'shield_user'          => $_POST['manage_groups']['g'.$group_id]['shield_user']?1:0,
                            'liftshield_user'      => $_POST['manage_groups']['g'.$group_id]['liftshield_user']?1:0,
                            'edit_group'           => $_POST['manage_groups']['g'.$group_id]['edit_group']?1:0,
                    );
                    $orm_group->db()->values($data);
                }
                $orm_group->db()->columns(array_keys($data))->insert( $orm_group->ids_tablename() );
            }

            if ( $del_diff_group )
            {
                $orm_group->db()->where('admin_id',$member->id)->in('group_id',$del_diff_group)->delete( $orm_group->ids_tablename() );
            }
        }
    }

    /**
     * 检验操作者权限是否高于此用户
     *
     * @param ORM_Admin_Member_Data $member
     */
    protected function check_is_over_perm(ORM_Admin_Member_Data $member)
    {
        # 超管
        if ( $this->session()->member()->perm()->is_super_perm() )return true;

        # 还没有创建的用户不需要检查
        if ( $member->id )
        {
            $my_groups_setting = $this->session()->member()->groups_setting();
            $member_groups_setting = $member->groups_setting();
            foreach ( $member_groups_setting as $g=>$p )
            {
                foreach ($p as $k=>$v)
                {
                    if ( $v==1 && (!isset($my_groups_setting[$g][$k]) || $my_groups_setting[$g][$k]!=1) )
                    {
                        return false;
                    }
                }
            }
            $my_perm_setting = $this->session()->member()->perm()->get_setting();
            $member_perm_setting = $member->perm()->get_setting();
            $tmp_perm = Arr::merge($my_perm_setting,$member_perm_setting);

            if ( $my_perm_setting == $tmp_perm )
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return true;
        }
    }

    /**
     * 检查用户是否有操作用户权限的权限
     *
     * @param ORM_Admin_Member_Data $gourp
     * @return boolean
     */
    protected function check_auth_for_perm(ORM_Admin_Member_Data $member)
    {
        $member_perm = $this->session()->member()->perm();
        if ( $member_perm->is_super_perm() )
        {
            # 超管
            return true;
        }

        if ( $member->id > 0 )
        {
            # 修改组
            if ( $member_perm->is_own('administrator.change_user_perm') )
            {
                return true;
            }
        }
        else
        {
            #添加
            if ( $member_perm->is_own('administrator.add_new_user') )
            {
                return true;
            }
        }

        return false;
    }

    /**
     * 整理并检查权限数据
     *
     * @param array $perm
     * @throws Exception
     * @return array
     */
    public static function check_perm_data( array $perm )
    {
        $my_perm = Session::instance()->member()->perm();
        $perm_config = Core::config('admin/permission');

        if ( Session::instance()->member()->perm()->is_super_perm() )
        {
            $is_super = true;
        }
        else
        {
            $is_super = false;
        }

        # 提交的权限中有超管权限
        if ( isset($perm['_super_admin']) )unset($perm['_super_admin']);

        foreach ( $perm as $key => $item )
        {
            if ( is_array($item) )
            {
                foreach ( $perm as $k => $v )
                {
                    if ( isset($perm_config[$key][$k]) )
                    {
                        # 忽略掉没有的项目
                        unset($perm[$key][$k]);
                        continue;
                    }
                    else
                    {
                        $perm[$key][$k] = 1;
                    }

                    # 判断当前用户是否有此权限，超管就不用检查了
                    if ( !$is_super && !$my_perm->is_own($key.'.'.$k) )
                    {
                        # 权限名称
                        $pname = $perm_config[$key][$k];
                        if ( is_array($pname) )
                        {
                            $pname = (string)$pname['innerHTML'];
                        }
                        throw new Exception('您不具备此权限:“'.$pname.'”，操作已取消', -1);
                    }
                }
            }
        }

        return $perm;
    }

    /**
     * 检查用户是否具有屏蔽指定用户的权限
     *
     * @param ORM_Admin_Member_Data $member
     * @throws Exception
     *
     * @return boolean
     */
    protected function check_auth_for_shield(ORM_Admin_Member_Data $member)
    {
        if ( $member->id == $this->session()->member()->id )
        {
            throw new Exception('您不可屏蔽自己',-1);
        }

        # 超管
        if ( $this->session()->member()->perm()->is_super_perm() )return true;

        if ( $member->perm()->is_super_perm() )
        {
            # 非超管操作超管
            throw new Exception('您不具备解除屏蔽超管的权限',-1);
        }

        if ( !$this->session()->member()->perm()->is_own('administrator.shield_user') )
        {
            # 拥有所在组管理权限
            if ( array_diff($member->groups()->ids() , $this->session()->member()->groups()->ids()) )
            {
                # 没有相同的组
                throw new Exception('您操作的用户拥有你不具备的权限组，所有你无法屏蔽此用户',-1);
            }

            foreach ( $member->groups()->ids() as $group_id )
            {
                # 不具备组管理权限
                if ( !$this->session()->member()->is_own_group_perm($group_id,'shield_user') )
                {
                    throw new Exception('您不具备屏蔽该用户的权限',-1);
                }
            }
        }

        if ( !$this->check_is_over_perm($member) )
        {
            throw new Exception('此管理员拥有您不具备的权限，所以你不可屏蔽此管理员',-1);
        }

        return true;
    }

    /**
     * 检查用户是否具有解除屏蔽指定用户的权限
     *
     * @param ORM_Admin_Member_Data $member
     * @throws Exception
     *
     * @return boolean
     */
    protected function check_auth_for_liftshield(ORM_Admin_Member_Data $member)
    {
        # 超管
        if ( $this->session()->member()->perm()->is_super_perm() )return true;

        if ( $member->perm()->is_super_perm() )
        {
            # 非超管操作超管
            throw new Exception('您不具备解除屏蔽超管的权限',-1);
        }

        if ( !$this->session()->member()->perm()->is_own('administrator.liftshield_user') )
        {
            # 拥有所在组管理权限
            if ( array_diff($member->groups()->ids() , $this->session()->member()->groups()->ids()) )
            {
                # 没有相同的组
                throw new Exception('您操作的用户拥有你不具备的权限组，所有你无法解除屏蔽此用户',-1);
            }

            foreach ( $member->groups()->ids() as $group_id )
            {
                # 不具备组管理权限
                if ( !$this->session()->member()->is_own_group_perm($group_id,'liftshield_user') )
                {
                    throw new Exception('您不具备解除屏蔽该用户的权限',-1);
                }
            }

        }

        if ( !$this->check_is_over_perm($member) )
        {
            throw new Exception('此管理员拥有您不具备的权限，所以您不能解除屏蔽当前管理员',-1);
        }

        return true;
    }

    /**
     * 检查用户是否有删除权限
     *
     * @param ORM_Admin_Member_Data $member
     * @throws Exception
     *
     * @return boolean
     */
    protected function check_auth_for_delete(ORM_Admin_Member_Data $member)
    {
        # 不可删除自己
        if ( $member->id==$this->session()->member()->id )throw new Exception('您不可删除自己，请使用其他账号执行本次操作' , -1);

        # 超管
        if ( $this->session()->member()->perm()->is_super_perm() )return true;

        if ( $member->perm()->is_super_perm() )
        {
            # 非超管操作超管
            throw new Exception('您不具备删除超管的权限',-1);
        }

        if ( !$this->session()->member()->perm()->is_own('administrator.delete_user') )
        {
            # 拥有所在组管理权限
            if ( array_diff($member->groups()->ids() , $this->session()->member()->groups()->ids()) )
            {
                # 没有相同的组
                throw new Exception('您操作的用户拥有你不具备的权限组，所有你无法删除此用户',-1);
            }

            foreach ( $member->groups()->ids() as $group_id )
            {
                # 不具备组管理权限
                if ( !$this->session()->member()->is_own_group_perm($group_id,'del_user') )
                {
                    throw new Exception('您不具备删除此用户的权限',-1);
                }
            }
        }

        if ( !$this->check_is_over_perm($member) )
        {
            throw new Exception('此管理员拥有您不具备的权限，所以您不可删除此管理员',-1);
        }

        return true;
    }

    /**
     * 检查用户是否有添加权限
     *
     * @param ORM_Admin_Member_Data $member
     * @throws Exception
     *
     * @return boolean
     */
    protected function check_auth_for_add( $group_ids )
    {
        if ( !$this->session()->member()->perm()->is_own('administrator.add_new_user') )
        {
            if ($group_ids)
            {
                if ( !is_array($group_ids) )return new Exception('数据格式错误',-1);

                foreach ($group_ids as $group_id)
                {
                    if ( !$this->session()->member()->is_own_group_perm($group_id,'add_user') )
                    {
                        # 选择的权限组中存当前管理员不存在的管理权限
                        throw new Exception('您不具备此权限',-1);
                    }
                }
            }
            else
            {
                if ( !$this->session()->member()->has_own_perm_groups('add_user')->count() )
                {
                    throw new Exception('您不具备创建小组成员的权限',-1);
                }
            }
        }

        return true;
    }

    /**
     * 检查用户是否有修改用户权限
     *
     * @param ORM_Admin_Member_Data $member
     * @throws Exception
     *
     * @return boolean
     */
    protected function check_auth_for_edit(ORM_Admin_Member_Data $member)
    {
        if ( $this->session()->member()->perm()->is_super_perm() )return true;

        if ( $member->perm()->is_super_perm() )
        {
            # 非超管操作超管
            throw new Exception('您不具备操作超管的权限',-1);
        }

        if ( $member->id && $member->id==$this->session()->member()->id )
        {
            if ( $this->session()->member()->perm()->is_own('administrator.edit_self_info') )
            {
                # 修改自己的信息
                return true;
            }
        }

        if ( !$this->session()->member()->perm()->is_own('administrator.edit_user_info') )
        {
            # 拥有所在组管理权限
            if ( array_diff($member->groups()->ids() , $this->session()->member()->groups()->ids()) )
            {
                # 没有相同的组
                throw new Exception('您操作的用户拥有你不具备的权限组，所有你无法修改此用户信息',-1);
            }

            foreach ( $member->groups()->ids() as $group_id )
            {
                # 不具备组管理权限
                if ( !$this->session()->member()->is_own_group_perm($group_id,'edit_users') )
                {
                    throw new Exception('您不具备修改此用户的权限',-1);
                }
            }
        }

        if ( !$this->check_is_over_perm($member) )
        {
            throw new Exception('此管理员拥有您不具备的权限，所以你不可修改此管理员设置',-1);
        }

        return true;
    }

    /**
     * 检查用户是否有修改用户密码权限
     *
     * @param ORM_Admin_Member_Data $member
     * @throws Exception
     *
     * @return boolean
     */
    protected function check_auth_for_edit_password(ORM_Admin_Member_Data $member)
    {
        # 超管
        if ( $this->session()->member()->perm()->is_super_perm() )return true;

        if ( $member->perm()->is_super_perm() )
        {
            # 非超管操作超管
            throw new Exception('您不具备修改超管密码的权限',-1);
        }

        if ( $member->id && $member->id==$this->session()->member()->id )
        {
            if ( $this->session()->member()->perm()->is_own('administrator.edit_self_password') )
            {
                # 修改自己的密码
                return true;
            }
        }

        if ( !$this->session()->member()->perm()->is_own('administrator.change_user_password') )
        {

            # 拥有所在组管理权限
            if ( array_diff($member->groups()->ids() , $this->session()->member()->groups()->ids()) )
            {
                # 没有相同的组
                $this->message('您操作的用户拥有你不具备的权限组，所有你无法修改此用户密码',-1);
            }

            foreach ( $member->groups()->ids() as $group_id )
            {
                # 不具备组管理权限
                if ( !$this->session()->member()->is_own_group_perm($group_id,'edit_users_password') )
                {
                    throw new Exception('您不具备修改此管理员密码的权限',-1);
                }
            }
        }

        if ( !$this->check_is_over_perm($member) )
        {
            throw new Exception('此管理员拥有您不具备的权限，所以您不可修改此管理员密码',-1);
        }

        return true;
    }
}