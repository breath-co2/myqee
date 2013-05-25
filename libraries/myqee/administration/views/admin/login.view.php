<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo __('Administrator login')?></title>
<style type="text/css">
html{
}
body{
color:#fff;
font-size:14px;
padding:0;
overflow:hidden;
margin:0;
width:100%;
height:100%;
min-height:400px;
background:#2784bd url(<?php echo Core::url('statics/images/bg_line.gif');?>) repeat-x left top;
}
#topbg{
    z-index:-1;
    position:absolute;
    left:0;
    width:100%;
    height:360px;
    background:url(<?php echo Core::url('statics/images/bg.jpg');?>) no-repeat center top;
}

h1{
font-wieght:bold;font-size:20px;
padding:0 28px;
margin:0;
text-shadow:0 1px 2px rgba(0, 0, 0, 0.4);
}

#login_form {
position:absolute;
width:500px;
}
.input{
font-size: 22px;
padding-left: 12px;
padding-right: 0px;
width: 234px;
height:30px;
background:url(<?php echo Core::url('statics/images/spacer.gif');?>);
color:#383838;
outline:medium none;
border:none;
}
.input_div{
position:absolute;
margin-top:14px;
width:295px;
height:80px;
background:url(<?php echo Core::url('statics/images/login_input_bg.png');?>) 0 10px;
}
.input_div input{
position:absolute;
top:26px;
}
.heightlight{
background-position:0 -71px;
}

.input_bg_text{
position:absolute;
line-height:28px;
top:26px;
color:#A2AFC2;
padding-left:13px;
font-size: 22px;
white-space:nowrap;
}

.submit_bg{
background:url(<?php echo Core::url('statics/images/login_submit.png');?>);
width:80px;
height:42px;
position:absolute;
overflow:hidden;
margin-top:34px;
}
.submit_text{
position:absolute;
text-shadow:0 2px 1px rgba(255, 255, 255, 0.4);
color:#4F3400;
font-weight:bold;
width:80px;
height:42px;
text-align:center;
line-height:40px;
cursor:pointer;
-moz-user-select:none;
user-select:none;
selcte:none;
font-size:20px;
}
.submit_bg_hover{
background-position:0 -80px;
}
#fog{z-index:-1;overflow-x:hidden;position:absolute;left:0;top:0;width:100%;height:100%;}
</style>
<!--[if lt IE 6.9]>
<style type="text/css">
.input_div{
background:none;
}
.submit_bg{background:none;}

.input_bg_forie6{
position:absolute;
width:290px;
height:80px;
overflow:hidden;
}

.input_bg_forie6 .inputbg{
position:absolute;
top:10px;
width:286px;
height:80px;
filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php echo Core::url('statics/images/login_input_bg.png');?>');
}

.submit_bg_forie6{
position:absolute;
width:80px;
height:42px;
overflow:hidden;
}
.submit_bg_forie6 .inputbg{
position:absolute;
width:80px;
height:42px;
filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php echo Core::url('statics/images/login_submit.png');?>');
}
</style>
<![endif]-->

<script type="text/javascript">
var userAgent = navigator.userAgent.toLowerCase();
var is_opera = navigator.appName.indexOf('Opera')>=0?true:false;
var is_ie =navigator.appName=="Microsoft Internet Explorer"?true:false;
var ie = (userAgent.indexOf('msie') != -1 && !is_opera) && userAgent.substr(userAgent.indexOf('msie') + 5, 3);

function heightlight(obj)
{
	obj.className = 'input_div heightlight';
    if (is_ie && ie<7)
    {
        var ie6bg = obj.getElementsByTagName('*')[0].getElementsByTagName('*')[0];
        ie6bg.style.top = '-71px';
    }
}
function offlight(obj)
{
	obj.className = 'input_div';
    if (is_ie && ie<7)
    {
        var ie6bg = obj.getElementsByTagName('*')[0].getElementsByTagName('*')[0];
        ie6bg.style.top = '10px';
    }
}
function show_hover()
{
	var obj=document.getElementById('submit_bg');
    obj.className = 'submit_bg submit_bg_hover';
	if (is_ie && ie<7)
    {
        var ie6bg = obj.getElementsByTagName('*')[0].getElementsByTagName('*')[0];
        ie6bg.style.marginTop = '-80px';
    }
}
function show_up()
{
    var obj=document.getElementById('submit_bg');
    obj.className = 'submit_bg';
    if (is_ie && ie<7)
    {
        var ie6bg = obj.getElementsByTagName('*')[0].getElementsByTagName('*')[0];
        ie6bg.style.marginTop = '';
    }
}
function input_key_down(e)
{
	e = e||window.event;
	if (e.keyCode==13)
	{
		show_hover();
	}
}
function input_key_up(e,obj,type)
{
	var tobj = document.getElementById('input_default_text_'+type);
	if (obj.value=='')
	{
		var v = {'username':'<?php echo __('Username')?>','password':'<?php echo __('Password')?>','captcha':'<?php echo __('Verification code')?>'};
		tobj.innerHTML=v[type];
	}
	else
	{
		tobj.innerHTML='';
	}
	show_up();
}
function check_submit(form)
{
	if (form.username.value=='')
	{
		show_up();
		form.username.select();
		return false;
	}
	if (form.password.value=='')
	{
		form.password.select();
		show_up();
		return false;
	}
    if (form.captcha)
    {
        if (form.captcha.value=='')
        {
            form.captcha.select();
            show_up();
            return false;
        }
    }

	return true;
}
</script>
</head>
<body scroll="no">
<div id="body">
<div id="topbg"></div>
<table border="0" align="center" height="100%" width="500">
<tr><td></td></tr>
<tr>
<td height="200" align="left" valign="top">
<?php
$f_w = 400;
$f_h = 190;
$img1 = Core::url('statics/images/admin.png');
$img2 = Core::url('statics/images/admin_p1.png');
$img3 = Core::url('statics/images/admin_p2.png');
$space_gif = Core::url('statics/images/spacer.gif');
?>
<div style="position:absolute;text-align:left;width:400px;height:190px;margin-left:90px;background:url(<?php echo $img1;?>) no-repeat left top;_background:none;_filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php echo $img1;?>');">
	<div style="position:absolute;margin:31px 0 0 231px;">
	<table border="0" cellpadding="0" cellspacing="0" width="30" height="45"><tr><td align="left" valign="bottom"><img id="image1" style="width:38px;height:44px;" src="<?php echo $img3;?>" /></td></tr></table>
	</div>
	<div style="position:absolute;margin:28px 0 0 232px;">
		<table style="width:60px;height:50px;" border="0" cellpadding="0" cellspacing="0"><tr><td align="left" valign="bottom"><img id="image2" style="width:60px;height:48px;" src="<?php echo $img2;?>" /></td></tr></table>
	</div>
    <div style="position:absolute;width:400px;height:190px;background:url(<?php echo $space_gif;?>);"></div>
</div>
<script type="text/javascript">
(function(){
	var obj1 = document.getElementById('image1');
	var obj2 = document.getElementById('image2');
	if (is_ie && ie<7)
	{
		// for ie6
		var src1 = obj1.src;
		var src2 = obj2.src;
		obj1.src = obj2.src = '<?php echo $space_gif;?>';
		obj1.style.filter="progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod=scale,src='"+src1+"')";
		obj2.style.filter="progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod=scale,src='"+src2+"')";
	}
	var n = 0;
	var t = 1;

	var run = function()
	{
		obj1.style.width  = ( obj1.width  + (t*2) ) + 'px';
		obj2.style.width  = ( obj2.width  - (t*2) ) + 'px';
		obj2.style.height = ( obj2.height - t     ) + 'px';
		n += t;
		if ( n==6 || n==0 )
		{
			t = -t;
		}
	}
	setInterval(run,80);
})();
</script>
</td>
</tr>
<tr>
<td height="200" valign="top">
<div id="login_form">
<form method="post" id="myform" autocomplete="off" onsubmit="return check_submit(this);">
    <input type="hidden" name="forward" value="<?php echo HttpIO::GET('forward',HttpIO::PARAM_TYPE_URL);?>" />
	<div style="position:absolute;margin-left:-60px;">
    	<h1><?php echo __('Administrator login')?></h1>
    </div>
    <div class="input_div" style="margin-left:-60px;">
        <!--[if lt IE 6.9]><div class="input_bg_forie6"><div class="inputbg"></div></div><![endif]-->
    	<div style="padding:28px;">
        	<div class="input_bg_text" id="input_default_text_username"><?php echo __('Username')?></div>
        	<input tabindex="1" type="text" size="30" name="username" id="username" value="<?php echo $username;?>" class="input" onkeydown="input_key_down(event);" onkeyup="input_key_up(event,this,'username');" onfocus="heightlight(this.parentNode.parentNode)" onblur="offlight(this.parentNode.parentNode)" />
    	</div>
    </div>
    <div class="input_div" style="margin-left:193px;">
        <div class="input_bg_forie6"><div class="inputbg"></div></div>
    	<div style="padding:28px;">
        	<div class="input_bg_text" id="input_default_text_password"><?php echo __('password')?></div>
            <input tabindex="2" type="password" style="font-family:Tahoma,Simsun,Helvetica,sans-serif;" name="password" size="30" id="password" class="input" onkeydown="input_key_down(event);" onkeyup="input_key_up(event,this,'password');" onfocus="heightlight(this.parentNode.parentNode)" onblur="offlight(this.parentNode.parentNode)" />
	    </div>
    </div>
    <?php
    if ($show_captcha)
    {
        $msg_top = 55;
    ?>
    <div class="input_div" style="margin-left:-60px;margin-top:68px;">
        <div class="input_bg_forie6"><div class="inputbg"></div></div>
    	<div style="padding:28px;">
        	<div class="input_bg_text" id="input_default_text_captcha"><?php echo __('Verification code')?></div>
            <input tabindex="2" type="text" maxlength="4" name="captcha" size="30" id="captcha" class="input" onkeydown="input_key_down(event);" onkeyup="input_key_up(event,this,'captcha');" onfocus="heightlight(this.parentNode.parentNode)" onblur="offlight(this.parentNode.parentNode)" />
            <div style="position:absolute;margin-left:254px;margin-top:-7px;border:1px solid #333;border-radius:6px;-moz-border-radius:6px;-khtml-border-radius:6px;-webkit-border-radius:6px;"><img src="<?php echo Core::url('captcha/120x38.png?timeline='.microtime(1));?>" style="cursor:pointer;" title="点击更换验证码" onclick="this.src=this.src.split('?')[0]+'?timeline='+new Date().getTime();" /></div>
	    </div>
    </div>
    <?php
    }
    else
    {
        $msg_top = 0;
    }
    ?>
    <div class="submit_bg" id="submit_bg" style="margin-left:475px;">
        <!--[if lt IE 6.9]><div class="submit_bg_forie6"><div class="inputbg"></div></div><![endif]-->
        <div style="widht:1px;height:1px;overflow:hidden;"><input tabindex="3" onfocus="show_hover()" onblur="show_up();" type="submit" value="_" /></div>
        <div class="submit_text" onmousedown="show_hover()" onmouseup="show_up()" onclick="var obj=document.getElementById('myform');if (check_submit(obj))obj.submit();"><?php echo __('Login')?></div>
    </div>
    <div style="clear:both;height:90px;"></div>
    <div style="position:absolute;margin-left:-30px;margin-top:<?php echo $msg_top?>px;font-size:13px;color:#ff0;"><?php echo $message;?></div>
</form>
</div>
</td>
</tr>
<tr><td></td></tr>
</table>
</div>
<script type="text/javascript">
window.onload = function(){
    input_key_up({'keyCode':13},document.getElementById('username'),'username');
    input_key_up({'keyCode':13},document.getElementById('password'),'password');
    if (document.getElementById('captcha'))
    {
        input_key_up({'keyCode':13},document.getElementById('captcha'),'captcha');
    }
	document.getElementById('username').focus();
    <?php
    if ($error_input)
    {
    ?>
    var obj = document.getElementById('myform')['<?php echo $error_input;?>'];
    if (obj)
    {
    	obj.select();
    }
    <?php
    }
    ?>
};
</script>
</body>
</html>