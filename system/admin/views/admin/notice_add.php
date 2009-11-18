<?php
$page_index = 'plus';
$page_title = '公告发布';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<script type="text/javascript" src="<?php echo ADMIN_IMGPATH;?>/admin/calendar.js"></script>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('plus/index');?>">插件管理</a> -&gt; <a href="<?php echo Myqee::url('notice/index');?>">公告管理</a> -&gt; 公告发布
</div>
<script type="text/javascript">
function chkforms(){
	var obj=document.forms['myforms'];
	if (obj['notice[title]'].value==''){
		alert("标题不能空！");
		return false;
	}
	if (GetLength('notice[content]')==0){
		alert("内容不能空！");
		return false;
	}
	return true;
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
<form method="post" name="myforms" action="<?php echo Myqee::url('notice/save');?>" target="hiddenFrame" onsubmit="return chkforms()">
<input type="hidden" name="notice[id]" value="<?php echo $notice['id']?>" />
<table border="0" cellpadding="4" cellspacing="1" width="96%" align="center" class="tableborder">
<tr>
	<th colspan="2">公告信息发布</th>
</tr>
<tr>
	<td class="td1" align="right" width="120">公告标题：</td>
	<td class="td2"><input name="notice[title]" type="text" size="40" class="input" value="<?php echo $notice['title']?>" /></td>
</tr>
<tr>
	<td class="td1" align="right" width="120">发布日期：</td>
	<td class="td2"><?php echo form::timeinput(array('name'=>'notice[postdate]','class'=>'input','size'=>20,'time'=>1,'readonly'=>'readonly'),$notice['postdate']?$notice['postdate']:$_SERVER['REQUEST_TIME']);?></td>
</tr>
<tr>
	<td class="td1" align="right">首页显示：</td>
	<td class="td2">
	<select name="notice[indexshow]">
		<option value="1"<?php if('a'.$notice['indexshow']=='a1'){echo ' selected="selected"';}?>>是</option>
		<option value="0"<?php if('a'.$notice['indexshow']=='a0'){echo ' selected="selected"';}?>>否</option>
	  </select>
	  </td>
</tr>
<tr>
	<td class="td1" align="right">公告正文：</td>
	<td class="td2"><textarea id="content" name="notice[content]" style="display:none;"><?php echo html::specialchars($notice['content']);?></textarea>
	<input id="notice[content]___Config" value="EnterMode=div&ShiftEnterMode=div" style="display: none;" type="hidden">
	<iframe id="notice[content]___Frame" src="<?php echo ADMIN_IMGPATH;?>/admin/fckeditor/editor/fckeditor.html?InstanceName=notice[content]&Toolbar=Default" frameBorder="no" width="620" scrolling="no" height="380"></iframe></td>
</tr>
<tr>
	<td class="td1" align="right">&nbsp;</td>
	<td class="td1"><input type="submit" value="保存公告" class="bbtn" /> <input onclick="goback(-1,'<?php echo Myqee::url('notice/index') ?>')" type="button" value="返回" class="btns" /></td>
</tr>
</table>
</form>


<?php View::factory('admin/footer') -> render(TRUE);?>