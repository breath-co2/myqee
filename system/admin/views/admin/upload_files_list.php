<?php
$page_index = 'info';
$page_title = '上传管理';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; 上传管理
</div>

<table border="0" cellpadding="4" cellspacing="1" width="96%" align="center" class="tableborder">
<tr>
	<th class="td1" width="60"><?php echo Myqee::lang('admin/upload.list.id');?></th>
	<th class="td1"><?php echo Myqee::lang('admin/upload.list.name');?></th>
	<th class="td1"><?php echo Myqee::lang('admin/upload.list.urlpath');?></th>
	<th class="td1" width="120"><?php echo Myqee::lang('admin/upload.list.uploadtime');?></th>
	<th class="td1" width="70"><?php echo Myqee::lang('admin/upload.list.size');?></th>
	<th class="td1" width="40"><?php echo Myqee::lang('admin/upload.list.filetype');?></th>
	<th class="td1" width="60"><?php echo Myqee::lang('admin/upload.list.do');?></th>
</tr>
<?php
if(is_array($list)):
	$i=0;
	$makesuredelete = Myqee::lang('admin/upload.list.makesuredeletefile');
	foreach ($list as $item):
	$i++;
?>
<tr align="center"<?php if($i%2==0){echo ' class="td3"';} ?> onmouseover="tr_moveover(this)" onmouseout="tr_moveout(this)">
    <td class="td1"><?php echo $item['id'];?></td>
	<td class="td2"><?php echo $item['filename'];?></td>
	<td class="td2"><?php echo $item['urlpath'];?></td>
	<td class="td2"><?php echo date("Y-m-d H:i", $item['uploadtime']);?></td>
	<td class="td2"><?php echo $item['size'];?></td>
	<td class="td2"><?php echo $item['filetype'];?></td>
	<td class="td2">
	<input onclick="ask_del(<?php echo $item['id'];?>,'uploadfile/del','<?php echo $makesuredelete;?>');" type="button" value="删除" class="btns" />
	</td>
</tr>
<?php 
	endforeach;
endif;
?>
</table>
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-top:none">
	<tr>
<td class="td1" align="right"><input type="button" class="bbtn" value="上传文件" onclick="goUrl('<?php echo Myqee::url('uploadfile/up');?>');" /></td>
	</tr>
</table>
<center><?php echo $page; ?></center>


<?php View::factory('admin/footer') -> render(TRUE);?>