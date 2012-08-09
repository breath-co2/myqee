<?php
/**
 * 管理员控制器
 *
 * @author jonwang
 *
 */
class Controller_MyQEE__Administrator__Group extends Controller_Admin
{
    /**
     * 权限组列表
     */
    public function action_default()
    {
        $this->quick_menu = array(
            'administrator/group/add' => '添加权限组',
            'administrator/'          => '管理员列表',
            'administrator/add'       => '添加管理员',
        );
        if ( !$this->session()->member()->perm()->is_own('administrator.view_group_info') )
        {
            if ( $this->session()->member()->perm()->is_own('administrator.can_edit_group') )
            {
                $groups = $this->session()->member()->groups();
            }
            else
            {
                $this->message('抱歉，您无此权限');
            }
        }

        $view = new View('admin/administrator/group_list');

        if ( isset($groups) )
        {
            $view->list = $groups;
            $view->group_manager = true;
        }
        else
        {
            if ( $this->session()->member()->perm()->is_super_perm() )
            {
                # 超级管理员列出所有组
                $project = null;
            }
            else
            {
                $project = Core::$project;
            }

            $model_administrator = new Model_Admin_Administrator();
            $count = $model_administrator->total_group_count($project);
            if ($count)
            {
                $pageconfig = Core::config('admin/pagination');
                $pageconfig['total_items'] = $count;

                $pagination = new Pagination($pageconfig);

                $list = $model_administrator->get_group_list( $project , $pagination->get_offset() , $pagination->get_items_per_page() );
                $view->list = $list;
                $view->pagehtml = $pagination->render();
            }
        }

        $view->render();
    }

    public function action_add()
    {
        $this->action_edit();
    }

    /**
     * 修改权限组
     *
     * @param int $group_id
     */
    public function action_edit($group_id=0)
    {
        $this->quick_menu = array(
            'administrator/group/' => '权限组列表',
            'administrator/'       => '管理员列表',
            'administrator/add'    => '添加管理员',
        );
        if ($group_id>0)
        {
            $this->page_title = '修改管理组';
        }

        $can_edit_perm = true;

        $orm_group = new ORM_Admin_MemberGroup_Finder();
        if ($group_id>0)
        {
            $group = $orm_group->get_by_id($group_id);

            if ( $group->project!=Core::$project && !$this->session()->member()->perm()->is_super_perm() )
            {
                # 不允许跨项目操作
                $this->message('所属项目不同，您不能通过此页面操作该组',-1);
            }
            if ( !$this->session()->member()->perm()->is_own('administrator.edit_group_info') )
            {
                if ( $this->check_auth_for_info($group) )
                {
                    if ( !$this->session()->member()->perm()->is_own('administrator.check_auth_for_perm') )
                    {
                        $can_edit_perm = false;
                    }
                }
                else
                {
                    $this->message('抱歉，您无此权限');
                }
            }
        }
        else
        {
            if ( !$this->session()->member()->perm()->is_own('administrator.add_group') )
            {
                $this->message('抱歉，您无此权限');
            }
            $group = $orm_group->create();
        }

        if (false)$group = new ORM_Admin_MemberGroup_Data();

        if (HttpIO::METHOD=='POST')
        {
            $this->save($group);
        }

        $view = new View('admin/administrator/group_form');

        $view->can_edit_perm = $can_edit_perm;
        $view->group = $group;
        $view->title = $group_id>0?'修改管理组':'添加管理组';

        $view->render();
    }

    /**
     * 删除权限组
     *
     * @param int $group_id
     */
    public function action_delete($group_id=0)
    {
        $orm_group = new ORM_Admin_MemberGroup_Finder();
        if (!$group_id>0)
        {
            $this->message('缺少参数',-1);
        }
        $group = $orm_group->get_by_id($group_id);
        if (!$group)
        {
            $this->message('指定的权限组不存在，可能已被删除',-1);
            $group = new ORM_Admin_MemberGroup_Data();
        }

        if ( !$this->session()->member()->perm()->is_own('administrator.delete_group') )
        {
            $this->message('您不具备删除该权限组的权限',-1);
        }

        if ( $group->project != Core::$project && !$this->session()->member()->perm()->is_super_perm() )
        {
            # 夸项目操作，只有超管才可以执行
            $this->message('您不可通过此URL执行本次操作',-1);
        }

        if ( $group->members()->count() )
        {
            $this->message('此权限组含有成员，无法删除。请先清空该权限组成员后再删除',-1);
        }

        $status = $group->delete();
        if ( $status )
        {
            $this->message('删除成功。',1);
        }
        else
        {
            $this->message('未删除',0);
        }
    }

    /**
     * 保存数据
     *
     * @param ORM_Admin_MemberGroup_Data $group
     */
    protected function save(ORM_Admin_MemberGroup_Data $group )
    {
        if ( isset($_POST['group_name']) && $this->check_auth_for_info($group) )
        {
            if ( empty($_POST['group_name']) )
            {
                $this->message('权限组名称不能空',0);
            }
            if ( strlen($_POST['group_desc'])>1000 )
            {
                $this->message('权限组说明太长了，限定1000个字符',0);
            }
            $group->group_name = $_POST['group_name'];
            $group->group_desc = $_POST['group_desc'];
            $group->sort       = (int)$_POST['sort'];

            # 群设置
            if ( isset($_POST['setting']['menu_config']) && !$this->session()->member()->perm()->is_own('administrator.edit_menu_config') )
            {
                # 若不具备菜单管理权限，则清除此配置
                unset($_POST['setting']['menu_config']);
            }
            $data = (array)$group->setting;
            if ( is_array($_POST['setting']) )foreach ($_POST['setting'] as $k=>$v)
            {
                $data[$k] = $v;
            }

            $group->setting = $data;
        }

        # 处理权限
        if ( isset($_POST['perm_setting']) && is_array($_POST['perm_setting']) && $this->check_auth_for_perm($group) )
        {
            try
            {
                $perm_setting = Controller_Administrator__Index::check_perm_data($_POST['perm_setting']);
            }
            catch (Exception $e)
            {
                $this->message( $e->getMessage(),$e->getCode() );
            }
            # 设置数据
            $group->perm_setting = $perm_setting;
        }

        try
        {
            if ($group->id)
            {
                $s = $group->update();
            }
            else
            {
                # 指定项目
                $group->project = Core::$project;

                $s = $group->insert();
            }

            if ($s)
            {
                $this->message('保存成功',1);
            }
            else
            {
                $this->message('未保存任何数据');
            }
        }
        catch (Exception $e)
        {
            Core::debug()->error($e->getMessage());
            $this->message('保存失败，请重试',-1);
        }
    }

    /**
     * 检查用户是否有操作对应组权限的权限
     *
     * @param ORM_Admin_MemberGroup_Data $gourp
     * @return boolean
     */
    protected function check_auth_for_perm(ORM_Admin_MemberGroup_Data $gourp)
    {
        $member_perm = $this->session()->member()->perm();
        if ( $member_perm->is_super_perm() )
        {
            # 超管
            return true;
        }

        if ( $gourp->id>0 )
        {
            # 修改组
            if ( $member_perm->is_own('administrator.edit_group_perm') )
            {
                return true;
            }
        }
        else
        {
            #添加
            if ( $member_perm->is_own('administrator.add_group') )
            {
                return true;
            }
        }

        return false;
    }

    protected function check_auth_for_info(ORM_Admin_MemberGroup_Data $gourp)
    {
        $member_perm = $this->session()->member()->perm();
        if ( $member_perm->is_super_perm() )
        {
            # 超管
            return true;
        }

        if ( $gourp->id > 0 )
        {
            # 修改组

            if ( $member_perm->is_own('administrator.edit_group_info') )
            {
                return true;
            }

            # 操作者修改本组数据
            if ( $this->session()->member()->is_own_group_perm($gourp->id,'edit_group') )
            {
                return true;
            }
        }
        else
        {
            #添加
            if ( $member_perm->is_own('administrator.add_group') )
            {
                return true;
            }
        }

        return false;
    }
}