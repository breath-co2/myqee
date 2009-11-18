<?php
$page_index = 'template';
//$page_title = $template?'编辑'.$template['title']:"添加新模板";
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<script src="<?php echo ADMIN_IMGPATH;?>/admin/tpleditor.js" type="text/javascript"></script>
<script src="<?php echo ADMIN_IMGPATH;?>/admin/highlight/js/codemirror.js" type="text/javascript"></script>
<script type="text/javascript">
var url_base = '<?php echo ADMIN_IMGPATH;?>';
/*
function FCKeditor_OnComplete(editorInstance){
	editorInstance.SwitchEditMode(false, true);
	var val = editorInstance.StartupValue;
	if (val) editorInstance.SetHTML(val, true);
}
*/
function chkforms(obj,objid){
	if (obj['template[tplname]'].value==''){
		alert("请选择输入模板名称！",null,null,null,function(){
			obj['template[tplname]'].focus();
		});
		return false;
	}
	var chkname = new RegExp("^[0-9a-zA-Z\_\/]+$");
	if (obj['template[filename]'].value=='' || !chkname.test(obj['template[filename]'].value)){
		alert("模板组名称只允许允许“数字、英文、下划线”且不能空！",null,null,null,function(){
			obj['template[filename]'].focus();
		});
		return false;
	}
	var issubmit =  submitForm(obj,objid);

	$('overfile').value='';
	return issubmit;
}

</script>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('template/index');?>">模板管理</a> -&gt; <?php echo $page_title;?>
</div>
<form method="post" id="myforms" name="myforms" action="<?php echo Myqee::url('template/save/'.$template['id']);?>" target="hiddenFrame" onsubmit="return chkforms(this,'template[content]');">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
<tr>
	<th colspan="2">模板设置 - <?php echo $page_title;?></th>
</tr>
<tr>
	<td class="td1" align="right" width="120">模板名称：</td>
	<td class="td2"><?php echo form::input('template[tplname]',$template['tplname'],' size="25" class="input"');?></td>
</tr>
<tr>
	<td class="td1" align="right">是否启用：</td>
	<td class="td2">
		<?php echo form::dropdown('template[isuse]',array(1=>'是',0=>'否'),$template['isuse']);?>
	</td>
</tr>
<tr>
	<td class="td1" align="right">模板文件名：</td>
	<td class="td2"><?php echo form::input('template[filename]',$template['filename'],' size="30" class="input" title="可以包括文件夹目录"').' '.form::dropdown('template[filename_suffix]',$allsuffix,$template['filename_suffix']);?>&nbsp;<font color="#a3a3a3">模板将存放在views/[所属模板组]目录，同一模板组不能有重名文件</font></td>
</tr>
<tr>
	<td class="td1" align="right">模板分类：</td>
	<td class="td2"><?php echo form::changeinput('template[cate]',$template['cate'],' size="15" class="input"',$tplcate,array('默认'=>'默认'));?> &nbsp;<font color="#a3a3a3">输入模板分类以区分不同类型模板，留空则为默认分类</font></td>
</tr>
<tr>
	<td class="td1" align="right">模板类型：</td>
	<td class="td2">
<script type="text/javascript">
function changetype(){
	var obj=$('template[type]');
	if (!obj)return;
	if (obj.value=='frame'){
		$('templateFrameCodeDiv').style.display='block';
		$('template[isuseframe]').disabled=true;
	}
}
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


</script>
		<?php echo form::dropdown('template[type]',array('页面模板'=>array('cover'=>'封面模板','list'=>'列表模板','content'=>'内容模板','search'=>'搜索模板','page'=>'单页模板'),'调用模板'=>array('frame'=>'框架模板','block'=>'区域模板')),$template['type'],'id="template[type]" onchange="changetype()"');?> &nbsp;<font color="#a3a3a3">“页面模板”是用来直接被信息调用的模板，“调用模板”是用来被页面模板调用的模板</font>
	</td>
</tr>
<tr>
	<td class="td1" align="right">模板代码：<br/><img src="<?php echo ADMIN_IMGPATH;?>/admin/spacer.gif" width="120" height="1" /></td>
	<td class="td2">
	<div style="padding:3px 3px 0 3px;font-size:0px;line-height:0px;" id="template[content]__div">
		<div style="font-size:12px;line-height:auto;" id="template[content]__TextDiv">
			<table border="0" cellpadding="0" cellspacing="1" width="100%">
			<tr><td width="35" id="textline_Div">
			<textarea id="textlineDiv" readonly="readonly" style="overflow:hidden;padding:6px 0;text-align:right;line-height:18px;height:400px;width:35px;border:none;background-color:#eee;color:#0000ff;font-family:Fixedsys,verdana,'宋体';cursor:default;" onfocus="this.blur();"></textarea>
			</td><td>
			<?php 
			echo form::textarea(array('name'=>'template[content]','class'=>'template_edit'),$template['content'],'id="template[content]" wrap="off" onscroll="scroll_len()" onkeydown="return checktabkey(event,this)" style="line-height:18px;width:99%;overflow:auto;scrolling:yes;float:left;height:400px;"');
			?>
			</td></tr>
			</table>
		</div>
	</div>
<script type="text/javascript">
var _line_i = 1;
function scroll_len(){
	var txt_main = $('template[content]');
	var txt_ln = $('textlineDiv');
	txt_ln.scrollTop = txt_main.scrollTop;
	while(_line_i<30 || txt_ln.scrollTop != txt_main.scrollTop) 
	{
		txt_ln.value += (_line_i++) + '\n';
		txt_ln.scrollTop = txt_main.scrollTop;
	}
	return;
}
scroll_len();
</script>
<?php
/*
	$htmleditor = Myqee::config('core.tplhtmleditor');
	if ($htmleditor == 'fckeditor'){
		echo '<script>changeEditor("template[content]","fckeditor")</script>';
	}else if ($htmleditor == 'highlight'){
		echo '<script>changeEditor("template[content]","highlight")</script>';
	}else if ($htmleditor == 'editlive'){
		echo '<script>changeEditor("template[content]","editlive")</script>';
	}
*/
?>
	<div style="clear:both;width:100%;height:6px;overflow:hidden;"></div>
	<div><input type="button" value="切换到文本状态" class="btnl" onclick="changeEditor('template[content]','textarea')" /> <input type="button" value="切换到高亮状态" class="btnl" title="代码视图，具有高亮功能，在模板代码量较大时请慎用！" onclick="changeEditor('template[content]','highlight')" /> <input type="button" value="切换到FCKEditor" class="btnl" onclick="changeEditor('template[content]','fckeditor')" title="切换到可视化编辑状态编辑会更方便<br/>但有可能会造成部分代码发生结构变化！" /> <input type="button" value="切换到EditLive!" class="btnl" title="EditLive是一个JAVA编辑器，<br/>具有强大的网页可视化、代码编辑视图，<br/>需要安装插件才可以使用，可到EditLive官方网站或麦琪官方网站下载。" onclick="changeEditor('template[content]','editlive')" />
	<font color="#929292"><br/>&nbsp;代码量较大时请慎用高亮代码状态！可视编辑器只编辑&lt;body&gt;标签中代码，各个编辑器之间切换会同步代码！</font></div>
	</td>
</tr>
<tr>
	<td class="td1" align="right">文件版本检查：</td>
	<td class="td2"><select name="overfile" id="overfile">
<option value="" selected="selected">自动检测</option>
<option value="yes">强制覆盖</option>
</select>  
&nbsp;<font color="#a3a3a3">若选择强制覆盖，则会将存于views目录里的视图文件覆盖掉！</font></td>
</tr>

</table>
<div style="height:40px">
	<div id="control_div" style="width:100%;min-width:800px" class="control">
	<img src="<?php echo ADMIN_IMGPATH;?>/admin/spacer.gif" style="width:800px;height:0px" /><br/>
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-top:none;">
<tr>
	<td class="td1" align="right" width="120">&nbsp;</td>
	<td class="td1"><input type="submit" value="保存模板" class="bbtn"<?php if (EDIT_TEMPLATE !== 1){echo ' disabled="disabled"';}?> /> 
	<input onclick="goback(-1,'<?php echo Myqee::url('template/index') ?>')" type="button" value="返回" class="btns" /> 
	<?php 
	if (EDIT_TEMPLATE !== 1){
		echo '<font color="red">当前设置不允许修改模板，您只能查看模板内容！</font>';
	}
	?>
	</td>
</tr>
</table>
</div></div>
</form>


<br/>
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
<tr>
<td class="td1" align="right" width="120">什么是框架模板？</td>
<td class="td2">框架模板是网站的结构性模板，使用框架模板的最大好处就是能够最大程度减小重复模板的使用，方便日后修改。
</td>
</tr>
<tr>
<td class="td1" align="right">什么是区域模板？</td>
<td class="td2">区域模板通常是用于数据读取的功能模板块，例如网页的头、尾，页面中的列表项、排行榜等等。
</td>
</tr>
<tr>
<td class="td1" align="right">调用模板的区分</td>
<td class="td2">页面调用模板是根据模板ID来调用的，而模板内内嵌模板调用是直接根据模板名称来调用的。<br/>
例如：首页选用的是ID=1的模板，则不管模板文件名或后缀怎么变换都不会影响；<br/>
再比如：有3个模板ID分别为1,2,3，文件名分别为a.php,b.php,c.php，本来ID=1的模板（即a.php）调用了b.php文件，则它成功加载的是ID=2的那个模板，而假如把ID=2的那个文件名换成了d.php，再将ID=3的那个模板文件名换成b.php，这时候a.php调用b.php实际上是调用的ID=3的那个模板了！
</td>
</tr>
</table>


<script type="text/javascript">
	window.onload = window.onscroll = function(){set_control_fixed(240)};
</script>


<?php View::factory('admin/footer') -> render(TRUE);?>

<!--[if lte IE 6]>
<script type="text/javascript">
//在IE6里，会出现第一次鼠标移动到菜单上，模板编辑区域会闪烁一下，导致卷去的位子发生变化，不知为何。
//下面这样做可以解决问题
	showMenu('menu_index');
	hideMenu();
</script>
<![endif]--> 