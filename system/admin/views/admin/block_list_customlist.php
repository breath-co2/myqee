<?php
$page_title = '自定义列表碎片';
$page_index = 'info';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>

<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; 
<a href="<?php echo Myqee::url('info/index');?>">内容管理</a> -&gt; 
<a href="<?php echo Myqee::url('block/index');?>">碎片管理</a> -&gt; 
<?php
echo $page_title;
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
	<li onclick="goUrl('<?php echo Myqee::url('block/index');?>')">系统栏目页碎片</li>
	<!-- <li>系统专题页碎片</li> -->
	<li onclick="goUrl('<?php echo Myqee::url('block/custompage');?>')">自定义页面碎片</li>
	<li class="now">自定义列表页碎片</li>
	<li onclick="goUrl('<?php echo Myqee::url('block/siteindex');?>')">子站点首页碎片</li>
</ul>
</div>
<div style="clear: both"></div>
<table border="0" cellpadding="0" cellspacing="1" width="96%" align="center" class="tableborder">
<tr>
	<th class="td1" width="40" height="26">序号</th>
	<th class="td1" width="60">站点ID</th>
	<th class="td1">自定义列表名称</th>
	<th class="td1">页面路径</th>
	<th class="td1" width="180">碎片维护</th>
</tr>
<?php
if($list):
	$countlist = count($list);
	$i = 0;
	foreach ($list as $item):
		if(!Passport::getisallow_site($item['id']))continue;
		$i++;
		$file = $item['filepath'].'/'.$item['filename'].$item['filename_suffix'];
?>
<tr<?php if($i%2==0){echo ' class="td3"';} ?> onmouseover="tr_moveover(this)" onmouseout="tr_moveout(this)">
	<td class="td1" align="center"><?php echo $i;?></td>
	<td class="td1" align="center"><?php echo $item['id'];?></td>
	<td class="td2">&nbsp;<?php echo $item['pagename'];?>&nbsp;
	<a title="点击访问" href="http://<?php echo Myqee::config('core.mysite_domain').'/'.ltrim(str_replace('{{page}}','1',$file),'/');?>" target="_blank"><img src="<?php echo ADMIN_IMGPATH;?>/admin/external.png" /></a>
	</td>
	<td class="td2">&nbsp;<?php echo $file;?></td>
	<td class="td2" align="center" style="padding:3px;">
	<input class="btnl" onclick="goUrl('<?php echo Myqee::url('block/view_edit/customlist/'.$item['id']);?>','_blank')"' type="button" value="自定义列表碎片" />
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