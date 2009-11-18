<?php
$page_index = 'task';
$page_title = '采集管理';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>

<div class="loaction">
<div style="float:right;height:28px;"><input type="button" value="关闭窗口" class="btn" onclick="window.close()" style="margin:0" /></div>
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; 
<a href="<?php echo Myqee::url('task/index');?>">任务管理</a> -&gt; 
<a href="<?php echo Myqee::url('acquisition/index');?>">采集管理</a> -&gt; 
任务：<a href="<?php echo Myqee::url('acquisition/node_list/'.$acqu_id);?>"><?php echo $acqu_name;?></a> -&gt; 
<?php if($node_name)echo '节点：“',$node_name,'”';?>
查看信息
</div>

<script type="text/javascript">
	var makesuredelete='您确认要删除此采集数据？';
</script>
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
<tr>
	<th class="td1" colspan="2">查看信息  </th>
</tr>
<?php
if(is_array($info)):
	$i=0;
	foreach ($info as $key => $value):
	$i++;
?>
<tr<?php if($i%2==0){echo ' class="td3"';} ?>>
	<td class="td1" width="120" align="right"><?php echo $key;?>:</td>
	<td class="td2"><?php echo $value;?></td>
</tr>
<?php 
	endforeach;
endif;

$uri = urlencode($_SERVER["SCRIPT_URI"]);
?>
</table>

<div style="height:40px">
	<div id="control_div" style="width:100%;min-width:800px" class="control">
	<img src="<?php echo ADMIN_IMGPATH;?>/admin/spacer.gif" style="width:800px;height:0px" /><br/>

	<div>
	<table border="0" cellpadding="4" cellspacing="1" width="96%" align="center" class="tableborder" style="border-top:none;">
	<tr>
		<td class="td1" width="120"></td>
		<td class="td1">
		<input type="button" value="自动入库" class="btn" onclick="goUrl('<?php echo Myqee::url('acquisition/info_todb/'.$acqu_id.'/'.$node_id.'/'.$data_id.'?dotime='.$dotime);?>','hiddenFrame')" />
		<input type="button" value="手动入库" class="btn" onclick="goUrl('<?php echo Myqee::url('info/for_acquisition/'.$data_id.'?fullpage=yes&forward='.$uri) ;?>')" />
		<input onclick="goUrl('<?php echo Myqee::url('acquisition/info_reread/'.$data_id) ;?>','hiddenFrame')" class="btn" type="button" value="重新采集" />
		<input onclick="ask_del(<?php echo $data_id;?>,'acquisition/info_del/<?php echo $acqu_id;?>/<?php echo $node_id;?>/',makesuredelete);" type="button" value="删除" class="btns" />
		<input type="button" value="关闭窗口" class="btn" onclick="window.close();" />
		</td>
	</tr>
	</table>
	</div>
	
</div>
</div>



<script type="text/javascript">

set_control_fixed();
window.onscroll = set_control_fixed;
window.onresize = set_control_fixed;
</script>


<?php View::factory('admin/footer') -> render(TRUE);?>