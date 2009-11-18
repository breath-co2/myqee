<html>
<head>
<meta http-equiv="Content-Language" content="zh-cn" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo Myqee::lang('admin/login.page_title');?></title>
<style type="text/css">
body {
	color: #FFFFFF;
	font-size: 12px;
	margin: 0px;
	background:#000177;
}

td {
	color: #FFFFFF;
	font-size: 12px;
}

a:link {
	color: #CFD1E8;
	text-decoration: underline;
}

a:hover {
	color: #FFCC00;
	text-decoration: none;
}

a:visited {
	color: #CFD1E8;
	text-decoration: underline;
}

form {
	margin: 0;
	padding: 0
}

.input {
	font-size: 12px;
	width: 120px;
	height: 16px;
	line-height:14px;
	color: #fff;
	border: #4047A4 1px solid;
	background-color: #000077;
}
<?php 
if($_GET['h']=='none'){
	$f_w=220;
	$f_h=120;
	$line_h=5;
	$bg_h = -100;
}else{
	$f_w=330;
	$f_h=190;
	$line_h=50;
	$bg_h=0;
}
?>
input.submit{background:url(<?php echo ADMIN_IMGPATH;?>/admin/bt_login.gif) no-repeat;border:none;width:70px;height:16px;padding:0 !important;padding:3px 0 0 0;font-size:12px;}

.tdbg {
	background: url(<?php echo ADMIN_IMGPATH;?>/admin/bg.jpg) repeat-x left <?php echo $bg_h;?>px;
}
</style>
<script type="text/javascript">
function checkForm()
{
	var form=document.forms["loginform"];
	if(form["username"].value==""){alert("<?php echo Myqee::lang('admin/login.alert.enterusername');?>",null,null,null,function(){form["username"].focus();});return false};
	if(form["password"].value==""){alert("<?php echo Myqee::lang('admin/login.alert.enterpassword');?>",null,null,null,function(){form["password"].focus();});return false};
}
try{
	if (self.name!='loginFrame' && self.location.href!=top.location.href){
		top.location.href=self.location.split('?')[0];
	}
}catch(e){}
</script>


</head>
<body scroll="no">
<form action="<?php echo Myqee::url('login/login').($_GET['h']=='none'?'?h=none':'');?>" name="form1" id="loginform" method="POST" onSubmit="return checkForm()" autocomplete="off">
<?php if (isset($_GET['forward']) && $_GET['forward']){
	echo '<input type="hidden" name="forward" value="'.str_replace('"','&quot;',$_GET['forward']).'" />';
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%">
<tr>
<td align="center" valign="middle" class="tdbg">
<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=5,0,0,0" width="<?php echo $f_w;?>" height="<?php echo $f_h;?>">
<param name="movie" value="<?php echo ADMIN_IMGPATH;?>/admin/admin_m.swf">
<param name="quality" value="High">
<param name="wmode" value="transparent">
<param name="menu" value="false">
<embed src="<?php echo ADMIN_IMGPATH;?>/admin/admin_m.swf" quality="High" pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash" type="application/x-shockwave-flash" width="<?php echo $f_w;?>" height="<?php echo $f_h;?>" menu="false" wmode="transparent">
</embed></object>
<div style="height:<?php echo $line_h;?>px;width:1px;"></div>
<table width="280" border="0" cellspacing="0" cellpadding="0" align="center" style="border:1px solid #4047A4">
<tr>
<td colspan="2" height="35"></td>
</tr>
<tr>
	<td width="100" height="30" align="right"><font color="#CFD1E8"><?php echo Myqee::lang('admin/login.username');?></font></td>
	<td><input type="text" id="username" name="username" value="" size="20" class="input" onfocus="this.style.backgroundColor='#000088'" onblur="this.style.backgroundColor=''" /></td>
</tr>
<tr>
	<td height="30" align="right"><font color="#CFD1E8"><?php echo Myqee::lang('admin/login.password');?></font></td>
	<td><input type="password" id="password" name="password" value="" size="20" class="input" onfocus="this.style.backgroundColor='#000088'" onblur="this.style.backgroundColor=''" style="font-family:Tahoma" /></td>
</tr>
<?php
if ($tooerror == true):
?>
<tr>
	<td height="30" align="right"><font color="#CFD1E8"><?php echo Myqee::lang('admin/login.captcha');?></font></td>
	<td><input type="text" id="captcha" name="captcha" value="" size="5" maxlength="4" class="input" onfocus="this.style.backgroundColor='#000088'" onblur="this.style.backgroundColor=''" style="width:60px;" /></td>
</tr>
<tr>
	<td></td>
	<td><img src="<?php echo ADMIN_URLPATH.'captcha/img.png';?>" /></td>
</tr>
<?php
endif;
?>
<tr>
	<td height="30"></td>
	<td><input type="submit" value="<?php echo Myqee::lang('admin/login.submit');?>" class="submit" border="0" /></td>
</tr>
<tr>
<td colspan="2" height="36" align="center"><?php echo $errinfo;?></td>
</tr>
</table>

</td>
</tr>
</table>

<script type="text/javascript">
	setTimeout("document.getElementById('username').focus();",10);
</script>
</body>
</html>