<?php
$page_title = '站点管理';
$page_index = 'index';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<div class="loaction">

您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('site/index');?>">站点管理</a> -&gt; 站点管理
</div>

<table border="0" cellpadding="4" cellspacing="1" width="96%" align="center" class="tableborder">
<tr>
	<th class="td1"><?php echo Myqee::lang('admin/site.list.title');?></th>
	<th class="td1"><?php echo Myqee::lang('admin/site.list.siteurl');?></th>
	<th class="td1" width="40"><?php echo Myqee::lang('admin/site.list.isuse');?></th>
	<th class="td1" width="120"><?php echo Myqee::lang('admin/site.list.changesite');?></th>
	<th class="td1" width="120"><?php echo Myqee::lang('admin/site.list.do');?></th>
</tr>
<?php
if($list):
	$i=0;
	$mytype = array(
		'cover' => '封面',
		'list' => '列表',
		'content' => '内容',
		'search' => '搜索',
	);
	$makesuredelete = Myqee::lang('admin/site.list.makesuredelete');
	foreach ($list as $key => $item):
	$i++;
?>
<tr align="center"<?php if($i%2==0){echo ' class="td3"';} ?> onmouseover="tr_moveover(this)" onmouseout="tr_moveout(this)">
	<td class="td2"><?php echo $item['sitename'];?></td>
	<td class="td2" align="left"><?php echo $item['siteurl'];?></td>
	<td class="td2">
	<?php echo $item['isuse'] ? '是':'<font color="red">否</font>';?>
	</td>
	<td class="td2">
	<input onclick="goUrl('<?php echo Myqee::url('site/changesite/' . $item['id']) ;?>')" type="button" value="切换到此站点" class="btnl" />
	</td>
	<td class="td2">
	<input onclick="goUrl('<?php echo Myqee::url('site/edit/'.$item['id']) ;?>')" type="button" value="修改" class="btns" />
	<input onclick="ask_del('<?php echo $item['id'];?>','site/del','<?php echo $makesuredelete;?>');" type="button" value="删除" class="btns" />
	</td>
</tr>
<?php
	endforeach;
endif;
?>
</table>
<table border="0" cellpadding="4" cellspacing="1" width="96%" align="center" class="tableborder" style="border-top:none;">
<tr>
	<td class="td1" align="right">
	<input onclick="goUrl('<?php echo Myqee::url('site/changesite/'); ?>')" type="button" value="管理主站点" class="btnl" />
	<input onclick="goUrl('<?php echo Myqee::url('site/add/'); ?>')" type="button" value="创建站点" class="bbtn" />
	</td>
</tr>
</table>

<center><?php echo $page; ?></center>

<?php View::factory('admin/footer') -> render(TRUE);?>