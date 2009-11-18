<?php View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);?>
<?php include 'page_menu.php';?>
<script src="/images/highlight/js/codemirror.js" type="text/javascript"></script>
<script type="text/javascript">
function chkforms(){
	var obj=document.forms['myforms'];
	if (obj['page[title]'].value==''){
		alert("页面名称不能空！");
		return false;
	}
	if (obj['page[title]'].value==''){
		alert("HTML内容不能空！");
		return false;
	}
	return true;
}
</script>
<br/>
<form method="post" name="myforms" action="<?php echo Myqee::url('admin/page/save');?>" target="hiddenFrame" onsubmit="return chkforms()">
<input type="hidden" name="page[id]" value="<?php echo $page['id']?>" />
<table border="0" cellpadding="4" cellspacing="1" width="96%" align="center" class="tableborder">
<tr>
	<th colspan="2">联盟页面管理</th>
</tr>
<tr>
	<td class="td1" align="right" width="120">页面名称：</td>
	<td class="td2"><input name="page[title]" type="text" size="30" class="input" value="<?php echo $page['title']?>" /> <font color="red">*</font></td>
</tr>
<tr>
	<td class="td1" align="right" width="120">页面文件名：</td>
	<td class="td2"><input name="page[filename]" type="text" size="30" class="input" value="<?php echo $page['filename']?>" /> <font color="red">*</font>页面文件名，通常以.htm结尾</td>
</tr>
<tr>
	<td class="td1" align="right" width="120">所属分类：</td>
	<td class="td2"><input name="page[classname]" type="text" size="10" class="input" value="<?php echo $page['classname']?>" /> 填写不同分类名称在生成时可以分批处理</td>
</tr>
<tr>
	<td class="td1" align="right" width="120">生成文件路径：</td>
	<td class="td2"><input name="page[filepath]" type="text" size="30" class="input" value="<?php echo $page['filepath']?>" /> <font color="red">*</font> 生成静态页面存放在服务器上的路径，以/结尾，<font color="#CC0099">通常填“/”即可</font></td>
</tr>
<tr>
	<td class="td1" align="right" width="120">页面访问路径：</td>
	<td class="td2"><input name="page[urlpath]" type="text" size="30" class="input" value="<?php echo $page['urlpath']?>" /> 网页访问路径，可以http://开头，以/结尾，<font color="#CC0099">一般情况与上面相同</font></td>
</tr>
<tr>
	<td class="td1" align="right">生成静态页面：</td>
	<td class="td2">
	<select name="page[istohtml]">
		<option value="1"<?php if('a'.$page['istohtml']=='a1'){echo ' selected="selected"';}?>>是</option>
		<option value="0"<?php if('a'.$page['istohtml']=='a0'){echo ' selected="selected"';}?>>否</option>
	  </select> 选择是，将生成静态页面，“生成文件路径”和“页面访问路径”将失效
	  </td>
</tr>
<tr>
	<td class="td1" align="right">是否启用：</td>
	<td class="td2">
	<select name="page[isshow]">
		<option value="1"<?php if('a'.$page['isshow']=='a1'){echo ' selected="selected"';}?>>是</option>
		<option value="0"<?php if('a'.$page['isshow']=='a0'){echo ' selected="selected"';}?>>否</option>
	  </select> 选择“否”用户将不能访问此页
	  </td>
</tr>
<tr>
	<td class="td1" align="right">页面模板：</td>
	<td class="td2"><div style="border-left:1px solid #666;border-top:1px solid #666;border-bottom:1px solid #ccc;border-right:1px solid #ccc;padding:2px;"><textarea id="content" name="page[content]" style="width:100%;height:450px;"><?php echo str_replace('<','&lt;',$page['content'])?></textarea></div></td>
</tr>
<tr>
	<td class="td1" align="right">&nbsp;</td>
	<td class="td1"><input type="submit" value="保存页面" class="bbtn" /> <input onclick="goback(-1,'<?php echo Myqee::url('admin/page/index') ?>')" type="button" value="返回" class="btns" /> 单页保存时会立即重新生成，列表页须保存后重新生成</td>
</tr>
</table>
</form>
<script type="text/javascript">
if($("content").value.length<15000){
	setTimeout(function(){
	var editor = CodeMirror.fromTextArea('content', {
		height: "450px",
		parserfile: "parsexml.js",
		stylesheet: "/images/highlight/css/xmlcolors.css",
		path: "/images/highlight/js/",
		continuousScanning: 500
	});
	},100);
}
</script>
 
<?php View::factory('admin/footer') -> render(TRUE);?>