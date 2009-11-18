<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Language" content="zh-cn" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>用户登陆</title>
<style type="text/css">
body {
	font-family: Helvetica,Verdana, sans-serif,'宋体';
	padding: 0px;
	font-size:12px;
	margin:0px auto;
	background:#ffffff;
	color:#333;
}

a:link,a:visited{
	color:#626262;
	text-decoration:none;
}
a:hover,a:active {
	color:#ed145b;
	text-decoration:underline;
}
form {
	margin:0px;
	padding:0px;
}
input, select, button, textarea {
	font-size:12px;
}

.input_1{border-top:1px solid #b4b4b4;border-left:1px solid #b4b4b4;border-right:1px solid #ebebeb;border-bottom:1px solid #ebebeb;background-color:#ffffcc;
height:1.2em;line-height:1.2em;font-size:14px;padding:2px;}

</style>

<script type="text/javascript">
function sendRequest() {
var objform=document.forms["myForms"];
if (objform["username"].value==""){alert("请输入用户名！");return false;}
if (objform["password"].value==""){alert("请输入密码！");return false;}
if (objform["imagecode"].value==""){alert("请输入验证码！");return false;}

var websearch=document.location.search;
if (websearch!="")
{
	if (websearch.substr(0,9)=="?forward=")
	{
		websearch=websearch.substr(9);
		websearch=websearch.replace(/&/g,'%26');
		objform.action="{site_url}login/login.html?forward="+websearch;
	}
}
objform.submit();
return false;
}


window._alert = window.alert;

window.alert= function(msg,w,h,title,handler) {
	_alert(msg);
	if (handler){
		try{handler()}catch(e){}
	}
}
window.error = window.alert;
window.succeed = window.alert;

</script>
</head>
<body>
<div style="padding:10px 20px;">
<form method="post" action="{site_url}login/login.html" name="myForms" id="myForms" target="hiddenFrame" onsubmit="return sendRequest();">

<div style="border-bottom:1px dashed #53a0d7;margin:0 0 15px 0;padding:4px"><div style="border-left:4px solid #ff6d00;padding:4px 0 2px 6px;font-size:14px;"><b>用户登录</b></div></div>
<table border="0" height="130">
	<tr>
		<td align="right" width="70">用户名：</td>
		<td><input type="text" class="input_1" style="width:140px;" name="username" tabindex="1" id="username" /> <input type="checkbox" name="isuid" value="1" />ID?</td>
	</tr>
	<tr>
		<td align="right">密　码：</td>
		<td><input type="password" class="input_1" style="width:140px;" tabindex="2" name="password" id="password" /> &nbsp;<a href="http://www.bobou.com/forget_password.html" target="_blank">忘记密码？</a></td>
	</tr>
	<tr>
		<td align="right">验证码：</td>
		<td><input type="text" class="input_1" style="width:77px;" name="imagecode" autocomplete="off" tabindex="3" id="imagecode" maxlength="4" style="width:40px;" /> <img src="{site_url}captcha/small/image.png" align="absmiddle" id="authCodeImage" /> &nbsp;<a href="#" onclick="document.getElementById('authCodeImage').src='{site_url}captcha/small/image.png?'+new Date();return false">换一张</a></td>
	</tr>
	<tr>
		<td align="right">&nbsp;</td>
		<td><input type="checkbox" name="autologin" value="1" checked="checked"/>下次自动登陆</td>
	</tr>
	<tr height="40">
		<td></td>
		<td><input type="image" src="{site_url}images/default/btn_login.gif" tabindex="4" />&nbsp;&nbsp;<input type="image" src="{site_url}images/default/btn_register.gif" onclick="parent.location.href='{site_url}register.html';return false;" /></td>
	</tr>
</table>
<script type="text/javascript">
document.getElementById('username').focus();
</script>
</form>
</div>
</body>
</html>