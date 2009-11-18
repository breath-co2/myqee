<?php
$page_title = '上传文件';
$page_index = 'info';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<script type="text/javascript" src="<?php echo ADMIN_IMGPATH;?>/admin/swfupload/swfupload.js"></script>
<script type="text/javascript" src="<?php echo ADMIN_IMGPATH;?>/admin/swfupload/uploadfun.js"></script>


<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('uploadfile/index');?>">上传管理</a> -&gt; 上传文件
</div>


<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" id="upload_list">
	<tr>
		<th class="td1" width="30">选择</th>
		<th class="td1">文件名</th>
		<th class="td1" width="80">大小</th>
		<th class="td1" width="80">类型</th>
		<th class="td1" width="60">上传情况</th>
		<th class="td1" width="180">操作</th>
	</tr>
</table>
<div id="upfileDiv">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-top:none">
<tr>
<td class="td2" align="center" height="50">还没有添加任何文件！</td>
</tr>
</table>
</div>
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-top:none">
	<tr>
		<td class="td1" width="30" align="center"><input type="checkbox" title="选择全部" onclick="selectbox(this,'select_id')" /></td>
		<td class="td1">
		<input type="button" class="btnl" value="上传选定文件" onclick="upselectfile()" /> 
		<input type="button" class="btnl" value="删除选定文件" onclick="upselectfile('delselect')" /> 
		</td>
		<td class="td1" width="80" align="center"><input type="button" class="btn" value="上传管理" onclick="goUrl('<?php echo Myqee::url('uploadfile/index');?>');" /></td>
		<td class="td1" width="150" align="center"><input type="button" class="btnl" value="删除已上传文件" onclick="deluploaded()" /></td>
		<td class="td1" width="180" align="center"><span style="float:left;height:27px;width:80px;padding-left:6px"><span id="spanButtonPlaceholder"></span></span> 
		<input type="button" class="bbtn" value="全部上传" onclick="swfu.continueUpload = true;swfu.startUpload();" /></td>
	</tr>
</table>
<br/>
<?php
$config_upload = Myqee::config('core.upload');
$maxsize = $config_upload['maxsize']>1024?($config_upload['maxsize']/1024).' MB':$config_upload['maxsize'].' KB';
$allowtype = empty($config_upload['extension'])?'*.*':'*.'.str_replace(',',';*.',$config_upload['extension']);
?>
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
	<tr>
		<th class="td1" colspan="2">上传参数设置</th>
	</tr>
	<tr>
		<td class="td1" width="200" align="right">允许上传的单文件大小</td>
		<td class="td2"><?php echo $maxsize;?></td>
	</tr>
	<tr>
		<td class="td1" align="right">允许上传的文件类型</td>
		<td class="td2"><?php echo $allowtype;?></td>
	</tr>
	<tr>
		<td class="td1" align="right">上传至目录</td>
		<td class="td2"><?php echo UPLOADPATH;?></td>
	</tr>
	<tr>
		<td class="td1" align="right">上传的图片是否添加水印</td>
		<td class="td2"><?php echo $config_upload['autowatermark']?'是':'否';?></td>
	</tr>
	<tr>
		<td class="td1" align="right">上传的图片是否同时生成缩略图</td>
		<td class="td2"><?php echo $config_upload['autothumb']?'是':'否';?></td>
	</tr>
	<tr>
		<td class="td1" align="right">&nbsp;</td>
		<td class="td1"><input type="button" value="修改上传参数" class="btnl" onclick="goUrl('<?php echo Myqee::url('index/config');?>#tag4')" /></td>
	</tr>
</table>

<script type="text/javascript">
var swfu;
(function() {
var settings = {
	flash_url : "<?php echo ADMIN_IMGPATH;?>/admin/swfupload/swfupload.swf",
	upload_url: "<?php echo Myqee::url( 'uploadfile/upfile');?>",	// Relative to the SWF file
	post_params: {"sid" : "<?php echo $checekinfo['sid'];?>","time":"<?php echo $checekinfo['time'];?>","adminid":"<?php echo $checekinfo['adminid'];?>","code":"<?php echo $checekinfo['code'];?>"},
	file_size_limit : '<?php echo $maxsize;?>',
	file_types : '<?php echo $allowtype;?>',
	file_types_description : '所有支持格式(最大<?php echo $maxsize;?>)',
	file_upload_limit : 100,
	file_queue_limit : 0,
	custom_settings : {
		progressTarget : "fsUploadProgress",
		cancelButtonId : "btnCancel"
	},
	debug: false,
	isautowrite : true,		//用于防止maxthon中出现后退的BUG

	upload_complete_handler : uploadComplete,
	upload_success_handler : uploadSuccess,
	upload_progress_handler : uploadProgress,
	upload_error_handler : uploadError,
	file_queued_handler : fileQueued,

	button_image_url : "<?php echo ADMIN_IMGPATH;?>/admin/upload_bbtn.png",	// Relative to the SWF file
	button_placeholder_id : "spanButtonPlaceholder",
	button_width: 80,
	button_height: 27
};

swfu = new SWFUpload(settings);
   })();
</script>
<?php View::factory('admin/footer') -> render(TRUE);?>
