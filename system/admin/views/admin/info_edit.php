<?php
$page_index = 'info';
if ($viewtype){
	$page_title = '查看信息';
}else{
	if ($myinfo[$sys_field['id']]>0){
		$page_title = '修改信息';
	}else{
		$page_title = '添加信息';
	}
}
if ($_GET['fullpage']=='yes')$showheader='fullpage';
if ($showheader == 'fullpage'){
	View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
	echo '<script type="text/javascript">function changeHeight(){set_control_fixed();return false;}</script>';
}else{
	$showheader = '';
	View::factory('admin/header_frame') -> render(TRUE);
}
?>
<style type="text/css">
ul.tag2{display:block;}
ul.tag2 li{float:left;white-space: nowrap;display:inline-block;line-height:1.8em;margin:0 6px;height:1.8em;overflow:hidden;cursor:pointer;text-decoration:underline;}
ul.tag2 li.now{font-weight:bold;text-decoration:none;cursor:default;}
</style>
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
<div class="loaction">
<div style="float: right"><input type="button" onclick="gohistory();return false;" class="btn" style="margin:0" value="返回上一页" /></div>
<div style="float:left;">
您的位置：<a href="<?php echo Myqee::url('index');?>" target="_top">管理首页</a> -&gt; 
<a href="<?php echo Myqee::url('info/main'),($showheader=='fullpage'?'?fullpage=yes':'');?>">内容管理</a> 
<?php
if ($classid>0){
	foreach ((array)$location as $item){
		echo ' -&gt; <a href="'.Myqee::url('info/myclass/1/'.$item['classid'].'/'.$showheader).'">'.$item['classname'].'</a>';
	}
}else{
	echo '-&gt; <a href="'.Myqee::url('info/main/1/'.$dbname).($showheader?'?fullpage=yes':'').'">数据表:'.$dbname.'</a>';
}
echo ' -&gt; '.$page_title;
?>
</div>
</div>

<div style="clear:both"></div>
<?php
$forward or $forward=Myqee::url('info/main'.($showheader?'?fullpage=yes':''));
if (!$viewtype){
?>
<form name="myeditform" action ="<?php echo Myqee::url('info/save/'.$showheader);?>" method="post" target="hiddenFrame">
<?php

echo form::hidden('sys[id]',$myinfo[$sys_field['id']]) , 
form::hidden('sys[classid]',$classid) ,
form::hidden('sys[time]',$time) ,
form::hidden('sys[savekey]',$savekey) ,
form::hidden('sys[mydbname]',$dbname) ,
form::input('sys[forward]',$forward,'style="display:none" id="sys[forward]"');

if (isset($acqu_dataid) && $acqu_dataid>0){
	echo form::hidden('sys[acquid]',$acqu_dataid);
}
?>

<?php echo $user_editinfo_formhtml;?>
<style type="text/css">
.control_inframe{
	position:absolute;
}
.control_table{width:100%;}
</style>
<div style="height:40px" id="control_show_div">
	<div id="control_div" style="width:100%;min-width:600px">
		<img src="<?php echo ADMIN_IMGPATH;?>/admin/spacer.gif" style="width:600px;height:0px" /><br/>
		<table border="0" cellpadding="0" cellspacing="0" class="control_table" align="center">
			<tr>
				<td><img id="control_table_left" src="<?php echo ADMIN_IMGPATH;?>/admin/spacer.gif" style="height:0px" /></td>
				<td id="control_table_right" style="width:100%;">
					<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-top:none;">
					<tr>
						<td class="td1" width="100" align="right">带(<font color="red">*</font>)必填</td><td class="td1">
						<input type="submit" value="保存信息" class="bbtn" id="info_submit_btton" /> 
						<input type="button" value="返回" class="btns" onclick="goUrl('<?php echo $forward;?>');" id="info_goback_btton" />
						</td>
					</tr>
					</table>
				</td>
			</tr>
		</table>
	</div>
</div>

<script type="text/javascript">
	set_control_fixed();
	window.onscroll = set_control_fixed;
</script>

</form>
<?php
}else{
	echo $user_editinfo_formhtml;
?>
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-top:none;">
<tr>
	<td class="td1" width="100" align="right">&nbsp;</td><td class="td1">
	<input type="button" value="返回上一页" class="btnl" onclick="goUrl('<?php echo $forward;?>');" />
	<a class="btn" href="<?php echo Myqee::url('info/'.($classid>0?'editbyclassid/'.$classid:'edit/'.$dbname).'/'.$myinfo[$sys_field['id']].'?forward='.$forward.($showheader?'&fullpage=yes':''));?>">修改信息</a> 
	</td>
</tr>
</table>
<?php
}
?>
<script>

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
