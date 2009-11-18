<?php
$page_index = 'index';
$page_title = Myqee::lang('admin/index.cachemanage');
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; 
<a href="<?php echo Myqee::url('task/index');?>">缓存管理</a>
</div>
<script type="text/javascript">
<!--
function showinfo(type,info){
	var obj=$(type+'_show_div');
	if (obj){
		obj.innerHTML = info;
		setTimeout(function(){obj.innerHTML='';},6000);
	}
}

function renewall(){
	var arr = ['class','model','db','template','filelist'];
	for(var i = 0;i<arr.length;i++){
		$(arr[i]+'btn').onclick();
	}
}
//-->
</script>
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
	<tr>
		<th class="td1" colspan="2">网站缓存管理</th>
	</tr>
	<tr>
		<td width="20%" align="right" class="td1">更新所有栏目配置缓存：</td>
		<td class="td2"><input id="classbtn" type="button" class="btn" value="立即更新" onclick="goUrl('<?php echo Myqee::url('class/renew_config?type=auto');?>','classframe')" /> 
		<span id="class_show_div"></span>
		<iframe name="classframe" src="<?php echo $blockurl = ADMIN_IMGPATH.'/admin/block.html';?>" width="1" height="1" style="display:none;"></iframe>
		</td>
	</tr>
	<tr>
		<td align="right" class="td1">更新模型配置缓存：</td>
		<td class="td2"><input id="modelbtn" type="button" class="btn" value="立即更新" onclick="goUrl('<?php echo Myqee::url('model/renewfiles?type=auto');?>','modelframe')" /> 
		<span id="model_show_div"></span>
		<iframe name="modelframe" src="<?php echo $blockurl;?>" width="1" height="1" style="display:none;"></iframe>
		</td>
	</tr>
	<tr>
		<td align="right" class="td1">更新数据表配置缓存：</td>
		<td class="td2"><input id="dbbtn" type="button" class="btn" value="立即更新" onclick="goUrl('<?php echo Myqee::url('model/dbrenewfiles?type=auto');?>','dbframe')" />
		<span id="db_show_div"></span>
		<iframe name="dbframe" src="<?php echo $blockurl;?>" width="1" height="1" style="display:none;"></iframe>
		</td>
	</tr>
	<tr>
		<td align="right" class="td1">更新模板缓存：</td>
		<td class="td2"><input id="templatebtn" type="button" class="btn" value="立即更新" onclick="goUrl('<?php echo Myqee::url('template/renewfiles?type=auto');?>','templateframe')" />
		<span id="template_show_div"></span>
		<iframe name="templateframe" src="<?php echo $blockurl;?>" width="1" height="1" style="display:none;"></iframe>
		</td>
	</tr>
	<tr>
		<td align="right" class="td1">重建站点文件索引缓存：</td>
		<td class="td2"><input id="filelistbtn" type="button" class="btn" value="立即更新" onclick="goUrl('<?php echo Myqee::url('index/renewfilelist?type=auto');?>','filelistframe')" /> 
		<span class="helpicon" title="将已存在的类、语言包、API、models等 <b>文件列表</b> 缓存">&nbsp;</span> 
		<span id="filelist_show_div"></span>
		<iframe name="filelistframe" src="<?php echo $blockurl;?>" width="1" height="1" style="display:none;"></iframe>
		</td>
	</tr>
	<tr>
		<td align="right" class="td1">全部更新：</td>
		<td class="td2">
		<input type="button" class="bbtn" value="全部更新" onclick="renewall()" /> 
		一键更新上面全部
		</td>
	</tr>
</table>
<?php
View::factory('admin/footer') -> render(TRUE);
?>