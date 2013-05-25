<?php
if (false)$member = new ORM_Admin_Member_Data();
?>
<script type="text/javascript">
var _is_run_ajax = false;
var now_groups_form = '_';
function do_next_step()
{
    if (_is_run_ajax)return;
    if (!MyQEE.$('checked_perm_div'))return;
    var checkboxs = MyQEE.$('checked_perm_div').getElementsByTagName('input');
    var group_ids = [];
    for ( var i=0;i<checkboxs.length;i++ )
    {
        if ( checkboxs[i].checked )
        {
            group_ids.push(checkboxs[i].value);
        }
    }
    var groups_form = group_ids.join(',');
    if ( now_groups_form==groups_form )
    {
        //直接切换显示
        MyQEE.$('mytag_main_1').style.display='none';
        MyQEE.$('mytag_main_2').style.display='';
        MyQEE.$('step_tag_1').className='';
        MyQEE.$('step_tag_2').className='hover';

        change_zdy_parm();
        return ;
    }

    _is_run_ajax = true;
    var ajax = new MyQEE.ajax('<?php echo Core::url('administrator/edit_perm_form/'.$member->id);?>');
    ajax.method = 'GET';
    ajax.setVar('group_ids',group_ids);
    ajax.onError = function()
    {
        _is_run_ajax = false;
        MyQEE.$('step1_loading').style.display='none';
        MyQEE.Msg('数据请求失败，请重试。');
    }

    ajax.onCompletion = function()
    {
        _is_run_ajax = false;
        MyQEE.$('step1_loading').style.display='none';
        MyQEE.$('mytag_main_1').style.display='none';
        MyQEE.$('mytag_main_2').style.display='';
        MyQEE.$('step_tag_1').className='';
        MyQEE.$('step_tag_2').className='hover';
        MyQEE.$('perm_div').innerHTML = this.response;

        now_groups_form = groups_form;

        change_zdy_parm();
    }

    ajax.runAJAX();
    MyQEE.$('step1_loading').style.display='';
}

function change_zdy_perm(v,c)
{
    obj_a = MyQEE.$('zdy_perm_redio_a');
    obj_b = MyQEE.$('zdy_perm_redio_b');
    obj_1 = MyQEE.$('zdy_perm_redio_1');
    obj_2 = MyQEE.$('zdy_perm_redio_2');
    if (obj_1 && obj_2)
    {
        if (v==0)
        {
            obj_1.checked = obj_a.checked = true;
            obj_2.checked = obj_b.checked = false;
        }
        else
        {
            obj_1.checked = obj_a.checked = false;
            obj_2.checked = obj_b.checked = true;
        }
    }
    change_zdy_parm();
}

function do_per_step()
{
    MyQEE.$('mytag_main_1').style.display='';
    MyQEE.$('mytag_main_2').style.display='none';
    MyQEE.$('step_tag_1').className='hover';
    MyQEE.$('step_tag_2').className='';
}
function change_zdy_parm()
{
    var zdy_perm_redio = MyQEE.$('zdy_perm_redio_1');
    var group_perm = false;
    if (zdy_perm_redio)
    {
        group_perm = zdy_perm_redio.checked;
    }

    super_admin();

    if (!MyQEE.$('perm_checkbox_div'))return;
    var objs = MyQEE.$('perm_checkbox_div').getElementsByTagName('input');
    for(var i=0;i<objs.length;i++)
    {
        if ( objs[i].getAttribute('auto_disabled')=='on' )
        {
            objs[i].disabled = 'disabled';
        }
        else
        {
            if ( objs[i].getAttribute('has_checked')!='yes' )
            {
                objs[i].checked = false;
            }
            if ( group_perm )
            {
                objs[i].disabled = 'disabled';
            }
            else
            {
                objs[i].disabled = false;
            }
        }
    }
}

function select_group_perm(obj,ck)
{
    var objs = obj.getElementsByTagName('INPUT');
    for(var i=0;i<objs.length;i++)
    {
        if (objs[i].disabled)continue;
        objs[i].checked = ck;
    }
}
<?php
if ($show_edit_perm)
{
?>

function change_group()
{
    var mey_groups_setting = <?php echo json_encode(Session::instance()->member()->groups_setting());?>;
    var manage_groups = <?php echo json_encode($member->groups_setting());?>;
    var checkboxs = MyQEE.$('checked_perm_div').getElementsByTagName('input');
    for(var i=0;i<checkboxs.length;i++)
    {
//        tmphtml += '<div style="padding-right:20px;float:left;display:inline;"></div>';
        var tmpobj = MyQEE.$('manage_group_div_'+checkboxs[i].value);
        if (checkboxs[i].checked)
        {
            if (tmpobj)continue;
            var inputname = 'manage_groups[g'+checkboxs[i].value+']';

            if (typeof manage_groups[checkboxs[i].value] == 'undefined')manage_groups[checkboxs[i].value] = {};
            var obj = document.createElement('div');
            obj.id = 'manage_group_div_'+checkboxs[i].value;
            obj.innerHTML = '<table class="mainTable" style="white-space:nowrap;margin-top:-1px;" cellpadding="0" cellspacing="1" style="white-space:nowrap;"><tr>'+
            '<td style="padding:2px;" class="td1"><div style="padding-right:30px;"><label title="勾选拥有管理组“'+MyQEE.$('group_name_'+checkboxs[i].value).innerHTML.replace(/"/g,'&quot;')+'”全部权限"><input onclick="select_group_perm(this.parentNode.parentNode.parentNode.parentNode,this.checked)" type="checkbox" />'+MyQEE.$('group_name_'+checkboxs[i].value).innerHTML+'</label></div></td>'+
            '<td style="padding:2px;"><label title="查看属于该组用户信息"><input value="1" type="checkbox" name="'+inputname+'[view_users]"'+(mey_groups_setting[checkboxs[i].value]['view_users']==1?'':' disabled="disabled"')+(manage_groups[checkboxs[i].value]['view_users']==1?' checked="checked"':'')+' />查看组成员</label></td>'+
            '<td style="padding:2px;"><label title="修改属于该组用户信息"><input value="1" type="checkbox" name="'+inputname+'[edit_users]"'+(mey_groups_setting[checkboxs[i].value]['edit_users']==1?'':' disabled="disabled"')+(manage_groups[checkboxs[i].value]['edit_users']==1?' checked="checked"':'')+' />修改成员信息</label></td>'+
            '<td style="padding:2px;"><label title="修改属于该组用户密码"><input value="1" type="checkbox" name="'+inputname+'[edit_users_password]"'+(mey_groups_setting[checkboxs[i].value]['edit_users_password']==1?'':' disabled="disabled"')+(manage_groups[checkboxs[i].value]['edit_users_password']==1?' checked="checked"':'')+' />修改成员密码</label></td>'+
            '<td style="padding:2px;"><label title="是否允许为该组添加新用户"><input value="1" type="checkbox" name="'+inputname+'[add_user]"'+(mey_groups_setting[checkboxs[i].value]['add_user']==1?'':' disabled="disabled"')+(manage_groups[checkboxs[i].value]['add_user']==1?' checked="checked"':'')+' />添加成员</label></td>'+
            '<td style="padding:2px;"><label title="是否允许删除该组用户"><input value="1" type="checkbox" name="'+inputname+'[del_user]"'+(mey_groups_setting[checkboxs[i].value]['del_user']==1?'':' disabled="disabled"')+(manage_groups[checkboxs[i].value]['del_user']==1?' checked="checked"':'')+' />删除成员</label></td>'+
            '<td style="padding:2px;"><label title="是否允许将用户移出此组，用户被移出后将移除此组的相应权限"><input value="1" type="checkbox" name="'+inputname+'[remove_user]"'+(mey_groups_setting[checkboxs[i].value]['remove_user']==1?'':' disabled="disabled"')+(manage_groups[checkboxs[i].value]['remove_user']==1?' checked="checked"':'')+' />移除成员</label></td>'+
            '<td style="padding:2px;"><label title="是否允许屏蔽该组用户"><input value="1" type="checkbox" name="'+inputname+'[shield_user]"'+(mey_groups_setting[checkboxs[i].value]['shield_user']==1?'':' disabled="disabled"')+(manage_groups[checkboxs[i].value]['shield_user']==1?' checked="checked"':'')+' />屏蔽成员</label></td>'+
            '<td style="padding:2px;"><label title="是否允许解除屏蔽该组用户"><input value="1" type="checkbox" name="'+inputname+'[liftshield_user]"'+(mey_groups_setting[checkboxs[i].value]['liftshield_user']==1?'':' disabled="disabled"')+(manage_groups[checkboxs[i].value]['liftshield_user']==1?' checked="checked"':'')+' />解除屏蔽</label></td>'+
            '<td style="padding:2px;"><label title="是否允许修改该组名称，说明等"><input value="1" type="checkbox" name="'+inputname+'[edit_group]"'+(mey_groups_setting[checkboxs[i].value]['edit_group']==1?'':' disabled="disabled"')+(manage_groups[checkboxs[i].value]['edit_group']==1?' checked="checked"':'')+' />修改组设置</label></td>'+
            '</tr></table>';
            MyQEE.$('manage_group_div').appendChild(obj);
        }
        else if (tmpobj)
        {
            tmpobj.parentNode.removeChild(tmpobj);
        }
    }
}

<?php
}
?>

function in_array(str,arr)
{
    for (var i in arr)
    {
        if (arr[i]==i)return true;
    }
    return false;
}

function super_admin()
{
    if ( MyQEE.$('super_admin_value') )
    {
        var ck = MyQEE.$('super_admin_value').checked;
    }
    else
    {
        var ck = false;
    }
    var zdy_ck = true;
    if (ck)
    {
        MyQEE.$('perm_setting_div').style.display = 'none';
    }
    else
    {
        zdy_ck = MyQEE.$('zdy_perm_redio_1').checked;
        MyQEE.$('perm_setting_div').style.display = '';
    }
    if (zdy_ck)
    {
        MyQEE.$('step_tag_2').style.display = 'none';
        MyQEE.$('step_submit_1').style.display = '';
        MyQEE.$('step_submit_2').style.display = 'none';
    }
    else
    {
        MyQEE.$('step_tag_2').style.display = '';
        MyQEE.$('step_submit_1').style.display = 'none';
        MyQEE.$('step_submit_2').style.display = '';
    }
}
</script>
<?php
if ($show_edit_perm)
{
?>
<div class="tag">
    <ul class="ul">
        <li id="step_tag_1" onclick="do_per_step()" class="hover">基本信息</li>
        <li<?php if($member->is_super_admin)echo ' style="display:none;"';?> id="step_tag_2" onclick="do_next_step()">权限设置</li>
    </ul>
</div>
<?php
}
?>
<form name="myform" id="myform" autocomplete="off" method="post" onsubmit="return MyQEE.form_post(this,document.referrer);">
<?php echo Form::hidden('edit_perm',$show_edit_perm?'1':'0');?>
<div id="mytag_main_1">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="mainTable">
    <tr>
        <th colspan="2"><?php echo $title;?> - 基本信息</th>
    </tr>
    <?php
    if (!$member->id>0)
    {
        # 创建新用户
    ?>
    <tr>
        <td width="120" class="td1" align="right">
           用户名：
        </td>
        <td class="td2">
            <input type="text" value="" name="username" style="width:220px;" />
        </td>
    </tr>
    <tr>
        <td class="td1" align="right">
           昵称：
        </td>
        <td class="td2">
            <input type="text" value="" name="nickname" style="width:220px;" />
        </td>
    </tr>
    <tr>
        <td class="td1" align="right">
            密码：
        </td>
        <td class="td2">
            <input autocomplete="off" type="password" value="" name="new_password" style="width:120px;" />
        </td>
    </tr>
    <tr>
        <td class="td1" align="right">
            确认密码：
        </td>
        <td class="td2">
            <input autocomplete="off" type="password" value="" name="new_password_2" style="width:120px;" />
        </td>
    </tr>
    <?php
    }
    else
    {
    ?>
    <tr>
        <td width="120" class="td1" align="right">
           用户名：
        </td>
        <td class="td2">
            <?php echo Form::input('',$member->username,array('style'=>'width:220px;','disabled'=>'disabled'));?>
        </td>
    </tr>
    <tr>
        <td class="td1" align="right">
           昵称：
        </td>
        <td class="td2">
            <?php echo Form::input('nickname',$member->nickname,array('style'=>'width:220px;'));?>
        </td>
    </tr>
    <tr>
        <td width="120" class="td1" align="right">是否允许多处登录：</td>
        <td class="td2">
            <label><?php echo Form::radio('setting[only_self_login]',1,$member->setting['only_self_login']?true:false)?>是</label>
            <label><?php echo Form::radio('setting[only_self_login]',0,$member->setting['only_self_login']?false:true)?>否</label>
            &nbsp;
            <span class="helpicon" title="如果选择“允许”则同一账号可多处登录，否则只允许单人登录使用。<br/>可防止“一号多开”情况。"> </span>
        </td>
    </tr>
    <?php
    }
    ?>
    <?php
    if ( $show_edit_perm )
    {
    ?>
    <?php
    if ( Session::instance()->member()->is_super_admin )
    {
    ?>
    <tr>
        <td class="td1" align="right">是否超级管理员：</td>
        <td class="td2">
            <label><?php echo Form::radio('is_super_admin',1,$member->is_super_admin?true:false,array('onclick'=>'super_admin();','id'=>'super_admin_value'))?>是</label>
            <label><?php echo Form::radio('is_super_admin',0,$member->is_super_admin?false:true,array('onclick'=>'super_admin();'))?>否</label>
        </td>
    </tr>
    <?php
    }
    ?>
    <tbody id="perm_setting_div"<?php if ($member->is_super_admin)echo ' style="display:none"'?>>
    <tr>
        <td class="td1" align="right">所属权限组：</td>
        <td colspan="2" class="td2">
            <ul class="ul" id="checked_perm_div">
            <?php
            $groupids = $member->groups()->ids();
            $my_groups_setting = Session::instance()->member()->groups_setting();
            $groups_count = count($groups);
            if ( $groups )foreach ($groups as $group)
            {
                if (false)$group = new ORM_Admin_MemberGroup_Data();

                $is_in_group = in_array($group->id, $groupids);
                $att = array(
                    'id'=>'group_'.$group->id,
                    'onclick'=>'change_group();',
                );
                if ( $is_in_group )
                {
                    if ( !($my_groups_setting[$group->id]['del_user']==1||$my_groups_setting[$group->id]['remove_user']==1) )
                    {
                        $att['onclick'] = 'this.checked = true;';
                        $att['title'] = '此组不可修改';
                    }
                }
                else
                {
                    if ( $my_groups_setting[$group->id]['add_user']!=1 )
                    {
                        $att['onclick'] = 'this.checked = false';
                        $att['title'] = '此组不可修改';
                    }
                }
                if ( 1===$groups_count && $member->id==0 )
                {
                     $att['onclick'] = 'this.checked = true;';
                    $att['checked'] = 'checked';
                }

                echo '<li style="padding-right:20px;float:left;display:inline;"><label for="group_'.$group->id.'">'.Form::checkbox('group_ids[]',$group->id,$is_in_group,$att).'<span id="group_name_'.$group->id.'">'.$group->group_name.'</span></label></li>';
            }
            ?>
            </ul>
        </td>
    </tr>
    <tr>
        <td class="td1" align="right">组管理权限：</td>
        <td class="td2" id="manage_group_div">

        </td>
    </tr>
    <tr>
        <td class="td1" align="right">权限设置：</td>
        <td class="td2">
            <label><?php echo Form::radio('zdy_perm',0,$member->perm_setting?false:true,array('id'=>'zdy_perm_redio_1','onclick'=>'change_zdy_perm(0,false)'));?>仅使用所在组权限</label>
            <label><?php echo Form::radio('zdy_perm',1,$member->perm_setting?true:false,array('id'=>'zdy_perm_redio_2','onclick'=>'change_zdy_perm(1,false)'));?>可自定义权限</label>
        </td>
    </tr>
    </tbody>
    <?php
    }
    ?>
    <tr>
        <td class="td1"> </td>
        <td class="td1">
        <?php
        if ($show_edit_perm)
        {
        ?>
        <span id="step_submit_1"<?php if (!$member->is_super_admin)echo ' style="display:none;"'?>>
        <input type="button" class="submit" onclick="document.forms['myform'].onsubmit();" value="立即保存" />
        <input type="button" onclick="do_next_step()" value="查看权限" />
        </span>
        <input type="button" id="step_submit_2"<?php if ($member->is_super_admin) echo ' style="display:none;"'?> class="submit" onclick="do_next_step()" value="下一步" />
        <?php
        }
        else
        {
        ?>
        <input type="button" class="submit" onclick="document.forms['myform'].onsubmit();" value="立即保存" />
        <?php
        }
        ?>
        <input type="button" value="返回" onclick="window.history.go(-1)" />
        <span id="step1_loading" style="display:none;">请稍等...</span>
        </td>
    </tr>
</table>
</div>

<?php
if ($show_edit_perm)echo '<script type="text/javascript">change_group();</script>';
?>

<div id="mytag_main_2" style="display:none;">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="mainTable">
    <tr>
        <th colspan="2"><?php echo $title;?> - 权限设置</th>
    </tr>
    <tr>
        <td width="120" class="td1" align="right">权限设置：</td>
        <td class="td2" id="perm_div">
        </td>
    </tr>
    <tr>
        <td class="td1" align="right">权限设置：</td>
        <td class="td2">
            <label><?php echo Form::radio(null,0,$member->perm_setting?false:true,array('id'=>'zdy_perm_redio_a','onclick'=>'change_zdy_perm(0,true);'));?>仅使用所在组权限</label>
            <label><?php echo Form::radio(null,1,$member->perm_setting?true:false,array('id'=>'zdy_perm_redio_b','onclick'=>'change_zdy_perm(1,true);'));?>可自定义权限</label>
        </td>
    </tr>
    <tr>
        <td class="td1"> </td>
        <td class="td1">
        <input type="button" class="submit" onclick="document.forms['myform'].onsubmit();" value="立即保存" />
        <input type="button" value="上一步" onclick="do_per_step()" />
        <input type="button" value="返回" onclick="window.history.go(-1)" />
        </td>
    </tr>
</table>
</div>
</form>

<?php
if ($show_edit_perm)
{
?>
<script type="text/javascript">change_zdy_perm(<?php echo $member->perm_setting?1:0?>,false);</script>
<?php
}
?>