
function callNowEditorName(objid){
	var text_div = $(objid + '__TextDiv');
	var fck_div = $(objid + '__FckDiv');
	if (!text_div)return;
	var highlight_div = text_div.getElementsByTagName('iframe')[0];
	var editlive_div = $(objid + '__EditliveDiv');

	var nowEditor ='textarea';
	if (fck_div)
	{
		if (fck_div.style.display != 'none')
		{
			nowEditor = 'fckeditor';
		}
	}
	if (highlight_div)
	{
		if (highlight_div.style.display != 'none')
		{
			nowEditor = 'highlight';
		}
	}
	if (editlive_div)
	{
		if (editlive_div.style.display != 'none')
		{
			nowEditor = 'editlive';
		}
	}
	return nowEditor;
}

function changeEditor(objid,toeditor){
	var obj = $(objid);
	var objdiv = $(objid+'__div');
	if (!obj || !objdiv)
	{
		alert("页面加载错误，缺少项目！");
		return;
	}
	if (toeditor != 'fckeditor' && toeditor != 'highlight' && toeditor != 'editlive')
	{
		toeditor = 'textarea';
	}
	var text_div = $(objid + '__TextDiv');
	var fck_div = $(objid + '__FckDiv');
	var editlive_div = $(objid + '__EditliveDiv');
	var highlight_div = text_div.getElementsByTagName('iframe')[0];

	var nowEditor = callNowEditorName(objid);

	if (nowEditor == toeditor)
	{
		return;
	}
	if (window.frames[objid+'___eFrame'] && toeditor=='editlive')
	{
		window.frames[objid+'___eFrame'].changeEditor = false;
	}

	renewEditorStr(objid,nowEditor,toeditor);

	if (nowEditor != 'editlive')
	{
		changeToEditor(objid,nowEditor,toeditor);
	}
}


function changeToEditor(objid,nowEditor,toeditor){
	obj = $(objid);
	var objdiv = $(objid+'__div');
	var objshowdiv = $('textline_Div');
	if (!obj || !objdiv)return;

	if (toeditor != 'fckeditor' && toeditor != 'highlight' && toeditor != 'editlive')
	{
		toeditor = 'textarea';
	}
	var text_div = $(objid + '__TextDiv');
	var fck_div = $(objid + '__FckDiv');
	var editlive_div = $(objid + '__EditliveDiv');
	var highlight_div = text_div.getElementsByTagName('iframe')[0];

	if (toeditor == 'editlive')
	{
		if (nowEditor == 'textarea' || nowEditor == 'highlight')
		{
			delHeaderCode(objid);		//移除HEADER部分
		}

		obj.style.display='none';
		if (objshowdiv)objshowdiv.style.display='none';

		if (fck_div)fck_div.style.display='none';
		if (highlight_div)highlight_div.style.display='none';

		if (!editlive_div){
			editlive_div = document.createElement("div");
			editlive_div.id = objid + '__EditliveDiv';
			editlive_div.style.fontSize = "12px";
			editlive_div.style.lineHeight = "1.6em";
			editlive_div.innerHTML = '<iframe id="'+objid+'___eFrame" name="'+objid+'___eFrame" frameBorder="no" width="100%" scrolling="no" height="510"></iframe>';
			objdiv.appendChild(editlive_div);

			try{
			window.frames[objid+'___eFrame'].document.write('<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><script>var changeEditor=false;window.onerror=function (){var myhash = document.location.hash||"#1";myhash=myhash.substr(1,1)-0;if (myhash<4 || myhash==0){document.location.hash = (myhash+1);window.location.reload();}else{alert(unescape("%u52A0%u8F7D%u5931%u8D25%uFF0C%u8BF7%u67E5%u770B%u9EA6%u742A%u5E2E%u52A9%u6587%u6863%uFF01"));parent.resetEditor("'+objid + '",true);parent.$("'+objid + '__EditliveDiv").parentNode.removeChild(parent.$("'+objid + '__EditliveDiv"));}return true;};</script><script src="'+(url_base||'')+'/admin/editlive/redistributables/editlivejava/editlivejava.js" type="text/javascript"></script></head><body style="padding:0;margin:0;"><textarea style="display:none;" id="bodyContent" />'+escape(obj.value)+'</textarea><script type="text/javascript">var bodyContents;var editlive;function showEditor(){ editlive = new EditLiveJava("ELApplet", "100%", 510);editlive.setDownloadDirectory("'+(url_base||'')+'/admin/editlive/redistributables/editlivejava"); editlive.setXMLURL("'+(url_base||'')+'/admin/editlive/redistributables/editlivejava/sample_eljconfig.xml");editlive.setBody(document.getElementById("bodyContent").value);editlive.setUseMathML(true);editlive.show();}showEditor();function GetBody(){editlive.GetBody("getEditLiveBody");}function getEditLiveBody(src){bodyContents=src;var input = document.getElementById("bodyContent");var obj=parent.$("'+objid+'");if (input.value!=bodyContents){input.value = src;obj.value=src;}if (parent.iseditorSubmit){parent.editorLiveSubmit(parent.iseditorSubmit,"'+objid+'");}else if (parent.editliveToEditor){parent.$("'+objid + '__EditliveDiv").style.display="none";parent.changeToEditor("'+objid+'","editlive",parent.editliveToEditor)};parent.iseditorSubmit = false;parent.editliveToEditor=false;}</script></body></html>');
			}catch(e){alert("创建失败，请刷新页面后再试！");resetEditor(objid,true);return;}
		}else{
			editlive_div.style.display='block';
			window.frames[objid+'___eFrame'].editlive.setBody(escape(obj.value));
		}

	}else if (toeditor == 'fckeditor'){

		if (nowEditor == 'textarea' || nowEditor == 'highlight')
		{
			delHeaderCode(objid);
		}
		
		/*
		var tmpvalue = obj.value.toLowerCase();
		if (tmpvalue.indexOf('<body')>=0 || tmpvalue.indexOf('<head>') >= 0)
		{
			if ( !confirm('当前代码含有FCKEditor会过滤的字符，是否继续？'))
			{
				return;
			}
		}
		*/
		if (highlight_div)highlight_div.style.display='none';
		if (editlive_div)editlive_div.style.display='none';
		obj.style.display='none';
		if (objshowdiv)objshowdiv.style.display='none';
		if (!fck_div)
		{
			fck_div = document.createElement("div");
			fck_div.id = objid + '__FckDiv';
			fck_div.style.fontSize = "12px";
			fck_div.style.lineHeight = "1.6em";
			fck_div.innerHTML = '<input id="'+objid+'___Config" value="FormatSource=true&FullPage=false&EnterMode=div&ShiftEnterMode=div" style="display:none;" type="hidden" /><iframe id="'+objid+'___Frame" src="'+(url_base||'')+'/admin/fckeditor/editor/fckeditor.html?InstanceName='+objid+'&Toolbar=Default" frameBorder="no" width="100%" scrolling="no" height="510"></iframe>';
			objdiv.appendChild(fck_div);
		}else{
			fck_div.style.display='block';
			var oEditor = FCKeditorAPI.GetInstance(objid);
			if (oEditor.GetXHTML(true) != obj.value){
				oEditor.SetData( obj.value||'' ) ;
			}
		}

	}else if(toeditor == 'highlight'){

		if (nowEditor == 'editlive' || nowEditor == 'fckeditor')
		{
			addHeaderCode(objid);
		}

		var tooLen = 10000;
		var tmpvalue;
		if (highLightEtidor[objid])
		{
			tmpvalue = obj.value;
			obj.value = highLightEtidor[objid].getCode();
		}

		if( obj.value.length > tooLen && tmpvalue != obj.value ){
			if ( !_confirm('当前代码超过' + tooLen + '字符，\n切换到高亮界面可能需要很长时间，也可能造成浏览器崩溃\n\n是否继续？'))
			{
				return;
			}else{
				_alert('现在切换，请耐心等待！\n\n若浏览器长时间未响应请刷新页面！');
			}
		}
		obj.style.display='none';
		if (objshowdiv)objshowdiv.style.display='none';
		if (fck_div)fck_div.style.display='none';
		if (editlive_div)editlive_div.style.display='none';
		if (!highLightEtidor[objid])
		{
			var thepath = (url_base||'')+'/admin/highlight/';
			highLightEtidor[objid] = CodeMirror.fromTextArea(objid, {
				height: "450px",
				parserfile: ["parsexml.js", "parsecss.js", "tokenizejavascript.js", "parsejavascript.js",
							 "../contrib/php/js/tokenizephp.js", "../contrib/php/js/parsephp.js",
							 "../contrib/php/js/parsephphtmlmixed.js"],
				stylesheet: [thepath + "css/xmlcolors.css", thepath + "css/jscolors.css", thepath + "css/csscolors.css", thepath + "contrib/php/css/phpcolors.css"],
				path: thepath + 'js/',
				parserConfig: {
					indentUnit: 4
				},
				continuousScanning: 500,
				lineNumbers: true,
				textWrapping: true
				
			});
			/*
			highLightEtidor[objid] = CodeMirror.fromTextArea(objid, {
				height: "450px",
				parserfile: "parsexml.js",
				stylesheet: (url_base||'')+"/admin/highlight/css1/xmlcolors.css",
				path: (url_base||'')+"/admin/highlight/js1/",
				continuousScanning: 500
			});
			*/
		}else{
			highlight_div.style.display = 'block';
			if (tmpvalue != obj.value)
			{
				highLightEtidor[objid].setCode(tmpvalue);
			}
		}
		//copytext(obj.parentNode.parentNode.innerHTML);
	}else{

		if (nowEditor == 'editlive' || nowEditor == 'fckeditor')
		{
			addHeaderCode(objid);
		}

		if (fck_div)fck_div.style.display='none';
		if (highlight_div)highlight_div.style.display='none';
		obj.style.display='';
		if (objshowdiv)objshowdiv.style.display='';
		obj.focus();
		if (nowEditor != 'editlive')
		{
	//		if (editlive_div)editlive_div.style.display='none';
			obj.style.display='';
			obj.focus();
			if (objshowdiv)objshowdiv.style.display='';
		}else{
			if (window.frames[objid+'___eFrame'])
			{
//				window.frames[objid+'___eFrame'].changeEditor = true;
			}
		}
	}
}

var highLightEtidor = {};
var editliveEtidor = {};
var foreditliveGetValue = false;
var editliveToEditor = false;		//标记由editlive切换到哪个编辑器
var codeHeader = {};

function renewEditorStr(objid,nowEditor,toeditor){
	if (nowEditor == 'textarea')return;

	var obj = $(objid);
	if (!obj)return;
	
	var mystring='';
	if(nowEditor=='fckeditor'){

		var oEditor = FCKeditorAPI.GetInstance(objid);
		mystring = oEditor.GetXHTML(true);

	}else if(nowEditor=='editlive')
	{
		if (window.frames[objid+'___eFrame'])
		{
			editliveToEditor = toeditor;
			try{
				window.frames[objid+'___eFrame'].GetBody();
			}catch(e){}
		}else{
			editliveToEditor = false;
		}
		return;
	}else if(nowEditor=='highlight')
	{
		mystring = highLightEtidor[objid].getCode();
	}

	obj.value = mystring;

	editliveToEditor = false;
}

function delHeaderCode(objid){
	var obj =$(objid);
	if (!obj)return;

	codeHeader[objid] = {};
	var tmpvalue = obj.value.toLowerCase();
	if (tmpvalue.indexOf('<body')>=0)
	{
		var temp1 = obj.value.substr(tmpvalue.indexOf('<body'));
		codeHeader[objid].left  = obj.value.substr(0,tmpvalue.indexOf('<body'))+temp1.substr(0,temp1.indexOf('>')+1)+"\n";
	}else{
		codeHeader[objid].left = '\n';
	}
	if (tmpvalue.indexOf('</body>')>=0)
	{
		codeHeader[objid].right  = "\n"+obj.value.substr(tmpvalue.indexOf('</body>'));
	}else{
		codeHeader[objid].right = '\n';
	}
	mystring = obj.value.substr(codeHeader[objid].left.length-1,obj.value.length - codeHeader[objid].right.length - codeHeader[objid].left.length+2);

	obj.value = mystring;

}

function addHeaderCode(objid){
	if (!codeHeader[objid])return;
	var obj =$(objid);
	if (!obj)return;

	obj.value = (codeHeader[objid].left=='\n'?"":codeHeader[objid].left||'') + obj.value +  (codeHeader[objid].right=='\n'?'':codeHeader[objid].right||'');
}

var FCKeditorAPI = false;
function getHtmlString(objid){
	var nowEditor = callNowEditorName(objid);
	var obj = $(objid);
	var oEditor;
	if (FCKeditorAPI)
	{
		oEditor = FCKeditorAPI.GetInstance(objid);
	}
	if (nowEditor == 'highlight')
	{
		var mystring = highLightEtidor[objid].getCode();
		obj.value = mystring;
		if (oEditor)
		{
			oEditor.SetData( obj.value ) ;
		}
	}else if (nowEditor == 'fckeditor'){
		if (oEditor)
		{
			obj.value = oEditor.GetXHTML(true);
		}
	}else{
		if (oEditor)
		{
			oEditor.SetData( obj.value ) ;
		}
	}
}


function resetEditor(objid,isadd){
	if (!$(objid))return;
	if (isadd)addHeaderCode(objid);
	if ($(objid+'__EditliveDiv'))$(objid+'__EditliveDiv').style.display="none";
	if ($(objid+'__FckDiv'))$(objid+'__FckDiv').style.display="none";
	var text_div = $(objid + '__TextDiv');
	var highlight_div = text_div.getElementsByTagName('iframe')[0];
	if (highlight_div)highlight_div.style.display="none";
	$(objid).style.display="block";
}


var iseditorSubmit = false;		//标记是否提交
function submitForm(formobj,objid){
	var obj = $(objid);
	if (!obj)
	{
		alert("错误参数！");
		return false;
	}

	iseditorSubmit = formobj;
	var nowEditor = callNowEditorName(objid);
	
	renewEditorStr(objid,nowEditor,'textarea');
	if (nowEditor == 'editlive' || nowEditor == 'fckeditor')
	{
		addHeaderCode(objid);
	}

	if (nowEditor!='editlive')
	{
		iseditorSubmit = false;
		formobj.submit();
	}
	return false;
}


function editorLiveSubmit(obj,objid){
	addHeaderCode(objid);
	obj.submit();
}