<?php
$page_index = 'model';
$page_title = '字段管理';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>

<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('model/index');?>">模型管理</a> -&gt; <a href="<?php echo Myqee::url('model/dblist');?>">数据表管理</a> -&gt; <?php echo $page_title;?>
</div>
<form method="post" name="myforms" action="<?php echo Myqee::url('model/dborder/'.$id);?>" target="hiddenFrame">

<style type="text/css">
.table_title_div{
	position:fixed;
	top:66px;
	z-index:10;
	_position:absolute;
	_top:expression(eval(document.compatMode &&document.compatMode=="CSS1Compat") ?documentElement.scrollTop+66:document.body.scrollTop+66);
}
.table_title{
	position:absolute;
	
}
</style>
<div id="table_title_div" class="table_title" style="width:100%;min-width:800px;">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" id="myEditorTableTitle">
	<tr>
		<th class="td1" width="52">排序操作</th>
		<th class="td1" width="150">字段</th>
		<th class="td1" width="150">字段标识</th>
		<th class="td1">字段说明</th>
		<th class="td1" width="130">字段类型</th>
		<th class="td1" width="70" title="当此项选择为列出时，在信息管理里列表页将会列出此项。">管理列表项</th>
		<th class="td1" width="120">操作</th>
	</tr>
</table>
</div>
<script type="text/javascript">	
	document.write('<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" id="myEditorTable">'+$('myEditorTableTitle').innerHTML+'</table>');
</script>

<div style="height:30px">
<div id="control_div" style="width:100%;min-width:800px" class="control">
	<img src="<?php echo ADMIN_IMGPATH;?>/admin/spacer.gif" style="width:800px;height:0px" /><br/>
	<table cellspacing="1" cellpadding="4" bgcolor="#ffffff" class="tableborder" align="center" border="0" style="border-top:none"><tr>
		<td class="td1"><input type="button" value="上移选定" onclick="if (!myTable['mytable'])return;myTable['mytable'].up(1);" class="btn" />
		<input type="button" value="下移选定" class="btn" onclick="if (!myTable['mytable'])return;myTable['mytable'].down();" />
		<input type="button" value="按字段名排序" onclick="myTable['mytable'].sort(1);" class="btnl" /> 
		<input type="button" value="恢复" class="btns" onclick="confirm('是否要恢复？',null,null,null,function(t){if(t=='ok')goback(0)})" />
		<input type="button" value="保存修改" onclick="document.forms.myforms.submit()" class="bbtn" /></td>
		<td class="td1" width="310" align="right"><input type="button" value="添加字段" onclick="goUrl('<?php echo Myqee::url('model/dbfieldadd/'.$id);?>')" class="bbtn" /></td>
	</tr></table>
</div>
</div>
</form>

<script type="text/javascript">
	function showEditorTable(){
		var modelArray = <?php echo $field;?>;
		var tableInfo= new Array();
		var i = 0;
		if (modelArray.length==0)return;
		for (var item in modelArray)
		{
			tableInfo[i] = new Array(
			item,
			'<input type="text" name="field['+item+'][dbname]" class="input" size="22" value="'+(modelArray[item]['dbname']||item)+'" maxlength="50" />',
			(modelArray[item]['comment']||'&nbsp;'),
			'<center>'+modelArray[item]['type']+'</center>',
			'<center>'+(modelArray[item]['islist']?'<font color="red">是</font>':'否')+'</center>',
			'<center><input type="button" class="btns" value="修改" onclick="goUrl(\'<?php echo ADMIN_URLPATH.'model/dbfieldadd/'.$id;?>/'+item+'\')" /> <input type="button" class="btns" value="删除" onclick="confirm(\'<?php echo Myqee::lang('admin/model.list.makesuredeletefield');?>\',null,null,null,function(t){if(t==\'ok\')goUrl(\'<?php echo ADMIN_URLPATH.'model/dbfielddel/'.$id;?>/'+item+'\',\'hiddenFrame\')});" /></center>'
			);
			i++;
		}
		myTable['mytable']=new CreateTable('myEditorTable',tableInfo);
	}

	showEditorTable();

	
	function set_control_fixed(){
		var h = dom.scrollHeight;		//页面总高度
		var sh = dom.scrollTop;			//被卷去的高度
		var ch = dom.clientHeight;		//可见区域高度
		var obj = $('control_div');
		if (h-sh-ch<90){
			obj.className = '';
		}else if(obj.className!='control'){
			obj.className = 'control';
		}

		var obj2 = $('table_title_div');
		if (sh<60)
		{
			if(obj2.className!='table_title'){
				obj2.className = 'table_title';
			}
		}else if(obj2.className!='table_title_div'){
			obj2.className = 'table_title_div';
		}

		obj.style.left = obj2.style.left = - dom.scrollLeft +'px';
	}
	set_control_fixed();
	window.onscroll = set_control_fixed;

</script>

<?php View::factory('admin/footer') -> render(TRUE);?>