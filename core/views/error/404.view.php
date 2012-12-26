<?php
    list($REQUEST_URI) = explode('?',$_SERVER['REQUEST_URI'],2);
    $REQUEST_URI = htmlspecialchars(rawurldecode($REQUEST_URI));
?>
<!DOCTYPE html>
<html>
<head>
<title><?php echo __('Page Not Found');?></title>
<style type="text/css">
body{
background:#f1f1f1;
font-size:9pt;
padding:10pt;
margin:0;
color:#111;
font-family:Arial,sans-serif,Helvetica,"宋体";
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
</head>
<body>
<h1><?php echo $message;?></h1>
<div class="box">
    <?php echo __('The requested URL :REQUEST_URI was not found on this server.', array(':REQUEST_URI'=>$REQUEST_URI) );?>
</div>
</body>
</html>