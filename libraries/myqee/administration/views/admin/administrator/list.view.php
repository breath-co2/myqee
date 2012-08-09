<?php
$is_ajax = HttpIO::IS_AJAX;
if ( !$is_ajax )
{
?>
<script>
function search_user()
{
    var obj = MyQEE.$('search_group_form');
    if (obj._is_postting)
    {
        MyQEE.Msg('正在搜索，请稍等...');
        return false;
    }
    obj._is_postting = true;
    var ajax = new MyQEE.ajax(obj.action);
    ajax.method = 'GET';
    ajax.setform(obj);
    ajax.onCompletion = function()
    {
        obj._is_postting = null;
        MyQEE.$('list_div').innerHTML = this.response;
    }
    ajax.onError = function()
    {
        obj._is_postting = null;

        MyQEE.error('数据提交失败，请重试！');
    }
    ajax.runAJAX();

    return false;
}
</script>
<table class="mainTable">
<tr>
<td class="td1">
<form method="get" id="select_group_form" action="<?php echo Core::url('administrator/');?>">
选择所属于组：<?php echo Form::select('group_id',array(''=>'请选择')+Session::instance()->member()->has_own_perm_groups('view_users')->get_array_for_select(),$_GET['group_id'],array('onchange'=>'MyQEE.$(\'select_group_form\').submit();'));?>
</form>
</td>
<?php
if ( Session::instance()->member()->is_super_admin )
{
?>
<td class="td1" align="center">
<input value="全部超管" type="button" onclick="goto(MyQEE.Url.Site+'/administrator/?list=supadmin')" />
<input value="全部组长" type="button" onclick="goto(MyQEE.Url.Site+'/administrator/?list=groupmanager')" />
<input value="含自定义权限用户" type="button" onclick="goto(MyQEE.Url.Site+'/administrator/?list=zdy')" />
</td>
<?php
}
?>
<td class="td1" align="center" width="280">
<form method="get" id="search_group_form" onsubmit="return search_user();" action="<?php echo Core::url('administrator/');?>">
搜索:<?php echo Form::input('k',$_GET['k'],array('id'=>'search_key','style'=>'width:120px;','title'=>'请输入管理员账号或昵称'));?>

<input type="submit" value="搜索" />
</form>
</td>
</tr>
</table>
<div style="height:8px;overflow:hidden;"></div>

<div id="list_div">
<?php
}
?>
<?php
if ($list)
{
?>
<table class="mainTable">
    <tr>
        <th width="40">ID</th>
        <th>用户名（昵称）</th>
        <th>所属管理组</th>
        <th width="30">组长</th>
        <th width="30">超管</th>
        <th width="30">屏蔽</th>
        <th width="40">登录数</th>
        <th width="190">操作</th>
    </tr>
    <?php
    $member_id = Session::instance()->member()->id;
    if ($list)foreach ($list as $item)
    {
        if (false)$item = new ORM_Admin_Member_Data();
    ?>
    <tr align="center">
        <td class="td1"><?php echo $item->id;?></td>
        <td class="td2"><?php echo $item->username;if ($item->nickname)echo ' ('.$item->nickname.')';?></td>
        <td class="td2">
        <?php
        if ( $item->groups()->count()==0 )
        {
            echo '&nbsp;';
        }
        else if ( !$item->is_super_admin )
        {
            $groups_setting = $item->groups_setting();
            $group_html = array();
            $st_str = array(
                'view_users'          => '查看成员列表',
                'edit_users'          => '修改成员信息',
                'edit_users_password' => '修改成员密码',
                'add_user'            => '添加成员',
                'del_user'            => '删除成员',
                'remove_user'         => '移除成员',
                'shield_user'         => '屏蔽成员',
                'liftshield_user'     => '解除屏蔽成员',
                'edit_group'          => '修改组设置',
            );

            $i=0;
            $count = $item->groups()->count();
            $title_str2 = array('<table><tr><th>权限</th>');
            foreach ($item->groups() as $v)
            {
                $i++;
                $group_setting = $groups_setting[$v->id];
                $is_mg = false;
                $title_str = '<table>';
                $title_str2[0] .= '<th>'.$v->group_name.'</th>';
                if ($i==1)foreach ($st_str as $kk=>$vv)
                {
                    $title_str2[$kk] .= '<tr><td>'.$vv.'</td>';
                }
                if ($group_setting)foreach ($group_setting as $k=>$st)
                {
                    if ($st==1)
                    {
                        $is_mg = true;
                        $title_str .= '<tr><td width=100>'.$st_str[$k] . '</td><td><font color=#5fd300>√</font></td></tr>';
                        $title_str2[$k] .= '<td align=center><font color=#5fd300>√</font></td>';
                    }
                    else
                    {
                        $title_str .= '<tr><td width=100>'.$st_str[$k] . '</td><td><font color=red>×</font></td></tr>';
                        $title_str2[$k] .= '<td align=center><font color=red>×</font></td>';
                    }
                }
                if ($i==$count)foreach ($st_str as $kk=>$vv)
                {
                    $title_str2[$kk] .= '</tr>';
                }
                $title_str .= '</table>';
                $tmpstr = '<a title="查看、修改组" href="'.Core::url('administrator/group/edit/'.$v->id).'">'.$v->group_name.'</a>';

                if ($is_mg)
                {
                    $tmpstr .= '<span style="color:red" title="'.$title_str.'">管</span>';
                }
                $group_html[] = $tmpstr;
            }
            $title_str2[0] .= '</tr>';
            $title_str2[] = '</table>';
            echo implode(' | ', $group_html);
        }
        ?>
        </td>
        <td class="td2"><?php echo $item->is_super_admin?'<font style="color:red">超管</font>':($is_mg?'<font style="color:#0dcc00" title="'.implode('',$title_str2).'">是</font>':'<font style="color:blue">否</font>');?></td>
        <td class="td2"><?php echo $item->is_super_admin?'<font style="color:red">是</font>':'<font style="color:blue">否</font>';?></td>
        <td class="td2"><?php echo $item->shielded?'<font style="color:red">是</font>':'<font style="color:green">否</font>';?></td>
        <td class="td2" title="<?php echo '最后登录IP：'.$item->last_login_ip;?><?php if($item->last_login_time>0)echo '<br>'.date('Y-m-d H:i:s',$item->last_login_time);?>"><?php echo $item->login_num;?></td>
        <td class="td2">
        <input type="button" value="改设置" onclick="goto('<?php echo Core::url('administrator/edit/'.$item->id);?>')" />
        <input type="button" value="改密码" onclick="goto('<?php echo Core::url('administrator/change_password/'.$item->id);?>')" />
        <?php
        if ($item->shielded)
        {
        ?>
        <input type="button" value="解" title="解除对用户的屏蔽" onclick="MyQEE.ask_todo('<?php echo Core::url('administrator/liftshield/'.$item->id);?>','您确认要解除对此用户的屏蔽？')" />
        <?php
        }
        else
        {
        ?>
        <input type="button" value="屏" <?php echo $item->id==$member_id?' disabled="disabled"':'';?> title="屏蔽用户登录" onclick="MyQEE.ask_todo('<?php echo Core::url('administrator/shield/'.$item->id);?>','您确认要屏蔽此用户？')" />
        <?php
        }
        ?>
        <input type="button" value="<?php echo __('Del');?>" <?php echo $item->id==$member_id?' disabled="disabled"':'';?> onclick="MyQEE.ask_todo('<?php echo Core::url('administrator/delete/'.$item->id);?>','您确认要删除此用户？')" />
        </td>
    </tr>
    <?php
    }
    ?>
    <tr>
        <td class="td1" colspan="7"> </td>
        <td class="td1" align="center">
        <input type="button" class="submit" value="创建新用户" onclick="goto('<?php echo Core::url('administrator/add');?>')" />
        </td>
    </tr>
</table>

<center>
<?php echo $pagehtml;?>
</center>
<?php
}
else
{
?>
<table class="mainTalbe">
<tr>
<td class="td1" align="center" height="60">没有指定的数据</td>
</tr>
</table>
<?php
}
?>
<?php
if ( !$is_ajax )
{
?>
</div>
<?php
}
?>