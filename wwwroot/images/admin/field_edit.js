function changeAutoSet(thefield){
	var isautoset = true;
	if (thefield == ''){
		isautoset = false;
	};
	if (!theField[thefield])return;

	var toSet = ['iskey','isonly','isnonull','istofile','type','length','inputtype','rows','size','format','class'];
	var editSet = ',inputtype,rows,size,format,class,';
	for (var i in toSet){
		var key = toSet[i];
		var item = theField[thefield][key];
		var inputobj = $('field['+key+']');
		var isindexof = editSet.indexOf(','+key+',');
		if (inputobj){
			if (inputobj.type == 'checkbox'){
				inputobj.checked = item?true:false;
			}else{
				inputobj.value = item || '';
				if(isindexof<0){
					if (inputobj.className=='input'||inputobj.className=='input disabled'){
						if (isautoset){
							inputobj.className = 'input disabled';
						}else{
							inputobj.className = 'input';
						}
					}
				}
			}
			if (isindexof<0){
				inputobj.disabled = isautoset;
			}
		}
	}
	$('field[type]').value = $('field[type]').value || 'varchar';
	$('field[type]').onchange();
	$('field[inputtype]').onchange();
	$('field[default]').value = theField[thefield]['default']||'';
	$('field[candidate]').value = theField[thefield]['candidate']||'';
}

function changeFieldType(newType){
	var obj = $('fieldDefaultValueDiv');
	if (obj){
		var theinputobj = obj.getElementsByTagName('input')[0];
		var thetextobj = obj.getElementsByTagName('textarea')[0];

		if (theinputobj && (newType=='textarea' || newType=='basehtmlarea' || newType=='htmlarea' || newType=='pagehtmlarea')){
			obj.innerHTML = '<textarea class="input" name="field[default]" onchange="theDefaultValue=this.value" style="width:300px;height:6em;">'+theDefaultValue.replace(/&/g,'&amp;').replace(/\"/g,'&quot;')+'</textarea>';
		}else if(thetextobj && newType!='textarea' && newType!='basehtmlarea' && newType!='htmlarea' && newType!='pagehtmlarea'){
			obj.innerHTML = '<input class="input" name="field[default]" onchange="theDefaultValue=this.value;" type="text" size="45" style="width:300px;" value="'+theDefaultValue.replace(/&/g,'&amp;').replace(/\"/g,'&quot;')+'" />';
		}
	}
	var formatObj = $('field[format]');
	if (newType=='time'||newType=='date'){
		formatObj.value = 'time';
	}else if (newType=='basehtmlarea'||newType=='htmlarea'||newType=='pagehtmlarea'){
		formatObj.value = 'html';
	}else{
		if (formatObj.value=='time'||formatObj.value=='html')
			formatObj.value = '';
	}
	chkInputBeign("input",obj);
	chkInputBeign("textarea",obj);
}




var filedGroupId = '0';
var nowGroupId = '0';
var fieldGroupFlag = {'f_0':''};
var parentGroupId = {};
var nowGroupIndex = '1';		//当前组所在表的行号
function edit_field(id,groupid){
	id = id || '';
	groupid = groupid || '0';
	
	var tmphtml = '<div style="padding-top:10px"><table border="0" cellpadding="2" cellspacing="1" style="width:520px;" align="center" class="tableborder">'+
	'<tr><th class="td1" colspan="2">添加字段</th></tr>'+
	'<tr><td class="td1" width="120" align="right">字段标识：</td><td class="td2"><input id="adv_field_flag" type="text" size="16" value="" class="input" /> 只允许字母数字下划线，例如：field_1</td></tr>' +
	'<tr><td class="td1" align="right">字段名称：</td><td class="td2"><input id="adv_field_name" type="text" size="26" value="" class="input" /> 例如：字段一</td></tr>' +
	'<tr><td class="td1" align="right">录入类型：</td><td class="td2"><select size="1" id="adv_field_type" onchange="changeAdvFieldType(this.value)"><option value="input" selected="selected">单行文本框(input)</option><option value="password">密码框(password)</option><option value="time">时间录入框(time)</option><option value="date">日期录入框(date)</option><option value="select">下拉框(select)</option><option value="selectinput">可修改下拉框(selectinput)</option><option value="radio">单选框(radio)</option><option value="checkbox">复选框(checkbox)</option><option value="textarea">多行文本框(textarea)</option><option value="basehtmlarea">简易HTML录入框(basehtmlarea)</option><option value="htmlarea">完整HTML录入框(htmlarea)</option><option value="imginput">图片(imginput)</option><option value="flash">FLASH文件(flash)</option><option value="file">文件(file)</option><option value="color">颜色(color)</option><option value="hidden">隐藏表单域(hidden)</option></select></td></tr>' +
	'<tr><td class="td1" width="120" align="right">对录入值进行处理：</td><td class="td2"><select id="adv_field_format" size="1"><option value="">默认(纯文本,过滤所有HTML标记)</option><option value="br">过滤除&lt;br&gt;外所有HTML</option><option value="string">带简单html的字符串</option><option value="time">时间,日期</option><option value="int">数字</option><option value="safehtml">保留常用标签的安全HTML</option><option value="html">保留所有标签的HTML</option><option value="htmlspec">转义HTML(htmlspecialchars)</option><option value="alt">alt说明</option><option value="filepath">文件路径</option><option value="filename">文件名</option><option value="serialize">serialize序列化</option><option value="json_encode">json_encode序列化</option><option value="nodo">不做任何处理</option></select></td></tr>' +
	'<tr><td class="td1" align="right">编辑页左侧列宽度：</td><td class="td2"><input id="adv_field_editwidth" type="text" size="6" value="" class="input" /> <font color="#a3a3a3">留空为默认，初始默认值：100，0表示不显示</font></td></tr>'+
	'<tr><td class="td1" align="right">宽度值(size)：</td><td class="td2"><input id="adv_field_size" type="text" class="input" size="10" /></td></tr>' +
	'<tr><td class="td1" align="right">行数值(rows)：</td><td class="td2"><input id="adv_field_rows" type="text" class="input" size="10" /></td></tr>' +
	'<tr><td class="td1" align="right">样式(class)：</td><td class="td2"><input id="adv_field_class" type="text" class="input" size="10" /></td></tr>' +
	'<tr><td class="td1" align="right">其它属性：</td><td class="td2"><input id="adv_field_other" type="text" class="input" size="20" /></td></tr>' +
	'<tr><td class="td1" align="right">默认值：</td><td class="td2"><span id="advfieldDefaultValueDiv"><input type="text" id="adv_field_default" style="width:300px;" class="input" size="40" /></span></td></tr>' +
	'<tr><td class="td1" align="right">候选值传入内容：</td><td class="td2"><textarea id="adv_field_candidate" style="width:300px;height:60px;" class="input" rows="6"></textarea><br/>候选者每行一条，值和显示内容用|分开。</td></tr>' +
	'<tr><td class="td1">&nbsp;</td><td class="td1"><input type="button" class="btns" value="确 定" onclick="edit_field_ok(\''+id+'\',\''+groupid+'\')" /><input type="button" class="btns" value="取消" onclick="ymPrompt.close();" /></td></tr></table></div>';
	
	win({'message':tmphtml,'title':'添加字段','allowSelect':true,'dragOut':true},550,560);

	if (id>0){
		var table = $('myEditorTable_'+groupid);
		var tr = table.rows[id];
		var mytd = tr.cells[1].getElementsByTagName('input');
		$('adv_field_flag').value = mytd[0].value || '';
		$('adv_field_name').value = mytd[1].value || mydata['flag'];
		$('adv_field_type').value = mytd[2].value||'input';
		$('adv_field_format').value = mytd[3].value||'';
		$('adv_field_editwidth').value = mytd[4].value||'';
		$('adv_field_size').value = mytd[5].value||'';
		$('adv_field_rows').value = mytd[6].value||'';
		$('adv_field_class').value = mytd[7].value||'';
		$('adv_field_other').value = mytd[8].value||'';
		$('adv_field_default').value = mytd[9].value||'';
		$('adv_field_candidate').value = mytd[10].value||'';
	}
}

function showParent(){
	pgroupid = parentGroupId['f_'+nowGroupId] || '0';

	view_group(pgroupid);
	nowGroupId = pgroupid;
}

function edit_field_selfgroup(){
	edit_field_group(nowGroupIndex,parentGroupId['f_'+nowGroupId],nowGroupId);
}

function edit_field_group(id,groupid,selfid){
	id = id || '';
	groupid = groupid || '0';
	selfid = selfid || '';
	
	var tmphtml = '<div style="padding-top:10px"><table border="0" cellpadding="2" cellspacing="1" style="width:520px;" align="center" class="tableborder">'+
	'<tr><th class="td1" colspan="2">添加多维数组</th></tr>'+
	(selfid=='0'?'':'<tr><td class="td1" width="120" align="right">数组标识：</td><td class="td2"><input id="adv_field_group_flag" type="text" size="16" value="" class="input" /> 只允许字母数字下划线，例如：field_1</td></tr>' +
	'<tr><td class="td1" align="right">数组名称：</td><td class="td2"><input id="adv_field_group_name" type="text" size="26" value="" class="input" /> 例如：字段一</td></tr>') +
	'<tr><td class="td1" align="right">最多子项目：</td><td class="td2"><input id="adv_field_group_num" type="text" class="input" value="0" size="6" /> 0表示不限制</td></tr>' +
	'<tr><td class="td1" align="right">编辑页左侧列宽度：</td><td class="td2"><input id="adv_field_group_editwidth" type="text" size="6" value="" class="input" /> <font color="#a3a3a3">留空为默认，初始默认值：100，0表示不显示</font></td></tr>'+
	'<tr><td class="td1" align="right">排列方式：</td><td class="td2"><select size="1" id="adv_field_group_type"><option value="0" selected="0">切换式</option><option value="1">竖排式</option><option value="2">展开式</option></select></td></tr>' +
	'<tr><td class="td1" width="120" align="right">是否有添加按钮：</td><td class="td2"><select id="adv_field_group_isadd" size="1"><option value="1">是</option><option value="0">否</option></select></td></tr>' +
	'<tr><td class="td1" width="120" align="right">是否有删除按钮：</td><td class="td2"><select id="adv_field_group_isdel" size="1"><option value="1">是</option><option value="0">否</option></select></td></tr>' +
	'<tr><td class="td1" width="120" align="right">是否允许排序：</td><td class="td2"><select id="adv_field_group_isorder" size="1"><option value="1">是</option><option value="0">否</option></select></td></tr>' +
	'<tr><td class="td1">&nbsp;</td><td class="td1"><input type="button" class="btns" value="确 定" onclick="edit_field_group_ok(\''+id+'\',\''+groupid+'\',\''+selfid+'\')" /><input type="button" class="btns" value="取消" onclick="ymPrompt.close();" /></td></tr></table></div>';
	
	win({'message':tmphtml,'title':'添加多维数组','allowSelect':true,'dragOut':true},550,selfid=='0'?290:350);

	if (id>0){
		var table = $('myEditorTable_'+groupid+(selfid=='0'?'0':''));
		var tr = table.rows[id];
		var mytd = tr.cells[1].getElementsByTagName('input');
		if (!(selfid=='0')){
			$('adv_field_group_flag').value = mytd[1].value || '';
			$('adv_field_group_name').value = mytd[2].value || mydata['flag'];
		}
		$('adv_field_group_editwidth').value = mytd[3].value||'';
		$('adv_field_group_type').value = mytd[4].value||'input';
		$('adv_field_group_num').value = mytd[5].value||'';
		$('adv_field_group_isadd').value = mytd[6].value||'';
		$('adv_field_group_isdel').value = mytd[7].value||'';
		$('adv_field_group_isorder').value = mytd[8].value||'';
	}
}

function check_flag(flag,id){
	if (!flag){
		_alert('字段标识不能空！');
		return false;
	}else if(!flag.match(/^[0-9a-z_]*$/i)){
		_alert('字段标识不符合要求，只允许字母下划线和数字！');
		return false;
	}

	var rows = $('myEditorTable_'+nowGroupId).rows;
	for (var i=1;i<rows.length;i++){
		var inputs = rows[i].cells[1].getElementsByTagName('INPUT');
		var myflag = inputs[1].value;
		if ( id!=i && myflag==flag){
			_alert('字段标识重复，请重新输入！');
			return false;
		}
	}
	return true;
}

var _field_tableall = false;
function edit_field_group_ok(id,groupid,selfid,mydata){
	groupid = groupid || '0';
	if (!mydata){
		mydata = {};
		if (!(selfid=='0')){
			mydata['flag'] = $('adv_field_group_flag').value
			mydata['name'] = $('adv_field_group_name').value || mydata['flag'];
		}
		mydata['num'] = $('adv_field_group_num').value;if (!(mydata['num']>=0))mydata['num'] = '0';
		mydata['editwidth'] = $('adv_field_group_editwidth').value;
		mydata['type'] = $('adv_field_group_type').value;
		mydata['isadd'] = $('adv_field_group_isadd').value;
		mydata['isdel'] = $('adv_field_group_isdel').value;
		mydata['isorder'] = $('adv_field_group_isorder').value;
	}
	
	if (!(selfid=='0')){
		if (!check_flag(mydata['flag'],id)){
			return;
		}
	}
	
	var tableInfo;
	if (id>0){
		//修改
		tableInfo = get_tableinfo_group(mydata,groupid,selfid);
		var row = $('myEditorTable_'+groupid+(selfid=='0'?'0':'')).rows[id];
		row.cells[1].innerHTML = tableInfo[0];
		row.cells[2].innerHTML = tableInfo[1];
	}else{
		//添加
		filedGroupId ++;
		selfid = filedGroupId;
		parentGroupId['f_'+selfid] = nowGroupId;		//指定此ID的上层组ID
		tableInfo = get_tableinfo_group(mydata,groupid,selfid);

		myTable['mytable_'+groupid].addrows(1,tableInfo.length,[tableInfo]);

		//创建组所在的table
		var tmphtml = '<table id="myEditorTable_'+filedGroupId+'" border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="'+(_field_tableall?'':'width:98%;border-top:none;')+'"><tr><th class="td1" width="30">选择</th><th class="td1">名称</th><th class="td1" width="200">类型</th><th class="td1" width="220">操作</th></tr></table><input type="hidden" id="parentGroupId_'+filedGroupId+'" value="'+nowGroupId+'" />';
		var divobj = $('advFieldDiv');
		var newobj = document.createElement('div');
		newobj.id = 'advFieldGroup_'+filedGroupId;
		newobj.style.display = 'none';
		newobj.innerHTML = tmphtml;
		divobj.appendChild(newobj);
		myTable['mytable_'+filedGroupId]=new CreateTable('myEditorTable_'+filedGroupId,[]);
	}

	if (mydata['flag']!=fieldGroupFlag['f_'+selfid]){
		fieldGroupFlag['f_'+selfid] = mydata['flag'];
		if (id>0){
			//修改了flag名称
			changeGroupFlag(selfid);
		}
	}

	myqee($('myEditorTable_'+groupid));

	if (ymPrompt.close)ymPrompt.close();
}

function _set_value(str){
	str = ''+(str||'');
	return str.replace(/\"/g,'&quot;');
}
function get_tableinfo_group(mydata,groupid,selfid){
	groupid = groupid || '0';
	var idstr = get_group_idstr(groupid);
	var flag = '';
	if(mydata['flag']){
		flag = '['+mydata['flag']+']';
	}
	var ln = 'field[adv]'+idstr+flag+'[_g]';
	var tmphtml =
	'<input type="hidden" isgroup="true" value="'+selfid+'" />'+
	'<input type="hidden"'+(selfid=='0'?'':' name="'+ln+'[flag]" flag="'+flag+'[_g][flag]" value="'+mydata['flag']+'"')+' />' +
	'<input type="hidden"'+(selfid=='0'?'':' name="'+ln+'[name]" flag="'+flag+'[_g][name]" value="'+_set_value(mydata['name'])+'"')+' />' +
	'<input type="hidden" name="'+ln+'[editwidth]" flag="'+flag+'[_g][editwidth]" value="'+(mydata['editwidth']!=null&&mydata['editwidth']>=0?mydata['editwidth']:'')+'" />' +
	'<input type="hidden" name="'+ln+'[type]" flag="'+flag+'[_g][type]" value="'+_set_value(mydata['type'])+'" />' +
	'<input type="hidden" name="'+ln+'[num]" flag="'+flag+'[_g][num]" value="'+_set_value(mydata['num'])+'" />' +
	'<input type="hidden" name="'+ln+'[isadd]" flag="'+flag+'[_g][flag]" value="'+_set_value(mydata['isadd'])+'" />' +
	'<input type="hidden" name="'+ln+'[isdel]" flag="'+flag+'[_g][flag]" value="'+_set_value(mydata['isdel'])+'" />' +
	'<input type="hidden" name="'+ln+'[isorder]" flag="'+flag+'[_g][flag]" value="'+_set_value(mydata['isorder'])+'" />'
	;
	
	var tableInfo = new Array(
		(selfid=='0'?'':mydata['name'].replace(/\</g,'&lt;').replace(/\>/g,'&gt;')+'('+mydata['flag']+')')+tmphtml,
		'<center style="color:#ff3300;">多维数组</center>',
		'<center><input type="button" class="btn" value="进入查看" onclick="view_group(this.parentNode.parentNode.parentNode.rowIndex,\''+selfid+'\')" /><input type="button" class="btns" value="修改" onclick="edit_field_group(this.parentNode.parentNode.parentNode.rowIndex,\''+groupid+'\',\''+selfid+'\');" /><input type="button" class="btns" value="删除" onclick="delgrouprows(this.parentNode.parentNode.parentNode,\''+selfid+'\')"/></center>'
	);

	return tableInfo;
}

function view_group(rowindex,groupid){
	groupid = groupid || '0';
	var obj = $('advFieldDiv');
	divs = obj.getElementsByTagName('DIV');
	for (var i=0;i<divs.length;i++){
		if (divs[i].id=='advFieldGroup_'+groupid){
			nowGroupId = groupid;
			nowGroupIndex = rowindex;
			divs[i].style.display='';
		}else{
			divs[i].style.display='none';
		}
	}
	
	var obj = $('showParentBtn');
	if(groupid=='0'){
		obj.disabled = true;
		obj.className = 'btns btn_disabled';
	}else{
		obj.disabled = false;
		obj.className = 'btns';
	}
}

function get_group_idstr(groupid){
	if (!(groupid>0))return '';
	if (!fieldGroupFlag['f_'+groupid])return '';

	var flag = '['+fieldGroupFlag['f_'+groupid]+']';
	var parentId = parentGroupId['f_'+groupid];
	if (parentId && parentId!='0' && parentId!=''){
		flag = get_group_idstr(parentId) + flag;
	}
	return flag;
}


function delgrouprows(tr,groupid,isautodel){
	if (isautodel!='yes'){
		confirm('您确认删除此组？将删除此组内所有字段，是否继续？',420,160,null,function(et){
			if (et == 'ok'){
				if (tr){
					tr.parentNode.removeChild(tr);
				}
				del_gourp_byid(groupid);
			}
		});
	}
}
function del_gourp_byid(groupid){
	var obj = $('myEditorTable_'+groupid);
	if (!obj)return;
	rows = obj.rows;
	for (var i=1;i<rows.length;i++){
		var input = rows[i].cells[1].getElementsByTagName('INPUT')[0];
		if (input.value>0 && !input.name && input.getAttribute('isgroup') == 'true'){
			del_gourp_byid(input.value);
		}
	}

	fieldGroupFlag['f_'+groupid] = null;
	parentGroupId['f_'+groupid] = null;

	obj.parentNode.removeChild(obj);
}

function changeGroupFlag(groupid){
	if (!(groupid>0))return;
	var table = $('myEditorTable_'+groupid);
	if (!table)return;
	var inputs = table.getElementsByTagName('INPUT');

	var idstr = get_group_idstr(groupid);
	for (var i=0;i<inputs.length;i++){
		if (inputs[i].type=='hidden'){
			if (inputs[i].name){
				change_input_name(inputs[i],'field[adv]'+idstr);
			}else if (inputs[i].value>0 && inputs[i].getAttribute('isgroup') == 'yes'){
				//发现组
				changeGroupFlag(inputs[i].value);
			}
		}
	}
}

function change_input_name(obj,newname){
	var val = obj.value;
	var flag = obj.getAttribute('flag');
	if (!flag)return;
	var parent = obj.parentNode;
	var sibling = obj.nextSibling;
	var newel = document.createElement('input');
	newel.setAttribute('type','hidden');
	newel.setAttribute('value',val);
	newel.setAttribute('name',newname+flag);
	newel.setAttribute('flag',flag);
	parent.removeChild(obj);
	parent.insertBefore(newel, sibling);
}

var theAdvDefaultValue ='';
function changeAdvFieldType(newType){
	var obj = $('advfieldDefaultValueDiv');
	if (obj){
		var theinputobj = obj.getElementsByTagName('input')[0];
		var thetextobj = obj.getElementsByTagName('textarea')[0];

		if (theinputobj && (newType=='textarea' || newType=='basehtmlarea' || newType=='htmlarea')){
			obj.innerHTML = '<textarea class="input" id="adv_field_default" onchange="theAdvDefaultValue=this.value" style="width:300px;height:6em;">'+theAdvDefaultValue.replace(/&/g,'&amp;').replace(/\"/g,'&quot;')+'</textarea>';
		}else if(thetextobj && newType!='textarea' && newType!='basehtmlarea' && newType!='htmlarea' && newType!='pagehtmlarea'){
			obj.innerHTML = '<input class="input" id="adv_field_default" onchange="theAdvDefaultValue=this.value;" type="text" size="45" style="width:300px;" value="'+theAdvDefaultValue.replace(/&/g,'&amp;').replace(/\"/g,'&quot;')+'" />';
		}
	}
	chkInputBeign("input",obj);
	chkInputBeign("textarea",obj);
}

function edit_field_ok(id,groupid,mydata){
	groupid = groupid || '0';
	if (!mydata){
		mydata = {};
		mydata['flag'] = $('adv_field_flag').value;
		mydata['name'] = $('adv_field_name').value || mydata['flag'];
		mydata['type'] = $('adv_field_type').value;
		mydata['format'] = $('adv_field_format').value||'';
		mydata['editwidth'] = $('adv_field_editwidth').value>=0?$('adv_field_editwidth').value:'';
		mydata['set'] = {};
		mydata['set']['size'] = $('adv_field_size').value;
		mydata['set']['rows'] = $('adv_field_rows').value;
		mydata['set']['class'] = $('adv_field_class').value;
		mydata['set']['other'] = $('adv_field_other').value;
		mydata['default'] = $('adv_field_default').value;
		mydata['candidate'] = $('adv_field_candidate').value;
	}

	if (!check_flag(mydata['flag'],id)){
		return;
	}

	var tableInfo = get_tableinfo(mydata,groupid);

	if (id>0){
		var row = $('myEditorTable_'+groupid).rows[id];
		row.cells[1].innerHTML = tableInfo[0];
		row.cells[2].innerHTML = tableInfo[1];
		row.cells[3].innerHTML = tableInfo[2];
	}else{
		myTable['mytable_'+groupid].addrows(1,tableInfo.length,[tableInfo]);
	}
	myqee($('myEditorTable_'+groupid));
	if (ymPrompt.close)ymPrompt.close();
}

function get_tableinfo(mydata,groupid){
	groupid = groupid || '0';
	var typestr = {
		'input' : '单行文本框(input)',
		'password' : '密码框(password)',
		'time' : '时间录入框(time)',
		'date' : '日期录入框(date)',
		'select' : '下拉框(select)',
		'selectinput' : '可修改下拉框(selectinput)',
		'radio' : '单选框(radio)',
		'checkbox' : '复选框(checkbox)',
		'textarea' : '多行文本框(textarea)',
		'basehtmlarea' : '简易HTML录入框(basehtmlarea)',
		'htmlarea' : '完整HTML录入框(htmlarea)',
		'pagehtmlarea' : '分页式HTML录入框(pagehtmlarea)',
		'imginput' : '图片(imginput)',
		'flash' : 'FLASH文件(flash)',
		'file' : '文件(file)',
		'color' : '颜色(color)',
		'hidden' : '隐藏表单域(hidden)'
	};
	var idstr = get_group_idstr(groupid);
	var ln = 'field[adv]'+idstr+'['+mydata['flag']+']';
	
	var tmphtml = 
	'<input type="hidden" flag="['+mydata['flag']+'][flag]" name="'+ln+'[flag]" value="'+mydata['flag']+'" />' +
	'<input type="hidden" flag="['+mydata['flag']+'][name]" name="'+ln+'[name]" value="'+_set_value(mydata['name'])+'" />' +
	'<input type="hidden" flag="['+mydata['flag']+'][type]" name="'+ln+'[type]" value="'+_set_value(mydata['type'])+'" />' +
	'<input type="hidden" flag="['+mydata['flag']+'][format]" name="'+ln+'[format]" value="'+_set_value(mydata['format'])+'" />' +
	'<input type="hidden" flag="['+mydata['flag']+'][editwidth]" name="'+ln+'[editwidth]" value="'+(mydata['editwidth']!=null&&mydata['editwidth']>=0?mydata['editwidth']:'')+'" />' +
	'<input type="hidden" flag="['+mydata['flag']+'][set][size]" name="'+ln+'[set][size]" value="'+_set_value(mydata['set']['size'])+'" />' +
	'<input type="hidden" flag="['+mydata['flag']+'][set][rows]" name="'+ln+'[set][rows]" value="'+_set_value(mydata['set']['rows'])+'" />' +
	'<input type="hidden" flag="['+mydata['flag']+'][set][class]" name="'+ln+'[set][class]" value="'+_set_value(mydata['set']['class'])+'" />' +
	'<input type="hidden" flag="['+mydata['flag']+'][set][other]" name="'+ln+'[set][other]" value="'+_set_value(mydata['set']['other'])+'" />' +
	'<input type="hidden" flag="['+mydata['flag']+'][default]" name="'+ln+'[default]" value="'+_set_value(mydata['default'])+'" />' +
	'<input type="hidden" flag="['+mydata['flag']+'][candidate]" name="'+ln+'[candidate]" value="'+_set_value(mydata['candidate'])+'" />'
	;
	var tableInfo = new Array(
		mydata['name'].replace(/\</g,'&lt;').replace(/\>/g,'&gt;')+'('+mydata['flag']+')'+tmphtml,
		'<center>'+(typestr[mydata['type']]||'单行文本框(input)')+'</center>',
		'<center><input type="button" class="btn" value="" style="visibility:hidden;" /><input type="button" class="btns" value="修改" onclick="edit_field(this.parentNode.parentNode.parentNode.rowIndex,\''+groupid+'\');" /><input type="button" class="btns" value="删除" onclick="delrows(this.parentNode.parentNode.parentNode,\''+groupid+'\')"/></center>'
	);

	return tableInfo;
}

function delrows(tr,groupid,isautodel){
	if (isautodel!='yes'){
		confirm('您确认删除？是否继续？',380,160,null,function(et){
			if (et =='ok'){
				if (tr){
					tr.parentNode.removeChild(tr);
				}
			}
		});
	}
}

function ini_field(myArray,nowid){
	nowid = nowid || '0';	//记录组ID
	for (var key in myArray)
	{
		if (key=='_g'){
			continue;
		}
		if (!myArray[key])
		{
			continue;
		}
		if ( typeof (myArray[key]['_g']) == 'object'){
			//创建组
			edit_field_group_ok(null,nowid,null,myArray[key]['_g']);

			ini_field(myArray[key],filedGroupId);
		}else{
			//创建字段
			nowGroupId = nowid;		//还原组ID
			var tmpc = myArray[key]['candidate'];
			var tmpstr = [];
			var i = 0;
			for(var ii in tmpc){
				tmpstr[i] = ii +(ii==tmpc[ii]?'':'|'+tmpc[ii]);
				i++;
			}
			myArray[key]['candidate'] = tmpstr.join("\n");
			edit_field_ok(null,nowGroupId,myArray[key]);
		}
	}
}