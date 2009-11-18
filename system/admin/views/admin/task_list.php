<?php
$page_index = 'task';
$page_title = '任务管理';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('task/index');?>">任务管理</a> -&gt; 所有计划任务
</div>

<script type="text/javascript">
var makesuredelete='您确认要删除此任务？';
</script>

<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
<tr>
	<th class="td1" colspan="2">计划任务摘要情况</th>
</tr>
<tr>
	<td class="td1" width="20%" align="right">允许运行自动任务：</td>
	<td class="td2">
	<span style="width:20px;overflow:hidden;display:inline-block;text-align:center;">是</span>
	<input type="button" class="btns" value="禁用" onclick="" />
	<font color="#999999"> &nbsp;&nbsp;只有在允许运行自动任务的情况下，下面的设置才有效。</font>
	</td>
</tr>
<tr>
	<td class="td1" align="right">监控状态：</td>
	<td class="td2">
	<span style="width:20px;overflow:hidden;display:inline-block;text-align:center;"><?php
	if ($is_task_run===true):
	?><img src="<?php echo ADMIN_IMGPATH?>/admin/run_ok.gif" alt="正在运行" /></span>
	<input type="button" class="btns" value="关闭" onclick="goUrl('<?php echo Myqee::url('task/run_task/0');?>','hiddenFrame')" />
	<?php
	else:
	?><img src="<?php echo ADMIN_IMGPATH?>/admin/run_no.gif" alt="已停止运行" /></span>
	<input type="button" class="btns" value="开启" onclick="goUrl('<?php echo Myqee::url('task/run_task/1');?>','hiddenFrame')" /><?php
	endif;
	?>
	 
	<font color="#999999"> &nbsp;&nbsp;关闭监控系统需要5秒左右才会生效。</font>
	</td>
</tr>
</table>

<br />

<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
<tr>
	<th class="td1" width="20">&nbsp;</th>
	<th class="td1" width="50">ID</th>
	<th class="td1">计划任务类别/计划任务名称</th>
	<th class="td1" width="30">启用</th>
	<th class="td1" width="110">下次执行时间</th>
	<th class="td1" width="80">周期</th>
	<th class="td1" width="80">最大次数</th>
	<th class="td1" width="80">脚本</th>
	<th class="td1" width="80">模式</th>
	<th class="td1" width="200">操作</th>
</tr>
<?php
if(is_array($list)):
	$i=0;
	$mycyclecat = array(
	'1' => '秒种',
	'2' => '分种',
	'3' => '小时',
		'4' => '日',
		'5' => '周',
		'6' => '月'
	);
	foreach ($list as $item):
	$i++;
?>
<tr align="center"<?php if($i%2==0){echo ' class="td3"';} ?>>
	<td class="td1"><input type="checkbox" id="select_id_<?php echo $item['id'];?>" /></td>
	<td class="td1"><?php echo $item['id'];?></td>
	<td class="td2"><?php if ($item['cate']):?><a class="classlink" href="<?php echo Myqee::url('task/index/1/'. urlencode($item['cate'])) ;?>">[<?php echo $item['cate'];?>]</a> <?php endif;?><?php echo $item['name'];?></td>
	<td class="td2"><?php echo $item['isuse']?'<font color="red">是</font>':'否';?></td>
	<td class="td2"><?php echo $item['nexttime'];?>&nbsp;</td>
	<td class="td2">每<?php echo $item['cycle'];?>(<?php echo $mycyclecat[$item['cycletype']];?>)</td>
	<td class="td2"><?php echo $item['maxtimes']!=0?$item['maxtimes']:"不限";?>次</td>
	<td class="td2"><?php echo $item['taskfile'];?>&nbsp;</td>
	<td class="td2"><?php echo $item['taskmode'];?>&nbsp;</td>
	<td class="td2"><input onclick="goUrl('<?php echo Myqee::url('task/edit/'.$item['id']) ;?>')" type="button" value="修改" class="btns" /><input onclick="goUrl('<?php echo Myqee::url('task/copy/'.$item['id']) ;?>')" type="button" value="复制" class="btns" />
	<input onclick="confirm('<?php echo $makesuredelete;?>',null,null,null,function(t){if(t=='ok')goUrl('<?php echo Myqee::url('task/del/'.$item['id']);?>','hiddenFrame')});" type="button" value="删除" class="btns" />
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
	    <input onclick="var myvalue=prompt('您可以输入加密密码以保护您的任务。留空或取消则不设置密码！','')||'';submitbox('select_id','task/output/[id]/'+myvalue,'hiddenFrame')" type="button" value="导出选定任务" class="btnl" />
	    <input onclick="goUrl('<?php echo Myqee::url('task/inputtask')?>')" type="button" value="导入任务" class="btn" />
	    <input type="button" class="btnl" value="更新任务文件" onclick="confirm('是否立即执行？\n\n将会将数据库中所有数据更新到文件里。',350,null,null,function(t){if(t=='ok')goUrl('<?php echo Myqee::url('task/renewfiles')?>','hiddenFrame')});" />
	    
	 </td>
	<td class="td1" width="320" align="right"><input type="button" class="bbtn" value="新增任务" onclick="goUrl('<?php echo Myqee::url('task/add')?>')" /></td>
</tr>
</table>

<center><?php echo $page; ?></center>


<?php View::factory('admin/footer') -> render(TRUE);?>