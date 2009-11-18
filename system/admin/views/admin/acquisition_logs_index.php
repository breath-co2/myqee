<?php
$page_index = 'task';
$page_title = '采集管理';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; 
<a href="<?php echo Myqee::url('task/index');?>">任务管理</a> -&gt; 
<a href="<?php echo Myqee::url('acquisition/index');?>">采集管理</a> -&gt; 
任务：<a href="<?php echo Myqee::url('acquisition/node_list/'.$acqu_id);?>"><?php echo $acqu_name;?></a> -&gt; 
<?php if($node_name)echo '节点：“',$node_name,'”';?>
日志列表
</div>

<script type="text/javascript">
	var makesuredelete='您确认要删除此日志？';
</script>
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
<tr>
	<th class="td1" width="60">序号</th>
	<th class="td1">日志文件</th>
	<th class="td1" width="220">日记大小</th>
	<th class="td1" width="150">最后修改时间</th>
	<th class="td1" width="220">操作</th>
</tr>
<?php
if(is_array($list)):
	$i=0;
	foreach ($list as $item):
	$i++;
?>
<tr align="center"<?php if($i%2==0){echo ' class="td3"';} ?> onmouseover="tr_moveover(this)" onmouseout="tr_moveout(this)" ondblclick="change_select('select_id_<?php echo $item['id'];?>');return false;">
	<td class="td1"><?php echo $i;?></td>
	<td class="td2"><?php echo $item['file'];?></td>
	<td class="td2"><?php echo $item['size'];?></td>
	<td class="td2"><?php echo date('Y-m-d H:i:s',$item['mtime']);?></td>
	<td class="td2">
	<a href="<?php echo Myqee::url('index/logs_view/?log='.$item['file']);?>" target="_blank" class="btn">查看日志</a>
	<input onclick="ask_del('<?php echo $item['file'];?>','index/logs_del/?log=',makesuredelete);" type="button" value="删除日志" class="btn" />
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
	<a href="<?php echo Myqee::url('acquisition/node_list/'.$acqu_id)?>" class="btnl">管理当前任务</a>
	<a href="<?php echo Myqee::url('acquisition/add')?>" class="btn">新增任务</a>
	<input onclick="ask_del(<?php echo $node_id;?>,'acquisition/logs_del/<?php echo $acqu_id;?>',makesuredelete);" type="button" value="全部删除" class="bbtn" />
	</td>
</tr>
</table>

<center><?php echo $page; ?></center>

<?php View::factory('admin/footer') -> render(TRUE);?>