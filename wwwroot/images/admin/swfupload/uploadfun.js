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
	oCell.innerHTML = file.size/1024>=1?(file.size/1048576>=1?(file.size/1073741824>=1?(file.size/1073741824).toFixed(2)+'GB':(file.size/1048576).toFixed(2)+'MB'):(file.size/1024).toFixed(2)+'KB'):file.size+'Bytes';
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
	oCell.innerHTML = '<input type="button" class="btn" onclick="upfile(\''+file.id+'\')" value="立即上传" /> <input type="button" class="btns" value="删除" onclick="delfile(\''+file.id+'\',this.parentNode.parentNode);" />';
	i++;
	
	if (listtable.rows.length > 1) {
		$('upfileDiv').style.display = 'none';
		myqee(newRow);
	}else{
		$('upfileDiv').style.display = 'block';
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
		if (!confirm('确认删除此列队？')){
			return;
		}
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
	swfu.selectFiles();
}