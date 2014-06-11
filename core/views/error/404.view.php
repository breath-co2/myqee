<?php
    list($REQUEST_URI) = explode('?',$_SERVER['REQUEST_URI'],2);
    $REQUEST_URI = htmlspecialchars(rawurldecode($REQUEST_URI));
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Language" content="zh-cn" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo __('Page Not Found');?></title>
<style type="text/css">
.error-full-page-div {
font-size:9pt;
padding:10pt;
color:#111;
font-family:Arial,sans-serif,Helvetica,'宋体';
}
.error-full-page-div a,.error-full-page-div a:link,.error-full-page-div a:visited
{
color:#689e00;
text-decoration:underline;
}
.error-full-page-div a:hover,.error-full-page-div a:active
{
color:#a62e2e;
text-decoration:underline;
}
.error-full-page-div h1{
font-size:14pt;
font-weight:bold;
padding:0 0 10px 0;
line-height:1.2em;
margin:0;
color:#a62e2e;
_padding-left:0;
}
.error-full-page-div .error-message-box{
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
</head>
<body style="background:#f1f1f1;padding:0;margin:0;">
<div class="error-full-page-div">
<h1><?php echo $message;?></h1>
<div class="error-message-box">
<?php echo __('The requested URL :REQUEST_URI was not found on this server.', array(':REQUEST_URI'=>$REQUEST_URI) );?>

<br /><br />
<a href="javascript:history.go(-1);"><?php echo __('Back');?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="<?php echo Core::url('/');?>"><?php echo __('Goto Home Page');?></a>
</div>
</div>
</body>
</html>