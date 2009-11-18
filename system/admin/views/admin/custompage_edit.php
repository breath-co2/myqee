<?php
$page_index = 'custompage';
$page_index = 'info';
if ($custompage ['id'] > 0) {
	$page_title = '修改自定义页';
} else {
	$page_title = '添加自定义页';
}
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<script src="<?php
echo ADMIN_IMGPATH;
?>/admin/tpleditor.js"
	type="text/javascript"></script>
<script type="text/javascript">


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


function  disTpl(istpl){
	var obj = $('showtlpdiv');
	if(istpl.checked==true){
		obj.style.visibility='visible';
	}else{
		obj.style.visibility='hidden';
	}
	
}
var url_base = "<?php echo ADMIN_IMGPATH; ?>";
function chkforms(obj,objid){

	if (obj['custompage[pagename]'].value==''){
		alert("请输入页面名称！",null,null,null,function(){
			obj['custompage[pagename]'].focus();
		});
		return false;
	}
	
	var chkname = new RegExp("^[0-9a-zA-Z\_,]+$");
	if (obj['custompage[filename]'].value=='' || !chkname.test(obj['custompage[filename]'].value)){
		alert("文件名称只允许允许“数字、英文、下划线、逗号”且不能空！",null,null,null,function(){
			obj['custompage[filename]'].focus();
		});
		return false;
	}
	
	return submitForm(obj,objid);
}

</script>

<div class="loaction">您的位置：<a
	href="<?php
	echo Myqee::url ( 'index' );
	?>">管理首页</a> -&gt; <a
	href="<?php
	echo Myqee::url ( 'custompage/index' );
	?>">自定义页面</a> -&gt; <?php
echo $page_title;
?>
</div>
<form method="post" name="myforms"
	action="<?php
	echo Myqee::url ( 'custompage/save/' . $custompage ['id'] );
	?>"
	target="hiddenFrame" 
	onsubmit="return chkforms(this,'custompage[content]')">
<div style="width: 96%; margin: auto;">
<ul class="ul tag">
	<li class="now" id="mytag_1"
		onclick="tag(this.id,'mytag','mytagmain');document.location.hash='#tag1'">基本属性</li>
	<li id="mytag_2"
		onclick="tag(this.id,'mytag','mytagmain');document.location.hash='#tag2'">传入参数</li>
</ul>
</div>
<div style="clear: both"></div>
<div id="mytagmain_1">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
	<tr>
		<th colspan="2">自定义页面设置 - <?php
		echo $page_title;
		?></th>
	</tr>
	<tr>
		<td class="td1" align="right" width="120">页面名称(<font color="red">*</font>)：</td>
		<td class="td2"><?php
		echo form::input ( array('name' => 'custompage[pagename]','id'=>'custompage[pagename]'), $custompage ['pagename'], ' size="25" class="input"' );
		?></td>
	</tr>
	<tr>
		<td class="td1" align="right">页面分类：</td>
		<td class="td2"><?php
		echo form::changeinput ( 'custompage[cate]', $custompage ['cate'], ' size="10" class="input" title="留空则默认"', $pagecate, array ('默认' => '默认' ) );
		?>  &nbsp;<font
			color="#a3a3a3">输入自定义页面分类以区分不同类型自定义页面，留空则为默认分类，每个分类单独文件夹存放,留空存放在根目录。</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">是否启用(<font color="red">*</font>)：</td>
		<td class="td2"><?php
		echo form::dropdown ( array('name' => 'custompage[isuse]','id'=>'custompage[isuse]'), array('1'=>'启用','0'=>'不启用'), $custompage ['isuse'] );
		?></td>
	</tr>
	<tr>
		<td class="td1" align="right">文件路径：</td>
		<td class="td2"><?php
		echo form::changeinput ( 'custompage[filepath]', $custompage ['filepath'], ' size="40" class="input" title="从网站的wwwroot目录算起。留空则是根目录。"', $filepatharr, array ('' => '根目录' ) )
		?> 留空则为根目录</td>
	</tr>
	<tr>
		<td class="td1" align="right">文件名称及后缀(<font color="red">*</font>)：</td>
		<td class="td2">
		<?php 
		echo form::input ( 'custompage[filename]', $custompage ['filename'], ' size="15" class="input" title="文件名，只允许数组字母下划线及英文逗号"' )
		,' ',
		form::dropdown ( 'custompage[filename_suffix]', array ('.html' => '.html',  '.htm' => '.htm','.css' => '.css', '.js' => '.js', '.txt' => '.txt' ), $custompage ['filename_suffix'] );
		?>&nbsp;<font
			color="#a3a3a3"></font></td>
	</tr>
	<tr>
		<td class="td1" align="right">页面模板：</td>
		<td class="td2">启用模板<?php
		echo form::checkbox ( 'custompage[istpl]', '1', $custompage ['istpl'], 'id="custompage[istpl]" onclick="disTpl(this)"' );
		?>&nbsp;<span id="showtlpdiv"><?php
		echo form::dropdown ( 'custompage[tplid]', $tplarray, $custompage ['tplid'], 'id="custompage[tplid]"'.($custompage ['istpl'] == 1 ? '' : ' disabled="disabled"') );
		?>
		<input type="button" value="管理单页模板" class="btnl" onclick="goUrl('<?php echo Myqee::url ( 'template/index/1/page' );?>','_blank')" />
		</span>
		<script>$('custompage[istpl]').onclick();</script>
		</td>
	</tr>
	<tr>
		<td class="td1" align="right">正文内容(<font color="red">*</font>)：</td>
		<td class="td2">
		<div style="padding:3px 3px 0 3px;font-size:0px;line-height:0px;" id="custompage[content]__div">
			<div style="font-size:12px;line-height:auto;" id="custompage[content]__TextDiv">
				<table border="0" cellpadding="0" cellspacing="1" width="100%">
				<tr><td width="35" id="textline_Div">
				<textarea id="textlineDiv" readonly="readonly" style="overflow:hidden;padding:6px 0;text-align:right;line-height:18px;height:400px;width:35px;border:none;background-color:#eee;color:#0000ff;font-family:Fixedsys,verdana,'宋体';cursor:default;" onfocus="this.blur();"></textarea>
				</td><td>
				<?php 
				echo form::textarea(array('name'=>'custompage[content]','class'=>'template_edit'),$custompage['content'],'id="custompage[content]" wrap="off" onscroll="scroll_len()" onkeydown="return checktabkey(event,this)" style="line-height:18px;width:99%;overflow:auto;scrolling:yes;float:left;height:400px;"');
				?>
				</td></tr>
				</table>
			</div>
		</div>
<script type="text/javascript">
var _line_i = 1;
function scroll_len(){
	var txt_main = $('custompage[content]');
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

<?php
if ($custompage['edit_type']==1)
{
	echo "changeEditor('custompage[content]','fckeditor')";
}
?>

</script>
	<div style="clear:both;width:100%;height:6px;overflow:hidden;"></div>
	<div><input type="button" value="切换到文本状态" class="btnl" onclick="changeEditor('custompage[content]','textarea')" /> <input type="button" value="切换到FCKEditor" class="btnl" onclick="changeEditor('custompage[content]','fckeditor')" title="切换到可视化编辑状态编辑会更方便<br/>但有可能会造成部分代码发生结构变化！" /> 

	&nbsp; &nbsp; 默认编辑器：<?php echo form::dropdown('custompage[edit_type]',array('源代码','Fck可视化编辑器'),$custompage['edit_type']);?>
	</div>
	</td>
	</tr>
</table>
</div>
<div id="mytagmain_2" style="display: none;">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
	<tr>
		<th class="td1" colspan="2">参数设置</th>
	</tr>
	<tr>
		<td class="td1" align="right">页面标题：</td>
		<td class="td2"><?php
		echo form::input ( 'custompage[pagetitle]', $custompage ['pagetitle'], 'size="50" class="input" style="width:300px;"' );
		?> 变量名：<?php
		echo form::input ( 'custompage[title_flag]', $custompage ['title_flag']==""?'title':$custompage ['title_flag'], 'size="15" class="input" maxlength="50" title="默认:title，将替换页面中&#38;#123;&#38;#123;title&#38;#125;&#38;#125;的变量<br/>当启用模板时，则将值传入变量$title<br/>下同。"' );
		?></td>
	</tr>
	<tr>
		<td class="td1" align="right" width="120">页面关键字：</td>
		<td class="td2"><span style="display:inline-block;width:313px;"><?php
		echo form::textarea ( 'custompage[keyword]', $custompage ['keyword'], 'size="50" class="input" style="position:absolute;margin-top:-15px;width:300px;height:14px;" onfocus="this.style.height=\'150px\';this.style.zIndex=\'1\';" onblur="this.style.height=\'14px\';this.style.zIndex=\'\';"');
		?></span>变量名：<?php
		echo form::input ( 'custompage[keywords_flag]', $custompage ['keywords_flag']==""?'keywords':$custompage ['keywords_flag'], 'size="15" class="input" maxlength="50" title="默认:keywords"' );
		?></td>
	</tr>
	<tr>
		<td class="td1" align="right">页面描述：</td>
		<td class="td2"><span style="display:inline-block;width:313px;"><?php
		echo form::textarea ( 'custompage[pagedesc]', $custompage ['pagedesc'], 'size="50" class="input" style="position:absolute;margin-top:-15px;width:300px;height:14px;" onfocus="this.style.height=\'150px\';this.style.zIndex=\'1\';" onblur="this.style.height=\'14px\';this.style.zIndex=\'\';"' );
		?></span>变量名：<?php
		echo form::input ( 'custompage[pagedesc_flag]', $custompage ['pagedesc_flag']==""?'description':$custompage ['pagedesc_flag'], 'size="15" class="input" maxlength="50" title="默认:description"' );
		?></td>
	</tr>
	<tr>
		<td class="td1" align="right">正文变量名：</td>
		<td class="td2">
		<?php echo form::input ( 'custompage[content_flag]', $custompage ['content_flag']==""?'content':$custompage ['content_flag'], 'size="15" class="input" maxlength="50" title="默认:content"' );?> <span class="helpicon" title="默认为：content<br/>只有启用页面模板时此项有效。">&nbsp;</span>
		</td>
	</tr>
	<tr>
		<td class="td1" align="right">其他参数：</td>
		<td class="td2">   
      <table  border="0" cellpadding="4" cellspacing="1" align="left" class="tableborder" style="width:760px;" id="myEditorTable">
              <tr id="trHeader">
                <th  class="td1" width="30">&nbsp;</th>
                <th  class="td1" width="140" title="参数名称不能空，例如：自定义参数一">参数名称(可中文)</th>
                <th  class="td1" width="140" title="只允许字母数字下划线，且以字母开头，区分大小写<br/>不允许：this,class,function">替换标签</th>
                <th  class="td1">参数值</th>
                <th  class="td1" width="60">操作</th>
              </tr>
        </table></td>
	</tr>
	<tr>
		<td class="td1" align="right" width="120">&nbsp;</td>
		<td class="td2">
		 <input name='txtTRLastIndex' type='hidden' id='txtTRLastIndex' value="<?php echo $i;?>" />
<input type="button" class="btn" value="添加参数" onclick="addMyTD()" /> 
<input type="button" value="上移选定" onclick="if (!myTable['mytable'])return;myTable['mytable'].up(1);" class="btn" /> 
<input type="button" value="下移选定" class="btn" onclick="if (!myTable['mytable'])return;myTable['mytable'].down();" />
<input type="button" value="按字段名排序" onclick="myTable['mytable'].sort(1);" class="btnl" /> 
</td>

	</tr>
</table>
</div>

<script type="text/javascript">
function delrows(tr,isautodel){
	if (isautodel!='yes'){
		confirm('您确认删除此参数？\n\n是否继续？',450,190,null,function(et){
			if (et =='ok'){
				if (tr){
					tr.parentNode.removeChild(tr);
				}
			}
		});
		return;
	}
	if (tr){
		tr.parentNode.removeChild(tr);
	}
}
	function showEditorTable(){
		var myArray = <?php echo $paramarr;?>;
		var tableInfo= new Array();
		var i = 0;
		var isdisabled = '';
		for (var item in myArray)
		{
			tableInfo[i] = get_tr_html(myArray[item]);
			i++;
		}
		myTable['mytable']=new CreateTable('myEditorTable',tableInfo);
	}
	

	function addMyTD(){
		var tmpValue= get_tr_html();
		var tr = myTable['mytable'].add(tmpValue.length,tmpValue);
		myqee(tr);
	}

	function get_tr_html(myarr){
		myarr = myarr || {};
		var arr = new Array(
			'<input name="custompage[param_name][]" id="custompage[param_name][]" type="input" size="20" class="input" value="'+(myarr['name']||'').toString().replace(/\"/g,'&quot;')+'" />',
			'<input name="custompage[param_flag][]" id="custompage[param_flag][]" type="input" size="20" class="input" value="'+(myarr['flag']||'').toString().replace(/"/g,'&quot;')+'" />',
			"<textarea name='custompage[param_value][]' id='custompage[param_value][]' cols='50' rows='1' class='input' style='position:absolute;margin-top:-12px;height:14px;' onfocus=\"this.style.height='150px';this.style.zIndex='1';\" onblur=\"this.style.height='14px';this.style.zIndex='';\">"+(myarr['value']||'').toString().replace(/</g,'&lt;')+"</textarea>",
			"<center><input type='button' class='btns' value='删除' onclick=\"delrows(this.parentNode.parentNode.parentNode)\"/></center>"
		);
		return arr;
	}


	showEditorTable();

if (document.location.hash =='#tag1'||document.location.hash =='#tag2'){
	tag('mytag_'+document.location.hash.substr(4),'mytag','mytagmain');
}
</script>

<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-top:none;">
	
	<tr>
		<td class="td1" align="right" width="120">&nbsp;</td>
		<td class="td1"><input type="submit" value="保存页面" class="bbtn" /> <input
			onclick="goback(-1,'<?php
			echo Myqee::url ( 'custompage/index' )?>')"
			type="button" value="返回" class="btns" /></td>
	</tr>
</table>
</form>
<?php View::factory('admin/footer') -> render(TRUE);?>