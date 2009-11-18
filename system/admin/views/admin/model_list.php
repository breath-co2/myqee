<?php
$page_index = 'model';
$page_title = '模型管理';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; 模型管理
</div>

<script type="text/javascript">

function iseditorder(e){
	if (document.all)e=window.event;
	if (e.keyCode == 13){
		editorder();
	}
}
function editorder(){
	var inputs = document.getElementsByTagName('input');
	var myneworder = '';
	for (var k in inputs){
		if (inputs[k].id){
			if (inputs[k].type=='text' && inputs[k].id.substr(0,6) == 'order_' ){
				var thisid = inputs[k].id.substr(6);
				var newid = inputs[k].value.replace(/[^0-9]/g,'');
				var oldorderobj = $('oldorder_'+thisid);
				if (oldorderobj){
					if ( oldorderobj.value != newid){
						myneworder += 'id_'+thisid + '='+newid+',';
					}
				}
			}
		}
	}
	if (myneworder==''){
		alert('排序没有发生变化！');
	}else{
		goUrl('<?php echo Myqee::url('model/changemyorder'); ?>?order='+myneworder , 'hiddenFrame');
	}
}
</script>
<table border="0" cellpadding="4" cellspacing="1" width="96%" align="center" class="tableborder">
<tr>
	<th class="td1" width="20">&nbsp;</th>
	<th class="td1" width="40"><?php echo Myqee::lang('admin/model.list.myorder');?></th>
	<th class="td1" width="60"><?php echo Myqee::lang('admin/model.list.id');?></th>
	<th class="td1"><?php echo Myqee::lang('admin/model.list.title');?></th>
	<th class="td1" width="120"><?php echo Myqee::lang('admin/model.list.database');?></th>
    <th class="td1" width="120"><?php echo Myqee::lang('admin/model.list.tablename');?></th>
	<th class="td1" width="70"><?php echo Myqee::lang('admin/model.list.isdefault');?></th>
	<th class="td1" width="40"><?php echo Myqee::lang('admin/model.list.isuse');?></th>
	<th class="td1" width="320"><?php echo Myqee::lang('admin/template.list.do');?></th>
</tr>
<?php
if(is_array($list)):
	$i=0;
	$makesuredelete = Myqee::lang('admin/model.list.makesuredeletemodel');
	foreach ($list as $item):
	list ($database,$tablename) = explode ('/',$item['dbname']);
	$i++;
?>
<tr align="center"<?php if($i%2==0){echo ' class="td3"';} ?> onmouseover="tr_moveover(this)" onmouseout="tr_moveout(this)" ondblclick="change_select('select_id_<?php echo $item['id'];?>');return false;">
	<td class="td1"><input type="checkbox" id="select_id_<?php echo $item['id'];?>" onclick="select_tr(this)" /></td>
	<td class="td1" align="center"><input type="hidden" id="oldorder_<?php echo $item['id'];?>" value="<?php echo $item['myorder']?>" /><input class="input" type="text" size="3" id="order_<?php echo $item['id']?>" value="<?php echo $item['myorder']?>" onkeydown="iseditorder(event)" /></td>
	<td class="td1"><?php echo $item['id'];?></td>
	<td class="td2"><?php echo $item['modelname'];?></td>
	<td class="td2"><?php echo $database;?></td>
    <td class="td2"><?php echo $tablename;?></td>
	<td class="td2"><?php if ($item['isdefault']){echo '<font color="red">是</font>';}else{echo '否';}?></td>
	<td class="td2"><?php if ($item['isuse']){echo '是';}else{echo '<font color="red">否</font>';}?></td>
	<td class="td2">
	<input onclick="var myvalue=prompt('您可以输入加密密码以保护您的模板。留空或取消则不设置密码！','')||'';goUrl('<?php echo 'model/output/'.$item['id'] ;?>/'+myvalue,'hiddenFrame')" type="button" value="导出模型" class="btn" />
	<input onclick="goUrl('<?php echo Myqee::url('model/copy/'.$item['id']) ;?>')" type="button" value="复制模型" class="btn" />
	<input onclick="goUrl('<?php echo Myqee::url('model/edit/'.$item['id']) ;?>')" type="button" value="修改" class="btns" />
	<input onclick="ask_del(<?php echo $item['id'];?>,'model/del','<?php echo $makesuredelete;?>');" type="button" value="删除" class="btns" />
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
	<td class="td1"><input type="button" class="btn" value="修改排序" onclick="editorder();" /><input onclick="var myvalue=prompt('您可以输入加密密码以保护您的模型。留空或取消则不设置密码！','')||'';submitbox('select_id','model/output/[id]/'+myvalue,'hiddenFrame')" type="button" value="导出选定模型" class="btnl" /><input onclick="goUrl('<?php echo Myqee::url('model/inputmodel')?>')" type="button" value="导入模型" class="btn" /><input type="button" class="btnl" value="更新模型配置" onclick="confirm('是否立即执行？\n\n将会将数据库中所有数据更新到配置文件里。',400,null,null,function(t){if(t=='ok')goUrl('<?php echo Myqee::url('model/renewfiles')?>','hiddenFrame')});" />
</td>
	<td class="td1" width="320" align="right"><input type="button" class="btnl" value="数据表管理" onclick="goUrl('<?php echo Myqee::url('model/dblist')?>')" /><input type="button" class="bbtn" value="新增模型" onclick="goUrl('<?php echo Myqee::url('model/add')?>')" /></td>
</tr>
</table>

<center><?php echo $page; ?></center>


<?php View::factory('admin/footer') -> render(TRUE);?>