<?php
$page_index = 'custompage';
$page_index = 'info';
if ($custompage ['id'] > 0) {
	$page_title = '修改自定义列表';
} else {
	$page_title = '添加自定义列表';
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


function chkforms(obj){

	if (obj['customlist[pagename]'].value==''){
		alert("请输入列表名称！",null,null,null,function(){
			obj['customlist[pagename]'].focus();
		});
		return false;
	}
	
	var chkname = new RegExp("^[0-9a-zA-Z\_,]+$");
	if (obj['customlist[filename]'].value=='' || !chkname.test(obj['customlist[filename]'].value)){
		alert("文件名称只允许允许“数字、英文、下划线、逗号”且不能空！",null,null,null,function(){
			obj['customlist[filename]'].focus();
		});
		return false;
	}
	if (obj['customlist[tplid]'].value==''){
		alert("请选择列表模板！",null,null,null,function(){
			obj['customlist[tplid]'].focus();
		});
		return false;
	}
	return true;
}

</script>

<div class="loaction">您的位置：<a
	href="<?php
	echo Myqee::url ( 'index' );
	?>">管理首页</a> -&gt; <a
	href="<?php
	echo Myqee::url ( 'customlist/index' );
	?>">自定义列表</a> -&gt; <?php
echo $page_title;
?>
</div>
<form method="post" name="myforms"
	action="<?php
	echo Myqee::url ( 'customlist/save/' . $customlist ['id'] );
	?>"
    target="hiddenFrame"
	onsubmit="return chkforms(this)">
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
		<th colspan="2">自定义列表设置 - <?php
		echo $page_title;
		?></th>
	</tr>
	<tr>
		<td class="td1" align="right" width="120">列表名称(<font color="red">*</font>)：</td>
		<td class="td2"><?php
		echo form::input ( array('name' => 'customlist[pagename]','id'=>'customlist[pagename]'), $customlist ['pagename'], ' size="25" class="input"' );
		?></td>
	</tr>
	<tr>
		<td class="td1" align="right">列表分类：</td>
		<td class="td2"><?php
		echo form::changeinput ( 'customlist[cate]', $customlist ['cate'], ' size="10" class="input" title="留空则默认"', $pagecate, array ('默认' => '默认' ) );
		?>  &nbsp;<font
			color="#a3a3a3">输入自定义页面分类以区分不同类型自定义页面，留空则为默认分类，每个分类单独文件夹存放,留空存放在根目录。</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">是否启用(<font color="red">*</font>)：</td>
		<td class="td2"><?php
		echo form::dropdown ( array('name' => 'customlist[isuse]','id'=>'customlist[isuse]'), array('1'=>'启用','0'=>'不启用'), $customlist ['isuse'] );
		?></td>
	</tr>
	<tr>
		<td class="td1" align="right" width="120" height="26">输出方式：</td>
		<td class="td2"><?php echo form::radio('customlist[istohtml]','0',$customlist['istohtml']==0,'onclick="if(this.checked){$(\'list_tohtmlshow\').style.display=\'none\';}"');?>生成静态列表页
		<?php echo form::radio('customlist[istohtml]','1',$customlist['istohtml']==1,'onclick="if(this.checked){$(\'list_tohtmlshow\').style.display=\'\';}" id="customlist[istohtml]_1"');?>使用动态列表输出
		<span style="display: none;" id="list_tohtmlshow">
			&nbsp; 缓存时间(秒)：<?php echo form::input('customlist[cachetime]',(int)$customlist['cachetime'],'size="4"')?> 0表示不缓存
		</span> <script type="text/javascript">if ($('customlist[istohtml]_1').checked){$('istohtml').style.display='';}</script>
		</td>
	</tr>
	<tr>
		<td class="td1" align="right">文件路径：</td>
		<td class="td2"><?php
		echo form::changeinput ( 'customlist[filepath]', $customlist ['filepath'], ' size="40" class="input" title="从网站的wwwroot目录算起。留空则是根目录。"', $filepatharr, array ('' => '根目录' ) )
		?> 留空则为根目录</td>
	</tr>
	<tr>
		<td class="td1" align="right">文件名称及后缀(<font color="red">*</font>)：</td>
		<td class="td2">
		<?php 
		echo form::input ( 'customlist[filename]', $customlist['filename']?$customlist['filename']:'list_{{page}}', ' size="15" class="input" title="文件名，只允许数组字母下划线及英文逗号"' )
		,' ',
		form::dropdown ( 'customlist[filename_suffix]', array ('.html' => '.html',  '.htm' => '.htm' ), $customlist ['filename_suffix'] );
		?>&nbsp;<font
			color="#a3a3a3"></font> 页码用{{page}}代替，例如list_{{page}}.html</td>
	</tr>
	
	<tr>
		<td class="td1" align="right" rowspan="4">查询SQL语句(<font color="red">*</font>)：</td>
		<td class="td2">查询记录：<?php
		echo form::input ( array('name' => 'customlist[listsql]','id'=>'customlist[listsql]'), $customlist ['listsql'], ' size="100" class="input"' );
		?></td>
	</tr>
		<tr>
		<td class="td2">(如：select count(*) as total from `mycms_[news]`  where classid=1)</td>
	</tr>
		<tr>
		<td class="td2">统计记录：<?php
		echo form::input ( array('name' => 'customlist[totalsql]','id'=>'customlist[totalsql]'), $customlist ['totalsql'], ' size="100" class="input"' );
		?></td>
	</tr>
		<tr>
		<td class="td2">(如：select * from `mycms_[news]` where classid=1  order by id)</td>
	</tr>
		<tr>
		<td class="td1" align="right">查询总条数：</td>
		<td class="td2"><?php
		echo form::input ( array('name' => 'customlist[totalnums]','id'=>'customlist[totalnums]'), $customlist ['totalnums'], ' size="15" class="input"' );
		?> 条信息(0为不限制)</td>
	</tr>
	<tr>
		<td class="td1" align="right">每页显示：</td>
		<td class="td2"><?php
		echo form::input ( array('name' => 'customlist[pnums]','id'=>'customlist[pnums]'), $customlist ['pnums'], ' size="15" class="input"' );
		?> 条信息 </td>
	</tr>
	<tr>
		<td class="td1" align="right">页面模板(<font color="red">*</font>)：</td>
		<td class="td2"><?php
		echo form::dropdown ( 'customlist[tplid]', $tplarray, $customlist ['tplid'], 'id="customlist[tplid]"' );
		?>
		<input type="button" value="管理列表模板" class="btnl" onclick="goUrl('<?php echo Myqee::url ( 'template/index/1/page' );?>','_blank')" />
		<script>$('customlist[istpl]').onclick();</script>
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
		echo form::input ( 'customlist[pagetitle]', $customlist ['pagetitle'], 'size="50" class="input" style="width:300px;"' );
		?> 变量名：<?php
		echo form::input ( 'customlist[title_flag]', $customlist ['title_flag']==""?'title':$customlist ['title_flag'], 'size="15" class="input" maxlength="50" title="默认:title，将替换页面中&#38;#123;&#38;#123;title&#38;#125;&#38;#125;的变量<br/>当启用模板时，则将值传入变量$title<br/>下同。"' );
		?></td>
	</tr>
	<tr>
		<td class="td1" align="right" width="120">页面关键字：</td>
		<td class="td2"><span style="display:inline-block;width:313px;"><?php
		echo form::textarea ( 'customlist[keyword]', $customlist ['keyword'], 'size="50" class="input" style="position:absolute;margin-top:-15px;width:300px;height:14px;" onfocus="this.style.height=\'150px\';this.style.zIndex=\'1\';" onblur="this.style.height=\'14px\';this.style.zIndex=\'\';"');
		?></span>变量名：<?php
		echo form::input ( 'customlist[keywords_flag]', $customlist ['keywords_flag']==""?'keywords':$customlist ['keywords_flag'], 'size="15" class="input" maxlength="50" title="默认:keywords"' );
		?></td>
	</tr>
	<tr>
		<td class="td1" align="right">页面描述：</td>
		<td class="td2"><span style="display:inline-block;width:313px;"><?php
		echo form::textarea ( 'customlist[pagedesc]', $customlist ['pagedesc'], 'size="50" class="input" style="position:absolute;margin-top:-15px;width:300px;height:14px;" onfocus="this.style.height=\'150px\';this.style.zIndex=\'1\';" onblur="this.style.height=\'14px\';this.style.zIndex=\'\';"' );
		?></span>变量名：<?php
		echo form::input ( 'customlist[pagedesc_flag]', $customlist ['pagedesc_flag']==""?'description':$customlist ['pagedesc_flag'], 'size="15" class="input" maxlength="50" title="默认:description"' );
		?></td>
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
			'<input name="customlist[param_name][]" id="customlist[param_name][]" type="input" size="20" class="input" value="'+(myarr['name']||'').toString().replace(/\"/g,'&quot;')+'" />',
			'<input name="customlist[param_flag][]" id="customlist[param_flag][]" type="input" size="20" class="input" value="'+(myarr['flag']||'').toString().replace(/"/g,'&quot;')+'" />',
			"<textarea name='customlist[param_value][]' id='customlist[param_value][]' cols='50' rows='1' class='input' style='position:absolute;margin-top:-12px;height:14px;' onfocus=\"this.style.height='150px';this.style.zIndex='1';\" onblur=\"this.style.height='14px';this.style.zIndex='';\">"+(myarr['value']||'').toString().replace(/</g,'&lt;')+"</textarea>",
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
		<td class="td1"><input type="submit" value="保存列表" class="bbtn" /> <input
			onclick="goback(-1,'<?php
			echo Myqee::url ( 'customlist/index' )?>')"
			type="button" value="返回" class="btns" /></td>
	</tr>
</table>
</form>
<?php View::factory('admin/footer') -> render(TRUE);?>