<?php
$page_index = 'task';
$page_title = '采集管理';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('task/index');?>">任务管理</a> -&gt; <a href="<?php echo Myqee::url('acquisition/index');?>">采集管理</a> -&gt; 所有任务
</div>

<script type="text/javascript">
	var makesuredelete='您确认要删除此任务？\n\n将删除此任务下所有节点及此任务下采集表内的数据。';
</script>
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
<tr>
	<th class="td1" width="20">&nbsp;</th>
	<th class="td1" width="50">ID</th>
	<th class="td1">任务名称</th>
	<th class="td1">所属栏目/模型/数据表</th>
	<th class="td1" width="30">启用</th>
	<th class="td1" width="40">节点数</th>
	<th class="td1" width="320">操作</th>
</tr>
<?php
if(is_array($list)):
	$i=0;
	foreach ($list as $item):
	$i++;
?>
<tr align="center"<?php if($i%2==0){echo ' class="td3"';} ?> onmouseover="tr_moveover(this)" onmouseout="tr_moveout(this)" ondblclick="change_select('select_id_<?php echo $item['id'];?>');return false;">
	<td class="td1"><input type="checkbox" id="select_id_<?php echo $item['id'];?>" onclick="select_tr(this)" /></td>
	<td class="td1"><?php echo $item['id'];?></td>
	<td class="td2"><?php echo $item['name'];?></td>
	<td class="td2"><?php echo $item['classname']?$item['classname']:'无';?>/<?php echo $item['modelname']?$item['modelname']:'无';?>/<?php echo $item['dbname'];?></td>
	<td class="td2"><?if ($item['isuse']){echo '是';}else{echo '<font color="red">否</font>';}?></td>
	<td class="td2"><?php echo $item['node']?count(unserialize($item['node'])):0;?></td>
	<td class="td2"><a 
	href="<?php echo Myqee::url('acquisition/node_list/'.$item['id']) ;?>" class="btn">管理节点</a><input 
	onclick="var myvalue=prompt('您可以输入加密密码以保护您的模板。留空或取消则不设置密码！','')||'';goUrl('<?php echo 'acquisition/output/'.$item['id'] ;?>/'+myvalue,'hiddenFrame')" type="button" value="导出" class="btns" /><a 
	href="<?php echo Myqee::url('acquisition/copy/'.$item['id']) ;?>" class="btns">复制</a><a 
	href="<?php echo Myqee::url('acquisition/edit/'.$item['id']) ;?>" class="btns">修改</a><input 
	onclick="ask_del(<?php echo $item['id'];?>,'acquisition/del',makesuredelete);" type="button" value="删除" class="btns" />
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
	<td class="td1"><input onclick="var myvalue=prompt('您可以输入加密密码以保护您的任务。留空或取消则不设置密码！','')||'';submitbox('select_id','acquisition/output/[id]/'+myvalue,'hiddenFrame')" type="button" value="导出选定任务" class="btnl" /><a 
	href="<?php echo Myqee::url('acquisition/inputmodel')?>" class="btn">导入任务</a></td>
	<td class="td1" width="320" align="right"><a 
	href="<?php echo Myqee::url('acquisition/info')?>" class="btnl">数据内容管理</a><a 
	href="<?php echo Myqee::url('acquisition/add')?>" class="bbtn">新增任务</a></td>
</tr>
</table>

<center><?php echo $page; ?></center>

<?php View::factory('admin/footer') -> render(TRUE);?>