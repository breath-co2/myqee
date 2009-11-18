<?php
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<script type="text/javascript" src="/images/admin/calendar.js"></script>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('plus/index');?>">插件管理</a> -&gt; <a href="<?php echo Myqee::url('link/index');?>">友情链接管理</a> -&gt; 链接发布
</div>
<script type="text/javascript">
function chkforms(){
	var obj=document.forms['myforms'];
	if (obj['link[name]'].value==''){
		alert("链接名称不能空！");
		return false;
	}
	if (obj['link[url]'].value == '') {
		alert("链接地址不能空！");
		return false;
	}
	return true;
}

function editLogo() {
	var logo = $("logo");
	var file = $("file");
	var obj = document.forms["myforms"]["link[logoHasChanged]"];
	if (logo.style.display == "none") {
		logo.style.display = "block";
		file.style.display = "none";
		obj.value = 0;
	} else {
		logo.style.display = "none";
		file.style.display = "block";
		obj.value = 1;
	}
}


//获取FCKeditor中文本长度
function GetLength(objid){
	var oEditor = FCKeditorAPI.GetInstance(objid) ;
	var oDOM = oEditor.EditorDocument ;
	var iLength ;

	if ( document.all )		// If Internet Explorer.
	{
		iLength = oDOM.body.innerText.length ;
	}
	else					// If Gecko.
	{
		var r = oDOM.createRange() ;
		r.selectNodeContents( oDOM.body ) ;
		iLength = r.toString().length ;
	}

	return iLength;
}
</script>
<form method="post" name="myforms" action="<?php echo Myqee::url('admin/link/save');?>" target="hiddenFrame" onsubmit="return chkforms()" enctype="multipart/form-data">
<input type="hidden" name="link[id]" value="<?php echo $link['id'];?>" />
<input type="hidden" name="link[site_id]" value="<?php echo $link['site_id'];?>" />
<table border="0" cellpadding="4" cellspacing="1" width="96%" align="center" class="tableborder">
<tr>
	<th colspan="2">添加友情链接</th>
</tr>
<tr>
	<td class="td1" align="right" width="120">链接名称：</td>
	<td class="td2"><input name="link[name]" type="text" size="40" class="input" value="<?php echo $link['link_name'];?>" /></td>
</tr>
<tr>
	<td class="td1" align="right" width="120">链接地址：</td>
	<td class="td2">
		<input name="link[url]" type="text" size="40" class="input" value="<?php echo $link['link_url'] ? $link['link_url'] : "http://";?>" />
		请填入链接的网址，以 http:// 开头。
	</td>
</tr>
<tr>
	<td class="td1" align="right" width="120">发布日期：</td>
	<td class="td2"><input name="link[postdate]" type="text" size="20" readonly="readonly" class="input" value="<?php echo $link['postdate']?Date("Y-m-d H:i",$link['postdate']):Date("Y-m-d H:i",$_SERVER['REQUEST_TIME']);?>" onclick="showcalendar(event, this,true);" onfocus="showcalendar(event, this,true);if(this.value=='0000-00-00 0:0:0')this.value=''" /></td>
</tr>
<tr>
	<td class="td1" align="right">LOGO：</td>
	<td class="td2">
		<input name="link[logoHasChanged]" type="hidden" value="0" />
		<div id="logo">
			<img src="/<?php echo $link['link_logo'] ? $link['link_logo'] : 'images/nologo.gif';?>" width="88" height="31" border="0" />
			<div style="height:6px;"></div>
			<input type="button" value="<?php echo $link["link_logo"] ? "修改" : "添加"; ?>" class="btns" onclick="editLogo();" />
		</div>
		<div id="file" style="display:none;">
			<input name="logo" type="file" size="40" /><?php echo $link["link_logo"] ? " <font color=\"red\">删除 LOGO 请留空。</font>" : "" ?>
			<div style="height:6px;"></div>
			<input type="button" value="取消" class="btns" onclick="editLogo();" />
		</div>
	</td>
</tr>
<tr>
	<td class="td1" align="right">已审核：</td>
	<td class="td2">
		<select name="link[audit_flag]">
			<option value="1"<?php if('a'.$link['audit_flag']=='a1'){echo ' selected="selected"';}?>>是</option>
			<option value="0"<?php if('a'.$link['audit_flag']=='a0'){echo ' selected="selected"';}?>>否</option>
		</select>
	</td>
</tr>
<tr>
	<td class="td1" align="right">链接描述：</td>
	<td class="td2"><textarea id="content" name="link[desc]" rows="8" cols="60"><?php echo str_replace('<','&lt;',$link['link_desc']);?></textarea>
</tr>
<tr>
	<td class="td1" align="right">&nbsp;</td>
	<td class="td1"><input type="submit" value="保存链接" class="bbtn" /> <input onclick="goback(-1,'<?php echo Myqee::url('admin/link/index'); ?>')" type="button" value="返回" class="btns" /></td>
</tr>
</table>
</form>


<?php View::factory('admin/footer') -> render(TRUE);?>