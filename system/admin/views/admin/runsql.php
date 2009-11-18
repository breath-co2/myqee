<?php
$page_index = 'index';
$page_title = '执行SQL语句';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; 执行SQL语句
</div>


<div style="width:96%;margin:auto;">
<ul class="ul tag">
	<li class="now" id="mytag_1" onclick="tag(this.id,'mytag','mytagmain');$('upload_fileDiv').innerHTML=$('upload_fileDiv').innerHTML">输入方式执行</li>
	<li id="mytag_2" onclick="tag(this.id,'mytag','mytagmain')">上传方式执行</li>
</ul>
</div>
<div style="clear:both"></div>

<form method="post" name="myforms" action="<?php echo $url?$url:Myqee::url('index/runsql_post');?>" target="hiddenFrame" enctype="multipart/form-data">
<div id="mytagmain_1">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-bottom:none;">
	<tr>
		<th class="td1" colspan="2">输入方式执行SQL</th>
	</tr>
	<tr>
		<td class="td1" align="right" width="150">待执行SQL语句：</td>
		<td class="td2"><textarea name="data" style="width:500px;overflow:auto;height:200px;" class="input"></textarea><br/>
		多条SQL语句用请;回车分开。</td>
	</tr>
</table>
</div>

<div id="mytagmain_2" style="display:none;">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-bottom:none;">
	<tr>
		<th class="td1" colspan="2">上传方式执行SQL</th>
	</tr>
	<tr>
		<td class="td1" align="right" width="150">上传SQL文件：</td>
		<td class="td2" id="upload_fileDiv"><input type="file" class="input" name="upload" size="50" /></td>
	</tr>
	<tr>
		<td class="td1" align="right" width="150">文件编码：</td>
		<td class="td2"><select name="upload_charset"><option value="">默认(UTF-8)</option><option value="GBK">GBK</option></select></td>
	</tr>
</table>
</div>

<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-top:none;">
	<tr>
		<td class="td1" align="right" width="150">执行前编码转换：</td>
		<td class="td2"><select name="changecharset"><option value="">不转换</option><option value="UTF-8">转为UTF-8</option><option value="GBK">转为GBK</option></select></td>
	</tr>
	<tr>
		<td class="td1" align="right">数据表配置：</td>
		<td class="td2"><?php echo form::dropdown('configname',$configname,'default')?></td>
	</tr>
	<tr>
		<td class="td1" align="right">&nbsp;</td>
		<td class="td1"><input type="submit" class="bbtn" value="执行SQL" /> <input onclick="goback(-1,'<?php echo Myqee::url('index/welcome') ?>')" type="button" value="返回" class="btns" /> 表前缀可以用<font color="red">{{table_prefix}}</font>代替，多条SQL语句用请;回车分开。</td>
	</tr>
</table>

</form>

<?php View::factory('admin/footer') -> render(TRUE);?>