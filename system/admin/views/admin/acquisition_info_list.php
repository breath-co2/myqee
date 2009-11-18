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
信息列表
</div>

<script type="text/javascript">
	var makesuredelete='您确认要删除此信息？';
</script>
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
<tr>
	<th class="td1" width="20">&nbsp;</th>
	<th class="td1" width="50">ID</th>
	<th class="td1">信息标题</th>
	<th class="td1" width="100">数据表</th>
	<th class="td1" width="56">入库状态</th>
	<th class="td1" width="120">采集时间</th>
	<th class="td1" width="270">操作</th>
</tr>
<?php
if(is_array($list)):
	$i=0;
	$uri = urlencode($_SERVER["SCRIPT_URI"]);
	foreach ($list as $item):
	$i++;
?>
<tr align="center"<?php if($i%2==0){echo ' class="td3"';} ?> onmouseover="tr_moveover(this)" onmouseout="tr_moveout(this)" ondblclick="change_select('select_id_<?php echo $item['id'];?>');return false;">
	<td class="td1"><input type="checkbox" id="select_id_<?php echo $item['id'];?>" onclick="select_tr(this)" /></td>
	<td class="td1"><?php echo $item['id'];?></td>
	<td class="td2" align="left"><?php echo $item['title']?$item['title']:'未知标题';?> <a href="<?php echo $item['info_url'];?>" target="_blank" onclick="window.open(this.href);return false;"><img src="<?php echo ADMIN_IMGPATH;?>/admin/external.png" alt="新窗口查看被采集页面" /></a></td>
	<td class="td2"><?php echo $item['dbname'];?></td>
	<td class="td2"><?php
		if ($item['is_todb']==1){echo '<font color="green">已入库</font>';}
		else if($item['is_todb']==-1){echo '<font color="blue">空信息</font>';}
		else if($item['is_todb']==-2){echo '<font color="red">入库异常</font>';}
		else{echo '<font color="#999999">未入库</font>';}
	?></td>
	<td class="td2" style="font-family:arial;"><?php echo date("Y-m-d H:i:s",$item['urlread_time']);?></td>
	<td class="td2"><input type="button" 
	onclick="goUrl('<?php echo Myqee::url('acquisition/info_todb/'.$acqu_id.'/'.$item['node_id'].'/'.$item['id'].'?dotime='.$item['dotime']) ;?>','hiddenFrame')" class="btn2" value="自动入库" /><a 
	href="<?php echo Myqee::url('info/for_acquisition/'.$item['id'].'?fullpage=yes&forward='.$uri) ;?>" class="btn2">手动入库</a><input 
	onclick="goUrl('<?php echo Myqee::url('acquisition/info_reread/'.$item['id']) ;?>','hiddenFrame')" class="btn2" type="button" value="重新采集" /><a 
	href="<?php echo Myqee::url('acquisition/info_view/'.$item['id']) ;?>" class="btnss" target="_blank">查看</a><input 
	onclick="ask_del(<?php echo $item['id'];?>,'acquisition/info_del/<?php echo $acqu_id;?>/<?php echo $node_id;?>',makesuredelete);" type="button" value="删除" class="btnss" />
	</td>
</tr>
<?php 
	endforeach;
endif;
?>
</table>
<table border="0" cellpadding="4" cellspacing="1" width="96%" align="center" class="tableborder" style="border-top:none;">
<tr>
	<td class="td1" width="20" align="center"><input type="checkbox" title="选择上面全部" onclick="selectbox(this,'select_id')" /></td>
	<td class="td1">
	<input type="button" value="删除已选" class="btn" onclick="confirm('确认删除已选择的信息？',null,null,null,function(e){if(e=='ok')submitbox('select_id','acquisition/info_del/<?php echo $acqu_id;?>/<?php echo $node_id;?>/[id]/','hiddenFrame')})" />
	<input type="button" value="入库已选" class="btn" onclick="confirm('将已选择的信息入库，是否继续？',null,null,null,function(e){if(e=='ok')submitbox('select_id','acquisition/info_todb/<?php echo $acqu_id;?>/<?php echo $node_id;?>/[id]/','hiddenFrame')})" />
	</td>
	<td class="td1" width="466" align="right">
	<a href="<?php echo Myqee::url('acquisition/node_list/'.$acqu_id)?>" class="btnl">管理当前任务</a>
	<input type="button" value="删除全部信息" class="btnl" onclick="confirm('确认删除本节点全部的信息？',null,null,null,function(e){if(e=='ok')goUrl('<?php echo Myqee::url('acquisition/info_del/'.$acqu_id.'/'.$node_id);?>','hiddenFrame')})" />
	<input type="button" value="全部入库" title="将未入库数据全部入库！" class="bbtn" onclick="confirm('将状态为未入库信息全部入库，是否继续？',400,null,null,function(e){if(e=='ok')goUrl('<?php echo Myqee::url('acquisition/info_todb/'.$acqu_id.'/'.$node_id);?>','hiddenFrame')})" />
	</td>
</tr>
</table>

<center><?php echo $page; ?></center>

<?php View::factory('admin/footer') -> render(TRUE);?>