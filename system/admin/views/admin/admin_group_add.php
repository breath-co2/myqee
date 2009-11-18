<?php
$page_index = 'member';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<style type="text/css">
.cptul li{float:left;width:140px;padding:3px 0;margin-right:1em;display:inline-block;overflow:hidden;}
</style>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('member/index');?>">用户管理</a> -&gt; <a href="<?php echo Myqee::url('admin/mylist');?>">管理员管理</a> -&gt; <a href="<?php echo Myqee::url('admin/group_list');?>">管理组列表</a> -&gt; 修改/添加管理员
</div>
<script type="text/javascript">
function chkforms(){
	var obj=document.forms['myforms'];
	if (obj['group[groupname]'].value==''){
		alert("管理组名称不能空！");
		return false;
	}
	return true;
}

function checkedall(obj){
	var thekey = obj.id.substr(11);
	var tableobj = $('cpttable_'+thekey);
	if (!tableobj)return;
	changecheckbox(tableobj,obj.checked);
}

function changecheckbox(obj,ischecked,type){
	if (!obj)return;
	var allcheckbox = obj.getElementsByTagName('input');
	if (allcheckbox.length<=1)return;
	type = type || 'checked';
	for (var i in allcheckbox){
		allcheckbox[i][type] = ischecked;
		if (type=='checked' && allcheckbox[i].indeterminate){
			allcheckbox[i].indeterminate = false;
		}
	}
}

function changesub(theid){
	var parcheckobj = $('competence_'+theid);
	if (!parcheckobj)return;
	var ulobj = $('subcpt_'+theid);
	if (!ulobj)return;
	var allcheckbox = ulobj.getElementsByTagName('input');
	var havecheecked = false;
	var havenocheecked = false;
	for (var i=0;i<allcheckbox.length;i++){
		if (allcheckbox[i].checked){
			havecheecked = true;
		}else{
			havenocheecked = true;
		}
	}
	if (havenocheecked==false){
		parcheckobj.checked = true;
		parcheckobj.indeterminate = false;
	}else{
		parcheckobj.checked = false;
		if (havecheecked==true){
			parcheckobj.indeterminate=true;
		}else{
			parcheckobj.indeterminate=false;
		}
	}
}
</script>
<form method="post" name="myforms" action="<?php echo Myqee::url('admin/group_save');?>" target="hiddenFrame" onsubmit="return chkforms()">
<input type="hidden" name="group[id]" value="<?php echo $group['id']?>" />
<div class="mainTable" >
<ul class="ul tag">
	<li class="now" id="mytag_1" onclick="tag(this.id,'mytag','mytagmain');document.location.hash='#tag1'">基本设置</li>
	<li id="mytag_2" onclick="tag(this.id,'mytag','mytagmain');document.location.hash='#tag2'">权限设置</li>
</ul>
</div>
<div style="clear:both"></div>
<div id="mytagmain_1">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
<tr>
	<th colspan="2">基本设置管理</th>
</tr>
<tr>
	<td class="td1" align="right" width="120">管理组名称：</td>
	<td class="td2"><input name="group[groupname]" type="text" size="20" class="input" value="<?php echo $group['groupname']?>" /></td>
</tr>
<tr>
	<td class="td1" align="right" width="120">允许操作的站点：</td>
	<td class="td2"><?php echo form::dropdown('group[site][]',$allsite,$group['site'],'size="6" multiple="multiple" style="width:300px;"')?> 
	同时按Ctrl或Shift键可多选</td>
</tr>
<tr>
	<td class="td1" align="right" width="120">默认操作的站点：</td>
	<td class="td2"><?php
	$allsite2 = array('0'=>'主站点 - (管理全部)');
	foreach($allsite as $k=>$v){
		if ($k!='-ALL-')$allsite2[$k] = $v;
	}
	echo form::dropdown('group[defaultsite]',$allsite2,$group['defaultsite'],'size="1" style="width:300px;"')?> 
	必须是上面拥有权限的站点</td>
</tr>
<tr>
	<td class="td1" align="right" width="120">允许操作的栏目：</td>
	<td class="td2"><?php echo form::classlist('group[class][]',$classtree,'size="15" multiple="multiple" style="width:300px;"',$group['class'],array('-ALL-'=>'├-----------------全部栏目----------------------'),false);?>
	同时按Ctrl或Shift键可多选。</td>
</tr>
<tr>
	<td class="td1" align="right" width="120">允许操作的数据表：</td>
	<td class="td2"><?php echo form::dropdown('group[db][]',$alldb,$group['db'],'size="10" multiple="multiple" style="width:300px;"')?>
	同时按Ctrl或Shift键可多选。</td>
</tr>
</table>
</div>

<div id="mytagmain_2" style="display:none;">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
<tr>
	<th colspan="2">管理组权限设置</th>
</tr>
<tr>
	<td class="td1" align="right" width="120">权限设定：</td>
	<td class="td2" id="competenceTD">
	<?php
if (is_array($competence)){
	foreach ($competence as $key => $value){
		if (!$value)continue;
		echo '<table border="0" cellpadding="4" cellspacing="1" style="margin-bottom:6px;" class="tableborder" id="cpttable_',$key,'"><tr>
		<td class="td1" style="font-weight:bold;"><input type="checkbox" id="competence_',$key,'" onclick="checkedall(this)" />',
		$value['name'],'</td></tr>';
		if (is_array($value['sub'])){
			echo '<tr><td class="td2"><ul class="ul cptul" id="subcpt_',$key,'">';
			foreach ($value['sub'] as $k => $v){
				echo '<li>',form::checkbox('competence['.$key.']['.$k.']',1,$group['competence'][$key][$k]?TRUE:FALSE,'onclick="changesub(\''.$key.'\')"'),$v,'</li>';
			}
			echo '</ul></td></tr>';
		}
		echo '</table><script>changesub(\'',$key.'\');</script>';
	}
}
	?>
	<input type="checkbox" onclick="changecheckbox($('competenceTD'),this.checked)" /><b>全选</b>
</td>
</tr>
</table>
</div>
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-top:none;">
<tr>
	<td class="td1" align="right" width="120">&nbsp;</td>
	<td class="td1"><input type="submit" value="保存修改" class="bbtn" /> <input onclick="goback(-1,'<?php echo Myqee::url('admin/group_list') ?>')" type="button" value="返回" class="btns" /></td>
</tr>
</table>
</form>

<script type="text/javascript">
if (document.location.hash =='#tag1'||document.location.hash =='#tag2'){
	tag('mytag_'+document.location.hash.substr(4),'mytag','mytagmain');
}
</script>

<?php View::factory('admin/footer') -> render(TRUE);?>