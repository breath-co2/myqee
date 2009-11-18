<?php
$page_index = 'info';
$page_title = '数据调用';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; 
<a href="<?php echo Myqee::url('mydata/index');?>">数据调用</a> -&gt; 
所有数据调用
</div>

<script type="text/javascript">
var makesuredelete='您确认要删除此数据调用吗？';
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
		goUrl('<?php echo Myqee::url('mydata/editorder') ?>?order='+myneworder , 'hiddenFrame');
	}
}
</script>
<table border="0" cellpadding="4" cellspacing="1" width="96%" align="center" class="tableborder">
<tr>
	<th class="td1" width="20">&nbsp;</th>
	<th class="td1" width="40">排序</th>
	<th class="td1" width="40">ID</th>
	<th class="td1">分类/名称</th>
	<th class="td1">栏目/模型/数据表</th>
	<th class="td1" width="60">操作方式</th>
	<th class="td1" width="30">启用</th>
	<th class="td1" width="80">查看数据</th>
	<th class="td1" width="240">操作</th>
</tr>
<?php
if(is_array($list)):
	$i=0;
	foreach ($list as $item):
	$i++;
?>
<tr align="center"<?php if($i%2==0){echo ' class="td3"';} ?> onmouseover="tr_moveover(this)" onmouseout="tr_moveout(this)">
	<td class="td2"><input type="checkbox" id="select_id_<?php echo $item['id'];?>" /></td>	
	<td class="td1" width="40" >
	<input type="hidden" id="oldorder_<?php echo $item['id']?>" value="<?php echo $item['myorder']?>" />
	<input type="text" id="myorder_<?php echo $item['id']?>" name="myorder_<?php echo $item['id']?>" value="<?php echo $item['myorder'];?>" size="3" onkeydown="iseditorder(event)" class="input" />
	</td>
	<td class="td1"><?php echo $item['id'];?></td>
	<td class="td2" align="left"><?php if ($item['cate']):?><a class="classlink" 
	href="<?php if(!is_null($_GET['type']) && $_GET['type'] != ''){echo Myqee::url('mydata/index?type=' . urlencode($item['type']) . '&cate=' . urlencode($item['cate']));}
	else{ echo Myqee::url('mydata/index?cate=' . urlencode($item['cate']));}?>">
	[<?php echo $item['cate'];?>]</a> <?php endif;?><?php echo $item['name'];?></td>
	<td class="td2" align="left" style="color:#12C450"><?php if($item['type'] == 1){echo '<font color="red">SQL语句方式</font>';}elseif($item['tpye'] == 0){echo $item['classname']?$item['classname']:'无';?>，<?php echo $item['modelname']?$item['modelname']:'无';?>，<?php echo $item['dbname']?$item['dbname']:'无';}?></td>
	<td class="td1"><?if ($item['type']){echo '<a class="classlink" 
	href="';if(!is_null($_GET['cate']) && $_GET['cate'] != ''){echo Myqee::url('mydata/index?type=' . urlencode($item['type']) . '&cate=' . urlencode($item['cate']));}
	else {echo Myqee::url('mydata/index?type=' . urlencode($item['type']));}echo '" title="SQL语句" ><font color="red">SQL语句</font></a>';}else{echo '<a class="classlink" 
	href="';if(!is_null($_GET['cate']) && $_GET['cate'] != ''){echo Myqee::url('mydata/index?type=' . urlencode($item['type']) . '&cate=' . urlencode($item['cate']));}
	else {echo Myqee::url('mydata/index?type=' . urlencode($item['type']));}echo '" title="自动生成" >自动生成</a>';}?></td>
	<td class="td1"><?if ($item['is_use']){echo '是';}else{echo '<font color="red">否</font>';}?></td>
	<td class="td1"><input onclick="goUrl('<?php echo Myqee::url('mydata/copy/'.$item['id']) ;?>')" type="button" value="查看数据" class="btn" /></td>
	<td class="td2">
	<input onclick="var myvalue=prompt('您可以输入加密密码以保护您的模板。留空或取消则不设置密码！','')||'';goUrl('<?php echo ADMIN_URLPATH.'mydata/output/'.$item['id'] ;?>/'+myvalue,'hiddenFrame')" type="button" value="导出任务" class="btn2" />
	<input onclick="goUrl('<?php echo Myqee::url('mydata/copy/'.$item['id']) ;?>')" type="button" value="复制" class="btns" />
	<input onclick="goUrl('<?php echo Myqee::url('mydata/edit/'.$item['id']) ;?>')" type="button" value="修改" class="btns" />
	<input onclick="ask_del(<?php echo $item['id'];?>,'mydata/del',makesuredelete);" type="button" value="删除" class="btns" />
	</td>
</tr>
<?php 
	endforeach;
endif;
?>
</table>
<div style="height:40px">
	<div id="control_div" style="width:100%;min-width:800px" class="control">
	<img src="<?php echo ADMIN_IMGPATH;?>/admin/spacer.gif" style="width:800px;height:0px" /><br/>

<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-top:none;">
<tr>
	<td class="td1" width="20" align="center"><input type="checkbox" title="选择上面全部" onclick="selectbox(this,'select_id')" /></td>
	<td class="td1">
	<input onclick="saveOrder();" type="button" value="修改栏目排序" class="btnl" />
	<input onclick="var myvalue=prompt('您可以输入加密密码以保护您的任务。留空或取消则不设置密码！','')||'';submitbox('select_id','mydata/output/[id]/'+myvalue,'hiddenFrame')" type="button" value="导出选定任务" class="btnl" />
	<input onclick="goUrl('<?php echo Myqee::url('mydata/input')?>')" type="button" value="导入调用" class="btn" />
	<input type="button" class="btnl" value="更新调用配置" onclick="confirm('是否立即执行？\n\n将会将数据库中所有数据更新到配置文件里。',400,null,null,function(t){if(t=='ok')goUrl('<?php echo Myqee::url('mydata/renewfiles')?>','hiddenFrame')});" />
	</td>
	<td class="td1" width="240" align="right">
	<input type="button" class="bbtn" value="新增调用" onclick="goUrl('<?php echo Myqee::url('mydata/add')?>')" />
	</td>
</tr>
</table>

</div>
</div>

<center><?php echo $page; ?></center>

<script type="text/javascript">
	set_control_fixed(160);
	window.onscroll = function(){set_control_fixed(160)};
</script>

<?php View::factory('admin/footer') -> render(TRUE);?>