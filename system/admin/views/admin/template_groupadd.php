<?php
$page_index = 'template';
$page_title = $thisgroup?'修改模板组':'新建模板组';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('template/index');?>">模板管理</a> -&gt; <a href="<?php echo Myqee::url('template/grouplist');?>">模板组管理</a> -&gt; <?php echo $page_title;?>
</div>

<form method="post" name="myforms" action="<?php echo Myqee::url('template/gropusave/'.$thisgroup);?>" target="hiddenFrame">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
	<tr>
		<th class="td1" colspan="2"><?php echo $page_title;?></th>
	</tr>
	<tr>
		<td class="td1" align="right" width="150">模板组目录：</td>
		<td class="td2"><input type="input" class="input" name="id" size="15" value="<?php echo $thisgroup;?>" />  &nbsp;<font color="#a3a3a3">只允许字母、数字和下划线</font></td>
	</tr>
	<tr>
		<td class="td1" align="right" width="150">模板组别名：</td>
		<td class="td2"><input type="text" name="name" size="15" class="input" value="<?php echo $group['name'];?>" />  &nbsp;<font color="#a3a3a3">为此模板添加名称</font></td>
	</tr>
	<tr>
		<td class="td1" align="right" width="150">是否默认模板组：</td>
		<td class="td2"><?php echo form::checkbox('isdefault','1',$defaultgroup==$thisgroup?1:0);?>&nbsp;<font color="#a3a3a3">设为默认模板组</font></td>
	</tr>
	<tr>
		<td class="td1" align="right" width="150">绑定模板引擎：</td>
		<td class="td2"><?php echo form::dropdown('engine',$engine_set,$group['engine']);?> 
		<font color="#a3a3a3">修改模板引擎将使本组内所有模板使用新引擎。</font></td>
	</tr>
	<tr>
		<td class="td1" align="right" width="150">默认模板后缀：</td>
		<td class="td2"><?php echo form::input('suffix',$group['suffix'],'class="input" size="4"');?> 例如：.tpl，留空则使用默认</td>
	</tr>
	<tr>
		<td class="td1" align="right" width="150">允许的模板后缀：</td>
		<td class="td2"><?php echo form::textarea('allsuffix',$group['allsuffix'],'class="input" cols="40" rows="2"');?> 模板之间用“|”隔开，留空则使用默认</td>
	</tr>
	<tr>
		<td class="td1" align="right">&nbsp;</td>
		<td class="td1"><input type="submit" class="bbtn" value="保存" /> <input onclick="goback(-1,'<?php echo Myqee::url('template/grouplist') ?>')" type="button" value="返回" class="btns" /></td>
	</tr>
</table>

</form>

<?php View::factory('admin/footer') -> render(TRUE);?>