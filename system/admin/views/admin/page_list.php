<?php View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);?>
<?php include 'page_menu.php';?>
<br/>
<table border="0" cellpadding="4" cellspacing="1" width="96%" align="center" class="tableborder">
<tr>
	<th class="td1" width="50"><b>ID</b></th>
	<th class="td1" width="80"><b>分类</b></th>
	<th class="td1"><b>页面名称</b></th>
	<th class="td1" width="30"><b>显示</b></th>
	<th class="td1" width="120"><b>最后生成</b></th>
	<th class="td1" width="280"><b>操作</b></th>
</tr>
<?php
if($list):
	$i=0;
	foreach ($list as $item):
	$i++;
?>
<tr<?php if($i%2==0){echo ' class="td3"';} ?> onmouseover="tr_moveover(this)" onmouseout="tr_moveout(this)">
	<td class="td1" align="center"><?php echo $item['id']?></td>
	<td class="td2" align="center"><?php echo $item['classname']?></td>
	<td class="td2"><a href="<?php echo UNCONURL?><?php echo $item['urlpath'].$item['filename']?>" target="_blank"><?php echo $item['title']?></a></td>
	<td class="td2" align="center"><?if ($item['isshow']){echo '是';}else{echo '<font color="red">否</font>';}?></td>
	<td class="td2" align="center"><?php echo date	("Y-m-d H:i",$item['lastcreatedate'])?></td>
	<td class="td2" align="center">
	<input onclick="goUrl('<?php echo Myqee::url('admin/page/rehtml/'.$item['id']) ?>','hiddenFrame')" type="button" value="重新生成" class="btn" /> 
	<input onclick="goUrl('<?php echo Myqee::url('admin/page/edit/'.$item['id']) ?>')" type="button" value="修改" class="btns" /> 
	<?php if ($item['isshow']==1): ?>
	<input onclick="ask_del(<?php echo $item['id'];?>,'admin/page/close','确认屏蔽页面？');" type="button" value="屏蔽" class="btns" />
	<?php else: ?>
	<input onclick="goUrl('<?php echo Myqee::url('admin/page/open/'.$item['id'])?>','hiddenFrame')" type="button" value="开启" class="btns" />
	<?php endif;?>
	<input onclick="ask_del(<?php echo $item['id'];?>,'page/del','确认删除？');" type="button" value="删除" class="btns" />
	</td>
</tr>
<?php
	endforeach;
endif;
?>
<tr>
	<td class="td1" align="center"></td>
	<td class="td1" colspan="5"><input onclick="goUrl('<?php echo Myqee::url('admin/page/add') ?>')" type="button" value="添加页面" class="bbtn" /></td>
</tr>
</table>
<br/><br/>
<center><?php echo $page?></center>

<?php View::factory('admin/footer') -> render(TRUE);?>