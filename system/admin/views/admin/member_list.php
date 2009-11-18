<?php
$page_index = 'member';
$page_title = '用户管理';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; 用户管理
</div>


<table border="0" cellpadding="4" cellspacing="1" width="96%" align="center" class="tableborder">
<tr>
	<th class="td1" colspan="2">快速检索</th>
</tr>
<tr>
	<td class="td2">
	<form action="#">
	用户名：<input type="text" class="input" name="k" size="15" /> <input type="checkbox" checked="checked" />精确查询 <input type="checkbox" checked="checked" />不区分大小写
	<input type="submit" class="btns" value="搜索" />
	</form>
	</td>
	<td class="td2" align="right">
	<form action="#">
	用户ID：<input type="text" name="id" class="input" name="k" size="10" /> <input type="submit" class="btn" value="查看用户" />
	</form>
	</td>
</tr>
</table>
<br/>
<?php
echo $db_info_html;
?>
<table border="0" cellpadding="4" cellspacing="1" width="96%" align="center" class="tableborder" style="border-top:none;">
<tr>
	<td class="td1" width="20" align="center"><input type="checkbox" title="选择上面全部" onclick="selectbox(this,'select_id')" /></td>
	<td class="td1">
	
	</td>
	<td class="td1" width="320" align="right"><input type="button" class="btnl" value="数据表配置" onclick="goUrl('<?php echo Myqee::url('model/dblist')?>')" /><input type="button" class="bbtn" value="新增用户" onclick="goUrl('<?php echo Myqee::url('member/add')?>')" /></td>
</tr>
</table>

<center><?php echo $page; ?></center>


<?php View::factory('admin/footer') -> render(TRUE);?>