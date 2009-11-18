<?php
$page_index = 'member';
if((int)$id >= 0 && $id != '' && $id != NULL){
$page_title = '字段关联编辑';
}else{
$page_title = '字段关联添加';
}
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>

<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('member/index');?>">用户列表</a> -&gt; <?php echo $page_title;?>
</div>
<form method="post" name="myforms" action="<?php echo Myqee::url('member/savefield/'.$id);?>" target="hiddenFrame" >
<table border="2" cellpadding="4" cellspacing="1" width="96%" align="center" class="tableborder" >
<tr>
	<th colspan="2">字段关联</th>
</tr>
<tr>
	<td class="td1" align="right" width="90">注释：</td>
	<td class="td2" width="910">
		<?php echo form::input('comment',$member['comment'],' size="39" class="input"');?>
	</td>
</tr>
<tr>
	<td class="td1" align="right">是否启用：</td>
	<td class="td2">
		<?php echo form::dropdown('is_use',array(1=>'是',0=>'否'),$member['is_use']);?>
	</td>
</tr>
<tr>
	<td class="td1" align="right">字段关联设置：</td>
	<td class="td2">
		<table width="100%" border="0" cellpadding="4" cellspacing="1" align="left" class="tableborder" >
			<tr>
				<th class="td1" width="10%">表名</th>
				<th class="td1" width="20%">字段名</th>
				<th class="td1" width="70%">&nbsp;</th>
			</tr>
			<?php 
			$i = 0;
			foreach ($dblists as $key => $db){ 
			$dbname = 'dbname_'.$i;
			$field = 'field_'.$i;
			?>
			<tr>
				<td class="td1" align="left" width="10%"><?php echo $db['name'];?>：<input type="hidden" name="<?=$dbname ?>" value="<?=$db['name'] ?>" /></td>
				<td class="td2" width="20%">
					<?php echo form::dropdown($field,$db['select'],$member[$db['name']]);?>
				</td>
				<td width="70%">
					&nbsp;
				</td>
			</tr>
			<?php $i++; }?>
		</table>
	</td>
</tr>
<tr>
	<td class="td1" align="right">&nbsp;<input type="hidden" name="field_num" value="<?=$i ?>" /></td>
	<td class="td1" align="left" >
		<input type="submit" value="关联" class="bbtn" />
		<input onclick="goback(-1,'<?php echo Myqee::url('member/index') ?>')" type="button" value="返回" class="btns" />
	</td>
</tr>
</table>
</form>
<?php View::factory('admin/footer') -> render(TRUE);?>
