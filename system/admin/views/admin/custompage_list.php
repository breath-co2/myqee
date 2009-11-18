<?php
$page_index = 'info';
$page_title = '自定义页管理';
View::factory ( 'admin/header', array ('page_index' => $page_index, 'page_title' => $page_title ) )->render ( TRUE );
?>
<div class="loaction">您的位置：<a href="<?php
echo Myqee::url ( 'index' );
?>">管理首页</a> 
<?php
echo ' -&gt; <a href="' . Myqee::url ( 'custompage/index' ) . '">' . $page_title . '</a>';
?> <?php
if ($cate) {
	echo ' -&gt; <a href="' . Myqee::url ( 'custompage/index/1/' . urlencode ( $cate ) ) . '">' . $cate . '分类</a>';
}
?>
</div>
<script type="text/javascript">
	var weburl = '<?php 
	$siteurl = Myqee::config('core.mysite_url');
	if (substr($siteurl,0,7)=='http://'){
		$host = $siteurl;
	}else{
		$host = 'http://'.Myqee::config('core.mysite_domain').$siteurl;
	}
	echo $host;
	?>';
</script>
<table border="0" cellpadding="4" cellspacing="1" width="96%" align="center" class="tableborder">
	<tr>
		<th class="td1" width="20">&nbsp;</th>
		<th class="td1" width="40">ID</th>
		<th class="td2">页面分类/页面名称</th>
		<th class="td2">文件路径</th>
		<th class="td2" width="140">创建时间</th>
		<th class="td2" width="40">启用</th>
		<th class="td2" width="60">使用模板</th>
		<th class="td2" width="320">操作</th>
	</tr>
<?php
if (is_array ( $list )) :
	$i = 0;
	$makesuredelete = '确认删除自定义页面?';
	foreach ( $list as $item ) :
		$i ++;
		$thefile = $item ['filepath'].($item ['filepath'] == '' ? '' : '/'). $item ['filename'].$item ['filename_suffix'];
?>
	<tr align="center" <?php if ($i % 2 == 0) {echo ' class="td3"';}?>>
		<td class="td1"><input type="checkbox" id="select_id_<?php echo $item ['id'];?>" /></td>
		<td class="td1"><?php echo $item ['id'];?></td>
		<td class="td2" align="left"><?php if ($item ['cate']) :?><a class="classlink" href="<?php echo Myqee::url ( 'custompage/index/1/' . urlencode ( $item ['cate'] ) );?>">[<?php echo $item ['cate'];?>]</a> <?php endif;?>
		<?php echo $item ['pagename'];?>
		<a href="#" onclick="goUrl(weburl+'<?php echo $thefile;?>','_blank');return false;" title="点击访问"><img src="<?php echo ADMIN_IMGPATH;?>/admin/external.png" /></a>
		</td>
		<td class="td2" align="left"><?php echo $thefile;?></td>
		<td class="td2"><?php echo date ( "Y-m-d H:i:s", $item ['createtime'] );?></td>
		<td class="td2"><?php echo $item ['isuse'] ? '是' : '<font color="red">否</font>';?></td>
		<td class="td2"><?php echo $item ['istpl'] ? '<font color="red">是</font>' : '否';?></td>
		<td class="td2">
		<input onclick="goUrl('<?php echo Myqee::url ( 'custompage/renew/' . $item ['id'] );?>','hiddenFrame');" type="button" value="重新生成" class="btn" />
		<input<?php if(!$item ['istpl']){echo ' disabled="disabled"';}?> onclick="goUrl('<?php echo Myqee::url ( 'block/view_edit/custompage/' . $item['id'] );?>','_blank')" type="button" value="编辑页面碎片" class="btnl" /> 
		<input onclick="goUrl('<?php echo Myqee::url ( 'custompage/edit/' . $item ['id'] );?>')" type="button" value="修改" class="btns" /> 
		<input onclick="confirm('<?php echo $makesuredelete;?>',null,null,null,function(t){if(t=='ok')goUrl('<?php echo Myqee::url ( 'custompage/del/' . $item ['id'] );?>','hiddenFrame')});" type="button" value="删除" class="btns" />
		</td>
	</tr>
<?php
	endforeach;

endif;
?>
</table>
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-top: none">
	<tr>
		<td class="td1" width="20" align="center"><input type="checkbox"
			title="选择上面全部" onclick="selectbox(this,'select_id')" /></td>
		<td class="td1" align="left">
			<input onclick="confirm('确认删除选中页面？',null,null,null,function(t){if(t=='ok')submitbox('select_id','custompage/del/[id]/','hiddenFrame')});" type="button" value="删除选中页面" class="btnl" />
			<input onclick="submitbox('select_id','custompage/renew/[id]/','hiddenFrame');" type="button" value="更新选中页面" class="btnl" />
			<input type="button" value="生成全部页面" class="btnl" onclick="goUrl('<?php echo Myqee::url('task/tohtml/tocustompage') ?>','_blank')" /> 
	    </td>
		<td class="td1" align="right" width="320"><input type="button" class="bbtn" value="新增页面" onclick="goUrl('<?php echo Myqee::url ( 'custompage/add' );?>');" /></td>
	</tr>
</table>
<center>
<script type="text/javascript">
function subForm(){
	confirm('是否删除所选自定义页？',350,null,null,function(t){
		if(t=='ok') 
			submitbox('select_id','custompage/del/[id]','hiddenFrame')
			}
	);
}
</script>
			
<?php
echo $page;
?></center>
<?php View::factory('admin/footer') -> render(TRUE);?>