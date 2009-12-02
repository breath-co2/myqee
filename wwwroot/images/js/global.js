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


MyQEE.ajax = function(file) {
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