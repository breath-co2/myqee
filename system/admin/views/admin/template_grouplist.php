<?php
$page_title = '模板列表';
$page_index = 'template';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<div class="loaction">

您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('template/index');?>">模板管理</a> -&gt; 模板组管理
</div>

<table border="0" cellpadding="4" cellspacing="1" width="96%" align="center" class="tableborder">
<tr>
	<th class="td1"><?php echo Myqee::lang('admin/template.list.groupname');?></th>
	<th class="td1"><?php echo Myqee::lang('admin/template.list.grouppath');?></th>
	<th class="td1"><?php echo Myqee::lang('admin/template.list.tplengine');?></th>
	<th class="td1" width="98"><?php echo Myqee::lang('admin/template.list.nowsiteuse');?></th>
	<th class="td1" width="40"><?php echo Myqee::lang('admin/template.list.isdefaultgroup');?></th>
	<th class="td1" width="110"><?php echo Myqee::lang('admin/template.list.groupview');?></th>
	<th class="td1" width="220"><?php echo Myqee::lang('admin/template.list.do');?></th>
</tr>
<?php
if($list):
	$i=0;
	$mytype = array(
		'cover' => '封面',
		'list' => '列表',
		'content' => '内容',
		'search' => '搜索',
	);
	$makesuredelete = Myqee::lang('admin/template.list.makesuredeletegroup');
	$tplgroup = MyqeeCMS::config('template');
	$engine = Myqee::config('template.engine');
	if ($_SESSION['now_site_tlpgroup']){
		$usegroup = $_SESSION['now_site_tlpgroup'];
	}else{
		$usegroup = $tplgroup['default'];
	}
	foreach ($list as $key => $item):
	$i++;
?>
<tr align="center"<?php if($i%2==0){echo ' class="td3"';} ?> onmouseover="tr_moveover(this)" onmouseout="tr_moveout(this)">
	<td class="td2"><?php echo $item['name'];?></td>
	<td class="td2" align="left"><font color="#12C450">MYAPPPATH/views/</font><font style="color:#FF6633;"><?php echo $key?></font>/</td>
	<td class="td2">
	<?php echo !$engine[$item['engine']]?'无':$engine[$item['engine']]['name'];?>
	</td>
	<td class="td2">
	<?php echo $usegroup == $key ? '<font color="red">使用中</font>':'未使用';?>
	</td>
	<td class="td2">
	<?php echo $defaultgroup == $key ? '<font color="red">是</font>':'否';?>
	</td>
	<td class="td2">
	<input onclick="goUrl('<?php echo Myqee::url('template/changegroup/' . $key) ;?>')" type="button" value="进入此模板组" class="btnl" />
	</td>
	<td class="td2">
	<input onclick="var myvalue=prompt('您可以输入加密密码以保护您的模板。留空或取消则不设置密码！','')||'';goUrl('<?php echo 'template/outputgroup/'.$key ;?>/'+myvalue,'hiddenFrame')" type="button" value="导出模板组" class="btnl" />
	<input onclick="goUrl('<?php echo Myqee::url('template/editgroup/'.$key) ;?>')" type="button" value="修改" class="btns" />
	<input onclick="ask_del('<?php echo $key;?>','template/delgroup','<?php echo $makesuredelete;?>');" type="button" value="删除" class="btns" />
	</td>
</tr>
<?php
	endforeach;
endif;
?>
</table>
<table border="0" cellpadding="4" cellspacing="1" width="96%" align="center" class="tableborder" style="border-top:none;">
<tr>
	<td class="td1">只有默认模板组才会被启用，若需要使用非默认模板组的模板，请建立站点后绑定模板组</td>
	<td class="td1" width="340" align="right">
	<input onclick="goUrl('<?php echo Myqee::url('template/'); ?>')" type="button" value="模板管理" class="btn" /> 
	<input type="button" class="bbtn" value="新建模板组" style="letter-spacing:0" onclick="goUrl('<?php echo Myqee::url('template/newgroup')?>')" /> 
	<input onclick="goUrl('<?php echo Myqee::url('template/add/'); ?>')" type="button" value="创建模板" class="bbtn" />
	</td>
</tr>
</table>

<center><?php echo $page; ?></center>

<?php View::factory('admin/footer') -> render(TRUE);?>