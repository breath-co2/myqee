<?php
$page_title = Myqee::lang('admin/admin.group_list.page_title');
$page_index = 'member';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('member/index');?>">用户管理</a> -&gt; <a href="<?php echo Myqee::url('admin/mylist');?>">管理员列表</a> -&gt; 管理组
</div>
<script type="text/javascript">
var makesuredelete = '<?php echo Myqee::lang('admin/admin.group_list.makesuredelete');?>';
</script>
<table border="0" cellpadding="4" cellspacing="1" width="96%" align="center" class="tableborder">
<tr>
	<th class="td1" width="70"><b><?php echo Myqee::lang('admin/admin.group_list.groupid');?></b></th>
	<th class="td1"><b><?php echo Myqee::lang('admin/admin.group_list.groupname');?></b></th>
	<th class="td1" width="280"><b><?php echo Myqee::lang('admin/admin.group_list.edit');?></b></th>
</tr>
<?php
if($list):
	$i=0;
	$editgroup = Myqee::lang('admin/admin.group_list.editgroup');
	$deletegroup = Myqee::lang('admin/admin.group_list.deletegroup');
	if (count($list)>0):
	foreach ($list as $item):
	$i++;
?>
<tr<?php if($i%2==0){echo ' class="td3"';} ?> onmouseover="tr_moveover(this)" onmouseout="tr_moveout(this)">
	<td class="td1" align="center"><?php echo $item['id']?></td>
	<td class="td2"><?php echo $item['groupname']?$item['groupname']:'自定义';?></td>
	<td class="td2" align="center"><a href="<?php echo Myqee::url('admin/group_edit/'.$item['id']) ?>" class="btnl"><?php echo $editgroup;?></a> <input onclick="ask_del(<?php echo $item['id'];?>,'admin/group_del',makesuredelete);" type="button" value="<?php echo $deletegroup;?>" class="btn" style="letter-spacing:0;" /></td>
</tr>
<?php
	endforeach;
	endif;
endif;
?>
<tr>
	<td class="td1">&nbsp;</td>
	<td class="td1">&nbsp;</td>
	<td class="td1" align="center">
	<a href="<?php echo Myqee::url('admin/group_add') ?>" class="bbtn"><?php echo Myqee::lang('admin/admin.group_list.addgroup');?></a>
	<a href="<?php echo Myqee::url('admin/mylist') ?>" class="bbtn" style="letter-spacing:0px;"><?php echo Myqee::lang('admin/admin.list.page_title');?></a>
	</td>
</tr>
</table>
<center><?php echo $page;?></center>

<?php View::factory('admin/footer') -> render(TRUE);?>