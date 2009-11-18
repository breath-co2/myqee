<?php
$page_index = 'model';
$page_title = '修改字段:'.$field['name'];
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);

$disabled = '';
$inputclass = 'input';
?>

<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('model/index');?>">模型管理</a> -&gt; <a href="<?php echo Myqee::url('model/edit/'.$modelid);?>">修改模型</a> -&gt; <?php echo $page_title;?>
</div>

<form method="post" name="myforms" action="<?php echo Myqee::url('model/modelfieldsave/'.$modelid.'/'.$field['name']);?>" target="hiddenFrame">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
<tr>
	<th colspan="2"><?php echo $page_title; ?></th>
</tr>
<tr>
	<td class="td1" align="right" width="140">字段名：</td>
	<td class="td2"><?php echo $dbname .'.<b>' .$field['name'].'</b>';?>&nbsp;</td>
</tr>
<tr>
	<td class="td1" align="right">表单名称：</td>
	<td class="td2"><input name="field[dbname]" type="text" size="20" class="input" value="<?php echo $field['dbname'];?>" /> &nbsp;<font color="#a3a3a3">例如“标题”</font></td>
</tr>
<tr>
	<td class="td1" align="right">表单录入说明：</td>
	<td class="td2"><input name="field[comment]" type="text" size="30" class="input" value="<?php echo $field['comment'];?>" /> &nbsp;<font color="#a3a3a3">例如“请输入标题，不能留空”</font></td>
</tr>
<tr>
	<td class="td1" align="right">录入表单显示方式：</td>
	<td class="td2"><table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="width:98%;">
		<tr>
			<td class="td1" width="120" align="right">表单类型：</td><td class="td2"><?php echo form::dropdown(
		'field[inputtype]',array(
			'input' => '单行文本框(input)',
			'password' => '密码框(password)',
			'time' => '时间录入框(time)',
			'date' => '日期录入框(date)',
			'select' => '下拉框(select)',
			'selectinput' => '可修改下拉框(selectinput)',
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
		<tr><td class="td1" align="right">宽度值(size)：</td><td class="td2"><?php echo form::input('field[size]',$field['size'],'id="field[size]" class="'.$inputclass.'" size="10"'.$disabled);?> <font class="helpicon" title="文本框通常是50，下拉框通常是1。对单选、复选框无效">&nbsp;</font> <font color="#a3a3a3">对于文本框将是它的size值，对于多行文本框，将是它的cols值</font></td></tr>
		<tr><td class="td1" align="right">行数值(rows)：</td><td class="td2"><?php echo form::input('field[rows]',$field['rows'],'id="field[rows]" class="'.$inputclass.'" size="10"'.$disabled);?> <font class="helpicon" title="高度只对多行文本框、可视化编辑器有效">&nbsp;</font> <font color="#a3a3a3">将用于多行文本框，通常是6</font></td></tr>
		<tr><td class="td1" align="right">样式(class)：</td><td class="td2"><?php echo form::input('field[class]',$field['class'],'id="field[class]" class="'.$inputclass.'" size="10"'.$disabled);?> <font color="#a3a3a3">&nbsp;对于input、多行文本框等推荐采用“input”样式</font></td></tr>
		<tr><td class="td1" align="right">其它属性：</td><td class="td2"><?php echo form::input('field[other]',$field['other'],'id="field[other]" class="input" size="30" class="input"');?> <font color="#a3a3a3">&nbsp;自定义其它的属性，例如：style="color:red"</font></td></tr>
		<tr><td class="td1" align="right">对录入值格式化：</td><td class="td2"><?php echo form::dropdown('field[format]',array(''=>'不做任何处理','string'=>'只允许纯字符','time'=>'时间','date'=>'日期','int'=>'数字','html'=>'HTML','alt'=>'alt说明','filepath'=>'文件路径','filename'=>'文件名'),$field['format'],$disabled);?></td></tr>
		<tr><td class="td1" align="right">默认值：</td>
			<td class="td2">
			<span id="fieldDefaultValueDiv"><?php echo form::input('field[default]',$field['default'],'id="field[default]" style="width:300px;" class="input" size="40" class="input"');?></span> &nbsp;<font class="helpicon">&nbsp;</font>
			</td>
		</tr>
		<tr><td class="td1" align="right">候选值传入内容：</td>
			<td class="td2">
			<span id="fieldCandidateValueDiv"><?php echo form::textarea('field[candidate]',$field['candidate'],'id="field[candidate]" style="width:300px;" class="input" rows="6" class="input"');?></span> &nbsp;<font class="helpicon" title="候选者每行一条，值和显示内容用|分开。例如：<br>0|否<br>1|是">&nbsp;</font>
			</td>
		</tr>
		</table>
<script type="text/javascript">
	var theDefaultValue = '<?php echo str_replace("\n",'\n',str_replace('\'','\\\'',str_replace('\\','\\\\',$field['default'])));?>';
	function changeFieldType(newType){
		var obj = $('fieldDefaultValueDiv');
		if (!obj)return;
		var theinputobj = obj.getElementsByTagName('input')[0];
		var thetextobj = obj.getElementsByTagName('textarea')[0];

		if (theinputobj && (newType=='textarea' || newType=='basehtmlarea' || newType=='htmlarea' || newType=='pagehtmlarea')){
			obj.innerHTML = '<textarea class="input" name="field[default]" onchange="theDefaultValue=this.value" style="width:300px;height:6em;">'+theDefaultValue.replace(/&/g,'&amp;').replace(/\"/g,'&quot;')+'</textarea>';
		}else if(thetextobj && newType!='textarea' && newType!='basehtmlarea' && newType!='htmlarea' && newType!='pagehtmlarea'){
			obj.innerHTML = '<input class="input" name="field[default]" onchange="theDefaultValue=this.value;" type="text" size="45" style="width:300px;" value="'+theDefaultValue.replace(/&/g,'&amp;').replace(/\"/g,'&quot;')+'" />';
		}

		chkInputBeign("input",obj);
		chkInputBeign("textarea",obj);
	}
</script>
	<div id="fieldTypeSetDiv"></div>
	</td>
</tr>
<tr>
	<td class="td1" align="right">后台信息管理列表项：</td>
	<td class="td2"><table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="width:98%;">
		<tr><td class="td1" width="120" align="right">是否列出：</td><td class="td2"><?php echo form::dropdown('field[islist]',array('-1'=>'字段默认设置('.$islist_normal.')','1'=>'列出','0'=>'不列出'),(string)$field['islist'],'id="field[islist]" onchange="islistclick(this.value)"');?>&nbsp;<select size="1" id="islist_auto" onchange="changelistset(this.value)">
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
			<td class="td1" width="120" align="right">输出转换函数：</td><td class="td2"><?php echo form::dropdown('field[docode]',$fielddocode,$field['docode'],'id="field[docode]"');?></td>
		</tr><tr>
			<td class="td1" width="120" align="right">输出转换传递参数：</td><td class="td2"><?php echo form::textarea('field[boolean]',$field['boolean'],'id="field[boolean]" cols="40" rows="4" class="input"');?>&nbsp;<font class="helpicon" title="例如用于显示是否显示等，每行替换一个值，支持HTML。<br/>例如：<br/>1|是<br/>0|&amp;lt;font color=&quot;red&quot;&amp;gt;否&amp;lt;/font&amp;gt;<br/>表示将0转换显示为“<font color=&quot;red&quot;>否</font>”，将1转换显示为“是”">&nbsp;</font></td>
		</tr>
		</table>
<script type="text/javascript">
function changelistset(key){
	if (!key)return;
	var autoset = {
		"0":{},
		"id":{"align":"center","width":60,"tdclass":"td1"},
		"title":{"tdclass":""},
		"writer":{"align":"center","tdclass":"","width":100},
		"boolean":{"align":"center","tdclass":"","width":55,"boolean":'0|否\n1|<font color="red">是</font>'},
		"boolean2":{"align":"center","tdclass":"","width":55,"boolean":'1|是\n0|<font color="red">否</font>'},
		"time":{"align":"center","tdclass":"","width":180,"boolean":'Y-m-d H:i:s',"docode":'timelinetostr'}
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
	if(ischecked=='1'){
		$('islist_auto').disabled = $('field[align]').disabled = $('field[width]').disabled = $('field[align]').disabled = $('field[tdclass]').disabled = $('field[docode]').disabled = $('field[boolean]').disabled = false;
		$('field[width]').className = $('field[boolean]').className = 'input';
	}else{
		$('islist_auto').disabled = $('field[align]').disabled = $('field[width]').disabled = $('field[align]').disabled = $('field[tdclass]').disabled = $('field[docode]').disabled = $('field[boolean]').disabled = true;
		$('field[width]').className = $('field[boolean]').className = 'input disabled';
	}
}
$('field[islist]').onchange();
</script>
	</td>
</tr>
<tr>
	<td class="td1" width="140" align="right">&nbsp;</td>
	<td class="td1"><input type="submit" value="保存设置" class="bbtn" /><input onclick="ask_del('<?php echo $field['name'];?>','model/clearmodel_fieldset/<?php echo $modelid;?>','您确实要清除此模型的数据字段设置?');" type="button" value="清除设置" class="btn" /><input onclick="goback(0,document.location)" type="button" value="重设" class="btns" /><input onclick="goback(-1,'<?php echo Myqee::url('model/edit/'.$modelid);?>')" type="button" value="返回" class="btns" /></td>
</tr>
</table>
</form>


<?php View::factory('admin/footer') -> render(TRUE);?>