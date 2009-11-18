<?php
$page_title = '栏目信息';
$page_index = 'info';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<style type="text/css">
.link2 a:link {
	color:#2C4150;
	text-decoration:none;
}
.link2 a:visited {
	color:#2C4150;
	text-decoration:none;
}
.link2 a:hover {
	color:#03f;
	font-weight:bold;
	text-decoration:none;
}
.link2 a:active {
	color:#03f;
	font-weight:bold;
	text-decoration:none;
}

#classLinkDiv{
	height:400px;
	overflow:auto;
}
#leftmainDIV{
	width:220px;
	position:fixed;
	top:78px;
	background:#F1F3F5;
	padding:0;
	_position:absolute;
	_top:expression((eval(document.compatMode &&document.compatMode=="CSS1Compat") ?documentElement.scrollTop:document.body.scrollTop)+78);
}
#rightbarImg{
	position:absolute;
	top:50px;
	left:221px;
	cursor:pointer;
}

#bottom{margin-left:236px;display:block;}

#control_div .tableborder{width:100%;}
#control_div .control_table{width:96%;}
</style>
<script type="text/javascript">
var cookiepath = '<?php
	$u=Myqee::url('info/',null,true);
$u=explode('/',$u);
$u=array_splice($u,3);
echo '/',implode('/',$u);
?>';

function classtag(obj,classid){
	var objdiv = $('class_div_'+classid);
	if (!objdiv)return;
	var openclass = readCookie('classopenset');
	if (!openclass||openclass==''){
		openclass='';
	}else{
		openclass=openclass.replace(eval('/,'+classid+',/g'),',');
	}
	if (obj.src.indexOf('tree_del.gif')>0){
		objdiv.style.display = 'none';
		obj.src = '<?php echo ADMIN_IMGPATH;?>/admin/tree_add.gif';
		obj.title = '展开子栏目';
	}else{
		objdiv.style.display = '';
		obj.src = '<?php echo ADMIN_IMGPATH;?>/admin/tree_del.gif';
		obj.title = '收起子栏目';
		if (openclass==''){
			openclass=',';
		}
		openclass+=classid+',';
	}
	var t=100;
	if (openclass==',')t=-99999;
	createCookie('classopenset',openclass,t,cookiepath);
}
</script>

<br/>
<table border="0" align="center" cellpadding="0" cellspacing="0" style="border:none;background:none;" class="tableborder">
<tr><td width="220" valign="top" id="leftTD" align="left">
<img id="lefttdImg" src="<?php echo ADMIN_IMGPATH;?>/admin/spacer.gif" style="height:0px;width:220px" /><br/>
<div id="leftmainDIV">
<div style="background:#F1F3F5;width:20px;height:100%;position:absolute;left:-20px;top:-10px;padding:10px 0;"></div>
<img style="margin-top:120px;" title="收缩、展开左侧栏目结构" onclick="changeFrame()" id="rightbarImg" src="<?php echo ADMIN_IMGPATH;?>/admin/rightbar_1.gif" width="10" />
<div id="leftDIV">
<ul class="ul tag">
<li class="now" id="mytag_1" onclick="tag(this.id,'mytag','mytagmain','leftDIV');createCookie('opentag',1,200,cookiepath);">我的栏目</li>
<li id="mytag_2" onclick="tag(this.id,'mytag','mytagmain','leftDIV');createCookie('opentag',2,200,cookiepath);">数 据 表</li>
<li id="mytag_3" onclick="tag(this.id,'mytag','mytagmain','leftDIV');createCookie('opentag',3,200,cookiepath);">表单信息</li>
</ul>
<div class="clear"></div>
<table border="0" cellpadding="3" cellspacing="1" class="tableborder" style="width:100%;text-align:left;">
<tr><td class="td2">
<div style="width:210px;overflow:auto;">
<div style="padding:4px" class="link2" id="classLinkDiv">
<div id="mytagmain_1">
<?php
global $openclass;
$openclass = explode(',',trim($_COOKIE['classopenset'],','));
function listclass($list,$spacer=''){
	global $openclass;
	if($list){
		$countlist = count($list);
		foreach ($list as $item){
			$classopen = in_array($item['classid'],$openclass);
			if ($item['islast'] ==0){
					if ($item['sonclassarray']&&!$classopen){
						$spacer1 = $spacer .'<img style="cursor:pointer" src="'.ADMIN_IMGPATH.'/admin/tree_add.gif" align="absmiddle" onclick="classtag(this,'.$item['classid'].')" title="展开子栏目" /> ';
					}
					else{
						$spacer1 = $spacer .'<img style="cursor:pointer" src="'.ADMIN_IMGPATH.'/admin/tree_del.gif" align="absmiddle" onclick="classtag(this,'.$item['classid'].')" title="收起子栏目" /> ';
					}
			}else{
				$spacer1 = $spacer .'　';
			}
		?>
		<div style="padding:2px;"><pre><?php echo $spacer1;?><img src="<?php echo ADMIN_IMGPATH;?>/admin/<?php echo $item['iscontent']?'ie.gif':'dir.gif" title="此栏目不能添加内容';?>" align="absmiddle" /><a href="<?php echo Myqee::url('info/myclass/1/' . $item['classid']);?>" target="mainFrame" id="classLink_<?php echo $item['classid'];?>"><?php echo $item['classname'];?></a></pre></div>
		<?php
			if ($item['sonclassarray']){
				$spacer .= '　 ';
				echo '<div id="class_div_'.$item['classid'].'"';
				if (!$classopen)echo ' style="display:none;"';
				echo '>';
				listclass($item['sonclassarray'],$spacer);
				$spacer = substr($spacer,0,-strlen('　 '));
				echo '</div>';
			}
		}
	}
}
listclass($list);
?>
</div>
<div id="mytagmain_2" style="display:none">
<?php 
if (is_array($list_db)){
	foreach ($list_db as $item){
		echo '<div style="padding:3px;"><pre><img src="'.ADMIN_IMGPATH.'/admin/icon_db.gif" align="absmiddle" />&nbsp;<a href="'.Myqee::url('info/main/1/'.$item['name']).'" target="mainFrame" id="dbLink_'.$item['name'].'">' . $item['dbname'] .'</a></pre></div>';
	}
}

?>
</div>
<div class="clear"></div>
</div>
</div>
</td></tr>
</table>
</div>

</div>
</td>
<script type="text/javascript">
(function (){
	var opentag = readCookie('opentag');
	if (opentag){
		var obj = $('mytag_'+opentag);
		if (obj){obj.onclick();}
	}
})();
var urlHistory = [];
function mainUrlset(){
	var nowurl = window.frames['mainFrame'].location.href;
	urlHistory[urlHistory.length] = nowurl;
	$('infoForWidth').style.width='0px';
}

function goHistory(go){
	if (!go)go=-1;
	if (!(go<0))return;
	var urlindex = (urlHistory.length + go-1);
	if (!(urlindex>0))urlindex = 0;
	var url = urlHistory[urlindex];
	if (!url)return;
	if (url!=window.frames['mainFrame'].location.href){
		var newhistory = [];
		for (var i=0;i<urlindex;i++){
			newhistory[i] = urlHistory[i];
		}
		urlHistory = newhistory;
		newhistory = null;
		window.frames['mainFrame'].location.href = url;
	}
}
</script>
<td width="10" style="cursor:pointer" onclick="changeFrame()" onmouseover="this.style.background='#fff';" onmouseout="this.style.background='none';" valign="top" title="收缩、展开左侧栏目结构"><img src="<?php echo ADMIN_IMGPATH;?>/admin/spacer.gif" style="height:0px;width:10px" /></td>
<td valign="top" id="mainFrameDiv"><iframe id="mainFrame" name="mainFrame" width="100%" height="500" allowTransparency="true" scrolling="no" frameborder="0" src="<?php echo Myqee::url('info/main');?>" onload="mainUrlset();"></iframe><br/><img id="infoForWidth" src="<?php echo ADMIN_IMGPATH;?>/admin/spacer.gif" style="height:0px;" />
</td>
</tr>
</table>
<script type="text/javascript">
function changeFrame(){
	var obj = $('rightbarImg');
	var lefttd = $('leftTD');
	var leftdiv = $('leftDIV');

	var controlleft = $('control_table_left');
	var controldiv = $('infoeditControl_Div');
	if (controldiv)controldiv.style.display='none';

	$('infoForWidth').style.width='0px';

	if (leftdiv.style.display == 'none'){
		$('lefttdImg').style.display='';
		leftdiv.style.display = 'block';
		lefttd.style.width = '220px';
		obj.src = '<?php echo ADMIN_IMGPATH;?>/admin/rightbar_1.gif';
		obj.style.left='221px';

		if (controlleft)controlleft.style.width='230px';
	}else{
		$('lefttdImg').style.display='none';
		leftdiv.style.display = 'none';
		lefttd.style.width = '1px';
		obj.src = '<?php echo ADMIN_IMGPATH;?>/admin/rightbar_2.gif';
		obj.style.left='2px';

		if (controlleft)controlleft.style.width='11px';
	}

	if (controldiv)controldiv.style.display='';
}

function changeHeight(){
	$('infoForWidth').style.width='0px';
	var h = document.documentElement.clientHeight-130;
	if (!(h>100))h=100;
	$("classLinkDiv").style.height=h+"px";
	set_control_fixed();
}
setTimeout('changeHeight()',100);
window.onresize = function (){
	changeHeight();
};


function set_control_fixed(){
	var obj = window['mainFrame'].document.getElementById('control_div');
	var obj2 = $('infoeditControl_Div');
	var obj_show = window['mainFrame'].document.getElementById('control_show_div');
	if (!obj || !obj_show){
		if(obj2)obj2.parentNode.removeChild(obj2);
		return;
	}
	if (!obj2)
	{
		obj2 = document.createElement("div");
		obj2.id='infoeditControl_Div';
		obj2.innerHTML = obj_show.innerHTML;
		document.body.appendChild(obj2);
		$('info_submit_btton').onclick = function(){window['mainFrame'].document.forms['myeditform'].submit()};
		$('info_goback_btton').onclick = function(){window['mainFrame'].$('info_goback_btton').onclick()};
		if ($('leftDIV').style.display != 'none')
		{
			$('control_table_left').style.width = '230px';
		}else{
			$('control_table_left').style.width = '0px';
		}
		$('control_div').className = 'control';
		myqee(obj2);
	}
	var h = dom.scrollHeight;			//页面总高度
	var sh = dom.scrollTop;				//被卷去的高度
	var ch = dom.clientHeight;			//可见区域高度
	if (h-sh-ch<100){
		obj2.style.display = 'none';
	}else{
		obj2.style.display = '';
	}
	$('control_div').style.left = - dom.scrollLeft +'px';
}
window.onscroll = set_control_fixed;
</script>


<?php View::factory('admin/footer') -> render(TRUE);?>