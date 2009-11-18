<?php
$page_title = '栏目管理';
$page_index = 'class';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<script type="text/javascript">
function isAble(obj){
	var tdobj = obj.parentNode.parentNode.cells[2];
	var disabled = true;
	if (obj.checked){
		disabled = false;
	}
		
	var inputobjs = tdobj.getElementsByTagName('INPUT');
	var textobjs = tdobj.getElementsByTagName('TEXTAREA');
	var selectobjs = tdobj.getElementsByTagName('SELECT');

	_setvalue(inputobjs,disabled);
	_setvalue(textobjs,disabled);
	_setvalue(selectobjs,disabled);

	obj.blur();
}

function _setvalue(objs,disabled){
	for (var i=0;i<objs.length;i++){
		if (objs[i].onclick && objs[i].type=='checkbox')continue;
		objs[i].disabled = disabled;
		if (objs[i].className.indexOf('input')>=0){
			if (disabled){
				objs[i].className = objs[i].className+' disabled';
			}else{
				objs[i].className = objs[i].className.replace(/ disabled/,'');
			}
		}
	}
}


</script>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('class/index');?>">栏目管理</a>-&gt; <?php
if ($page){
	echo '分页显示';
}else{
	echo '<a href="',Myqee::url('class/set'),'">批量设置</a>';
}
?>
</div>

<form method="post" name="myforms" action="<?php echo Myqee::url('class/psave');?>" target="hiddenFrame">
<table border="0" cellpadding="3" cellspacing="1" align="center" class="tableborder">
<tr>
	<th class="td1" width="200">选择栏目</th>
	<th class="td1">批量参数设置栏目</th>
</tr>
<tr>
	<td class="td2" align="center" valign="top" width="200">
	<?php echo form::classlist('classid[]',$classtree,'id="classid" size="40" style="width:190px;" multiple="multiple"',0,array('选择全部栏目'),true);?>
	<div style="padding:6px 0 0 0">同时按Ctrl或Shift可以选择多个，灰色栏目表示没有启动内容功能。</div></td>
	<td class="td2" align="center" valign="top" id="setValueDiv">
	<div style="padding:5px 5px 0 5px;margin:auto;">
	<ul class="ul tag">
		<li class="now" id="mytag_1" onclick="tag(this.id,'mytag','mytagmain');document.location.hash='#tag1'">基本设置</li>
		<li id="mytag_2" onclick="tag(this.id,'mytag','mytagmain');document.location.hash='#tag2'">输出设置</li>
	</ul>
	</div>
	<div id="mytagmain_1" style="padding:0 5px;">
	<table border="0" cellpadding="4" cellspacing="1" style="width:100%;" class="tableborder">
	<tbody>
	<tr><th class="td1" width="28">&nbsp;</th>
	<th class="td1" colspan="2" >参数设置</th>
	</tr>

	<tr class="td3">
	<td align="center" class="td1"><input type="checkbox" id="select_id_121" onclick="isAble(this)" /></td>
	<td class="td1" width="120" align="right" >是否显示到导航：</td>
	<td class="td2" align="left" >
		<?php echo form::radio('class[isnavshow]',1,!isset($class['isnavshow']) || (int)$class['isnavshow']==1); ?>显示
		<?php echo form::radio('class[isnavshow]',0,isset($class['isnavshow']) && (int)$class['isnavshow']==0); ?>不显示 &nbsp;
		<font color="#999999">只有父栏目显示到导航，此设置才有效</font>
	</td>
	</tr>

	<tr><td align="center" class="td1"><input type="checkbox" id="select_id_120" onclick="isAble(this)" /></td>
	<td class="td1" align="right" >栏目显示排序：</td>
	<td class="td2" align="left" ><?php echo form::input(array('name'=>'class[myorder]','class'=>'input disabled','disabled'=>'disabled'),$class['myorder'],'size="6"') ?> <font color="#999999">(值越小越前面)</font></td>
	</tr>

	<tr class="td3"><td align="center" class="td1"><input type="checkbox" id="select_id_105" onclick="isAble(this)" /></td>
	<td class="td1" align="right" >后台管理排序方式：</td>
	<td class="td2" align="left" >按字段<?php echo form::input('class[manage_orderbyfield]',$class['manage_orderbyfield']?$class['manage_orderbyfield']:'id','size="14" class="input" id="class[manage_orderbyfield]"') ?>
		<select onchange="$('class[manage_orderbyfield]').value=this.value">
              <option value="id">常用字段候选</option>
              <option value="updatetime">发布时间(updatetime)</option>
              <option value="id">ID(id)</option>
              <option value="hits">点击率(hits)</option>
              <option value="hits_down">下载数(hits_down)</option>
              <option value="commentsnum">评论数(commentsnum)</option>
            </select> ，排列方式<?php echo form::dropdown('class[manage_orderby]',array('DESC'=>'倒序排序','ASC'=>'顺序排序'),$class['manage_orderby']); ?></td>
	</tr>

	<tr><th class="td1" width="28">&nbsp;</th>
	<th class="td1" colspan="2" >SEO设置</th>
	</tr>

	<tr class="td3"><td align="center" class="td1"><input type="checkbox" id="select_id_105" onclick="isAble(this)" /></td>
	<td class="td1" align="right" >页面关键字：</td><td class="td2" align="left" ><?php echo form::input(array('name'=>'class[keyword]','size'=>30,'class'=>'input disabled','disabled'=>'disabled'),$class['keyword']) ?> (keyword)
	</td>
	</tr>

	<tr><td align="center" class="td1"><input type="checkbox" id="select_id_104" onclick="isAble(this)" /></td>
	<td class="td1" align="right">栏目简介：</td>
	<td class="td2" align="left" ><?php echo form::textarea(array(
			'name' => 'class[description]',
			'value' =>$class['description'],
			'class' => 'input',
			'cols' => 70,
			'rows' => 6,
		));
		?> (description)</td>
	</tr>

	</tbody>
	</table>
	</div>
	
	<div id="mytagmain_2" style="padding:0 5px;display:none;">
	<table border="0" cellpadding="4" cellspacing="1" style="width:100%;" class="tableborder">
	<tbody>
	<tr><th class="td1" width="28" >&nbsp;</th>
	<th class="td1" colspan="2" align="left" >封面参数设置</th>
	</tr>
	<tr class="td3">
	<td align="center" class="td1"><input type="checkbox" id="select_id_121" onclick="isAble(this)" /></td>
	<td class="td1" width="120" align="right">输出方式：</td>
    <td class="td2" align="left" ><?php echo form::radio('class[cover_tohtml]','0',$class['cover_tohtml']==0);?>生成静态封面页
	<?php echo form::radio('class[cover_tohtml]','1',$class['cover_tohtml']==1);?>使用动态列表输出
	</td>
	</tr>
	
	<tr><td align="center" class="td1"><input type="checkbox" id="select_id_120" onclick="isAble(this)" /></td>
	<td class="td1" align="right">文件名：</td>
	<td class="td2" align="left"><?php echo form::input('class[cover_filename]',$class['cover_filename']?$class['cover_filename']:'index.html','class="input" size="20"'); ?> <?php echo form::checkbox('class[cover_hiddenfilename]',1,$class['cover_hiddenfilename']);?>URL地址隐藏文件名 <span class="helpicon" title="例如index.html，选择“生成静态封面页”才有效<br/>URL地址隐藏文件名是指通过http://yourhost.com/myclass/这样的形式访问">&nbsp;</span></td>
	</tr>
	
	<tr class="td3">
	<td align="center" class="td1"><input type="checkbox" id="select_id_121" onclick="isAble(this)" /></td>
	<td class="td1" align="right">封面模板：</td>
	<td class="td2" align="left"><?php echo form::dropdown('class[cover_tplid]',$cover_tplarray,$class['cover_tplid']); ?> &nbsp;&nbsp;<input type="button" value="管理封面模板" class="btnl" onclick="goUrl('<?php echo Myqee::url('template/index/1/cover');?>','_blank')" /></td>
	</tr>
	
	<tr><th class="td1">&nbsp;</th>
	<th class="td1" colspan="2" align="left" >列表页参数设置</th>
	</tr>

	<tr class="td3">
	<td align="center" class="td1"><input type="checkbox" id="select_id_121" onclick="isAble(this)" /></td>
	<td class="td1" align="right">输出方式：</td>
	<td class="td2" align="left"><?php echo form::radio('class[list_tohtml]','0',$class['list_tohtml']==0);?>生成静态列表页
	<?php echo form::radio('class[list_tohtml]','1',$class['list_tohtml']==1);?>使用动态列表输出
	</td>
	</tr>

	<tr><td align="center" class="td1"><input type="checkbox" id="select_id_120" onclick="isAble(this)" /></td>
	<td class="td1" align="right">文件名：</td>
	<td class="td2" align="left"><?php echo form::input('class[list_filename]',$class['list_filename']?$class['list_filename']:'list_{{page}}.html','class="input" size="20"'); ?> &nbsp;页码用<font color="red">{{page}}</font>代替，例如list_{{page}}.html</td>
	</tr>

	<tr class="td3">
	<td align="center" class="td1"><input type="checkbox" id="select_id_119" onclick="isAble(this)" /></td>
	<td class="td1" align="right">列表模板：</td>
	<td class="td2" align="left" ><?php echo form::dropdown('class[list_tplid]',$list_tplarray,$class['list_tplid']); ?> &nbsp;&nbsp;<input type="button" value="管理列表模板" class="btnl" onclick="goUrl('<?php echo Myqee::url('template/index/1/list');?>','_blank')" /></td>
	</tr>

	<tr><td align="center" class="td1"><input type="checkbox" id="select_id_118" onclick="isAble(this)" /></td>
	<td class="td1" align="right">列取信息：</td>
	<td class="td2"align="left"><?php echo form::radio('class[list_nosonclass]',0,(int)$class['list_nosonclass']==0);?>本栏目及子栏目 <?php echo form::radio('class[list_nosonclass]',1,$class['list_nosonclass']==1);?>仅本栏目 <?php echo form::radio('class[list_nosonclass]',2,$class['list_nosonclass']==2);?>仅子栏目 &nbsp;<font class="helpicon" title="必须和此栏目在同一个数据表的栏目才会被列出">&nbsp;</font></td>
	</tr>

	<tr><td align="center" class="td1"><input type="checkbox" id="select_id_106" onclick="isAble(this)" /></td>
	<td class="td1" align="right">每页显示：</td>
	<td class="td2" align="left" ><?php echo form::input('class[list_pernum]',$class['list_pernum']>0?$class['list_pernum']:20,'size="5" class="input"'); ?> 条信息 </td>
	</tr>

	<tr class="td3"><td align="center" class="td1"><input type="checkbox" id="select_id_105" onclick="isAble(this)" /></td>
	<td class="td1" align="right">列出页码：</td>
	<td class="td2" align="left" ><?php echo form::input('class[list_allpage]',(int)$class['list_allpage'],'size="5" class="input"'); ?> 页 <font color="#999999">(0为显示所有记录)</font></td>
	</tr>

	<tr><td align="center" class="td1"><input type="checkbox" id="select_id_104" onclick="isAble(this)" /></td>
	<td class="td1" align="right">排序方式：</td>
	<td class="td2" align="left" >&nbsp;按字段<?php echo form::input('class[list_byfield]',$class['list_byfield']?$class['list_byfield']:'id','size="14" class="input" id="class[list_byfield]"') ?>
		<select onchange="$('class[list_byfield]').value=this.value">
              <option>常用字段候选</option>
              <option value="updatetime">发布时间(updatetime)</option>
              <option value="id">ID(id)</option>
              <option value="hits">点击率(hits)</option>
              <option value="hits_down">下载数(hits_down)</option>
              <option value="commentsnum">评论数(commentsnum)</option>
		</select> ，排列方式<?php echo form::dropdown('class[list_orderby]',array('DESC'=>'倒序排序','ASC'=>'顺序排序'),$class['list_orderby']); ?>
	</td>
	</tr>

	<tr><th class="td1" width="28">&nbsp;</th>
	<th class="td1" colspan="2" align="left" >内容页数设置</th>
	</tr>

	<tr class="td3"><td align="center" class="td1"><input type="checkbox" id="select_id_105" onclick="isAble(this)" /></td>
	<td class="td1" align="right">内容页输出方式：</td><td class="td2" align="left" ><?php echo form::radio('class[content_tohtml]','0',$class['content_tohtml']==0);?>生成静态内容页
	<?php echo form::radio('class[content_tohtml]','1',$class['content_tohtml']==1);?>使用动态内容输出
	</td>
	</tr>

	<tr><td align="center" class="td1"><input type="checkbox" id="select_id_104" onclick="isAble(this)" /></td>
	<td class="td1" align="right">内容页模板：</td>
	<td class="td2" align="left"><?php echo form::dropdown('class[content_tplid]',$content_tplarray,$class['content_tplid']); ?> &nbsp;&nbsp;<input type="button" value="管理内容模板" class="btnl" onclick="goUrl('<?php echo Myqee::url('template/index/1/content');?>','_blank')" /></td>
	</tr>

	<tr class="td3"><td align="center" class="td1"><input type="checkbox" id="select_id_105" onclick="isAble(this)" /></td>
	<td class="td1" align="right">存放目录：</td><td class="td2" align="left" ><?php echo form::radio('class[content_pathtype]',0,(int)$class['content_pathtype']==0,'onclick="$(\'content_pathtypeDiv\').style.display=\'none\'"'); ?>栏目目录 <?php echo form::radio('class[content_pathtype]',1,(int)$class['content_pathtype']==1,'onclick="$(\'content_pathtypeDiv\').style.display=\'block\'"'); ?>自定义目录
	<div id="content_pathtypeDiv" style="padding:4px 0;<?php if ((int)$class['content_pathtype']==0){echo 'display:none';}?>">&nbsp;<?php echo str_replace("\\",'/',WWWROOT) .' '. form::input('class[content_path]',$class['content_path'],'class="input" size="20"'); ?> <font color="#999999">(从www根目录开始)</font></div></td>
	</tr>

	<tr><td align="center" class="td1"><input type="checkbox" id="select_id_104" onclick="isAble(this)" /></td>
	<td class="td1" align="right">目录存放形式：</td><td class="td2" align="left" ><?php echo form::input('class[content_selfpath]',$class['content_selfpath']?$class['content_selfpath']:'Y-m-d','class="input" size="10" id="class[content_selfpath]"'); ?> <select onchange="$('class[content_selfpath]').value=this.value">
		<option value="Y-m-d">选择</option>
		<option value="Y-m-d">2008-08-09</option>
		<option value="Y/m-d">2008/08-09</option>
		<option value="Y/m/d">2008/08/09</option>
		<option value="Ymd">20080809</option>
		<option value="">不设置目录</option>
		</select> <font color="#999999">(如Y-m-d，Y/m-d等形式)</font></td>
	</tr>

	<tr class="td3"><td align="center" class="td1"><input type="checkbox" id="select_id_105" onclick="isAble(this)" /></td>
	<td class="td1" align="right">文件名前缀：</td>
	<td class="td2" align="left" ><?php echo form::input('class[content_prefix]',$class['content_prefix'],'class="input" size="12"');?></td>
	</tr>

	<tr><td align="center" class="td1"><input type="checkbox" id="select_id_104" onclick="isAble(this)" /></td>
	<td class="td1" align="right">文件名：</td><td class="td2" align="left" >
	<?php echo form::radio('class[content_filenametype]',0,(int)$class['content_filenametype']==0);?>信息ID
	<?php echo form::radio('class[content_filenametype]',1,(int)$class['content_filenametype']==1);?>$_SERVER['REQUEST_TIME']
	<?php echo form::radio('class[content_filenametype]',2,(int)$class['content_filenametype']==2);?>md5(32位)
	<?php echo form::radio('class[content_filenametype]',3,(int)$class['content_filenametype']==3);?>md5(16位)
	&nbsp;&nbsp;<font class="helpicon" title="选择信息ID则必须数据表中有ID匹配字段<br/>选择后面3项则数据表中存在content_name对应字段<br/>更详细说明请见网站帮助中关于栏目设置项">&nbsp;</font>
	</td>
	</tr>

	<tr class="td3"><td align="center" class="td1"><input type="checkbox" id="select_id_105" onclick="isAble(this)" /></td>
	<td class="td1" align="right">文件扩展名：</td><td class="td2" align="left" ><?php echo form::input('class[content_suffix]',$class['content_suffix'],'class="input" size="12" id="class[content_suffix]"');?>  <select onchange="$('class[content_suffix]').value=this.value">
		<option value=".html">扩展名</option>
		<option value=".html">.html</option>
		<option value=".htm">.htm</option>
		<option value=".php">.php</option>
		<option value=".shtml">.shtml</option>
		<option value=".shtml">.xml</option>
		</select> <font color="#999999">(如.html,.xml,.htm等)</font></td>
	</tr>
	
	<tr><th class="td1" width="28">&nbsp;</th>
	<th class="td1" colspan="2" align="left" >搜索页参数设置</th>
	</tr>

	<tr class="td3"><td align="center" class="td1"><input type="checkbox" id="select_id_105" onclick="isAble(this)" /></td>
	<td class="td1" align="right">搜索列表模板：</td>
	<td class="td2" align="left" ><?php echo form::dropdown('class[search_tplid]',$search_tplarray,$class['search_tplid']); ?> &nbsp;&nbsp;<input type="button" value="管理搜索模板" class="btnl" onclick="goUrl('<?php echo Myqee::url('template/index/1/search');?>','_blank')" /></td>
	</tr>

	<tr><td align="center" class="td1"><input type="checkbox" id="select_id_104" onclick="isAble(this)" /></td>
	<td class="td1" align="right">默认排序方式：</td>
	<td class="td2" align="left" >&nbsp;按字段<?php echo form::input('class[search_byfield]',$class['search_byfield']?$class['search_byfield']:'id','size="14" class="input" id="class[search_byfield]"') ?>
	<select onchange="$('class[search_byfield]').value=this.value">
		<option value="id">常用字段候选</option>
		<option value="updatetime">发布时间(updatetime)</option>
		<option value="id">ID(id)</option>
		<option value="hits">点击率(hits)</option>
		<option value="hits_down">下载数(hits_down)</option>
		<option value="commentsnum">评论数(commentsnum)</option>
		</select> ，排列方式<?php echo form::dropdown('class[search_orderby]',array('DESC'=>'倒序排序','ASC'=>'顺序排序'),$class['search_orderby']); ?>
	</td>
	</tr>
	</tbody>
	</table>
	</div>
	</td>
</tr>
<tr>
	<td class="td1" align="right">&nbsp;</td>
	<td class="td1" colspan="2" ><input type="submit" value="保存栏目" class="bbtn" /> <input onclick="goback(-1,'<?php echo Myqee::url('class/index');?>')" type="button" value="返回" class="btns" /></td>
</tr>
</table>
</form>
<script type="text/javascript">
(function (){
var obj = $('setValueDiv');
var allobjs = obj.getElementsByTagName('INPUT');
_setvalue(allobjs,true);
allobjs = obj.getElementsByTagName('TEXTAREA');
_setvalue(allobjs,true);
allobjs = obj.getElementsByTagName('SELECT');
_setvalue(allobjs,true);
})();
</script>
<center><?php echo $page;?></center>
<?php View::factory('admin/footer') -> render(TRUE);?>