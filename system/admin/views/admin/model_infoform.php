<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Language" content="zh-cn" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="<?php echo ADMIN_IMGPATH;?>/admin/style.css" />
<script type="text/javascript" src="<?php echo ADMIN_IMGPATH;?>/admin/main.js"></script>
<title></title>
<style type="text/css">
body{background:#fff;min-width:600px;}
.tableborder{width:100%;}
</style>
</head>
<body>
<script type="text/javascript" src="<?php echo ADMIN_IMGPATH;?>/admin/calendar.js"></script>

<?php echo $user_editinfo_formhtml;?>

<script type="text/javascript">
myqee();
function changeHeight(){
	window.parent.$("mainFrame").style.height=document.body.scrollHeight+"px";
}
if (self.location.href != parent.location.href){
	changeHeight();
	window.onload = changeHeight;
}
</script>
</body>
</html>