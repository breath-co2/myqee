<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Language" content="zh-cn" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $config['title'];?></title>
<link rel="stylesheet" type="text/css" href="<?php echo Core::url('statics/css/global.css');?>" />
<link rel="stylesheet" type="text/css" href="<?php echo Core::url('statics/css/install.css');?>" />
</head>
<body>
<div id="headerDiv">
    <div class="mainWidth">
        <div id="logoDiv"><img src="<?php echo Core::url('statics/images/install_logo.png');?>" /></div>
        <div id="titleText"><?php echo $config['title'];?></div>
        <div id="topRightDiv"><?php echo $config['desc'];?></div>
    </div>
</div>

<div class="mainWidth" style="padding:20px 0;">
<?php
if ($step>0)
    View::factory('admin/install/step')->render();
?>