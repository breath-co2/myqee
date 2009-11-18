<?php
$page_index = 'task';
$page_title = '生成静态页';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('task/index');?>">任务管理</a> -&gt; 生成静态页
</div>

<!--
<div style="width:96%;margin:auto;">
<ul class="ul tag">
	<li class="now" id="mytag_1" onclick="tag(this.id,'mytag','mytagmain')">常用页面生成</li>
	<li id="mytag_2" onclick="tag(this.id,'mytag','mytagmain')">批量高级生成</li>
</ul>
</div>
<div style="clear:both"></div>
-->

<script type="text/javascript">
function gotohtml(form,type){
	type = type || 'info';
	$('type').value=type;
	form.submit();
}
function showclass(t){
	$('classDiv').style.display='';
	$('classTitle').innerHTML = t||'生成页面';
}
</script>

<div class="mainTable">
<ul class="ul tag" id="mytag">
	<li class="now" id="mytag_1" onclick="tag(this.id,'mytag','mytagmain','mytag');$('classDiv').style.display='none';document.location.hash='#tag1'">生成首页面</li>
	<li id="mytag_2" onclick="tag(this.id,'mytag','mytagmain','mytag');showclass('生成栏目列表');document.location.hash='#tag2'">生成栏目列表</li>
	<li id="mytag_3" onclick="tag(this.id,'mytag','mytagmain','mytag');showclass('生成内容页');document.location.hash='#tag3'">生成内容页</li>
    <li id="mytag_4" onclick="tag(this.id,'mytag','mytagmain','mytag');showclass('生成专题页');document.location.hash='#tag4';$('classDiv').style.display='none';">生成专题页</li>
</ul>
</div>
<div style="clear:both"></div>
<div id="mytagmain_1">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
	<tr>
		<th class="td1" colspan="2">生成首页静态页</th>
	</tr>
	<tr>
		<td width="150" align="right" class="td1">主站首页生成：</td>
		<td class="td2"><input type="button" value="生成首页" class="bbtn" onclick="goUrl('<?php echo Myqee::url('task/tohtml/toindex') ?>','_blank')" /> &nbsp;
		<a href="<?php echo Myqee::url('index/config') ?>"><u>首页参数设置</u></a> &nbsp;
		<a href="#" onclick="goUrl('<?php
		$siteurl = Myqee::config('core.mysite_url');
		if (!substr($siteurl,0,7)!='http://'){
			$siteurl = 'http://'.Myqee::config('core.mysite_domain').Myqee::config('core.mysite_url');
		}
		echo $siteurl;?>','_blank');return false;"><u>访问首页</u></a></td>
	</tr>
	<tr>
	<td class="td1" align="right">生成自定义页：</td>
	<td class="td2">
	<input type="button" value="生成自定义页" class="btnl" onclick="goUrl('<?php echo Myqee::url('task/tohtml/tocustompage') ?>','_blank')" /> 
	 &nbsp; <a href="<?php echo Myqee::url('custompage/index') ?>"><u>管理自定义页</u></a>
	</td>
	</tr>
	<tr>
	<td class="td1" align="right">生成子站点首页：</td>
	<td class="td2">
	<input type="button" value="生成子站点首页" class="btnl" onclick="goUrl('<?php echo Myqee::url('task/tohtml/siteindex') ?>','_blank')" /> 
	 &nbsp; <a href="<?php echo Myqee::url('site/index') ?>"><u>管理子站点</u></a>
	</td>
	</tr>
</table>
</div>

<form name="tohtmlform" target="_blank" action="<?php echo Myqee::url('task/tohtml/frame/');?>">
<div id="classDiv" style="display:none;">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
	<tr>
		<th class="td1" colspan="2" id="classTitle"></th>
	</tr>
	<tr>
	<td class="td1" align="right" width="150">选择栏目：</td>
	<td class="td2">
	
	<input type="hidden" name="type" id="type" value="info" />
	<?php echo form::classlist('classid[]',$classtree,'id="classid" size="18" style="width:300px;" multiple="multiple"',0,array('选择全部栏目'),true);?>
	
	<div style="padding:6px 0 0 0">同时按Ctrl或Shift可以选择多个，灰色栏目表示没有启动内容功能。</div></td>
	</tr>
</table>
</div>
<div id="mytagmain_2" style="display:none;">
<table border="0" cellpadding="4" cellspacing="1" align="center" style="border-top:none;" class="tableborder">
	<td class="td1" width="150">&nbsp;</td>
	<td class="td1">
	<input type="button" value="生成列表" class="bbtn" onclick="gotohtml(this.form,'class')" />
	新窗口中打开
	</td>
	</tr>
</table>
</div>
<div id="mytagmain_3" style="display:none;">
<table border="0" cellpadding="4" cellspacing="1" align="center" style="border-top:none;" class="tableborder">
	<tr>
	<td class="td1" align="right">不重复生成信息页： </td>
	<td class="td2">
	<input type="checkbox" value="1" name="noreto_contenthtml" checked="checked" />
	</td>
	</tr>
	<tr>
	<td class="td1" width="150">&nbsp;</td>
	<td class="td1">
	<input type="button" value="生成页面" class="bbtn" onclick="gotohtml(this.form,'info')" />
	新窗口中打开
	</td>
	</tr>
</table>
</div>
</form>

<div id="mytagmain_4" style="display:none;">
<form name="tohtmlform" target="_blank" action="<?php echo Myqee::url('task/tohtml/sframe/');?>">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
	<tr>
		<th class="td1" colspan="2" id="classTitle"></th>
	</tr>
	<tr>
	<td class="td1" align="right" width="150">选择专题：</td>
	<td class="td2">
		<?php echo form::dropdown ('specialid[]',$speciallist,array(),'sieze=5');?>
    </td>
	</tr>
    <tr>
	<td class="td1" width="150">&nbsp;</td>
	<td class="td1">
	<input type="submit" value="生成页面" class="bbtn"/>
    <input type="hidden" name="type" id="type" value="info" />
	新窗口中打开
	</td>
	</tr>
</table>
</form>
</div>

<script type="text/javascript">
if (document.location.hash =='#tag1'||document.location.hash =='#tag2'||document.location.hash =='#tag3'||document.location.hash =='#tag4'){
	$('mytag_'+document.location.hash.substr(4)).onclick();
}
</script>

<?php View::factory('admin/footer') -> render(TRUE);?>