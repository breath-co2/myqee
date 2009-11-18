<?php
$page_index = 'template';
$page_title = '导入模板';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('template/index');?>">模板管理</a> -&gt; 导入模板
</div>

<div style="width:96%;margin:auto;">
<ul class="ul tag">
	<li class="now" id="mytag_1" onclick="tag(this.id,'mytag','mytagmain');$('upload_fileDiv').innerHTML=$('upload_fileDiv').innerHTML">输入方式导入</li>
	<li id="mytag_2" onclick="tag(this.id,'mytag','mytagmain')">上传方式导入</li>
</ul>
</div>
<div style="clear:both"></div>

<form method="post" name="myforms" action="<?php echo Myqee::url('template/input/');?>" target="hiddenFrame" enctype="multipart/form-data">
<div id="mytagmain_1">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-bottom:none;">
	<tr>
		<th class="td1" colspan="2">输入方式导入</th>
	</tr>
	<tr>
		<td class="td1" align="right" width="150">待导入内容：</td>
		<td class="td2"><textarea name="template" style="width:500px;overflow:auto;height:200px;" class="input"></textarea></td>
	</tr>
</table>
</div>

<div id="mytagmain_2" style="display:none;">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-bottom:none;">
	<tr>
		<th class="td1" colspan="2">上传方式导入</th>
	</tr>
 	<tr>
		<td class="td1" align="right" width="150">上传模板文件：</td>
		<td class="td2" id="upload_fileDiv"><input type="file" class="input" name="upload" size="50" /></td>
	</tr>
</table>
</div>

<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-top:none;">
	<tr>
		<td class="td1" align="right" width="150">模板导入密码：</td>
		<td class="td2"><input type="text" name="key" size="20" class="input" value="" />  &nbsp;<font color="#a3a3a3">若模板无加密密码，则留空即可</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">选择导入到的模板组：</td>
		<td class="td2"><?php echo form::dropdown('group',$tplgroup,$nowtplgroup,'id="group"');?> <input name="isnewgroup" value="1" onfocus="this.blur()" type="checkbox" onclick="if(this.checked){$('newTplGroup').style.visibility='visible';$('newgroup').select();$('group').disabled = true;}else{$('newTplGroup').style.visibility='hidden';$('group').disabled = false;}" />新建模板组 <span id="newTplGroup" style="visibility:hidden;"><input id="newgroup" name="newgroup" size="15" class="input" /> &nbsp;<font color="#a3a3a3">模板组只能是字母、数字、下划线组合</font></span></td>
	</tr>
	<tr>
		<td class="td1" align="right">&nbsp;</td>
		<td class="td1"><input type="submit" class="bbtn" value="导入模板" /> <input onclick="goback(-1,'<?php echo Myqee::url('template/index') ?>')" type="button" value="返回" class="btns" /></td>
	</tr>
</table>

</form>

<?php View::factory('admin/footer') -> render(TRUE);?>