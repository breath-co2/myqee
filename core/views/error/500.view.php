<?php
    list($REQUEST_URI) = explode('?',$_SERVER['REQUEST_URI'],2);
    $REQUEST_URI = htmlspecialchars(rawurldecode($REQUEST_URI));
?>
<!DOCTYPE html>
<html>
<head>
<title><?php echo __('Internal Server Error');?></title>
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
pre{
margin:5px 2px 0 2px;
padding:6px 8px;
border:1px dotted #ff9797;
background:#ffffe3;
}
</style>
</head>
<body>
<h1><?php echo __('Internal Server Error');?></h1>
<div class="box">
    <?php echo __('The requested URL :REQUEST_URI was error on this server.', array(':REQUEST_URI'=>$REQUEST_URI) );?>

    <br />
    <br />
    <b><?php echo __('Error Message:');?></b>
    <pre><?php echo $error;?></pre>
    <?php
    if ($error_saved)
    {
    ?>
    <div style="padding:18px 0 0 0;">
        <b><?php echo __('Error NO:');?></b><input onclick="this.select();" style="border:none;width:180px;background:transparent;" readonly="readonly" value="<?php echo $error_no;?>" />
        <div style="color:#bbb;"><?php echo __('The error has been logged and can use this number to report the error message to the system administrator.');?></div>
        <br />
        <a href="javascript:history.go(-1);"><?php echo __('Back');?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="<?php echo Core::url('/');?>"><?php echo __('Goto Home Page');?></a>
    </div>
    <?php
    }
    ?>
</div>
</body>
</html>