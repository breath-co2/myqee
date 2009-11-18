<?php
$page_index = 'task';
$page_title = '节点管理';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('task/index');?>">任务管理</a> -&gt; <a href="<?php echo Myqee::url('acquisition/index');?>">采集管理</a>
 -&gt; 任务：<a href="<?php echo Myqee::url('acquisition/node_list/'.$acquisition_id);?>"><?php echo $acquisition_name;?></a> -&gt; <?php echo $page_title; ?>
</div>
<script type="text/javascript">
var makesuredelete = '确认要删除此节点？\n\n将删除所有子节点和采集表临时数据！';

function iseditorder(e){
	if (document.all)e=window.event;
	if (e.keyCode == 13){
		editorder();
	}
}
function editorder(){
	var inputs = document.getElementsByTagName('input');
	var myneworder = '';
	for (var k in inputs){
		if (inputs[k].id){
			if (inputs[k].type=='text' && inputs[k].id.substr(0,6) == 'order_' ){
				var thisid = inputs[k].id.substr(6);
				var newid = inputs[k].value.replace(/[^0-9]/g,'');
				var oldorderobj = $('oldorder_'+thisid);
				if (oldorderobj){
					if ( oldorderobj.value != newid){
						myneworder += 'node_'+thisid + '='+newid+',';
					}
				}
			}
		}
	}
	if (myneworder==''){
		alert('排序没有发生变化！');
	}else{
		goUrl('<?php echo Myqee::url('acquisition/node_order/'.$acquisition_id);?>?order='+myneworder , 'hiddenFrame');
	}
}

</script>

<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
<tr>
	<th class="td1" width="40">排序</th>
	<th class="td1" width="40">ID</th>
	<th class="td1">采集点名称</th>
	<th class="td1" width="30"><?php echo Myqee::lang('admin/model.list.isuse');?></th>
	<th class="td1" width="550"><?php echo Myqee::lang('admin/template.list.do');?></th>
</tr>
<?php
if(is_array($list)):
	$i=0;
	foreach ($list as $item):
	$i++;
?>
<tr align="center"<?php if($i%2==0){echo ' class="td3"';} ?> onmouseover="tr_moveover(this)" onmouseout="tr_moveout(this)">
	<td class="td1" align="center"><input type="hidden" id="oldorder_<?php echo $item['id']?>" value="<?php echo $item['myorder'];?>" /><input type="text" size="3" id="order_<?php echo $item['id']?>" value="<?php echo $item['myorder']?>" class="input" onkeydown="iseditorder(event)" /></td>
	<td class="td1"><?php echo $item['id'];?></td>
	<td class="td2"><?php echo $item['name'];?></td>
	<td class="td2"><?if ($item['isuse']){echo '是';}else{echo '<font color="red">否</font>';}?></td>
	<td class="td2">
    <input onclick="ask_del(<?php echo $item['id'];?>,'acquisition/node_stop/<?php echo $acquisition_id;?>','确实要停止该节点的采集么？');" type="button" value="停止采集" class="btn" />
	<a href="<?php echo Myqee::url('acquisition/doit/'.$acquisition_id.'/'.$item['id']) ;?>" class="btn">开始采集</a><a 
	href="<?php echo Myqee::url('acquisition/logs_list/'.$acquisition_id.'/'.$item['id']) ;?>" class="btn">采集日志</a><a 
	href="<?php echo Myqee::url('acquisition/info_list/'.$acquisition_id.'/'.$item['id']) ;?>" class="btn">管理信息</a><input 
	onclick="ask_del(<?php echo $item['id'];?>,'acquisition/info_del/<?php echo $acquisition_id;?>','确认要删除此节点所采集的全部数据？');" type="button" value="清空数据" class="btn" /><a 
	href="<?php echo Myqee::url('acquisition/node_copy/'.$acquisition_id.'/'.$item['id']) ;?>" class="btnss">复制</a><a 
	href="<?php echo Myqee::url('acquisition/node_edit/'.$acquisition_id.'/'.$item['id']) ;?>" class="btns">修改</a><input 
	onclick="ask_del(<?php echo $item['id'];?>,'acquisition/node_del/<?php echo $acquisition_id;?>',makesuredelete);" type="button" value="删除" class="btnss" />
	</td>
</tr>
<?php 
	endforeach;
endif;
?>
</table>
<table border="0" cellpadding="4" cellspacing="1" width="96%" align="center" class="tableborder" style="border-top:none;">
<tr>
	<td class="td1"><input type="button" class="btn" value="修改排序" onclick="editorder();" /></td>
	<td class="td1" width="550" align="center">
	<input type="button" class="btn" value="任务设置" onclick="goUrl('<?php echo Myqee::url('acquisition/edit/'.$acquisition_id)?>')" />
	<input onclick="ask_del(<?php echo $acquisition_id;?>,'acquisition/logs_del','将删除本任务所有采集日志，是否继续？');" type="button" value="删除日志" class="btn" />
	<input onclick="ask_del(<?php echo $acquisition_id;?>,'acquisition/info_del','确认要删除此任务所采集的全部数据？');" type="button" value="清空数据" class="btn" />
	<a href="<?php echo Myqee::url('acquisition/info_list/'.$acquisition_id)?>" class="btn">数据管理</a>
	<a href="<?php echo Myqee::url('acquisition/node_add/'.$acquisition_id)?>" class="bbtn">新增节点</a>
	</td>
</tr>
</table>



<?php View::factory('admin/footer') -> render(TRUE);?>