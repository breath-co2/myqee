<?php
$page_index = 'plus';
$page_title = Myqee::lang('admin/notice.title.noticelist');
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('plus/index');?>">插件管理</a> -&gt; <a href="<?php echo Myqee::url('notice/index');?>">公告管理</a> -&gt; 公告列表
</div>

<table border="0" cellpadding="4" cellspacing="1" width="96%" align="center" class="tableborder">
<tr>
	<th class="td1" width="50"><b>ID</b></th>
	<th class="td1"><b>公告标题</b></th>
	<th class="td1" width="60"><b>首页显示</b></th>
	<th class="td1" width="80"><b>访问次数</b></th>
	<th class="td1" width="200"><b>操作</b></th>
</tr>
<?php
if($list):
	$i=0;
	foreach ($list as $item):
	$i++;
?>
<tr<?php if($i%2==0){echo ' class="td3"';} ?> onmouseover="tr_moveover(this)" onmouseout="tr_moveout(this)">
	<td class="td1" align="center"><?php echo $item['id'];?></td>
	<td class="td2"><a href="<?php echo Myqee::config('core.mysite_url');?>notice/content/<?php echo $item['id'];?>/" target="_blank"><?php echo $item['title'];?></a></td>
	<td class="td2" align="center"><?if ($item['indexshow']){echo '<font color="red">是</font>';}else{echo '否';}?></td>
	<td class="td2" align="center"><?php echo $item['hits'];?></td>
	<td class="td2" align="center"><input onclick="goUrl('<?php echo Myqee::url('admin/notice/send/'.$item['id']) ;?>')" type="button" value="修改公告" class="btn" /> <input onclick="goUrl('<?php echo Myqee::url('admin/notice/del/'.$item['id']);?>','hiddenFrame')" type="button" value="删除公告" class="btn" /></td>
</tr>
<?php 
	endforeach;
endif;
?>
<tr>
	<td class="td1" align="center">&nbsp;</td>
	<td class="td1" colspan="4"><input onclick="goUrl('<?php echo Myqee::url('admin/notice/send'); ?>')" type="button" value="添加公告" class="bbtn" /></td>
</tr>
</table>

<center><?php echo $page; ?></center>


<?php View::factory('admin/footer') -> render(TRUE);?>