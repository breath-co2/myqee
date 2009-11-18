<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Language" content="zh-cn" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script type="text/javascript">
if (self.location.href == parent.location.href){
	if (document.location.search.length>1){
		if (document.location.search.indexOf('fullpage=yes')==-1){
			document.location.href = document.location.href+'&fullpage=yes';
		}
	}else{
		document.location.href = document.location.href+'?fullpage=yes';
	}
}else{
	window.onunload = function (){var obj=parent.document.getElementById('infoeditControl_Div');if(obj){obj.parentNode.removeChild(obj);}}
}
function changeHeight(noscroll){
	if (self.location.href != parent.location.href){
		var body = document.body;
		if(!body)return false;
		parent.$("mainFrame").style.height=(body.scrollHeight||400)+"px";
		var infoForWidth = parent.$("infoForWidth");
		if(document.documentElement.scrollWidth && infoForWidth){
			infoForWidth.style.width=document.documentElement.scrollWidth+"px";
		}
		if (!noscroll)parent.scrollTo(0,0);
		parent.set_control_fixed();
	}else{
		set_control_fixed();
	}
}
if (window.name=='mainFrame'){
	window.onload = function (){changeHeight();};
	window.onresize = function (){changeHeight(true);};
}
</script>
<link rel="stylesheet" type="text/css" href="<?php echo ADMIN_IMGPATH;?>/admin/style.css" />
<script type="text/javascript" src="<?php echo ADMIN_IMGPATH;?>/admin/main.js"></script>
<script type="text/javascript">
var page_index = '<?php echo $page_index;?>';
var page_urlpath = '<?php echo ADMIN_URLPATH;?>';
var _admin_inmpath = '<?php echo ADMIN_IMGPATH;?>';
</script>
<style type="text/css">
body{min-width:600px;}
html{overflow:hidden;}
.tableborder{width:100%;}
.loaction{width:100%;padding:0 0 6px 0;margin:0;}
.mainTable{width:100%;}
</style>
</head>
<body>
<div id="appendChildDiv"></div>
<iframe src="javascript:void(0)" width="1" height="1" name="hiddenFrame" id="hiddenFrame" src="about:blank" style="display:none;"></iframe>