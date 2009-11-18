<?php
$page_title = '数据同步';
$page_index = 'member';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>

<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('member/index');?>">用户列表</a>-&gt; <?php
echo '<a href="',Myqee::url('member/data_update'),'">数据同步</a>';
?>
</div>

<form method="post" name="myforms" action="<?php echo Myqee::url('member/data_update_save');?>" target="hiddenFrame">
<table border="0" cellpadding="3" cellspacing="1" align="center" class="tableborder">
<tr>
	<th class="td1" width="33%">&nbsp;</th>
	<th class="td1" width="13%">选择数据表</th>
	<th class="td1" width="8%">&nbsp;</th>
	<th class="td1" width="13%">选择数据表</th>
	<th class="td1" width="33%">&nbsp;</th>
</tr>
<tr>
	<td width="33%">&nbsp;</td>
	<td class="td2" valign="top" width="13%">
	<?php echo $fromtable;?>
	<div style="padding:6px 0 0 0；">同时按Ctrl或Shift可以选择多个，灰色数据表表示没有启动</div></td>
	<td width="8%" align="center"><input type="submit" value="同步数据" class="bbtn" /></td>
	<td class="td2" align="left" valign="top" width="13%">
	<?php echo $totable;?>
	<div style="padding:6px 0 0 0；">同时按Ctrl或Shift可以选择多个，灰色数据表表示没有启动</div></td>
	<td width="33%">&nbsp;</td>
</tr>
</table>
</form>
<center><?php echo $page;?></center>
<?php View::factory('admin/footer') -> render(TRUE);?>