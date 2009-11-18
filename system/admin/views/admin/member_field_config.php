<?php
$page_index = 'member';
$page_title = '字段关联';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
$makesuredelete = '您确认要删除此字段关联？';
?>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('member/index');?>">用户列表</a> -&gt; 字段关联
</div>
<table border="1" cellpadding="4" cellspacing="1" width="96%" align="center" class="tableborder">
<tr>
	<th class="td1" width="20">&nbsp;</th>
	<th class="td1" width="50">ID</th>
	<th class="td1">字段关联</th>
	<th class="td1" width="280">注释</th>
	<th class="td1" width="30">启用</th>
	<th class="td1" width="130" align="center" >操作</th>
</tr>
<?php foreach ($memberConfig as $key => $member){
	if(!is_array($member))continue;
?>
<tr>
	<td class="td1">
		<input type="checkbox" id="select_id_<?php echo $key;?>"/>
	</td>
	<td class="td1" align="center">
	<?php echo $key;?>
	</td>
	<td class="td1"><?php echo $member['field'];?></td>
	<td class="td1"><?php echo $member['comment'];?></td>
	<td class="td2" align="center" >
		<?php if ($member['is_use']){echo '是';}else{echo '<font color="red">否</font>';}?>
	</td>
	<td class="td2" align="center" >
		<input onclick="goUrl('<?php echo Myqee::url('member/addfield/'.$key);?>')" type="button" value="编辑" class="btns" />
		<input onclick="ask_del(<?php echo $key;?>,'member/delfield','<?php echo $makesuredelete;?>')" type="button" value="删除" class="btns" />
	</td>
</tr>
<?php }?>
</table>
<table border="0" cellpadding="4" cellspacing="1" width="96%" align="center" class="tableborder" style="border-top:none;">
<tr>
	<td class="td1" width="20" align="center">
		<input type="checkbox" title="选择上面全部" onclick="selectbox(this,'select_id')" />
	</td>
	<td class="td1" colspan="3">
		&nbsp;
	</td>
	<td class="td1" width="130" align="center">
		<input type="button" class="bbtn" value="添加" style="letter-spacing:0" onclick="goUrl('<?php echo Myqee::url('member/addfield')?>')" />
	</td>
</tr>
</table>

<center><?php echo $page; ?></center>
<?php View::factory('admin/footer') -> render(TRUE);?>