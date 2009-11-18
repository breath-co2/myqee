<?php
$page_index = 'index';
$page_title = $site['id']?'修改站点':'新建站点';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('site/index');?>">站点管理</a> -&gt; <?php echo $page_title;?>
</div>

<form method="post" name="myforms" action="<?php echo Myqee::url('site/save/'.$site['id']);?>" target="hiddenFrame">
<div class="mainTable">
<ul class="ul tag" id="mytag">
	<li class="now" id="mytag_1" onclick="tag(this.id,'mytag','mytagmain','mytag');document.location.hash='#tag1'">基本属性</li>
	<li id="mytag_2" onclick="tag(this.id,'mytag','mytagmain','mytag');document.location.hash='#tag2'">参数设置</li>
	<!-- <li id="mytag_3" onclick="tag(this.id,'mytag','mytagmain','mytag');document.location.hash='#tag3'">其它设置</li> -->
</ul>
</div>

<div style="clear:both"></div>

<div id="mytagmain_1">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
	<tr>
		<th class="td1" colspan="2">基本属性 - <?php echo $page_title;?></th>
	</tr>
	<tr>
		<td class="td1" align="right" width="150">子站点名称：</td>
		<td class="td2"><?php echo form::input('site[sitename]',$site['sitename'],'class="input" size="30"');?>  &nbsp;<font color="#a3a3a3">例如：我的新站点</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">排序：</td>
		<td class="td2"><input type="text" name="site[myorder]" size="4" class="input" value="<?php echo (int)$site['myorder'];?>" />  &nbsp;<font color="#a3a3a3">数值越小越前</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">是否启用：</td>
		<td class="td2"><?php echo form::dropdown('site[isuse]',array('1'=>'是','0'=>'否'),$site['isuse']);?> &nbsp;<font color="#a3a3a3">网站关闭后，将不能对它进行操作！</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">绑定模板组：</td>
		<td class="td2"><?php echo form::dropdown('site[config][template_group]',$tplgroup,$site['config']['template_group'],'id="template_group" onchange="change_tpl()"'); ?>
		&nbsp;<font color="#a3a3a3">绑定模板组后，此站点下的栏目将使用此模板组</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">子站点首页模板：</td>
		<td class="td2"><span id="indextplDiv"></span></td>
	</tr>
	<tr>
		<td class="td1" align="right">站点域名：</td>
		<td class="td2"><?php echo form::input('site[sitehost]',$site['sitehost'],'class="input" size="30"');?> &nbsp;<font color="#a3a3a3">例如：php.com，留空则与主站相同</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">站点网页路径：</td>
		<td class="td2"><?php echo form::input('site[siteurl]',$site['siteurl'],'class="input" size="30"');?> &nbsp;<font color="#a3a3a3">
		请用"/"结尾,例如：/mysite/</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">网站说明：</td>
		<td class="td2"><?php echo form::textarea('site[content]',$site['content'],'class="input" cols="50" rows="4"');?></td>
	</tr>
</table>
</div>

<script type="text/javascript">
var _tplArr = {};
function change_tpl(tpl){
	var v = $('template_group').value;
	if (_tplArr[v]){
		var obj = $('indextplDiv').innerHTML = _tplArr[v];
		return;
	}
	ajax.requestFile = "<?php echo Myqee::url('site/get_tlphtml');?>?group="+v;
	ajax.method = 'GET';
	ajax.onCompletion = function (){
		var arr;
		var obj=$('indextplDiv');
		if (this.response.substr(0,5)!='HTML='){
			_alert('读取模板列表失败,可能已登录超时，请重试！');
			if (obj){
				obj.innerHTML ='<input type="button" value="重试" class="btns" onclick="change_tpl(\''+tpl+'\')" />';
				myqee(obj);
			}
		}else{
			obj.innerHTML = _tplArr[v] = this.response.substr(5);
			if (tpl){
				var obj1 = $('index_tpl');
				if (obj1)obj1.value = tpl;
				obj1 = null;
			}
		}
		obj = null;
	}
	ajax.runAJAX();
}
change_tpl('<?php echo $site['config']['indexpage']['tpl'];?>');
</script>
<div id="mytagmain_2" style="display:none;">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
	<tr>
		<th class="td1" colspan="2">参数设置</th>
	</tr>
	<tr>
		<td class="td1" align="right" width="150">启用站点首页：</td>
		<td class="td2"><?php echo form::dropdown('site[config][indexpage][isuse]',array('1'=>'启用','0'=>'禁用'),$site['config']['indexpage']['isuse']) ?> 
		<font color="#999999">只有启用，下面的设置才有效。</font></td>
	</tr>
	<tr>
		<td class="td1" align="right" width="150">子站点首页文件名：</td>
		<td class="td2"><?php echo form::input('site[config][indexpage][filename]',$site['config']['indexpage']['filename'],'size="20" class="input"') ?> 
		<span class="helpicon" title="只允许字母数字及_-.(),~等">&nbsp;</span> <font color="#999999">留空则为默认，例如：“index.html”</font></td>
	</tr>
	<tr>
		<td class="td1" align="right" width="150">子站点首页目录：</td>
		<td class="td2"><?php echo form::input('site[config][indexpage][filepath]',$site['config']['indexpage']['filepath'],'size="30" class="input"') ?> 
		<span class="helpicon" title="例如:<br/><font color=red>E:/wwwroot/mysite/<br/>/wwwroot/mysite/<br/>test/</font><br/>上例中，test/这个将被定义到wwwroot目录下的test目录<br/>可以在网站wwwroot目录以内，网站程序以外。<br/>不允许指定到不存在目录"></span><font color="#999999">留空则为wwwroot目录</font></td>
	</tr>
</table>
</div>
<div id="mytagmain_3" style="display:none;">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
	<tr>
		<th class="td1" colspan="2">绑定数据表、模型、栏目</th>
	</tr>
	<tr>
		<td class="td1" align="right" width="150">请选择栏目：</td>
		<td class="td2">
		
		</td>
	</tr>
</table>
<!-- 
<script type="text/javascript">
function changedb(){
	var checked_db = get_select_value($('dbset'));
	if (_myModelArr[checked_db]){
		$('modelsetDiv').innerHTML = _myModelArr[checked_db];
		changemodel();
		return;
	}
	run_ajax('model',checked_db,get_select_value($('modelset')));
}

function changemodel(){
	var checked_model = get_select_value($('modelset'));
	var checked_model2 = checked_model+'|||'+get_select_value($('dbset'));
	if (_myClassArr[checked_model2]){
		$('classsetDiv').innerHTML = _myClassArr[checked_model2];
		return;
	}
	run_ajax('class',checked_model,get_select_value($('classset')));
}

function get_select_value(obj){
	var myvalue = [];
	for (var i=0;i<obj.options.length;i++){
		if (obj.options[i].value==''){
			if (obj.options[i].selected){
				return '';
			}
		}
		if (obj.options[i].selected){
			myvalue[myvalue.length] = obj.options[i].value;
		}
	}
	return myvalue.join(',');
}


function run_ajax(type,value,checked){
	type = type=='class'?'class':'model';
	ajax.requestFile = '<?php echo Myqee::url('site/get_html');?>?type=my'+type+'&value='+value+'&checked='+checked;
	if (type=='class'){
		ajax.requestFile += '&db='+get_select_value($('dbset'));
	}
	ajax.method = 'GET';
	ajax.onCompletion = function (){
		$(type+'setDiv').innerHTML = this.response;
		if (type=='model'){
			_myModelArr[value] = this.response;
			changemodel();
		}else{
			_myClassArr[get_select_value($('modelset'))+'|||'+get_select_value($('dbset'))] = this.response;
		}
	}
	ajax.runAJAX();
}

var _myModelArr = {};
_myModelArr['<?php echo is_array($site['model'])?implode(',',$site['model']):'';?>'] = $('modelsetDiv').innerHTML;
var _myClassArr = {};
_myClassArr['<?php echo is_array($site['model'])?implode(',',$site['model']):'';?>|||<?php echo is_array($site['class'])?implode(',',$site['class']):'';?>'] = $('classsetDiv').innerHTML;

</script>
 -->
</div>
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-top:none;">
	<tr>
		<td class="td1" width="150">&nbsp;</td>
		<td class="td1"><input type="submit" class="bbtn" value="保存站点" /> <input onclick="goback(-1,'<?php echo Myqee::url('site/index') ?>')" type="button" value="返回" class="btns" /></td>
	</tr>
</table>

</form>

<script type="text/javascript">
if (document.location.hash =='#tag1'||document.location.hash =='#tag2'||document.location.hash =='#tag3'||document.location.hash =='#tag4'){
	tag('mytag_'+document.location.hash.substr(4),'mytag','mytagmain');
}
</script>
<?php View::factory('admin/footer') -> render(TRUE);?>