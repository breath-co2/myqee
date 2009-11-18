<?php
$page_title = '插件管理';
$page_index = 'plugins';
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
	overflow-x:hidden;
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

.leftlink li,.leftlink dd{
	display:block;
}

.leftlink font{
	padding:4px 0;
	display:block;
	border-bottom:1px dashed #ccc;
}
font.mtag{
	padding:1px 0;
	border-bottom:1px solid #ccc;
	display:block;
}
.mlink{
	display:block;
	padding:4px;
}
a.mlink:link,a.mlink:visited{
	color:#2C4150;
	text-decoration:none;
	background:#e8f3f8;
}
a.mlink:hover,a.mlink:active {
	color:green;
	text-decoration:none;
	background:#E0F0F5;
}
a.mlink:link span,a.mlink:visited span{
	display:none;
}
a.mlink:hover span,a.mlink:active span{
	display:block;
}

.leftlink li.now a.mlink{
	background:#CCE8F4;
	font-weight:bold;
}
.leftlink ol{
	padding:0;
	margin:0;
	background:url(<?php echo ADMIN_IMGPATH;?>/admin/tree_3.gif) repeat-y 0 0;
}

.leftlink ol li{
	background:url(<?php echo ADMIN_IMGPATH;?>/admin/tree_5.gif) no-repeat left top;
}

.leftlink ol li a:link,.leftlink ol li a.mlink:visited{
	position:block;
	background:#fff;
}
.leftlink ol li a:hover,.leftlink ol li a.mlink:active{
	position:absolute;
	background:#fff;
}

.leftlink ol li div{padding-left:20px;line-height:24px;white-space:nowrap;}

.leftlink ol li div.li_end{
	background:#fff url(<?php echo ADMIN_IMGPATH;?>/admin/tree_4.gif) no-repeat left top;
}

</style>
<script type="text/javascript">
function classtag(obj,classid){
	var objdiv = $('class_div_'+classid);
	if (!objdiv)return;
	if (obj.src.indexOf('tree_del.gif')>0){
		objdiv.style.display = 'none';
		obj.src = '<?php echo ADMIN_IMGPATH;?>/admin/tree_add.gif';
	obj.title = '展开子栏目';
}else{
	objdiv.style.display = 'block';
	obj.src = '<?php echo ADMIN_IMGPATH;?>/admin/tree_del.gif';
		obj.title = '收起子栏目';
	}
}

function changemenu(m_index){
	tag('mtag_'+m_index,'mtag','plugins_menu','mytagmain_1');
	var obj = $('plugins_menu_'+m_index);
	if (innerText(obj)=='Loading...'){
		get_mymenu(m_index);
	}
	createCookie('pluginsopen',m_index,100,'<?php
	$u=Myqee::url('plugins/',null,true);
	$u=explode('/',$u);
	$u=array_splice($u,3);
	echo '/',implode('/',$u);
	?>');
}


function get_mymenu(m_index){
	if (!m_index)return false;
	ajax.requestFile = ("<?php echo Myqee::url('plugins/ajax_getmenu/{{mindex}}');?>").replace(/\{\{mindex\}\}/,m_index);
	ajax.method = 'GET';
	ajax.onCompletion = function (){
		var arr;
		var iserror=false;
		try{
			arr = eval('('+this.response+')');
			if (typeof(arr)!='object'){
				iserror = true;
			}
		}catch(e){
			iserror = true;
		};
		if (iserror==true){
			alert('读取菜单失败,可能已超时，请重试！');
			var obj=$('plugins_menu_'+m_index);
			if (obj){
				obj.innerHTML ='<div style="padding:5px 0;"><input type="button" value="重试" class="btns" onclick="get_mymenu(\''+m_index+'\')" /></div>';
				myqee(obj);
			}
		}else{
			var obj=$('plugins_menu_'+m_index);
			var tmphtml = get_menuhtml(m_index,arr);
			if (obj){
				obj.innerHTML = tmphtml;
			}
		}
	}
	ajax.onError = function (){
		alert('读取菜单失败,可能已超时，请重试！');
		var obj=$('plugins_menu_'+m_index);
		if (obj){
			obj.innerHTML ='<div style="padding:5px 0;"><input type="button" value="重试" class="btns" onclick="get_mymenu(\''+m_index+'\')" /></div>';
			myqee(obj);
		}
	}
	ajax.runAJAX();

}


function get_menuhtml(p,arr){
	var tmphtml = '<ol>';
	var urlstr = "<?php echo Myqee::url('plugins/run/{{url}}/');?>";
	var len = 0;
	for (var j in arr){len++;}
	var n=0;
	for (var i in arr)
	{
		n++;
		tmphtml+='<li><div'+(n==len?' class="li_end"':'')+'><img style="background:#fff;" src="<?php echo ADMIN_IMGPATH;?>/admin/'+(arr[i]['icon']?'icon/'+arr[i]['icon']:'plugins.png')+'" width="16" height="16" align="absmiddle" />&nbsp;<a href="'+urlstr.replace(/\{\{url\}\}/g,p+'/'+arr[i]['href'])+'" title="'+(arr[i]['title']||'')+'" target="'+(arr[i]['target']||'mainFrame')+'">'+arr[i]['name']+'</a>';
		if (arr[i]['submenu'])
		{
			if (typeof (arr[i]['submenu']) == 'object')
			{
				tmphtml += get_menuhtml(p,arr[i]['submenu']);
			}
		}
		tmphtml +='</div></li>';
	}
	tmphtml +='</ol>';
	return tmphtml;
}

var _edit_config=false;
</script>
<br/>
<table border="0" align="center" cellpadding="0" cellspacing="0" style="border:none;background:none;" class="tableborder">
	<tr><td width="220" valign="top" id="leftTD" align="left">
			<img id="lefttdImg" src="<?php echo ADMIN_IMGPATH;?>/admin/spacer.gif" style="height:0px;width:220px" /><br/>
<div id="leftmainDIV">
	<div style="background:#F1F3F5;width:20px;height:100%;position:absolute;left:-20px;top:-10px;padding:10px 0;"></div>
	<img style="margin-top:120px;" title="收缩、展开左侧栏目结构" onclick="changeFrame()" id="rightbarImg" src="<?php echo ADMIN_IMGPATH;?>/admin/rightbar_1.gif" width="10" />
<div id="leftDIV">
	<table border="0" cellpadding="1" cellspacing="1" class="tableborder" style="width:100%;text-align:left;">
		<tr><th class="td1">操作菜单</th></tr>
		<tr><td class="td2">
				<div style="width:213px;overflow:auto;">
					<div class="link2" id="classLinkDiv">
						<div id="mytagmain_1">
							<ul class="ul leftlink">
								<?php
if (is_array($plugins)){
	foreach($plugins as $k=>$v){
		echo '<li id="mtag_'.$k.'"><font class="mtag"><a href="#" class="mlink" onclick="if(!_edit_config)changemenu(\''.$k.'\');this.blur();return false;"><img src="'.ADMIN_IMGPATH.'/admin/plugins.png" align="absmiddle" width="16" height="16" /><span style="float:right;padding-top:1px;" onclick="_edit_config=true;goUrl(\''.Myqee::url('plugins/config/'.$k).'\',\'mainFrame\');setTimeout(\'_edit_config=false\',300);return false;"><img src="'.ADMIN_IMGPATH.'/admin/config.gif" width="16" height="16" title="修改插件配置" /></span> '.$v.'</a></font>
		<span id="plugins_menu_'.$k.'" style="display:none;"><div style="padding:4px;color:#999">Loading...</div></span>
		</li>';
	}
}
?>

								</ul>
							</div>

							<div class="clear"></div>
						</div>
					</div>
				</td></tr>
		</table>
	</div>

</div>
<script type="text/javascript">
(function(){
	var popen = readCookie('pluginsopen');
	if(popen){
		changemenu(popen);
	}
})()
var urlHistory = [];
function mainUrlset(){
	var nowurl = window.frames['mainFrame'].location.href;
	urlHistory[urlHistory.length] = nowurl;
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

var onresizefun;
</script>
</td>

<td width="10" style="cursor:pointer" onclick="changeFrame()" onmouseover="this.style.background='#fff';" onmouseout="this.style.background='none';" valign="top" title="收缩、展开左侧栏目结构"><img src="<?php echo ADMIN_IMGPATH;?>/admin/spacer.gif" alt="" style="height:0px;width:10px" /></td>
<td valign="top">
	<div class="loaction" id="loaction_div" style="width:auto;margin:auto;padding-top:0;">
	您的位置：<a href="<?php echo Myqee::url('index');?>" target="_top">管理首页</a> -&gt;
<a href="<?php echo Myqee::url('plugins/index');?>">插件管理</a> -&gt;
	<span id="location">管理首页</span>
</div>

<iframe id="mainFrame" name="mainFrame" width="100%" height="500" allowTransparency="true" scrolling="no" frameborder="0" src="<?php echo Myqee::url('plugins/main');?>" onload="mainUrlset();" unload="$('infoForWidth').style.width='auto';"></iframe><br/>

<img id="infoForWidth" src="<?php echo ADMIN_IMGPATH;?>/admin/spacer.gif" style="height:0px;" />

		</td>
	</tr>
</table>
<script type="text/javascript">
function changeFrame(){
	$('infoForWidth').style.width='0px';

	var obj = $('rightbarImg');
	var lefttd = $('leftTD');
	var leftdiv = $('leftDIV');

	$('infoForWidth').style.width='0px';

	if (leftdiv.style.display == 'none'){
		$('lefttdImg').style.display='';
		leftdiv.style.display = 'block';
		lefttd.style.width = '220px';
		obj.src = '<?php echo ADMIN_IMGPATH;?>/admin/rightbar_1.gif';
		obj.style.left='221px';
	}else{
		$('lefttdImg').style.display='none';
		leftdiv.style.display = 'none';
		lefttd.style.width = '1px';
		obj.src = '<?php echo ADMIN_IMGPATH;?>/admin/rightbar_2.gif';
		obj.style.left='2px';
	}
}

function changeHeight(){
	$('infoForWidth').style.width='0px';
	var h = document.documentElement.clientHeight-115;
	if (!(h>100))h=100;
	$("classLinkDiv").style.height=h+"px";
	if (typeof (onresizefun) == 'function'){
		onresizefun();
	}
}
setTimeout('changeHeight()',100);
window.onresize = function (){changeHeight();};
</script>


<?php View::factory('admin/footer') -> render(TRUE);?>