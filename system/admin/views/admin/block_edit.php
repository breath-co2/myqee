<?php
if ($block['id']>0){
	$page_title = '碎片设置';
}else{
	$page_title = '新增碎片';
}
$page_index = 'info';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; 
<a href="<?php echo Myqee::url('info/index');?>">内容管理</a> -&gt; 
<a href="<?php echo Myqee::url('block/index');?>">碎片管理</a> -&gt; 
<a href="<?php echo Myqee::url('block/mylist');?>">碎片设置</a> -&gt; 
<?php echo $page_title;?>
</div>
<div style="clear: both"></div>
<script type="text/javascript">
function edit_block(id){
	win({
		title:'碎片信息编辑',
		width:750,
		height:420,
		minBtn:true,
		maxBtn:true,
		showMask:false,
		dragOut:true,
		iframe:{
			src:'<?php echo Myqee::url('block/view_edit_frame')?>?id='+id,
			width:'100%',
			height:'100%'
		}
		}
	);
}
</script>

<div class="mainTable">
<ul class="ul tag" id="mytag">
	<li class="now" id="mytag_1" onclick="tag(this.id,'mytag','mytagmain','mytag');document.location.hash='#tag1'">基本属性</li>
	<li id="mytag_2" onclick="tag(this.id,'mytag','mytagmain','mytag');document.location.hash='#tag2'">碎片模板</li>
	<?php
	if (!$block['id'] || ($block['id']>0&&$block['show_type']>0)){
		$showextfield = true;
		echo '<li id="mytag_3" onclick="tag(this.id,\'mytag\',\'mytagmain\',\'mytag\');document.location.hash=\'#tag3\'">扩展字段</li>';
	}else{
		$showextfield = false;
	}
	?>
	
</ul>
</div>
<form action="<?php echo Myqee::url('block/save/'.$block['id']);?>" method="post" target="hiddenFrame">
<div id="mytagmain_1">
<table border="0" cellpadding="2" cellspacing="1" align="center" class="tableborder">
<tr>
	<th class="td1" colspan="2"><?php echo $page_title;?></th>
</tr>
<tr>
	<td class="td1" align="right" width="140">碎片名称(<font color="red">*</font>)：</td>
	<td class="td2">
	<?php echo form::input('block[title]',$block['title'],'class="input" size="30"');?>
	</td>
</tr>
<tr>
	<td class="td1" align="right">显示类型(<font color="red">*</font>)：</td>
	<td class="td2">
	<?php echo form::dropdown('block[show_type]',array('HTML编辑器录入','通用链接格式化碎片','自定义字段碎片'),$block['show_type'],'id="block[show_type]" onchange="if(this.value>0){$(\'mytag_3\').style.display=\'\';}else{$(\'mytag_3\').style.display=\'none\';}"'.($block['id']>0?'disabled="disabled" class="btn_disabled"':null));?> 
	<span class="helpicon" title="“通用链接格式化碎片”和“自定义字段碎片”具备<b>扩展字段功能</b><br/>“HTML编辑器录入”不具备">&nbsp;</span>
	<font color="#ff6600">添加碎片时可选，</font><font color="#ff0000"><b>以后不可修改！</b></font>
	<?php 
	if ($showextfield){
		echo '<script>$("block[show_type]").onchange();</script>';
	}
	?>
	</td>
</tr>
<tr>
	<td class="td1" align="right">所属区块(<font color="red">*</font>)：</td>
	<td class="td2">
	<?php
	echo form::changeinput('block[type]',$block['type'],'class="input" size="25"',$type);
	?> 
	<span class="helpicon" title="您可以自定义所属区块，只允许数字字母及下划线。<br/>例如：index，class_list_12等等">&nbsp;</span>
	<font color="#ff6600">所属区块和调用ID请勿随意修改</font>
	</td>
</tr>
<tr>
	<td class="td1" align="right">调用ID(<font color="red">*</font>)：</td>
	<td class="td2">
	<?php echo form::input('block[no]',$block['no'],'class="input" size="6"');?> 
	<span class="helpicon" title="所属区块和调用ID请勿随意修改，否则可能引起已有模板调用无法访问">&nbsp;</span>
	<font color="#999999">同一个区块不允许有重复调用ID，留空则系统自动添加。</font>
	</td>
</tr>
<tr>
	<td class="td1" align="right">是否启用：</td>
	<td class="td2">
	<?php 
	echo form::dropdown('block[isuse]',array('1'=>'是','0'=>'否'),!isset($block['isuse'])?1:$block['isuse']);
	?>
	</td>
</tr>
<tr>
	<td class="td1" align="right">内容长度限制：</td>
	<td class="td2">
	<?php echo form::input('block[len]',$block['len'],'class="input" size="6"');?>  
	<font color="#999999">0表示不限制，若显示类型为“格式化碎片”则为碎片组数量，若为“HTML代码”则为HTML长度。</font>
	</td>
</tr>
<tr>
	<td class="td1" align="right">缓存时间：</td>
	<td class="td2">
	<?php echo form::input('block[cache_time]',$block['cache_time'],'class="input" size="6"');?>  
	<font color="#999999">0表示不缓存，单位秒。</font>
	</td>
</tr>
<tr>
	<td class="td1" align="right">排序：</td>
	<td class="td2">
	<?php echo form::input('block[myorder]',$block['myorder'],'class="input" size="6"');?>  
	<font color="#999999">越小越靠前</font>
	</td>
</tr>
<tr>
	<td class="td1" align="right">数据传入模板变量名称：</td>
	<td class="td2">
	<?php echo form::input('block[varname]',$block['varname'],'class="input" size="10"');?>  
	<span class="helpicon" title="例如传入变量名称为data，<br/>那么在模板里就可以直接用<font color=red>$data</font>引用到此碎片数据！">&nbsp;</span>
	<font color="#999999">默认为data，留空则使用默认，只允许数字字母下划线，且字母开头。</font>
	</td>
</tr>
<tr>
	<td class="td1" align="right">选择调用数据：</td>
	<td class="td2">
	<?php echo 
					//$fdatabase,$ftablename,$ffieldsave,$ffieldshow,$fieldname,$savevalue,$showvalue,$isappend
	form::pageselect('block[mydata_id]',$block['mydata_id'],'default/[mydata],id,name');
	//form::dropdown('block[mydata_id]',$block['mydata_id']);
	?> 
	<font color="#ff6600"></font>
	</td>
</tr>
</table>
</div>


<script src="<?php echo ADMIN_IMGPATH;?>/admin/tpleditor.js" type="text/javascript"></script>

<script type="text/javascript">
function checktabkey(ev,obj){
	e = ev||event;
	if (e.keyCode == 9) {
		//obj.setSelectionRange().text = '	';
		if (document.selection) {
			//IE
			document.selection.createRange().text = '	';
		}else if (obj.setSelectionRange && obj.selectionStart>=0) {
			//OTHER
			var myv = obj.value;
			var start = obj.selectionStart;
			var end = obj.selectionEnd;
			var scroltop = obj.scrollTop;
			var scrolleft = obj.scrollLeft;
			obj.value = myv.substr(0,start)+'	'+myv.substr(end);
			obj.setSelectionRange(start+1,start+1);
			obj.scrollTop = scroltop;
			obj.scrollLeft = scrolleft;
		}
		return false;
	}
}
var url_base = "<?php echo ADMIN_IMGPATH; ?>";
</script>
<div id="mytagmain_2" style="display: none;">
<table border="0" cellpadding="2" cellspacing="1" align="center" class="tableborder">
<tr>
	<th class="td1" colspan="2">碎片模板</th>
</tr>
<tr>
	<td class="td1" align="right" width="140">调用模板：</td>
	<td class="td2">
	<?php echo form::dropdown('block[tpl_id]',array_merge(array('0'=>'不使用任何模板'),$template),$block['tpl_id']);
	?> 
	<font color="#ff6600">只有选择“不使用任何模板”，下面的设置才有效！</font>
	</td>
</tr>
<tr>
	<td class="td1" align="right" width="140">模板引擎：</td>
	<td class="td2">
	<?php echo form::dropdown('block[tpl_engie]',array(''=>'系统默认引擎'),$block['tpl_engie']);
	?> 
	</td>
</tr>
<tr>
	<td class="td1" align="right">模板内容：</td>
	<td class="td2">
		<div style="padding:3px 3px 0 3px;font-size:0px;line-height:0px;" id="custompage[content]__div">
			<div style="font-size:12px;line-height:auto;" id="custompage[content]__TextDiv">
				<table border="0" cellpadding="0" cellspacing="1" width="100%">
				<tr><td width="35" id="textline_Div">
				<textarea id="textlineDiv" readonly="readonly" style="overflow:hidden;padding:6px 0;text-align:right;line-height:18px;height:400px;width:35px;border:none;background-color:#eee;color:#0000ff;font-family:Fixedsys,verdana,'宋体';cursor:default;" onfocus="this.blur();"></textarea>
				</td><td>
				<?php 
				echo form::textarea(array('name'=>'block[template]','class'=>'template_edit'),$block['template'],'id="block[template]" wrap="off" onscroll="scroll_len(this)" onkeydown="return checktabkey(event,this)" style="line-height:18px;width:99%;overflow:auto;scrolling:yes;float:left;height:400px;"');
				?>
				</td></tr>
				</table>
			</div>
		</div>
<script type="text/javascript">
var _line_i = 1;
function scroll_len(txt_main){
	var txt_ln = $('textlineDiv');
	txt_ln.scrollTop = txt_main.scrollTop;
	while(_line_i<30 || txt_ln.scrollTop != txt_main.scrollTop) 
	{
		txt_ln.value += (_line_i++) + '\n';
		txt_ln.scrollTop = txt_main.scrollTop;
	}
	return;
}
scroll_len($('block[template]'));


</script>
	<div style="clear:both;width:100%;height:6px;overflow:hidden;"></div>
	<div><input type="button" value="切换到文本状态" class="btnl" onclick="changeEditor('block[template]','textarea')" /> 
	<input type="button" value="切换到FCKEditor" class="btnl" onclick="changeEditor('block[template]','fckeditor')" title="切换到可视化编辑状态编辑会更方便<br/>但有可能会造成部分代码发生结构变化！" /> 
	<font color="#ff3300">安全起见，碎片中模板不支持PHP代码</font>
	</div>
	</td>
</tr>
</table>
</div>


<?php 
	if ($showextfield){
?>
<script type="text/javascript" src="<?php echo ADMIN_IMGPATH;?>/admin/field_edit.js"></script>

<div id="mytagmain_3" style="display: none;">
	<div id="advFieldDiv">
		<div id="advFieldGroup_0">
		<table id="myEditorTable_00" border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="display:none;">
			<tr><th></th></tr>
			<tr>
			<td></td>
			<td>
			<input type="hidden" value="0" isgroup="true"/>
			<input type="hidden" />
			<input type="hidden" />
			<input type="hidden" value="" flag="[_g][editwidth]" name="field[adv][_g][editwidth]"/>
			<input type="hidden" value="input" flag="[_g][type]" name="field[adv][_g][type]"/>
			<input type="hidden" value="0" flag="[_g][num]" name="field[adv][_g][num]"/>
			<input type="hidden" value="1" flag="[_g][isadd]" name="field[adv][_g][isadd]"/>
			<input type="hidden" value="1" flag="[_g][isdel]" name="field[adv][_g][isdel]"/>
			<input type="hidden" value="1" flag="[_g][isorder]" name="field[adv][_g][isorder]"/>
			</td>
			<td></td>
			</tr>
		</table>
		<table id="myEditorTable_0" border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
			<tr>
			<th class="td1" width="30">选择</th>
			<th class="td1">名称</th>
			<th class="td1" width="200">类型</th>
			<th class="td1" width="220">操作</th>
			</tr>
		</table>
		<input type="hidden" id="parentGroupId_0" value="0" />
		</div>
	</div>

	<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-top:none;">
		<tr>
		<td class="td1"><input type="button" value="上移选定" onclick="try{myTable['mytable_'+nowGroupId].up(1);}catch(e){}" class="btn" /> 
<input type="button" value="下移选定" class="btn" onclick="try{myTable['mytable_'+nowGroupId].down();}catch(e){}" />
		</td>
		<td class="td1" align="center" width="160"><input type="button" class="btn" value="本组设置" onclick="edit_field_selfgroup()" /><input type="button" class="btn" value="大组设置" onclick="edit_field_group('1','0','0')" /></td>
		<td class="td1" align="center" width="220">
		<input type="button" class="btns" value="上一级" disabled="disabled" id="showParentBtn" onclick="showParent()" />
		<input type="button" class="btn" value="添加字段" onclick="edit_field(null,nowGroupId)" />
		<input type="button" class="btn" value="添加新组" onclick="edit_field_group(null,nowGroupId)" />
		</td>
		</tr>
	</table>

	<script type="text/javascript">
	_field_tableall = true;
	(function (){
		var myArray = <?php echo $field_adv;?>;
		myTable['mytable_0'] = new CreateTable('myEditorTable_0',[]);
		if (typeof(myArray['_g']) =='object'){
			var inputs = $('myEditorTable_00').rows[1].cells[1].getElementsByTagName('INPUT');
			inputs[3].value = myArray['_g']['editwidth']||'';
			inputs[4].value = myArray['_g']['type']||'0';
			inputs[5].value = myArray['_g']['num']||'0';
			inputs[6].value = myArray['_g']['isadd']||'1';
			inputs[7].value = myArray['_g']['isdel']||'1';
			inputs[8].value = myArray['_g']['isorder']||'1';
		}
		ini_field(myArray);
		nowGroupId = '0';
	})();
	</script>
</div>
<?php 
	}
?>

<table border="0" cellpadding="2" cellspacing="1" align="center" class="tableborder" style="border-top:none;">
<tr>
	<td class="td1" width="140">&nbsp;</td>
	<td class="td1">
	<input type="submit" class="bbtn" value="保存碎片" /> 
	<?php 
	if($block['id']>0)echo '<input type="button" class="btnl" value="修改碎片信息" onclick="edit_block(\'', $block['id'], '\');" />';
	?>
	
	<input type="button" class="btns" value="返回" onclick="goback(-1,'<?php echo Myqee::url('block/mylist');?>')" />
	</td>
</tr>
</table>
</form>

<script type="text/javascript">
if (document.location.hash =='#tag1'||document.location.hash =='#tag2'||(document.location.hash =='#tag3'&&$('mytagmain_3'))){
	tag('mytag_'+document.location.hash.substr(4),'mytag','mytagmain');
}
</script>
<?php 
View::factory('admin/footer') -> render(TRUE);
?>