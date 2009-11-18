<?php
$page_title = '安装插件';
$page_index = 'plugins';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<script type="text/javascript" src="<?php echo ADMIN_IMGPATH;?>/admin/swfupload/uploadfun.js"></script>

<div class="loaction" >
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('plugins');?>">插件管理</a> -&gt; 安装插件
</div>

<form method="post" name="myforms" action="<?php echo Myqee::url('plugins/setup_save/');?>" target="hiddenFrame" enctype="multipart/form-data" >


<table border="0" cellpadding="4" cellspacing="1" align="center" id="showstep1" class="tableborder">
	<tr>
		<th class="td1" colspan="2">欢迎使用插件安装程序</th>
	</tr>
	<tr height="100">
		<td class="td1" width="180" align="right">请选择安装方式：</td>
		<td class="td2">
			<?php echo form::dropdown('filetype',array('localfile'=>'本地上传安装包','serverfile'=>'已传到服务器临时目录的安装包','remotefile'=>'远程直接下载'),'localfile','id="filetype" style="vertical-align:middle;"'); ?> 
			<input type="button" class="bbtn" value="下一步" style="vertical-align:middle;" onclick="$('mytag_'+$('filetype').value).onclick();$('setup_div').style.display='';$('showstep1').style.display='none';$('upfile').value=''" />
		</td>
	</tr>
</table>


<ul class="ul tag" style="display:none;">
	<li class="now" id="mytag_localfile" style="width:60px" onclick="tag(this.id,'mytag','mytagmain');"><center>本地</center></li>
	<li id="mytag_remotefile" style="width:60px;" onclick="tag(this.id,'mytag','mytagmain');" ><center>远程</center></li>
	<li id="mytag_serverfile" style="width:60px" onclick="tag(this.id,'mytag','mytagmain');"><center>服务器</center></li>
</ul>

<div style="clear:both"></div>

<div id="setup_div" style="display:none">

<div id="mytagmain_remotefile" style="display:none;">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-bottom:none;" >
	<tr>
		<th class="td1" colspan="2">远程文件安装</th>
	</tr>
	<tr>
		<td class="td1" align="right" width="180">远程文件地址：</td>
		<td class="td2">
		<input type="hidden" id="redownload" name="redownload" /> 
		<input type="text" id="url" name="url" size="50" class="input" value="http://" /> 
		由程序直接远程抓取
		</td>
	</tr>
</table>
</div>

<div id="mytagmain_localfile" style="display:none;">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-bottom:none;" >
	<tr>
		<th class="td1" colspan="2">本地文件安装</th>
	</tr>
	<tr>
		<td class="td1" align="right" width="180">本地文件：</td>
		<td class="td2">
		<input type="file" class="input" id="upfile" name="upfile" size="40" />
		插件文件是以.plugins.zip为后缀的文件
		</td>
	</tr>
</table>
</div>

<div id="mytagmain_serverfile" style="display:none;">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-bottom:none;" >
	<tr>
		<th class="td1" colspan="2">服务器文件安装</th>
	</tr>
	<tr>
		<td class="td1" align="right" width="180">选择服务器文件：</td>
		<td class="td2">
			<?php echo form::dropdown('server_file',$files,''); ?>
		</td>
	</tr>
</table>
</div>

<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" id="submitdiv" style="border-top:none;">
	<tr>
		<td class="td1" align="right">MD5校验：</td>
		<td class="td2"><input type="text" id="md5" name="md5" size="30" maxlength="32" class="input" /> 
		可校验安装文件是否正确，留空则不校验MD5
		</td>
	</tr>
	<tr>
		<td class="td1" align="right">安装密码：</td>
		<td class="td2"><input type="text" id="md5" name="pass" size="16" class="input" /> 
		请输入插件安装密码，留空则表示没有。
		</td>
	</tr>
	<tr>
		<td class="td1" align="right">安装目录：</td>
		<td class="td2"><input type="text" name="path" size="10" maxlength="32" class="input" /> 
		留空则为插件默认目录，<font color="#ff3300">建议留空</font>，目录只允许数字字母下划线的组合
		</td>
	</tr>
	<tr>
		<td class="td1" align="right">安装完成后删除安装文件：</td>
		<td class="td2" align="left">
			<?php echo form::radio('isdel',0,TRUE); ?>否
			<?php echo form::radio('isdel',1,FALSE); ?>是
		</td>
	</tr>
	<tr>
		<td class="td1" align="right" width="180">&nbsp;</td>
		<td class="td1">
		<input type="submit" class="bbtn" value="开始安装" />
		<input onclick="$('setup_div').style.display='none';$('showstep1').style.display='';" type="button" value="返回上步" class="btn" />
		安装过程可能需要几分钟，请耐心等待。
		</td>
	</tr>
</table>
</div>

</form>
<?php View::factory('admin/footer') -> render(TRUE);?>
