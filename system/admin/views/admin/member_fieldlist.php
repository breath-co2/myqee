<?php
$page_index = 'model';
$page_title = '字段管理';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('model/index');?>">模型管理</a> -&gt; <a href="<?php echo Myqee::url('model/dblist');?>">数据表管理</a> -&gt; <?php echo $page_title;?>
</div>
<form method="post" name="myforms" action="<?php echo Myqee::url('model/dborder/'.$id);?>" target="hiddenFrame">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" id="myEditorTable">
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
<table cellspacing="1" cellpadding="4" bgcolor="#ffffff" class="tableborder" align="center" border="0" style="border-top:none"><tr>
	<td class="td1"><input type="button" value="上移选定" onclick="if (!myTable['mytable'])return;myTable['mytable'].up(1);" class="btn" /> 
	<input type="button" value="下移选定" class="btn" onclick="if (!myTable['mytable'])return;myTable['mytable'].down();" /> 
	<input type="button" value="恢复" class="btns" onclick="confirm('是否要恢复？',null,null,null,function(t){if(t=='ok')goback(0);});" />
	<input type="button" value="保存修改" onclick="document.forms.myforms.submit()" class="bbtn" /></td>
	<td class="td1" width="310" align="right"><input type="button" value="添加字段" onclick="goUrl('<?php echo Myqee::url('model/dbfieldadd/'.$id['id']);?>')" class="bbtn" /></td>
</tr></table>
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
			'<center><input type="button" class="btns" value="修改" onclick="goUrl(\'<?php echo 'model/dbfieldadd/'.$id;?>/'+item+'\')" /> <input type="button" class="btns" value="删除" onclick="del_ask(\''+item+'\',\'model/dbfielddel/<?php echo $id;?>\',\'<?php echo Myqee::lang('admin/model.list.makesuredeletefield');?>\');" /></center>'
			);
			i++;
		}
		myTable['mytable']=new CreateTable('myEditorTable',tableInfo);
	}

	showEditorTable();
</script>

<?php View::factory('admin/footer') -> render(TRUE);?>