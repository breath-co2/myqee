<?php
$page_index = 'model';
if ($model['id']){
	$page_title ='修改模型设置';
}else{
	$page_title ='新建模型';
}
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('model/index');?>">模型管理</a> -&gt; <?php echo $page_title;?>
</div>
<form method="post" name="myforms" action="<?php echo Myqee::url('model/save/'.$model['id']);?>" target="hiddenFrame">
<table border="0" cellpadding="4" cellspacing="1" width="96%" align="center" class="tableborder">
<tr>
	<th colspan="2">模型设置 - <?php echo $page_title;?></th>
</tr>
<tr>
	<td class="td1" align="right" width="180">模型名称：</td>
	<td class="td2"><?php echo form::input('model[modelname]',$model['modelname'],' size="25" class="input"');?> &nbsp;<font color="#a3a3a3">例如“新闻模型”</font></td>
</tr>
<tr>
	<td class="td1" align="right">排序：</td>
	<td class="td2">
		<?php echo form::input('model[myorder]',$model['myorder'],'size="4" class="input"');?> &nbsp;<font color="#a3a3a3">数字越高越靠前</font>
	</td>
</tr>
<tr>
	<td class="td1" align="right">是否启用：</td>
	<td class="td2">
		<?php echo form::dropdown('model[isuse]',array(1=>'是',0=>'否'),$model['isuse']);?>
	</td>
</tr>
<tr>
	<td class="td1" align="right">是否默认：</td>
	<td class="td2">
		<?php echo form::dropdown('model[isdefault]',array(1=>'是',0=>'否'),(int)$model['isdefault']);?>
	</td>
</tr>
<tr>
	<td class="td1" align="right">选择数据表：</td>
	<td class="td2">
		<?php echo form::dropdown('model[dbname]',(array)$dblist,$model['dbname']);?> &nbsp;<font style="color:red">选择数据表请谨慎操作！</font>
	</td>
</tr>
<tr>
	<td class="td1" align="right">&nbsp;</td>
	<td class="td1"><input type="submit" value="保存模型" class="bbtn" /> <input onclick="goback(-1,'<?php echo Myqee::url('model/index'); ?>')" type="button" value="返回" class="btns" /></td>
</tr>
</table>
</form>

<?php View::factory('admin/footer') -> render(TRUE);?>