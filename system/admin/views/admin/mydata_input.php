<?php
$page_index = 'info';
$page_title = '导入模型';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('mydata/index');?>">数据调用</a> -&gt; 导入任务
</div>

<div style="width:96%;margin:auto;">
<ul class="ul tag">
	<li class="now" id="mytag_1" onclick="tag(this.id,'mytag','mytagmain')">输入方式导入</li>
	<li id="mytag_2" onclick="tag(this.id,'mytag','mytagmain')">上传方式导入</li>
</ul>
</div>
<div style="clear:both"></div>

<form method="post" name="myforms" action="<?php echo Myqee::url('mydata/input_save/');?>" target="hiddenFrame" enctype="multipart/form-data">
<div id="mytagmain_1">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-bottom:none;">
	<tr>
		<th class="td1" colspan="2">输入方式导入</th>
	</tr>
	<tr>
		<td class="td1" align="right" width="150">待导入内容：</td>
		<td class="td2"><textarea name="data" style="width:500px;overflow:auto;height:200px;" class="input"></textarea></td>
	</tr>
</table>
</div>

<div id="mytagmain_2" style="display:none;">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-bottom:none;">
	<tr>
		<th class="td1" colspan="2">上传方式导入</th>
	</tr>
	<tr>
		<td class="td1" align="right" width="150">上传任务文件：</td>
		<td class="td2"><input type="file" class="input" name="upload" size="50" /></td>
	</tr>
</table>
</div>

<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-top:none;">
	<tr>
		<td class="td1" align="right" width="150">任务导入密码：</td>
		<td class="td2"><input type="text" name="key" size="20" class="input" value="" />  &nbsp;<font color="#a3a3a3">若模型无加密密码，则留空即可</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">&nbsp;</td>
		<td class="td1"><input type="submit" class="bbtn" value="导入调用" /> 
		<input onclick="goback(-1,'<?php echo Myqee::url('mydata/index'); ?>')" type="button" value="返回" class="btns" /></td>
	</tr>
</table>
</form>

<?php View::factory('admin/footer') -> render(TRUE);?>