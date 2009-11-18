<?php
$page_index = 'info';
if ($_GET['fullpage'] == 'yes'):
	$page_title = '评论管理 ';
	View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<script type="text/javascript">
	function changeHeight(){return false;}
</script>
<?php
else:
	$showheader = '';
	View::factory('admin/header_frame') -> render(TRUE);
?>
<script type="text/javascript">
var makesuredelete = '<?php echo Myqee::lang('admin/comment.list.makesuredelete');?>';
</script>
<?php
endif;
?>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>" target="_top">管理首页</a> -&gt; 评论管理
</div>
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-bottom:none;">
<tr>
<th class="td1" width="20">&nbsp;</th>
	<th class="td1" width="60"><?php echo Myqee::lang('admin/comment.list.id');?></th>
	<th class="td1"><?php echo Myqee::lang('admin/comment.list.username');?>/<?php echo Myqee::lang('admin/comment.list.news_title');?></th>
	<th class="td1" width="100"><?php echo Myqee::lang('admin/comment.list.addip');?></th>
	<th class="td1" width="120"><?php echo Myqee::lang('admin/comment.list.addtime');?></th>
	<th class="td1" width="30">推荐</th>
	<th class="td1" width="30">审核</th>
	<th class="td1" width="200"><?php echo Myqee::lang('admin/comment.list.do');?></th>
</tr>
</table>
<?php
if(is_array($list)):
	foreach ($list as $item):
?>
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
<tr align="center">
<td class="td1" width="20"><input type="checkbox" id="select_id_<?php echo $item['id'];?>>" /></td>
<td class="td1" width="60"><?php echo $item['id'];?></td>
<td class="td1" align="left">“<?php echo $item['username'];?>”说：<?php echo $item['news_title'];?></td>
<td class="td1" width="100"><?php echo $item['addip'];?>&nbsp;</td>
<td class="td1" width="120"><?php echo date("Y-m-d H:i", $item['addtime'])?>&nbsp;</td>
<td class="td1" width="30"><?php echo ($item['is_commend']?'<font color="red">是</font>':'否');?>&nbsp;</td>
<td class="td1" width="30"><?php echo ($item['is_checked']?'是':'否');?>&nbsp;</td>
<td class="td1" width="200">
<input <?php if($item['is_checked'])echo 'disabled="disabled" ';?>onclick="goUrl('<?php echo Myqee::url('comment/checked/'.$item['id']);?>','hiddenFrame')" type="button" value="审核" class="btns" /><?php if($item['is_commend']):?><input onclick="goUrl('<?php echo Myqee::url('comment/uncommend/'.$item['id']);?>','hiddenFrame')" type="button" value="取消评论" class="btn" /><?php else:?><input onclick="goUrl('<?php echo Myqee::url('comment/commend/'.$item['id']);?>','hiddenFrame')" type="button" value="推荐评论" class="btn" /><?php endif;?><input onclick="ask_del(<?php echo $item['id'];?>,'comment/del',makesuredelete);" type="button" value="删除" class="btns" />
</td>
</tr>
</table>
<table border="0" cellpadding="6" cellspacing="1" align="center" class="tableborder" style="border-top:none;">
<tr>
<tr>
<td><?php echo $item['comment'];?>&nbsp;</td>
</tr>
</table><div style="clear:both;width:1px;height:6px;overflow:hidden"></div>
<?php 
	endforeach;
endif;
?>
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-top:none">
	<tr>
	<td class="td1" width="10" align="center"><input type="checkbox" title="选择上面全部" onclick="selectbox(this,'select_id')" /></td>
	
<td class="td1">
<input type="button" value="删除" class="btns" onclick="confirm('确认执行删除操作？',null,null,'请确认',function(t){if(t=='ok')submitbox('select_id','comment/del/[id]/','hiddenFrame')})" />
<input type="button" value="审核" class="btns" onclick="confirm('确认执审核操作？',null,null,'请确认',function(t){if(t=='ok')submitbox('select_id','comment/checked/[id]/','hiddenFrame')})" />
<input type="button" value="推荐" class="btns" onclick="confirm('确认执行推荐评论操作？',null,null,'请确认',function(t){if(t=='ok')submitbox('select_id','comment/commend/[id]/','hiddenFrame')})" />
<input type="button" value="取消推荐" class="btn" onclick="confirm('确认执行取消推荐操作？',null,null,'请确认',function(t){if(t=='ok')submitbox('select_id','comment/uncommend/[id]/','hiddenFrame')})" />

</td>
	</tr>
</table>
<center><?php echo $page; ?></center>


<?php
if ($_GET['fullpage'] == 'yes'):
View::factory('admin/footer') -> render(TRUE);
else:
?>
<script type="text/javascript">
myqee();
changeHeight();
</script>
</body>
</html>

<?php
endif;
?>