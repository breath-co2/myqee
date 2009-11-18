<div class="loaction" id="loaction_div">
您的位置：<a href="<?php echo Myqee::url('index')?>" target="_top">管理首页</a> -&gt; 
<a href="<?php echo Myqee::url('plugins/index');?>">插件管理</a>  -&gt; 
CKFiner管理
</div>
<script type="text/javascript">
if (parent.location.href!=self.location.href && window.name=='mainFrame'){
	$('loaction_div').style.display='none';
	parent.$('loaction_div').innerHTML = $('loaction_div').innerHTML;
}
</script>


<script type="text/javascript">
var CKF_CORE_PATH='<?php echo ADMIN_IMGPATH;?>/admin/plugins/<?php echo PLUGINS_PATH;?>/';
var CKF_CORE_PATH_CODE='<?php echo ADMIN_URLPATH;?>plugins/run/<?php echo PLUGINS_PATH;?>/';
</script>
<script type="text/javascript">

// CKFinder specific settings.
var CKFConfig =
{
	AutoDetectLanguage	: true,
	DefaultLanguage		: 'zh-cn',
	Languages			: 'en,de,es,es-mx,fr,it,lv,pl,pt-br,ru,sk,zh-cn,zh-tw',

	SkinPath			: CKF_CORE_PATH+'skins/<?php echo Plugins::config('skin');?>/',
	DirectDownload		: false
};

</script>
<script type="text/javascript">
function LoadScript(url){document.write('<script type="text/javascript" src="'+url+'"><\/script>');}(function(){var lang=false;if(CKFConfig.AutoDetectLanguage){var ey=(navigator.userLanguage||navigator.language).toLowerCase();CKFConfig.Languages=','+CKFConfig.Languages+',';if(ey.length>=5){ey=ey.substr(0,5);if(CKFConfig.Languages.indexOf(','+ey+',')>=0)lang=ey;}if(!lang&&ey.length>=2){ey=ey.substr(0,2);if(CKFConfig.Languages.indexOf(','+ey+',')>=0)lang=ey;}}LoadScript(CKF_CORE_PATH+'lang/'+(lang||CKFConfig.DefaultLanguage)+'.js');})();</script>
<script type="text/javascript">
var sSuffix = /msie/.test( navigator.userAgent.toLowerCase() ) ? 'ie' : 'gecko' ;

LoadScript( CKF_CORE_PATH+'js/ckfinder_' + sSuffix + '.js' ) ;
</script>
<script type="text/javascript">window.onload=function(){ab.Init();$('xDialogButtonCancel').value=CKFLang.CancelBtn;$('xDialogButtonOk').value=CKFLang.OkBtn;};window.onunload=ab.jy;if(U.dF){try{document.execCommand('BackgroundImageCache',false,true);}catch(e){}cl.af=new hw(window);}document.write('<link href="'+CKFConfig.SkinPath+'fck_editor.css" type="text/css" rel="stylesheet" />');
document.write('<link href="'+CKFConfig.SkinPath+'fck_dialog.css" type="text/css" rel="stylesheet" />');
document.write('<link href="'+CKF_CORE_PATH+'css/ckfinder.css" type="text/css" rel="stylesheet" />');
</script>
<div class="mainTable" id="ckfind_maindiv" style="height:500px;">
<table style="height: 100%; width: 100%; table-layout:fixed;" cellpadding="0" cellspacing="0">
	<tr>
		<td style="width: 200px; height: 100%;">
			<table style="height: 100%; width: 100%" cellpadding="0" cellspacing="0">
				<tr>
					<td class="PopupTitle">
						<script type="text/javascript">document.write(CKFLang.FoldersTitle);</script>
					</td>
				</tr>
				<tr>
					<td id="hf" style="height: 100%;">
						&nbsp;
					</td>
				</tr>
			</table>
		</td>
		<td id="qB" class="PopupTabArea" style="width: 5px">
		</td>
		<td id="iO" style="height: 100%; vertical-align:top">
			&nbsp;
		</td>
	</tr>
	<tr>
		<td id="jR" class="PopupTabArea dY" colspan="3">
			&nbsp;
		</td>
	</tr>
</table>
<div id="xCover" class="BackColor" style="display: none">
</div>
<div id="xDialog" class="DarkBackground" style="display: none">
	<div class="DialogInner BackColor">
		<div id="iM">
			Alert message</div>
			<input id="xDialogField" type="text" />
		<div id="xDialogButtons">
			<input id="xDialogButtonOk" type="button" value="Ok" />
			<input id="xDialogButtonCancel" type="button" value="Cancel" />
		</div>
	</div>
</div>

</div>

<script type="text/javascript">
if (document.location.href!=parent.location.href){
	var onresizefun = function (){
		var obj=window.$('ckfind_maindiv').style.height = Math.max(300,(parent.document.documentElement.clientHeight - 110)) +'px';
		parent.onresizefun = null;
		changeHeight();
		parent.changeHeight();
		parent.onresizefun = onresizefun;
	};
	parent.onresizefun = onresizefun;
	onresizefun();
}else{
	window.onresize = function (){
		var obj = $('ckfind_maindiv');
		obj.style.height = Math.max(300,(document.documentElement.clientHeight - 110)) +'px';
	}
	window.onresize();
}

</script>