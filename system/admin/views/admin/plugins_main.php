<?php
$page_title = '插件管理首页';
$page_index = 'plugins';
if ($_GET['fullpage']=='yes')$showheader='fullpage';
if ($showheader == 'fullpage'):
	View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<script type="text/javascript">
function changeHeight(){return false;}
</script>
<?php
else:
	$showheader = '';
	View::factory('admin/header_frame') -> render(TRUE);
?>
<?php
endif;
?>
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
<form method="post" name="myforms" action="<?php echo Myqee::url('plugins/main_save');?>" target="hiddenFrame">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" id="myEditorTable">
	<tr>
		<th class="td1" width="30">排序</th>
		<th class="td1" width="180">插件目录</th>
		<th class="td1">名称</th>
		<th class="td1" width="60">是否启用</th>
		<th class="td1" width="250">管理</th>
	</tr>
</table>

<table cellspacing="1" cellpadding="4" bgcolor="#ffffff" class="tableborder" align="center" border="0" style="border-top:none"><tr>
	<td class="td1" width="220"><input type="button" value="上移选定" onclick="if (!myTable['mytable'])return;myTable['mytable'].up(1);" class="btn" />
	<input type="button" value="下移选定" class="btn" onclick="if (!myTable['mytable'])return;myTable['mytable'].down();" />
	</td>
	<td class="td1">
	<input type="submit" value="保存修改" class="bbtn" /> 
	</td>
</tr>
</table>

<script type="text/javascript">
	function showEditorTable(){
		var dataArray = <?php echo $plugins;?>;
		var tableInfo= new Array();
		var i = 0;
		if (dataArray.length==0)return;
		for (var item in dataArray)
		{
			tableInfo[i] = new Array(
			'<center>'+item+'</center>',
			'<!--[if lte IE 6]><div style="position:absolute;height:24px;"><div style="position:absolute;top:-4px;width:95%;padding:0 12px 0 0;"><![endif]--><input type="text" name="plugins['+item+'][name]" class="input" style="width:92%" size="22" value="'+(dataArray[item]['name']||item)+'" maxlength="50" /><!--[if lte IE 6]></div></div>&nbsp;<![endif]-->',
			'<center><select name="plugins['+item+'][isuse]"><option value="1"'+(dataArray[item]['isuse']==1?' selected="selected"':'')+'>是</option><option value="0"'+(dataArray[item]['isuse']==1?'':' selected="selected"')+'>否</option></center>',
			'<center><a href="'+('<?php echo Myqee::url('plugins/config/{{item}}');?>').replace(/\{\{item\}\}/g,item)+'" class="btn2">修改配置</a><input type="button" value="导出" title="制作插件安装包" class="btnss" /><input type="button" value="检查更新" class="btn_disabled btn2" title="开发中..." /><input type="button" onclick="ask_del(\''+item+'\',\'plugins/uninstall\',\'您确实要删除此插件？将删除插件程序。\');" value="卸载" class="btnss" /></center>'
			);
			i++;
		}
		myTable['mytable']=new CreateTable('myEditorTable',tableInfo);
	}

	showEditorTable();
</script>


</form>

<?php
if ($showheader == 'fullpage'):
View::factory('admin/footer') -> render(TRUE);
else:
?>
<script type="text/javascript">
myqee();
//changeHeight();
</script>
</body>
</html>

<?php
endif;
?>