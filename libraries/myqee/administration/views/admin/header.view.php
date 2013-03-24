<!DOCTYPE>
<html>
<head>
<meta http-equiv="Content-Language" content="zh-cn" />
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo Core::$charset;?>" />
<title><?php echo $page_title;?></title>
<script type="text/javascript" src="<?php echo url_assets('jquery/jquery'.($jquery_version?'-'.$jquery_version:'').'.min.js');?>"></script>
<script type="text/javascript">
var url_assets = <?php var_export(URL_ASSETS);?>;
</script>
<?php
if ($header_html)echo $header_html;
?>
</head>
<body>