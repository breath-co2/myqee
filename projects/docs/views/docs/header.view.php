<?php
$site_view = Core::config('core.site_views');
if ( $site_view && $site_view['header'] )
{
    if ($site_view['by_project'])Core::set_project($site_view['by_project']);
    View::factory($site_view['header'])->render(true);
    if ($site_view['by_project'])Core::reset_project();
}
else
{
    $header_height = 80;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title><?php echo isset($title)?$title:'用户手册'?></title>
<style type="text/css">
body { overflow-y:scroll;color:#333;margin: 0 auto;padding:0; font-size:12px; font-family: Arial, sans-serif;}

.mainwidth{
    width:<?php echo $site_view['main_width']?$site_view['main_width']:960;?>px;
    margin:0 auto;
}
</style>
<script src="<?php echo Core::$base_url;?>/~<?php echo Core::$project;?>~js/jquery.min.js" type="text/javascript"></script>
</head>
<body>
<div style="background: #34393f;margin-bottom:10px;">
<div id="topbar" class="mainwidth">
    <ul>
        <li><a href="/">网站首页</a></li>
        <li><a href="<?php echo Core::url('/');?>">手册首页</a></li>
        <li><a href="<?php echo Core::url('/api/');?>">API Explorer</a></li>
    </ul>
</div>
</div>
<?php
}
?>
<style type="text/css">
a:link,a:visited{
	color:#333;
	text-decoration:underline;
}
a:hover,a:active{
	color:#911;
	text-decoration:underline;
}
.mainwidth{
    margin:0 auto;
}
#topbar{
    padding:8px 0;
    height:16px;
}
#topbar ul{
	list-style:none;
    padding:0;
    margin:0;
}
#topbar li{
	float:left;
    padding-right:10px;
}
#topbar a,#topbar a:link,#topbar a:visited{
	color:#ccc;
	text-decoration:none;
}
#topbar a:hover,#topbar a:active{
	color:#fff;
	text-decoration:underline;
}
.main_left{
	float:left;
	width:180px;
    display:inline;
    overflow:auto;
    margin-left:4px;
}
.main_center{
	float:left;
	width:740px;
    margin-left:10px;
	display:inline;
    overflow:auto;
}
.main_right{
    display:none;
	float:right;
	width:200px;
    display:inline;
    overflow:auto;
}
.main_left ul{
    list-style:none;
    padding:0;
    margin:0;
}
.main_left ul li{
    padding:2px 0 2px 18px;
    background:url(<?php echo Core::$base_url;?>/~<?php echo Core::$project;?>~img/gif_link.gif) no-repeat 5px 6px;
}
.main_left ul li.active{
    background-color:#c1c1c1;
}
.box_1{
	border:1px solid #ccc;
	-moz-border-radius: 4px;
	-khtml-border-radius: 4px;
	-webkit-border-radius: 4px;
	border-radius: 4px;
	padding:6px;
	font-size:14px;
	line-height:1.6em;
    background:#fff;
}

.box_1 p{
	line-height:1.6em;
	font-size:14px;
	font-weight:normal;
	margin:0;
	padding:3px 10px 5px 10px;
	text-align:justify;
	text-justify:inter-ideograph;
    white-space:pre-wrap;
}

.box_2{
	border:1px solid #ccc;
	background:#f5f5f5;
	-moz-border-radius: 4px;
	-khtml-border-radius: 4px;
	-webkit-border-radius: 4px;
	border-radius: 4px;
	padding:6px;
}

h1{
	font-size:16px;
	margin:0;
	padding:5px;
	display:block;
	font-weight:bold;
	background:#34393F;
	color:#eee;
}
h1 a:link,h1 a:visited{
	color:#eee;
	text-decoration:none;
}
h1 a:hover,h1 a:active{
	color:#fff;
	text-decoration:underline;
}

h2{
	font-size:16px;
	margin:4px 0;
	padding:2px 8px;
	background:#f6f6f6;
	border-bottom:1px dotted #ccc;
}

h3{
	font-size:14px;
	margin:4px 0;
	padding:2px 8px;
}
pre{
    padding:5px;
    margin:0 10px 5px 10px;
    font-size:9pt;
    line-height:1.4em;
    background:#f8ffe1;
    color:#12190a;
    border:1px dotted #ccc;
}
em{color:#c11;}
code{
	font-family:"Consolas","Bitstream Vera Sans Mono","Courier New",Courier,monospace;
}

.box_2 h1{
	font-size:13px;
}

.box_2 h2{
	background:#e5e5e5;
}

.box_2 ul{
	line-height:1.5em;
	font-size:12px;
}

.p{
	padding:10px;
	line-height:1.6em;
}

.spacer{
	clear:both;
	height:10px;
	overflow:hidden;
}

</style>
