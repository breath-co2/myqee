<?php
$page_index = 'plugins';
$page_title = '修改插件配置';
if ($_GET['fullpage']=='yes')$showheader='fullpage';
if ($showheader == 'fullpage'):
	View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<script type="text/javascript">
function changeHeight(){set_control_fixed();return false;}
</script>
<?php
else:
	$showheader = '';
	View::factory('admin/header_frame') -> render(TRUE);
?>
<script type="text/javascript">
function set_tag(i){
	document.location.hash='#tag'+i;
	changeHeight();
}
</script>
<?php
endif;
?>
<style type="text/css">
ul.tag2{display:block;}
ul.tag2 li{float:left;white-space: nowrap;display:inline-block;line-height:1.8em;margin:0 6px;height:1.8em;overflow:hidden;cursor:pointer;text-decoration:underline;}
ul.tag2 li.now{font-weight:bold;text-decoration:none;cursor:default;}
</style>
<script type="text/javascript" src="<?php echo ADMIN_IMGPATH;?>/admin/calendar.js"></script>
<script type="text/javascript" src="<?php echo ADMIN_IMGPATH;?>/admin/info_edit.js"></script>
<script type="text/javascript">
function gohistory(go){
	try {
		if( typeof(parent.goHistory)=='function'){
			parent.goHistory(go);
			return;
		}
	}catch(e){goback(go);}
}
</script>
<div class="loaction" id="loaction_div">
您的位置：<a href="<?php echo Myqee::url('index');?>" target="_top">管理首页</a> -&gt; 
<a href="<?php echo Myqee::url('plugins/index'),($showheader=='fullpage'?'?fullpage=yes':'');?>">插件管理</a> 
<?php
echo ' -&gt; '.$page_title;
?>
</div>
<script type="text/javascript">
if (parent.location.href!=self.location.href && window.name=='mainFrame'){
	$('loaction_div').style.display='none';
	parent.$('loaction_div').innerHTML = $('loaction_div').innerHTML;
}
</script>

<div style="clear:both"></div>
<?php
$forward or $forward=Myqee::url('info/main'.($showheader?'?fullpage=yes':''));
?>
<form name="myeditform" action ="<?php echo Myqee::url('plugins/config_save/'.$plugins);?>" method="post" target="hiddenFrame">

<?php echo $user_editinfo_formhtml;?>

<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-top:none;">
<tr>
	<td class="td1" width="120" align="right">带(<font color="red">*</font>)必填</td><td class="td1">
	<input type="submit" value="保存信息" class="bbtn" id="info_submit_btton" /> 
	<input type="button" value="返回" class="btns" onclick="goUrl('<?php echo Myqee::url('plugins/main');?>');" id="info_goback_btton" />
	</td>
</tr>
</table>
</form>

<script>
////从弹出框中选择录入值，用户字段管理扩展表的时候
function showSelectValueFrame (page,dbname,fieldname) {
	ajax.requestFile = ("<?php echo Myqee::url('info/getExtendFieldValue/{{page}}/{{dbname}}/{{fieldname}}');?>").replace(/\{\{dbname\}\}/,dbname).replace(/\{\{page\}\}/,page).replace(/\{\{fieldname\}\}/,fieldname);
	ajax.method = 'GET';
	ajax.onCompletion = function (){
		window.win(this.response,560,300,'');
	}
	ajax.onError = function (){
		alert('请求失败，请稍后再试！');
	}
	ajax.runAJAX();
}
function setExtandValue (fieldname,writeValue,showValue,isappend) {
	if (isappend == 0) {
		$('__myqee_input_info['+fieldname+']').value=showValue;
		$('_myqee_input_info['+fieldname+']').value=writeValue;
	} else {
		$('_myqee_input_info['+fieldname+']').value += '|' + writeValue;
		$('__myqee_input_info['+fieldname+']').value += '|' + showValue;
		$('_myqee_input_info['+fieldname+']').value = $('_myqee_input_info['+fieldname+']').value.trim('|');
	}
	closewin();
}
</script>
<?php
if ($showheader == 'fullpage'):
View::factory('admin/footer') -> render(TRUE);
else:
?>
<script type="text/javascript">
myqee();
changeHeight();
</script>

<script type="text/javascript" src="<?php echo ADMIN_IMGPATH;?>/admin/tooltip.js"></script>
</body>
</html>

<?php
endif;
?>
