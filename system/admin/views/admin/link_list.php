<?php
$page_index = 'plus';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('plus/index');?>">插件管理</a> -&gt; <a href="<?php echo Myqee::url('link/index');?>">友情链接管理</a> -&gt; 链接列表
</div>
<table border="0" cellpadding="2" cellspacing="1" width="96%" align="center" class="tableborder" style="border-bottom:none;">
<tr>
	<th class="td1" style="text-align:left">
	<table border="0" cellpadding="0" cellspacing="0" style="float:left;"><tr>
	<td>&nbsp;&nbsp;搜索网站：</td>
	<td><input type="text" size="20" id="hostName" class="input" class="input" value="<?php echo $hostname;?>" onkeydown="(function (e){if (e.keyCode == 13){$('searchBtn').onclick();}})(event)" /></td>
	<td>&nbsp;<input type="button" id="searchBtn" value="查询" class="btns" onclick="goUrl('/admin/count/index/1/'+$('beginTime').value+'/'+$('endTime').value+($('hostName').value==''?'':'/'+encodeURIComponent($('hostName').value))+'.do')" /></td>
	</tr></table>
	</th>
</tr>
</table>
<table border="0" cellpadding="4" cellspacing="1" width="96%" align="center" class="tableborder">
<tr>
	<th class="td1" width="50"><b>ID</b></th>
	<th class="td1" width="120"><b>LOGO</b></th>
	<th class="td1"><b>链接名称</b></th>
	<th class="td1" width="60"><b>审核</b></th>
	<th class="td1" width="160"><b>添加时间</b></th>
	<th class="td1" width="200"><b>操作</b></th>
</tr>
<?php
if($list):
	$i=0;
	foreach ($list as $item):
	$i++;
?>
<tr<?php if($i%2==0){echo ' class="td3"';} ?>> onmouseover="tr_moveover(this)" onmouseout="tr_moveout(this)">
	<td class="td1" align="center"><?php echo $item['id'];?></td>
	<td class="td2" align="center"><img src="/<?php echo $item['link_logo'] ? $item['link_logo'] : 'images/nologo.gif';?>" width="88" height="31" border="0" /></td>
	<td class="td2"><a href="<?php echo $item['link_url'];?>" target="_blank"><?php echo $item['link_name'];?></a></td>
	<td class="td2" align="center"><?php if ($item['audit_flag']){echo '<font color="red">是</font>';}else{echo '否';}?></td>
	<td class="td2" align="center"><?php echo date("Y-m-d H:i", $item['add_date']);?></td>
	<td class="td2" align="center">
		<input type="button" onclick="goUrl('<?php echo Myqee::url("admin/link/audit/{$item["id"]}"); ?>', 'hiddenFrame');" value="<?if ($item['audit_flag']){echo '取消';}else{echo '审核';}?>" class="btns" />
		<input onclick="goUrl('<?php echo Myqee::url('admin/link/edit/'.$item['id']); ?>')" type="button" value="修改" class="btns" />
		<input onclick="ask_del(<?php echo $item['id'];?>,'admin/link/del','确认删除？');" type="button" value="删除" class="btns" />
	</td>
</tr>
<?php
	endforeach;
endif;
?>
<tr>
	<td class="td1" align="center"></td>
	<td class="td1" colspan="6"><input onclick="goUrl('<?php echo Myqee::url('admin/link/add'); ?>')" type="button" value="添加链接" class="bbtn" /></td>
</tr>
</table>
<br/><br/>
<center><?php echo $page;?></center>

<?php View::factory('admin/footer') -> render(TRUE);?>