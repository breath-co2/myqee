<?php
$page_index = 'model';
if ($field['name']){
	$page_title = '修改字段:'.$field['name'];
}else{
	$page_title = '添加字段';
}
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>

<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('model/index');?>">模型管理</a> -&gt; <a href="<?php echo Myqee::url('model/dblist');?>">数据表管理</a> -&gt; <a href="<?php echo Myqee::url('model/dbfield/'.$dbid);?>">字段管理</a> -&gt; <?php echo $page_title;?>
</div>

<script type="text/javascript" src="<?php echo ADMIN_IMGPATH;?>/admin/field_edit.js"></script>
<form method="post" name="myforms" action="<?php echo Myqee::url('model/dbfieldsave/'.$dbid.'/'.$field['name']);?>" target="hiddenFrame">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
<tr>
	<th colspan="2"><?php echo $page_title; ?></th>
</tr>
<tr>
	<td class="td1" align="right" width="140">字段名：</td>
	<td class="td2"><input name="field[name]" type="text" size="20" class="input" value="<?php echo $field['name'];?>" /> &nbsp;<font color="#a3a3a3">例如“title”</font></td>
</tr>
<tr>
	<td class="td1" align="right">字段标识：</td>
	<td class="td2"><input name="field[dbname]" type="text" size="30" class="input" value="<?php echo $field['dbname'];?>" /> &nbsp;<font color="#a3a3a3">例如“标题”</font></td>
</tr>
<tr>
	<td class="td1" align="right">预设字段关联：</td>
	<td class="td2"><?php echo form::dropdown('field[autoset]',$sysfieldselect,$field['autoset'],'id="field[autoset]" onchange="changeAutoSet(this.value);"');?> <font class="helpicon" title="关联字段的作用是：<br/>系统本身会预设一些字段，例如标题，作者，时间等<br/>这些字段在输入输出时都会经过特殊处理，<br/>当您将此字段到对应的预设字段后将也赋予了它相应的特殊属性！">&nbsp;</font>&nbsp;<font color="#a3a3a3">选择自定义，将不关联字段。</font>
<script type="text/javascript">
	var theDefaultValue = '<?php echo str_replace("\n",'\n',str_replace('\'','\\\'',str_replace('\\','\\\\',$field['default'])));?>';
	var theCandidateValue = '<?php echo str_replace("\n",'\n',str_replace('\'','\\\'',str_replace('\\','\\\\',$field['candidate'])));?>';
	var theField = <?php echo $sysfieldjson;?>;
</script>
</td>
</tr>
<tr><td class="td1" align="right">数据表字段设置：</td><td class="td2">

<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="width:98%;">
<tr>
	<td class="td1" width="120" align="right">字段属性：</td>
	<td class="td2">
	<?php echo form::checkbox('field[iskey]','1',$field['iskey']==true,'id="field[iskey]" title="对于查询中经常用到的条件字段，建议使用索引"');?>索引
	<?php echo form::checkbox('field[isonly]','1',$field['isonly'],'id="field[isonly]" title="通常只用于ID"');?>自动增量
	<?php echo form::checkbox('field[istofile]','1',$field['istofile'],'id="field[istofile]"');?>内容存文本
	<font class="helpicon" title="<b>索引</b>：能够加快数据表查询速度，通常用于ID等<br/><b>自动增量</b>：通常用于数据表的ID<br/><b>内容存文本</b>：数据将存放到外部文本中，通常用于正文较大的字段">&nbsp;</font>
	</td>
</tr>
<tr>
	<td class="td1" align="right">字段类型：</td>
	<td class="td2">
<?php
	$field['type'] or $field['type'] = 'varchar';
	echo form::dropdown(
		'field[type]',array(
		'varchar' => '字符型1-65535字节(VARCHAR)',
		'text' => '小型字符型(TEXT)',
		'mediumtext' => '中型字符型(MEDIUMTEXT)',
		'longtext' => '大型字符型(LONGTEXT)',
		'tinyint' => '小数值型,长度1-3(TINYINT)',
		'smallint' => '中数值型,长度1-6(SMALLINT)',
		'int' => '大数值型,长度1-11(INT)',
		'bigint' => '超大数值型(BIGINT)',
		'float' => '数值浮点型(FLOAT)',
		'double' => '数值双精度型(DOUBLE)',
		'decimal' => '一个串的浮点数(DECIMAL)',
		)
		,$field['type'],'id="field[type]" onchange="if (this.value.substr(this.value.length-4)!=\'text\'){$(\'fieldLenDiv\').style.visibility=\'visible\'}else{$(\'fieldLenDiv\').style.visibility=\'hidden\'}"'
	);
?>
	<span id="fieldLenDiv">&nbsp;&nbsp;字段长度：<?php echo form::input('field[length]',$field['length'],'id="field[length]" type="text" size="5" class="input"');?></span>
	<script type="text/javascript">$('field[type]').onchange();</script>
	</td>
</tr>
<tr><td class="td1" align="right">不允许空：</td><td class="td2"><?php echo form::checkbox('field[isnonull]','1',$field['isnull']!='YES','id="field[isnonull]"');?> <font color="#a3a3a3">字段是否允许留空，通常允许空</font></td></tr>

</table>
</td>
</tr>
<tr>
	<td class="td1" align="right">字段录入显示方式：</td>
	<td class="td2">
<?php
if ($field['usehtml']=='1' && !empty($field['html'])){
	$showtag_1 = $showtag_3 = '';
	$showtagstyle_1 = $showtagstyle_3 = 'style="display:none;"';
	$showtag_2 = ' class="now"';
	$showtagstyle_2 = '';
}elseif($field['usehtml']=='2'){
	$showtag_3 = ' class="now"';
	$showtagstyle_3 = '';
	$showtag_1 = $showtag_2 = '';
	$showtagstyle_1 = $showtagstyle_2 = 'style="display:none;"';
}else{
	$showtag_1 = ' class="now"';
	$showtagstyle_1 = '';
	$showtag_2 = $showtag_3 = '';
	$showtagstyle_2 = $showtagstyle_3 = 'style="display:none;"';
}
echo form::input(array('name'=>'field[usehtml]','id'=>'field[usehtml]','type'=>'hidden'),$field['usehtml']>0?$field['usehtml']:0);
?>
		<div style="width:98%;margin:auto;">
		<ul class="ul tag">
		<li<?php echo $showtag_1;?> id="mytag_1" onclick="tag(this.id,'mytag','mytagmain','leftDIV');$('field[usehtml]').value='0';">使用普通模式</li>
		<li<?php echo $showtag_2;?> id="mytag_2" onclick="tag(this.id,'mytag','mytagmain','leftDIV');$('field[usehtml]').value='1';">自定义HTML</li>
		<li<?php echo $showtag_3;?> id="mytag_3" onclick="tag(this.id,'mytag','mytagmain','leftDIV');$('field[usehtml]').value='2';" title="使用高级分组，请将字段类型选择为TEXT类型，否则容易导致错误。">高级分组录入</li>
		</ul>
		</div>
		<div class="clear"></div>
		
		<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="width:98%;">
			<tr><td class="td1" align="right">编辑页面左侧列宽度：</td><td class="td2"><?php echo form::input('field[editwidth]',$field['editwidth'],'id="field[editwidth]" class="input" size="10"');?> 
			<font color="#a3a3a3">留空为默认，初始默认值：100，0表示不显示</font></td></tr>
			<tr><td class="td1" width="120" align="right">对录入值进行处理：</td><td class="td2"><?php echo form::dropdown('field[format]',
			array(
				''=>'默认(纯文本,过滤所有HTML标记)',
				'br'=>'过滤除&lt;br&gt;外所有HTML',
				'string'=>'带简单html的字符串',
				'time'=>'时间,日期',
				'int'=>'数字',
				'safehtml'=>'保留常用标签的安全HTML',
				'html'=>'保留所有标签的HTML',
				'htmlspec'=>'转义HTML(htmlspecialchars)',
				'alt'=>'alt说明',
				'filepath'=>'文件路径',
				'filename'=>'文件名',
				'serialize'=>'serialize序列化',
				'json_encode'=>'json_encode序列化',
				'nodo'=>'不做任何处理',
			),$field['format'],'id="field[format]"');?></td></tr>
			</table>
		<div id="mytagmain_1"<?php echo $showtagstyle_1;?>>
		<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-top:none;width:98%;">
		<tr>
			<td class="td1" width="120" align="right">表单类型：</td><td class="td2"><?php echo form::dropdown(
		'field[inputtype]',array(
			'input' => '单行文本框(input)',
			'password' => '密码框(password)',
			'time' => '时间录入框(time)',
			'date' => '日期录入框(date)',
			'select' => '下拉框(select)',
			'selectinput' => '可修改下拉框(selectinput)',
			'pageselect' => '分页式下拉框(pageselect)',
			'radio' => '单选框(radio)',
			'checkbox' => '复选框(checkbox)',
			'textarea' => '多行文本框(textarea)',
			'basehtmlarea' => '简易HTML录入框(basehtmlarea)',
			'htmlarea' => '完整HTML录入框(htmlarea)',
			'pagehtmlarea' => '分页式HTML录入框(pagehtmlarea)',
			'imginput' => '图片(imginput)',
			'flash' => 'FLASH文件(flash)',
			'file' => '文件(file)',
			'color' => '颜色(color)',
			'hidden' => '隐藏表单域(hidden)',
			)
			,$field['inputtype'],'id="field[inputtype]" onchange="changeFieldType(this.value)"'
		);
	?></td></tr>
		<tr><td class="td1" align="right">宽度值(size)：</td><td class="td2"><?php echo form::input('field[size]',$field['size'],'id="field[size]" class="input" size="10"');?> <font class="helpicon" title="文本框通常是50，下拉框通常是1。对单选、复选框无效">&nbsp;</font> <font color="#a3a3a3">对于文本框将是它的size值，对于多行文本框，将是它的cols值</font></td></tr>
		<tr><td class="td1" align="right">行数值(rows)：</td><td class="td2"><?php echo form::input('field[rows]',$field['rows'],'id="field[rows]" class="input" size="10"');?> <font class="helpicon" title="高度只对多行文本框、可视化编辑器有效">&nbsp;</font> <font color="#a3a3a3">将用于多行文本框，通常是6</font></td></tr>
		<tr><td class="td1" align="right">样式(class)：</td><td class="td2"><?php echo form::input('field[class]',$field['class'],'id="field[class]" class="input" size="10"');?> <font color="#a3a3a3">&nbsp;对于input、多行文本框等推荐采用“input”样式</font></td></tr>
		<tr><td class="td1" align="right">其它属性：</td><td class="td2"><?php echo form::input('field[other]',$field['other'],'id="field[other]" class="input" size="30"');?> <font color="#a3a3a3">&nbsp;自定义其它的属性，例如：style="color:red"</font></td></tr>
		<tr><td class="td1" align="right">默认值：</td>
			<td class="td2">
			<span id="fieldDefaultValueDiv"><?php echo form::input('field[default]',$field['default'],'id="field[default]" style="width:300px;" class="input" size="40"');?></span> 
			&nbsp;<font class="helpicon">&nbsp;</font>
			</td>
		</tr>
		<tr><td class="td1" align="right">候选值传入内容：</td>
			<td class="td2">
			<span id="fieldCandidateValueDiv"><?php echo form::textarea('field[candidate]',$field['candidate'],'id="field[candidate]" style="width:300px;" class="input" rows="6"');?></span> &nbsp;<font class="helpicon" title="候选者每行一条，值和显示内容用|分开。例如：<br>0|否<br>1|是">&nbsp;</font>
			</td>
		</tr>
		<tr><td class="td1" align="right">&nbsp;</td>
			<td class="td2"><input type="button" class="btnl" value="查看生成的HTML" />
			<font class="helpicon" title="小技巧：若你希望使用自定义html录入框：<br/>您可以先点此按钮查看系统为您生成的代码<br/>然后将代码复制后粘贴到自定义HTML里，稍微修改即可。">&nbsp;</font>
			</td>
		</tr>
		</table>
	</div>
	<div id="mytagmain_2"<?php echo $showtagstyle_2;?>>
	<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="width:98%;border-top:none;">
		<tr><td class="td1" width="120" align="right">默认值：</td><td class="td2">
		<?php echo form::textarea('field[default2]',$field['default'],'id="field[default2]" style="width:300px;height:50px" class="input"');?>
		</td></tr>
		<tr><td class="td1" width="120" align="right">字段录入HTML：</td><td class="td2"><?php echo form::textarea('field[html]',$field['html'],'id="field[html]" cols="80" rows="8" class="input"');?>
		<br />
		替换变量说明：<br />
<pre>{{value}} 	- 对应的值
{{name}}  	- 表单的name
{{id}} 		- 表单的id
</pre>
		</td></tr>
	</table>
	</div>
	
	<div id="mytagmain_3"<?php echo $showtagstyle_3;?>>
	<div id="advFieldDiv">
		<div id="advFieldGroup_0">
		<table id="myEditorTable_00" border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="display:none;width:98%;border-top:none;">
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
		<table id="myEditorTable_0" border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="width:98%;border-top:none;">
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

	<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="width:98%;border-top:none;">
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
	</td>
</tr>
<tr>
	<td class="td1" align="right">后台信息管理列表项：</td>
	<td class="td2"><table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="width:98%;">
		<tr><td class="td1" width="120" align="right">后台列表是否列出：</td><td class="td2"><?php echo form::checkbox('field[islist]',1,$field['islist'],'id="field[islist]" onclick="islistclick(this.checked)"');?>&nbsp;<select size="1" id="islist_auto" onchange="changelistset(this.value)">
		<option value="0">快速设置样板</option>
		<option value="id">用于ID列</option>
		<option value="title">标题列</option>
		<option value="writer">作者列</option>
		<option value="boolean">是/否列(着重是)</option>
		<option value="boolean2">是/否列(着重否)</option>
		<option value="time">时间列</option>
		</select>&nbsp;<font class="helpicon" title="通常列出字段的有：ID，标题，是否显示，访问数这几栏。">&nbsp;</font><font color="#a3a3a3">当选择此项后，在系统后台的列表中将列出此字段。</font>
		</td>
		</tr><tr>
			<td class="td1" width="120" align="right">列的宽度：</td><td class="td2"><?php echo form::input('field[width]',$field['width'],'id="field[width]" class="input" size="5"');?>&nbsp;<font color="#a3a3a3">设置列出的宽度，数字，留空表示自适应宽度。</font></td>
		</tr><tr>
			<td class="td1" width="120" align="right">对齐方式：</td><td class="td2"><?php echo form::dropdown('field[align]',array(''=>'左对齐','center'=>'居中','right'=>'右对齐'),$field['align'],'id="field[align]" size="1"');?>&nbsp;<font color="#a3a3a3">大部分情况下都是居中，标题列建议左对齐。</font></td>
		</tr><tr>
			<td class="td1" width="120" align="right">单元格样式：</td><td class="td2"><?php echo form::dropdown('field[tdclass]',array(''=>'默认样式(td2)','td1'=>'加深醒目(td1)'),$field['tdclass'],'id="field[tdclass]" size="1"');?>&nbsp;<font color="#a3a3a3">若需要醒目则选择td2。</font></td>
		</tr><tr>
			<td class="td1" width="120" align="right">输出转换函数：</td><td class="td2"><?php echo form::dropdown('field[docode]',$fielddocode,$field['docode'],'id="field[docode]"');?> 
			<font class="helpicon" title="函数位于model/fieldapi.php文件">&nbsp;</font></td>
		</tr><tr>
			<td class="td1" width="120" align="right">输出转换传递参数：</td><td class="td2"><?php echo form::textarea('field[boolean]',$field['boolean'],'id="field[boolean]" cols="40" rows="4" class="input"');?>&nbsp;<font class="helpicon" title="当“输出转换函数”为“默认”时：每行替换一个值，支持HTML。<br/>例如：<br/>1|是<br/>0|&amp;lt;font color=&quot;red&quot;&amp;gt;否&amp;lt;/font&amp;gt;<br/>表示将0转换显示为“<font color=&quot;red&quot;>否</font>”，将1转换显示为“是”">&nbsp;</font></td>
		</tr>
		</table>
<script>
function changelistset(key){
	if (!key)return;
	var autoset = {
		"0":{},
		"id":{"align":"center","width":60,"tdclass":"td1"},
		"title":{"tdclass":""},
		"writer":{"align":"center","tdclass":"","width":100},
		"boolean":{"align":"center","tdclass":"","width":55,"boolean":'0|否\n1|<font color="red">是</font>'},
		"boolean2":{"align":"center","tdclass":"","width":55,"boolean":'1|是\n0|<font color="red">否</font>'},
		"time":{"align":"center","tdclass":"","width":150,"boolean":'Y-m-d H:i:s',"docode":'timelinetostr'}
	};
	if (!autoset[key])return;
	$('field[align]').value = autoset[key]["align"]||"";
	$('field[width]').value = autoset[key]["width"]||"";
	$('field[align]').value = autoset[key]["align"]||"";
	$('field[tdclass]').value = autoset[key]["tdclass"]||"";
	$('field[boolean]').value = autoset[key]["boolean"]||"";
	$('field[docode]').value = autoset[key]["docode"]||"";
}
function islistclick(ischecked){
	if(ischecked){
		$('islist_auto').disabled = $('field[align]').disabled = $('field[width]').disabled = $('field[align]').disabled = $('field[tdclass]').disabled = $('field[docode]').disabled = $('field[boolean]').disabled = false;
		$('field[width]').className = $('field[boolean]').className = 'input';
	}else{
		$('islist_auto').disabled = $('field[align]').disabled = $('field[width]').disabled = $('field[align]').disabled = $('field[tdclass]').disabled = $('field[docode]').disabled = $('field[boolean]').disabled = true;
		$('field[width]').className = $('field[boolean]').className = 'input disabled';
	}
	
}
$('field[islist]').onclick();
</script>
	</td>
</tr>
<tr>
	<td class="td1" align="right">字段说明：</td>
	<td class="td2"><textarea class="input" name="field[comment]" cols="80" rows="6"><?php echo html::specialchars($field['comment']);?></textarea>
	</td>
</tr>
<tr>
	<td class="td1" width="140" align="right">&nbsp;</td>
	<td class="td1"><input type="submit" value="保存设置" class="bbtn" /><input onclick="goback(0,document.location)" type="button" value="重设" class="btns" /><input onclick="goback(-1,'<?php echo Myqee::url('model/dbfield/'.$dbid);?>')" type="button" value="返回" class="btns" /></td>
</tr>
</table>
</form>

<script type="text/javascript">
	changeAutoSet($("field[autoset]").value);
</script>

<?php View::factory('admin/footer') -> render(TRUE);?>