<?php
$page_title = Myqee::lang('admin/admin.list.page_title');
$page_index = 'member';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('member/index');?>">用户管理</a> -&gt; 管理员列表
</div>
<script type="text/javascript">
var makesuredelete = '<?php echo $makesuredelete;?>';
</script>
<table border="0" cellpadding="4" cellspacing="1" width="96%" align="center" class="tableborder">
<tr>
	<th class="td1" width="70"><b><?php echo Myqee::lang('admin/admin.list.adminid');?></b></th>
	<th class="td1" width="120"><b><?php echo Myqee::lang('admin/admin.list.group');?></b></th>
	<th class="td1"><b><?php echo Myqee::lang('admin/admin.list.adminname');?></b></th>
	<th class="td1" width="80"><b><?php echo Myqee::lang('admin/admin.list.loginnum');?></b></th>
	<th class="td1" width="120"><b><?php echo Myqee::lang('admin/admin.list.lastloginip');?></b></th>
	<th class="td1" width="280"><b><?php echo Myqee::lang('admin/admin.list.edit');?></b></th>
</tr>
<?php
if($list):
	$i=0;
	$editadmin = Myqee::lang('admin/admin.list.editadmin');
	$deleteadmin = Myqee::lang('admin/admin.list.deleteadmin');
	$makesuredelete = Myqee::lang('admin/admin.list.makesuredelete');
	if (count($list)>0):
	foreach ($list as $item):
	$i++;
?>
<tr<?php if($i%2==0){echo ' class="td3"';} ?> onmouseover="tr_moveover(this)" onmouseout="tr_moveout(this)">
	<td class="td1" align="center"><?php echo $item['id']?></td>
	<td class="td2" align="center"><?php if ($item['groupid']>0):?><a href="<?php echo Myqee::url('admin/group_edit/'.$item['groupid']); ?>"><?php echo $item['groupname'];?></a><?php else:echo $item['groupname'];endif;?></td>
	<td class="td2"><?php echo $item['username']?></td>
	<td class="td2" align="center"><?php echo $item['countlogin']?></td>
	<td class="td2" align="center"><?php echo $item['lastloginip']?></td>
	<td class="td2" align="center"><input onclick="goUrl('<?php echo Myqee::url('admin/edit/'.$item['id']) ?>')" type="button" value="<?php echo $editadmin;?>" class="btnl" /> <input onclick="ask_del(<?php echo $item['id']?>,'admin/del',makesuredelete);" type="button" value="<?php echo $deleteadmin;?>" class="btn" style="letter-spacing:0;" /></td>
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
	<input onclick="goUrl('<?php echo Myqee::url('admin/add') ?>')" type="button" value="<?php echo Myqee::lang('admin/admin.list.addadmin');?>" class="bbtn" />
	<input onclick="goUrl('<?php echo Myqee::url('admin/group_list') ?>')" type="button" value="<?php echo Myqee::lang('admin/admin.group_list.page_title');?>" class="bbtn" />
	</td>
</tr>
</table>
<center><?php echo $page;?></center>

<?php View::factory('admin/footer') -> render(TRUE);?>