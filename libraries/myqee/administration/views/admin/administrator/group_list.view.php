<table border="0" cellpadding="4" cellspacing="1" align="center" class="mainTable">
    <tr>
        <th width="60">ID</th>
        <th>管理组名称</th>
        <th>管理组说明</th>
        <th width="180">操作</th>
    </tr>
    <?php
    $member_id = Session::instance()->member()->id;
    if ($group_manager)
    {
        $groups_setting = Session::instance()->member()->groups_setting();
    }
    if ($list)foreach ($list as $item)
    {
        if (false)$item = new ORM_Admin_MemberGroup_Data();
    ?>
    <tr align="center">
        <td class="td1"><?php echo $item->id;?></td>
        <td class="td2"><?php echo $item->group_name;?></td>
        <td class="td2"><?php echo $item->group_desc;?></td>
        <td class="td2">
        <input type="button" value="查看成员"<?php if ( $group_manager && !$groups_setting[$item->id]['view_users'])echo ' disabled="disabled"'; ?> onclick="goto('<?php echo Core::url('administrator/?group_id='.$item->id);?>')" />
        <input type="button" value="修改"<?php if ( $group_manager && !$groups_setting[$item->id]['edit_group'])echo ' disabled="disabled"'; ?> onclick="goto('<?php echo Core::url('administrator/group/edit/'.$item->id);?>')" />
        <input type="button" value="删除"<?php if ($group_manager)echo ' disabled="disabled"';?> onclick="MyQEE.ask_todo('<?php echo Core::url('administrator/group/delete/'.$item->id);?>','您确认要删除此管理组？')" />
        </td>
    </tr>
    <?php
    }
    ?>
    <tr>
        <td class="td1" colspan="3"> </td>
        <td class="td1" align="center">
        <input type="button" class="submit" value="添加管理组"<?php if ($group_manager)echo ' disabled="disabled"';?> onclick="goto('<?php echo Core::url('administrator/group/add/');?>')" />
        </td>
    </tr>
</table>

<center>
<?php echo $pagehtml;?>
</center>