<?php
$page_title = '碎片管理';
$page_index = 'info';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>

<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; 
<a href="<?php echo Myqee::url('info/index');?>">内容管理</a> -&gt; 
<a href="<?php echo Myqee::url('block/index');?>">碎片管理</a> 
<?php 
if (is_array($location)){
	foreach ($location as $item){
		echo ' -&gt; <a href="',Myqee::url('block/index/'.$item['classid']),'">',$item['classname'],'</a>';
	}
	echo ' -&gt; 子栏目碎片';
}else{
	echo ' -&gt; 栏目碎片';
}
?>
</div>
<style type="text/css">
/* 　 */
.tree_0,.tree_1,.tree_2,.tree_3{width:18px;height:26px;float:left;}
/* │ */
.tree_1{background:url(<?php echo ADMIN_IMGPATH;?>/admin/tree_3.gif);}
/* ├ */
.tree_2{background:url(<?php echo ADMIN_IMGPATH;?>/admin/tree_5.gif) 0 1px;}
/* └ */
.tree_3{background:url(<?php echo ADMIN_IMGPATH;?>/admin/tree_4.gif) 0 2px;}
</style>


<div class="mainTable">
<ul class="ul tag">
	<li style="float:right;margin-right:0" onclick="goUrl('<?php echo Myqee::url('block/mylist'); ?>')">碎片参数设置</li>
	<li class="now">系统栏目页碎片</li>
	<!-- <li>系统专题页碎片</li> -->
	<li onclick="goUrl('<?php echo Myqee::url('block/custompage');?>')">自定义页面碎片</li>
	<li onclick="goUrl('<?php echo Myqee::url('block/customlist');?>')">自定义列表页碎片</li>
	<li onclick="goUrl('<?php echo Myqee::url('block/siteindex');?>')">子站点首页碎片</li>
</ul>
</div>
<div style="clear: both"></div>
<table border="0" cellpadding="0" cellspacing="1" width="96%" align="center" class="tableborder">
<tr>
	<th class="td1" width="40" height="26">序号</th>
	<th class="td1" width="60">栏目ID</th>
	<th class="td1">栏目名称</th>
	<th class="td1" width="320">碎片维护</th>
</tr>
<tr class="td3" onmouseover="tr_moveover(this)" onmouseout="tr_moveout(this)">
	<td class="td1" align="center" height="26">&nbsp;</td>
	<td class="td1" align="center">&nbsp;</td>
	<td class="td2">&nbsp;<strong>网站首页</strong></td>
	<td class="td2" align="center">
	<input class="btn2" onclick="goUrl('<?php echo Myqee::url('block/view_edit/index');?>','_blank')" style="font-weight:bold;" type="button" value="首页" />
	<input class="btn2 btn_disabled" disabled="disabled" type="button" value="列表页" />
	<input class="btn2 btn_disabled" disabled="disabled" type="button" value="内容页"" />
	<input class="btn2 btn_disabled" disabled="disabled" type="button" value="搜索页" />
	</td>
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
		if ( $i == $countlist){
			$outspacer = $spacer . '<span class="tree_3"></span>';
		}else{
			$outspacer = $spacer . '<span class="tree_2"></span>';
		}
?>
<tr<?php if($count_i%2==0){echo ' class="td3"';} ?> onmouseover="tr_moveover(this)" onmouseout="tr_moveout(this)">
	<td class="td1" align="center"><?php echo $count_i;?></td>
	<td class="td1" align="center"><?php echo $item['classid']?></td>
	<td class="td2"><table border="0" cellpadding="0" cellspacing="0"><tr><td><?php echo $outspacer;?></td><td>&nbsp;<a href="<?php echo Myqee::url('block/index/'.$item['classid']);?>"><?php echo $item['classname']?></a></td></tr></table></td>
	<td class="td2" align="center">
	<input <?php echo $item['iscover']?'class="btn2" onclick="goUrl(\''. Myqee::url('block/view_edit/cover/'.$item['classid']).'\',\'_blank\')"':'class="btn2 btn_disabled" disabled="disabled"';?> type="button" value="页封面" />
	<input <?php echo $item['islist']?'class="btn2" onclick="goUrl(\''. Myqee::url('block/view_edit/list/'.$item['classid']).'\',\'_blank\')"':'class="btn2 btn_disabled" disabled="disabled"';?> type="button" value="列表页" />
	<input <?php echo $item['iscontent']?'class="btn2" onclick="goUrl(\''. Myqee::url('block/view_edit/content/'.$item['classid']).'\',\'_blank\')"':'class="btn2 btn_disabled" disabled="disabled"';?> type="button" value="内容页"" />
	<input <?php echo $item['issearch']?'class="btn2" onclick="goUrl(\''. Myqee::url('block/view_edit/search/'.$item['classid']).'\',\'_blank\')"':'class="btn2 btn_disabled" disabled="disabled"';?> type="button" value="搜索页" />
	</td>
</tr>
<?php
		if ($item['sonclassarray']){
			if ( $i == $countlist){
				$spacer .= '<span class="tree_0"></span>';
			}else{
				$spacer .= '<span class="tree_1"></span>';
			}
			listclass($item['sonclassarray'],$spacer);
			$spacer = substr($spacer,0,-strlen('<span class="tree_0"></span>'));
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

	<table border="0" cellpadding="5" cellspacing="1" width="96%" align="center" class="tableborder" style="border-top:none;">
	<tr>
		<td class="td1" align="right">
		<input onclick="goUrl('<?php echo Myqee::url('block/mylist'); ?>')" type="button" value="碎片参数设置" class="btnl" /> 
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