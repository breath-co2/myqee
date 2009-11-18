<?php
$page_index = 'info';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>

<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('uploadfile/index');?>">上传管理</a> -&gt; <?php echo $page_title;?>
</div>
<form method="post" name="myforms" action="<?php echo Myqee::url('uploadfile/save/'.$model['id']);?>" target="hiddenFrame">
<?php
if ((int)$copyid>0)echo form::hidden('copyid',($copyid));
?>
<table border="0" cellpadding="4" cellspacing="1" width="96%" align="center" class="tableborder">
<tr>
	<th colspan="2">文件设置 - <?php echo $page_title;?></th>
</tr>
<tr>
	<td class="td1" align="right" width="80">文件名称：</td>
	<td class="td2"><?php echo form::input('model[modelname]',$model['modelname'],' size="25" class="input"');?> &nbsp;<font color="#a3a3a3">例如“新闻模型”</font></td>
</tr>
<tr>
	<td class="td1" align="right">URL路径：</td>
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
	<td class="td1" align="right">&nbsp;</td>
	<td class="td1"><input type="submit" value="保存模型" class="bbtn" /> <input onclick="goback(-1,'<?php echo Myqee::url('model/index') ?>')" type="button" value="返回" class="btns" /></td>
</tr>
</table>
</form>

<?php View::factory('admin/footer') -> render(TRUE);?>