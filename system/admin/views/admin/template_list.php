<?php
$page_index = 'template';
$page_title = Myqee::lang('admin/template.title.'.$tplname);
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<div class="loaction">

<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr>
<td>您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('template/index');?>"><?php echo $tplgroup[$nowtplgroup];?></a> -&gt; <a href="<?php echo Myqee::url('template/index/1/'. $tplname);?>"><?php echo $page_title;?></a><?php
if ($cate){
	echo ' -&gt; <a href="'.Myqee::url('template/index/1/'. $tplname.'/'.urlencode($cate)).'">'.$cate.'分类</a>';
}
?>
 -&gt; 模板列表</td>
<td align="right">当前模板组：<?php echo form::dropdown('',$tplgroup,$nowtplgroup,'onchange="goUrl(\''.Myqee::url_base().'template/changegroup/\'+this.value)"');?> <input onclick="goUrl('<?php echo Myqee::url('template/grouplist/');?>')" style="margin-right:0;letter-spacing:0" type="button" value="管理模板组" class="btn" /></td>
</tr></table>
</div>

<table border="0" cellpadding="4" cellspacing="1" width="96%" align="center" class="tableborder">
<tr>
	<th class="td1" width="20">&nbsp;</th>
	<th class="td1" width="60"><?php echo Myqee::lang('admin/template.list.id');?></th>
	<th class="td1" width="60"><?php echo Myqee::lang('admin/template.list.type');?></th>
	<th class="td1"><?php echo Myqee::lang('admin/template.list.class');?>/<?php echo Myqee::lang('admin/template.list.title');?></th>
	<th class="td1" title="存放于视图目录：<br/><font color=red><?php echo MYAPPPATH .'views/';?></font>"><?php echo Myqee::lang('admin/template.list.filepath');?></th>
	<th class="td1" width="30"><?php echo Myqee::lang('admin/template.list.isuse');?></th>
	<th class="td1" width="280"><?php echo Myqee::lang('admin/template.list.do');?></th>
</tr>
<?php
if($list):
	$i=0;
	$mytype = array(
		'cover' => '封面',
		'list' => '列表',
		'content' => '内容',
		'search' => '搜索',
		'block' => '模块',
		'frame' => '框架',
	'page' => '单页',
	);
	$makesuredelete = Myqee::lang('admin/template.list.makesuredeletetpl');
	foreach ($list as $item):
	$i++;
?>
<tr<?php if($i%2==0){echo ' class="td3"';} ?> onmouseover="tr_moveover(this)" onmouseout="tr_moveout(this)" ondblclick="change_select('select_id_<?php echo $item['id'];?>');return false;">
	<td class="td1" align="center"><input type="checkbox" id="select_id_<?php echo $item['id'];?>" onclick="select_tr(this)" /></td>
	<td class="td1" align="center"><?php echo $item['id'];?></td>
	<td class="td2" align="center"><a href="<?php echo Myqee::url('template/index/1/' .  $item['type']) ;?>"><?php echo $mytype[$item['type']];?></a></td>
	<td class="td2"><?php if ($item['cate']):?><a class="classlink" href="<?php echo Myqee::url('template/index/1/' . $tplname . '/' . urlencode($item['cate'])) ;?>">[<?php echo $item['cate'];?>]</a> <?php endif;?><?php echo $item['tplname'];?></td>
	<td class="td2" style="color:#12C450"><?php echo $item['group'].'/<font style="color:#b52">'.$item['filename'].$item['filename_suffix'].'</font>';?></td>
	<td class="td2" align="center"><?if ($item['isuse']){echo '是';}else{echo '<font color="red">否</font>';}?></td>
	<td class="td2" align="center">
	<input onclick="var myvalue=prompt('您可以输入加密密码以保护您的模板。留空或取消则不设置密码！','')||'';goUrl('<?php echo ADMIN_URLPATH.'template/output/'.$item['id'] ;?>/'+myvalue,'hiddenFrame')" type="button" value="导出模板" class="btn" />
	<input onclick="goUrl('<?php echo Myqee::url('template/copy/'.$item['id']) ;?>')" type="button" value="复制模板" class="btn" />
	<input onclick="goUrl('<?php echo Myqee::url('template/edit/'.$item['id']) ;?>')" type="button" value="修改" class="btns" />
	<input onclick="ask_del(<?php echo $item['id'];?>,'template/del','<?php echo $makesuredelete;?>');" type="button" value="删除" class="btns" />
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
	<td class="td1"><input onclick="var myvalue=prompt('您可以输入加密密码以保护您的模板。留空或取消则不设置密码！','')||'';submitbox('select_id','template/output/[id]/'+myvalue,'hiddenFrame')" type="button" value="导出模板" class="bbtn" />
	<input type="button" class="btn" value="导入模板" onclick="goUrl('<?php echo Myqee::url('template/inputtpl')?>')" />
	<input type="button" class="btnl" value="更新模板文件" onclick="confirm('是否立即执行？\n\n将会将数据库中所有数据更新到文件里。',350,null,null,function(t){if(t=='ok')goUrl('<?php echo Myqee::url('template/renewfiles')?>','hiddenFrame')});" />
    <input onclick="submitbox('select_id','template/dels/[id]/','hiddenFrame')" type="button" value="删除选定模板" class="btnl" />
	</td>
	<td class="td1" width="280" align="right"><input onclick="goUrl('<?php echo Myqee::url('template/add/'); ?>')" type="button" value="创建模板" class="bbtn" /><!--<input onclick="goUrl('<?php echo Myqee::url('template/add/list'); ?>')" type="button" value="添加列表模板" class="btnl" /><input onclick="goUrl('<?php echo Myqee::url('template/add/search'); ?>')" type="button" value="添加搜索模板" class="btnl" /><input onclick="goUrl('<?php echo Myqee::url('template/add/content'); ?>')" type="button" value="添加内容模板" class="btnl" /><input onclick="goUrl('<?php echo Myqee::url('template/add/page'); ?>')" type="button" value="添加单页模板" class="btnl" />--></td>
</tr>
</table>

<center><?php echo $page; ?></center>

<?php View::factory('admin/footer') -> render(TRUE);?>