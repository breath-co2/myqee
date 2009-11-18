<?php
$page_index = 'task';
if ($node_id>0){
	$page_title = '修改采集点';
}elseif ($iscopy){
	$page_title = '复制采集点';
}else{
	$page_title = '增加采集点';
}
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('task/index');?>">任务管理</a> -&gt; <a href="<?php echo Myqee::url('acquisition/index');?>">采集管理</a>
 -&gt; 任务：<a href="<?php echo Myqee::url('acquisition/node_list/'.$acquisition_id);?>"><?php echo $acquisition_name;?></a> -&gt; <?php echo $page_title;?>
</div>

<div id="thetempMenu_Div" style="position:absolute;top:0;left:0;"></div>

<div style="width:96%;margin:auto;">
<ul class="ul tag">
	<li class="now" id="mytag_1" onclick="tag(this.id,'mytag','mytagmain');set_control_fixed();document.location.hash='#tag1'">基本设置</li>
	<li id="mytag_2" onclick="tag(this.id,'mytag','mytagmain');set_control_fixed();document.location.hash='#tag2'">采集地址</li>
	<li id="mytag_3" onclick="tag(this.id,'mytag','mytagmain');set_control_fixed();document.location.hash='#tag3'">过滤设置</li>
	<li id="mytag_5" onclick="tag(this.id,'mytag','mytagmain');set_control_fixed();document.location.hash='#tag5'">采集规则</li>
	<li id="mytag_4" onclick="tag(this.id,'mytag','mytagmain');set_control_fixed();document.location.hash='#tag4'">输出采集地址</li>
	<li id="mytag_6" onclick="tag(this.id,'mytag','mytagmain');set_control_fixed();document.location.hash='#tag6'">采集附件</li>
	<li id="mytag_7" onclick="tag(this.id,'mytag','mytagmain');set_control_fixed();document.location.hash='#tag7'">入库设置</li>
</ul>
</div>
<div style="clear:both"></div>

<form action="<?php echo Myqee::url('acquisition/node_save/'.$acquisition_id.'/'.$node_id);?>" method="POST" target="hiddenFrame">
<div id="mytagmain_1">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
<tr>
	<th class="td1" colspan="2">基本设置</th>
</tr>
<tr>
<td class="td1" align="right">采节点名称：</td>
<td class="td2"><?php echo form::input('acqu_node[name]',$data['name'],'class="input" size="30"');?></td>
</tr>
<tr>
<td class="td1" align="right">是否启用：</td>
<td class="td2">
<?php echo form::radio('acqu_node[isuse]',1,!isset($data['isuse']) || (int)$data['isuse']==1);?>是 
<?php echo form::radio('acqu_node[isuse]',0,isset($data['isuse']) && (int)$data['isuse']==0);?>否
</td>
</tr>
<tr>
<td class="td1" align="right">操作密钥：</td>
<td class="td2"><?php echo form::input('acqu_node[key]',$data['key']?$data['key']:Tools::get_rand(30),'class="input" size="30"');?> 
<font color="#999999">密钥可以是任意符号，清空则重新随机生成</font></td>
</tr>
<tr>
<td class="td1" align="right">排序：</td>
<td class="td2"><?php echo form::input('acqu_node[myorder]',(int)$data['myorder'],'class="input" size="5"');?> 
<font color="#999999">排序越小越前</font></td>
</tr>
<tr>
<td class="td1" align="right">栏目/模型/数据表：</td>
<td class="td2"><?php
if ($data['dbname']){
	echo '<font style="color:red">自定义设置</font> '.($data['classname']?$data['classname']:'无'),'/'.($data['modelname']?$data['modelname']:'无').'/'.$data['dbname'];
}else{
	echo '<font color="green">[默认设置]</font> '.($acquisition['classname']?$acquisition['classname']:'无'),'/'.($acquisition['modelname']?$acquisition['modelname']:'无').'/'.$acquisition['dbname'];
}
?> <input type="button" class="btns" value="修改" title="可自定义栏目、模型或数据表" /></td>
</tr>
<tr>
<td class="td1" align="right">打开远程地址超时时间：</td>
<td class="td2"><?php echo form::input('acqu_node[openurltimeout]',isset($data['openurltimeout'])?$data['openurltimeout']:60,'class="input" size="5"');?> 单位：秒
<font class="helpicon" title="尽量不要设置为0，否则可能导致服务器运行缓慢<br/>特别是被打开网站不稳定的情况下，尤其要注意设定合理的值。">&nbsp;</font><font color="#999999">0表示不限制，设置时间过短容易导致抓取失败，建议设置60秒</font></td>
</tr>
<tr>
<td class="td1" align="right">每组采集页面数：</td>
<td class="td2"><?php echo form::input('acqu_node[limitpage]',(int)$data['limitpage']>1?(int)$data['limitpage']:1,'class="input" size="5"');?> 页 
<font color="#999999">(默认为1，数字越大采集效率越高，但也可能造成超时，根据采集速度自行调节，不要太大。)</font></td>
</tr>
<tr>
<td class="td1" align="right">每组采集时间间隔：</td>
<td class="td2"><?php echo form::input('acqu_node[limittime]',isset($data['limittime'])?(int)$data['limittime']:1000,'class="input" size="5"');?> 毫秒 
<font color="#999999">(0为连续采集，采集过快会造成服务器压力较大。)</font></td>
</tr>
<tr>
<td class="td1" align="right">组完成后操作：</td>
<td class="td2"><?php echo form::input('acqu_node[donum]',(int)$data['donum'],'class="input" title="0表示不进行操作" size="5"');?> 组执行完成后 
<?php echo form::dropdown('acqu_node[dotype]',array('执行入库','新启动另一节点'),$data['dotype'],'id="acqu_node[dotype]" onchange="if(this.value==1){$(\'acqu_node[donext_node]\').style.display=\'\'}else{$(\'acqu_node[donext_node]\').style.display=\'none\'}"');?> 
<span><?php echo form::dropdown('acqu_node[donext_node]',$otheracqu,$data['donext_node'],'id="acqu_node[donext_node]" style="display:none;"');?></span>
<script type="text/javascript">$("acqu_node[dotype]").onchange();</script>
</td>
</tr>
<tr>
<td class="td1" align="right">任务全部完成后操作：</td>
<td class="td2"><?php 
echo form::dropdown('acqu_node[doalltype]',
	array('结束任务','执行入库,完成后结束任务','执行入库,完成后执行另一节点','执行入库,并同时执行另一节点','直接执行另一节点'),
	$data['doalltype'],
	'id="acqu_node[doalltype]" onchange="if(this.value==2||this.value==3||this.value==4){$(\'acqu_node[doallnext_node]\').style.display=\'\'}else{$(\'acqu_node[doallnext_node]\').style.display=\'none\'}"');
?> 
<span><?php echo form::dropdown('acqu_node[doallnext_node]',$otheracqu,$data['doallnext_node'],'id="acqu_node[doallnext_node]" style="display:none;"');?></span>
<script type="text/javascript">$("acqu_node[doalltype]").onchange();</script>
</td>
</tr>
<tr>
<td class="td1" align="right">提交方式：</td>
<td class="td2"><?php echo form::dropdown('acqu_node[method]',array('GET'=>'GET','POST'=>'POST'),isset($data['method'])?$data['method']:$acquisition['method']);?> 
<font color="#999999">通常都是GET方式，支持GET或POST方式。</font></td>
</tr>
<tr>
<td class="td1" align="right" width="150">提交参数：</td>
<td class="td2"><?php echo form::textarea('acqu_node[postdata]',$data['postdata'],'class="input" cols="60" rows="6"');?> 多个参数之间用&隔开，例如：user=myname&pass=123</td>
</tr>
<tr>
<td class="td1" align="right">允许重复采集同一链接：</td>
<td class="td2"><?php echo form::checkbox('acqu_node[reacquurl]',1,(int)$data['reacquurl']);?>允许重复采集 
<font color="#999999">(不选为不允许重复采集)</font></td>
</tr>
</table>
</div>

<div id="mytagmain_3" style="display:none;">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" id="filtertable">
<tr>
	<th class="td1" colspan="2">过滤设置</th>
</tr>
<tr>
<td class="td1" align="right" width="150">过滤规则：</td>
<td class="td2">
<div id="filterDiv"></div>
<input type="button" class="btnl" value="新增过滤规则" onclick="addfilter()" />
</td>
</tr>
</table>
</div>
<script type="text/javascript">
var allFilterNum = 0;
var allFilterIds = ',';
function get_apiformstr(name,value){
	var arr = <?php echo Tools::json_encode($apilist)?>;
	var html = '<select name="'+name+'">';
	for (var i in arr)
	{
		html += '<option value="'+i+'"'+(value==i?' selected="selected"':'')+'>'+arr[i]+'</option>';
	}
	html += '</select>';
	return html;
}

function addfilter(filterdata){
	var obj=$('filterDiv');
	if (!filterdata){
		filterdata = {};
		filterdata['filter_'+allFilterNum] = {};
	}
	for (var key in filterdata){
		var item = filterdata[key];
		var newobj =$('filterDiv_'+allFilterNum);
		if (newobj){
			allFilterNum++;
			var newdata = {};
			newdata['filter_'+allFilterNum] = item;
			addfilter(newdata);
			continue;
		}
		newobj = document.createElement("div");
		newobj.id = 'filterDiv_'+allFilterNum;
		newobj.style.overflow = 'hidden';
		newobj.style.width = '100%';
		newobj.innerHTML = '<div style="padding:4px 3px 5px 3px;"><table border="0" cellpadding="4" cellspacing="1" class="tableborder" style="width:100%">'+
		'<tr><th class="td1" colspan="2">过滤设置</th></tr>'+
		'<tr><td class="td1" align="right" width="90">过滤标识：</td><td class="td2"><input class="input" type="text" size="20" maxlength="30" id="filter_key_'+allFilterNum+'" name="filter[filter_'+allFilterNum+'][key]" value="'+key+'" /> 必须唯一，只允许字母数字下划线的组合，且不能全为数字。例如：filterad</td></tr>'+
		'<tr><td class="td1" align="right">过滤别名：</td><td class="td2"><input class="input" type="text" size="20" maxlength="30" onchange="setdoinfochange('+allFilterNum+',this.value)" id="filter_name_'+allFilterNum+'" name="filter[filter_'+allFilterNum+'][name]" value="'+(item['name']||'过滤选项_'+allFilterNum).replace(/\"/g,'')+'" /> 例如：过滤页面广告</td></tr>'+
		'<tr><td class="td1" align="right">内容匹配范围：</td><td class="td2"><input type="hidden" name="filter[filter_'+allFilterNum+'][doid]" id="filterdoid_'+allFilterNum+'" value="'+(item['doid']||'-1')+'" /><span id="show_filtertext_'+allFilterNum+'" class="select" onclick="selectFilter('+allFilterNum+')">全部源代码</span></td></tr>'+
		'<tr><td class="td1" align="right">过滤规则：</td><td class="td2"><textarea class="input" rows="4" style="height:60px;overflow:auto" cols="60" name="filter[filter_'+allFilterNum+'][preg]">'+(item['preg']||'').replace(/</g,'&lt;').replace(/>/g,'&gt;')+'</textarea> <span class="helpicon" title="例如：<font color=red>/&amp;lt;script.*&amp;gt;.*&amp;lt;\\/script&amp;gt;/Uis</font> 可匹配所有脚本代码<br/>其中，最后的U代表忽略换行，i代表忽略大小写，s代表匹配最小范围<br/>更多参数请查阅php手册中正则“模式语法”">&nbsp;</span>输入过滤的正则</td></tr>'+
		'<tr><td class="td1" align="right">保留内容：</td><td class="td2"><textarea class="input" rows="4" style="height:60px;overflow:auto" cols="60" name="filter[filter_'+allFilterNum+'][replace]">'+(item['replace']||'').replace(/</g,'&lt;').replace(/>/g,'&gt;')+'</textarea> 对匹配到的内容部分片段进行必要的保留，留空则不保留</td></tr>'+
		'<tr><td class="td1" align="right">过滤后执行：</td><td class="td2"> '+get_apiformstr('filter[filter_'+allFilterNum+'][api]',item['api']||'')+' <span class="helpicon" title="任何添加自己的函数？<br/>在/admin/api/MY_Acquisition.php的Acquisition_Api类中添加自己的接口函数即可！">&nbsp;</span> 在执行过滤正则后对字符串进行处理</td></tr>'+
		'<tr><td class="td1">&nbsp;</td><td class="td2"><input type="button" class="btn" value="测试规则" /><input type="button" onclick="delectfilter('+allFilterNum+')" value="删除规则" class="btn" /></td></tr>'+
		'</table></div>';
		obj.appendChild(newobj);
		
		allFilterIds += allFilterNum + ',';
		allFilterNum ++;
	}
//	$('filter_doinfo_0').innerHTML='<option value=3>asdf</option><option value=6 selected>asdf</option>'
	//setdoinfoselect();
	myqee(obj);
	set_control_fixed();
}

(function (){
	var node = <?php echo Tools::json_encode($data['filter']);?>;
	if (!node)return;
	addfilter(node);
	//设置“内容匹配范围”名称
	var i=0;
	for (var j in node){
		if (node[j].doid>=0){
			setdoinfoselect(i,node[j].doid);
		}
		i++;
	}
})();


function getfilterbyvalue(value){
	if (!value)return;
	var obj=$('filterDiv');
	var inputs = obj.getElementsByTagName('input');

	for (var i in inputs){
		if (inputs[i].value==value && inputs[i].name.indexOf('[key]')>0){
			var theid = inputs[i].id.substr(11);//filter_key_
			var obj2 = $('filter_name_'+theid);
			if (obj2){
				return [theid,obj2.value.replace(/</g,'&lt;').replace(/>/g,'&gt;')];
			}
		}
	}
	return ['-1',null];
}

function selectFilter(theid){
	var allids = [];
	if (allFilterIds.length>2){
		allids = allFilterIds.substr(1,allFilterIds.length-2).split(',');
	}
	var oldvalue=$('filterdoid_'+theid).value;
	var tmpstr = '<div id="show_filtertext_'+theid+'_menu" style="display:none;width:150px;height:230px;padding:0;"><select size="5" style="width:150px;height:230px;" onchange="setdoinfoselect('+theid+',this.value)"><option value="-1"'+(oldvalue=='-1'?' selected="selected"':'')+'>全部源文件</option><option value="-2"'+(oldvalue=='-2'?' selected="selected"':'')+'>网页URL</option>';
	for (var i=0;i<allids.length;i++){
		if (allids[i]!=theid){
			var objtitle=$('filter_name_'+allids[i]);
			tmpstr += '<option value="'+allids[i]+'"'+(allids[i]==oldvalue?' selected="selected"':'')+'>'+objtitle.value.replace(/</g,'&lt;').replace(/>/g,'&gt;')+'</option>';
		}
	}
	tmpstr += '</select></div>';


	$('thetempMenu_Div').innerHTML = tmpstr;

	showMenu('show_filtertext_'+theid,true,0,2,500,'acqu');
	
//	window.win('选择过滤设置',tmpstr,300,280);
}

function delectfilter(filterid){
	confirm('您确实要删除此过滤规则？',null,null,'请确认',function(tp){
		if (tp=='ok'){
			var obj = $('filterDiv_'+filterid);
			if (!obj)return;
			doDelObj(obj,function(){
				delfilter(filterid,false);
				delfilter(filterid,'acqu');
				delfilter(filterid,'urls');
			},0);
		}
	});
}


function delfilter(id,thetype){
	var theids;
	var allids = [];
	var objstr1 = 'filterdoid_';
	var objstr2 = 'show_filtertext_';
	if (thetype=='acqu'){
		theids=allAcquIds;
		objstr1 = 'acqu_doinfo_';
		objstr2 = 'acqu_doinfo_text_';
	}else if (thetype=='urls'){
		theids=allUrlIds;
		objstr1 = 'urls_doinfo_';
		objstr2 = 'urls_doinfo_text_';
	}else{
		allFilterIds = allFilterIds.replace(','+id+',',',');
		theids=allFilterIds;
	}
	if (theids.length>2){
		allids = theids.substr(1,theids.length-2).split(',');
	}
	for (var i=0;i<allids.length;i++){
		var obj=$(objstr1+allids[i]);
		if (!obj)continue;
		if (obj.value==id){
			obj.value='-1';
			$(objstr2+allids[i]).innerHTML = '全部源代码';
		}
	}

	var obj = $('acqu_node[the_id_name]');
	if (obj){
		if (obj.value==id){
			setdo_foridname(-1);
		}
	}
}

function setdoinfoselect(id,newvalue){
	if (chkfilterinfoid(newvalue,id)==0)return;
	var obj = $('filterdoid_'+id);
	if (obj)obj.value = newvalue;
	var obj2 = $('filter_name_'+newvalue);
	if (obj2){
		$('show_filtertext_'+id).innerHTML = obj2.value.replace(/</g,'&lt;').replace(/>/g,'&gt;');
	}else if(newvalue==-2){
		$('show_filtertext_'+id).innerHTML = '网页URL';
	}else{
		obj.value = '-1';
		$('show_filtertext_'+id).innerHTML = '全部源代码';
	}
	try{hideMenu('acqu');}catch(e){}
	//closeMsgBox();
}

function chkfilterinfoid(id,newvalue){
	var obj = $('filterdoid_'+id);
	if (!obj)return 1;
	if(newvalue>=0){
		if (newvalue==obj.value){
			alert('抱歉，您选择的内容范围已属于此过滤设置的子步骤，请重新选择！');
			return 0;
		}
		return chkfilterinfoid(obj.value,newvalue);
	}
	return -1;
}

function setdoinfochange(id,newtext){
	var allids = [];
	if (allFilterIds.length>2){
		allids = allFilterIds.substr(1,allFilterIds.length-2).split(',');
	}
	newtext = newtext.replace(/</,'&lt;').replace(/>/,'&gt;').replace(/>/g,'&gt;');
	for (var i=0;i<allids.length;i++){
		var obj=$('filterdoid_'+allids[i]);
		if (!obj)continue;
		if (obj.value==id){
			$('show_filtertext_'+allids[i]).innerHTML = newtext;
		}
	}
	
	allids = [];
	if (allAcquIds.length>2){
		allids = allAcquIds.substr(1,allAcquIds.length-2).split(',');
	}
	for (var i=0;i<allids.length;i++){
		var obj=$('acqu_doinfo_'+allids[i]);
		if (!obj)continue;
		if (obj.value==id){
			$('acqu_doinfo_text_'+allids[i]).innerHTML = newtext;
		}
	}

	allids = [];
	if (allUrlIds.length>2){
		allids = allUrlIds.substr(1,allUrlIds.length-2).split(',');
	}
	for (var i=0;i<allids.length;i++){
		var obj=$('urls_doinfo_'+allids[i]);
		if (!obj)continue;
		if (obj.value==id){
			$('urls_doinfo_text_'+allids[i]).innerHTML = newtext;
		}
	}

	allids = [];
	if (allFileIds.length>2){
		allids = allFileIds.substr(1,allFileIds.length-2).split(',');
	}
	for (var i=0;i<allids.length;i++){
		var obj=$('file_doinfo_'+allids[i]);
		if (!obj)continue;
		if (obj.value==id){
			$('file_doinfo_text_'+allids[i]).innerHTML = newtext;
		}
	}
	
	var obj = $('acqu_node[the_id_name]');
	if (obj){
		if (obj.value==id){
			setdo_foridname(id);
		}
	}
}
/*
function setdoinfoselect(isincludeself){
	var obj =$('filterDiv');
	var selects = obj.getElementsByTagName('select');
	var allids = [];
	if (allFilterIds.length>2){
		allids = allFilterIds.substr(1,allFilterIds.length-2).split(',');
	}
	for (var i=0;i<selects.length;i++){
		if ( (selects[i].id||'').substr(0,14)!='filter_doinfo_')continue;
		var theid = selects[i].id.substr(14);
		var thevalue = selects[i].value;
		//清空下拉框数据
		selects[i].innerHTML = '';
		if (ie)selects[i].outHTML = '<select id="filter_doinfo_'+theid+'" name="filter[filter_'+theid+'][doinfo]"></select>';

		selects[i].oldvalue = '';
		for (var j=0;j<allids.length;j++){
			if (j==0)selects[i].options[0] = new Option('全部源文件','');
			var nameobj = $('filter_name_'+allids[j]);
			if (!nameobj)continue;
			
			if (theid!=allids[j]){
				selects[i].options[selects[i].options.length] = new Option(nameobj.value,allids[j]);
				
				if (allids[j]==thevalue){
					selects[i].value = thevalue;
					
					if (ie){
						selects[i].oldvalue = thevalue;
					}else{
						selects[i].setAttribute('oldvalue',thevalue);
					}
				}
			}
		}
	}

	var obj = $('acquisitionDiv');
	var selects = obj.getElementsByTagName('select');
	for (var i=0;i<selects.length;i++){
		if ( (selects[i].id||'').substr(0,12)!='acqu_doinfo_')continue;
		var theid = selects[i].id.substr(14);
		var thevalue = selects[i].value;
		//清空下拉框数据
		selects[i].innerHTML = '';
		if (ie)selects[i].outHTML = '<select id="acqu_doinfo_'+theid+'" name="acqu[acqu_'+theid+'][doinfo]"></select>';

		selects[i].oldvalue = '';
		for (var j=0;j<=allids.length;j++){
			if (j==0)selects[i].options[0] = new Option('全部源文件','');
			var nameobj = $('filter_name_'+allids[j]);
			if (!nameobj)continue;
			
			selects[i].options[selects[i].options.length] = new Option(nameobj.value,allids[j]);
			if (allids[j]==thevalue){
				selects[i].value = thevalue;
			}
		}
	}
}


function chkfilterinfoid(obj,id){
	if (!obj)return false;
	if(obj.value>=0 && obj.value!=''){
		if (obj.value==id || chkfilterinfoid($('filter_doinfo_'+obj.value),id)==true){
			var oldvalue;
			var theobj = $('filter_doinfo_'+id);
			if (ie){
				oldvalue = theobj.oldvalue;
			}else{
				oldvalue = theobj.getAttribute('oldvalue');
			}
			alert('抱歉，您选择的内容范围已属于此过滤设置的子步骤，请重新选择！'+oldvalue);
			theobj.value = oldvalue||'';
			return false;
		}
		return false;
	}
	return false;
}

*/
</script>





<div id="mytagmain_2" style="display:none;">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
<tr>
	<th class="td1" colspan="2">采集地址</th>
</tr>
<tr>
<td class="td1" align="right">获取采集地址方式：
</td>
<td class="td2">
<?php echo form::dropdown('acqu_node[urltype]',array('一组固定的地址列表','根据当前采集页面分析下一页面地址','有规律的页面地址','调用其它节点输出的地址'),$data['urltype'],'id="acqu_node[urltype]" onchange="tag(\'urltagtype_\'+this.value,\'urltagtype\',\'urltype\',\'theurltypetag\');"');?> 
<span class="helpicon" title="“<b>一组固定的地址列表</b>”通常是一些个别的较固定且较少的页面！<br/>“<b>根据当前采集页面分析下一页面地址</b>”通常可用户列表页。<br/>“<b>有规律的页面地址</b>”例如list_1.html,list_2.html这样的页面<br/>“<b>调用其它节点输出的地址</b>”是调用其它采集点（例如列表采集页）采集的输出的页面地址列表。">&nbsp;</span>
</td>
</tr>
<tr>
<td class="td1" width="150" align="right">采集地址设置：</td>
<td class="td2" id="theurltypetag">
<div style="display:none;">
<ul class="ul tag">
	<li class="now" id="urltagtype_0"></li>
	<li id="urltagtype_1"></li>
	<li id="urltagtype_2"></li>
	<li id="urltagtype_3"></li>
</div>
<div id="urltype_0"><?php echo form::textarea('acqu_node[theurl0]',$data['theurl0'],'class="input" cols="90" rows="10"');?> 
（一行一条页面）
</div>
<div id="urltype_1" style="display:none;">
<table border="0" cellpadding="4" cellspacing="1" style="width:550px;" class="tableborder">
<tr>
	<td class="td1" align="right" width="90">原始采集页面：</td>
	<td class="td2"><?php echo form::input('acqu_node[theurl1][url]',$data['theurl1']['url'],'class="input" size="40"');?> 
	<span class="helpicon" title="当程序执行时传入一个页面地址时，此项会被覆盖掉<br/>例如：http://www.myqee.com/class1/list_1.html"></span> 采集时可传入值替代</td>
</tr>
<tr>
	<td class="td1" align="right">匹配范围：</td>
	<td class="td2">
	<input type="hidden" name="acqu_node[theurl1][filter]" id="acqu_node[theurl1][filter]" value="-1" />
	<span id="show_theurl1_filter" class="select" onclick="selectAcqu_forurl1()">全部HTML</span>
	<font color="#999999"></font>
	</td>
</tr>

<script type="text/javascript">
function selectAcqu_forurl1(){
	var allids = [];
	if (allFilterIds.length>2){
		allids = allFilterIds.substr(1,allFilterIds.length-2).split(',');
	}
	var oldvalue=$('acqu_node[theurl1][filter]').value;
	var tmpstr = '<div id="show_theurl1_filter_menu" style="display:none;width:150px;height:250px;padding:0;"><select size="6" style="width:150px;height:250px;" onchange="setdo_forurl1(this.value)"><option value="-1"'+(oldvalue=='-1'?' selected="selected"':'')+'>全部HTML</option>';
	for (var i=0;i<allids.length;i++){
		var objtitle=$('filter_name_'+allids[i]);
		tmpstr += '<option value="'+allids[i]+'"'+(allids[i]==oldvalue?' selected="selected"':'')+'>'+objtitle.value.replace(/</g,'&lt;').replace(/>/g,'&gt;')+'</option>';
	}
	tmpstr += '</select></div>';


	$('thetempMenu_Div').innerHTML = tmpstr;

	showMenu('show_theurl1_filter',true,0,2,500,'acqu');
}

function setdo_forurl1(newvalue){
	var obj = $('acqu_node[theurl1][filter]');
	if (!obj){
		try{hideMenu('acqu');}catch(e){}
		return;
	}
	obj.value = newvalue;
	var textobj =$('show_theurl1_filter');
	var titleobj = $('filter_name_'+newvalue);
	if (titleobj){
		textobj.innerHTML = titleobj.value.replace(/</g,'&lt;').replace(/>/g,'&gt;');
	}else{
		obj.value = '-1';
		textobj.innerHTML = '全部HTML';
	}
	try{hideMenu('acqu');}catch(e){}
}

setdo_forurl1("<?php echo $data['theurl1']['filter'];?>");
</script>

<tr>
	<td class="td1" align="right">下一地址规则：</td>
	<td class="td2">
<?php echo form::textarea('acqu_node[theurl1][next]',$data['theurl1']['next'],'class="input" cols="60" rows="8"');?> 
<span class="helpicon" title="在当前采集页面源文件里进行匹配，与后面的采集规则相同！<br/>例如：/&#38;lt;a href=&quot;\/class\/\?id=1&page=([0-9]+)&quot;&#38;gt;下一页&#38;lt;\/a&#38;gt;/Uis">&nbsp;</span>
<div style="padding:3px 0;">将上面的规则结果转换为需要的结果</div>
<?php echo form::input('acqu_node[theurl1][tourl]',$data['theurl1']['tourl'],'class="input" size="40"');?> 
<span class="helpicon" title="将上面的匹配内容转换为需要的结果<br/>例如：http://www.myqee.com/class1/list_&#38;#123;$1&#38;#125;.html！">&nbsp;</span>
</td>
</tr>
<tr>
	<td class="td1" align="right">&nbsp;</td>
	<td class="td2"><input type="button" class="btn" value="测试规则" /></td>
</tr>
</table>
</div>
<div id="urltype_2" style="display:none;">
<table border="0" cellpadding="4" cellspacing="1" style="width:550px;" class="tableborder">
<tr>
	<td class="td1" align="right" width="70">规则地址：</td>
	<td class="td2"><?php echo form::input('acqu_node[theurl2][url]',$data['theurl2']['url'],'class="input" size="40"');?> 
	<span class="helpicon" title="例如：http://www.myqee.com/class1/list_&#38;#123;&#38;#123;page&#38;#125;&#38;#125;.html">&nbsp;</span> 
	采集时可传入值。</td>
</tr>
<tr>
	<td class="td1" align="right">替换变量：</td>
	<td class="td2"><?php echo form::input('acqu_node[theurl2][replace]',isset($data['theurl2']['replace']) && !empty($data['theurl2']['replace'])?$data['theurl2']['replace']:'{{page}}','class="input" size="8"');?> 将上面地址中出现的此内容替换为操作页码/ID</td>
</tr>
<tr>
	<td class="td1" align="right">起始页码：</td>
	<td class="td2"><?php echo form::input('acqu_node[theurl2][begin]',$data['theurl2']['begin']>1?$data['theurl2']['begin']:1,'class="input" size="8"');?> 
	采集时可传入值。</td>
</tr>
<tr>
	<td class="td1" align="right">结束页码：</td>
	<td class="td2"><?php echo form::input('acqu_node[theurl2][end]',$data['theurl2']['end']>1?$data['theurl2']['end']:1,'class="input" size="8"');?> 
	采集时可传入值。</td>
</tr>
<tr>
	<td class="td1" align="right">间隔倍数：</td>
	<td class="td2"><?php echo form::input('acqu_node[theurl2][limit]',$data['theurl2']['limit']>1?$data['theurl2']['limit']:1,'class="input" size="8"');?> 
<span class="helpicon" title="间隔倍数为1，则采集的页码为1,2,3,4,5...<br/>间隔倍数为10则为1,11,21,31,41,51..."></span> 
</td>
</tr>
<tr>
	<td class="td1" align="right">补足位数：</td>
	<td class="td2">向 <?php echo form::dropdown('acqu_node[theurl2][makeup]',array('前','后'),(int)$data['theurl2']['makeup']);?> 
	补足<?php echo form::input('acqu_node[theurl2][makeupnum]',$data['theurl2']['makeupnum']>0?$data['theurl2']['makeupnum']:0,'class="input" size="4"');?> 位，补足字符 <?php echo form::input('acqu_node[theurl2][makeupstr]',$data['theurl2']['makeupstr'],'class="input" size="6"');?> 
	<span class="helpicon" title="例如可以进行补0操作<br/>补0操作：补足字符输入“0”，补足位数输入“2”。">&nbsp;</span><font color="#999999">“补足0位”表示不进行补足操作</font>
</td>
</tr>
<tr>
	<td class="td1" align="right">倒序：</td>
	<td class="td2"><?php echo form::checkbox('acqu_node[theurl2][reverse]',1,$data['theurl2']['reverse']==1?true:false);?>
</td>
</tr>
<tr>
	<td class="td1" align="right">&nbsp;</td>
	<td class="td2"><input type="button" class="btnl" value="查看输出结果" /></td>
</tr>
</table>
</div>
<div id="urltype_3" style="display:none;">
<?php echo form::dropdown('acqu_node[theurl3]',$otheracqu_forurl,$data['theurl3']['id'].'|'.$data['theurl3']['nodeid'],'size="1"');?> 
可以选择调用其它节点输出的采集地址
</div>
<script type="text/javascript">
$('acqu_node[urltype]').onchange();
</script>
</td>
</tr>
</table>
</div>


<div id="mytagmain_4" style="display:none;">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
<tr>
	<th class="td1" colspan="2">输出采集地址</th>
</tr>
<tr>
<td class="td1" align="right" width="150">输出采集地址：</td>
<td class="td2">
<div id="urlsDiv"></div>
<input type="button" class="btnl" value="新增输出采集" onclick="addurls()" />
</td>
</tr>
</table>
</div>

<script type="text/javascript">

var allUrlNum = 0;
var allUrlIds = ',';

function addurls(acqudata){
	var obj=$('urlsDiv');
	if (!acqudata){
		acqudata = {};
		acqudata['url_'+allUrlNum] = {};
	}
	for (var key in acqudata){
		var item = acqudata[key];
		var newobj =$('urlDiv_'+allUrlNum);
		if (newobj){
			allUrlNum++;
			var newdata = {};
			newdata['url_'+allUrlNum] = item;
			addurls(newdata);
			continue;
		}
		newobj = document.createElement("div");
		newobj.id = 'urlDiv_'+allUrlNum;
		newobj.style.overflow = 'hidden';
		newobj.style.width = '100%';
		if (item['doinfo']){
			var tmpv = getfilterbyvalue(item['doinfo']);
			item['doinfo'] = tmpv[0];
			item['doinfo_name'] = tmpv[1];
		}
		newobj.innerHTML = '<div style="padding:4px 3px 5px 3px;"><table border="0" cellpadding="4" cellspacing="1" class="tableborder" style="width:100%">'+
		'<tr><th class="td1" colspan="2">输出采集地址设置</th></tr>'+
		'<tr><td class="td1" align="right" width="90">地址标识：</td><td class="td2"><input class="input" type="text" size="20" maxlength="30" id="urls_key_'+allUrlNum+'" name="urls[urls_'+allUrlNum+'][key]" value="'+key+'" /> 必须唯一，只允许字母数字下划线的组合，且不能全为数字。例如：urls_news</td></tr>'+
		'<tr><td class="td1" align="right">名称：</td><td class="td2"><input class="input" type="text" size="20" maxlength="30" onchange="setdoinfoselect()" id="urls_name_'+allUrlNum+'" name="urls[urls_'+allUrlNum+'][name]" value="'+(item['name']||'输出地址规则_'+allUrlNum).replace(/\"/g,'')+'" /> 例如：新闻采集地址</td></tr>'+
		'<tr><td class="td1" align="right">地址更新方式：</td><td class="td2"><select name="urls[urls_'+allUrlNum+'][infotype]"><option value="0"'+(item['infotype']==0?' selected="selected"':'')+'>覆盖原先</option><option value="1"'+(item['infotype']==1?' selected="selected"':'')+'>追加插入</option></select> <font class="helpicon" title="默认为覆盖插入<br/>“追加插入”常用于带有分页内容的字段"></font></td></tr>'+
		'<tr><td class="td1" align="right">内容匹配范围：</td><td class="td2"><input type="hidden" id="urls_doinfo_'+allUrlNum+'" value="'+(item['doinfo']||'-1')+'" name="urls[urls_'+allUrlNum+'][doinfo]" /><span id="urls_doinfo_text_'+allUrlNum+'" class="select" onclick="selectUrls('+allUrlNum+')">'+(item['doinfo_name']||'全部源文件')+'</span> 可以到“过滤设置”中添加一个新的过滤项。</td></tr>'+
		'<tr><td class="td1" align="right">匹配规则：</td><td class="td2"><textarea class="input" rows="4" style="width:400px;height:60px;overflow:auto" cols="60" name="urls[urls_'+allUrlNum+'][preg]">'+(item['preg']||'').replace(/</g,'&lt;').replace(/>/g,'&gt;')+'</textarea> <span class="helpicon" title="<span style=&quot;font-family:宋体&quot;>例如：<font color=red>/&amp;lt;a href=&quot;(.*)&quot;&amp;gt;.*&amp;lt;\\/a&amp;gt;/Uis</font> 可匹配所有超链接<br/>其中，最后的U代表忽略换行，i代表忽略大小写，s代表匹配最小范围<br/>更多参数请查阅php手册中正则“模式语法”<br/><font color=red>若待匹配内容为数组</font><br/>例如<br/><pre>array(<br/>	array(&quot;blog_url&quot;=>&quot;http://www.test.com/&quot;),<br/>	array(&quot;blog_url&quot;=>&quot;http://www.test2.com/&quot;),<br/>)</pre>可采用<font color=red>/^[0-9]+ => blog_url => (.*)$/</font>这样的形式匹配<br/><b>注意</b>“ => ”前后要有空格</span>">&nbsp;</span>输入匹配的的正则</td></tr>'+
		'<tr><td class="td1" align="right">转换为地址：</td><td class="td2"><input class="input" size="60" style="width:400px;" name="urls[urls_'+allUrlNum+'][replace]" value="'+(item['replace']||'').replace(/</g,'&lt;').replace(/>/g,'&gt;')+'" /> 对匹配到的内容部分进行处理，转换为地址</td></tr>'+
		'<tr><td class="td1">&nbsp;</td><td class="td2"><input type="button" class="btn" value="测试规则" /><input type="button" onclick="delecturls('+allUrlNum+')" value="删除规则" class="btn" /></td></tr>'+
		'</table><div style="clear:both;"></div></div>';
		obj.appendChild(newobj);
		allUrlIds += allUrlNum + ',';
		allUrlNum ++;
	}
	myqee(obj);
	set_control_fixed();
}


(function (){
	var node = <?php echo Tools::json_encode($data['urls']);?>;
	if (!node)return;
	addurls(node);
})();


function delecturls(id){
	confirm('您确实要删除此输出地址规则？',null,null,'请确认',function(tp){
		if (tp =='ok'){
			var obj = $('urlDiv_'+id);
			if (!obj)return;
			doDelObj(obj,function (){
				allUrlIds = allUrlIds.replace(','+id+',',',');
			},0);
		}
	});
}


function selectUrls(theid){
	var allids = [];
	if (allFilterIds.length>2){
		allids = allFilterIds.substr(1,allFilterIds.length-2).split(',');
	}
	var oldvalue=$('urls_doinfo_'+theid).value;
	var tmpstr = '<div id="urls_doinfo_text_'+theid+'_menu" style="display:none;width:150px;height:230px;padding:0;"><select size="5" style="width:150px;height:230px;" onchange="seturlfilterselect('+theid+',this.value)"><option value="-1"'+(oldvalue=='-1'?' selected="selected"':'')+'>全部源文件</option>';
	for (var i=0;i<allids.length;i++){
		var objtitle=$('filter_name_'+allids[i]);
		tmpstr += '<option value="'+allids[i]+'"'+(allids[i]==oldvalue?' selected="selected"':'')+'>'+objtitle.value.replace(/</g,'&lt;').replace(/>/g,'&gt;')+'</option>';
	}
	tmpstr += '</select></div>';
	$('thetempMenu_Div').innerHTML = tmpstr;
	
	showMenu('urls_doinfo_text_'+theid,true,0,2,500,'acqu');
}

function seturlfilterselect(theid,newvalue){
	var obj = $('urls_doinfo_'+theid);
	if (!obj)return hideMenu('acqu');
	obj.value = newvalue;
	var textobj =$('urls_doinfo_text_'+theid);
	var titleobj = $('filter_name_'+newvalue);
	if (titleobj){
		textobj.innerHTML = titleobj.value.replace(/</g,'&lt;').replace(/>/g,'&gt;');
	}else{
		obj.value = '-1';
		textobj.innerHTML = '全部源文件';
	}
	hideMenu('acqu');
}
</script>

<!-- 
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
<tr>
<td class="td1" align="right">列表匹配规则：</td>
<td class="td2">
<?php echo form::textarea('acqu_node[theurlout_preg]',$data['theurlout_preg'],'class="input" cols="60" rows="8" style="width:400px;"');?> 
<div style="padding:3px 0;">将上面的规则结果转换为需要的列表结果</div>
<?php echo form::input('acqu_node[theurlout_tolist]',$data['theurlout_tolist'],'class="input" size="63" style="width:400px;"');?> 
<span class="helpicon" title="将上面的匹配内容转换为需要的结果<br/>例如：http://www.myqee.com/class1/list_&#38;#123;$1&#38;#125;.html">&nbsp;</span>
<br/>
<input type="button" class="btnl" value="测试以上规则" style="margin:5px 0 0 0;" />
</td>
</tr>
<tr>
<td class="td1" align="right">传给采集点立即执行采集：</td>
<td class="td2">
<?php echo form::dropdown('acqu_node[next_node]',array('不操作'),$data['next_node']);?> 
<font color="#999999">在获取输出采集地址完成时启动选中的其它的采集点，此功能可用于采集存在分页的内容页。</font>
</td>
</tr>
</table>
 -->

<div id="mytagmain_5" style="display:none;">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" id="filtertable">
<tr>
	<th class="td1" colspan="2">采集规则</th>
</tr>
<tr>
<td class="td1" align="right" width="150">匹配规则：</td>
<td class="td2">
<div id="acquisitionDiv"></div>
<input type="button" class="btnl" value="新增匹配规则" onclick="addacqu()" />
</td>
</tr>
</table>
</div>


<script type="text/javascript">
var selectedField = ',';
function getallfield(){
	var allfield = <?php echo $field;?>;
	return allfield;
}

var allAcquNum = 0;
var allAcquIds = ',';

function addacqu(acqudata){
	var obj=$('acquisitionDiv');
	if (!acqudata){
		acqudata = {};
		acqudata['acqu_'+allAcquNum] = {};
	}
	for (var key in acqudata){
		var item = acqudata[key];
		var newobj =$('acquDiv_'+allAcquNum);
		if (newobj){
			allAcquNum++;
			var newdata = {};
			newdata['acqu_'+allAcquNum] = item;
			addacqu(newdata);
			continue;
		}
		newobj = document.createElement("div");
		newobj.id = 'acquDiv_'+allAcquNum;
		newobj.style.overflow = 'hidden';
		newobj.style.width = '100%';
		if (item['doinfo']){
			var tmpv = getfilterbyvalue(item['doinfo']);
			item['doinfo'] = tmpv[0];
			item['doinfo_name'] = tmpv[1];
		}
		newobj.innerHTML = '<div style="padding:4px 3px 5px 3px;"><table border="0" cellpadding="4" cellspacing="1" class="tableborder" style="width:100%">'+
		'<tr><th class="td1" colspan="2">采集规则</th></tr>'+
		'<tr><td class="td1" align="right" width="90">过滤标识：</td><td class="td2"><input class="input" type="text" size="20" maxlength="30" id="acqu_key_'+allAcquNum+'" name="acqu[acqu_'+allAcquNum+'][key]" value="'+key+'" /> 必须唯一，只允许字母数字下划线的组合，且不能全为数字。例如：acqu_title</td></tr>'+
		'<tr><td class="td1" align="right">过滤名称：</td><td class="td2"><input class="input" type="text" size="20" maxlength="30" onchange="setdoinfoselect()" id="acqu_name_'+allAcquNum+'" name="acqu[acqu_'+allAcquNum+'][name]" value="'+(item['name']||'匹配规则_'+allAcquNum).replace(/\"/g,'')+'" /> 例如：文章标题</td></tr>'+
		'<tr><td class="td1" align="right">匹配后不能空：</td><td class="td2"><input name="acqu[acqu_'+allAcquNum+'][isnotnull]" type="checkbox" value="1"'+(item['isnotnull']?' checked="checked"':'')+' /> 若选择此项，当在匹配时遇到采集的是空内容则认为是无效的信息</td></tr>'+
		'<tr><td class="td1" align="right">对应字段：</td><td class="td2"><input type="hidden" id="acqu_dbfield_'+allAcquNum+'" name="acqu[acqu_'+allAcquNum+'][dbfield]" value="'+(item['dbfield']||'')+'" /><span class="select" id="selectdbtext_'+allAcquNum+'" onclick="selectDbfield('+allAcquNum+')">请选择</span> 选择一个字段，入库时将会将数据录入到对应字段。</td></tr>'+
		'<tr><td class="td1" align="right">数据插入方式：</td><td class="td2"><select name="acqu[acqu_'+allAcquNum+'][infotype]"><option value="0">覆盖插入</option><option value="1"'+(item['infotype']==1?' selected="selected"':'')+'>追加插入</option><option value="2"'+(item['infotype']==2?' selected="selected"':'')+'>数值相加插入</option></select> <font class="helpicon" title="默认为覆盖插入<br/>“追加插入”常用于带有分页内容的字段<br/>“数值相加插入”会与之前数据表数据进行相加运算，讲结果存入数据表">&nbsp;</font></td></tr>'+
		'<tr><td class="td1" align="right">内容匹配范围：</td><td class="td2"><input type="hidden" id="acqu_doinfo_'+allAcquNum+'" value="'+(item['doinfo']||'-1')+'" name="acqu[acqu_'+allAcquNum+'][doinfo]" /><span id="acqu_doinfo_text_'+allAcquNum+'" class="select" onclick="selectAcqu('+allAcquNum+')">'+(item['doinfo_name']||'全部源文件')+'</span> 可以到“过滤设置”中添加一个新的过滤项。</td></tr>'+
		'<tr><td class="td1" align="right">匹配规则：</td><td class="td2"><textarea class="input" rows="4" style="height:60px;overflow:auto" cols="60" name="acqu[acqu_'+allAcquNum+'][preg]">'+(item['preg']||'').replace(/</g,'&lt;').replace(/>/g,'&gt;')+'</textarea> <span class="helpicon" title="例如：<font color=red>/&amp;lt;a href=&quot;(.*)&quot;&amp;gt;.*&amp;lt;\\/a&amp;gt;/Uis</font> 可匹配所有超链接<br/>其中，最后的U代表忽略换行，i代表忽略大小写，s代表匹配最小范围<br/>更多参数请查阅php手册中正则“模式语法”">&nbsp;</span>输入匹配的的正则</td></tr>'+
		'<tr><td class="td1" align="right">转换内容：</td><td class="td2"><textarea class="input" rows="4" style="height:60px;overflow:auto" cols="60" name="acqu[acqu_'+allAcquNum+'][replace]">'+(item['replace']||'').replace(/</g,'&lt;').replace(/>/g,'&gt;')+'</textarea> 对匹配到的内容部分进行处理</td></tr>'+
		'<tr><td class="td1">&nbsp;</td><td class="td2"><input type="button" class="btn" value="测试规则" /><input type="button" onclick="delectacqu('+allAcquNum+')" value="删除规则" class="btn" /></td></tr>'+
		'</table><div style="clear:both;"></div></div>';
		obj.appendChild(newobj);

		if (item['dbfield']){
			setdbinfoselect(allAcquNum,item['dbfield']);
		}

		
		allAcquIds += allAcquNum + ',';
		allAcquNum ++;
	}
	myqee(obj);
	set_control_fixed();
}


(function (){
	var node = <?php echo Tools::json_encode($data['acqu']);?>;
	if (!node)return;
	addacqu(node);
	//设置“内容匹配范围”名称
	var i=0;
	for (var j in node){
		if (node[j].doid>=0){
			setdoinfoselect(i,node[j].doid);
		}
		i++;
	}
})();

function selectDbfield(theid){
	var allfield = getallfield();
	var oldvalue=$('acqu_dbfield_'+theid).value;
	var tmpstr = '<div id="selectdbtext_'+theid+'_menu" style="display:none;width:200px;height:260px;overflow:hidden;padding:0;"><select size="5" style="width:200px;height:260px;" onchange="setdbinfoselect('+theid+',this.value)">';
	for (var i in allfield){
		if (selectedField.indexOf(','+i+',')<0 || i==oldvalue){
			tmpstr += '<option value="'+i+'"'+(i==oldvalue?' selected="selected"':'')+'>'+allfield[i].replace(/</g,'&lt;').replace(/>/g,'&gt;')+'</option>';
		}
	}
	tmpstr += '</select></div>';
	$('thetempMenu_Div').innerHTML = tmpstr;

	showMenu('selectdbtext_'+theid,true,0,2,500,'acqu');
	
}

function selectAcqu(theid){
	var allids = [];
	if (allFilterIds.length>2){
		allids = allFilterIds.substr(1,allFilterIds.length-2).split(',');
	}
	var oldvalue=$('acqu_doinfo_'+theid).value;
	var tmpstr = '<div id="acqu_doinfo_text_'+theid+'_menu" style="display:none;width:150px;height:230px;padding:0;"><select size="5" style="width:150px;height:230px;" onchange="setacqufilterselect('+theid+',this.value)"><option value="-1"'+(oldvalue=='-1'?' selected="selected"':'')+'>全部源文件</option>';
	for (var i=0;i<allids.length;i++){
		var objtitle=$('filter_name_'+allids[i]);
		tmpstr += '<option value="'+allids[i]+'"'+(allids[i]==oldvalue?' selected="selected"':'')+'>'+objtitle.value.replace(/</g,'&lt;').replace(/>/g,'&gt;')+'</option>';
	}
	tmpstr += '</select></div>';
	$('thetempMenu_Div').innerHTML = tmpstr;
	
	showMenu('acqu_doinfo_text_'+theid,true,0,2,500,'acqu');
}

function delectacqu(id){
	confirm('您确实要删除此采集规则？',null,null,'请确认',function(tp){
		if (tp =='ok'){
			var obj = $('acquDiv_'+id);
			if (!obj)return;
			var oldvalue=$('acqu_dbfield_'+id).value;
			doDelObj(obj,function (){
				allAcquIds = allAcquIds.replace(','+id+',',',');
				selectedField = selectedField.replace(','+oldvalue+',',',');
			},0);
		}
	});
}
function setdbinfoselect(theid,newvalue){
	var allfield = getallfield();
	if (!allfield[newvalue])return hideMenu('acqu');
	var obj = $('acqu_dbfield_'+theid);
	if (!obj)return closeMsgBox();;

	var allids = [];
	if (selectedField.length>2){
		allids = selectedField.substr(1,selectedField.length-2).split(',');
	}
	selectedField = selectedField.replace(','+obj.value+',',',');
	obj.value=newvalue;
	selectedField += newvalue+',';
	var obj2 = $('selectdbtext_'+theid);
	obj2.innerHTML = allfield[newvalue];
	//closeMsgBox();
	try{hideMenu('acqu');}catch(e){}
}
function setacqufilterselect(theid,newvalue){
	var obj = $('acqu_doinfo_'+theid);
	if (!obj)return hideMenu('acqu');
	obj.value = newvalue;
	var textobj =$('acqu_doinfo_text_'+theid);
	var titleobj = $('filter_name_'+newvalue);
	if (titleobj){
		textobj.innerHTML = titleobj.value.replace(/</g,'&lt;').replace(/>/g,'&gt;');
	}else{
		obj.value = '-1';
		textobj.innerHTML = '全部源文件';
	}
	hideMenu('acqu');
}

/*
function setdbinfoselect(){
	var obj = $('acquisitionDiv');
	var selects = obj.getElementsByTagName('select');
	var allfield = getallfield();

	var thevaluestring = '|';
	var theidstring = ',';
	for (var i=0;i<selects.length;i++){
		if ((selects[i].id||'').substr(0,13) == 'acqu_dbfield_'){
			if (selects[i].value!='')thevaluestring += selects[i].value +'|';
		}
	}
	for (var i=0;i<selects.length;i++){
		if ((selects[i].id||'').substr(0,13) == 'acqu_dbfield_'){
			var thevalue = selects[i].value;
			var theid = selects[i].id.substr(13);
			selects[i].innerHTML = '';
			if (ie)selects[i].outHTML = '<select id="acqu_dbfield_'+theid+'" onchange="setdbinfoselect()" name="acqu[acqu_'+theid+'][dbfield]"></select>';
	
			selects[i].options[0] = new Option('请选择','');
			for(var j in allfield){
				if (thevaluestring.indexOf('|'+j+'|')==-1 || thevalue==j){
					selects[i].options[selects[i].options.length] = new Option(allfield[j],j);
					if (j==thevalue)selects[i].value=thevalue;
				}
			}
		}
	}
}
*/
</script>


<div id="mytagmain_6" style="display:none;">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
<tr>
	<th class="td1" colspan="2">采集附件</th>
</tr>
<tr>
<td class="td1" align="right" width="150">附件采集设置：</td>
<td class="td2">
<div id="filesDiv"></div>
<input type="button" class="btnl" value="新增附件规则" onclick="addfiles()" />
</td>
</tr>
</table>
</div>
<script type="text/javascript">

var allFileNum = 0;
var allFileIds = ',';
function addfiles(filesdata){
	var obj=$('filesDiv');
	if (!filesdata){
		filesdata = {};
		filesdata['file_'+allFileNum] = {};
	}
	for (var key in filesdata){
		var item = filesdata[key];
		var newobj =$('filesDiv_'+allFileNum);
		if (newobj){
			allFileNum++;
			var newdata = {};
			newdata['file_'+allFileNum] = item;
			addfiles(newdata);
			continue;
		}
		newobj = document.createElement("div");
		newobj.id = 'filesDiv_'+allFileNum;
		newobj.style.overflow = 'hidden';
		newobj.style.width = '100%';
		if (item['doinfo']){
			var tmpv = getfilterbyvalue(item['doinfo']);
			item['doinfo'] = tmpv[0];
			item['doinfo_name'] = tmpv[1];
		}
		newobj.innerHTML = '<div style="padding:4px 3px 5px 3px;"><table border="0" cellpadding="4" cellspacing="1" class="tableborder" style="width:100%">'+
		'<tr><th class="td1" colspan="2">附件设置</th></tr>'+
		'<tr><td class="td1" align="right" width="90">附件标识：</td><td class="td2"><input class="input" type="text" size="20" maxlength="30" id="file_key_'+allFileNum+'" name="file[file_'+allFileNum+'][key]" value="'+key+'" /> 必须唯一，只允许字母数字下划线的组合，且不能全为数字。例如：down_pic</td></tr>'+
		'<tr><td class="td1" align="right">附件别名：</td><td class="td2"><input class="input" type="text" size="20" maxlength="30" onchange="setdoinfoselect()" id="file_name_'+allFileNum+'" name="file[file_'+allFileNum+'][name]" value="'+(item['name']||'附件下载_'+allFileNum).replace(/\"/g,'')+'" /> 例如：下载标题图片</td></tr>'+
//		'<tr><td class="td1" align="right">线程数目：</td><td class="td2"><input class="input" type="text" size="4" maxlength="10" name="file[file_'+allFileNum+'][readnum]" value="'+((item['readnum']>0&&item['readnum']<50)?item['readnum']+'" disabled="disabled':1)+'" /> 默认为1，数值范围：1-50，当线程数大于1时，请确保有大于文件2倍大小的空间。</td></tr>'+
		'<tr><td class="td1" align="right">图片添加水印：</td><td class="td2"><select name="file[file_'+allFileNum+'][iswatermark]"><option value="1"'+(item['iswatermark']==1?' selected="selected':'')+'>是</option><option value="0"'+(item['iswatermark']==0?' selected="selected':'')+'>否</></option></select> 若附件是图片，则进行添加水印处理。</td></tr>'+
		'<tr><td class="td1" align="right">内容匹配范围：</td><td class="td2"><input type="hidden" id="file_doinfo_'+allFileNum+'" value="'+(item['doinfo']||'-1')+'" name="file[file_'+allFileNum+'][doinfo]" /><span id="file_doinfo_text_'+allFileNum+'" class="select" onclick="selectFiles('+allFileNum+')">'+(item['doinfo_name']||'全部源文件')+'</span> 可以到“过滤设置”中添加一个新的过滤项。</td></tr>'+
		'<tr><td class="td1" align="right">匹配规则：</td><td class="td2"><textarea class="input" rows="4" style="width:400px;height:60px;overflow:auto" cols="60" name="file[file_'+allFileNum+'][preg]">'+(item['preg']||'').replace(/</g,'&lt;').replace(/>/g,'&gt;')+'</textarea> <span class="helpicon" title="例如：<font color=red>/&amp;lt;a href=&quot;(.*)&quot;&amp;gt;.*&amp;lt;\\/a&amp;gt;/Uis</font> 可匹配所有超链接<br/>其中，最后的U代表忽略换行，i代表忽略大小写，s代表匹配最小范围<br/>更多参数请查阅php手册中正则“模式语法”">&nbsp;</span>输入匹配的的正则</td></tr>'+
		'<tr><td class="td1" align="right">转换为地址：</td><td class="td2"><input class="input" style="width:400px;overflow:auto" name="file[file_'+allFileNum+'][replace]" value="'+(item['replace']||'').replace(/</g,'&lt;').replace(/>/g,'&gt;')+'" /> 对匹配到的内容部分进行处理</td></tr>'+
		'<tr><td class="td1">&nbsp;</td><td class="td2"><input type="button" class="btn" value="测试规则" /><input type="button" onclick="delectfile('+allFileNum+')" value="删除规则" class="btn" /></td></tr>'+
		'</table><div style="clear:both;"></div></div>';
		obj.appendChild(newobj);
		allFileIds += allFileNum + ',';
		allFileNum ++;
	}
	myqee(obj);
	set_control_fixed();
}


(function (){
	var node = <?php echo Tools::json_encode($data['file']);?>;
	if (!node)return;
	addfiles(node);
})();


function selectFiles(theid){
	var allids = [];
	if (allFilterIds.length>2){
		allids = allFilterIds.substr(1,allFilterIds.length-2).split(',');
	}
	var oldvalue=$('file_doinfo_'+theid).value;
	var tmpstr = '<div id="file_doinfo_text_'+theid+'_menu" style="display:none;width:150px;height:230px;padding:0;"><select size="5" style="width:150px;height:230px;" onchange="setfileselect('+theid+',this.value)"><option value="-1"'+(oldvalue=='-1'?' selected="selected"':'')+'>全部源文件</option>';
	for (var i=0;i<allids.length;i++){
		var objtitle=$('filter_name_'+allids[i]);
		tmpstr += '<option value="'+allids[i]+'"'+(allids[i]==oldvalue?' selected="selected"':'')+'>'+objtitle.value.replace(/</g,'&lt;').replace(/>/g,'&gt;')+'</option>';
	}
	tmpstr += '</select></div>';
	$('thetempMenu_Div').innerHTML = tmpstr;
	
	showMenu('file_doinfo_text_'+theid,true,0,2,500,'acqu');
}

function setfileselect(theid,newvalue){
	var obj = $('file_doinfo_'+theid);
	if (!obj)return hideMenu('acqu');
	obj.value = newvalue;
	var textobj =$('file_doinfo_text_'+theid);
	var titleobj = $('filter_name_'+newvalue);
	if (titleobj){
		textobj.innerHTML = titleobj.value.replace(/</g,'&lt;').replace(/>/g,'&gt;');
	}else{
		obj.value = '-1';
		textobj.innerHTML = '全部源文件';
	}
	hideMenu('acqu');
}


function delectfile(id){
	confirm('您确实要删除此附件规则？',null,null,'请确认',function(tp){
		if (tp =='ok'){
			var obj = $('filesDiv_'+id);
			if (!obj)return;
			var oldvalue=$('file_doinfo_'+id).value;
			doDelObj(obj,function (){
				allFileIds = allFileIds.replace(','+id+',',',');
			},0);
		}
	});
}
</script>


<div id="mytagmain_7" style="display:none;">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
<tr>
	<th class="td1" colspan="2">入库设置</th>
</tr>
<tr>
<td class="td1" align="right" width="150">唯一标识：</td>
<td class="td2">
<table border="0" cellpadding="4" cellspacing="1" style="width:500px" class="tableborder">
<tr>
	<th class="td1" colspan="2">入库设置</th>
</tr>
<tr>
<td class="td1" align="right" width="110">唯一标识：</td>
<td class="td2">
<input type="hidden" name="acqu_node[the_id_name][string]" id="acqu_node[the_id_name][string]" value="<?php echo $data['the_id_name']['string'];?>" />
<span id="show_the_id_name" class="select" onclick="selectAcqu_foridname()">全部源文件</span>
<font class="helpicon" title="唯一标识通常是原信息的ID，<br/>用于在多个采集点之间确认同一个或关联信息的依据。">&nbsp;</font>
</td>
</tr>
<tr>
<td class="td1" align="right">匹配规则：</td>
<td class="td2">
<textarea rows="5" cols="55" name="acqu_node[the_id_name][preg]" class="input"><?php echo $data['the_id_name']['preg'];?></textarea>
</td>
</tr>
<tr><td class="td1" align="right">转换为唯一标识：</td>
<td class="td2"><input class="input" size="40" name="acqu_node[the_id_name][replace]" value="<?php echo $data['the_id_name']['replace'];?>" /></td></tr>
</table>
</td>
</tr>
<tr>
<td class="td1" align="right" width="150">入库时缺少信息时：</td>
<td class="td2">
<?php echo form::dropdown('acqu_node[tohtml_errortype]',array('只要采集规则不允许空项符合条件即入库','放弃入库','强制入库','提示人工录入 '),$data['tohtml_errortype']);?> 
<font class="helpicon" title="缺少信息：例如新闻栏目中，标题为必填，<br/>则结果采集的信息确实标题，则被判断为缺少信息"></font> <font color="#999999">若选择人工录入，则会跳出录入界面。</font>
</td>
</tr>
<tr>
<td class="td1" align="right" width="150">批量入库每次入库条数：</td>
<td class="td2">
<?php echo form::input('acqu_node[tohtml_autonum]',isset($data['tohtml_autonum'])?(int)$data['tohtml_autonum']:50,'size="3" class="input"');?>  
<font color="#999999">默认为50，0表示全部一次性入库。</font>
</td>
</tr>
<tr>
<td class="td1" align="right" width="150">批量入库每组间隔时间：</td>
<td class="td2">
<?php echo form::input('acqu_node[tohtml_limitnum]',isset($data['tohtml_limitnum'])?(int)$data['tohtml_limitnum']:100,'size="3" class="input"');?>  
<font color="#999999">单位毫秒，0表示不停顿。</font>
</td>
</tr>
<tr>
<td class="td1" align="right" width="150">入库完自动发布：</td>
<td class="td2">
<?php echo form::radio('acqu_node[is_autotohtml]',0,$data['is_autotohtml']==0?TRUE:FALSE);?>否 
<?php echo form::radio('acqu_node[is_autotohtml]',1,$data['is_autotohtml']==1?TRUE:FALSE);?>是 
<span class="helpicon" title="前提条件：<br/>必须已选定栏目，且栏目设定为内容页生成HTML">&nbsp;</span>
<font color="#999999">若自动发布则会在入库时生成静态页。</font>
</td>
</tr>
</table>
</div>
<script type="text/javascript">
function selectAcqu_foridname(){
	var allids = [];
	if (allFilterIds.length>2){
		allids = allFilterIds.substr(1,allFilterIds.length-2).split(',');
	}
	var oldvalue=$('acqu_node[the_id_name][string]').value;
	var tmpstr = '<div id="show_the_id_name_menu" style="display:none;width:150px;height:250px;padding:0;"><select size="6" style="width:150px;height:250px;" onchange="setdo_foridname(this.value)"><option value="-1"'+(oldvalue=='-1'?' selected="selected"':'')+'>全部源文件</option><option value="-2"'+(oldvalue=='-2'?' selected="selected"':'')+'>网页URL</option>';
	for (var i=0;i<allids.length;i++){
		var objtitle=$('filter_name_'+allids[i]);
		tmpstr += '<option value="'+allids[i]+'"'+(allids[i]==oldvalue?' selected="selected"':'')+'>'+objtitle.value.replace(/</g,'&lt;').replace(/>/g,'&gt;')+'</option>';
	}
	tmpstr += '</select></div>';


	$('thetempMenu_Div').innerHTML = tmpstr;

	showMenu('show_the_id_name',true,0,2,500,'acqu');
}

function setdo_foridname(newvalue){
	var obj = $('acqu_node[the_id_name][string]');
	if (!obj)return hideMenu('acqu');
	obj.value = newvalue;
	var textobj =$('show_the_id_name');
	var titleobj = $('filter_name_'+newvalue);
	if (titleobj){
		textobj.innerHTML = titleobj.value.replace(/</g,'&lt;').replace(/>/g,'&gt;');
	}else{
		if (obj.value=='-2'){
			textobj.innerHTML = '网页URL';
		}else{
			obj.value = '-1';
			textobj.innerHTML = '全部源文件';
		}
	}
	try{hideMenu('acqu');}catch(e){}
}

setdo_foridname($('acqu_node[the_id_name][string]').value);
</script>

<div style="height:40px">
	<div id="control_div" style="width:100%;min-width:800px" class="control">
	<img src="<?php echo ADMIN_IMGPATH;?>/admin/spacer.gif" style="width:800px;height:0px" /><br/>

<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-top:none;">
<tr>
<td class="td1" width="150">&nbsp;</td>
<td class="td1"><input type="submit" class="bbtn" value="保存节点" /> <input type="button" onclick="goback(-1,'<?php echo Myqee::url('acquisition/node_list/'.$acquisition_id);?>')" class="btns" value="返回" /></td>
</tr>
</table>
</div></div>

</form>
<script type="text/javascript">
	var myhash = document.location.hash.substr(4);
	if (document.location.hash.substr(0,4)=='#tag' && myhash>0 && myhash<8){
		tag('mytag_'+myhash,'mytag','mytagmain');
	}

	set_control_fixed();
	window.onscroll = set_control_fixed;
</script>

<?php View::factory('admin/footer') -> render(TRUE);?>