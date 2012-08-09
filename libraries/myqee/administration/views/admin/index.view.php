<?php
$member = Session::instance()->member();
?><h1 style="font-size:14pt;text-align:center;padding:15px 0 20px 0;margin:0;font-family:'楷体_GB2312','楷体';"><?php echo Core::config('admin/core.index_title');?></h1>
<table border="0" cellpadding="4" cellspacing="1" align="center" class="mainTable">
	<tr>
		<th colspan="2">基本信息</th>
	</tr>
	<tr>
		<td width="20%" align="right" class="td1">版本信息：</td>
		<td class="td2">
        Core版本：<b style="color:blue">V<?php echo Core::VERSION;?></b> &nbsp;&nbsp;
        Bootstrap版本：<b style="color:green">V<?php echo Bootstrap::VERSION;?></b> &nbsp;&nbsp;
		<!-- 最新版本：<span id="newvision_div" style="color:red">检测中...</span>-->
        </td>
	</tr>
	<?php if (Core::config('admin/core.show_support')) {?>
	<tr>
		<td align="right" class="td1">技术支持：</td>
		<td class="td2">
        迈启网 (<a href="http://www.myqee.com/" target="_blank">www.myqee.com</a>) &nbsp;&nbsp;
        博客 (<a href="http://www.myqee.com/blog/" target="_blank">www.myqee.com/blog</a>)
        </td>
	</tr>
	<tr>
		<td align="right" class="td1">开发作者：</td>
		<td class="td2">
        MyQEE PHP Framework Develop Team
        </td>
	</tr>
	<?php
	}
	?>
	<!--[if lt IE 8.9]>
	<tr>
		<td align="right" class="td1"> </td>
		<td class="td2" style="color:#999;">使用IE9或Firefox4或Chrome或Safari浏览器能得到最佳使用体验</td>
	</tr>
	<![endif]-->
</table>
<br/>
<?php
if ( $member->perm()->is_own('default.view_serverinfo') )
{
    $supGetCfgVar = function_exists('get_cfg_var');
    $supUname = function_exists('php_uname');
?>

<table border="0" cellpadding="4" cellspacing="1" align="center" class="mainTable">
	<tr>
		<th colspan="4">服务器信息</th>
	</tr>
	<tr>
		<td width="20%" align="right" class="td1">服务器操作系统：</td>
		<td width="40%" class="td2"><?php
		if ( $supUname )
		{
			$os = explode(" ", php_uname());
			echo $os[0] . '&nbsp;&nbsp;内核版本：'.(IS_WIN===true?$os[1]:$os[2]);
		}else{
			echo '未知，' . (IS_WIN?'window平台':'非window平台');
		}
		?>
        </td>
		<td width="20%" align="right" class="td1">mysql数据库：</td>
		<td width="20%" class="td2">
		<?php
		$supMySQL = function_exists('mysql_close');
		$supMySQLI = function_exists('mysqli_close');
		if( $supMySQL || $supMySQLI ){
			echo '<font color="#5fd300"><strong>√</strong></font>&nbsp;支持:';
			if ( $supMySQL ) {
				echo 'MySQL';
			}
			if ( $supMySQL && $supMySQLI ) {
				echo ',';
			}
			if ( $supMySQLI ) {
				echo 'MySQLI';
			}
		}else{
			echo '<font color="red"><strong>×</strong></font>&nbsp;不支持';
		}
		?>
        </td>
	</tr>
	<tr>
		<td width="20%" align="right" class="td1">服务器名称：</td>
		<td width="40%" class="td2"><?php
		if ( $supUname )
		{
			echo php_uname('n');
		}else{
			echo '未知';
		}
		?>
        </td>
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
		<td class="td2"><?php if(@get_magic_quotes_gpc("smtp")){ echo "<font color='green'><b>√</b></font>&nbsp;支持"; }else{ echo "<font color='red'><b>×</b></font>&nbsp;不支持"; }?></td>
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
		<td class="td2"><?php if ($supGetCfgVar){if(get_cfg_var("display_errors") == 1){ echo "<font color='green'><b>√</b></font>&nbsp;是"; }else{ echo "<font color='red'><b>×</b></font>&nbsp;否"; }}else{echo '未知';}?></td>
	</tr>
	<tr>
		<td align="right" class="td1">脚本超时时间：</td>
		<td class="td2"><?php echo $supGetCfgVar?(int)get_cfg_var("max_execution_time").' 秒':'未知';?></td>
		<td align="right" class="td1">允许打开远程文件：</td>
		<td class="td2"><?php if ($supGetCfgVar){if(@get_cfg_var("allow_url_fopen") == 1){ echo "<font color='green'><b>√</b></font>&nbsp;支持"; }else{ echo "<font color='red'><b>×</b></font>&nbsp;不支持"; }}else{echo '未知';}?></td>
	</tr>
	<tr>
		<td align="right" class="td1">脚本上传文件大小限制：</td>
		<td class="td2"><?php echo $supGetCfgVar?((get_cfg_var("upload_max_filesize")?get_cfg_var("upload_max_filesize"):"不允许上传附件")):'未知';?> <span class="helpicon" title="本项和下面的POST提交大小限制将决定了上传文件的大小！">&nbsp;</span></td>
		<td align="right" class="td1">压缩文件支持(Zlib)：</td>
		<td class="td2"><?php if(function_exists("gzclose")){ echo "<font color='green'><b>√</b></font>&nbsp;支持"; }else{ echo "<font color='red'><b>×</b></font>&nbsp;不支持"; }?></td>
	</tr>
	<tr>
		<td align="right" class="td1">POST提交内容限制：</td>
		<td class="td2"><?php echo $supGetCfgVar?get_cfg_var("post_max_size"):'未知';?></td>
		<td align="right" class="td1">ZEND支持：</td>
		<td class="td2"><?php echo (@get_cfg_var("zend_optimizer.optimization_level")||@get_cfg_var("zend_extension_manager.optimizer_ts")||@get_cfg_var("zend_extension_ts")) ?"<font color='green'><b>√</b></font>&nbsp;支持":'<font color="red"><b>×</b></font>&nbsp;不支持'?>		</td>
	</tr>
	<tr>
		<td align="right" class="td1">脚本运行时可占最大内存：</td>
		<td class="td2"><?php echo $supGetCfgVar?((get_cfg_var("memory_limit")?get_cfg_var("memory_limit"):"无")):'未知';?></td>
		<td align="right" class="td1">编码转换：</td>
		<td class="td2"><?php if(function_exists("mb_convert_encoding") == 1){ echo "<font color='green'><b>√</b></font>&nbsp;支持"; }else{ echo "<font color='red'><b>×</b></font>&nbsp;不支持"; }?></td>
	</tr>
	<tr>
		<td align="right" class="td1">服务器语种：</td>
		<td class="td2"><?php echo getenv("HTTP_ACCEPT_LANGUAGE")?></td>
		<td align="right" class="td1">Sendmail：</td>
		<td class="td2"><?php if(@get_magic_quotes_gpc("Internal Sendmail Support for Windows 4") == 1){ echo "<font color='green'><b>√</b></font>&nbsp;支持"; }else{ echo "<font color='red'><b>×</b></font>&nbsp;不支持"; }?></td>
	</tr>
	<tr>
		<td align="right" class="td1">程序路径：</td>
		<td class="td2"><?php echo DIR_SYSTEM;?></td>
		<td align="right" class="td1">运行于安全模式：</td>
		<td class="td2"><?php if($supGetCfgVar){if(get_cfg_var("safe_mode")){ echo "<font color='green'><b>√</b></font>&nbsp;是"; }else{ echo "<font color='red'><b>×</b></font>&nbsp;否"; }}else{echo '未知';}?></td>
	</tr>
	<tr>
		<td align="right" class="td1">网站类库路径：</td>
		<td class="td2"><?php echo DIR_LIBRARY;?></td>
		<td align="right" class="td1">拼写检查 ASpell Library：</td>
		<td class="td2"><?php if(function_exists("aspell_check_raw")){ echo "<font color='green'><b>√</b></font>&nbsp;支持"; }else{ echo "<font color='red'><b>×</b></font>&nbsp;不支持"; }?></td>
	</tr>
	<tr>
		<td align="right" class="td1">项目路径：</td>
		<td class="td2"><?php echo DIR_PROJECT;?></td>
		<td align="right" class="td1">高精度数学运算BCMath：</td>
		<td class="td2"><?php if(function_exists("bcadd")){ echo "<font color='green'><b>√</b></font>&nbsp;支持"; }else{ echo "<font color='red'><b>×</b></font>&nbsp;不支持"; }?></td>
	</tr>
	<tr>
		<td align="right" class="td1">WWW路径：</td>
		<td class="td2"><?php echo DIR_WWWROOT;?></td>
		<td align="right" class="td1">Socket支持：</td>
		<td class="td2"><?php if(function_exists("socket_accept")){ echo "<font color='green'><b>√</b></font>&nbsp;支持"; }else{ echo "<font color='red'><b>×</b></font>&nbsp;不支持"; }?></td>
	</tr>
</table>
<?php
}
?>