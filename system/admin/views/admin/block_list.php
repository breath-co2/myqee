<?php
$page_title = '碎片管理';
$page_index = 'info';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>

<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; 
<a href="<?php echo Myqee::url('info/index');?>">内容管理</a> -&gt; 
<a href="<?php echo Myqee::url('block/index');?>">碎片管理</a> -&gt;
<a href="<?php echo Myqee::url('block/mylist');?>">碎片列表</a> -&gt;
参数设置
</div>

<script type="text/javascript">
function edit_block(type,no,obj){
	win({
		title:'碎片信息编辑',
		width:750,
		height:420,
		minBtn:true,
		maxBtn:true,
		showMask:false,
		dragOut:true,
		iframe:{
			src:'<?php echo Myqee::url('block/view_edit_frame')?>?type='+type+'&no='+no,
			width:'100%',
			height:'100%'
		}
		}
	);
}
</script>

<table border="0" cellpadding="3" cellspacing="1" width="96%" align="center" class="tableborder">
<tr>
	<th class="td1" width="45" height="30">ID</th>
	<th class="td1">碎片名称</th>
	<th class="td1" width="100">所属板块</th>
	<th class="td1" width="30">启用</th>
	<th class="td1" width="60">显示类型</th>
	<th class="td1" width="60">数据调用</th>
	<th class="td1" width="40">模板</th>
	<th class="td1" width="280">操作</th>
</tr>
<?php
if($list){
	$i = 0;
	foreach ($list as $item){
		$i++;
		if ($item['type']=='index'){
			$type = '<b>首页板块</b>';
		}
?>
<tr align="center"<?php if($i%2==0){echo ' class="td3"';} ?> onmouseover="tr_moveover(this)" onmouseout="tr_moveout(this)">
	<td class="td1"><?php echo $item['id'];?></td>
	<td class="td2"><?php echo $item['title'];?></td>
	<td class="td2"><?php echo $type;?></td>
	<td class="td2"><?php echo $item['isuse']?'<font color="green">是</font>':'<font color="red">否</font>';?></td>
	<td class="td2"><?php echo $item['show_type']==1?'<font color="green">格式化碎片</font>':'<font color="blue">代码碎片</font>';?></td>
	<td class="td2"><?php echo $item['mydata_id']?'<font color="green">启用</font>':'<font color="#FF6600">未启用</font>';?></td>
	<td class="td2"><?php echo $item['tpl_id']?'<font color="green">启用</font>':'<font color="#FF6600">未启用</font>';?></td>
	<td class="td2">
	<input class="btn" type="button" value="内容维护" onclick="edit_block('<?php echo $item['type']?>','<?php echo $item['no']?>')" />
	<input class="btnss" type="button" value="复制" onclick="goUrl('<?php echo Myqee::url('block/copy/'.$item['id'])?>')" />
	<input class="btn" type="button" value="修改设置" onclick="goUrl('<?php echo Myqee::url('block/edit/'.$item['id'])?>')" />
	<input class="btnss" type="button" value="删除" onclick="ask_del(<?php echo $item['id'];?>,'block/del','您确认删除此碎片？');" />
	</td>
</tr>
<?php
	}
}
?>
</table>

<div style="height:40px">
	<div id="control_div" style="width:100%;min-width:800px" class="control">
	<img src="<?php echo ADMIN_IMGPATH;?>/admin/spacer.gif" style="width:800px;height:0px" /><br/>

	<table border="0" cellpadding="5" cellspacing="1" width="96%" align="center" class="tableborder" style="border-top:none;">
	<tr>
		<td class="td1" align="right">
		<input onclick="goUrl('<?php echo Myqee::url('block/index'); ?>')" type="button" value="碎片信息管理" class="btnl" /> 
		<input onclick="goUrl('<?php echo Myqee::url('block/add'); ?>')" type="button" value="添加碎片" class="bbtn" /> 
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