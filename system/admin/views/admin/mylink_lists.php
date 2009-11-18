<?php
$page_index = 'info';
$page_title = '快速链接';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('mylink/index');?>">快速链接</a> -&gt; 所有链接
</div>

<script type="text/javascript">
var makesuredelete='您确认要删除此快速链接吗？';
function iseditorder(e){
	if (document.all)e=window.event;
	if (e.keyCode == 13){
		saveOrder();
	}
}
function saveOrder(){
	var inputs = document.getElementsByTagName('input');
	var myneworder = '';
	for (var k in inputs){
		if (inputs[k].id){
			if (inputs[k].type=='text' && inputs[k].id.substr(0,8) == 'myorder_' ){
				var thisid = inputs[k].id.substr(8);
				var newid = inputs[k].value.replace(/[^0-9]/g,'');
				var oldorderobj = $('oldorder_'+thisid);
				if (oldorderobj){
					if ( oldorderobj.value != newid){
						myneworder += 'mydataid_'+thisid + '='+newid+',';
					}
				}
			}
		}
	}
	if (myneworder==''){
		alert('排序没有发生变化！');
	}else{
		goUrl('<?php echo Myqee::url('mylink/editorder') ?>?order='+myneworder , 'hiddenFrame');
	}
}
</script>
<table border="0" cellpadding="4" cellspacing="1" width="96%" align="center" class="tableborder">
<tr>
	<th class="td1" width="20">&nbsp;</th>
	<th class="td1" width="40">排序</th>
	<th class="td1" width="30">ID</th>
	<th class="td1">链接名称</th>
	<th class="td1" width="60">数据调用</th>
	<th class="td1" width="60">子链接</th>
	<th class="td1" width="30">启用</th>
	<th class="td1" width="300">操作</th>
</tr>
<?php
if(is_array($list)):
	$i=0;
	foreach ($list as $key => $item):
	$i++;
?>
<tr align="center"<?php if($i%2==0){echo ' class="td3"';} ?> onmouseover="tr_moveover(this)" onmouseout="tr_moveout(this)">
	<td class="td2"><input type="checkbox" id="select_id_<?php echo $item['id'];?>" /></td>	
	<td class="td1" width="40" >
	<input type="hidden" id="oldorder_<?php echo $item['id']?>" value="<?php echo $item['myorder']?>" />
	<input type="text" id="myorder_<?php echo $item['id']?>" name="myorder_<?php echo $item['id']?>" value="<?php echo $item['myorder'];?>" size="3" onkeydown="iseditorder(event)" class="input" />
	</td>
	<td class="td1"><?php echo $item['id'];?></td>
	<td class="td2" align="left" style="color:#12C450"><?php if ($item['name']){echo $item['name'];}else{echo '<font color="red">NULL</font>';}?></td>
	<td class="td1"><?php if ($item['mydata_id']){echo $item['mydata_id'];}else{echo '<font color="red">无</font>';}?></td>
	<td class="td1"><?php echo $item['count'];?></td>
	<td class="td1"><?if ($item['is_use']){echo '是';}else{echo '<font color="red">否</font>';}?></td>
	<td class="td2">
		<input onclick="goUrl('<?php echo Myqee::url('mylink/mylinkedit/'.$item['id']) ;?>')" type="button" value="编辑" class="btns" />&nbsp;
		<input onclick="ask_del(<?php echo $item['id'];?>,'mylink/mylinkDel',makesuredelete);" type="button" value="删除" class="btns" />&nbsp;
		<input onclick="goUrl('<?php echo ADMIN_URLPATH.'mylink/links/'.$item['id'] ?>')" type="button" value="管理子链接" class="btn" style="letter-spacing:0" />&nbsp;
		<input onclick="goUrl('<?php echo ADMIN_URLPATH.'mylink/renewfiles/'.$item['id'] ?>','hiddenFrame')" type="button" value="更新子链接" class="btn" style="letter-spacing:0" <?if (!$item['mydata_id']){echo 'disabled="disabled"';}?> />
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
		<input onclick="saveOrder();" type="button" value="修改栏目排序" class="btnl" />
		<input onclick="var myvalue=prompt('您可以输入加密密码以保护您的任务。留空或取消则不设置密码！','')||'';submitbox('select_id','mylink/mylink_output/[id]/'+myvalue,'hiddenFrame')" type="button" value="导出选定链接" class="btnl" />
		<input onclick="goUrl('<?php echo Myqee::url('mylink/mylink_inputmodel')?>')" type="button" value="导入链接" class="btn" />
		<input type="button" class="btnl" value="更新全部链接" onclick="confirm('是否立即执行？\n\n将会将数据库中所有数据更新到配置文件里。',450,null,null,function(et){if(et=='ok')goUrl('<?php echo Myqee::url('mylink/renewfiles/')?>','hiddenFrame')});" />
	</td>
	<td class="td1" width="300" align="right">
		<input type="button" class="bbtn" value="新增链接" onclick="goUrl('<?php echo Myqee::url('mylink/mylinkadd')?>')" />
	</td>
</tr>
</table>
<center><?php echo $page; ?></center>

<?php View::factory('admin/footer') -> render(TRUE);?>