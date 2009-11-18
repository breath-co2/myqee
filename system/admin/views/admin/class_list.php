<?php
$page_title = '栏目管理';
$page_index = 'class';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<script type="text/javascript">
function delclass(id){
	ask_del(id,'class/del','<?php echo Myqee::lang('admin/class.list.makesuredelete');?>');
}

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
						myneworder += 'classid_'+thisid + '='+newid+',';
					}
				}
			}
		}
	}
	if (myneworder==''){
		alert('排序没有发生变化！');
	}else{
		goUrl('<?php echo Myqee::url('class/editorder') ?>?order='+myneworder , 'hiddenFrame');
	}
}
</script>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('class/index');?>">栏目管理</a>-&gt; <?php
if ($page){
	echo '分页显示';
}else{
	echo '<a href="',Myqee::url('class/index'),'">全部栏目</a>';
	if (is_array($location)){
		foreach ($location as $item){
			echo ' -&gt; <a href="',Myqee::url('class/index/'.$item['classid']),'">',$item['classname'],'</a>';
		}
		echo ' -&gt; 所有子栏目';
	}else{
		echo ' -&gt; 树状列表';
	}
}
?>
</div>
<table border="0" cellpadding="3" cellspacing="1" width="96%" align="center" class="tableborder">
<tr>
	<th class="td1" width="40">排序</th>
	<th class="td1" width="50">ID</th>
	<th class="td1">栏目名称</th>
	<th class="td1" width="120">数据表</th>
	<th class="td1" width="30">导航</th>
	<th class="td1" width="370">操作</th>
</tr>
<?php
$count_i = 0;
function listclass($list,$spacer=''){
global $count_i;
if($list):
	$countlist = count($list);
	$i = 0;
	foreach ($list as $item):
	$i++;
	$count_i++;
	if ($item['islast'] ==0){
			if ($item['sonclassarray']){
				$spacer1 = $spacer .'<img src="'.ADMIN_IMGPATH.'/admin/tree_add.gif" align="absmiddle" /> ';
			}
			else{
				$spacer1 = $spacer .'<img src="'.ADMIN_IMGPATH.'/admin/tree_del.gif" align="absmiddle" /> ';
			}
	}else{
		$spacer1 = $spacer .'　';
	}
?>
<tr<?php if($count_i%2==0){echo ' class="td3"';} ?> onmouseover="tr_moveover(this)" onmouseout="tr_moveout(this)">
	<td class="td1" align="center"><input type="hidden" id="oldorder_<?php echo $item['classid']?>" value="<?php echo $item['myorder']?>" /><input type="text" size="3" id="order_<?php echo $item['classid']?>" value="<?php echo $item['myorder']?>" onkeydown="iseditorder(event)" class="input" /></td>
	<td class="td1" align="center"><?php echo $item['classid']?></td>
	<td class="td2"><?php echo $spacer1?><img src="<?php echo ADMIN_IMGPATH;?>/admin/<?php echo ($item['iscontent']?'ie':'dir');?>.gif" align="absmiddle" />
	<a href="<?php echo Myqee::url('class/index/'.$item['classid']);?>"><?php echo $item['classname']?></a>
	<a href="<?php echo $item['URL'];?>" target="_blank"><img src="<?php echo ADMIN_IMGPATH;?>/admin/external.png" title="点击访问栏目页" /></a>
	</td>
	<td class="td2" align="center"><?php echo $item['dbname']?></td>
	<td class="td2" align="center"><?php echo $item['isnavshow']?'<font color="green">显示</font>':'否';?></td>
	<td class="td2" align="center">
	<input onclick="goUrl('<?php echo Myqee::url('task/tohtml/frame/?type=class&classid%5B%5D='.$item['classid']) ?>','_blank')" type="button" value="更新列表" class="btn2" /><input onclick="goUrl('<?php echo Myqee::url('info/add/'.$item['classid'].'/fullpage') ?>','_blank')" type="button" value="添加内容" class="btn2" /><input onclick="goUrl('<?php echo Myqee::url('info/myclass/1/'.$item['classid'].'/fullpage') ?>','_blank')" type="button" value="管理信息" class="btn2" /><input onclick="goUrl('<?php echo Myqee::url('class/add/'.$item['classid']) ?>')" type="button" value="新子栏目" class="btn2" /><input onclick="goUrl('<?php echo Myqee::url('class/copy/'.$item['classid']) ?>')" type="button" value="复制" class="btnss" /><input onclick="goUrl('<?php echo Myqee::url('class/edit/'.$item['classid']) ?>')" type="button" value="修改" class="btnss" /><input onclick="delclass(<?php echo $item['classid'];?>)" type="button" value="删除" class="btnss" /></td>
</tr>
<?php
//echo ($i+1).'|'.$countlist.'|'.$item['classname'];
	if ($item['sonclassarray']){
		$spacer .= '　 ';
		listclass($item['sonclassarray'],$spacer);
		$spacer = substr($spacer,0,-strlen('　 '));
	}
	endforeach;
endif;
}
listclass($list);
?>
</table>

<div style="height:40px">
	<div id="control_div" style="width:100%;min-width:800px" class="control">
	<img src="<?php echo ADMIN_IMGPATH;?>/admin/spacer.gif" style="width:800px;height:0px" /><br/>

	<table border="0" cellpadding="3" cellspacing="1" width="96%" align="center" class="tableborder" style="border-top:none;">
	<tr>
		<td class="td1">
		<div style="float:right">
		<input onclick="goUrl('<?php echo Myqee::url('class/add') ?>')" type="button" value="添加栏目" class="bbtn" /> 
		</div>
		<input onclick="editorder();" type="button" value="修改栏目顺序" class="btnl" /> 
		<input onclick="goUrl('<?php echo Myqee::url('task/tohtml/frame/?type=class&classid[]=0');?>','_blank')" type="button" value="刷新栏目页面" class="btnl" /> 
		<input onclick="goUrl('<?php echo Myqee::url('task/tohtml/frame/?type=info&classid[]=0');?>','_blank')" type="button" value="刷新栏目页面" class="btnl" /> 
		<input onclick="goUrl('<?php echo Myqee::url('class/renew_config') ?>','hiddenFrame')" type="button" value="更新配置缓存" class="btnl" /> 
		</td>
	</tr>
	</table>
	</div>
</div>

<script type="text/javascript">
	set_control_fixed(160);
	window.onscroll = function(){set_control_fixed(160)};

</script>
<center style="height:50px;overflow:hidden;"><?php echo $page;?></center>

<?php View::factory('admin/footer') -> render(TRUE);?>