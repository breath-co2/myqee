<?php
$page_index = 'index';
$page_title = '后台菜单管理';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
$defaulttarget = array(''=>'默认','_self'=>'相同窗口','_blank'=>'新窗口','_parent'=>'父窗口','[other]'=>'其它','[other2]'=>'自定义');
?>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('index/menu');?>">后台菜单</a>
</div>

<script type="text/javascript">
	var navKey = 0;
	function add_row(tableid,fatherkey){
		var trarray = new Array(get_tr_array('__'+navKey,fatherkey,{}));
		myTable['mytable_'+tableid].addrows(1,5,trarray);
		navKey++;
	}

	function del_row(tr,isautodel){
		if (isautodel!='yes'){
			confirm('<?php echo Myqee::lang('admin/index.menu.makesuredelete_menu');?>',450,190,null,function(et){
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

	function showEditorTable(childMenuArray,theid,fatherkey){
		var tableInfo= new Array();
		var i = 0;
		var sysMenuArray = childMenuArray['sub'];
		for (var key in sysMenuArray)
		{
			tableInfo[i] = get_tr_array(key,fatherkey,sysMenuArray[key]);
			i++;
		}
		myTable['mytable_'+theid]=new CreateTable('editorTable_'+theid,tableInfo);
	}

	function get_tr_array(key,fatherkey,sysmenu){
		var trarray;
		trarray = new Array(
			'<!--[if lte IE 6]><div style="position:absolute;"><div style="position:absolute;top:-4px;"><div style="padding:0;"><![endif]--><input type="text" name="menu['+fatherkey+'][sub]['+key+'][name]" class="input" size="18" value="'+(key.substring(0,2) == '__'?'':(sysmenu['name']||key))+'" maxlength="50" style="width:90%;"/><!--[if lte IE 6]></div></div></div>&nbsp;<![endif]-->',
			'<!--[if lte IE 6]><div style="position:absolute;"><div style="position:absolute;top:-4px;"><div style="padding:0 0 0 4px;"><![endif]--><input type="text" name="menu['+fatherkey+'][sub]['+key+'][address]" class="input" size="20" value="'+(key.substring(0,2) == '__'?'':(sysmenu['address']||''))+'" maxlength="50" style="width:90%;"/><!--[if lte IE 6]></div></div></div>&nbsp;<![endif]-->',
			'<!--[if lte IE 6]><div style="position:absolute;"><div style="position:absolute;top:-4px;"><div style="padding:0;"><![endif]--><input type="text" name="menu['+fatherkey+'][sub]['+key+'][level]" class="input" size="18" value="'+(key.substring(0,2) == '__'?'':(sysmenu['level'] == null ? '':sysmenu['level']))+'" maxlength="50" style="width:90%;"/><!--[if lte IE 6]></div></div></div>&nbsp;<![endif]-->',
			'<center><select name="menu['+fatherkey+'][sub]['+key+'][target]" style="width:61px;" id="menu['+fatherkey+'][sub]['+key+'][target]" onchange="if(this.value==\'[other]\'||this.value==\'[other2]\'){this.style.display=\'none\';$(\'menu['+fatherkey+'][sub]['+key+'][target2]\').style.display=\'block\';$(\'menu['+fatherkey+'][sub]['+key+'][target2]\').focus()}" ><option value=""'+(sysmenu['target']==''?' selected="selected"':'')+'>默认</option><option value="_self" '+(sysmenu['target']=='_self'?' selected="selected"':'')+' >相同窗口</option><option value="_blank" '+(sysmenu['target']=='_blank'?' selected="selected"':'')+' >新窗口</option><option value="_parent" '+(sysmenu['target']=='_parent'?' selected="selected"':'')+' >父窗口</option><option value="[other]" '+(sysmenu['target']=='[other]'?' selected="selected"':'')+' >其它</option><option value="[other2]">自定义</option></select><input type="text" name="menu['+fatherkey+'][sub]['+key+'][target2]" value="'+(key.substring(0,2) == '__'?'':(sysmenu['target2'] == null ? '':sysmenu['target2']))+'" id="menu['+fatherkey+'][sub]['+key+'][target2]" class="input" size="8" style="display:none;" onblur="$(\'menu['+fatherkey+'][sub]['+key+'][target]\').value=this.value==\'\'?\'\':\'[other]\';this.style.display=\'none\';$(\'menu['+fatherkey+'][sub]['+key+'][target]\').style.display=\'\';" /></center>',

			key.substring(0,1) == '_' ?
				'<center><input onclick = "del_row(this.parentNode.parentNode.parentNode,\'yes\')" type="button" value="删除" class="btnss" /></center>'
				:
				'<center><input id="menu['+fatherkey+'][sub]['+key+'][is_use]" name="menu['+fatherkey+'][sub]['+key+'][is_use]" type="checkbox" value="1"'+(sysmenu['is_use']==1?' checked="checked"':'')+' /></center>'
		);
		return trarray;
	}

	function isMenuUse(id,isuse){
		var obj = $('isuse_Div_'+id);
		if (!obj)return;
		if (isuse){
			obj.style.display = '';
		}else{
			obj.style.display = 'none';
		}
	}

	function in_array(needle, haystack){
		for(var i in haystack){
			if(haystack[i] == needle){
				_alert(haystack[i]);
				return true;
			}
		}
		return false;
	}

	function addFatherMenu(){
		win('<form method="post" name="myforms" action="<?php echo Myqee::url('index/addmenusave/');?>" target="hiddenFrame">'+
			'<table border="0" cellpadding="4" cellspacing="1" width="96%" align="center" class="tableborder" id=""><tr><th colspan="2">添加父菜单</th></tr>'+
			'<tr><td class="td1" align="right" width="90">菜单名称：</td><td class="td2"><input type="text" name="menu[name]" value="" class="input"  size="39" class="input" /></td></tr>'+
			'<tr><td class="td1" align="right">链接地址：</td><td class="td2" align="left"><input type="text" name="menu[address]" value="" class="input" size="39" class="input" /></td></tr>'+
			'<tr><td class="td1" align="right">权限：</td><td class="td2" align="left"><input type="text" name="menu[level]" value="" class="input" size="39" class="input" /></td></tr>'+
			'<tr><td class="td1" align="right">KEY值：</td><td class="td2" align="left"><input type="text" name="menu[key]" value="" class="input" size="39" class="input" /></td></tr>'+
			'<tr><td class="td1" align="right">打开方式：</td><td class="td2" align="left"><select name="menu[target]" style="width:61px;" id="menu[target]" onchange="if(this.value==\'[other]\'||this.value==\'[other2]\'){this.style.display=\'none\';$(\'menu[target2]\').style.display=\'block\';$(\'menu[target2]\').focus()}">'+
			'<option value="">默认</option>'+
			'<option value="_self">相同窗口</option>'+
			'<option value="_blank">新窗口</option>'+
			'<option value="_parent">父窗口</option>'+
			'<option value="[other]">其它</option>'+
			'<option value="[other2]">自定义</option>'+
			'</select><input type="text" name="menu[target2]" value="" id="menu[target2]" class="input" size="8" style="display:none;" onblur="$(\'menu[target]\').value=this.value==\'\'?\'\':\'[other]\';this.style.display=\'none\';$(\'menu[target]\').style.display=\'\';" /></td></tr>'+
			'<tr><td class="td1" align="right">排序(序号)：</td><td class="td2" align="left"><input type="text" name="menu[myorder]" value="" class="input" class="input" size="8" /></td></tr>'+
			'</table>'+
			'<table border="0" cellpadding="4" cellspacing="1" width="96%" align="center" class="tableborder" style="border-top:none">'+
			'<tr><td class="td1" align="right" width="90">&nbsp;</td>'+
			'<td class="td1"><input type="submit" value="保存菜单" class="bbtn" /><input onclick="goback(-1,\'<?php echo Myqee::url('index/menu') ?>\')" type="button" value="返回" class="btns" /></td></tr>'+
			'</table>'+
			'</form>',420,310);
	}
</script>
<form method="post" name="myforms" action="<?php echo Myqee::url('index/adminmenusave/');?>" target="hiddenFrame">
<div style="width:96%;margin:auto;height:26px;">
	<div style="float:right;"><input onclick = "addFatherMenu()" type="button" value="添加菜单" class="btn" /></div>
	<ul class="ul tag" id="mytag_title"></ul>
</div>
<div class="clear"></div>
<?php 
$i=0;
foreach ($data as $key => $item):
$i++;
?>
<script>
(function(){
	$('mytag_title').innerHTML +='<li <?php if ($i==1)echo 'class="now" ';?>id="mytag_<?php echo $i;?>" onclick="tag(this.id,\'mytag\',\'mytagmain\');document.location.hash=\'#tag<?php echo $i;?>\'"><?php echo $item['name'];?></li>';
})();
</script>
<div id="mytagmain_<?php echo $i;?>"<?php if ($i>1)echo ' style="display:none;"';?>>
<?php if($key != '' && $key != NULL && in_array($key,$allkey)){form::hidden('menu['.$key.'][key]',$key);}else{echo form::hidden('menu['.$key.'][key]',$key);}?>
<table border="0" cellpadding="4" cellspacing="1" width="96%" align="center" class="tableborder" id="table_<?php echo $i;?>">
	<tr>
		<th colspan="2"><?php echo $item['name']?></th>
	</tr>
	<tr>
		<td class="td1" align="right" width="90">菜单名称：</td>
		<td class="td2">
		<?php echo form::input('menu['.$key.'][name]',$item['name'],' size="39" class="input"');?>
		</td>
	</tr>
	<tr>
		<td class="td1" align="right">链接地址：</td>
		<td class="td2" align="left">
			<?php echo form::input('menu['.$key.'][address]',$item['address'],'size="39" class="input"');?>
		</td>
	</tr>
	<tr>
		<td class="td1" align="right">权限：</td>
		<td class="td2" align="left">
			<?php echo form::input('menu['.$key.'][level]',$item['level'],'size="39" class="input"');?>
		</td>
	</tr>
	<tr>
		<td class="td1" align="right">打开方式：</td>
		<td class="td2" align="left">
			<?php echo form::dropdown('menu['.$key.'][target]',$defaulttarget,$defaulttarget[$item['target']]?$item['target']:'[other]','style="width:61px;" id="menu['.$key.'][target]" onchange="if(this.value==\'[other]\'||this.value==\'[other2]\'){this.style.display=\'none\';$(\'menu['.$key.'][target2]\').style.display=\'block\';$(\'menu['.$key.'][target2]\').focus()}"')?>
			<input type="text" name="menu[<?php echo $key?>][target2]" value="<?php echo $item['target2'];?>" id="menu[<?php echo $key?>][target2]" class="input" size="8" style="display:none;" onblur="$('menu[<?php echo $key?>][target]').value=this.value==''?'':'[other]';this.style.display='none';$('menu[<?php echo $key?>][target]').style.display='';" />
		</td>
	</tr>
	<tr>
		<td class="td1" align="right">排序(序号)：</td>
		<td class="td2" align="left">
			<?php echo form::input('menu['.$key.'][myorder]',$item['myorder'],'class="input" size="8"');?>
		</td>
	</tr>
	<?php if ($key != '' && $key != NULL && in_array($key,$allkey)) {?>
	<tr>
		<td class="td1" align="right">启用：</td>
		<td class="td2" align="left">
			<?php echo form::radio('menu['.$key.'][is_use]',1,!isset($item['is_use']) || (int)$item['is_use']==1?TRUE:FALSE,'id="isuse_'.$i.'" onclick="isMenuUse('.$i.',this.checked)"');?>是 
			<?php echo form::radio('menu['.$key.'][is_use]',0,isset($item['is_use']) && (int)$item['is_use']==0?TRUE:FALSE,'onclick="isMenuUse('.$i.',false);"');?>否
			<font color="#999999">若不启用，则在保存时将清除本项菜单自定义设置。</font>
		</td>
	</tr>
	<?php } else {?>
	<tr>
		<td class="td1" align="right">启用：</td>
		<td class="td2" align="left">
			<?php echo form::button('','删除',' onclick = "del_row(this.parentNode.parentNode.parentNode,\'yes\')" type="button" class="btnss" ');?>
		</td>
	</tr>
	<?php } ?>
	<tr>
		<td class="td1" align="right">子菜单设置：</td>
		<td class="td2">
			<div id="isuse_Div_<?php echo $i;?>">
				<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="width:100%;" id="editorTable_<?php echo $i;?>">
					<tr>
						<th class="td1" width="26">排序</th>
						<th class="td1" width="16%">菜单名称</th>
						<th class="td1">链接地址</th>
						<th class="td1" width="200">权限</th>
						<th class="td1" width="70">打开方式</th>
						<th class="td1" width="40">启用</th>
					</tr>
				</table>
				<table cellspacing="1" cellpadding="4" bgcolor="#ffffff" class="tableborder" border="0" style="width:100%;border-top:none">
					<tr>
						<td class="td1">
							<input type="button" value="上移选定" onclick="if (!myTable['mytable_<?php echo $i;?>'])return;myTable['mytable_<?php echo $i;?>'].up(1);" class="btn" /> 
							<input type="button" value="下移选定" class="btn" onclick="if (!myTable['mytable_<?php echo $i;?>'])return;myTable['mytable_<?php echo $i;?>'].down();" />
							<input type="button" value="按菜单名排序" onclick="myTable['mytable_<?php echo $i;?>'].sort(1);" class="btnl" />
						</td>
						<td class="td1" width="100" align="right">
							<input type="button" value="添加子菜单" onclick="add_row(<?php echo $i;?>,'<?php echo $key;?>');" class="btn" />
						</td>
					</tr>
				</table>
			</div>&nbsp;
		</td>
	</tr>
</table>
</div>
<script type="text/javascript">
(function (){
	showEditorTable(<?php echo Tools::json_encode($item);?>,<?php echo $i;?>,<?php echo Tools::json_encode($key);?>);
	var obj = $('isuse_<?php echo $i?>');
	if (obj)obj.onclick();
})();
</script>
<?php 
endforeach;
?>
<table border="0" cellpadding="4" cellspacing="1" width="96%" align="center" class="tableborder" style="border-top:none" >
	<tr>
		<td class="td1" align="right" width="90" >&nbsp;</td>
		<td class="td1"><input type="submit" value="保存模型" class="bbtn" /><input onclick="goback(-1,'<?php echo Myqee::url('index/welcome') ?>')" type="button" value="返回" class="btns" /></td>
		<td class="td1" align="right" width="100" ><input onclick="confirm('您确定恢复默认配置文件吗？',450,null,null,function(et){if(et=='ok')goUrl('<?php echo Myqee::url('index/memu_reset');?>','hiddenFrame')});" type="button" value="恢复默认配置" class="btnl" /></td>
	</tr>
</table>
</form>
<script>
if (document.location.hash =='#tag1'||document.location.hash =='#tag2'||document.location.hash =='#tag3'||document.location.hash =='#tag4'||document.location.hash =='#tag5'||document.location.hash =='#tag6'||document.location.hash =='#tag7'||document.location.hash =='#tag8'){
	tag('mytag_'+document.location.hash.substr(4),'mytag','mytagmain');
}
</script>

<?php View::factory('admin/footer') -> render(TRUE);?>