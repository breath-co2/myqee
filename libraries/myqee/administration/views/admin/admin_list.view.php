<table border="0" cellpadding="4" cellspacing="1" width="96%" align="center" class="mainTable">
<tr>
	<th width="70">ID</th>
	<th width="120">所属组</th>
	<th>用户名</th>
	<th width="80">登录次数</th>
	<th width="120">最后登录IP</th>
	<th width="280">操作</th>
</tr>
<?php
if($list):
	$i=0;
	$editadmin = '管理';
	$deleteadmin = '删除';
	$makesuredelete = '您确定要删除此管理员？';
	if (count($list)>0):
	foreach ($list as $item):
	$i++;
?>
<tr<?php if($i%2==0){echo ' class="td3"';} ?>>
	<td class="td1" align="center"><?php echo $item['id']?></td>
	<td class="td2" align="center">
	    <?php if ($item['groupid']>0){
            echo '<a href="',Core::url('group_edit/'.$item['groupid']),'">',$item['groupname'],'</a>';
	    }else{
	        echo $item['groupname'];
	    }
	    ?></td>
	<td class="td2"><?php echo $item['username']?></td>
	<td class="td2" align="center"><?php echo $item['countlogin']?></td>
	<td class="td2" align="center"><?php echo $item['lastloginip']?></td>
	<td class="td2" align="center">
        <input onclick="goUrl('<?php echo Core::url('admin/edit/'.$item['id']) ?>')" type="button" value="<?php echo $editadmin;?>" class="btnl" />
        <input onclick="ask_del(<?php echo $item['id']?>,'admin/del',makesuredelete);" type="button" value="<?php echo $deleteadmin;?>" class="btn" style="letter-spacing:0;" />
    </td>
</tr>
<?php
	endforeach;
	endif;
endif;
?>
<tr>
	<td class="td1">&nbsp;</td>
	<td class="td1" colspan="4">&nbsp;</td>
	<td class="td1" align="center">
	<input onclick="goUrl('<?php echo Core::url('admin/add') ?>')" type="button" value="添加管理员" />
	<input onclick="goUrl('<?php echo Core::url('admin/group_list') ?>')" type="button" value="管理管理组" />
	</td>
</tr>
</table>
<center><?php echo $page_html;?></center>