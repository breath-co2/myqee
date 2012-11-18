if (typeof(MyQEE) != 'object')
{
	var MyQEE = {};
}
MyQEE.userAgent = navigator.userAgent.toLowerCase();

MyQEE.is_firefox = MyQEE.userAgent.indexOf('firefox')>=0?true:false;
MyQEE.is_opera = navigator.appName.indexOf('Opera')>=0?true:false;
MyQEE.is_safari = MyQEE.userAgent.indexOf('safari')>=0?true:false;
MyQEE.is_chrome = MyQEE.userAgent.indexOf('chrome')>=0?true:false;if(MyQEE.is_chrome)MyQEE.is_safari=false;
MyQEE.is_ie =navigator.appName=="Microsoft Internet Explorer"?true:false;
MyQEE.ie = (MyQEE.userAgent.indexOf('msie') != -1 && !MyQEE.is_opera) && MyQEE.userAgent.substr(MyQEE.userAgent.indexOf('msie') + 5, 3);

MyQEE.DOM = function(){
    return (document.compatMode && document.compatMode=="CSS1Compat")?document.documentElement:document.body;
}

MyQEE.$ = function(el){return document.getElementById(el);}
var $ = MyQEE.$;

function goto(url)
{
    document.location.href = url;
}

/**
 * 脚本调试，支持多参数输出，可在firebug中控制台查看
 * @return
 */
var trace = function(){
	if (typeof console == 'undefined'){
		return false;
	}
	if (arguments.length>1){
		console.group('Debug',123,456);
	}
	for(var i=0;i<arguments.length;i++){
		console.info(arguments[i])
	}
	if (arguments.length>1){
		console.groupEnd();
	}
}

if (typeof console == 'undefined')
{
	console = {
		'error' : function(){},
		'debug' : function(){},
		'info' : function(){},
		'log' : function(){},
		'warn' : function(){},
		'trace' : function(){},
		'group' : function(){},
		'groupEnd' : function(){}
	};
}

/**
 * 当如JS
 * @param js路径
 * @param 加载完后执行，可以是function也可以是字符串运行
 */
MyQEE.$import = function (jsfile,onLoadFun,onErrorFun)
{
	MyQEE._IMPORT_JSFILE = MyQEE._IMPORT_JSFILE || {};
	if (!MyQEE._IMPORT_JSFILE[jsfile])
	{
		MyQEE._IMPORT_JSFILE[jsfile] = 1;
		var obj = document.createElement('script');
		if (onLoadFun){
            if (typeof onLoadFun != 'function'){
                var onLoadFunStr = onLoadFun;
                onLoadFun = function(){
                    try{
                        eval(onLoadFunStr);
                    }catch(e){}
                }
            }
		    if (MyQEE.is_ie){
		        obj.onreadystatechange = function (){
		            if(this.readyState=='complete'||this.readyState=='loaded'){
		                onLoadFun();
		            }
		        }
		    }else{
		        obj.onload = onLoadFun;
		    }
		}
		if (onErrorFun){
		    if (typeof onErrorFun != 'function'){
		        var onErrorFunStr = onErrorFun;
		        onErrorFun = function(){
		            try{
		                eval(onErrorFunStr);
		            }catch(e){}
		        }
		    }
	        obj.onerror = onErrorFun;
		}
        obj.type = 'text/javascript';
        obj.src = jsfile;
		var s = document.getElementsByTagName('script')[0];
		s.parentNode.insertBefore(obj,s);
	}
}

/**
 * 执行初始化HTML脚本，在重新设置innerHTML后建议执行
 */
MyQEE.iniHtml = function(myobj){
	myobj = myobj||document;
	MyQEE._iniHtml_run = MyQEE._iniHtml_run||[];
	for (var _i=0;_i<MyQEE._iniHtml_run.length;_i++){
		if (typeof MyQEE._iniHtml_run[_i] == 'function'){
			try{
				MyQEE._iniHtml_run[_i](myobj);
			}catch(e){}
		}
	}
	
	//执行一次
	MyQEE._iniHtml_run_Once = MyQEE._iniHtml_run_Once||[];
	for (var _i=0;_i<MyQEE._iniHtml_run_Once.length;_i++){
		if (typeof MyQEE._iniHtml_run_Once[_i] == 'function'){
			try{
				MyQEE._iniHtml_run_Once[_i](myobj);
			}catch(e){}
		}
	}
	MyQEE._iniHtml_run_Once = [];
}

/**
 * 添加初始化HTML脚本的自定义方法
 */
MyQEE.iniHtmlAdd = function (fun){
	if (typeof fun == 'function'){
		MyQEE._iniHtml_run = MyQEE._iniHtml_run||[];
		MyQEE._iniHtml_run.push(fun);
	}
}
MyQEE.iniHtmlOnce = function (fun){
	if (typeof fun == 'function'){
		MyQEE._iniHtml_run_Once = MyQEE._iniHtml_run_Once||[];
		MyQEE._iniHtml_run_Once.push(fun);
	}
}

MyQEE.frameFrame = window.self;	//子窗口
MyQEE.parentFrame = window.self;	//父窗口

/**
 * 返回文件大小
 */
Number.prototype.bytes = String.prototype.bytes = function(text){
    text = text || ["B","KB","MB","GB","TB","PB"];
    var c = 0;
    var a = this-0;
    while (a>=1024) {
        c++;
        a = a/1024;
    }
    return (Math.round(a*10)/10)+''+text[c];
}

if (typeof ''.trim != 'function')
{
    /**
     * 清楚文字两端空格或指定内容
     */
    String.prototype.trim = function()
    {
        return this.replace(/^\s+/, '').replace(/\s+$/, '');
    }
}

/**
 * 将日期格式化输出，类似php的date方法，本接口默认偏移时区为+8
 * @param str 例如 Y-m-d H:i:s
 * @param utc 偏移时区，默认+8
 */
Date.prototype.format = function(str,utc){
    str = str || 'Y-m-d H:i:s';
    if (!(utc>=-12 && utc<=12)){
        utc = 8;
    }
    utc = utc*60*60*1000;
    var d = new Date(this.getTime());
    d.setUTCMilliseconds(utc); // 服务器时区偏移 毫秒

    var dateStr = {
        Y : d.getUTCFullYear(),
        m : d.getUTCMonth()+1,
        d : d.getUTCDate(),
        H : d.getUTCHours(),
        i : d.getUTCMinutes(),
        s : d.getUTCSeconds()
    }
    for(var key in dateStr){
        if (dateStr[key]<10)dateStr[key] = '0'+dateStr[key];
        var regexp = new RegExp(key,'g');
        str = str.replace(regexp,dateStr[key])
    }
    return str;
}

/**
 * 显示提示信息
 * @param string msg 信息提示内容
 * @param string 跳转到下一页url，留空则在本页直接提示，否则跳转到下一页后提示
 * @param int showtime 显示时间，单位秒，默认3
 */
MyQEE.Msg = function(msg,href,showtime){
	showtime = showtime||3;
	if (href){
		MyQEE.cookie.set('flash_message_',msg);
		document.location.href = href;
		return true;
	}
	if (typeof msg != 'string'){
		msg = MyQEE.cookie.get('flash_message_');
		if (msg)
			MyQEE.cookie.del('flash_message_');
	}
	if (msg){
		var runTime = 0;
		var tmpleft = 0;
		var width;
		var obj = MyQEE.$('myqee_show_msg_div_');
		
		if (MyQEE.is_ie && MyQEE.ie<7){
			//解决IE浮动问题
			var runtime = setInterval(function(){
				var rand = Math.ceil(Math.random()*10);
				obj.style.bottom = rand+'px';
				obj.style.bottom = '0px';
				},20);
		}
		
		var hidden = function (_run){
			runTime++;
			tmpleft = width * runTime/20;
			var tmpopacity = 80 - 80 * runTime/20;
			if (runTime<=20){
				obj.style.left = '-'+tmpleft+'px';
				obj.style.opacity = tmpopacity/100;
				obj.style.filter = 'alpha(opacity='+tmpopacity+')';
				setTimeout(function(){hidden(_run);},10);
			}else{
				runTime = 0;
				if (MyQEE.is_ie && MyQEE.ie<7){
					clearInterval(runtime);
				}
				MyQEE._msgRunHidden = null;
				if (typeof _run =='function'){
					try{
						_run();
					}catch(e){}
				}
			}
		}
		if (MyQEE._msgRunHidden){
			clearTimeout(MyQEE._msgRunHidden);
			width = obj.offsetWidth * 0.2;
			hidden(function(){
				MyQEE.Msg(msg,href,showtime);
			});
			return true;
		}
		if (!obj){
			obj = document.createElement('div');
			obj.style.cssText = 'z-index:111111;position:fixed;opacity:0.01;filter:alpha(opacity=1);_position:absolute;left:0;bottom:0px;padding:3px 12px;border:1px solid #ccc;overflow:hidden;background:#FF6600;width:27%;color:#fff;text-align:left;';
			obj.id = 'myqee_show_msg_div_';
			document.body.appendChild(obj);
		}
		obj.innerHTML = msg;
		width = obj.offsetWidth * 0.2;
		
		var tmpleft2 = width;
		var show = function (){
			runTime++;
			tmpleft2 = tmpleft2 * 7/10;
			var tmpopacity = 80 * runTime/20;
			if (runTime<=20){
				obj.style.left = '-'+tmpleft2+'px';
				obj.style.opacity = tmpopacity/100;
				obj.style.filter = 'alpha(opacity='+tmpopacity+')';
				setTimeout(show,10);
			}else{
				obj.style.left = '0px';
				runTime = 0;
				MyQEE._msgRunHidden = setTimeout(hidden,showtime*1000);
			}
		}
		setTimeout(show,500);
	}
}
MyQEE.iniHtmlOnce(function(){MyQEE.Msg()});

/**
 * 获取cookie
 */
MyQEE.cookie = {
	get : function (name){
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
	},
	set : function (name, value, days, path){
		var expires = "";
		if (days) {
			var date = new Date();
			date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
			expires = "; expires=" + date.toGMTString();
		};
		path = path || '/';
		document.cookie = name + "=" + encodeURIComponent(value) + expires + ";path=" + path;
	},
	del : function (name){
		var exp = new Date();   
		exp.setTime (exp.getTime() - 99999);
		document.cookie = name + "=''; expires=" + exp.toGMTString()+';path=/';
	}
}

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

	if (!window.MyQEE.parentFrame.ymPrompt){
		window.alert(alertset.message);
		if (alertset.handler){
			try{alertset.handler('ok')}catch(e){}
		}
	}else{
		//try{MyQEE.parentFrame.ymPrompt.close();}catch(e){}
		if (alertset._type=='errorInfo'){
			window.MyQEE.parentFrame.ymPrompt.errorInfo(alertset);
		}else if(alertset._type=='succeedInfo'){
			window.MyQEE.parentFrame.ymPrompt.succeedInfo(alertset);
		}else if(alertset._type=='win'){
			alertset.allowSelect = alertset.allowSelect || true;		//默认允许
			alertset.allowRightMenu = alertset.allowRightMenu || true;	//默认允许
			window.MyQEE.parentFrame.ymPrompt.win(alertset);
		}else{
			window.MyQEE.parentFrame.ymPrompt.alert(alertset);
		}
		if (window.MyQEE.parentFrame)window.MyQEE.parentFrame.MyQEE.iniHtml(MyQEE.$('ym-window'));
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
	alertset.middlevalign = false;
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

	if (!MyQEE.parentFrame.ymPrompt){
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
		MyQEE.parentFrame.MyQEE.iniHtml(MyQEE.$('ym-window'));
	}
}


MyQEE.closewin = function(type,autoclose){
	MyQEE._set_frame();
	try{MyQEE.parentFrame.ymPrompt.doHandler(type,autoclose);}catch(e){}
}


MyQEE.urlpath = '/';
MyQEE.ask_del = function(id,delurl,msg){
	msg=msg||'您确认要删除此数据？';
	MyQEE.confirm(msg,400,null,'删除确认',function(tp){if (tp=='ok') goUrl( MyQEE.urlpath + delurl +'/'+id,'hiddenFrame')});
}


MyQEE.show_loading = function(){
    var obj = MyQEE.$('_the_loading_div');
    if (!obj)
    {
        obj = document.createElement('div');
        obj.id = '_the_loading_div';
        obj.style.cssText = 'z-index:20000;position:fixed;_position:absolute;top:0;left:0;text-align:center;width:100%;color:#111;font-size:14px;top:expression((eval(document.compatMode&&document.compatMode=="CSS1Compat")?documentElement.scrollTop:document.body.scrollTop)+MyQEE._show_loading_ie6_top_);';
        obj.innerHTML = '<span class="loading_div">正在加载，请稍等...</span>';
        document.body.appendChild(obj);
    }
    var top = MyQEE.DOM().clientHeight/2-30;
    MyQEE._show_loading_ie6_top_ = top;
    obj.style.top = top+'px';
    obj.style.display = '';
}

MyQEE.hidden_loading = function(){
    var obj = MyQEE.$('_the_loading_div');
    if (obj)
    {
        obj.style.display = 'none';
    }
}

MyQEE._set_frame = function(){
	try {
		if( typeof(window.parent.MyQEE)=='object' )
		{
			MyQEE.parentFrame = window.parent;
		}
	}
	catch(e)
	{
	}
	MyQEE.parentFrame.MyQEE.frameFrame = window.self;
}


/**
 * 显示弹出提示信息
 * @param string msg 信息提示内容
 * @param string 跳转到下一页url，留空则在本页直接提示，否则跳转到下一页后提示
 * @param int time 显示时间，单位秒，默认5
 */
MyQEE.popMsg = function(msg,href,time){
    if (href){
        MyQEE.cookie.set('flash_pop_message_',msg);
        document.location.href = href;
        setTimeout('document.location.reload();',500);
        return true;
    }
    time = time||5;
    if (typeof msg != 'string'){
        msg = MyQEE.cookie.get('flash_pop_message_');
        if (msg)
            MyQEE.cookie.del('flash_pop_message_');
    }
    if (!msg)return false;
    
    if (MyQEE._pop_msg_timeout_show){
        clearTimeout(MyQEE._pop_msg_timeout_show);
        MyQEE._pop_msg_timeout_show = null;
    }
    if (MyQEE._pop_msg_timeout_hidden){
        clearTimeout(MyQEE._pop_msg_timeout_hidden);
        MyQEE._pop_msg_timeout_hidden = null;
    }
    var displayOpacity = function(id, n) {
        if(!MyQEE.$(id)) {
            return;
        }
        if(n >= 0) {
            n -= 10;
            MyQEE.$(id).style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=' + n + ')';
            MyQEE.$(id).style.opacity = n / 100;
            MyQEE._pop_msg_timeout_show = setTimeout(function(){displayOpacity(id, n);}, 50);
        } else {
            MyQEE.$(id).style.display = 'none';
            MyQEE.$(id).style.filter = '';
            MyQEE.$(id).style.opacity = '';
        }
    }
    var showmsgwin = function(b, e, a, t) {
        var step = (b - e) / 10;
        var msgWinObj = MyQEE.$('msgwin');
        var newp = (parseInt(msgWinObj.style.top) - step);
        if(newp > e) {
            msgWinObj.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=' + a + ')';
            msgWinObj.style.opacity = a / 100;
            msgWinObj.style.top = newp + 'px';
            MyQEE._pop_msg_timeout_hidden = setTimeout(function () {showmsgwin(b, e, a += 10, t)}, 10);
        } else {
            msgWinObj.style.filter = '';
            msgWinObj.style.opacity = '';
            MyQEE._pop_msg_timeout_show = setTimeout(function(){displayOpacity('msgwin', 100);}, t);
        }
    }
    var msgWinObj = MyQEE.$('msgwin');
    if (!msgWinObj){
        var msgDiv = document.createElement('div');
        msgDiv.style.cssText = 'position:fixed;_position:absolute;left:0;z-index:100000;top:0;_top:expression((eval(document.compatMode &&document.compatMode=="CSS1Compat") ?documentElement.scrollTop:document.body.scrollTop));';
        document.body.insertBefore(msgDiv,document.body.getElementsByTagName('div')[0]);
        msgDiv.innerHTML = '<div id="msgwin" style="position:absolute;display:none;"></div>';
        msgWinObj = MyQEE.$('msgwin');
    }
    msgWinObj.style.display = '';
    msgWinObj.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=0)';
    msgWinObj.style.opacity = 0;
    msgWinObj.innerHTML = '<div class="popupmenu_layer">'+msg+'</div>';
    MyQEE.iniHtml(msgWinObj);
    var pbegin = (document.documentElement.clientHeight / 2);
    var pend = (document.documentElement.clientHeight / 5);
    msgWinObj.style.left = ((document.documentElement.clientWidth - msgWinObj.clientWidth) / 2) + 'px';
    msgWinObj.style.top = pbegin + 'px';
    MyQEE._pop_msg_timeout_show = setTimeout(function () {showmsgwin(pbegin, pend, 0, time*1000)}, 10);
}
MyQEE.iniHtmlOnce(function(){MyQEE.popMsg()});

/**
 * 确认执行
 * @parem url 待执行的URL
 * @parem ask 内容
 */
MyQEE.ask_todo = function(url,ask)
{
    MyQEE.confirm(
        {
            'message':ask||'请确认',
            'title':'请确认',
            'handler':function(el){
                if (el!='ok')return true;
                
                MyQEE.run_ajax(url);
            }
        }
    );
}

MyQEE.run_ajax = function(url)
{
    if (!MyQEE._AJAX_RUNNING)MyQEE._AJAX_RUNNING = {};
    if (MyQEE._AJAX_RUNNING[url])
    {
        MyQEE.Msg('页面正在执行，请稍等...');
        return;
    }
    MyQEE._AJAX_RUNNING[url] = 1;
    MyQEE.show_loading();
    
    var ajax = new MyQEE.ajax(url);
    ajax.method = 'POST';
    ajax.onError = function()
    {
        MyQEE._AJAX_RUNNING[url] = null;
        MyQEE.hidden_loading();
        
        if (this.status == 404)
        {
            MyQEE.error('指定的页面不存在');
        }
        else
        {
            MyQEE.error('页面执行失败，请重试。');
        }
        return false;
    }

    ajax.onCompletion = function()
    {
        MyQEE._AJAX_RUNNING[url] = null;
        MyQEE.hidden_loading();
        
        if (!this.responseJSON)
        {
            MyQEE.error('数据异常，请重试或联系管理员。');
            return false;
        }

        if (this.responseJSON['code']==1)
        {
            MyQEE.Msg(this.responseJSON['msg'],document.location);
        }
        else if (this.responseJSON['code']<0)
        {
            MyQEE.error(this.responseJSON['msg']);
        }
        else{
            MyQEE.alert(this.responseJSON['msg']);
        }
    }

    ajax.runAJAX();    
}

MyQEE.tag = function(thisid,titlePrefix,mainPrefix,searchID,classname){
	var mainobj;
	if (searchID)
	{
		if (typeof searchID == 'object')
		{
			mainobj = searchID;
		}else{
			mainobj = MyQEE.$(searchID) || document.body;
		}
	}else{
		mainobj = document.body;
	}
	classname = classname || 'now';
	var el = mainobj.getElementsByTagName("li");
	for (var i=0;i<el.length;i++)
	{
		if (el[i].id)
		{
			if (el[i].id.substr(0,titlePrefix.length+1) == titlePrefix+'_')
			{
				var thisTitleId = el[i].id.substr(titlePrefix.length);
				var mainobj = MyQEE.$(mainPrefix + thisTitleId);
				if (el[i].id != thisid)
				{
					el[i].className = '';
					if (mainobj)
					{
						mainobj.style.display = 'none';
					}
				} else {
					el[i].className = classname;
					if (mainobj)
					{
						mainobj.style.display = '';
					}
				}
			}
		}
	}
}


MyQEE.isinFrame = function(){
    try{
        if(document.location!=parent.location && parent.window.name.indexOf('framemain_')){
            return true;
        }
    }catch(e){}
    return false;
}

/**
 * 执行初始化HTML脚本，在重新设置innerHTML后建议执行
 */
MyQEE.iniHtml = function(myobj){
    myobj = myobj||document;
    MyQEE._iniHtml_run = MyQEE._iniHtml_run||[];
    for (var _i=0;_i<MyQEE._iniHtml_run.length;_i++){
        if (typeof MyQEE._iniHtml_run[_i] == 'function'){
            try{
                MyQEE._iniHtml_run[_i](myobj);
            }catch(e){}
        }
    }

    //执行一次
    var tmpRun = MyQEE._iniHtml_run_Once||[];
    MyQEE._iniHtml_run_Once = [];
    for (var _i=0;_i<tmpRun.length;_i++){
        if (typeof tmpRun[_i] == 'function'){
            try{
                tmpRun[_i](myobj);
            }catch(e){}
        }
    }
}

/**
 * 获取指定对象的X,Y坐标点
 * 
 * @return array
 */
MyQEE.position = function(obj) {
    var r = new Array();
    r['x'] = obj.offsetLeft;
    r['y'] = obj.offsetTop;
    while(obj = obj.offsetParent) {
        r['x'] += obj.offsetLeft;
        r['y'] += obj.offsetTop;
    }
    return r;
}

/**
 * AJAX
 */
MyQEE.ajax = function(file) {
    /**
     * 顶层域名后缀数
     * 例如www.mydomain.com为com则为1
     * www.mydomain.com.cn为com.cn则为2
     */
    this.domain_suffix_dot_num = 1;
    
    /**
     * 跨子域代理HTML页面路径
     */
    this.sub_domain_proxy_file = 'ajax_proxy.html';
    
    /**
     * AJAX请求对象
     */
	this.xmlhttp = null;

	this.resetData = function() {
		this.method = "POST";
  		this.queryStringSeparator = "?";
		this.argumentSeparator = "&";
		this.URLString = "";
		this.streaming = false;        //是否支持ajax Streaming模式（开启的话，IE将会采取特殊处理）
		this.encodeURIString = true;
  		this.execute = false;
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
        if (this.xmlhttp){
            return true;
        }
		if (this.requestFile){
		    //根据请求的URL判断是否采用跨域方案
			if (this.requestFile.substr(0,7).toLowerCase()=='http://'||this.requestFile.substr(0,8).toLowerCase=='https://'){
				var host = this.requestFile.split('/')[2];
				if (host!=document.location.host){
				    var domainArr = document.location.host.split('.');
				    var requestFileDomainArr = host.split('.');
				    var topdomain = '';
                    var is_same_domain = false;
				    for(var i=0;i<=this.domain_suffix_dot_num;i++){
				        if (domainArr[domainArr.length-i-1] == requestFileDomainArr[requestFileDomainArr.length-i-1]){
				            topdomain = domainArr[domainArr.length-i-1]+'.'+topdomain;
                            is_same_domain = true;
				        }else{
				            is_same_domain = false;
				            break;
				        }
				    }
				    if (is_same_domain){
				        this.top_domain = topdomain.substr(0,topdomain.length-1);
				        // 子域
                        this.createIframeAjax();
				    }else{
				        this.createFlashAjax();
				    }
				    return true;
				}
			}
		}
		
		if (this.streaming && MyQEE.is_ie && MyQEE.ie<9)
        {
            //开启ajax streaming模式，IE下特殊处理
            this.createIEStreamingAjax();
	        return true;
        }
		
		try {
			this.xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e1) {
			try {
				this.xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e2) {
			}
		}

		if (! this.xmlhttp) {
			if (typeof XMLHttpRequest != "undefined") {
				this.xmlhttp = new XMLHttpRequest();
			} else {
				this.createFlashAjax();
			}
		}
	};
	
	/**
	 * 封装iframe+domain方式加载ajax，支持子域跨域请求
	 */
	this.createIframeAjax = function(){
        var host = this.requestFile.split('/')[2];
	    var subIframeSupperUrl = this.requestFile.split('/')[0]+'//'+this.requestFile.split('/')[2]+'/'+this.sub_domain_proxy_file;

        if (!this.iframeobj){
            var obj = MyQEE.$('_ajax_iframediv_'+host.replace(/\./g,'_'));
            if (!obj){
                obj = document.createElement('IFRAME');
                obj.id='_ajax_iframediv_'+host.replace(/\./g,'_');
                obj.style.cssText = 'display:none';
                obj.src = subIframeSupperUrl;
                obj.is_loading = false;     //对象是否正在请求数据
                obj.pageisloaded = false;   //代理页是否加载完毕
                //代理页面加载完毕后执行
                obj.onload = obj.onreadystatechange = function(){
                    if (this.readyState!='interactive'){
                        obj.pageisloaded = true;
                    }
                }
                //防止IE在没有加载完全后出错
                document.body.insertBefore(obj,MyQEE.$('header_div'));
            }
            this.iframeobj = obj;
        }
        var time = new Date().getTime()+'_'+ Math.round(Math.random()*10000);
        if (this.iframeobj.is_loading==true){
            //如果正在使用中，则重新构建一个新的对象
            var obj = document.createElement('IFRAME');
            obj.style.cssText = 'display:none';
            obj.id='_ajax_iframediv_iframe_'+host.replace(/\./g,'_')+'_'+time;
            obj.src = subIframeSupperUrl;
            obj.is_loading = false;
            obj.pageisloaded = false;
            document.body.insertBefore(obj,MyQEE.$('header_div'));
            obj.onload = obj.onreadystatechange = function(){
                if (this.readyState!='interactive'){
                    obj.pageisloaded = true;
                }
            }
            this.iframeobj = obj;
        }

        var self = this;
        this.iframeobjajax = null;
        this.xmlhttp = new function(){
            var header = [];
            var method = 'GET';
            var url = null;
            var openb = null;
            var runnum = 0;
            this.open = function(m, u,b){
                method = m||self.method;
                url = u||self.requestFile;
                openb = b;
            };
            this.setRequestHeader = function(k,v){
                header[k] = v;
            }
            this.send = function(urlstring){
                self.iframeobj.is_loading = true;
                var show_error = function(){
                    //错误
                    self.iframeobj.is_loading = false;
                    self.xmlhttp.readyState = 4;
                    self.xmlhttp.responseText = '';
                    self.xmlhttp.status = '500';
                    self.xmlhttp.onreadystatechange();
                }
                
                if (self.iframeobj.pageisloaded) {
                    try{
                        var xmlhttp = self.iframeobj.contentWindow.xmlhttp();
                    }catch(e){
                        // 代理页加载失败
                        return show_error();
                    }
                    if (!xmlhttp){
                        return show_error();
                    }
                    xmlhttp.open(method, url,openb);
                    for(var i in header){
                        try {
                            xmlhttp.setRequestHeader(i,header[i]);
                        } catch (e) { }
                    }
                    xmlhttp.onreadystatechange = function(){
                        if (xmlhttp.readyState==4){
                            self.xmlhttp.status = xmlhttp.status;
                            self.xmlhttp.responseText = xmlhttp.responseText;
                            self.iframeobj.is_loading = false;
                        }
                        self.xmlhttp.readyState = xmlhttp.readyState;
                        self.xmlhttp.onreadystatechange();
                    };
                    xmlhttp.send(urlstring);
                    runnum = 0;
                }else{
                    if (runnum<300){
                        setTimeout(function(){self.xmlhttp.send(urlstring);},30);
                    }else{
                        show_error();
                    }
                }
                runnum++;
            }
        };
	}
	
	/**
	 * IE下用frame来实现ajax streaming模式
	 */
	this.createIEStreamingAjax = function()
	{
	    // 只有IE
	    if (!MyQEE.is_ie)return false;
	    var self = this;
	    // 构造一个虚拟的xmlhttp对象
	    this.xmlhttp = {
	        responseXML : null,
	        responseText : '',
	        responseType : null,
	        response : '',
	        status : 0,
	        readyState : 0,
	        method : 'POST',
	        open : function(method,url){
	            this.method = method;
	            var newurl = [];
	            if ( url.indexOf('#')!=-1 )
                {
	                newurl = url.split('#');
                }
	            else
                {
	                newurl = [url];
                }
	            if ( newurl[0].indexOf('?')==-1 )
	            {
	                newurl[0] += '?_ajax=true';
	            }
	            else
	            {
	                newurl[0] += '&_ajax=true';
	            }
	            url = newurl.join('#');
	            this.url = url;
	            this.ifrDiv.innerHTML = '<iframe id="myqee_ajax_iframe" name="myqee_ajax_iframe"></iframe><form id="myqee_ajax_fram" target="myqee_ajax_iframe" action="'+url+'" method="POST"></form>';
	            var objframe = this.obj.getElementById('myqee_ajax_iframe');
                var getdata = function(){
                    if (objframe.readyState=='loadinng')
                    {
                        if (self.xmlhttp.readyState==0)
                        {
                            self.xmlhttp.readyState = 1;
                            self.xmlhttp.onreadystatechange();
                        }
                    }
                    else if (objframe.readyState=='interactive')
                    {
                        if (self.xmlhttp.readyState==0)
                        {
                            self.xmlhttp.readyState = 1;
                            self.xmlhttp.onreadystatechange();
                        }
                        if (self.xmlhttp.readyState==1)
                        {
                            self.xmlhttp.readyState = 2;
                            self.xmlhttp.onreadystatechange();
                        }
                        if (self.xmlhttp.readyState==2)
                        {
                            self.xmlhttp.readyState = 3;
                        }
                        if (self.xmlhttp.response!=objframe.contentWindow.document.body.innerHTML)
                        {
                            self.xmlhttp.response = self.xmlhttp.responseText = objframe.contentWindow.document.body.innerHTML;
                            self.xmlhttp.onreadystatechange();
                        }
                    }
                    else if (objframe.readyState=='complete')
                    {
                        clearInterval(runtime);
                        runtime = null;
                        if (self.xmlhttp.response!=objframe.contentWindow.document.body.innerHTML)
                        {
                            self.xmlhttp.response = self.xmlhttp.responseText = objframe.contentWindow.document.body.innerHTML;
                            self.xmlhttp.onreadystatechange();
                        }
                        self.xmlhttp.readyState = 4;
                        self.xmlhttp.status = 200;
                        self.xmlhttp.onreadystatechange();
                    }
                };
                var runtime = setInterval(getdata,10);
	        },
	        send : function(data){
	            if (this.method=='GET')
                {
	                //GET方式最简单了
	                this.obj.getElementById('myqee_ajax_iframe').src = this.url;
                }
	            else
                {
	                var newdata = data.split('&');
	                var html = '';
	                for (var i=0;i<newdata.length;i++)
                    {
	                    var index = newdata[i].indexOf('=');
	                    var k = decodeURIComponent(newdata[i].substr(0,index));
	                    var v = decodeURIComponent(newdata[i].substr(index+1));
	                    if (typeof v =='undefined')v='';
	                    html += '<textarea name="'+k.replace(/"/g,'&quot;')+'">'+v.replace(/</g,'&lt;')+'</textarea>'+"\n";
                    }
	                this.obj.getElementById('myqee_ajax_fram').innerHTML = html;
	                this.obj.getElementById('myqee_ajax_fram').submit();
	                //构造出一个表单提交
                }
	        },
	        obj : new ActiveXObject("htmlfile")
	    };
	    this.xmlhttp.obj.open();
	    this.xmlhttp.obj.write("<html>");
	    this.xmlhttp.obj.write("</html>");
	    this.xmlhttp.obj.close();
	    this.xmlhttp.ifrDiv = this.xmlhttp.obj.createElement("div");
	    this.xmlhttp.obj.appendChild(this.xmlhttp.ifrDiv);
	}
	
	/**
	 * 封装flash方式加载ajax，支持全局跨域
	 */
	this.createFlashAjax = function(){
		if (!this.flashobj){
			var obj = MyQEE.$('_ajax_fdiv');
			if (!obj){
				obj = document.createElement('DIV');
				obj.id='_ajax_fdiv';
				//Window的firefox里必须在可见区域，且不容许隐藏，很变态，2010-6-11 by jonwang
				obj.style.cssText = 'position:fixed;_position:absolute;top:100px;left:0px;opacity:0.01;';
				//防止IE在没有加载完全后出错
				document.body.insertBefore(obj,MyQEE.$('header_div'));
			}
			if (!MyQEE.get_flash('_ajax_fdiv_flash')){
				MyQEE.flash('_ajax_fdiv',MyQEE.Url.Statics+'/swf/ajaxcdr.swf',1,1,null,null,null,{'allowscriptaccess':'always','swliveconnect':'true','bgcolor':'#ffffff'});
			}
			this.flashobj = MyQEE.get_flash('_ajax_fdiv_flash');
		}
        var time = new Date().getTime()+'_'+ Math.round(Math.random()*10000);
		if (this.flashobj.is_loading==true){
			obj = document.createElement('DIV');
			obj.id='_ajax_fdiv_'+time;
			obj.style.cssText = 'position:fixed;_position:absolute;top:100px;left:0px;opacity:0.01;';
			document.body.insertBefore(obj,MyQEE.$('header_div'));
			MyQEE.flash('_ajax_fdiv_'+time,MyQEE.Url.Statics+'/swf/ajaxcdr.swf',1,1,null,null,null,{'allowscriptaccess':'always','swliveconnect':'true'});
			this.flashobj = MyQEE.get_flash('_ajax_fdiv_'+time+'_flash');
		}
		var self = this;
		if (typeof MyQEE._flashAjaxCallback != 'object'){
			MyQEE._flashAjaxCallback = [];
		}
		this.xmlhttp = new function(){
			var method = null;
			var url = null;
			var randid = time;
			this.open = function(m, u){
				method = m||self.method;
				url = u||self.requestFile;
			};
			this.setRequestHeader = function(k,v){
			    //TODO 暂不支持
			}
			this.send = function(urlstring){
				self.flashobj.is_loading = true;
				self.xmlhttp.readyState = 1;
				self.xmlhttp.onreadystatechange();
				var tryNum = 0;
				var loadhttp = function(){
					setTimeout(function(){
						tryNum++;
						if (self.flashobj.XmlHttp){
							self.flashobj.XmlHttp(url , 'MyQEE._flashAjaxCallback["'+randid+'"]' , method , urlstring, 'application/x-www-form-urlencoded');
						}else{
							//9秒钟（300次尝试获取数据）
							if (tryNum<300){
								loadhttp();
							}else{
								//错误
								self.flashobj.is_loading = false;
								self.xmlhttp.responseText = '';
								self.xmlhttp.readyState = 2;
								self.xmlhttp.onreadystatechange();
								self.xmlhttp.readyState = 3;
								self.xmlhttp.onreadystatechange();
								self.xmlhttp.readyState = 4;
								self.xmlhttp.status = '500';
								self.xmlhttp.responseText = '';
								self.xmlhttp.onreadystatechange();
								delete MyQEE._flashAjaxCallback[time];
							}
						}
					},30);
				}
				loadhttp();
			};
		};
		MyQEE._flashAjaxCallback[time] = function(){
            self.xmlhttp.readyState = 2;
            self.xmlhttp.onreadystatechange();
            self.xmlhttp.readyState = 3;
            self.xmlhttp.onreadystatechange();
			self.flashobj.is_loading = false;
			self.xmlhttp.responseText = self.flashobj.GetVariable("retText");
			self.xmlhttp.status = '200';
			self.xmlhttp.readyState = 4;
			self.xmlhttp.onreadystatechange();
			delete MyQEE._flashAjaxCallback[time];
		}
	};
	
	this.setVar = function(name, value){
		if (this.needresetvar==true){
			this.vars = new Array();
			this.needresetvar =false;
		}
		this.vars.push(Array(name,value, false));
	};

	this.encVar = function(name, value, returnvars) {
		if (true == returnvars) {
			return Array(encodeURIComponent(name), encodeURIComponent(value));
		} else {
			if (this.needresetvar==true){
				this.vars = new Array();
				this.needresetvar =false;
			}
			this.vars.push(Array( encodeURIComponent(name), encodeURIComponent(value), true));
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
			this.createAJAX();
			
			if (this.xmlhttp) {
				var self = this;
				if (this.method == "GET") {
					if (this.URLString){
						totalurlstring = this.requestFile + (this.requestFile.indexOf('?')>=0 ? this.argumentSeparator : this.queryStringSeparator ) + this.URLString;
					}else{
						totalurlstring = this.requestFile;
					}
					this.xmlhttp.open(this.method, totalurlstring, true);
				} else {
					this.xmlhttp.open(this.method, this.requestFile, true);
					try {
						this.xmlhttp.setRequestHeader("Content-Type", 'application/x-www-form-urlencoded')
					} catch (e) { }
				}
				
				try {
					this.xmlhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
				} catch (e) { }

				this.xmlhttp.onreadystatechange = function() {
					switch (self.xmlhttp.readyState) {
						case 1:
							self.onLoading();
							break;
						case 2:
							self.onLoaded();
							break;
						case 3:
						    try
						    {
	                            self.response = self.xmlhttp.responseText;
	                            self.responseJSON = false;
	                            var tmpstr = self.response.trim().substr(0,1);
	                            if (tmpstr=='{'||tmpstr=='[')
	                            {
	                                try{
	                                    self.responseJSON = eval('('+self.response+')');
	                                }catch(e){
	                                    self.responseJSON = false;
	                                }
	                            }
	                            tmpstr = null;
	                            self.responseXML = self.xmlhttp.responseXML;
	                            self.responseStatus[0] = self.xmlhttp.status;
	                            self.responseStatus[1] = self.xmlhttp.statusText;
						    }
						    catch(e){}

                            self.onInteractive();
                            break;
						case 4:

                            self.response = self.xmlhttp.responseText;
                            self.responseJSON = false;
                            var tmpstr = self.response.trim().substr(0,1);
                            if (tmpstr=='{'||tmpstr=='[')
                            {
                                try{
                                    self.responseJSON = eval('('+self.response+')');
                                }catch(e){
                                    self.responseJSON = false;
                                }
                            }
                            tmpstr = null;
                            self.responseXML = self.xmlhttp.responseXML;
                            self.responseStatus[0] = self.xmlhttp.status;
                            self.responseStatus[1] = self.xmlhttp.statusText;
                            
							if (self.execute)
							{
								self.runResponse();
							}
							if (self.responseStatus[0] == "200") {
								self.onCompletion();
							} else {
								self.onError();
							}

							self.URLString = "";
							
							//销毁对象防止内存溢出
							self.xmlhttp = null;
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

	/**
	 * 将指定表单的元素设置到COOKIE里
	 * 注意：不支持带文件上传的表单
	 */
	this.setform = function(formname){
		var obj;
		if(typeof formname =='object'){
			obj = formname;
		}else{
			obj = document.forms[formname];
			if (!obj){
				obj = document.getElementById(formname);
				if (!obj)return;
				if (obj.tagName!='FORM')obj = null;
			}
		}
		if (!obj)return;
		
		var myform = obj.elements;

		var tt=0
		for (var i=0;i<myform.length;i++){
			if (myform[i].disabled)continue;
			if (myform[i].name){
				if (myform[i].type=='checkbox' || myform[i].type=='radio'){
					if (myform[i].checked) this.setVar(myform[i].name,myform[i].value);
				}else if (myform[i].type.substr(0,6)=='select'){
					for(var k=0;k<myform[i].options.length;k++){
						if (myform[i].options[k].selected){
							this.setVar(myform[i].name,myform[i].options[k].value);
						}
					}
				}else{
					this.setVar(myform[i].name,myform[i].value);
				}
			}
		}
	}

	/**
	 * 提交表单
	 */
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
}


MyQEE.suggest = function (obj){
    // 当前值
    this.value = '';
    // 请求的URL
    this.url = '';
    // 关键词query的字符串
    this.qstr = 'keyword';
    // AJAX请求模式
    this.method = 'GET';
    // 存返回的数据
    this.result = [];
    // 对象数据
    this.options = [];
    // 当按键弹起时登时执行请求等待时间，若已经传options，则可以设为0，否则推荐300
    this.wait_time = 0;
    // 矫正left
    this.correction_left = 0;
    // 矫正top
    this.correction_top = 0;
    // 矫正宽度
    this.correction_width = 0;
    // 矫正高度
    this.correction_height = 0;
    
    var self = this;
    obj.setAttribute('autocomplete',"off");
    
    obj.onkeydown = function(e){
        e = e||event;
        if (e.keyCode==40||e.keyCode==38){
            if (self.downup_run){
                clearInterval(self.downup_run);
                self.downup_run = null;
            }
            self.downup_run = setTimeout(function(){
                self.downup_run = setInterval(
                    function(){
                        self.updown(e.keyCode==40?'down':'up');
                    }
                    ,40
                );
            },800);
            return self.updown(e.keyCode==40?'down':'up');
        }else if(e.keyCode==13){
            // 回车
            var obj_div = MyQEE.$('_suggest_div');
            if (!obj_div)return true;
            if (obj_div.style.display=='none')return true;
            var lis = obj_div.getElementsByTagName('TR');
            if (lis.length==0)return true;
            obj_div.style.display='none';
            for(var i=0;i<lis.length;i++){
                if (lis[i].ishighlight == true){
                    lis[i].onmousedown();
                }
            }
            return false;
        }
        else if(e.keyCode==27)
        {
            //ESC
            var obj_div = MyQEE.$('_suggest_div');
            if (!obj_div)return true;
            obj_div.style.display='none';
            return false;
        }
    }
    obj.onkeyup = function(e){
        e = e||event;
        if (e.keyCode==40||e.keyCode==38){
            if (self.downup_run){
                clearInterval(self.downup_run);
                self.downup_run = null;
            }
            return true;
        }
        else if(e.keyCode==13||e.keyCode==27)
        {
            obj.blur();
            return false;
        }else{
            if ( self.options )
            {
                self.value = obj.value;
                self.showList(obj.value);
                return true;
            }
            if (obj.value==self.value){
                return true;
            }
            if (self.timeout_run){
                clearTimeout(self.timeout_run);
            }
            if (obj.value==''){
                self.value = '';
                return obj.onblur();
            }
            var key = obj.value;
            if (key.length>1){
                var leftkey = key.substr(0,key.length-1);
                if (typeof self.result[leftkey] != 'undefined'){
                    if (self.result[leftkey].length==0){
                        self.result[key] = [];
                        return obj.onblur();
                    }
                }
            }
            if (typeof self.result[key] != 'undefined'){
                self.value = key;
                self.showList(key);
                return true;
            }
            self.timeout_run = setTimeout(function(){
                self.timeout_run = null;
                self.value = key;

                if(self.url)
                {
                    var ajax = new MyQEE.ajax(self.url);
                    ajax.method = self.method;
                    ajax.setVar(self.qstr,key);
                    ajax.onCompletion = function(){
                        if (this.response!='' && this.response!='undefined'){
                            self.result[key] = self.prease_ajax_request(this.response);
                        }else{
                            self.result[key] = [];
                            trace(self.result[key].length)
                        }
                        self.showList(key);
                        ajax=null;
                    }
                    ajax.onError = function(){
                        ajax=null;
                        trace(key);
                    }
                    ajax.runAJAX();
                }
            },self.wait_time);
        }
    }
    obj.onfocus = function(){
        this.oldvalue = this.value;
        self.showList(self.value);
        if (MyQEE._suggest_blur)clearTimeout(MyQEE._suggest_blur);
    }
    obj.onblur = function(){
        var obj_div = MyQEE.$('_suggest_div');
        if (obj_div){
            obj_div.style.display='none';
        }
        MyQEE._suggest_blur = setTimeout(function(){
            var showobj = obj.previousSibling;
            if (showobj && showobj.getAttribute('_is_inputselect_show')=='true')
            {
                showobj.value = self.options[obj.value]||obj.value;
                showobj.style.display = '';
                obj.style.display = 'none';
            }
        },100);
    }
    /**
     * 解析ajax返回的内容
     */
    this.prease_ajax_requeset = function(response)
    {
        return response.split("\n");
    }
    this.updown = function(type){
        var obj_div = MyQEE.$('_suggest_div');
        if (!obj_div)return true;
        if (!self.options && self.value==''){
            obj_div.style.display = 'none';
            return false;
        }
        if (obj_div.style.display=='none'){
            self.showList(self.value);
            return false;
        }
        var lis = obj_div.getElementsByTagName('TR');
        if (lis.length==0)return true;
        var hobj = false;
        for(var i=0;i<lis.length;i++){
            if (lis[i].ishighlight){
                hobj = true;
            }
            if (hobj){
                if (type=='up'){
                    if (i==0){
                        obj.onblur();
                    }else{
                        self.highlight(lis[i-1]);
                    }
                    return false;
                }else{
                    // down
                    if (i<lis.length-1){
                        self.highlight(lis[i+1]);
                    }
                    return false;
                }
            }
        }
        if (hobj==false){
            if (type=='up'){
                self.highlight(lis[lis.length-1]);
            }else{
                self.highlight(lis[0]);
            }
        }
        return true;
    }

    /**
     * 数据请求后操作
     */
    this.showList = function(key){
        if (key!=self.value){
            // 当前input里的关键词已换成其它的
            return true;
        }
//        if (typeof self.result[key] == 'undefined'){
//            return true;
//        }
        var result = self.options;
        if (self.value)
        {
            result = [];
            for (var i in self.options)
            {
                if (i.indexOf(self.value)!=-1||self.options[i].indexOf(self.value)!=-1)
                {
                    result[i] = self.options[i];
                }
            }
        }
        
        var obj_div = MyQEE.$('_suggest_div');
        if (!obj_div){
            obj_div = document.createElement('div');
            obj_div.id = '_suggest_div';
            document.body.insertBefore(obj_div,document.body.getElementsByTagName('*')[0]);
            obj_div.className = 'mainDiv';
            obj_div.style.height='0px';
        }
        obj_div.style.display='';
        var pos = MyQEE.position(obj);
        // 显示列表
        var html ='<div style="position:absolute;z-index:9999999;left:'+(pos['x']+self.correction_left)+'px;top:'+(self.correction_top+obj.clientHeight+pos['y'])+'px;min-width:'+(obj.clientWidth+self.correction_width)+'px;-moz-box-shadow: 5px 5px 5px rgba(0,0,0,0.3);-webkit-box-shadow: 5px 5px 5px rgba(0,0,0,0.3);box-shadow: 5px 5px 5px rgba(0,0,0,0.3);"><div style="background:#fff;border:1px solid #808080;"><div style="max-height:300px;overflow:auto;overflow-x:hidden;" id="_suggest_div_scroll_div"><table border="0" cellspacing="0" cellpadding="0" style="width:100%;">';
        for(var i in result){
            if (typeof result[i] =='undefined'){
                break;
            }
            html += '<tr style="white-space:nowrap;cursor:default;line-height:1.4em;height:1.4em;overflow:hidden;" value="'+i.replace(/"/g,'&quot;')+'"><td>'+self.prease_td(i,result[i])+'</td><td width="20"></td></tr>';
        }
        html+='</table></div></div></div>';
        obj_div.innerHTML = html;
        var lis = obj_div.getElementsByTagName('TR');
        if (lis.length==0){
            obj_div.style.display='none';
        }
        var found = false;
        for(var i=0;i<lis.length;i++){
            lis[i].onmouseover = function(){
                self.highlight(this,true);
            }
            lis[i].onmousedown = function(){
                obj.value = this.getAttribute('value');
                if (obj.value!=obj.oldvalue && typeof obj.onchange == 'function')
                {
                    obj.onchange();
                }
            }
            if ( lis[i].getAttribute('value')==obj.value )
            {
                found = true;
                lis[i].onmouseover();

                var objdiv2 = MyQEE.$('_suggest_div_scroll_div');
                if (lis[i].offsetTop>objdiv2.clientHeight-100)
                {
                    objdiv2.scrollTop = lis[i].offsetTop-objdiv2.clientHeight+100;
                }
            }
        }
        if (!found)
        {
            lis[0].onmouseover();
        }
    }
    this.highlight = function (liobj,no_scroll){
        var obj_div = MyQEE.$('_suggest_div');
        if (!obj_div)return false;
        var lis = obj_div.getElementsByTagName('TR');
        for(var i=0;i<lis.length;i++){
            lis[i].style.background = '';
            lis[i].style.color = '';
            lis[i].ishighlight = false;
            lis[i].getElementsByTagName('div')[0].style.color = 'blue';
        }
        liobj.ishighlight = true;
        liobj.style.background = '#426FD9';
        liobj.style.color = '#fff';
        liobj.getElementsByTagName('div')[0].style.color = '#fff';
        if (!no_scroll)
        {
            var objdiv2 = MyQEE.$('_suggest_div_scroll_div');
            if (liobj.offsetTop>objdiv2.clientHeight-100)
            {
                objdiv2.scrollTop = liobj.offsetTop-objdiv2.clientHeight+100;
            }
        }
    }
    /**
     * 解析TD项
     */
    this.prease_td = function(key,value)
    {
        return '<div style="padding-left:5px;color:blue">'+key +'&nbsp;</div></td><td><div style="padding-right:15px;">= '+value+'</div>';
    }
}

/**
 * 用AJAX方式提交表单
 * @param obj 表单对象
 * @param goto_url 成功后跳转页面
 * @param option 给ajax对象设置的参数
 */
MyQEE.form_post = function (obj,goto_url,options)
{
    if (obj.is_postting==1)
    {
        MyQEE.Msg('数据正在提交，请稍等...');
        return false;
    }
    MyQEE.show_loading();
    
    obj.is_postting = 1;
    var ajax = new MyQEE.ajax(obj.action||document.location.href);
    ajax.method = obj.method;
    ajax.setform(obj);
    ajax.onCompletion = function()
    {
        obj.is_postting = 0;
        MyQEE.hidden_loading();
        
        if (!this.responseJSON)
        {
            MyQEE.error('数据异常，请重试或联系管理员。');
            return false;
        }

        if (this.responseJSON['code']==1)
        {
            MyQEE.Msg(this.responseJSON['msg'],goto_url);
        }
        else if (this.responseJSON['code']==-1)
        {
            MyQEE.error(this.responseJSON['msg']);
        }
        else{
            MyQEE.alert(this.responseJSON['msg']);
        }
    }
    ajax.onError = function()
    {
        obj.is_postting = 0;
        MyQEE.hidden_loading();
        
        MyQEE.error('数据提交失败，请重试！');
    }
    if ( options )
    {
        try{
            for (var k in options)
            {
                ajax[k] = options[k];
            }
        }catch(e){}
    }
    ajax.runAJAX();
    
    return false;
}


/**
 * 滑动门效果打开或关闭div
 */
MyQEE.resizeDiv = function(obj,handler){
    var objid = obj.id;
    var objnoid = false;
    if (!objid){
        objid = obj.id = '_resize_rand_'+new Date().getTime()+'_'+Math.ceil(Math.random()*100000000);
        objnoid = true;
    }
    MyQEE._resizeDiv_runNum=MyQEE._resizeDiv_runNum||{};
    if (MyQEE._resizeDiv_runNum[objid]>0){
        return false;
    }
    MyQEE._resizeDiv_runNum[objid] = 0;
    
    var type = 'hidden';
    if (obj.style.display=='none'){
        type = 'show';
    }
    var height = obj.offsetHeight;
    if (height==0){
        obj.style.visibility='hidden';
        obj.style.display='';
        //取得实际高度
        height = obj.offsetHeight;
        
        obj.style.visibility='';
    }
    var old_overflow = obj.style.overflow;
    obj.style.display='';
    obj.style.overflow='hidden';
    if (type=='show'){
        obj.style.opacity = 0;
        obj.style.filter = 'alpha(opacity=0)';
    }
    
    var tmpHeight = height;
    var changeDiv = function(){
        MyQEE._resizeDiv_runNum[objid]++;
        var runNum = MyQEE._resizeDiv_runNum[objid];
        tmpHeight = tmpHeight*4/5;
        var changeHeight;
        if (type=='show'){
            obj.style.opacity = runNum*20/100;
            obj.style.filter = 'alpha(opacity='+(runNum*10)+')';
            changeHeight = height - tmpHeight;
        }else{
            obj.style.opacity = 1-runNum*20/100;
            obj.style.filter = 'alpha(opacity='+(100-runNum*10)+')';
            changeHeight = tmpHeight;
        }
        obj.style.height = changeHeight+'px';
        if (runNum<=5){
            setTimeout(changeDiv,10);
        }else{
            if (type=='hidden'){
                obj.style.display='none';
            }
            obj.style.height='';
            obj.style.opacity = '';
            obj.style.filter = '';
            obj.style.overflow = old_overflow;
            MyQEE._resizeDiv_runNum[objid] = 0;
            delete MyQEE._resizeDiv_runNum[objid];
            if (handler){
                try{handler();}catch(e){}
            }
            if (objnoid){
                obj.id='';
            }
        }
    }
    changeDiv();
}


/**
 * 输出Flash
 * @param id flashID或容器ID，传入NULL时采用document.write输出
 * @param url flash url地址
 * @param w 宽度 可用百分比
 * @param h 高度 同上
 * @param p 是否不透明，0为透明，1表示不透明
 * @param rightmenu 右键菜单 0为显示菜单，1为屏蔽
 * @param flashvars 传递falshvar参数
 * @param other 其它自定义参数，用object传递
 * @return
 */
MyQEE.flash = function(id,url,w,h,p,rightmenu,flashvars,other,ourflash){
    ourflash=ourflash||false;
    id=id||'';
    var obj=this.$(id);
    p=(!p)?'Transparent':'Opaque';
    rightmenu=(!rightmenu)?true:false;
    flashvars=flashvars||'';
    if(typeof other !='object'){
        other = {};
    }
    other['allowscriptaccess'] = other['allowscriptaccess']||'sameDomain';
    var str1='',str2='';
    for(var k in other){
        str1+='<param name="'+k+'" value="'+other[k]+'">';
        str2+=' '+k+'="'+other[k]+'"';
    }

    var tmphtml='<object id="'+(obj?id+'_flash"':id)+'" filetype="swf" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="'+w+'" height="'+h+'">' + 
                '<param name="movie" value="'+url+'">'+
                (ourflash?'':'<param name="wmode" value="'+p+'">')+
                (ourflash?'<param name="allowFullScreen" value="true" />':'')+
                '<param name="menu" value="'+rightmenu+'">'+
                '<param name="FlashVars" value="'+flashvars+'">'+str1+
                '<embed name="'+(obj?id+'_flash':id)+'" src="'+url+'" width="'+w+'" height="'+h+'"'+
                    (ourflash?'':' wmode="'+p+'"') + 
                    'flashvars="'+flashvars+'" menu="'+rightmenu+'"'+str2+' type="application/x-shockwave-flash"' +
                    (ourflash?' allowFullScreen="true"':'')+
                ' />'+
                '</object>';
    if (obj!=null){
        obj.innerHTML = tmphtml;
    }else{
        document.write(tmphtml);
    }
}

/**
 * 获取Flash版本
 * @return 版本号，例如:10.x 若没有安装则返回false
 */
MyQEE.flash_version = function() {
    var hasFlash=0;     //是否安装了flash
    var flashVersion=0; //flash版本
    if(MyQEE.is_ie){
        var swf = new ActiveXObject('ShockwaveFlash.ShockwaveFlash');
        if(swf) {
            hasFlash=1;
            VSwf=swf.GetVariable("$version");
            flashVersion=parseInt(VSwf.split(" ")[1].split(",")[0]);
        }
    }else{
        if (navigator.plugins && navigator.plugins.length > 0)
        {
            var swf=navigator.plugins["Shockwave Flash"];
            if (swf)
            {
                hasFlash=1;
                var words = swf.description.split(" ");
                for (var i = 0; i < words.length; ++i)
                {
                    if (isNaN(parseInt(words[i]))) continue;
                    flashVersion = parseInt(words[i]);
                }
            }
        }
    }
    if (hasFlash){
        return flashVersion;
    }else{
        return false;
    }
}

MyQEE.get_flash = function(objname){
    if (document[objname]){
        return document[objname];
    }else{
        return this.$((objname));
    }
}



/**
 * 加入收藏夹功能
 * @param url 指定URL，不写则是当前的url
 * @param title 指定标题，不写则是当前页面的title
 */
MyQEE.addfavorite = function(url,title)
{
    url = url||document.location.href;
    title = title||document.title|'';
    if (MyQEE.is_ie)
    {
       window.external.addFavorite(url,title);
    }
    else if (window.sidebar)
    {
       window.sidebar.addPanel(title, url, '');
    }else{
        MyQEE.Msg('您的浏览器不支持此功能，请手动加入，谢谢。');
    }
}

/**
 * 本地储存，若浏览器不支持本地储存，则采用Cookie方式
 * 
 *    // 获取
 *    MyQEE.localStorage.getItem('test');
 *    // 设置
 *    MyQEE.localStorage.setItem('test','123');
 *    // 删除
 *    MyQEE.localStorage.removeItem('test');
 * 
 */
MyQEE.localStorage = {
    getItem : function(key)
    {
        if( MyQEE.localStorage.issup() )
        {
            return localStorage.getItem(key);
        }
        else
        {
            return MyQEE.cookie.get('_'+key);
        }
    },
    setItem : function(key,value)
    {
        if( MyQEE.localStorage.issup() )
        {
            return localStorage.setItem(key,value);
        }
        else
        {
            return MyQEE.cookie.set('_'+key,value,999,'/');
        }
    },
    removeItem : function(key)
    {
        if( MyQEE.localStorage.issup() )
        {
            return localStorage.removeItem(key);
        }
        else
        {
            return MyQEE.cookie.del('_'+key);
        }
    },
    issup : function(){
        return ('localStorage' in window) && window['localStorage'] !== null;
    }
}

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



var _scroll = window.scroll;
/**
 * 实现滑动门效果的滚动，覆盖系统自带的方法
 */
window.scroll = function(x,y){
    if (MyQEE._scroll_runNum_>0){
        return true;
    }
    MyQEE._scroll_runNum_ = 0;
    var runNum = MyQEE._scroll_runNum_;
    var scrollX = MyQEE.DOM.scrollLeft || document.body.scrollLeft;
    var scrollY = MyQEE.DOM.scrollTop || document.body.scrollTop;
    var tmpX = scrollX-x;
    var tmpY = scrollY-y;
    var scroll = function(){
        runNum++;
        var newX = 0;
        var newY = 0;
        if (tmpY<0){
            //向下移
            scrollY = scrollY - tmpY/5;
            newY = scrollY;
            tmpY = tmpY*4/5;
        }else{
            //向上移
            tmpY = tmpY*4/5;
            newY = y + tmpY;
        }
        if (runNum<=20){
            _scroll(newX,newY);
            setTimeout(scroll,10);
        }else{
            _scroll(x,y);
        }
    }
    window.scroll();
}

MyQEE.scroll = scroll;

// firefox 支持 onmousewheel，文本框支持滚动条滚动改变数字，input内支持max,min
(function()
{
function listener_onmousewheel(e)
{
    e = e||window.event;
    var obj = document.activeElement;
    //有onmousewheel方法，则跳过
    if (typeof obj.onmousewheel =='function')return;
    //非input跳过
    if (obj.tagName!='INPUT')return;
    if (obj.type!='text')return;
    if ((e.target||e.srcElement)!=obj)return;
    
    //实现滚动滚轮改变input值
    var re = /^(\-)?\d*$/i;
    var r = obj.value.match(re);
    if (r!=null && r[0]!='')
    {
        var max = obj.getAttribute('max');
        var min = obj.getAttribute('min');
        if(e.preventDefault)e.preventDefault();
        e.returnValue=false;
        var old_value = obj.value;
        var n = old_value-0;
        var nn = (typeof e.wheelDelta=='number'?e.wheelDelta:-(e.detail))>0?1:-1;
        n += nn;
        if (null!=max)
        {
            n = Math.min(max,n);
        }
        if (null!=min)
        {
            n = Math.max(min,n);
        }
        obj.value = n;
        obj.select();
        
        if (obj.onchange)
        {
            // 修复因为滚动而改变数值后onchange失效的问题
            if (typeof obj._tmp_onblur=='undefined')
            {
                obj._old_value = old_value;
                obj._tmp_onblur = obj.onblur?obj.onblur:function(){};
                obj._tmp_onchange = obj.onchange;
                obj.onchange = function(){};
                obj.onblur = function()
                {
                    if (this._old_value!=obj.value)this._tmp_onchange();
                    this._tmp_onblur(e);
                    this.onblur = this._tmp_onblur;
                    this.onchange = this._tmp_onchange;
                    this._tmp_onblur = undefined;
                    this._old_value = undefined;
                }
            }
        }
    }
}

if (MyQEE.is_firefox)
{
    //Firefoxd的MozWebSocket赋值给WebSocket
    if (typeof window.WebSocket=='undefined' && typeof MozWebSocket=='function')
    {
        window.WebSocket = MozWebSocket;
    }
    
    //firefox支持onmousewheel
    addEventListener('DOMMouseScroll',function(e)
    {
        var obj = e.target;
        var onmousewheel;
        while (obj)
        {
            onmousewheel = obj.getAttribute('onmousewheel') || obj.onmousewheel;
            if (onmousewheel)break;
            if (obj.tagName=='BODY')break;
            obj = obj.parentNode;
        }
        if (onmousewheel)
        {
            if(e.preventDefault)e.preventDefault();
            e.returnValue=false;    //禁止页面滚动
            
            if ( typeof obj.onmousewheel!='function' )
            {
                //将onmousewheel转换成function
                eval('window._tmpFun = function(event){'+onmousewheel+'}');
                obj.onmousewheel = window._tmpFun;
                window._tmpFun = null;
            }
            // 不直接执行是因为若onmousewheel(e)运行时间较长的话，会导致锁定滚动失效，使用setTimeout可避免
            setTimeout(function(){
                obj.onmousewheel(e);
            },1);
        }
        else
        {
            //没有onmousewheel则监听默认
            listener_onmousewheel(e);
        }
    },false);
}
else
{
    if( document.addEventListener )
    {
        document.addEventListener('DOMMouseScroll',listener_onmousewheel,false);
    }
    document.onmousewheel = listener_onmousewheel;
}
})();

/*
---------------------------------------------------------- prototype --------------------
*/
if(!MyQEE.is_ie)
{
    HTMLElement.prototype.__defineGetter__("innerText",
    function(){
        var anyString = "";
        var childS = this.childNodes;
        for(var i=0; i<childS.length; i++) {
            if(childS[i].nodeType==1)
                //anyString += childS[i].tagName=="BR" ? "\n" : childS[i].innerText;
                anyString += childS[i].innerText;
            else if(childS[i].nodeType==3)
                anyString += childS[i].nodeValue;
        }
        return anyString;
    }
    );
    HTMLElement.prototype.__defineSetter__("innerText",
    function(sText){
        this.textContent=sText;
    }
    );
}