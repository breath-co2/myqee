<?php
View::factory('admin/header_frame') -> render(TRUE);
?>
<style type="text/css">
html{overflow:hidden;}
body{padding:10px 0 0 0;min-width:100px;}
.tableborder{width:96%;}
.mainTable{width:96:%;}
</style>

<script type="text/javascript" src="<?php echo ADMIN_IMGPATH;?>/admin/swfupload/swfupload.js"></script>
<script type="text/javascript">
if (self.location.href == parent.location.href){
	self.location.href = '<?php echo Myqee::url('uploadfile/up');?>';
}
document.oncontextmenu = function(){return false;};

function extkey(e){
	if (document.all)e = window.event;
	//ESC键
	if (e.keyCode == 27) {
		parent.closeMsgBox();
		return false;
	}
};
if (ie){
	document.body.onkeydown = extkey;
}else{
	window.onkeydown = extkey;
}

//if (ie){
//	document.body.onkeydown = parent.document.body.onkeydown;
//}else{
//	window.onkeydown = parent.window.onkeydown;
//}
function uploadComplete(file){
	if (this.continueUpload) {
		var stats = this.getStats();
		if (stats.files_queued > 0) {
			this.startUpload();
		}else{
			this.continueUpload = false;
		}
	}else if (this.nextupload){
		thefile = this.nextupload[0];
		if (this.nextupload.length<=1){
			this.nextupload = null;
		}else{
			this.nextupload = this.nextupload.slice(1);
		}
		if (thefile)upfile(thefile,this.nextupload)
	}
}

function uploadSuccess(file, serverData) {
		var tdobj = $('upfile_info_'+file.id);
		if (tdobj)tdobj.innerHTML = '完毕';
		
		var jsondata = JSON.parse(serverData);
		if (typeof swfu.getSetting('return_fileurl') == 'function')swfu.getSetting('return_fileurl')(jsondata);
}

function uploadProgress(file, bytesLoaded, totalBytes) {
	try {
		var percent = Math.ceil((bytesLoaded / file.size) * 100);
		if (percent < 10) {
			percent = "  " + percent;
		} else if (percent < 100) {
			percent = " " + percent;
		}

//		FeaturesDemo.selQueue.value = file.id;
		var queueString = percent + "%";
		var infoobj = $('upfile_info_'+file.id);
		if (infoobj)infoobj.innerHTML = queueString;
		var inputobj = $('_upload_file_input');
		if (inputobj)
		{
			var position = (-253+(11*Math.round(percent*2.53/11)));
			inputobj.style.backgroundPosition = position +'px 0';
			inputobj.value = '已上传:'+queueString;
		}
//		FeaturesDemo.selQueue.options[FeaturesDemo.selQueue.selectedIndex].text = queueString;


//		FeaturesDemo.selEventsFile.options[FeaturesDemo.selEventsFile.options.length] = new Option("Upload Progress: " + bytesLoaded, "");
	} catch (ex) {
		this.debug(ex);
	}
}

function uploadError(file, errorCode, message) {
	try {
		var errorName = "";
		switch (errorCode) {
		case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
			//alert(file.id + ":HTTP:" + file.name);
			errorName = "网络错误";
			break;
		case SWFUpload.UPLOAD_ERROR.MISSING_UPLOAD_URL:
			errorName = "参数错误";
			break;
		case SWFUpload.UPLOAD_ERROR.IO_ERROR:
			//alert(file.id + ":IO  :" + file.name);
			errorName = "上传失败";
			break;
		case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
			//alert(file.id + ":SEC :" + file.name);
			errorName = "SECURITY ERROR";
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
			errorName = "UPLOAD LIMIT EXCEEDED";
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
			errorName = "上传失败";
			break;
		case SWFUpload.UPLOAD_ERROR.SPECIFIED_FILE_ID_NOT_FOUND:
			errorName = "文件已上传或未发现";
			break;
		case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
			errorName = "FILE VALIDATION FAILED";
			break;
		case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
			errorName = "取消上传";
			return;
			//alert(file.id + ":----:" + file.name);
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
			errorName = "停止上传";
			//alert(file.id + ":  0%:" + file.name);
			break;
		default:
			errorName = "未知错误";
			break;
		}
		
		var errorString = errorName + "\n文件：" + (typeof(file) === "object" && file !== null ? file.name : '未发现 ') + "   " + message;
		var tdobj = $('upfile_info_'+file.id);
		if (tdobj)tdobj.innerHTML = '<span title="'+errorString.replace(/"/g,'&quot;')+'" style="color:red">上传失败</font>';

	} catch (ex) {
		this.debug(ex);
	}
}
</script>


<script type="text/javascript">
function fileQueued(file) {
	var listtable = $('upload_list');
	var rows_num = listtable.rows.length;
	var newRow = listtable.insertRow(rows_num);
	newRow.id = 'tr_'+file.id;
	newRow.align='center';
	i=0;
	
	var oCell = newRow.insertCell(i);
	oCell.className='td1';
	oCell.innerHTML = '<input type="checkbox" id="select_id_'+file.id+'" />';
	i++;
	
	var oCell = newRow.insertCell(i);
	oCell.className='td2';
	oCell.innerHTML = file.name.replace(/</g,'&lt;').replace(/>/g,'&gt;');
	i++;
	
	var oCell = newRow.insertCell(i);
	oCell.className='td2';
	var thesize = file.size/1024>=1?(file.size/1048576>=1?(file.size/1073741824>=1?(file.size/1073741824).toFixed(2)+'GB':(file.size/1048576).toFixed(2)+'MB'):(file.size/1024).toFixed(2)+'KB'):file.size+'Bytes';
	oCell.innerHTML = thesize;
	i++;
	
	var oCell = newRow.insertCell(i);
	oCell.className='td2';
	oCell.innerHTML = file.type;
	i++;
	
	var oCell = newRow.insertCell(i);
	oCell.id = 'upfile_info_' + file.id;
	oCell.className='td2';
	oCell.innerHTML = '排队中...';
	i++;
	
	var oCell = newRow.insertCell(i);
	oCell.className='td2';
	oCell.innerHTML = '<input type="button" class="btns" onclick="upfile(\''+file.id+'\')" value="上传" /><input type="button" class="btns" value="删除" onclick="delfile(\''+file.id+'\',this.parentNode.parentNode);" />';
	i++;
	
	if (listtable.rows.length > 1) {
		$('upfileDiv').style.display = 'none';
		myqee(newRow);
	}else{
		$('upfileDiv').style.display = 'block';
	}
	
	var inputobj = $('_upload_file_input');
	if (inputobj)inputobj.value = file.name+'('+thesize+')';
	
	//swfu.getSetting('');
	/*
	var aaa=''
	for (var i in swfu.getStats()){
		aaa += i+'='+swfu.getStats()[i]+"\n";
	}
	alert(aaa)
	*/
	if (swfu.getSetting('file_upload_limit_1') < swfu.getStats()['files_queued']){
		//删除一个
		var needdel = listtable.rows[1];
		if (needdel){
			delfile('',needdel,'yes');
		}
	}
}
function upfile(fileid,nextupload){
	if (nextupload)swfu.nextupload = nextupload;
	swfu.startUpload(fileid);
}
function upselectfile(thetype){
	leftper = 'select_id';
	var allInput = document.body.getElementsByTagName('input');
	var tmpid = [];
	for (var i in allInput )
	{
		if (allInput[i].type=='checkbox' && allInput[i].id.substr(0,leftper.length+1)==leftper+'_')
		{
			var fileid = allInput[i].id.substr(leftper.length+1);
			if (allInput[i].checked)
			{
				var str = innerText('upfile_info_'+fileid);
				if (thetype=='delselect'){
					tmpid.push(fileid);
				}else if (str!='完毕' && str!='上传失败'){
					tmpid.push(fileid);
				}
			}
		}
	}
	
	if (thetype=='delselect'){
		if (tmpid.length==0){
			alert("未选择任何文件！");
			return false;
		}
		for (i=0;i<tmpid.length;i++){
			delfile(tmpid[i],$('tr_'+tmpid[i]),'yes');
		}
	}else{
		if (tmpid.length==0){
			alert("未发现任何待上传文件！");
			return false;
		}
		upfile(tmpid[0],tmpid.slice(1));
	}
}

function deluploaded(){
	var listtable = $('upload_list');
	var rows_num = listtable.rows.length;
	var deltr = [];
	for (i=1;i<rows_num;i++){
		var tr = listtable.rows[i];
		var str = innerText(tr.cells[4]);
		var fileid = tr.id.substr(3);
		if (str=='完毕' || str=='上传失败'){
			deltr.push({'tr':tr,'id':fileid});
		}
	}
	for (j=0;j<deltr.length;j++){
		delfile(deltr[j]['id'],deltr[j]['tr'],'yes');
	}
}
function delfile(fileid,tr,isautodel){
	var str = innerText(tr.cells[4]);
	if (isautodel!='yes' && str!='完毕' && str!='上传失败'){
		
		confirm('确认删除此列队？',null,null,null,function(t){
			if (t=='ok'){
				if (tr){
					tr.parentNode.removeChild(tr);
				}
				swfu.cancelUpload(fileid);
				
				var listtable = $('upload_list');
				if (listtable.rows.length > 1) {
					$('upfileDiv').style.display = 'none';
				}else{
					$('upfileDiv').style.display = 'block';
				}
			}
		});
		return;
	}
	if (tr){
		tr.parentNode.removeChild(tr);
	}
	swfu.cancelUpload(fileid);
	
	var listtable = $('upload_list');
	if (listtable.rows.length > 1) {
		$('upfileDiv').style.display = 'none';
	}else{
		$('upfileDiv').style.display = 'block';
	}
	
}

function addfile(){
	if (swfu.getSetting('file_upload_limit_1')==1){
		swfu.selectFile();
	}else{
		swfu.selectFiles();
	}
}

</script>


<?php
$maxsize = $config_upload['maxsize']>1024?($config_upload['maxsize']/1024).' MB':$config_upload['maxsize'].' KB';

if (isset($allow_type) && is_array($allow_type)){
	$tmpext = explode(',',$config_upload['extension']);
	$tmpext = array_intersect($tmpext,$allow_type);			//计算交集
	$config_upload['extension'] = join(',',$tmpext);
}
$allowtype = empty($config_upload['extension'])?'*.*':'*.'.str_replace(',',';*.',$config_upload['extension']);
?>
<div style="width:96%;margin:auto;">
<ul class="ul tag">
<?php
if ($limit_file==1){
	echo '<li class="now" id="mytag_1" onclick="tag(this.id,\'mytag\',\'mytagmain\');">单文件上传</li>';
}else{
	echo '<li class="now" id="mytag_2" onclick="tag(this.id,\'mytag\',\'mytagmain\');">多文件上传</li>';
}
?>
<!-- <li id="mytag_3" onclick="tag(this.id,'mytag','mytagmain');">浏览服务器</li>
 -->
</ul>
</div>
<div style="clear:both"></div>
<div id="mytagmain_1">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
	<tr><th class="td1" colspan="2">上传文件</th></tr>
	<tr>
		<td class="td1" align="right" width="120">浏览文件：</td>
		<td class="td2">
		<span style="float:left"><input type="text" size="30" id="_upload_file_input" readonly="readonly" class="input" style="width:240px;height:13px;background-position:-253px 0;background-repeat:repeat-y;background-image:url(<?php echo ADMIN_IMGPATH;?>/admin/loading3.gif);" /></span>
		<span style="float:left;padding-left:5px;"><span id="spanButtonPlaceholder"></span></span>
		</td>
	</tr>
	<tr>
		<td class="td1" align="right">&nbsp;</td>
		<td class="td1"><input type="button" class="bbtn" value="立即上传" onclick="swfu.startUpload();" /></td>
	</tr>
	<tr>
		<th class="td1" colspan="2">上传参数设置</th>
	</tr>
	<tr>
		<td class="td1" align="right">允许上传的单文件大小</td>
		<td class="td2"><?php echo $maxsize;?></td>
	</tr>
	<tr>
		<td class="td1" align="right">允许上传的文件类型</td>
		<td class="td2"><?php echo $allowtype;?></td>
	</tr>
	<tr>
		<td class="td1" align="right">上传至目录</td>
		<td class="td2"><?php echo $config_upload['filepath'];?></td>
	</tr>
	<tr>
		<td class="td1" align="right">图片是否添加水印</td>
		<td class="td2"><?php echo $config_upload['autowatermark']?'是':'否';?></td>
	</tr>
	<tr>
		<td class="td1" align="right">图片自动生成缩略图</td>
		<td class="td2"><?php echo $config_upload['autothumb']?'是':'否';?></td>
	</tr>
</table>

</div>

<div id="mytagmain_3" style="display:none;">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
<tr><th class="td1">浏览服务器</th></tr>
<tr>
<td class="td2" valign="top"><div style="padding:50px 0;text-align:center">请稍等...</div></td>
</tr>
</table>
</div>

<div id="mytagmain_2" style="display:none;">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" id="upload_list">
	<tr>
		<th class="td1" width="30">选择</th>
		<th class="td1">文件名</th>
		<th class="td1" width="60">大小</th>
		<th class="td1" width="40">类型</th>
		<th class="td1" width="50">状态</th>
		<th class="td1" width="110">操作</th>
	</tr>
</table>
<div id="upfileDiv">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-top:none">
<tr>
<td class="td2" align="center" height="50" style="cursor:pointer;font-weight:bold;" onclick="addfile();"><font title="一次可同时添加多个">点击这里添加上传文件</font></td>
</tr>
</table>
</div>
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-top:none">
	<tr>
		<td class="td1" width="30" align="center"><input type="checkbox" title="选择全部" onclick="selectbox(this,'select_id')" /></td>
		<td class="td1"><input type="button" class="btn" value="上传选定" onclick="upselectfile()" /><input type="button" class="btn" value="删除选定" onclick="upselectfile('delselect')" /></td>
		<td class="td1" align="center"><input type="button" class="btnl" value="删除已上传文件" onclick="deluploaded()" /><input type="button" class="btn" value="添加文件" title="一次可同时添加多个" onclick="addfile();" /><input type="button" class="bbtn" value="全部上传" onclick="swfu.continueUpload = true;swfu.startUpload();" /></td>
	</tr>
</table>
</div>
<br/>
<script type="text/javascript">
var swfu;
(function() {
	var inputid = unescape(document.location.hash.substr(1));

	var settings = {
		flash_url : "<?php echo ADMIN_IMGPATH;?>/admin/swfupload/swfupload.swf",
		upload_url: "<?php echo Myqee::url( 'uploadfile/upfile');?>",	// Relative to the SWF file
		post_params: {"sid" : "<?php echo $checekinfo['sid'];?>","time":"<?php echo $checekinfo['time'];?>","adminid":"<?php echo $checekinfo['adminid'];?>","code":"<?php echo $checekinfo['code'];?>","config":"<?php echo $config;?>"},
		file_size_limit : '<?php echo $maxsize;?>',
		file_types : '<?php echo $allowtype;?>',
		file_types_description : '所有支持格式(最大<?php echo $maxsize;?>)',
		file_upload_limit_1 : <?php echo (int)$limit_file;?>,
		file_queue_limit : 0,
		custom_settings : {
			progressTarget : 'fsUploadProgress',
			cancelButtonId : 'btnCancel'
		},
		debug: false,
		upload_complete_handler : uploadComplete,
		upload_success_handler : uploadSuccess,
		upload_progress_handler : uploadProgress,
		upload_error_handler : uploadError,
		file_queued_handler : fileQueued,
		
		//isautowrite : true,		//用于防止maxthon中出现后退的BUG
		
		return_fileurl : function (info){
			var obj=parent.frameFrame.$(inputid);
			if (obj){
				obj.value = info['url']||'';
			}else{
				parent.copytext(info['url']||'','文件路径已复制在剪贴板，请手动粘贴到对应栏里！');
			}
			parent.closewin();
		},
		
		button_image_url : "<?php echo ADMIN_IMGPATH;?>/admin/upload_btn.png",	// Relative to the SWF file
		button_placeholder_id : "spanButtonPlaceholder",
		button_width: 75,
		button_height: 22
	};

	swfu = new SWFUpload(settings);
 })();
</script>

<script type="text/javascript">myqee();</script>

</body>

</html>