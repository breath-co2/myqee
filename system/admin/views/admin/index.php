<?php
$page_index = 'index';
$page_title = Myqee::lang('admin/index.page_title');
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<table border="0" width="900" align="center">
	<tr>
		<td align="center" height="50"><br/>
			<h2 style="font-family:'楷体_GB2312','楷体';font-size:25px;"><?php if($welcome=Myqee::config('core.admin_welcome')){echo $welcome;}else{echo '欢迎您使用麦琪内容管理系统';}?></h2></td>
	</tr>
</table>
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
	<tr>
		<th class="td1" colspan="2">基本信息</th>
	</tr>
	<tr>
		<td align="right" class="td1">您所在的管理组：</td>
		<td class="td2"><?php echo Session::instance()->get('admin.groupname');?></td>
	</tr>
	<tr>
		<td align="right" class="td1">当前管理的站点：</td>
		<td class="td2"><font style="color:red"><?php echo $_SESSION['now_site']?$_SESSION['now_site_name']:'[主站点]';?></font> <input type="button" value="切换站点" onclick="goUrl('<?php echo Myqee::url('site/index');?>')" class="btn" /></td>
	</tr>
	<tr>
		<td width="20%" align="right" class="td1">版本信息：</td>
		<td class="td2">当前版本：<b style="color:blue"><?php echo MYQEE_VERSION;?></b> &nbsp;&nbsp;
		<!-- 最新版本：<span id="newvision_div" style="color:red">检测中...</span>--></td>
	</tr>
	<tr>
		<td align="right" class="td1">技术支持：</td>
		<td class="td2">麦琪网(<a href="http://www.myqee.com/" target="_blank">www.myqee.com</a>) &nbsp;&nbsp;&nbsp;论坛(<a href="http://bbs.myqee.com/" target="_blank">bbs.myqee.com</a>) </td>
	</tr>
	<tr>
		<td align="right" class="td1">开发作者：</td>
		<td class="td2">麦琪内容管理系统发小组</td>
	</tr>
</table>
<br/>
<?php
if (Passport::getisallow('index.indexshowpath')):?>
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
	<tr>
		<th class="td1" colspan="4">服务器信息</th>
	</tr>
	<tr>
		<td width="20%" align="right" class="td1">服务器操作系统：</td>
		<td width="40%" class="td2"><?php $os = explode(" ", php_uname());echo $os[0];?>
&nbsp;&nbsp;内核版本：<?php echo MYQEE_IS_WIN===true?$os[1]:$os[2];?></td>
		<td width="20%" align="right" class="td1">mysql数据库：</td>
		<td width="20%" class="td2"><?php if(function_exists("mysql_close") == 1){ echo "<font color='green'><font color='green'><b>√</b></font></font>&nbsp;支持"; }else{ echo "<font color='red'><b>×</b></font>&nbsp;不支持"; }?></td>
	<tr>
		<td width="20%" align="right" class="td1">服务器名称：</td>
		<td width="40%" class="td2"><?php echo MYQEE_IS_WIN===true?$os[2]:$os[1];?></td>
		<td align="right" class="td1">odbc数据库：</td>
		<td class="td2"><?php if(function_exists("odbc_close") == 1){ echo "<font color='green'><b>√</b></font>&nbsp;支持"; }else{ echo "<font color='red'><b>×</b></font>&nbsp;不支持"; }?></td>
	</tr>
	<tr>
		<td align="right" class="td1">服务器时间：</td>
		<td class="td2"><?php echo date("Y年m月d日 H:i:s")?> <span class="helpicon" title="北京时间：<?php echo gmdate("Y年n月j日 H:i:s",time()+8*3600);?>">&nbsp;</span></td>
		<td align="right" class="td1"> SQL Server数据库：</td>
		<td class="td2"><?php if(function_exists("mssql_close") == 1){ echo "<font color='green'><b>√</b></font>&nbsp;支持"; }else{ echo "<font color='red'><b>×</b></font>&nbsp;不支持"; }?></td>
	</tr>
	<tr>
		<td align="right" class="td1">服务器IP：</td>
		<td class="td2"><?php echo $_SERVER['SERVER_ADDR']?></td>
		<td align="right" class="td1">msql数据库：</td>
		<td class="td2"><?php if(function_exists("msql_close") == 1){ echo "<font color='green'><b>√</b></font>&nbsp;支持"; }else{ echo "<font color='red'><b>×</b></font>&nbsp;不支持"; }?></td>
	</tr>
	<tr>
		<td align="right" class="td1">服务器端口：</td>
		<td class="td2"><?php echo $_SERVER["SERVER_PORT"]?></td>
		<td align="right" class="td1">SMTP：</td>
		<td class="td2"><?php if(get_magic_quotes_gpc("smtp")){ echo "<font color='green'><b>√</b></font>&nbsp;支持"; }else{ echo "<font color='red'><b>×</b></font>&nbsp;不支持"; }?></td>
	</tr>
	<tr>
		<td align="right" class="td1">服务器管理员：</td>
		<td class="td2"><?php echo $_SERVER['SERVER_ADMIN'];?></td>
		<td align="right" class="td1">图形处理 GD Library：</td>
		<td class="td2"><?php if(function_exists("imageline") == 1){ echo "<font color='green'><b>√</b></font>&nbsp;支持"; }else{ echo "<font color='red'><b>×</b></font>&nbsp;不支持"; }?></td>
	</tr>
	<tr>
		<td align="right" class="td1">您的IP：</td>
		<td class="td2"><?php echo $_SERVER["REMOTE_ADDR"]?></td>
		<td align="right" class="td1">XML：</td>
		<td class="td2"><?php if(function_exists("xml_set_object")){ echo "<font color='green'><b>√</b></font>&nbsp;支持"; }else{ echo "<font color='red'><b>×</b></font>&nbsp;不支持"; }?></td>
	</tr>
	<tr>
		<td align="right" class="td1">PHP版本：</td>
		<td class="td2"><?php echo PHP_VERSION?></td>
		<td align="right" class="td1">FTP：</td>
		<td class="td2"><?php if(function_exists("ftp_login")){ echo "<font color='green'><b>√</b></font>&nbsp;支持"; }else{ echo "<font color='red'><b>×</b></font>&nbsp;不支持"; }?></td>
	</tr>
	<tr>
		<td align="right" class="td1">服务器解译引擎：</td>
		<td class="td2"><?php echo $_SERVER["SERVER_SOFTWARE"]?></td>
		<td align="right" class="td1">显示错误信息：</td>
		<td class="td2"><?php if(get_cfg_var("display_errors") == 1){ echo "<font color='green'><b>√</b></font>&nbsp;是"; }else{ echo "<font color='red'><b>×</b></font>&nbsp;否"; }?></td>
	</tr>
	<tr>
		<td align="right" class="td1">脚本超时时间：</td>
		<td class="td2"><?php echo get_cfg_var("max_execution_time")?> 秒</td>
		<td align="right" class="td1">允许打开远程文件：</td>
		<td class="td2"><?php if(get_cfg_var("allow_url_fopen") == 1){ echo "<font color='green'><b>√</b></font>&nbsp;支持"; }else{ echo "<font color='red'><b>×</b></font>&nbsp;不支持"; }?></td>
	</tr>
	<tr>
		<td align="right" class="td1">脚本上传文件大小限制：</td>
		<td class="td2"><?php echo (get_cfg_var("upload_max_filesize")?get_cfg_var("upload_max_filesize"):"不允许上传附件")?> <span class="helpicon" title="本项和下面的POST提交大小限制将决定了上传文件的大小！">&nbsp;</span></td>
		<td align="right" class="td1">压缩文件支持(Zlib)：</td>
		<td class="td2"><?php if(function_exists("gzclose")){ echo "<font color='green'><b>√</b></font>&nbsp;支持"; }else{ echo "<font color='red'><b>×</b></font>&nbsp;不支持"; }?></td>
	</tr>
	<tr>
		<td align="right" class="td1">POST提交内容限制：</td>
		<td class="td2"><?php echo get_cfg_var("post_max_size")?></td>
		<td align="right" class="td1">ZEND支持：</td>
		<td class="td2"><?php echo (get_cfg_var("zend_optimizer.optimization_level")||get_cfg_var("zend_extension_manager.optimizer_ts")||get_cfg_var("zend_extension_ts")) ?"<font color='green'><b>√</b></font>&nbsp;支持":'<font color="red"><b>×</b></font>&nbsp;不支持'?>		</td>
	</tr>
	<tr>
		<td align="right" class="td1">脚本运行时可占最大内存：</td>
		<td class="td2"><?php echo (get_cfg_var("memory_limit")?get_cfg_var("memory_limit"):"无")?></td>
		<td align="right" class="td1">编码转换：</td>
		<td class="td2"><?php if(function_exists("mb_convert_encoding") == 1){ echo "<font color='green'><b>√</b></font>&nbsp;支持"; }else{ echo "<font color='red'><b>×</b></font>&nbsp;不支持"; }?></td>
	</tr>
	<tr>
		<td align="right" class="td1">服务器语种：</td>
		<td class="td2"><?php echo getenv("HTTP_ACCEPT_LANGUAGE")?></td>
		<td align="right" class="td1">Sendmail：</td>
		<td class="td2"><?php if(get_magic_quotes_gpc("Internal Sendmail Support for Windows 4") == 1){ echo "<font color='green'><b>√</b></font>&nbsp;支持"; }else{ echo "<font color='red'><b>×</b></font>&nbsp;不支持"; }?></td>
	</tr>
	<tr>
		<td align="right" class="td1">站点wwwroot路径：</td>
		<td class="td2"><?php echo WWWROOT;?></td>
		<td align="right" class="td1">运行于安全模式：</td>
		<td class="td2"><?php if(get_cfg_var("safe_mode")){ echo "<font color='green'><b>√</b></font>&nbsp;是"; }else{ echo "<font color='red'><b>×</b></font>&nbsp;否"; }?></td>
	</tr>
	<tr>
		<td align="right" class="td1">网站系统路径：</td>
		<td class="td2"><?php echo MYQEEPATH;?></td>
		<td align="right" class="td1">拼写检查 ASpell Library：</td>
		<td class="td2"><?php if(function_exists("aspell_check_raw")){ echo "<font color='green'><b>√</b></font>&nbsp;支持"; }else{ echo "<font color='red'><b>×</b></font>&nbsp;不支持"; }?></td>
	</tr>
	<tr>
		<td align="right" class="td1">用户application路径：</td>
		<td class="td2"><?php echo MYAPPPATH;?></td>
		<td align="right" class="td1">高精度数学运算BCMath：</td>
		<td class="td2"><?php if(function_exists("bcadd")){ echo "<font color='green'><b>√</b></font>&nbsp;支持"; }else{ echo "<font color='red'><b>×</b></font>&nbsp;不支持"; }?></td>
	</tr>
	<tr>
		<td align="right" class="td1">模块modules路径：</td>
		<td class="td2"><?php echo MODULEPATH;?></td>
		<td align="right" class="td1">Socket支持：</td>
		<td class="td2"><?php if(function_exists("socket_accept")){ echo "<font color='green'><b>√</b></font>&nbsp;支持"; }else{ echo "<font color='red'><b>×</b></font>&nbsp;不支持"; }?></td>
	</tr>
</table>
<?php
endif;
View::factory('admin/footer') -> render(TRUE);
?>