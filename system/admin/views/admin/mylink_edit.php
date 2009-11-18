<?php
$page_index = 'info';
$page_title = '快速链接';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('mylink/index');?>">快速链接</a> -&gt;
<?php
$defaulttarget = array(''=>'默认','_self'=>'相同窗口','_blank'=>'新窗口','_parent'=>'父窗口','[other]'=>'其它','[other2]'=>'自定义');
if ($isedit == true){
	echo '编辑链接：'.$data['name'];
}else{
	echo '添加链接';
}
?>
</div>

<form action="<?php echo Myqee::url('mylink/mylinksave/'.$id);?>" method="POST" target="hiddenFrame">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" width="100%">
	<tr>
		<th class="td1" colspan="2">基本设置</th>
	</tr>
	<tr>
		<td class="td1" align="right" width="150">链接名称：</td>
		<td class="td2" align="left">
			<?php echo form::input('mylink[name]',$data['name'],'class="input" size="30"');?>
		</td>
	</tr>
	<tr>
		<td class="td1" align="right">是否启用：</td>
		<td class="td2" align="left">
			<?php echo form::radio('mylink[is_use]',1,!isset($data['is_use']) || (int)$data['is_use']==1);?>是 
			<?php echo form::radio('mylink[is_use]',0,isset($data['is_use']) && (int)$data['is_use']==0);?>否
		</td>
	</tr>
	<tr>
		<td class="td1" align="right">数据调用：</td>
		<td class="td2" align="left">
			<table border="0" cellpadding="4" cellspacing="1" align="left" style="background:#e7f5f9 url(tablebg.gif) repeat-x left top" border="1px solid #739cb5" width="30%">
			<tr>
				<td width="120" style="background-color: #e8f3f8;border-left:1px solid #c3d3dc;color:#2c4150;border-top:1px solid #c3d3dc;" align="right">数据调用ID：</td>
				<td width="160" style="background-color: #FFFFFF;border-left:1px solid #c3d3dc;color:#2c4150;border-top:1px solid #c3d3dc;" align="left">
					<?php echo form::input('mylink[mydata_id]',$data['mydata_id'],'class="input" size="15"');?>
				</td>
			</tr>
			<tr>
				<td width="120" style="background-color: #e8f3f8;border-left:1px solid #c3d3dc;color:#2c4150;border-top:1px solid #c3d3dc;" align="right">标题对应的字段：</td>
				<td width="160" style="background-color: #FFFFFF;border-left:1px solid #c3d3dc;color:#2c4150;border-top:1px solid #c3d3dc;" align="left">
					<?php echo form::input('mylink[mydata_title]',$data['mydata_title'],'class="input" size="15"');?>
				</td>
			</tr>
			<tr>
				<td style="background-color: #e8f3f8;border-left:1px solid #c3d3dc;color:#2c4150;border-top:1px solid #c3d3dc;" align="right">打开方式：</td>
				<td style="background-color: #FFFFFF;border-left:1px solid #c3d3dc;color:#2c4150;border-top:1px solid #c3d3dc;" align="left">
					<?php echo form::dropdown('mylink[mydata_target]',$defaulttarget,$defaulttarget[$data['mydata_target']]?$data['mydata_target']:'[other]','style="width:104px;" id="mylink[mydata_target]" onchange="if(this.value==\'[other]\'||this.value==\'[other2]\'){this.style.display=\'none\';$(\'mylink[mydata_target2]\').style.display=\'block\';$(\'mylink[mydata_target2]\').focus()}"')?>
					<input type="text" name="mylink[mydata_target2]" value="<?php echo $data['mydata_target'];?>" id="mylink[mydata_target]" class="input" size="15" style="display:none;" onblur="$('mylink[mydata_target]').value=this.value==''?'':'[other]';this.style.display='none';$('mylink[mydata_target]').style.display='';" />
				</td>
			</tr>
			<tr>
				<td style="background-color: #e8f3f8;border-left:1px solid #c3d3dc;color:#2c4150;border-top:1px solid #c3d3dc;" align="right">默认排序：</td>
				<td style="background-color: #FFFFFF;border-left:1px solid #c3d3dc;color:#2c4150;border-top:1px solid #c3d3dc;" align="left">
					<?php echo form::input('mylink[mydata_order]',$data['mydata_order'],'class="input" size="15"');?>
				</td>
			</tr>
			<tr>
				<td style="background-color: #e8f3f8;border-left:1px solid #c3d3dc;color:#2c4150;border-top:1px solid #c3d3dc;" align="right">运行条数(条)：</td>
				<td style="background-color: #FFFFFF;border-left:1px solid #c3d3dc;color:#2c4150;border-top:1px solid #c3d3dc;" align="left">
					<?php echo form::input('mylink[mydata_limit]',$data['mydata_limit'],'class="input" size="15"');?>
				</td>
			</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="td1" align="right">排序(序号)：</td>
		<td class="td2" align="left">
			<?php echo form::input('mylink[myorder]',$data['myorder'],'class="input" size="5"');?>
		</td>
	</tr>
</table>
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-top:none;">
	<tr>
		<td class="td1" width="150">&nbsp;</td>
		<td class="td1" align="left"><input type="submit" class="bbtn" value="保存任务" /> 
			<input type="button" onclick="goback(-1,'<?php echo Myqee::url('mylink/index');?>')" class="btns" value="返回" />
		</td>
	</tr>
</table>
</form>
<?php View::factory('admin/footer') -> render(TRUE);?>