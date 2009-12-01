<?php
	$page_index or $page_index = Myqee::segment(1);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Language" content="zh-cn" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $page_title; ?><?php echo Myqee::lang('admin/head.site_title');?></title>
<link rel="stylesheet" type="text/css" href="<?php echo ADMIN_IMGPATH;?>/admin/style.css" />
<link rel="stylesheet" type="text/css" href="<?php echo ADMIN_IMGPATH;?>/admin/ymPrompt/skin/dmm-green/ymPrompt.css" />
<script type="text/javascript" src="<?php echo ADMIN_IMGPATH;?>/admin/ymPrompt/ymPrompt.js"></script>
<script type="text/javascript" src="<?php echo ADMIN_IMGPATH;?>/admin/main.js"></script>
<script type="text/javascript">
var page_index = '<?php echo $page_index;?>';
var page_urlpath = '<?php echo ADMIN_URLPATH;?>';
var _admin_inmpath = '<?php echo ADMIN_IMGPATH;?>';
</script>
</head>
<body>
<?php
$cacheid = $_SERVER['HTTP_HOST'].'admin.header_admin_'.$_SESSION['admin']['id'];
if (!$recache && $headerhtml = Cache::instance()->get($cacheid)):
	echo $headerhtml;
else:
	ob_start();
?>
<script type="text/javascript">
document.write('<div id="appendChildDiv"></div><div id="hiddenfRameDiv"><iframe src="<?php echo ADMIN_IMGPATH.'/admin/block.html'?>" width="1" height="1" name="hiddenFrame" id="hiddenFrame" style="display:none;"></iframe></div>');
</script>
<div id="mytopDiv">
<iframe style="position:absolute;top:0;left:0;filter:alpha(opacity=0);opacity:0;z-index:-1" frameborder="0" width="100%" height="68"></iframe>
<table cellspacing="0" cellpadding="0" width="100%" align="center" class="toptable" height="38">
<tr>
<td style="color:#FFF;line-height:1.6em;min-width:800px;">
<div style="position:absolute;z-index:0;width:365px;height:38px;background:url(<?php echo ADMIN_IMGPATH;?>/admin/headerimg.png) no-repeat;_background:none;_filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php echo ADMIN_IMGPATH;?>/admin/headerimg.png',sizingMethod='image');"></div>
<div style="text-align:right"><?php echo Myqee::lang('admin/head.welcomeadmin',$_SESSION['admin']['username']) .Myqee::lang('admin/head.adminloginnum',$_SESSION['admin']['countlogin']);?>
<a href="<?php echo Myqee::url('admin/edit/'.$_SESSION['admin']['id']) ?>" style="color:#ff0"><?php echo Myqee::lang('admin/head.changepassword');?></a>&nbsp;&nbsp;
<a href="<?php echo Myqee::url('login/logout') ?>" style="color:#ff0;"><?php echo Myqee::lang('admin/head.logout');?></a>&nbsp;&nbsp;
<br/><?php echo Myqee::lang('admin/head.nowsite');?><a href="<?php echo Myqee::url('site/index');?>" title="<?php echo Myqee::lang('admin/head.sitemanage');?>" style="color:#cf0"><?php echo $_SESSION['now_site']?$_SESSION['now_site_name']:'主站点';?></a>&nbsp;&nbsp;
<br/><img src="<?php echo ADMIN_IMGPATH;?>/admin/spacer.gif" style="height:0px;width:800px;" /></div>
</td>
</tr>
</table>
<div class="mainTable">
<ul class="ul menu">
<?php
$menuConfigData = Myqee::config('adminmenu');
if (!is_array($menuConfigData)){
	$menuConfigData = Myqee::config('adminmenu');
}
$submenu = '';
if (is_array($menuConfigData)){
	foreach ($menuConfigData as $key => $item){
		if (!$item['level'] || Passport::getisallow($item['level'])){
			if (!isset($item['is_use']) || $item['is_use']){
				echo '	<li id="menuLi_',$key,'"><a href="',Myqee::url($item['address']),'"',($item['target']?' target="'.$item['target'].'"':''),' id="menu_',$key,'" onfocus="this.blur()" onmouseover="showMenu(this.id,null,null,null,null,null,null,null,true);">',$item['name'],'</a> </li>';
			}else{
				continue;
			}
		}else{
			continue;
		}
		if(!is_array($menuConfigData[$key]['sub']))continue;
		$submenu .= '<div class="popupmenu_popup mainmenu" style="display:none;" id="menu_'.$key.'_menu"><ul class="ul">';
		foreach ($menuConfigData[$key]['sub'] as $subitem){
			if (!$subitem['level'] || Passport::getisallow($subitem['level'])){
				if (!isset($subitem['is_use']) || $subitem['is_use']){
					$submenu .= '<li><a href="'.Myqee::url($subitem['address']).'"'.($subitem['target']?' target="'.$subitem['target'].'"':'').'>'.$subitem['name'].'</a></li>';
				}
			}
		}
		$submenu .= '</ul></div>';
	}
}
?>
</ul>
</div>
<div class="clear"></div>
<div class="line1"></div>
<script type="text/javascript">
(function(){
	var obj=$('menuLi_'+page_index);
	if (obj){
		obj.className = 'menunow';
	}
})();
</script>

<?php 
echo $submenu; 
?>

</div>

<div id="mytopSpacerDiv"></div>
<div class="clear"></div>

<?php
$headerhtml = ob_get_clean();
echo $headerhtml;
Cache::instance()->set($cacheid,$headerhtml,NULL,1800);
endif;
unset($cacheid,$headerhtml);
?>