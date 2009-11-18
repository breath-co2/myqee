var userAgent = navigator.userAgent.toLowerCase();

var is_firefox=navigator.userAgent.indexOf('Firefox')>=0?true:false;
var is_opera = navigator.appName.indexOf('Opera')>=0?true:false;;
var is_moz = (navigator.product == 'Gecko') && userAgent.substr(userAgent.indexOf('firefox') + 8, 3);
var is_ie =navigator.appName=="Microsoft Internet Explorer"?true:false;
var ie = (userAgent.indexOf('msie') != -1 && !is_opera) && userAgent.substr(userAgent.indexOf('msie') + 5, 3);

var dom = (document.compatMode &&document.compatMode=="CSS1Compat")?document.documentElement:document.body;

function $(el){return document.getElementById(el);}


var _IMPORT_JSFILE = {};
function $import(jsfile,onloadfun,defer){
	if (!_IMPORT_JSFILE[jsfile])
	{
		var s = 'script';
		_IMPORT_JSFILE[jsfile] = 1;
		var a=('<'+s+' src="'+jsfile+'"'+(defer?' defer="defer"':'')+(onloadfun?(is_ie?' onreadystatechange="if(this.readyState!=\'complete\'){return;}'+onloadfun+'"':' onload="'+onloadfun+'"'):'')+'></'+s+'>');
		document.write (a);
	}
}


var frameFrame = window.self;		//子窗口
var parentFrame = window.self;		//父窗口

String.prototype.trim = function(charlist){
	if (charlist == '' ) {
		return this.replace(/(^\s*)|(\s*$)/g, "");	
	} else {
		var re = new RegExp ('(^'+charlist+')|('+charlist+'$)','g');
		return this.replace(re, "");
	}
}

window._alert = window.alert;

window.alert = function(alertset,w,h,title,handler) {
	_set_frame();
	if (typeof (alertset) != 'object'){
		alertset = {'message':(alertset||'')};
	}
	//alertset.message = alertset.message.replace(/\n/g,'<br />');
	alertset.width = alertset.width || w;
	alertset.height = alertset.height || h;
	alertset.title = alertset.title || title || '信息提示';
	alertset.handler = alertset.handler || handler;

	if (!parentFrame.ymPrompt){
		_alert(alertset.message);
		if (alertset.handler){
			try{alertset.handler('ok')}catch(e){}
		}
	}else{
		//try{parentFrame.ymPrompt.close();}catch(e){}
		if (alertset._type=='errorInfo'){
			parentFrame.ymPrompt.errorInfo(alertset);
		}else if(alertset._type=='succeedInfo'){
			parentFrame.ymPrompt.succeedInfo(alertset);
		}else if(alertset._type=='win'){
			alertset.allowSelect = alertset.allowSelect || true;		//默认允许
			alertset.allowRightMenu = alertset.allowRightMenu || true;	//默认允许
			parentFrame.ymPrompt.win(alertset);
		}else{
			parentFrame.ymPrompt.alert(alertset);
		}
		if (window.parentFrame)window.parentFrame.myqee($('ym-window'));
	}
}

window.succeed = function(alertset,w,h,title,handler){
	if (typeof (alertset) != 'object'){
		alertset = {'message':(alertset||'')};
	}
	alertset.title = alertset.title || '操作成功';
	alertset._type = 'succeedInfo';
	alert(alertset,w,h,title,handler);
}
window.error = function(alertset,w,h,title,handler){
	if (typeof (alertset) != 'object'){
		alertset = {'message':(alertset||'')};
	}
	alertset.title = alertset.title || '错误提示';
	alertset._type = 'errorInfo';
	alert(alertset,w,h,title,handler);
}
window.win = function(alertset,w,h,title,handler){
	if (typeof (alertset) != 'object'){
		alertset = {'message':(alertset||'')};
	}
	alertset._type = 'win';
	alert(alertset,w,h,title,handler);
}

window._confirm = window.confirm;

window.confirm = function (alertset,w,h,title,handler){
	_set_frame();
	if (typeof (alertset) != 'object'){
		alertset = {'message':(alertset||'是否继续此操作？')};
	}
	//alertset.message = alertset.message.replace(/\n/g,'<br />');
	alertset.width = alertset.width || w;
	alertset.height = alertset.height || h;
	alertset.title = alertset.title || '请确认';
	alertset.handler = alertset.handler || handler;

	if (!parentFrame.ymPrompt){
		var myconform = window._confirm(alertset['message']);
		if (!alertset.handler)
		{
			return myconform;
		}else{
			var r = false;
			try{r = alertset.handler(myconform?'ok':'cancel');}catch(e){}
			return r;
		}
	}else{
		try{parentFrame.ymPrompt.close();}catch(e){}
		parentFrame.ymPrompt.confirmInfo(alertset,w,h,title,handler);
		parentFrame.myqee($('ym-window'));
	}
}


window.closewin = function(type,autoclose){
	_set_frame();
	try{parentFrame.ymPrompt.doHandler(type,autoclose);}catch(e){}
}


var page_urlpath = '/';
function ask_del(id,delurl,msg){
	msg=msg||'您确认要删除此数据？';
	confirm(msg,400,null,'删除确认',function(tp){if (tp=='ok') goUrl( page_urlpath + delurl +'/'+id,'hiddenFrame')});
}


function _set_frame(){
	try {
		if( typeof(frameFrame.win)=='function'){
			parentFrame = window.parent;
		}
	}catch(e){}
	parentFrame.frameFrame = window.self;
}

function doane(event) {
	var e = event ? event : window.event;
	if(ie) {
		e.returnValue = false;
		e.cancelBubble = true;
	} else if(e) {
		e.stopPropagation();
		e.preventDefault();
	}
}
function isUndefined(variable) {
	return typeof variable == 'undefined' ? true : false;
}


function showMenu(){
	return false;
}

function innerHTML(el,HTMLString) {
	if (!el) return;
	HTMLString=HTMLString||'';
	if (is_ie) {
		el.innerHTML=HTMLString;
	} else {
		//For Mozilla,Opare
		var nSibling = el.nextSibling;
		var pNode = el.parentNode;
		pNode.removeChild(el);
		el.innerHTML = HTMLString;
		pNode.insertBefore(el,nSibling);
	}
}


//获取文本
function innerText(objID){
	if (typeof(objID)==='object')
	{
		var obj=objID;
	}else{
		var obj=$(objID);
	}
	if (obj!=null)
	{
		if (is_ie)
		{
			return obj.innerText;
		}else{
			return obj.textContent;
		}
	}else{
		return false;
	}
}

function myqee(obj){
	chkInput(obj);
}

function chkInput(obj){
	chkInputBeign("input",obj);
	//chkInputBeign("select",obj);
	chkInputBeign("textarea",obj);
}

function chkInputBeign(inputType,obj){
	inputType=inputType||"input";
	obj=obj||document.body;
	var objobj=obj.getElementsByTagName(inputType);
	for (var inputobj in objobj)
	{
		var theClassObj=objobj[inputobj];
		var theClassName="";
		if (theClassObj!=null)
		{
			theClassName=theClassObj.className;
		}
		if (theClassName)
		{
			var isset = objobj[inputobj].getAttribute('_is_myqee_set');
			if (isset=='true')continue;
			objobj[inputobj].setAttribute('_is_myqee_set','true');
			if (theClassName=="btn"||theClassName=="btn2"||theClassName=="bbtn"||theClassName=="btns"||theClassName=="btnss"||theClassName=="btnsss"||theClassName=="btnl"||theClassName=="btnall")
			{
				if(objobj[inputobj].onmouseover)objobj[inputobj].__onmouseover=objobj[inputobj].onmouseover;
				objobj[inputobj].onmouseover=function(){this.className=this.className+" btnover";if(this.__onmouseover)this.__onmouseover();};

				if(objobj[inputobj].onmouseout)objobj[inputobj].__onmouseout = objobj[inputobj].onmouseout;
				objobj[inputobj].onmouseout=function(){this.className=this.className.replace(/ btnover/g,"").replace(/ btnactive/g,"");if(this.__onmouseout)this.__onmouseout();};
				
				if(objobj[inputobj].onmousedown)objobj[inputobj].__onmousedown = objobj[inputobj].onmousedown;
				objobj[inputobj].onmousedown=function(){this.className=this.className.replace(/ btnover/g,"")+" btnactive";if(this.__onmousedown)this.__onmousedown();};
				
				if (objobj[inputobj].disabled){objobj[inputobj].className+=' btn_disabled';};
			}
			else if(theClassName.indexOf('input')>=0)
			{
				if(objobj[inputobj].onfocus)objobj[inputobj].__onfocus = objobj[inputobj].onfocus;
				objobj[inputobj].onfocus=function(){this.className=this.className+" inputover";if(this.__onfocus)this.__onfocus();};
				
				if(objobj[inputobj].onblur)objobj[inputobj].__onblur = objobj[inputobj].onblur;
				objobj[inputobj].onblur=function(){this.className=this.className.replace(/ inputover/g,"");if(this.__onblur)this.__onblur();};
				
				if (objobj[inputobj].disabled){objobj[inputobj].className+=' disabled';};
			}
		}
	}
}


function goUrl(newurl,target){
	if ( newurl.substr(0,7) != 'http://' && newurl.substr(0,8) != 'https://' && newurl.substr(0,1) != '/' && newurl.substr(0,2) != './' && newurl.substr(0,3) != '../' && newurl.substr(0,1) != '?' && newurl.substr(0,1) != '#'){
		newurl = (parent.document.getElementsByTagName('base')[0]||{'href':'/'}).href + newurl;
	}
	if (!target){
		location=newurl;
	}else if(target.indexOf("_blank")>=0){
		window.open(newurl);
	}else if(!window.frames[target]){
		window.open(newurl,target);
	}else{
		window.frames[target].location=newurl;
	}
}

function goback(go,goUrl){
	if (go>-100){}else{
		go=-1;
	}
	if (go==-1 && document.referrer)
	{
		self.document.location=document.referrer;
	}else{
		history.go(go);
	}
	if (goUrl){
		setTimeout(function(){
			location=goUrl;
		},200);
	}
}



function tag(thisid,titlePrefix,mainPrefix,searchID){
	var mainobj;
	if (searchID)
	{
		mainobj = $(searchID) || document.body;
	}else{
		mainobj = document.body;
	}
	var el = mainobj.getElementsByTagName("li");
	for (var i=0;i<el.length;i++)
	{
		if (el[i].id)
		{
			if (el[i].id.substr(0,titlePrefix.length+1) == titlePrefix+'_')
			{
				var thisTitleId = el[i].id.substr(titlePrefix.length);
				var mainobj = $(mainPrefix + thisTitleId);
				if (el[i].id != thisid)
				{
					el[i].className = '';
					if (mainobj)
					{
						mainobj.style.display = 'none';
					}
				} else {
					el[i].className = 'now';
					if (mainobj)
					{
						mainobj.style.display = '';
					}
				}
			}
		}
	}
}




function selectbox(obj,leftper,type){
	var allInput = document.body.getElementsByTagName('input');
	var ischeck = obj.checked;
	type=type||false;

	for (var i in allInput )
	{
		if (allInput[i].type=='checkbox' && allInput[i].id.substr(0,leftper.length+1)==leftper+'_')
		{
			if (type)
			{
				allInput[i].checked = allInput[i].checked?false:true;
			}else{
				allInput[i].checked = ischeck;
			}
			if(allInput[i].onclick)allInput[i].onclick();
		}
	}
}


function submitbox(leftper,gourl,target){
	var allInput = document.body.getElementsByTagName('input');
	var tmpid = ',';
	for (var i in allInput )
	{
		if (allInput[i].type=='checkbox' && allInput[i].id.substr(0,leftper.length+1)==leftper+'_')
		{
			if (allInput[i].checked)
			{
				tmpid += allInput[i].id.substr(leftper.length+1)+',';
			}
		}
	}
	if (tmpid == ',')
	{
		alert("未选择任何信息！");
		return false;
	}
	tmpid = tmpid.substr(1,tmpid.length-2);
	goUrl(page_urlpath+gourl.replace(/\[id\]/g,tmpid),target);
}



function copytext(str,alertinfo){
	alertinfo=alertinfo||'复制成功';
	if (is_ie){
		window.clipboardData.setData("Text",str);
	}else{
		var flashObj=$("flashCopyDiv");
		if (!flashObj){
			flashObj = document.createElement("div");
			flashObj.id='flashCopyDiv';
			document.body.appendChild(flashObj);
		}
		flashObj.innerHTML='<embed src="'+page_urlpath+'images/admin/copytext.swf" FlashVars="clipboard=' + encodeURIComponent(str) + '" width="0" height="0" type="application/x-shockwave-flash"></embed>';
	}
	alert(alertinfo);
	return false;
}


function show_upload_frame(id,url,type){
	var theTitle = {
		'upfile':'文件上传',
		'upimg':'图片上传',
		'upflash':'Flash上传'
	};
	theTitle[type] = theTitle[type] || '文件上传';
	window.win({message:url+'#'+escape(id),width:600,height:320,title:theTitle[type],iframe:true});
}

function swapNodes(item1,item2) {
	if (!item1 || !item2)return;
	if (is_ie){
		if (ie<7){
			//ie6有BUG
			var el = item1.getElementsByTagName("INPUT"),i=0,arr=[];
			for(;i<el.length;i++) {
				if(el[i].type == "checkbox")arr.push(el[i], el[i].checked);
			}
			el = item2.getElementsByTagName("INPUT"),i=0;
			for(;i<el.length;i++) {
				if(el[i].type == "checkbox")arr.push(el[i],el[i].checked);
			}
		}
		item1.swapNode(item2);
		if (ie<7){
			while(arr.length>0)
			arr.shift().checked=arr.shift();
		}
		return;
	}
	var itemtmp = item1.cloneNode(1);
	var parent = item1.parentNode;
	item2 = parent.replaceChild(itemtmp,item2);
	parent.replaceChild(item2,item1);
	parent.replaceChild(item1,itemtmp);
	itemtmp = null;
}



function readCookie(name){
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for (var i = 0; i < ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0) == ' ') 
			c = c.substring(1, c.length);
		if (c.indexOf(nameEQ) == 0) 
			return decodeURIComponent(c.substring(nameEQ.length, c.length));
	}
	return null;
}

function createCookie(name, value, days, path){
	var expires = "";
	if (days) {
		var date = new Date();
		date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
		expires = "; expires=" + date.toGMTString();
	};
	path = path || '/';
	document.cookie = name + "=" + encodeURIComponent(value) + expires + ";path=" + path;
}

////从弹出框中选择录入值，用户字段管理扩展表的时候
var _SELECT_VALUE_CACHE = [];
function showSelectValueFrame (code,page) {
	page = page||1;
	if(_SELECT_VALUE_CACHE[code+'_'+page]){
		window.win(_SELECT_VALUE_CACHE[code+'_'+page],560,300,'请选择');
		return true;
	}
	ajax.requestFile = page_urlpath+'info/get_extend_fieldvalue?page='+page;
	ajax.setVar('code',code);
	ajax.method = 'POST';
	ajax.onCompletion = function (){
		if (this.response.substr(0,6)=='ERROR:')
		{
			window.alert(this.response.substr(6),400);
			return false;
		}
		_SELECT_VALUE_CACHE[code+'_'+page] = this.response;
		window.win(this.response,560,300,'请选择');
	}
	ajax.onError = function (){
		alert('请求失败，请稍后再试！');
	}
	ajax.runAJAX();
}

function setExtandValue (fieldname,writeValue,showValue,expstr,isappend) {
	var obj = $('__myqee_input_'+fieldname);
	var objshow = $('__myqee_input_'+fieldname+'_showinput');
	if (!obj||!objshow)return;
	var autoclose = true;
	if (isappend == 0) {
		objshow.value=showValue;
		obj.value=writeValue;
	} else {
		autoclose = $('_select_auto_close_obj');
		if (autoclose)autoclose = autoclose.checked;
		expstr = expstr || '|';
		var oldvalue = obj.value;
		var newvalue = _get_ext_value(oldvalue,writeValue,expstr);
		if (oldvalue!=newvalue){
			obj.value = newvalue;
			objshow.value = (objshow.value+expstr+showValue).trim(expstr);
		}
	}
	if(autoclose)window.closewin();
}

function _get_ext_value(str,v,expstr){
	if ((expstr+str+expstr).indexOf((expstr+v+expstr))>=0){
		return str;
	}
	return (str+expstr+v).trim(expstr);
}
/*

var frameFrame = window.self;		//子窗口
var parentFrame = window.self;	//父窗口
var _showBoxCloseAPI = false;		//关闭窗口时调用接口

function showMsgBox(title, msg, w, h , closeapi){
	try {
		if( typeof(window.parent.showMsgBox)=='function'){
			parentFrame = window.parent;
			var iWidth = document.documentElement.clientWidth;
			var iHeight = document.documentElement.clientHeight;
			_showFrameBg(iWidth,iHeight);
		}
	}catch(e){}
	parentFrame.frameFrame = window.self;
	parentFrame.showBox( title, msg, w, h ,closeapi);
}

function _showFrameBg(iWidth,mHeight){
	if (is_ie){
		var frameObj = $('_msg_box_iframe');
		if(frameObj){
			frameObj.style.width = iWidth + 'px';
			frameObj.style.height = mHeight + 'px';
			frameObj.style.display = 'block';
		}else{
			frameObj = document.createElement("iframe");
			frameObj.id='_msg_box_iframe';
			frameObj.style.cssText = "position:absolute;left:0px;top:0px;width:"+iWidth+"px;height:"+mHeight+"px;filter:Alpha(Opacity=0);";
			document.body.appendChild(frameObj);
		}
	}
}

function showBox(title, msg, w, h,closeapi){
	w = w >= 100?w:100;
	h = h >= 50?h:50;
	var titleheight = "22px";			// 提示窗口标题高度 
	var bordercolor = "#739cb5";		// 提示窗口的边框颜色 
	var bgcolor = "#FFFFFF";			// 提示内容的背景色

	var iWidth = document.documentElement.clientWidth;
	var iHeight = document.documentElement.clientHeight;

	var mHeight = Math.max(document.body.clientHeight, iHeight);
	
	var sTop = document.body.scrollTop||document.documentElement.scrollTop;
	var sLeft = document.documentElement.scrollLeft||document.documentElement.scrollLeft;

	_showFrameBg(iWidth,iHeight);

	var bgObj = $('_bg_box_div');
	if (bgObj){
		bgObj.style.width = iWidth + 'px';
		bgObj.style.height = mHeight + 'px';
		bgObj.style.display = 'block';
	}else{
		bgObj = document.createElement("div");
		bgObj.id='_bg_box_div';
		bgObj.style.cssText = "position:absolute;left:0px;top:0px;width:"+iWidth+"px;height:"+mHeight+"px;filter:Alpha(Opacity=30);opacity:0.3;background-color:#000000;";
		document.body.appendChild(bgObj);
	}

	var msgObj = $('_obj_box_div');
	if (msgObj){
		msgObj.style.top = (sTop + (iHeight-h)/2)+"px";
		msgObj.style.left = (sLeft + (iWidth-w)/2)+"px";
		msgObj.style.width = w +'px';
		msgObj.style.height = h +'px';
		msgObj.style.display = 'block';
	}else{
		msgObj=document.createElement("div");
		msgObj.id = '_obj_box_div';
		msgObj.style.cssText = "position:absolute;top:"+(sTop+(iHeight-h)/2)+"px;left:"+(sLeft+(iWidth-w)/2)+"px;width:"+w+"px;height:"+h+"px;text-align:left;border:1px solid "+bordercolor+";background-color:"+bgcolor+";padding:1px;line-height:1.6em;";
		document.body.appendChild(msgObj);
	}

	var table = $('_msg_box_table');
	var titleBar = $('_msg_box_table_title');
	var msgBox = $('_msg_box_table_box');
	if (table){
		table.style.display = 'block';
		titleBar.style.width = (w-26) +'px';
		msgBox.style.height = (h-24) +'px';
	}
	else{
		table = document.createElement("table");
		table.id = '_msg_box_table';
		msgObj.appendChild(table);
		table.style.cssText = "margin:0px;border:0px;padding:0px;width:100%;";
		table.cellSpacing = 0;
		table.cellSadding = 0;
		var tr = table.insertRow(-1);
		tr.className = 'msgboxTitle';
		titleBar = tr.insertCell(-1);
		titleBar.id = '_msg_box_table_title';
		titleBar.style.paddingLeft = "5px";
		titleBar.style.width = (w-26) +"px";
		titleBar.unselectable="on";
		

		var closeBtn = tr.insertCell(-1);
		closeBtn.style.cssText = 'text-align:center;width:18px;cursor:pointer;font-weight:bold;';
		closeBtn.innerHTML = '×';
		closeBtn.title = '也可以按ESC键退出';
		closeBtn.onclick = closeMsgBox;
		msgBox = table.insertRow(-1).insertCell(-1);
		msgBox.id = '_msg_box_table_box';
		msgBox.colSpan = 2;
		msgBox.style.cssText ='width:100%;height:'+(h-24)+'px';

		msgBox.innerHTML = '<div id="_msg_box_position_div" style="position:absolute;width:100%;height:100%;display:none;"></div><div id="_msg_box_theinfo_div" style="width:100%;height:100%;overflow:auto;"></div>';
	}

	var moveX = 0;
	var moveY = 0;
	var moveTop = 0;
	var moveLeft = 0;
	var moveable = false;
	var docMouseMoveEvent = document.onmousemove;
	var docMouseUpEvent = document.onmouseup;
	
	titleBar.onmousedown = function(e) {
		var evt = e||event;
		moveable = true;
		moveX = evt.clientX;
		moveY = evt.clientY;
		moveTop = parseInt(msgObj.style.top);
		moveLeft = parseInt(msgObj.style.left);
		var sTop = document.body.scrollTop||document.documentElement.scrollTop;
		var sLeft = document.documentElement.scrollLeft||document.documentElement.scrollLeft;

		//备份原有函数
		document.onmousemove_bak = document.onmousemove;
		document.onmouseup_bak = document.onmouseup;
		
		document.onmousemove = function(e) {
			if (moveable) {
				var evt = e||event;
				var x = moveLeft + evt.clientX - moveX;
				var y = moveTop + evt.clientY - moveY;
				if ( x-sLeft > 5 &&( x + w - sLeft < iWidth-5) && y-sTop > 5 && (y + h - sTop < iHeight-5) ) {
					msgObj.style.left = x + "px";
					msgObj.style.top = y + "px";
				}
			}
		};
		
		document.onmouseup = function () {
			if (moveable) {
				document.onmousemove = docMouseMoveEvent;
				document.onmouseup = docMouseUpEvent;
				moveable = false;
				moveX = 0;
				moveY = 0;
				moveTop = 0;
				moveLeft = 0;
				var tmpobj = $('_msg_box_position_div');
				if (tmpobj)tmpobj.style.display = 'none';
			}
			//恢复原有函数
			document.onmousemove = document.onmousemove_bak;
			document.onmouseup = document.onmouseup_bak;
		};

		var tmpobj = $('_msg_box_position_div');
		if (tmpobj)tmpobj.style.display = 'block';
	}
	

	//增加对ESC键的支持
	if (is_ie){
		document.body.onkeydown_bak = document.body.onkeydown;
		frameFrame.document.body.onkeydown_bak = frameFrame.document.body.onkeydown;

		frameFrame.document.body.onkeydown = document.body.onkeydown = function (e){
			if (moveable)return false;
			//ESC键
			e = e||window.event||frameFrame.window.event;
			if (e.keyCode == 27) {
				parentFrame.closeMsgBox();
				return false;
			}
		}
	}else {
		window.onkeydown_bak = window.onkeydown;
		frameFrame.window.onkeydown_bak = frameFrame.window.onkeydown;
		frameFrame.window.onkeydown = window.onkeydown = function (e){
			if (moveable)return false;
			//ESC键
			e = e||window.event||frameFrame.window.event;
			if (e.keyCode == 27) {
				parentFrame.closeMsgBox();
				return false;
			}
		}
	}
	
	titleBar.innerHTML = title;
	
	var boxmsgdiv = $('_msg_box_theinfo_div');

	if (boxmsgdiv){
		innerHTML(boxmsgdiv,msg);
		myqee(boxmsgdiv);
	}else{
		innerHTML(msgBox,msg);
		myqee(msgBox);
	}
	
	_showBoxCloseAPI = closeapi;

	msgBox=null;
	titleBar=null;
	frameObj=null;
	bgObj=null;
	closeBtn=null;
}

function closeMsgBox(){
	if (_showBoxCloseAPI){
		if (typeof(_showBoxCloseAPI)==='function'){
			if (_showBoxCloseAPI()===false){
				return;
			}
		}else{
			try{
				eval(_showBoxCloseAPI);
			}catch(e){
				setTimeout("showMsgBox('执行错误','<br/><center>接口调用失败，请联系管理员！</center>',260,80);setTimeout('closeMsgBox()',2000);",10);
			}
		}
		_showBoxCloseAPI = null;
	}
	if ($('_msg_box_iframe'))$('_msg_box_iframe').style.display = 'none';
	$('_bg_box_div').style.display = 'none';
	$('_obj_box_div').style.display = 'none';
	$('_msg_box_table').style.display = 'none';
	//下面的是关闭框架内的框架遮层
	var _msg_box_iframe = frameFrame.$('_msg_box_iframe');
	if (_msg_box_iframe){
		_msg_box_iframe.style.display = 'none';
	}
	
	//恢复按键事件
	if (is_ie){
		document.body.onkeydown = document.body.onkeydown_bak;
		frameFrame.document.body.onkeydown = frameFrame.document.body.onkeydown_bak;
	}else{
		window.onkeydown = window.onkeydown_bak;
		frameFrame.window.onkeydown = frameFrame.window.onkeydown_bak;
	}
}
*/

function doDelObj(obj,runfun,runnum){
	if (!obj)return;
	if (!(runnum>=0 && runnum<=20))runnum=0;
	runnum++;
	if (runnum>0 && runnum<=10){
		var opacity = parseInt(10-runnum);
		obj.style.filter = "alpha(opacity=" + (opacity*10) + ")";
		obj.style.opacity = opacity/10;
	}else if(runnum==11){
		obj.style.filter = null;
		obj.style.opacity = null;
		obj.style.visibility = 'hidden';
	}else{
		obj.style.height = (obj.clientHeight/2) +'px';
	}
	//alert(obj.style.filter)
	if (runnum<=20){
		setTimeout(function(){doDelObj(obj,runfun,runnum)},20);
	}else{
		obj.parentNode.removeChild(obj);
		if (runfun)runfun();
	}
}



function tr_moveover(obj){
	if(!obj)return;
	obj.className +=' tr_over';
}

function tr_moveout(obj){
	if(!obj)return;
	if (obj.className){
		if (obj.className=='tr_over'||obj.className=='tr_over tr_over')
		{
			obj.className = '';
		}else{
			obj.className = obj.className.replace(/ tr_over/g,'');
		}
	}
}

function change_select(objid){
	var obj = $(objid);
	if (!obj)return;
	if (obj.checked){
		obj.checked = false;
	}else{
		obj.checked = true;
	}
	return select_tr(obj);
}
function select_tr(obj){
	var pobj = obj.parentNode;
	var parentobj;
	for(var i=0;i<10;i++){
		if (pobj.tagName.toLowerCase()=='tr'){
			parentobj = pobj;
			break;
		}else{
			pobj = pobj.parentNode;
		}
	}
	if (!parentobj)return;
	if (obj.checked){
		pobj.className += ' tr_select';
	}else{
		if (pobj.className)pobj.className = pobj.className.replace(/ tr_select/g,'');
	}
}

function set_control_fixed(bh,id){
	var h = dom.scrollHeight;		//页面总高度
	var sh = dom.scrollTop;			//被卷去的高度
	var ch = dom.clientHeight;		//可见区域高度
	var obj = document.getElementById((id||'control_div'));
	if (!obj)return;
	
	bh = bh>0?bh:100;
	if (h-sh-ch<bh){
		obj.className = '';
	}else if(obj.className!='control'){
		obj.className = 'control';
	}
	obj.style.left = - dom.scrollLeft +'px';
}

////////////////////////////////创建表格
var myTable =new Array();

function $A(arrayLike){
	for(var i=0,ret=[];i<arrayLike.length;i++){
		ret.push(arrayLike[i]);
	}

	ret.each=function(f){for(var i=0;i<this.length;i++){ f(this[i],i,this);}};
	return ret;
}

function CreateTable(id,trinfo){
	this.tbl=typeof(id)=="string"?document.getElementById(id):id;
	var rows = trinfo.length;
	if (rows>0)
	{
		var cols = trinfo[0].length;
		if (rows!=null && cols!=null)
		{
			this.addrows(rows,cols,trinfo);
		}
	}
}

CreateTable.prototype={
	addrows:function(r,c,trinfo){				//添加n个tr
		var tr=[];
		for (var ij=0;ij<r;ij++ )
		{
			tr[tr.length]=this.add(c,trinfo[ij]);
		}
		return tr;
	},
	add:function(cols,tdinfo){						//添加1个tr   
		var self=this;
		var tr = self.tbl.insertRow(-1);
		tr.onmouseover = function (){tr_moveover(this);};
		tr.onmouseout = function(){tr_moveout(this);};
		var td1=tr.insertCell(-1);
		td1.align="center";
		td1.className="td1";
		var chkbox=document.createElement("INPUT");
		chkbox.type="checkbox";
		self.highlight.bind = function(){
			var __method = this,args = $A(arguments), object = args.shift();
			return function(){
				return __method.apply(object,args.concat($A(arguments)));
			}
		};
		chkbox.onclick=self.highlight.bind(self);
		chkbox.onfocus=function (){this.blur();};
		td1.appendChild(chkbox);
		
		for (var ir=0;ir<cols;ir++ )
		{
			var td=tr.insertCell(-1);
			td.className="td2";
			td.innerHTML=tdinfo[ir];
		}
		return tr;
	},
	del:function(){						//删除所选tr
	var self=this;
		$A(self.tbl.rows).each(function(tr){if (self.getChkBox(tr).checked) tr.parentNode.removeChild(tr)})
	},
	highlight:function(){				//设置tr的背景色
		var self=this;
		var evt=arguments[0] || window.event;
		var chkbox=evt.srcElement || evt.target;
		var tr=chkbox.parentNode.parentNode;
		chkbox.checked?self.setBgColor(tr):self.restoreBgColor(tr);
	},
	setBgColor:function(tr){
		tr.className="tr_select";
	},
	restoreBgColor:function(tr){
		tr.className="";
	},
	getChkBox:function(tr){					//从tr得到checkbox对象
		return tr.cells[0].firstChild;
	},
	up:function(topRowIndex,moveitem){		//上移所选tr
		topRowIndex = topRowIndex || 0;
		var self=this;
		var upOne=function(tr,setchecked){			//上移1个tr
			if   (tr.rowIndex>topRowIndex){
				self.swapTr(tr,self.tbl.rows[tr.rowIndex-1]);
				if (setchecked)self.getChkBox(tr).checked=true;
			}
		}
		var arr=$A(self.tbl.rows).reverse();
		if (arr.length>0 && self.getChkBox(arr[arr.length-1-topRowIndex]).checked){
			for(var i=arr.length-1;i>=0;i--){
				if (moveitem){
					if (arr[i]==moveitem){
						arr.pop();
					}else{
						break;
					}
				}else{
					if (self.getChkBox(arr[i]).checked==false){
						break;
					}else{
						arr.pop();
					}
				}
			}
		}
		arr.reverse().each(
			function(tr){
				if (moveitem){
					if (moveitem==tr){
						upOne(tr,false);
					}
				}else{
					if(self.getChkBox(tr).checked)upOne(tr,true);
				}
			}
		);
	},
	down:function(topRowIndex,moveitem){
		topRowIndex = topRowIndex || 0;
		var self=this;
		var downOne=function(tr,setchecked){
			if (tr.rowIndex<self.tbl.rows.length-1-topRowIndex){
				self.swapTr(tr,self.tbl.rows[tr.rowIndex+1]);
				if (setchecked)self.getChkBox(tr).checked=true;
			}
		}
		var arr=$A(self.tbl.rows);
		if (arr.length>0 && self.getChkBox(arr[arr.length-1-topRowIndex]).checked){
			for(var i=arr.length-1;i>=0;i--){
				if (moveitem){
					if (arr[i]==moveitem){
						arr.pop();
					}else{
						break;
					}
				}else{
					if (self.getChkBox(arr[i]).checked){
						arr.pop();
					}else{
						break;
					}
				}
			}
		}
		arr.reverse().each(
			function(tr){
				if (moveitem){
					if (moveitem==tr){
						downOne(tr,false);
					}
				}else{
					if(self.getChkBox(tr).checked)downOne(tr,true);
				}
			}
		);
		//arr.reverse().each(function(tr){if(self.getChkBox(tr).checked) downOne(tr)});
	},
	sort:function(){    //排序
		var self=this,order=arguments[0];
		var sortBy=function(a,b){
			if (typeof(order)=="number"){     //数字,则按数字指示的列排序
				return a.cells[order].innerHTML>=b.cells[order].innerHTML?1:-1;       //转化为数字类型比较大小
			}else if(typeof(order)=="function"){           //为程序,按   程序的返回结果排序
				return order(a,b);
			}else{
				return 1;
			}
		}
		var myrows = new Array();
		for (var i=0;i<self.tbl.rows.length;i++){
			
			myrows[(i-1)] = self.tbl.rows[i];
		}
		$A(myrows).sort(sortBy).each(function(x){
			var checkStatus=self.getChkBox(x).checked;
			self.tbl.firstChild.appendChild(x);
			if (checkStatus)self.getChkBox(x).checked=checkStatus;
		});
	},
	swapTr:function(tr1,tr2){				//交换tr1和tr2的位置
		var target=(tr1.rowIndex<tr2.rowIndex)?tr2.nextSibling:tr2;
		var tBody=tr1.parentNode;
		var tmpinput1 = tr1.getElementsByTagName('INPUT');
		var tmpvalue1 = Array();
		for (var item1 in tmpinput1)
		{
			if (tmpinput1[item1].checked == true)
			{
				tmpvalue1[item1] = true;
			}
		}

		var tmpinput2 = tr2.getElementsByTagName('INPUT');
		var tmpvalue2 = Array();
		for (var item2 in tmpinput2)
		{
			if (tmpinput2[item2].checked == true)
			{
				tmpvalue2[item2] = true;
			}
		}

		tBody.replaceChild(tr2,tr1);
		tBody.insertBefore(tr1,target);

		tmpinput1 = tr1.getElementsByTagName('INPUT');
		for (item1 in tmpinput1)
		{
			if (tmpvalue1[item1] == true)
			{
				tmpinput1[item1].checked = true;
			}
		}
		tmpinput2 = tr2.getElementsByTagName('INPUT');
		for (item2 in tmpinput2)
		{
			if (tmpvalue2[item2] == true)
			{
				tmpinput2[item2].checked = true;
			}
		}
	}
}






/* Simple AJAX Code-Kit (SACK) v1.6.1 */
/* ?005 Gregory Wild-Smith */
/* www.twilightuniverse.com */
/* Software licenced under a modified X11 licence,
   see documentation or authors website for more details */

function sack(file) {
	this.xmlhttp = null;

	this.resetData = function() {
		this.method = "POST";
  		this.queryStringSeparator = "?";
		this.argumentSeparator = "&";
		this.URLString = "";
		this.encodeURIString = true;
  		this.execute = false;
  		this.element = null;
		this.elementObj = null;
		this.requestFile = file;
		this.vars = new Array();
		this.responseStatus = new Array(2);
		this.renewData = false;
		this.needresetvar = false;
  	};

	this.resetFunctions = function() {
  		this.onLoading = function() { };
  		this.onLoaded = function() { };
  		this.onInteractive = function() { };
  		this.onCompletion = function() { };
  		this.onError = function() { };
		this.onFail = function() { };
	};

	this.reset = function() {
		this.resetFunctions();
		this.resetData();
	};

	this.createAJAX = function() {
		try {
			this.xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e1) {
			try {
				this.xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e2) {
				this.xmlhttp = null;
			}
		}

		if (! this.xmlhttp) {
			if (typeof XMLHttpRequest != "undefined") {
				this.xmlhttp = new XMLHttpRequest();
			} else {
				this.failed = true;
			}
		}
	};

	this.setVar = function(name, value){
		if (this.needresetvar==true){
			this.vars = new Array();
			this.needresetvar =false;
		}
		this.vars[this.vars.length] = Array(name,value, false);
	};

	this.encVar = function(name, value, returnvars) {
		if (true == returnvars) {
			return Array(encodeURIComponent(name), encodeURIComponent(value));
		} else {
			if (this.needresetvar==true){
				this.vars = new Array();
				this.needresetvar =false;
			}
			this.vars[this.vars.length] = Array( encodeURIComponent(name), encodeURIComponent(value), true);
		}
	}

	this.processURLString = function(string, encode) {
		encoded = encodeURIComponent(this.argumentSeparator);
		regexp = new RegExp(this.argumentSeparator + "|" + encoded);
		varArray = string.split(regexp);
		for (i = 0; i < varArray.length; i++){
			var urlVars = varArray[i].split("=");
			if (true == encode){
				this.encVar(urlVars[0], urlVars[1] );
			} else {
				this.setVar(urlVars[0], urlVars[1]);
			}
		}
	}

	this.createURLString = function(urlstring) {
		if (this.encodeURIString && this.URLString.length) {
			this.processURLString(this.URLString, true);
		}

		if (urlstring) {
			if (this.URLString.length) {
				this.URLString += this.argumentSeparator + urlstring;
			} else {
				this.URLString = urlstring;
			}
		}

		if (this.method == 'GET' && this.renewData==true){
			// prevents caching of URLString
			this.setVar("rndval", new Date().getTime());
		}

		var urlstringtemp = new Array();
		for (var key=0;key<this.vars.length;key++) {
			var thekey = this.vars[key][0];
			var thevalue = this.vars[key][1];
			if (false == this.vars[key][2] && true == this.encodeURIString) {
				var encoded = this.encVar(thekey,thevalue,true);
				thekey = encoded[0];
				thevalue = encoded[1];
			}
			urlstringtemp[urlstringtemp.length] = thekey + "=" + thevalue;
		}
		if (urlstring){
			this.URLString += this.argumentSeparator + urlstringtemp.join(this.argumentSeparator);
		} else {
			this.URLString += urlstringtemp.join(this.argumentSeparator);
		}
	}

	this.runResponse = function() {
		eval(this.response);
	}

	this.runAJAX = function(urlstring) {
		if (this.failed) {
			this.onFail();
		} else {
			this.createURLString(urlstring);
			if (this.element) {
				this.elementObj = document.getElementById(this.element);
			}
			if (this.xmlhttp) {
				var self = this;
				if (this.method == "GET") {
					totalurlstring = this.requestFile + (this.requestFile.indexOf('?')>=0 ? this.argumentSeparator : this.queryStringSeparator ) + this.URLString;
					this.xmlhttp.open(this.method, totalurlstring, true);
				} else {
					this.xmlhttp.open(this.method, this.requestFile, true);
					try {
						this.xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded")
					} catch (e) { }
				}

				this.xmlhttp.onreadystatechange = function() {
					switch (self.xmlhttp.readyState) {
						case 1:
							self.onLoading();
							break;
						case 2:
							self.onLoaded();
							break;
						case 3:
							self.onInteractive();
							break;
						case 4:
							self.response = self.xmlhttp.responseText;
							self.responseXML = self.xmlhttp.responseXML;
							self.responseStatus[0] = self.xmlhttp.status;
							self.responseStatus[1] = self.xmlhttp.statusText;

							if (self.execute) {
								self.runResponse();
							}

							if (self.elementObj) {
								elemNodeName = self.elementObj.nodeName;
								elemNodeName.toLowerCase();
								if (elemNodeName == "input"
								|| elemNodeName == "select"
								|| elemNodeName == "option"
								|| elemNodeName == "textarea") {
									self.elementObj.value = self.response;
								} else {
									self.elementObj.innerHTML = self.response;
								}
							}
							if (self.responseStatus[0] == "200") {
								self.onCompletion();
							} else {
								self.onError();
							}

							self.URLString = "";
							break;
					}
				};

				this.xmlhttp.send(this.URLString);
				this.needresetvar = true;
			}
		}
	};

	this.stop = function (){
		this.onError = function(){};
		this.xmlhttp.abort();
		if (this.onStop)
		{
			this.onStop();
		}
	}

	this.setform = function(formname){
		var obj;
		if(typeof formname =='object'){
			obj = formname;
		}else{
			obj = document.forms[formname];
			if (!obj){
				obj = document.getElementById(formname);
				if (!obj)return;
				if (obj.tagName!='form')obj = null;
			}
		}
		if (!obj)return;
		
		var myform = obj.elements;

		for (var i=0;i<myform.length;i++){
			if (myform[i].disabled)continue;
			if (myform[i].name){
				if (myform[i].type=='checkbox'){
					if (myform[i].checked) this.encVar(myform[i].name,myform[i].value);
				}else if (myform[i].type=='select'){
					if (myform[i].selected) this.encVar(myform[i].name,myform[i].value);
				}else{
					this.encVar(myform[i].name,myform[i].value);
				}
			}
		}
	}

	this.submit = function (theform,hander){
		if (!theform)return;
		this.reset();
		this.setform(theform);
		this.method = (theform.method||'POST').toUpperCase();
		this.requestFile = theform.action;
		this.onCompletion = function (){
			alert('提交成功！');
		}
		if (typeof hander == 'object'){
			this.onLoading = hander.onLoading || this.onLoading;
			this.onLoaded = hander.onLoaded || this.onLoaded;
			this.onInteractive = hander.onInteractive || this.onInteractive;
			this.onCompletion = hander.onCompletion || this.onCompletion;
			this.onError = hander.onError || this.onError;
			this.onFail = hander.onFail || this.onFail;
		}
		this.runAJAX();
		return false;
	}

	this.reset();
	this.createAJAX();
}

var ajax=new sack();




/*
---------------------------------------------------------- json2.js begin --------------------
    json2.js
    2007-11-06

    Public Domain

    No warranty expressed or implied. Use at your own risk.

    See http://www.JSON.org/js.html

    This file creates a global JSON object containing two methods:

        JSON.stringify(value, whitelist)
            value       any JavaScript value, usually an object or array.

            whitelist   an optional that determines how object values are
                        stringified.

            This method produces a JSON text from a JavaScript value.
            There are three possible ways to stringify an object, depending
            on the optional whitelist parameter.

            If an object has a toJSON method, then the toJSON() method will be
            called. The value returned from the toJSON method will be
            stringified.

            Otherwise, if the optional whitelist parameter is an array, then
            the elements of the array will be used to select members of the
            object for stringification.

            Otherwise, if there is no whitelist parameter, then all of the
            members of the object will be stringified.

            Values that do not have JSON representaions, such as undefined or
            functions, will not be serialized. Such values in objects will be
            dropped, in arrays will be replaced with null. JSON.stringify()
            returns undefined. Dates will be stringified as quoted ISO dates.

            Example:

            var text = JSON.stringify(['e', {pluribus: 'unum'}]);
            // text is '["e",{"pluribus":"unum"}]'

        JSON.parse(text, filter)
            This method parses a JSON text to produce an object or
            array. It can throw a SyntaxError exception.

            The optional filter parameter is a function that can filter and
            transform the results. It receives each of the keys and values, and
            its return value is used instead of the original value. If it
            returns what it received, then structure is not modified. If it
            returns undefined then the member is deleted.

            Example:

            // Parse the text. If a key contains the string 'date' then
            // convert the value to a date.

            myData = JSON.parse(text, function (key, value) {
                return key.indexOf('date') >= 0 ? new Date(value) : value;
            });

    This is a reference implementation. You are free to copy, modify, or
    redistribute.

    Use your own copy. It is extremely unwise to load third party
    code into your pages.
*/

/*jslint evil: true */
/*extern JSON */

if (!this.JSON) {

    JSON = function () {

        function f(n) {    // Format integers to have at least two digits.
            return n < 10 ? '0' + n : n;
        }

        Date.prototype.toJSON = function () {

// Eventually, this method will be based on the date.toISOString method.

            return this.getUTCFullYear()   + '-' +
                 f(this.getUTCMonth() + 1) + '-' +
                 f(this.getUTCDate())      + 'T' +
                 f(this.getUTCHours())     + ':' +
                 f(this.getUTCMinutes())   + ':' +
                 f(this.getUTCSeconds())   + 'Z';
        };


        var m = {    // table of character substitutions
            '\b': '\\b',
            '\t': '\\t',
            '\n': '\\n',
            '\f': '\\f',
            '\r': '\\r',
            '"' : '\\"',
            '\\': '\\\\'
        };

        function stringify(value, whitelist) {
            var a,          // The array holding the partial texts.
                i,          // The loop counter.
                k,          // The member key.
                l,          // Length.
                r = /["\\\x00-\x1f\x7f-\x9f]/g,
                v;          // The member value.

            switch (typeof value) {
            case 'string':

// If the string contains no control characters, no quote characters, and no
// backslash characters, then we can safely slap some quotes around it.
// Otherwise we must also replace the offending characters with safe sequences.

                return r.test(value) ?
                    '"' + value.replace(r, function (a) {
                        var c = m[a];
                        if (c) {
                            return c;
                        }
                        c = a.charCodeAt();
                        return '\\u00' + Math.floor(c / 16).toString(16) +
                                                   (c % 16).toString(16);
                    }) + '"' :
                    '"' + value + '"';

            case 'number':

// JSON numbers must be finite. Encode non-finite numbers as null.

                return isFinite(value) ? String(value) : 'null';

            case 'boolean':
            case 'null':
                return String(value);

            case 'object':

// Due to a specification blunder in ECMAScript,
// typeof null is 'object', so watch out for that case.

                if (!value) {
                    return 'null';
                }

// If the object has a toJSON method, call it, and stringify the result.

                if (typeof value.toJSON === 'function') {
                    return stringify(value.toJSON());
                }
                a = [];
                if (typeof value.length === 'number' &&
                        !(value.propertyIsEnumerable('length'))) {

// The object is an array. Stringify every element. Use null as a placeholder
// for non-JSON values.

                    l = value.length;
                    for (i = 0; i < l; i += 1) {
                        a.push(stringify(value[i], whitelist) || 'null');
                    }

// Join all of the elements together and wrap them in brackets.

                    return '[' + a.join(',') + ']';
                }
                if (whitelist) {

// If a whitelist (array of keys) is provided, use it to select the components
// of the object.

                    l = whitelist.length;
                    for (i = 0; i < l; i += 1) {
                        k = whitelist[i];
                        if (typeof k === 'string') {
                            v = stringify(value[k], whitelist);
                            if (v) {
                                a.push(stringify(k) + ':' + v);
                            }
                        }
                    }
                } else {

// Otherwise, iterate through all of the keys in the object.

                    for (k in value) {
                        if (typeof k === 'string') {
                            v = stringify(value[k], whitelist);
                            if (v) {
                                a.push(stringify(k) + ':' + v);
                            }
                        }
                    }
                }

// Join all of the member texts together and wrap them in braces.

                return '{' + a.join(',') + '}';
            }
        }

        return {
            stringify: stringify,
            parse: function (text, filter) {
                var j;

                function walk(k, v) {
                    var i, n;
                    if (v && typeof v === 'object') {
                        for (i in v) {
                            if (Object.prototype.hasOwnProperty.apply(v, [i])) {
                                n = walk(i, v[i]);
                                if (n !== undefined) {
                                    v[i] = n;
                                }
                            }
                        }
                    }
                    return filter(k, v);
                }


// Parsing happens in three stages. In the first stage, we run the text against
// regular expressions that look for non-JSON patterns. We are especially
// concerned with '()' and 'new' because they can cause invocation, and '='
// because it can cause mutation. But just to be safe, we want to reject all
// unexpected forms.

// We split the first stage into 4 regexp operations in order to work around
// crippling inefficiencies in IE's and Safari's regexp engines. First we
// replace all backslash pairs with '@' (a non-JSON character). Second, we
// replace all simple value tokens with ']' characters. Third, we delete all
// open brackets that follow a colon or comma or that begin the text. Finally,
// we look to see that the remaining characters are only whitespace or ']' or
// ',' or ':' or '{' or '}'. If that is so, then the text is safe for eval.

                if (/^[\],:{}\s]*$/.test(text.replace(/\\./g, '@').
replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(:?[eE][+\-]?\d+)?/g, ']').
replace(/(?:^|:|,)(?:\s*\[)+/g, ''))) {

// In the second stage we use the eval function to compile the text into a
// JavaScript structure. The '{' operator is subject to a syntactic ambiguity
// in JavaScript: it can begin a block or an object literal. We wrap the text
// in parens to eliminate the ambiguity.

                    text==''?'':j = eval('(' + text + ')');

// In the optional third stage, we recursively walk the new structure, passing
// each name/value pair to a filter function for possible transformation.

                    return typeof filter === 'function' ? walk('', j) : j;
                }

// If the text is not JSON parseable, then a SyntaxError is thrown.

				return [];
                throw new SyntaxError('parseJSON');
            }
        };
    }();
}

/*
---------------------------------------------------------- json2.js end --------------------
*/