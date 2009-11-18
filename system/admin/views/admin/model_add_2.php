<?php
$page_index = 'model';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('model/index');?>">模型管理</a> -&gt; <?php echo $page_title;?>
</div>
<form method="post" name="myforms" action="<?php echo Myqee::url('model/save/'.$model['id']);?>" target="hiddenFrame">
<?php
if ((int)$copyid>0)echo form::hidden('copyid',($copyid));
?>
<div class="mainTable" >
<ul class="ul tag" id="mymodelTag">
	<li class="now" id="mymodel_1" onclick="tag(this.id,'mymodel','mymodelmain','mymodelTag');document.location.hash='#tag1'">模型基本属性</li>
	<li id="mymodel_2" onclick="tag(this.id,'mymodel','mymodelmain','mymodelTag');set_control_fixed();document.location.hash='#tag2'">模型字段设置</li>
	<li id="mymodel_3" onclick="tag(this.id,'mymodel','mymodelmain','mymodelTag');document.location.hash='#tag3'">列表操作菜单</li>
	<li id="mymodel_4" onclick="tag(this.id,'mymodel','mymodelmain','mymodelTag');document.location.hash='#tag4'">数据调用接口</li>
</ul>
</div>
<div style="clear:both"></div>

<div id="mymodelmain_1">
<table border="0" cellpadding="4" cellspacing="1" width="96%" align="center" class="tableborder">
<tr>
	<th colspan="2">模型设置 - <?php echo $page_title;?></th>
</tr>
<tr>
	<td class="td1" align="right" width="150">模型名称：</td>
	<td class="td2"><?php echo form::input('model[modelname]',$model['modelname'],' size="25" class="input"');?> &nbsp;<font color="#a3a3a3">例如“新闻模型”</font></td>
</tr>
<tr>
	<td class="td1" align="right">排序：</td>
	<td class="td2">
		<?php echo form::input('model[myorder]',$model['myorder'],'size="4" class="input"');?> &nbsp;<font color="#a3a3a3">数字越高越靠前</font>
	</td>
</tr>
<tr>
	<td class="td1" align="right">是否启用：</td>
	<td class="td2">
		<?php echo form::dropdown('model[isuse]',array(1=>'是',0=>'否'),$model['isuse']);?>
	</td>
</tr>
<tr>
	<td class="td1" align="right">是否默认：</td>
	<td class="td2">
		<?php echo form::dropdown('model[isdefault]',array(1=>'是',0=>'否'),(int)$model['isdefault']);?>
	</td>
</tr>
<tr>
	<td class="td1" align="right">修改数据表：</td>
	<td class="td2">
		<input type="button" class="btnl" value="修改数据表" onclick="goUrl('<?php echo Myqee::url('model/edit_step1/'.$model['id']) ?>')"<?php if ((int)$copyid){echo 'disabled="disabled"';}else{echo 'title="点击进入修改数据表页面，<br/>修改数据表后，字段项设置将有可能会不能继续使用"';}?> /> &nbsp;<font color="#a3a3a3">此操作请谨慎操作</font>
	</td>
</tr>
</table>
</div>


<div id="mymodelmain_3" style="display:none;">
	<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" id="editorTable_listmenu">
		<tr>
			<th class="td1" width="28">排序</th>
			<th class="td1" width="16%">菜单名称</th>
			<th class="td1" title="例如：<br/>model/index/add/&#38;&#35;123;&#38;&#35;123;id&#38;&#35;125;&#38;&#35;125;<br/>model/index/add/&#38;&#35;123;&#38;&#35;123;title&#38;&#35;125;&#38;&#35;125;.html	<br/>其中，<font color=red>&#38;&#35;123;&#38;&#35;123;id&#38;&#35;125;&#38;&#35;125;</font>对应于当前信息id字段。">链接地址</th>
			<th class="td1" width="116">菜单样式</th>
			<th class="td1" width="70">打开方式</th>
			<th class="td1" width="40">启用</th>
		</tr>
	</table>
	
	<table cellspacing="1" cellpadding="4" bgcolor="#ffffff" class="tableborder" align="center" order="0" style="border-top:none">
		<tr>
			<td class="td1">
				<input type="button" value="上移选定" onclick="if (!myTable['mytable_listmenu'])return;myTable['mytable_listmenu'].up(1);" class="btn" /> 
				<input type="button" value="下移选定" class="btn" onclick="if (!myTable['mytable_listmenu'])return;myTable['mytable_listmenu'].down();" />
				<input type="button" value="按菜单名排序" onclick="myTable['mytable_listmenu'].sort(1);" class="btnl" />
			</td>
			<td class="td1" width="110" align="center">
				<input type="button" value="添加菜单" onclick="add_row();" class="btn" />
			</td>
		</tr>
	</table>

<script type="text/javascript">
	var navKey = 0;
	function add_row(){
		var trarray = new Array(get_tr_array('_'+navKey,{}));
		myTable['mytable_listmenu'].addrows(1,5,trarray);
		myqee($('editorTable_listmenu'));
		navKey++;
	}

	function del_row(tr,isautodel){
		if (isautodel=='yes'){
			confirm('确实删除此菜单？',300,170,null,function(et){
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

	function showEditorMenuTable(mymenu){
		var sysmenu = <?php echo $sysadminlist;?>;
		var tableInfo= new Array();
		var i = 0;
		for (var key in mymenu)
		{
			if (key.substr(0,1)!='_'&&key!='sys_commend'&&key!='sys_edit'&&key!='sys_view'&&key!='sys_del')continue;
			if ((key=='sys_edit'||key=='sys_del') && typeof(mymenu[key])!='object'){
				mymenu[key]={'isuse':1};
			}
			tableInfo[i] = get_tr_array(key,mymenu[key]);
			sysmenu[key] = null;
			i++;
		}
		for(var key in sysmenu){
			if (sysmenu[key]){
				if (key=='sys_edit'||key=='sys_del'){
					sysmenu[key]['isuse']=1;
				}
				tableInfo[i] = get_tr_array(key,sysmenu[key]);
				i++;
			}
		}
		myTable['mytable_listmenu'] = new CreateTable('editorTable_listmenu',tableInfo);
		showEditorMenuTable = null;
	}

	function get_tr_array(key,sysmenu){
		var trarray;
		var mytarget = '';
		if (sysmenu['target']=='_blank'||sysmenu['target']=='_top'||sysmenu['target']=='_self'||sysmenu['target']=='_parent'){
			mytarget = '';
		}else{
			mytarget = sysmenu['target'];
		}
		trarray = new Array(
			'<!--[if lte IE 6]><div style="position:absolute;"><div style="position:absolute;top:-4px;"><div style="padding:0;"><![endif]--><input type="text" name="model[adminlistmenu]['+key+'][name]" class="input" size="18" value="'+(sysmenu['name']||'')+'" maxlength="20" style="width:94%;"/><!--[if lte IE 6]></div></div></div>&nbsp;<![endif]-->',
			key.substring(0,1) == '_'?
				'<!--[if lte IE 6]><div style="position:absolute;"><div style="position:absolute;top:-4px;"><div style="padding:0 0 0 4px;"><![endif]--><input type="text" name="model[adminlistmenu]['+key+'][address]" class="input" size="20" value="'+(sysmenu['address']||'')+'" maxlength="200" style="width:94%;"/><!--[if lte IE 6]></div></div></div>&nbsp;<![endif]-->'
			:
				'<font color="#cccccc">系统路径不需要修改</font>',

			'<center><select name="model[adminlistmenu]['+key+'][class]" style="width:110px;"><option value="btnss"'+(!sysmenu['class']||sysmenu['class']=='btnss'?' selected="selected"':'')+'>默认btnss(1-2字)</option><option value="btns"'+(sysmenu['class']=='btns'?' selected="selected"':'')+'>btns(2-3字)</option><option value="btn2"'+(sysmenu['class']=='btn2'?' selected="selected"':'')+'>btn2(2-3字)</option><option value="btn"'+(sysmenu['class']=='btn'?' selected="selected"':'')+'>btn(4-5字)</option><option value="btnl"'+(sysmenu['class']=='btnl'?' selected="selected"':'')+'>btnl(5-6字)</option><option value="bbtn"'+(sysmenu['class']=='bbtn'?' selected="selected"':'')+'>bbtn(4字大按钮)</option></select></center>',

			key == 'sys_del' ?
				'<center>删除确认</center>'
			:
				'<center><select name="model[adminlistmenu]['+key+'][target]" style="width:61px;" id="model[adminlistmenu]['+key+'][target]" onchange="if(this.value==\'[other]\'||this.value==\'[other2]\'){this.style.display=\'none\';$(\'model[adminlistmenu]['+key+'][target2]\').style.display=\'block\';$(\'model[adminlistmenu]['+key+'][target2]\').focus()}" ><option value=""'+(sysmenu['target']==''?' selected="selected"':'')+'>默认</option><option value="_self" '+(sysmenu['target']=='_self'?' selected="selected"':'')+' >相同窗口</option><option value="_blank" '+(sysmenu['target']=='_blank'?' selected="selected"':'')+' >新窗口</option><option value="_parent" '+(sysmenu['target']=='_parent'?' selected="selected"':'')+' >父窗口</option><option value="[other]" '+(mytarget?' selected="selected"':'')+' >其它</option><option value="[other2]">自定义</option></select><input type="text" name="model[adminlistmenu]['+key+'][target2]" value="'+(mytarget?mytarget:'')+'" id="model[adminlistmenu]['+key+'][target2]" class="input" size="8" style="display:none;" onblur="$(\'model[adminlistmenu]['+key+'][target]\').value=this.value==\'\'?\'\':\'[other]\';this.style.display=\'none\';$(\'model[adminlistmenu]['+key+'][target]\').style.display=\'\';" /></center>',

			key== 'sys_commend'||key== 'sys_edit'||key== 'sys_del'||key== 'sys_view' ?
				'<center><input id="model[adminlistmenu]['+key+'][isuse]" name="model[adminlistmenu]['+key+'][isuse]" type="checkbox" value="1"'+(sysmenu['isuse']==1?' checked="checked"':'')+' /></center>'
				:
				'<center><input onclick = "del_row(this.parentNode.parentNode.parentNode,\'yes\')" type="button" value="删除" class="btnss" /></center>'
		);
		return trarray;
	}
	
	</script>

<script type="text/javascript">
(function (){
	showEditorMenuTable(<?php echo $adminlist;?>);
})();
</script>
</div>

<div id="mymodelmain_4" style="display:none;">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
	<tr>
		<th colspan="2">数据操作接口设置</th>
	</tr>
	<tr>
		<td class="td1" align="right" width="150">新增数据后执行函数：</td>
		<td class="td2">
			<?php echo form::dropdown('model[adminedit][add]',$doinfomodel,$adminedit['add']);?> 
			函数位于admin/api/MY_Info.php的Info_Api类
		</td>
	</tr>
	<tr>
		<td class="td1" align="right">修改数据后执行函数：</td>
		<td class="td2">
			<?php echo form::dropdown('model[adminedit][edit]',$doinfomodel,$adminedit['edit']);?>
		</td>
	</tr>
	<tr>
		<td class="td1" align="right">删除数据后执行函数：</td>
		<td class="td2">
			<?php echo form::dropdown('model[adminedit][del]',$doinfomodel,$adminedit['del']);?>
		</td>
	</tr>
</table>
</div>


<div id="mymodelmain_2" style="display:none;">
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
		<th class="td1" width="28">排序</th>
		<th class="td1" width="120">字段</th>
		<th class="td1" title="清空则恢复默认值">表单名称<br/><img src="<?php echo ADMIN_IMGPATH;?>/admin/spacer.gif" style="width:80px;height:0px;" /></th>
		<th class="td1" title="清空则恢复默认值">表单录入说明<br/><img src="<?php echo ADMIN_IMGPATH;?>/admin/spacer.gif" style="width:87px;height:0px;" /></th>
		<th class="td1" width="60" title="默认值“常规选项”，留空则同上">所属标签</th>
		<th class="td1" width="26">录入</th>
		<th class="td1" width="26">修改</th>
		<th class="td1" width="26">查看</th>
		<th class="td1" width="26">必填</th>
		<th class="td1" width="26">投稿</th>
		<th class="td1" width="26">采集</th>
		<th class="td1" width="26">搜索</th>
		<th class="td1" width="26" title="搜索结合项">结合</th>
		<th class="td1" width="26">列表</th>
		<th class="td1" width="26">内容</th>
		<th class="td1" width="40">高级</th>
	</tr>
</table>
</div>
<script type="text/javascript">	
	document.write('<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" id="myEditorTable">'+$('myEditorTableTitle').innerHTML+'</table>');
</script>

<div style="height:30px">
	<div id="control_div" style="width:100%;min-width:800px" class="control">
	<img src="<?php echo ADMIN_IMGPATH;?>/admin/spacer.gif" style="width:800px;height:0px" /><br/>
	<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-top:none;">
		<tr>
		<td colspan="16" class="td1"><input type="button" value="上移选定" onclick="if (!myTable['mytable'])return;myTable['mytable'].up(1);" class="btn" /> 
		<input type="button" value="下移选定" class="btn" onclick="if (!myTable['mytable'])return;myTable['mytable'].down();" />
		<input type="button" value="按字段名排序" onclick="myTable['mytable'].sort(1);" class="btnl" /> 
		</td>
	</tr></table>
	</div>
</div>

</div>
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-top:none;">
<tr>
	<td class="td1" align="right" width="150" onclick="set_control_fixed()">&nbsp;</td>
	<td class="td1"><input type="submit" value="保存模型" class="bbtn" /> <input onclick="goback(-1,'<?php echo Myqee::url('model/index') ?>')" type="button" value="返回" class="btns" /></td>
</tr>
</table>
</form>
<script type="text/javascript">	
	function showEditorTable(){
		var modelArray = <?php echo $field;?>;
		var thesysfield = <?php echo $the_sysfield;?>;
		var tableInfo= new Array();
		var i = 0;
		if (modelArray.length==0)return;
		var isdisabled = '';
		for (var item in modelArray)
		{
			isinputdisabled = '';
			iseditdisabled = '';
			if (item==thesysfield['id']||item==thesysfield['class_id']||item==thesysfield['class_name'] || modelArray[item]['disable'] == 1){
				if (modelArray[item]['disable'] == 1) {
					isinputdisabled = '';
					iseditdisabled = '';
				} else {
					isinputdisabled = ' disabled="disabled"';
					iseditdisabled = ' disabled="disabled"';
				}
				isdisabled = ' disabled="disabled"';
			}else{
				isdisabled = '';
			}
			tableInfo[i] = new Array(
			'<div style="width:120px;overflow:hidden;" title="'+item+'">'+item+'</div>',
			'<div style="padding-right:8px"><!--[if lte IE 6]><div style="position:absolute;"><div style="position:absolute;top:-4px;"><div style="padding:0;"><![endif]--><input type="text" name="field['+item+'][dbname]" class="input" size="18" value="'+(modelArray[item]['dbname']||item)+'" maxlength="50" style="width:100%;" /><!--[if lte IE 6]></div></div></div>&nbsp;<![endif]--></div>',
			'<div style="padding-right:8px"><!--[if lte IE 6]><div style="position:absolute;"><div style="position:absolute;top:-4px;"><div style="padding:0"><![endif]--><input type="text" name="field['+item+'][comment]" class="input" size="20" value="'+(modelArray[item]['comment']||'')+'" maxlength="50" style="width:100%;" /><!--[if lte IE 6]></div></div></div>&nbsp;<![endif]--></div>',
			//'<center><select size="1"><option value="input">文本框</option><option value="select">下拉框</option><option value="textarea">文本区</option><option value="htmlarea">HTML编辑区</option><option value="radio">单选框</option><option value="checkbox">复选框</option><option value="dateinput">日期录入</option>><option value="timeinput">时间录入</option>><option value="imgupload">图片上传</option><option value="fileupload">文件上传</option></select></center>',
			'<input type="text" name="field['+item+'][tag]" class="input" size="10" value="'+(modelArray[item]['tag']||'')+'" maxlength="50" style="width:50px;" />',
			'<center><input name="field['+item+'][input]"'+isinputdisabled+' type="checkbox" value="1"'+(modelArray[item]['input']?' checked="checked"':'')+' /></center>',
			'<center><input name="field['+item+'][editor]"'+iseditdisabled+' type="checkbox" value="1"'+(modelArray[item]['editor']?' checked="checked"':'')+' /></center>',
			'<center><input name="field['+item+'][view]"'+isdisabled+' type="checkbox" value="1"'+(modelArray[item]['view']?' checked="checked"':'')+' /></center>',
			'<center><input name="field['+item+'][notnull]"'+isdisabled+' type="checkbox" value="1"'+(modelArray[item]['notnull']?' checked="checked"':'')+' /></center>',
			'<center><input name="field['+item+'][post]"'+isdisabled+' type="checkbox" value="1"'+(modelArray[item]['post']?' checked="checked"':'')+' /></center>',
			'<center><input name="field['+item+'][caiji]"'+isdisabled+' type="checkbox" value="1"'+(modelArray[item]['caiji']?' checked="checked"':'')+' /></center>',
			'<center><input name="field['+item+'][search]"'+isdisabled+' type="checkbox" value="1"'+(modelArray[item]['search']?' checked="checked"':'')+' /></center>',
			'<center><input name="field['+item+'][jiehe]"'+isdisabled+' type="checkbox" value="1"'+(modelArray[item]['jiehe']?' checked="checked"':'')+' /></center>',
			'<center><input name="field['+item+'][list]"'+isdisabled+' type="checkbox" value="1"'+(modelArray[item]['list']?' checked="checked"':'')+' /></center>',
			'<center><input name="field['+item+'][content]"'+isdisabled+' type="checkbox" value="1"'+(modelArray[item]['content']?' checked="checked"':'')+' /></center>',
			'<center><input type="button" value="设置" '+isdisabled+' onclick="goUrl(\'<?php echo ADMIN_URLPATH;?>model/editfield/<?php echo $model['id'];?>/'+item+'\')" class="btnss" /></center>'
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
		if (h-sh-ch<110){
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
<script type="text/javascript">
if (document.location.hash =='#tag1'||document.location.hash =='#tag2'||document.location.hash =='#tag3'||document.location.hash =='#tag4'){
	tag('mymodel_'+document.location.hash.substr(4),'mymodel','mymodelmain','mymodelTag');
}
</script>
<?php View::factory('admin/footer') -> render(TRUE);?>