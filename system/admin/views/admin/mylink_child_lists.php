<?php
$page_title = '快速链接';
$page_index = 'info';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<script type="text/javascript">
function dellink(tr,isautodel){
	if (isautodel!='yes'){
		confirm('<?php echo Myqee::lang('admin/info.list.makesuredelete_link');?>',450,190,null,function(et){
			if (et =='ok'){
				if (tr){
					tr.parentNode.removeChild(tr);
				}
			}
		});
		return;
	}
	if (tr){
		tr.parentNode.removeChild(tr);
	}
}
var navKey = 0;
function add_link(){
	//add a row to the rows collection and get a reference to the newly added row
	var listtable = $("mynavTable");
	var newRow = listtable.insertRow(listtable.rows.length);
	newRow.align='center';
	var i=0;

	var oCell = newRow.insertCell(i);
	oCell.className='td1';
	oCell.innerHTML = '<input type="text" size="3" class="input" name="data[__'+navKey+'][myorder]" value="" title="默认为0" />';
	i++;
	
	oCell = newRow.insertCell(i);
	oCell.className='td2';
	oCell.innerHTML = '<font color="#0000cc">自定义</font>';
	i++;
	
	oCell = newRow.insertCell(i);
	oCell.className='td2';
	oCell.innerHTML = '<input type="text" size="10" name="data[__'+navKey+'][newkey]" class="input" value="" title="链接标识不能空" />';
	i++;
	
	oCell = newRow.insertCell(i);
	oCell.className='td2';
	oCell.innerHTML = '<input type="text" size="16" name="data[__'+navKey+'][name]" class="input" value="" title="链接名称不能空" />';
	i++;
	
	oCell = newRow.insertCell(i);
	oCell.className='td2';
	oCell.align='left';
	oCell.valign='top';
	oCell.innerHTML = '<!--[if IE]><div style="position:absolute;"><div style="position:absolute;top:-4px"><div style="padding:0 12px 0 4px;"><![endif]--><input type="text" size="12" name="data[__'+navKey+'][url]" class="input" value="" style="width:92% !important;width:96%;" title="链接地址不能空" /><!--[if IE]></div></div></div>&nbsp;<![endif]-->';
	i++;
	
	oCell = newRow.insertCell(i);
	oCell.className='td2';
	oCell.innerHTML = '<select name="data[__'+navKey+'][target]" style="width:60px;" id="data[__'+navKey+'][target]" onchange="if(this.value==\'[other]\'||this.value==\'[other2]\'){this.style.display=\'none\';$(\'data[__'+navKey+'][target2]\').style.display=\'block\';$(\'data[__'+navKey+'][target2]\').focus()}"><option value="" selected="selected">默认</option><option value="_self">相同窗口</option><option value="_blank">新窗口</option><option value="_parent">父窗口</option><option value="[other]">其它</option><option value="[other2]">自定义</option></select><input type="text" name="data[__'+navKey+'][target2]" value="" id="data[__'+navKey+'][target2]" class="input" size="8" style="display:none;" onblur="$(\'data[__'+navKey+'][target]\').value=this.value==\'\'?\'\':\'[other]\';this.style.display=\'none\';$(\'data[__'+navKey+'][target]\').style.display=\'block\';" /> ';
	i++;
	
	oCell = newRow.insertCell(i);
	oCell.className='td2';
	oCell.innerHTML = '0';
	i++;
	
	oCell = newRow.insertCell(i);
	oCell.className='td2';
	oCell.innerHTML = '<input type="button" style="visibility:hidden" class="btn" /> <input onclick="dellink(this.parentNode.parentNode,\'yes\')" type="button" value="删除链接" class="btn" title="新增的链接的标识、链接名或链接地址留空则自动忽略" />';

	navKey++;
	myqee(newRow);
}
</script>
<div class="loaction" style="height:22px;">
<div style="float:left;padding-top:4px">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('mylink/index');?>">快速链接</a> -&gt; 链接管理
</div>
<div style="float:right"><input type="button" value="上级链接" class="btn" style="margin-right:0" onclick="goUrl('<?php echo Myqee::url('mylink/links/'.$id).($nav_parentpath?'?path='.$nav_parentpath:'');?>');"<?php if($nav_path == $nav_parentpath)echo ' disabled="disabled"';?> /></div>
</div>
<form action="<?php echo Myqee::url('mylink/save_child_links/'.$id).'?path='.$nav_path;?>" method="POST" target="hiddenFrame">
<table border="0" cellpadding="3" cellspacing="1" width="96%" align="center" class="tableborder" id="mynavTable">
<tr>
	<th class="td1" width="40">排序</th>
	<th class="td1" width="50">类别</th>
	<th class="td1" width="80">链接标识</th>
	<th class="td1" width="120">链接名称</th>
	<th class="td1">链接地址</th>
	<th class="td1" width="70">打开方式</th>
	<th class="td1" width="45">子链接</th>
	<th class="td1" width="180">操作</th>
</tr>
<?php
$defaulttarget = array(''=>'默认','_self'=>'相同窗口','_blank'=>'新窗口','_parent'=>'父窗口','[other]'=>'其它','[other2]'=>'自定义');
if(is_array($list)):
	$countlist = count($list);
	foreach ($list as $key => $item):
	$i++;
?>
<tr align="center"<?php if($i%2==0){echo ' class="td3"';} ?> onmouseover="tr_moveover(this)" onmouseout="tr_moveout(this)">
	<td class="td1">
		<input type="text" size="3" class="input" name="data[<?php echo $key;?>][myorder]" value="<?php echo $item['myorder']?>" />
	</td>
	<td class="td1">
		<?php echo $item['infoid']?'<font color="#cc0000">数据调用</font>':'<font color="#0000cc">自定义</font>';?>
	</td>
	<td class="td2">
		<input type="hidden" size="10" name="data[<?php echo $key;?>][oldkey]" value="<?php echo $key;?>" />
		<input type="text" size="10" name="data[<?php echo $key;?>][newkey]" value="<?php echo $key;?>" class="input" />
	</td>
	<td class="td2">
		<input type="text" size="16" name="data[<?php echo $key;?>][name]" class="input" value="<?php echo htmlspecialchars($item['name']);?>"<?php if($item['id'])echo ' title="清空则恢复默认值"';?> />
	</td>
	<td class="td2" align="left" valign="top">
		<!--[if IE]><div style="position:absolute;"><div style="padding:0 12px 0 4px;"><![endif]-->
		<input type="text" size="12" name="data[<?php echo $key;?>][url]" class="input" value="<?php echo $item['url'];?>" style="width:92% !important;width:96%;"<?php if($item['infoid'])echo ' title="清空URL则恢复默认值"';?> />
		<!--[if IE]></div></div>&nbsp;<![endif]-->
	</td>
	<td class="td2">
		<?php echo form::dropdown('data['.$key.'][target]',$defaulttarget,$defaulttarget[$item['target']]?$item['target']:'[other]','style="width:60px;" id="data['.$key.'][target]" onchange="if(this.value==\'[other]\'||this.value==\'[other2]\'){this.style.display=\'none\';$(\'data['.$key.'][target2]\').style.display=\'block\';$(\'data['.$key.'][target2]\').focus()}"')?>
		<input type="text" name="data[<?php echo $key;?>][target2]" value="<?php echo $item['target'];?>" id="data[<?php echo $key;?>][target2]" class="input" size="8" style="display:none;" onblur="$('data[<?php echo $key;?>][target]').value=this.value==''?'':'[other]';this.style.display='none';$('data[<?php echo $key;?>][target]').style.display='block';" />
	</td>
	<td class="td2"><?php echo count($item['submenu']);?></td>
	<td class="td2">
		<input onclick="goUrl('<?php echo ADMIN_URLPATH.'mylink/links/'.$id.'?path='.($nav_path?$nav_path.'/':'').$key; ?>')" type="button" value="管理子链接" class="btn" style="letter-spacing:0" /> 
		<input onclick="dellink(this.parentNode.parentNode)" type="button" value="删除链接" class="btn" title="删除链接后，点“保存修改”才会生效！" />
	</td>
</tr>

<?php
	endforeach;
endif;
?>
</table>
<table border="0" cellpadding="3" cellspacing="1" width="96%" align="center" class="tableborder" style="border-top:none;">
<tr>
	<td class="td1">
	排序号越小越靠前，数据调用的“链接名称”或“链接地址”清空则恢复默认值，菜单名称支持HTML</td>
	<td class="td1" width="123" align="center">
	<input onclick="add_link();" type="button" value="新增链接" class="bbtn" /> 
	</td>
	<td class="td1" width="180" align="center">
	<input type="submit" value="保存修改" class="bbtn" />
	</td>
</tr>
</table>
</form>

<?php View::factory('admin/footer') -> render(TRUE);?>