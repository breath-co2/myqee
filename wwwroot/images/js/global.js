if (typeof(MyQEE)!='object')
{
	var MyQEE = {};
}
if (typeof(MyQEE.site_url)=='undefined')
{
	MyQEE.site_url = '/';
}

MyQEE.userAgent = navigator.userAgent.toLowerCase();
MyQEE.is_firefox = MyQEE.userAgent.indexOf('firefox')>=0?true:false;
MyQEE.is_opera = navigator.appName.indexOf('Opera')>=0?true:false;;
MyQEE.is_moz = (navigator.product == 'Gecko') && MyQEE.userAgent.substr(MyQEE.userAgent.indexOf('firefox') + 8, 3);
MyQEE.is_ie = navigator.appName=="Microsoft Internet Explorer"?true:false;
MyQEE.ie = (MyQEE.userAgent.indexOf('msie') != -1 && !MyQEE.is_opera) && MyQEE.userAgent.substr(MyQEE.userAgent.indexOf('msie') + 5, 3);

MyQEE.dom = (document.compatMode &&document.compatMode=="CSS1Compat")?document.documentElement:document.body;

MyQEE.$ = function(el){return document.getElementById(el);}


MyQEE.auto_ymPrompt_Run = true;

MyQEE._IMPORT_JSFILE = {};

MyQEE.$import = function(jsfile,onloadfun,defer){
	if (!MyQEE._IMPORT_JSFILE[jsfile])
	{
		var s = 'script';
		MyQEE._IMPORT_JSFILE[jsfile] = 1;
		var a=('<'+s+' src="'+jsfile+'"'+(defer?' defer="defer"':'')+(onloadfun?(is_ie?' onreadystatechange="if(this.readyState!=\'complete\'){return;}'+onloadfun+'"':' onload="'+onloadfun+'"'):'')+'></'+s+'>');
		document.write (a);
	}
}

MyQEE.frameFrame = window.self;		//子窗口
MyQEE.parentFrame = window.self;		//父窗口

MyQEE.alert = function(alertset,w,h,title,handler) {
	MyQEE._set_frame();
	if (typeof (alertset) != 'object'){
		alertset = {'message':(alertset||'')};
	}
	//alertset.message = alertset.message.replace(/\n/g,'<br />');
	alertset.width = alertset.width || w;
	alertset.height = alertset.height || h;
	alertset.title = alertset.title || title || '信息提示';
	alertset.handler = alertset.handler || handler;

	if (!MyQEE.parentFrame.ymPrompt){
		window.alert(alertset.message);
		if (alertset.handler){
			try{alertset.handler('ok')}catch(e){}
		}
	}else{
		if (alertset._type=='errorInfo'){
			MyQEE.parentFrame.ymPrompt.errorInfo(alertset);
		}else if(alertset._type=='succeedInfo'){
			MyQEE.parentFrame.ymPrompt.succeedInfo(alertset);
		}else if(alertset._type=='win'){
			alertset.allowSelect = alertset.allowSelect || true;		//默认允许
			alertset.allowRightMenu = alertset.allowRightMenu || true;	//默认允许
			MyQEE.parentFrame.ymPrompt.win(alertset);
		}else{
			MyQEE.parentFrame.ymPrompt.alert(alertset);
		}
	}
}

MyQEE.succeed = function(alertset,w,h,title,handler){
	if (typeof (alertset) != 'object'){
		alertset = {'message':(alertset||'')};
	}
	alertset.title = alertset.title || '操作成功';
	alertset._type = 'succeedInfo';
	MyQEE.alert(alertset,w,h,title,handler);
}
MyQEE.error = function(alertset,w,h,title,handler){
	if (typeof (alertset) != 'object'){
		alertset = {'message':(alertset||'')};
	}
	alertset.title = alertset.title || '错误提示';
	alertset._type = 'errorInfo';
	MyQEE.alert(alertset,w,h,title,handler);
}
MyQEE.win = function(alertset,w,h,title,handler){
	if (typeof (alertset) != 'object'){
		alertset = {'message':(alertset||'')};
	}
	alertset._type = 'win';
	MyQEE.alert(alertset,w,h,title,handler);
}

MyQEE.confirm = function (alertset,w,h,title,handler){
	MyQEE._set_frame();
	if (typeof (alertset) != 'object'){
		alertset = {'message':(alertset||'是否继续此操作？')};
	}
	//alertset.message = alertset.message.replace(/\n/g,'<br />');
	alertset.width = alertset.width || w;
	alertset.height = alertset.height || h;
	alertset.title = alertset.title || '请确认';
	alertset.handler = alertset.handler || handler;

	if (!parentFrame.ymPrompt){
		var myconform = window.confirm(alertset['message']);
		if (!alertset.handler)
		{
			return myconform;
		}else{
			var r = false;
			try{r = alertset.handler(myconform?'ok':'cancel');}catch(e){}
			return r;
		}
	}else{
		try{MyQEE.parentFrame.ymPrompt.close();}catch(e){}
		MyQEE.parentFrame.ymPrompt.confirmInfo(alertset,w,h,title,handler);
	}
}


window.closewin = function(type,autoclose){
	MyQEE._set_frame();
	try{MyQEE.parentFrame.ymPrompt.doHandler(type,autoclose);}catch(e){}
}



MyQEE._set_frame = function(){
	try {
		if( typeof(frameFrame.MyQEE.win)=='function'){
			MyQEE.parentFrame = window.parent;
		}
	}catch(e){}
	MyQEE.parentFrame.frameFrame = window.self;
}

MyQEE.href = function(newurl,target){
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

MyQEE.goback = function(go,goUrl){
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


/**
 * 输出Flash
 * @param id flash显示的容器ID，传入NULL时采用document.write输出
 * @param url flash url地址
 * @param w 宽度 可用百分比
 * @param h 高度 同上
 * @param p 是否不透明，0为透明，1表示不透明
 * @param r 右键菜单 0为显示菜单，1为屏蔽
 * @param v 传递falshvar参数
 * @return
 */
 MyQEE.flash = function(id,url,w,h,p,r,v){
	var o=$(id);
	p=(!p)?'Transparent':'Opaque';
	r=(!r)?true:false;
	v=(!v)?'':v;
	var tmphtml='<object filetype="swf" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="'+w+'" height="'+h+'"><param name="movie" value="'+url+'"><param name="AllowScriptAccess" value="never"><param name="wmode" value="'+p+'"><param name="menu" value="'+r+'"><param name="FlashVars" value="'+v+'"><embed src="'+url+'" AllowScriptAccess="never" width="'+w+'" height="'+h+'" wmode="'+p+'" FlashVars="'+v+'" menu="'+r+'" type="application/x-shockwave-flash"></embed></object>';
	if (o!=null){
		o.innerHTML = tmphtml;
	}else{
		document.write(tmphtml);
	}
}

MyQEE.tag = function(thisid,titlePrefix,mainPrefix,searchID,classname){
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
					el[i].className = classname||'now';
					if (mainobj)
					{
						mainobj.style.display = '';
					}
				}
			}
		}
	}
}


MyQEE.copytext = function(str,alertinfo){
	alertinfo=alertinfo||'复制成功！';
	if (is_ie){
		window.clipboardData.setData("Text",str);
	}else{
		var flashObj=$("flashCopyDiv");
		if (!flashObj){
			flashObj = document.createElement("div");
			flashObj.id='flashCopyDiv';
			document.body.appendChild(flashObj);
		}
		MyQEE.flash('flashCopyDiv',site_url+'images/swf/copytext.swf',0,0,null,null,'clipboard=' + encodeURIComponent(str));
	}
	MyQEE.alert(alertinfo);
	return false;
}




MyQEE.read_cookie = function (name){
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

MyQEE.create_cookie = function (name, value, days, path){
	var expires = "";
	if (days) {
		var date = new Date();
		date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
		expires = "; expires=" + date.toGMTString();
	};
	path = path || '/';
	document.cookie = name + "=" + encodeURIComponent(value) + expires + ";path=" + path;
}