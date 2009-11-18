<?php
$page_index = 'info';
View::factory('admin/header_frame') -> render(TRUE);
?>
<style type="text/css">
html{overflow:auto;}
body{min-width:300px;}
ul.tag2{display:block;}
ul.tag2 li{float:left;white-space: nowrap;display:inline-block;line-height:1.8em;margin:0 6px;height:1.8em;overflow:hidden;cursor:pointer;text-decoration:underline;}
ul.tag2 li.now{font-weight:bold;text-decoration:none;cursor:default;}
</style>
<link rel="stylesheet" type="text/css" href="<?php echo ADMIN_IMGPATH;?>/admin/ymPrompt/skin/dmm-green/ymPrompt.css" />
<script type="text/javascript" src="<?php echo ADMIN_IMGPATH;?>/admin/ymPrompt/ymPrompt.js"></script>

<script type="text/javascript">
if (self.location.href == parent.location.href){
	document.location.href = '<?php echo Myqee::url('index');?>';
}

function _set_frame(){
	
}

function postdata(){
	window._set_frame = function(){
		try {
			if( typeof(frameFrame.win)=='function'){
				parentFrame = window.parent;
			}
		}catch(e){}
		parentFrame.frameFrame = window.self;
		window._set_frame = function(){};
	}
}

function changeHeight(){
	set_control_fixed(2)
	var obj = $('content___Frame');
	if (!obj)return;
	obj.style.height = (dom.clientHeight -38)+ 'px';
}

function show_upload_frame(id,url,type){
	var theTitle = {
		'upfile':'文件上传',
		'upimg':'图片上传',
		'upflash':'Flash上传'
	};
	theTitle[type] = theTitle[type] || '文件上传';
	var oldclosewin = window.closewin;
	window.closewin = function(type,autoclose){
		ymPrompt.close();
		window.closewin = oldclosewin;
	}
	ymPrompt.win({message:url+'#'+escape(id),width:600,height:320,title:theTitle[type],iframe:true,handler:function(){window.closewin = oldclosewin;}});
}

</script>
<form name="myeditform" id="myeditform" onsubmit="postdata()" action="<?php echo Myqee::url('block/view_edit_save');?>" method="POST" target="hiddenFrame">

<?php echo 
form::hidden('id',$block['id']),
form::hidden('autohtml',$autohtml),
form::hidden('infoid',$infoid)
;

if ($block['show_type']==1){
	?>
<style type="text/css">
	html{overflow:auto;overflow-y:scroll;}
</style>
<div style="padding:3px 1px">
<table border="0" cellpadding="3" cellspacing="1" width="96%" align="center" class="tableborder" id="myEditorTable">
<tr>
	<th class="td1" width="20">选</th>
	<th class="td1">标题、链接(均不能空)</th>
	<th class="td1" width="230">图片、目标、时间</th>
	<th class="td1">摘要</th>
	<th class="td1" width="45">操作</th>
</tr>
</table>
<script type="text/javascript">$import("<?php echo ADMIN_IMGPATH;?>/admin/calendar.js");</script>

<script type="text/javascript">
function get_value(str){
	if (typeof (str) == 'undefined')
	{
		return '';
	}
	str = str.toString().replace(/\"/g,"&quot;").replace(/</g,"&lt;").replace(/>/g,"&gt;");
	return str;
}
var tr_len = 0;
function get_tr_arr(data){
	data = data||{};
	var tr = [
		'<!--[if lte IE 6]><div style="position:absolute;height:24px;"><div style="position:absolute;top:-4px;width:95%;padding:0 12px 0 0;"><![endif]--><input name="content['+tr_len+'][title]" value="'+get_value(data['title'])+'" style="width:96%" type="text" size="30" maxlength="250" class="input" title="标题，不能空！" /><!--[if lte IE 6]></div></div>&nbsp;<![endif]--><div class="clear" style="height:3px;"></div><!--[if lte IE 6]><div style="position:absolute;height:24px;"><div style="position:absolute;top:-4px;width:95%;padding:0 12px 0 0;"><![endif]--><input name="content['+tr_len+'][URL]" value="'+get_value(data['URL']||data['url'])+'" style="width:96%" type="text" size="30" maxlength="250" class="input" title="链接地址，不能空！" /><!--[if lte IE 6]></div></div>&nbsp;<![endif]-->',
	
		'<span style="white-space:nowrap;"><input type="text" id="content['+tr_len+'][image]" name="content['+tr_len+'][image]" value="'+get_value(data['image'])+'" class="input"  style="width:200px;"style="width:300px;" />&nbsp;<img src="<?php echo ADMIN_IMGPATH;?>/admin/upimg.gif" align="absmiddle" style="cursor:pointer" onclick="if(this.getAttribute(\'disabled\')==\'disabled\')return;show_upload_frame(\'content['+tr_len+'][image]\',\'<?php echo Myqee::url('uploadfile/inframe/upimg/1');?>\',\'upimg\')" /></span><div class="clear" style="height:3px;"></div><input type="text" id="content['+tr_len+'][target]" name="content['+tr_len+'][target]" value="'+get_value(data['target'])+'" class="input" size="6" style="width:45px;padding-right:25px;" /><span style="position:absolute;margin:2px 0 0 -20px;width:18px;height:18px;_height:16px;overflow:hidden;"><span style="position:absolute;right:-2px;top:-2px;_right:-1px;_top:-1px;"><select onmouseover="this.value=$(\'content['+tr_len+'][target]\').value;this.style.width=$(\'content['+tr_len+'][target]\').clientWidth+\'px\';" onchange="$(\'content['+tr_len+'][target]\').value=this.value;" ><option value="">默认</option><option value="_blank">新窗口</option><option value="_self">本窗口</option><option value="_parent">父窗口</option><option value="_top">顶端窗口</option></select></span></span>&nbsp;<span style="white-space:nowrap;"><input type="text" id="content['+tr_len+'][time]" name="content['+tr_len+'][time]" value="'+get_value(data['time'])+'" onclick="showcalendar(event,this,true);" style="width:120px;" class="input" /></span>',

		'<!--[if lte IE 6]><div style="position:absolute;height:24px;"><div style="position:absolute;top:-4px;width:95%;padding:0 12px 0 0;"><![endif]--><textarea id="content['+tr_len+'][description]" name="content['+tr_len+'][description]"  style="width:95%;height:40px;" class="input">'+get_value(data['description'])+'</textarea><!--[if lte IE 6]></div></div>&nbsp;<![endif]-->',

		'<div style="text-align:center" onmousedown="var _mytr= this.parentNode.parentNode;if(_mytr.up){return;}else{_mytr.swapNodes=function(t1){window.swapNodes(t1,_mytr);};_mytr.move=function(type){var rowindex;if(type==\'down\'){rowindex=_mytr.rowIndex+1;if (rowindex>=_mytr.parentNode.rows.length)return;}else{rowindex=_mytr.rowIndex-1;if (rowindex<=0)return;}var obj = _mytr.parentNode.rows[rowindex];_mytr.swapNodes(obj);};}"><a href="#" onclick="this.parentNode.parentNode.parentNode.move(\'up\');this.ovmouseout();return false;"><img src="'+_admin_inmpath+'/admin/up.gif" width="11" height="11" alt="上移一行" /></a> <a href="#" onclick="this.parentNode.parentNode.parentNode.move(\'down\');this.ovmouseout();return false;"><img src="'+_admin_inmpath+'/admin/down.gif" width="11" height="11" alt="下移一行" /></a> <a href="#" onclick="if(_confirm(\'确实要删除这一组？\')){var tr=this.parentNode.parentNode.parentNode;tr.parentNode.removeChild(tr);window.onresize();}"><img src="'+_admin_inmpath+'/admin/delete.gif" width="11" height="11" alt="删除本行" /></a></div>'
	];
	tr_len++;
	return tr;
}


function showEditorTable(){
	var dataArray = <?php 
		if(($dataarr = unserialize($block['content'])) &&is_array($dataarr)){
			echo Tools::json_encode($dataarr);
			$len = count($dataarr);
		}else{
			echo '[]';
			$len = 0;
		}
	?>;
	var tableInfo= new Array();
	var i = 0;
	for (var item in dataArray)
	{
		tableInfo[i] = get_tr_arr(dataArray[i]);
		i++;
	}
	myTable['mytable']=new CreateTable('myEditorTable',tableInfo);
}

function addrows(){
	var maxlen = <?php echo (int)$block['len'];?>;
	if (maxlen>0)
	{
		var nowlen = $('myEditorTable').rows.length;
		if (nowlen > maxlen)
		{
			_alert('只允许'+maxlen+'条记录！');
			return false;
		}
		var showobj = $('showNowlen');
		if(showobj)showobj.innerHTML = nowlen;
	}
	myqee(myTable['mytable'].add(4,get_tr_arr()));
	window.onresize();
}

showEditorTable();
</script>
<div style="height:40px">
	<div id="control_div" style="width:100%;min-width:600px" class="control">
	<img src="<?php echo ADMIN_IMGPATH;?>/admin/spacer.gif" style="width:600px;height:0px" /><br/>
<table border="0" cellpadding="3" cellspacing="1" width="96%" align="center" class="tableborder" style="border-top:none;">
<tr>
	<td class="td1" align="right">
	<div style="float:left;">
		<input type="button" value="上移选定" onclick="if (!myTable['mytable'])return;myTable['mytable'].up(1);" class="btn" />
		<input type="button" value="下移选定" class="btn" onclick="if (!myTable['mytable'])return;myTable['mytable'].down();" />
		<input type="button" value="碎片设置" class="btn" onclick="goUrl('<?php echo Myqee::url('block/edit/'.$block['id']);?>','_blank')" />
	</div>
<?php
if ($block['len']>0){
	echo '最多 <font style="color:red;">'.$block['len'].'</font> 组，目前已有 <span id="showNowlen" style="color:red;">'.$len.'</span> 组';
}
?> 
	<input type="button" value="新增一组" onclick="addrows();" class="btn" /> 
	</td>
	<td class="td1" width="90" align="center">
	<input type="submit" value="保存碎片" class="bbtn" />
	</td>
</tr>
</table>
	</div>
	
</div>

</div>

<?php
}elseif ($block['show_type']==2){
?>
<div style="padding:2px 1px 0 1px;">
<?php 
echo form::edithtml($block['advfield'],unserialize($block['content']),'adv','content');?>
</div>
<?php
}else{
	echo form::htmlarea(array('name'=>'content','toolbar'=>'Block'),$block['content']);
?>
<script type="text/javascript">
		function set_control_fixed(){return false;}
</script>
<?php
}

if ($block['show_type']==0 || $block['show_type']==2){
?>
<div style="padding:0px 1px;text-align:center;">
<table border="0" cellpadding="3" cellspacing="1" width="96%" align="center" class="tableborder" style="border-top:none;">
<tr>
	<td class="td1" align="center">
	<input type="submit" class="bbtn" value="保存碎片" style="margin:0;"/>
	<input type="button" class="btn" value="关闭窗口" onclick="parent.closewin()" />
	<input type="button" value="碎片设置" class="btn" onclick="goUrl('<?php echo Myqee::url('block/edit/'.$block['id']);?>','_blank')" />
	</td>
</tr>
</table>
</div>
<?php 
}
?>

</form>

<script type="text/javascript">
myqee();
window.onresize= changeHeight;
set_control_fixed();
</script>

<script type="text/javascript" src="<?php echo ADMIN_IMGPATH;?>/admin/tooltip.js"></script>
</body>
</html>