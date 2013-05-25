<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="zh-CN" lang="zh-CN">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="noindex,nofollow" />
<style type="text/css">
body{
background:#f1f1f1;
font-size:9pt;
padding:10pt;
margin:0;
color:#111;
font-family:Arial,sans-serif,Helvetica,"宋体";
}
a,a:link,a:visited
{
    color:#689e00;
    text-decoration:underline;
}
a:hover,a:active
{
    color:#a62e2e;
    text-decoration:underline;
}
h1{
font-size:14pt;
font-weight:bold;
padding:0 0 10px 0;
line-height:1.2em;
margin:0;
color:#a62e2e;
_padding-left:0px;
}
.box{
border:1px solid #ccc;
padding:12px 10px;
background:#fff;
line-height:1.4em;
-moz-border-radius:8px;
-webkit-border-radius:8px;
border-radius:8px;
-moz-box-shadow:2px 2px 5px #e1e1e1;
-webkit-box-shadow:2px 2px 5px #e1e1e1;
box-shadow:2px 2px 5px #e1e1e1;
}
</style>
<title><?php echo $open?'开启调试':'关闭调试';?></title>
</head>
<body>
<h1>在线调试工具</h1>
<div class="box">
<?php
if ($open)
{
?>
<form method="post" action="<?php echo $url;?>" name="form1" id="form1">
<input type="hidden" name="action" value="login" /><?php echo $str;?>
认证：用户名:<input type="input" name="debug_user" /> 密码:<input type="password" name="debug_hash" />
<input type="submit" name="submit" value="开启调试" />
</form>
</div>
<script type="text/javascript">
document.forms['form1']['debug_user'].focus();
</script>
<?php
}
else
{
?>
<form method="post" action="<?php echo $url;?>" name="form1">
已开启<b style="color:green"><?php echo (IS_DEBUG&1)?'本地':'在线';?></b>调试模式
<input type="submit" name="submit" value="关闭调试"<?php if (IS_DEBUG&1)echo ' disabled="disabeld" title="本地调试模式必需修改php.ini中相关配置才可以关闭"';?> />
<?php echo $str;?>
</form>
<script type="text/javascript">
document.forms['form1']['submit'].focus();
</script>
<?php
}
?>
</body>
</html>