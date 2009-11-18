<?php
$page_index = 'model';
if($db['id']){
	$page_title = '修改数据表';
}else{
	$page_title = '新增数据表';
}
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('model/index');?>">模型管理</a> -&gt; <a href="<?php echo Myqee::url('model/dblist');?>">数据表管理</a> -&gt; <?php echo $page_title;?>
</div>
<form method="post" name="myforms" action="<?php echo Myqee::url('model/'.((int)$copyid?'dbsavecopy/':'dbsave/').$db['id']);?>" target="hiddenFrame">
<div class="mainTable" >
<ul class="ul tag">
	<li class="now" id="mytag_1" onclick="tag(this.id,'mytag','mytagmain');document.location.hash='#tag1'">基本属性</li>
	<li id="mytag_2" onclick="tag(this.id,'mytag','mytagmain');document.location.hash='#tag2'">数据表模型</li>
	<li id="mytag_3" onclick="tag(this.id,'mytag','mytagmain');document.location.hash='#tag3'">扩展数据表</li>
	<li id="mytag_4" onclick="tag(this.id,'mytag','mytagmain');document.location.hash='#tag4'">子数据表</li>
</ul>
</div>
<div style="clear:both"></div>
<div id="mytagmain_1">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
<tr>
	<th colspan="2">数据表设置 - <?php echo $page_title;?></th>
</tr>
<tr>
	<td class="td1" align="right" width="120">数据表名称：</td>
	<td class="td2"><?php echo form::input('db[dbname]',$db['dbname'],' size="25" class="input"');?> &nbsp;<font color="#a3a3a3">例如“新闻数据表”，不能空</font></td>
</tr>
<tr>
	<td class="td1" align="right">数据表：</td>
	<td class="td2"><?php echo form::input('db[name]',$db['name'],' size="15" class="input"');?> &nbsp;<font color="#a3a3a3">例如“news”，注意：只允许是字母数字和下划线的组合，不能空</font></td>
</tr>
<tr>
	<td class="td1" align="right">所属数据库：</td>
	<td class="td2"><?php echo $dbdatabase;?> &nbsp;<font color="#a3a3a3">所选后不能修改</font></td>
</tr>
<tr>
	<td class="td1" align="right">排序：</td>
	<td class="td2">
		<?php echo form::input('db[myorder]',$db['myorder'],'size="4" class="input"');?> &nbsp;<font color="#a3a3a3">数字越高越靠前</font>
	</td>
</tr>
<tr>
	<td class="td1" align="right">是否启用：</td>
	<td class="td2">
		<?php echo form::dropdown('db[isuse]',array(1=>'是',0=>'否'),$db['isuse']);?>
	</td>
</tr>
<tr>
	<td class="td1" align="right">是否默认：</td>
	<td class="td2">
		<?php echo form::dropdown('db[isdefault]',array(0=>'否',1=>'是'),(int)$db['isdefault']);?>
	</td>
</tr>
<tr>
	<td class="td1" align="right">是否用户数据表：</td>
	<td class="td2">
		<?php echo form::dropdown('db[ismemberdb]',array(0=>'否',1=>'是'),(int)$db['ismemberdb']);?> &nbsp;<font color="#a3a3a3">选择用户数据表，将作为用户表使用，在其它模型中将不显示</font>
	</td>
</tr>
<tr>
	<td class="td1" align="right">前台数据读取方式：</td>
	<td class="td2">
		<?php echo form::dropdown('db[readbydbname]',array(0=>'仅允许通过栏目ID读取',1=>'允许通过数据表名读取'),(int)$db['readbydbname']);?> 
		&nbsp;<font color="#a3a3a3">若选择后者，则前台可以通过/myclass/dbname/1.html这样的形式读取数据</font>
	</td>
</tr>
<tr>
	<td class="td1" align="right">数据表说明：</td>
	<td class="td2">
		<?php echo form::textarea('db[content]',$db['content'],'class="input" cols="60" rows="5"');?>
	</td>
</tr>
</table>
</div>
<div id="mytagmain_2" style="display:none">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-bottom:none;">
<tr>
	<th colspan="2">数据表模型设置</th>
</tr>
<tr>
	<td class="td1" align="right" width="120">是否启用数据表模型：</td>
	<td class="td2">
		<?php echo form::checkbox('db[usedbmodel]',1,$db['usedbmodel']?true:false);?>&nbsp;<span class="helpicon" title="数据表拥有一个独立的模型配置，这样就可以脱离模型独立使用。"></span>
		<font color="#ff6600">只有启用数据表模型，下面的设置才有效！</font>
	</td>
</tr>
</table>
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-top:none;">
<tr>
<td class="td2" colspan="2">
<ul class="ul tag" id="mymodelTag">
	<li id="mymodel_1" onclick="tag(this.id,'mymodel','mymodelmain','mymodelTag');" class="now">模型字段设置</li>
	<li id="mymodel_2" onclick="tag(this.id,'mymodel','mymodelmain','mymodelTag')">列表操作菜单</li>
	<li id="mymodel_3" onclick="tag(this.id,'mymodel','mymodelmain','mymodelTag');">数据调用接口</li>
</ul>
<div style="clear:both"></div>

<div id="mymodelmain_1">
	<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="width:100%;" id="myEditorTable">
		<tr>
			<th class="td1" width="28">排序</th>
			<th class="td1">字段</th>
			<th class="td1" title="清空则恢复默认值">表单名称</th>
			<th class="td1" title="清空则恢复默认值">表单录入说明</th>
			<th class="td1" width="60" title="默认值“常规选项”，留空则同上">所属标签</th>
			<th class="td1" width="26">录入</th>
			<th class="td1" width="26">修改</th>
			<th class="td1" width="26">查看</th>
			<th class="td1" width="26">必填</th>
			<th class="td1" width="26">投稿</th>
			<th class="td1" width="26">采集</th>
			<th class="td1" width="26">搜索</th>
			<th class="td1" width="26">列表</th>
			<th class="td1" width="26">内容</th>
		</tr>
	</table>
	<table cellspacing="1" cellpadding="4" bgcolor="#ffffff" class="tableborder" border="0" style="width:100%;border-top:none"><tr>
		<td class="td1"><input type="button" value="上移选定" onclick="if (!myTable['mytable'])return;myTable['mytable'].up(1);" class="btn" /> 
		<input type="button" value="下移选定" class="btn" onclick="if (!myTable['mytable'])return;myTable['mytable'].down();" />
		<input type="button" value="按字段名排序" onclick="myTable['mytable'].sort(1);" class="btnl" /> 
		</td>
	</tr></table>
</div>

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
			'<div style="padding-right:8px"><!--[if lte IE 6]><div style="position:absolute;"><div style="position:absolute;top:-4px;"><div style="padding:0;"><![endif]--><input type="text" name="field['+item+'][dbname]" class="input" size="18" value="'+(modelArray[item]['dbname']||item)+'" maxlength="50" style="width:100%;" /><!--[if lte IE 6]></div></div></div>&nbsp;<![endif]--></div>',
			'<div style="padding-right:8px"><!--[if lte IE 6]><div style="position:absolute;"><div style="position:absolute;top:-4px;"><div style="padding:0"><![endif]--><input type="text" name="field['+item+'][comment]" class="input" size="20" value="'+(modelArray[item]['comment']||'')+'" maxlength="50" style="width:100%;" /><!--[if lte IE 6]></div></div></div>&nbsp;<![endif]--></div>',
			//'<center><select size="1"><option value="input">文本框</option><option value="select">下拉框</option><option value="textarea">文本区</option><option value="htmlarea">HTML编辑区</option><option value="radio">单选框</option><option value="checkbox">复选框</option><option value="dateinput">日期录入</option>><option value="timeinput">时间录入</option>><option value="imgupload">图片上传</option><option value="fileupload">文件上传</option></select></center>',
			'<input type="text" name="field['+item+'][tag]" class="input" size="10" value="'+(modelArray[item]['tag']||'')+'" maxlength="50" style="width:50px;" />',
			'<center><input name="field['+item+'][input]" type="checkbox" value="1"'+(modelArray[item]['input']?' checked="checked"':'')+' /></center>',
			'<center><input name="field['+item+'][editor]" type="checkbox" value="1"'+(modelArray[item]['editor']?' checked="checked"':'')+' /></center>',
			'<center><input name="field['+item+'][view]" type="checkbox" value="1"'+(modelArray[item]['view']?' checked="checked"':'')+' /></center>',
			'<center><input name="field['+item+'][notnull]" type="checkbox" value="1"'+(modelArray[item]['notnull']?' checked="checked"':'')+' /></center>',
			'<center><input name="field['+item+'][post]" type="checkbox" value="1"'+(modelArray[item]['post']?' checked="checked"':'')+' /></center>',
			'<center><input name="field['+item+'][caiji]" type="checkbox" value="1"'+(modelArray[item]['caiji']?' checked="checked"':'')+' /></center>',
			'<center><input name="field['+item+'][search]" type="checkbox" value="1"'+(modelArray[item]['search']?' checked="checked"':'')+' /></center>',
			'<center><input name="field['+item+'][list]" type="checkbox" value="1"'+(modelArray[item]['list']?' checked="checked"':'')+' /></center>',
			'<center><input name="field['+item+'][content]" type="checkbox" value="1"'+(modelArray[item]['content']?' checked="checked"':'')+' /></center>'
			);
			i++;
		}
		myTable['mytable']=new CreateTable('myEditorTable',tableInfo);
	}
	showEditorTable();
</script>

<div id="mymodelmain_2" style="display:none;">
	<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="width:100%;" id="editorTable_listmenu">
		<tr>
			<th class="td1" width="28">排序</th>
			<th class="td1" width="160">菜单名称</th>
			<th class="td1" title="例如：<br/>model/index/add/&#38;&#35;123;&#38;&#35;123;id&#38;&#35;125;&#38;&#35;125;<br/>model/index/add/&#38;&#35;123;&#38;&#35;123;title&#38;&#35;125;&#38;&#35;125;.html	<br/>其中，<font color=red>&#38;&#35;123;&#38;&#35;123;id&#38;&#35;125;&#38;&#35;125;</font>对应于当前信息id字段。">链接地址</th>
			<th class="td1" width="116">菜单样式</th>
			<th class="td1" width="70">打开方式</th>
			<th class="td1" width="40">启用</th>
		</tr>
	</table>
	
	<table cellspacing="1" cellpadding="4" bgcolor="#ffffff" class="tableborder" border="0" style="width:100%;border-top:none">
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
			navKey++;
		}
		for(var key in sysmenu){
			if (sysmenu[key]){
				if (key=='sys_edit'||key=='sys_del'){
					sysmenu[key]['isuse']=1;
				}
				tableInfo[i] = get_tr_array(key,sysmenu[key]);
				i++;
				navKey++;
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
			'<div style="padding-right:8px;"><!--[if lte IE 6]><div style="position:absolute;"><div style="position:absolute;top:-4px;"><div style="padding:0;"><![endif]--><input type="text" name="db[adminlistmenu]['+key+'][name]" class="input" size="18" value="'+(sysmenu['name']||'')+'" maxlength="20" style="width:94%;"/><!--[if lte IE 6]></div></div></div>&nbsp;<![endif]--></div>',
			key.substring(0,1) == '_'?
				'<!--[if lte IE 6]><div style="position:absolute;"><div style="position:absolute;top:-4px;"><div style="padding:0 0 0 4px;"><![endif]--><input type="text" name="db[adminlistmenu]['+key+'][address]" class="input" size="20" value="'+(sysmenu['address']||'')+'" maxlength="200" style="width:94%;"/><!--[if lte IE 6]></div></div></div>&nbsp;<![endif]-->'
			:
				'<font color="#cccccc">系统路径不需要修改</font>',

			'<center><select name="db[adminlistmenu]['+key+'][class]" style="width:110px;"><option value="btnss"'+(!sysmenu['class']||sysmenu['class']=='btnss'?' selected="selected"':'')+'>默认btnss(1-2字)</option><option value="btns"'+(sysmenu['class']=='btns'?' selected="selected"':'')+'>btns(2-3字)</option><option value="btn2"'+(sysmenu['class']=='btn2'?' selected="selected"':'')+'>btn2(2-3字)</option><option value="btn"'+(sysmenu['class']=='btn'?' selected="selected"':'')+'>btn(4-5字)</option><option value="btnl"'+(sysmenu['class']=='btnl'?' selected="selected"':'')+'>btnl(5-6字)</option><option value="bbtn"'+(sysmenu['class']=='bbtn'?' selected="selected"':'')+'>bbtn(4字大按钮)</option></select></center>',

			key == 'sys_del' ?
				'<center>删除确认</center>'
			:
				'<center><select name="db[adminlistmenu]['+key+'][target]" style="width:61px;" id="db[adminlistmenu]['+key+'][target]" onchange="if(this.value==\'[other]\'||this.value==\'[other2]\'){this.style.display=\'none\';$(\'db[adminlistmenu]['+key+'][target2]\').style.display=\'block\';$(\'db[adminlistmenu]['+key+'][target2]\').focus()}" ><option value=""'+(sysmenu['target']==''?' selected="selected"':'')+'>默认</option><option value="_self" '+(sysmenu['target']=='_self'?' selected="selected"':'')+' >相同窗口</option><option value="_blank" '+(sysmenu['target']=='_blank'?' selected="selected"':'')+' >新窗口</option><option value="_parent" '+(sysmenu['target']=='_parent'?' selected="selected"':'')+' >父窗口</option><option value="[other]" '+(mytarget?' selected="selected"':'')+' >其它</option><option value="[other2]">自定义</option></select><input type="text" name="db[adminlistmenu]['+key+'][target2]" value="'+(mytarget?mytarget:'')+'" id="db[adminlistmenu]['+key+'][target2]" class="input" size="8" style="display:none;" onblur="$(\'db[adminlistmenu]['+key+'][target]\').value=this.value==\'\'?\'\':\'[other]\';this.style.display=\'none\';$(\'db[adminlistmenu]['+key+'][target]\').style.display=\'\';" /></center>',

			key== 'sys_commend'||key== 'sys_edit'||key== 'sys_del'||key== 'sys_view' ?
				'<center><input id="db[adminlistmenu]['+key+'][isuse]" name="db[adminlistmenu]['+key+'][isuse]" type="checkbox" value="1"'+(sysmenu['isuse']==1?' checked="checked"':'')+' /></center>'
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

<!--
	<table border="0" cellpadding="2" cellspacing="1" style="width:100%;" class="tableborder">
		<tr>
			<th colspan="2">后台管理列表设置</th>
		</tr>
		<tr>
			<td class="td1" align="right" width="117">操作菜单宽度：</td>
			<td class="td2">
				<?php echo form::input('db[adminlistmenu][width]',!isset($adminlistmenu['width'])||empty($adminlistmenu['width'])?0:$adminlistmenu['width'],'class="input" size="5"');?> 0为默认，可以数字也可以是百分比宽度，例如：8%
			</td>
		</tr>
		<tr>
			<td class="td1" align="right">查看菜单：</td>
			<td class="td2">
				<?php echo form::checkbox('db[adminlistmenu][sys_view]',1,$adminlistmenu['sys_view']?true:false);?>显示到列表
			</td>
		</tr>
		<tr>
			<td class="td1" align="right">评论菜单：</td>
			<td class="td2">
				<?php echo form::checkbox('db[adminlistmenu][sys_commend]',1,!isset($adminlistmenu['sys_commend']) || $adminlistmenu['sys_commend']?true:false);?>显示到列表
			</td>
		</tr>
		<tr>
			<td class="td1" align="right">修改菜单：</td>
			<td class="td2">
				<?php echo form::checkbox('db[adminlistmenu][sys_edit]',1,!isset($adminlistmenu['sys_edit']) || $adminlistmenu['sys_edit']?true:false);?>显示到列表
			</td>
		</tr>
		<tr>
			<td class="td1" align="right">删除菜单：</td>
			<td class="td2">
				<?php echo form::checkbox('db[adminlistmenu][sys_del]',1,!isset($adminlistmenu['sys_edit']) || $adminlistmenu['sys_del']?true:false);?>显示到列表
			</td>
		</tr>
		<tr>
			<td class="td1" align="right">自定义菜单：</td>
			<td class="td2">
				<?php echo form::textarea('db[adminlistmenu][user_menu]',$adminlistmenu['user_menu'],'class="input" cols="40" style="width:420px;" rows="6"');?> 
				<span class="helpicon" title="请输入HTML。<br/>例如：<br/>&amp;lt;a href=&amp;quot;model/index/add/&#38;&#35;123;&#38;&#35;123;id&#38;&#35;125;&#38;&#35;125;.html&amp;quot;&amp;gt;新测试&amp;lt;/a&amp;gt;<br/>&amp;lt;a href=&amp;quot;model/index/add/&#38;&#35;123;&#38;&#35;123;title&#38;&#35;125;&#38;&#35;125;.html&amp;quot; target=&amp;quot;hiddenFrame&amp;quot;&amp;gt;测试2&amp;lt;/a&amp;gt;<br/>其中，<font color=red>&#38;&#35;123;&#38;&#35;123;id&#38;&#35;125;&#38;&#35;125;</font>对应于当前信息id字段。">&nbsp;</span>
			</td>
		</tr>
	</table>
-->
</div>

<div id="mymodelmain_3" style="display:none;">
	<table border="0" cellpadding="2" cellspacing="1" style="width:100%;" class="tableborder">
		<tr>
			<th colspan="2">数据操作接口设置</th>
		</tr>
		<tr>
			<td class="td1" align="right" width="117">新增数据后调用：</td>
			<td class="td2">
				<?php echo form::dropdown('db[adminedit][add]',$doinfomodel,$adminedit['add']);?> 
				函数位于admin/api/MY_Info_Api.php的Info_Api类
			</td>
		</tr>
		<tr>
			<td class="td1" align="right">修改数据后调用：</td>
			<td class="td2">
				<?php echo form::dropdown('db[adminedit][edit]',$doinfomodel,$adminedit['edit']);?>
				同上
			</td>
		</tr>
		<tr>
			<td class="td1" align="right">删除数据后调用：</td>
			<td class="td2">
				<?php echo form::dropdown('db[adminedit][del]',$doinfomodel,$adminedit['del']);?>
				同上
			</td>
		</tr>
	</table>
</div>

</td>
</tr>
</table>
</div>

<div id="mytagmain_3" style="display:none;">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
<tr>
	<th colspan="2">扩展数据表</th>
</tr>
<tr>
	<td class="td1" align="right" width="120">设置扩展数据表：</td>
	<td class="td2">
	<table border="0" cellpadding="4" cellspacing="1" class="tableborder" id="myRelationTable" style="width:100%;">
		<tr>
			<th class="td1" width="12">&nbsp;</th>
			<th class="td1">名称</th>
			<th class="td1">待对应字段</th>
			<th class="td1">扩展表名称</th>
			<th class="td1">关联字段</th>
			<th class="td1" width="120">操作</th>
		</tr>
	</table>
	<div class="clear"></div>
	<table cellspacing="1" cellpadding="4" bgcolor="#ffffff" class="tableborder" border="0" style="width:100%;border-top:none"><tr>
		<td class="td1">
			<div style="float:right"><input type="button" value="添加新扩展表" class="btnl" onclick="relationfield_edit();" /></div>
			<input type="button" value="上移选定" onclick="if (!myTable['myrelation'])return;myTable['myrelation'].up(1);" class="btn" /> 
			<input type="button" value="下移选定" class="btn" onclick="if (!myTable['myrelation'])return;myTable['myrelation'].down();" />
			
		</td>
	</tr></table>
	</td>
</tr>
</table>
<script type="text/javascript">

var relationfieldNum = 0;
function relationfield_edit(rowIndex){
	var fieldlist = <?php echo Tools::json_encode($fieldlist);?>;
	var fieldlist_html ='';
	for(var i in fieldlist){
		fieldlist_html += '<option value="'+i+'">'+(fieldlist[i]||'')+'</option>';
	}
	var tablelist = <?php echo Tools::json_encode($dblist);?>;
	var tablelist_html ='';
	for(var i in tablelist){
		tablelist_html += '<option value="'+i+'">'+(tablelist[i]||'')+'('+i+')</option>';
	}
	var title = (rowIndex?'修改':'添加新')+'扩展数据表';
	var tmphtml = '<input type="hidden" id="relationfield_id" value="'+(rowIndex>0?rowIndex:0)+'" /><div style="padding:10px" id="myrelationfieldDiv"><ul class="ul tag"><li class="now" id="relationfield_1" onclick="tag(this.id,\'relationfield\',\'relationfieldmain\',\'myrelationfieldDiv\');">基本设置</li><li id="relationfield_2" onclick="tag(this.id,\'relationfield\',\'relationfieldmain\',\'myrelationfieldDiv\')">高级设置</li><li id="relationfield_3" onclick="tag(this.id,\'relationfield\',\'relationfieldmain\',\'myrelationfieldDiv\');">附加设置</li></ul><div style="clear:both"></div><table cellspacing="1" cellpadding="2" class="tableborder" border="0" align="center" style="width:100%;"><tr><th class="td1" colspan="2">'+title+'</th></tr><tr><td class="td1" width="120" align="right">名称：</td><td class="td2"><input type="text" class="input" size="20" maxlength="40" id="relationfield_title" /> 例如：我的新扩展表</td></tr><tr><td class="td1" align="right">待对应字段：</td><td class="td2"><select id="relationfield_field">'+fieldlist_html+'</select> 通常是本表的ID</td></tr><tr><td class="td1" align="right">扩展数据表：</td><td class="td2"><select id="relationfield_dbtable" onchange="relationfield_getfield(this.value)">'+tablelist_html+'</select></td></tr><tr><td class="td1" align="right">扩展表录入字段：</td><td class="td2"><span id="relationfield_dbfieldDiv"></span></td></tr> <tr> <td align="right" class="td1">扩展表显示字段：</td><td class="td2"><div id="relationfield_dbfieldshowDiv"><select id="relationfield_dbfieldshow"></select></div></td></tr><tr><td class="td1" align="right">对应关系：</td><td class="td2"><select id="relationfield_relation"><option value="1:1">一对一</option><option value="n:1">多对一</option><option value="n:n">多对多</option></select><span class=helpicon title="一对一：主表和扩展表会同时录入<br>多对一：扩展表的一个字段会以下拉列表的方式出现，供主表选择，只能选择一个<br>多对多：扩展表的一个字段会以下拉列表的方式出现，可以选择多个">&nbsp;</span></td></tr><tr><td class="td1" align="right">说明备注：</td><td class="td2"><textarea class="input" cols="30" rows="4" id="relationfield_content" style="width:350px;height:50px;"></textarea></td></tr><tr><td class="td1" class="nbsp"></td><td class="td2"><input type="button" class="btn" value="确定修改" onclick="relationfield_editset()" /><input type="button" class="btns" value="关闭" onclick="parentFrame.ymPrompt.close();" /> 这里的“确定修改”只是临时保存</td></tr></table></div>';
	window.win(tmphtml,560,390,title);

	if (rowIndex>0){
		var row = $('myRelationTable').rows[rowIndex];
		if (!row)return;
		var inputs = row.cells[1].getElementsByTagName('input');
		
		$('relationfield_title').value=inputs[0].value;
		$('relationfield_field').value=inputs[1].value;
		$('relationfield_dbtable').value=inputs[2].value;
		$('relationfield_relation').value=inputs[4].value;
		$('relationfield_content').value=inputs[5].value;

		relationfield_getfield(inputs[2].value,inputs[3].value,inputs[6].value);
		
		//_alert(2);
		if (inputs[4].value == '1:1') {
			$('relationfield_dbfieldshow').disabled = true;
		}
		//dbfieldshow_change();
	}else{
		$('relationfield_dbtable').onchange();
	}

	//<tr><td class="td1" align="right">编辑模式：</td><td class="td2"><select><option value="0">同时编辑</option><option value="1">单独编辑</option></select> </td></tr><tr><td class="td1" align="right">排列方式：</td><td class="td2"><select><option value="0">切换式</option><option value="1">竖排式</option><option value="2">展开式</option></select></td></tr><tr><td class="td1" align="right">是否允许添加：</td><td class="td2"><select><option value="0">是</option><option value="1">否</option></select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;是否允许删除：<select><option value="0">是</option><option value="1">否</option></select></td></tr><tr><td class="td1" align="right">排序字段：</td><td class="td2"><select></select></td></tr>
}
//function dbfieldshow_change () {
//	if ($('relationfield_relation').value == 'n:1') {
//		$('relationfield_dbfieldshow').disabled = false;
//	}else{
//		$('relationfield_dbfieldshow').disabled = true;
//	}
//}
function relationfield_editset(){
	var obj_id=$('relationfield_id');
	var obj_title=$('relationfield_title');
	var obj_field=$('relationfield_field');
	var obj_dbtable=$('relationfield_dbtable');
	var obj_dbfield=$('relationfield_dbfield');
	var obj_content=$('relationfield_content');
	var obj_relation=$('relationfield_relation');
	var obj_dbfieldshow=$('relationfield_dbfieldshow');
	var obj_table = $('myRelationTable');
	if (!obj_table||!obj_id||!obj_title||!obj_field||!obj_dbtable||!obj_dbfield||!obj_content){
		return;
	}
	if (obj_title.value==''){
		_alert('请输入扩展名称！');
		return;
	}
	var rows;
	var trset = {'title':obj_title.value,'field':obj_field.value,'dbtable':obj_dbtable.value,'dbfield':obj_dbfield.value,'content':obj_content.value,'relation':obj_relation.value,'dbfieldshow':obj_dbfieldshow.value};
	if (obj_id.value>0){
		//修改
		rows=obj_table.rows[obj_id.value];
		var rowtr = relationfield_trarr(trset);
		for (var i=1;i<rows.cells.length;i++){
			rows.cells[i].innerHTML=rowtr[i-1]||'';
		}
	}else{
		rows = relationfield_set([trset]);
	}

	parentFrame.ymPrompt.close();
}

function relationfield_set(fieldset){
	fieldset = fieldset || [{}];
	var tableInfo= new Array();
	for(var i=0;i<fieldset.length;i++){
		tableInfo[i] = relationfield_trarr(fieldset[i]);
	}
	var tr;
	if (!myTable['myrelation']){
		myTable['myrelation'] = new CreateTable('myRelationTable',tableInfo);
	}else{
		tr = myTable['myrelation'].addrows(fieldset.length,5,tableInfo);
	}
	myqee($('myRelationTable'));
	return tr;
}

function relationfield_trarr(trset){
	
	relationfieldNum++;
	return new Array(
		'<input type="hidden" name="db[relationfield]['+relationfieldNum+'][title]" value="'+forformatstr(trset['title'])+'" />'+
		'<input type="hidden" name="db[relationfield]['+relationfieldNum+'][field]" value="'+forformatstr(trset['field'])+'" />'+
		'<input type="hidden" name="db[relationfield]['+relationfieldNum+'][dbtable]" value="'+forformatstr(trset['dbtable'])+'" />'+
		'<input type="hidden" name="db[relationfield]['+relationfieldNum+'][dbfield]" value="'+forformatstr(trset['dbfield'])+'" />'+
		'<input type="hidden" name="db[relationfield]['+relationfieldNum+'][relation]" value="'+forformatstr(trset['relation'])+'" />'+
		'<input type="hidden" name="db[relationfield]['+relationfieldNum+'][content]" value="'+forformatstr(trset['content'])+'" />'+
		'<input type="hidden" name="db[relationfield]['+relationfieldNum+'][dbfieldshow]" value="'+forformatstr(trset['dbfieldshow'])+'" />'+
			'<center>'+(trset['title']||'<div class="nbsp"></div>')+'</center>',
		'<center>'+(trset['field']||'<div class="nbsp"></div>')+'</center>',
		'<center>'+(trset['dbtable']||'<div class="nbsp"></div>')+'</center>',
		'<center>'+(trset['dbfield']||'<div class="nbsp"></div>')+'</center>',
		'<center><input type="button" value="修改" class="btns" onclick="relationfield_edit(parentNode.parentNode.parentNode.rowIndex)" /><input type="button" value="删除" class="btns" onclick="confirm({\'title\':\'确实要删除？\',\'message\':\'确认要删除此扩展表？\',\'handler\':function(e){if (e==\'ok\'){var tr=parentNode.parentNode.parentNode;tr.parentNode.removeChild(tr);}}});" /></center>'
	);
}

function forformatstr(str){
	str = str||'';
	str += '';
	str.replace(/"/g,'\\"');
	return str;
}

var relationfield_dbField = {};

function relationfield_getfield(dbname,dbfield,dbfieldshow){
	if (!dbname){
		relationfield_renewfield();
		return;
	}
	if (relationfield_dbField[dbname]){
		relationfield_renewfield(dbname,dbfield,dbfieldshow);
		return;
	}
	ajax.requestFile = ("<?php echo Myqee::url('model/get_dbfield/{{dbname}}');?>").replace(/\{\{dbname\}\}/,dbname);
	ajax.method = 'GET';
	ajax.onCompletion = function (){
		var arr;
		var iserror=false;
		try{
			arr = eval('('+this.response+')');
			if (typeof(arr)!='object'){
				iserror = true;
			}
		}catch(e){
			iserror = true;
		};
		if (iserror==true){
			this.onError();
		}else{
			relationfield_dbField[dbname] = arr;
			relationfield_renewfield(dbname,dbfield,dbfieldshow);
		}
	}
	ajax.onError = function(){
		alert('读取列表失败,可能已登录超时，请重试！',400);
		var obj=$('relationfield_dbfieldDiv');
		if (obj){
			obj.innerHTML ='<input type="button" value="重试" class="btns" onclick="relationfield_getfield(\''+dbname+'\')" />';
			myqee(obj);
		}
	}
	ajax.runAJAX();

}
function relationfield_renewfield(dbname,dbfield,dbfieldshow){
	obj = $('relationfield_dbfieldDiv');
	_obj = $('relationfield_dbfieldshowDiv');
	if (!obj)return;
	var tmphtml = '<select id="relationfield_dbfield">';
	var _tmphtml = '<select id="relationfield_dbfieldshow">';
	var j = 0;
	if (typeof(relationfield_dbField[dbname])=='object'){
		for (var i in relationfield_dbField[dbname]){
			tmphtml+='<option value="'+i+'"'+(dbfield==i?' selected="selected"':'')+'>'+relationfield_dbField[dbname][i]+'</option>';
			_tmphtml+='<option value="'+i+'"'+(dbfieldshow==i?' selected="selected"':'')+'>'+relationfield_dbField[dbname][i]+'</option>';
			//if (i == dbfieldshow) {
//				_tmphtml += '<option value="'+i+'" selected="selected">'+relationfield_dbField[dbname][i]+"</option>";
//			} else {
//				_tmphtml += '<option value="'+i+'">'+relationfield_dbField[dbname][i]+"</option>";
//			}
			j++;
		}
	}
	tmphtml+='</select>';
	_tmphtml+='</select>';
	obj.innerHTML = tmphtml;
	_obj.innerHTML = _tmphtml;
}
</script>
</div>


<div id="mytagmain_4" style="display:none;">
	<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
		<tr>
			<th colspan="2">数据字表：</th>
		</tr>
		<tr>
			<td class="td1" align="right" width="120">设置子数据表：</td>
			<td class="td2">
				
			</td>
		</tr>
	</table>
</div>


<script type="text/javascript">
if (document.location.hash =='#tag1'||document.location.hash =='#tag2'||document.location.hash =='#tag3'||document.location.hash =='#tag4'){
	tag('mytag_'+document.location.hash.substr(4),'mytag','mytagmain');
}
relationfield_set(<?php echo $relationfield;?>);
</script>

<table border="0" cellpadding="4" cellspacing="1" style="border-top:none" align="center" class="tableborder">
<tr>
	<td class="td1" width="120" align="right">&nbsp;</td>
	<td class="td1"><input type="submit" value="保存设置" class="bbtn" /> <input onclick="goback(-1,'<?php echo Myqee::url('model/dblist'); ?>')" type="button" value="返回" class="btns" /> 
	<font color="#ff3300">以上的任何修改都必须点此保存设置才会被保存</font></td>
</tr>
</table>
</form>

<?php View::factory('admin/footer') -> render(TRUE);?>