/**
 * ymPrompt.js 消息提示组件
 * @author netman8410@163.com
 * $Id: ymPrompt.js 75 2010-01-18 04:17:23Z rovewang $
 *
 * @editor myqee.com
 */
//<meta http-equiv="X-UA-Compatible" content="IE=7" />  IE8透明度解决方案
//var location=window.location; 避免iframe跳转解决方案
if (typeof (auto_ymPrompt_Run) == 'undefined')
{
	var auto_ymPrompt_Run = false;
}
var ym_wintop = 0;
(function() {
	if (window.ymPrompt) return;
	var objType = function(type) {
		return new Function('o', "return Object.prototype.toString.call(o)=='[object " + type + "]'")
	}; //判断元素类型
	var isArray = objType('Array'),
	isObj = objType('Object'); //判断元素是否数组、object
	window.ymPrompt = {
		version: '4.0',
		pubDate: '2009-03-02',
		apply: function(o, c, d) {
			if (d) ymPrompt.apply(o, d);
			if (o && c && isObj(c)) for (var p in c) o[p] = c[p];
			return o;
		},
		eventList: []
	};
	/*初始化可能在页面加载完成调用的接口，防止外部调用失败。_initFn:缓存初始调用传入的参数*/
	var initFn = ['setDefaultCfg', 'show'], _initFn = {}, t;
	while (t = initFn.shift()) ymPrompt[t] = eval('0,function(){_initFn.' + t + '?_initFn.' + t + '.push(arguments):(_initFn.' + t + '=[arguments])}');
	/*以下为公用函数及变量*/
	var isIE = !+'\v1'; //IE浏览器
	var isCompat = document.compatMode == 'CSS1Compat';	//浏览器当前解释模式
	var IE6 = isIE && /MSIE (\d)\./.test(navigator.userAgent) && parseInt(RegExp.$1) < 7; //IE6以下需要用iframe来遮罩
	var useFixed = true; //= !isIE || (!IE6 && isCompat); //滚动时，IE7+（标准模式）及其它浏览器使用Fixed定位
	var $ = function(id) {return document.getElementById(id)}; //获取元素
	var $height = function(obj) {return parseInt(obj.style.height) || obj.offsetHeight}; //获取元素高度
	var addEvent = (function() {
		return new Function('env', 'fn', 'obj', 'obj=obj||document;' + (window.attachEvent ? "obj.attachEvent('on'+env,fn)": 'obj.addEventListener(env,fn,false)') + ';ymPrompt.eventList.push([env,fn,obj])')
	})(); //事件绑定
	var detachEvent = (function() {
		return new Function('env', 'fn', 'obj', 'obj=obj||document;' + (window.attachEvent ? "obj.detachEvent('on'+env,fn)": 'obj.removeEventListener(env,fn,false)'))
	})(); //取消事件绑定

	//为元素的特定样式属性设定值
	var setStyle = function(el, n, v) {
		if (!el) return;
		if (isObj(n)) {
			for (var i in n) setStyle(el, i, n[i]);
			return;
		}
		/*dom数组或dom集合*/
		if (isArray(el) || /htmlcollection|nodelist/i.test('' + el)) {
			for (var i = el.length - 1; i >= 0; i--) setStyle(el[i], n, v);
			return;
		}
		if (IE6 && dftCfg.fixPosition && n=='top')
		{
			ym_wintop = parseInt(v);
		}else{
			try {
				el.style[n] = v
			} catch(e) {}
		}
	};
	/*----------------和业务有关的公用函数-----------------*/
	var btnIndex = 0, btnCache, seed = 0; //当前焦点的按钮的索引、当前存在的按钮、id种子
	/*创建按钮*/
	var mkBtn = function(txt, sign, autoClose, id) {
		if (!txt) return;
		if (isArray(txt)) {
			/*无效按钮删除*/
			var item, t = [],
			dftBtn = {
				OK: [curCfg.okTxt, 'ok'],
				CANCEL: [curCfg.cancelTxt, 'cancel']
			};
			while (txt.length)(item = txt.shift()) && t[t.push(mkBtn.apply(null, dftBtn[item] || item)) - 1] || t.pop();
			return t;
		}
		id = id || 'ymPrompt_btn_' + seed++;
		autoClose = autoClose == undefined ? 'undefined': !!autoClose;
		return {
			id: id,
			html: "<input type='button' id='" + id + "' onclick='ymPrompt.doHandler(\"" + sign + "\"," + autoClose + ")' style='cursor:pointer' class='btnStyle handler' value='" + txt + "' />"
		};
	};
	/*生成按钮组合的html*/
	var joinBtn = function(btn) {
		if (!btn) return btnCache = '';
		if (!isArray(btn)) btn = [btn];
		if (!btn.length) return btnCache = '';
		btnCache = btn.concat();
		var html = [];
		while (btn.length) html.push(btn.shift().html);
		return html.join('&nbsp;&nbsp;');
	}
	/*默认显示配置及用户当前配置*/
	var dftCfg = {
		message: '内容',        //消息框内容
		width: 360,				//消息框宽度
		height: 185,			//消息框高度
		title: '标题',			//消息框标题
		handler: function() {},	//回调事件，默认空函数
		maskAlphaColor: '#000',	//遮罩透明色，默认黑色
		maskAlpha: 0.5,			//遮罩透明度，默认0.5
		iframe: false,			//iframe模式，默认不是
		icoCls: '',				//消息框左侧图标，默认无
		btn: null,				//消息框显示的按钮，默认无
		autoClose: true,		//点击关闭、确定等按钮是否自动关闭，默认自动关闭
		fixPosition: true,		//是否随滚动条滚动，默认是
		dragOut: false,			//是否允许拖出窗口范围，默认不允许
		titleBar: true,			//是否显示标题栏，默认显示
		showMask: true,			//是否显示遮罩，默认显示
		winPos: 'c',			//消息框弹出的位置，默认在页面中间
		winAlpha: 0.8,			//拖动时消息框的透明度，默认0.8
		closeBtn: true,			//是否显示关闭按钮，默认显示
		showShadow: true,		//是否显示消息框的阴影，默认不显示（IE支持）
		useSlide: false,		//是否启用消息框的淡入淡出效果，默认不启用
		slideCfg: {				//淡入淡出效果配置，useSlide=true时有效
			increment: 0.3,		//每次渐变的值，值范围0-1
			interval: 50		//渐变的速度
		},
		closeTxt: '关闭',		//关闭按钮的提示文本
		okTxt: '确定',		    //确定按钮的提示文本
		cancelTxt: '取消',	    //取消按钮的提示文本
		msgCls: 'ym-content',	//消息框内容的class名称，用于自定义验尸官，默认为ym-content,仅在iframe:false时有效
		minBtn: false,			//是否显示最小化按钮，默认不显示
		minTxt: '最小化',	    //最小化按钮的提示文本
		maxBtn: false,			//是否显示最大化按钮，默认不显示
		maxTxt: '最大化',		//最大化按钮的提示文本
		allowSelect:false,		//是否允许选择消息框内容，默认不允许
		allowRightMenu:false,	//是否允许在消息框使用右键，默认不允许
		middlevalign:true,		//内容是否垂直居中
		supperjs:false			//传入的内容区域是否支持再加载JS，false则使用innerHTML设置，不执行HTML中的JS
	},curCfg = {};

	/*开始解析*/
	(function(autorun) {
		var rootEl = document.body;
		window.ymInitialization = arguments.callee;
		if (!autorun)
		{
			if (!rootEl || typeof rootEl != 'object') return addEvent('load', window.ymInitialization, window); //等待页面加载完成
			/*防止在IE下因document未就绪而报“IE无法打开INTERNET站点的错”的错*/
			if (isIE && document.readyState != 'complete') return addEvent('readystatechange',function() {
				if (document.readyState == "complete" && window.ymInitialization) window.ymInitialization();
			});
		}
		if ($('ym-shadow')){
			return false;
		}
		window.ymInitialization = null;
		rootEl = isCompat ? document.documentElement: rootEl; //根据html Doctype获取html根节点，以兼容非xhtml的页面
		var frameset = document.getElementsByTagName('frameset').length; //是否frameset页面
		if (!isIE && frameset) return; //frameset页面且不是IE则直接返回，否则会出现错误。
		/*获取scrollLeft和scrollTop，在fixed定位时返回0，0*/
		var getScrollPos = function() {
			return curCfg.fixPosition && useFixed ? [0, 0] : [rootEl.scrollLeft, rootEl.scrollTop];
		}
		/*保存窗口定位信息，弹出窗口相对页面左上角的坐标信息*/
		var saveWinInfo = function() {
			var pos = getScrollPos();
			ymPrompt.apply(dragVar, {
				_offX: parseInt(ym_win.style.left) - pos[0],
				_offY: parseInt(ym_win.style.top) - pos[1]
			});
		};

		/*-------------------------创建弹窗html-------------------*/
		var maskStyle = 'position:fixed;top:0;left:0;display:none;text-align:center;'+(IE6 && dftCfg.fixPosition ? '_position:absolute;top:expression((eval(document.compatMode &&document.compatMode=="CSS1Compat") ?documentElement.scrollTop:document.body.scrollTop));':'');
		var div = document.createElement('div');
		div.innerHTML = [
			/*遮罩*/
			"<div id='maskLevel' style=\'" + maskStyle + ';z-index:100001;\'></div>', 
			isIE ? ("<iframe id='maskIframe' style='" + maskStyle + ";z-index:99999;filter:alpha(opacity=0);' frameborder='0'></iframe>") : '',
			/*阴影*/
			"<div id='ym-shadow' style='display:none;overflow:auto;position:absolute;z-index:100001;"+(IE6 && dftCfg.fixPosition ? 'top:expression((eval(document.compatMode &&document.compatMode=="CSS1Compat") ?documentElement.scrollTop:document.body.scrollTop)+ym_wintop);':'')+"'>"+(IE6 ? ("<iframe id='maskIframe' style='width:100%;height:100%;filter:alpha(opacity=0);' frameborder='0'></iframe>") : '')+"</div>",
			/*窗体*/
			"<div id='ym-window' style='overflow:hidden;position:absolute;text-align:left;z-index:100001;display:none;",IE6 && dftCfg.fixPosition ? 'top:expression((eval(document.compatMode &&document.compatMode=="CSS1Compat") ?documentElement.scrollTop:document.body.scrollTop)+ym_wintop);':'' , "'>", 
				"<div class='ym-tl' id='ym-tl'><div class='ym-tr'><div class='ym-tc' style='cursor:move;'><div class='ym-header-text'></div><div class='ym-header-tools'>", "<div class='ymPrompt_min' title='最小化'><strong>"+(isIE?'0':'＿')+"</strong></div>", "<div class='ymPrompt_max' title='最大化'><strong>"+(isIE?'1':'□')+"</strong></div>", "<div class='ymPrompt_close' title='关闭'><strong>"+(isIE?'r':'×')+"</strong></div>", "</div></div></div></div>", 
				"<div class='ym-ml' id='ym-ml'><div class='ym-mr'><div class='ym-mc'><div id='ym-body-div' class='ym-body' style='position:relative;'>www</div></div></div></div>", 
				"<div class='ym-ml' id='ym-btnl'><div class='ym-mr'><div class='ym-btn'></div></div></div>", "<div class='ym-bl' id='ym-bl'><div class='ym-br'><div class='ym-bc'></div></div></div>", "</div>"
		].join('');
		document.body.appendChild(div);
		/*窗口上的对象*/
		/*mask、window*/
		var maskLevel = $('maskLevel');
		var ym_win = $('ym-window');
		var ym_shadow = $('ym-shadow');
		var ym_wins;
		/*header*/
		var ym_headbox = $('ym-tl');
		var ym_head = ym_headbox.firstChild.firstChild;
		var ym_hText = ym_head.firstChild;
		var ym_hTool = ym_hText.nextSibling;
		/*content*/
		var ym_body = $('ym-body-div');
		/*button*/
		var ym_btn = $('ym-btnl');
		var ym_btnContent = ym_btn.firstChild.firstChild;
		/*bottom*/
		var ym_bottom = $('ym-bl');
		var maskEl = [maskLevel]; //遮罩元素
		isIE && maskEl.push($('maskIframe'));
		var ym_ico = ym_hTool.childNodes; //右上角的图标
		var dragVar = {};
		/*窗口的最大化最小化核心功能实现*/
		var cur_state = 'normal',
		cur_cord = [0, 0]; //cur_cord记录最大化前窗口的坐标
		var cal_cord = function() {
			var pos = getScrollPos();
			cur_cord = [parseInt(ym_win.style.left) - pos[0], parseInt(ym_win.style.top) - pos[1]]
		}; //保存坐标(相对页面左上角坐标)

		//页面正文修正高度，默认0，可以正数也可以负数
		var bodyAmendHeight = false;
		
		var tmpScrollTop = 0;
		/*从常态到最大化*/
		var doMax = function() {
			cal_cord(); //记录坐标，便于还原时使用
			cur_state = 'max';
			ym_ico[1].firstChild.innerHTML = isIE?'2':'〓';
			ym_ico[1].className = 'ymPrompt_normal';
			if (IE6 && dftCfg.fixPosition){
				tmpScrollTop = document.compatMode=="CSS1Compat"?document.documentElement.scrollTop:document.body.scrollTop;
			}
			setWinSize(rootEl.clientWidth, rootEl.clientHeight, [0, 0]);
		};
		/*从正常到最小化*/
		var doMin = function() {
			cal_cord();
			cur_state = 'min';
			ym_ico[0].firstChild.innerHTML = isIE?'2':'〓';
			ym_ico[0].className = 'ymPrompt_normal';
			ym_btn.style.display='none';
			$('ym-ml').style.display='none';
			if (IE6 && dftCfg.fixPosition){
				ym_wintop = cur_cord[1];
				cur_cord[1] = cur_cord[1]-(document.compatMode=="CSS1Compat"?document.documentElement.scrollTop:document.body.scrollTop);
			}
			setWinSize(0, $height(ym_headbox)+$height(ym_bottom), cur_cord); //定位在当前坐标
		};
		var doNormal = function(init) { //init=true,弹出时调用该函数
			! init && cur_state == 'min' && cal_cord(); //从最小化过来重新获取坐标
			ym_ico[0].firstChild.innerHTML = isIE?'0':'＿';
			ym_ico[1].firstChild.innerHTML = isIE?'1':'□';
			ym_ico[0].className = 'ymPrompt_min';
			ym_ico[1].className = 'ymPrompt_max';
			$('ym-ml').style.display='';
			ym_btn.style.display=ym_btnContent.innerHTML==''?'none':'';
			if (IE6 && dftCfg.fixPosition){
				ym_wintop = cur_cord[1];
				if (cur_state=='min'){
					tmpScrollTop = document.compatMode=="CSS1Compat"?document.documentElement.scrollTop:document.body.scrollTop;
				}
				cur_cord[1] = cur_cord[1]-tmpScrollTop;
			}
			cur_state = 'normal';
			setWinSize.apply(this, init ? [] : [0, 0, cur_cord]);
		};
		var max, min;
		addEvent('click', min = function() {
			cur_state != 'normal' ? doNormal() : doMin();
		},
		ym_ico[0]); //最小化
		addEvent('mouseover',function(){ym_ico[0].className=cur_state == 'min' ?'ymPrompt_normal ymPrompt_normal_hover':'ymPrompt_min ymPrompt_min_hover';},ym_ico[0]); //最小化mouseover
		addEvent('mouseout', function(){ym_ico[0].className=cur_state == 'min' ?'ymPrompt_normal':'ymPrompt_min';},ym_ico[0]); //最小化mouseout

		addEvent('click', max = function() {
			cur_state != 'normal' ? doNormal() : doMax();
		},
		ym_ico[1]); //最大化
		addEvent('dblclick', function(e) {
			/*如果操作元素是最大最小关闭按钮则不进行此处理*/
			curCfg.maxBtn && (e.srcElement || e.target).parentNode != ym_hTool && max()
		},
		ym_head);
		addEvent('mouseover',function(){ym_ico[1].className=cur_state == 'max' ?'ymPrompt_normal ymPrompt_normal_hover':'ymPrompt_big ymPrompt_max_hover';},ym_ico[1]); //最大化mouseover
		addEvent('mouseout', function(){ym_ico[1].className=cur_state == 'max' ?'ymPrompt_normal':'ymPrompt_max';},ym_ico[1]); //最大化mouseout

		addEvent('click', function() {
			ymPrompt.doHandler('close');
		},
		ym_ico[2]); //关闭
		addEvent('mouseover', function(){ym_ico[2].className='ymPrompt_close ymPrompt_close_hover';},ym_ico[2]); //关闭mouseover
		addEvent('mouseout', function(){ym_ico[2].className='ymPrompt_close';},ym_ico[2]); //关闭mouseout

		/*窗口最大化最小化核心部分结束*/
		/*getWinSize取得页面实际大小*/
		var getWinSize = function() {
			return [Math.max(rootEl.scrollWidth, rootEl.clientWidth), Math.max(rootEl.scrollHeight, rootEl.clientHeight),rootEl.clientWidth, rootEl.clientHeight]
		};
		var winSize = getWinSize(); //保存当前页面的实际大小
		/*事件绑定部分*/
		var bindEl = ym_head.setCapture && ym_head; //绑定拖放事件的对象，只有Ie下bindEl有效
		/*窗体透明度控制*/
		var filterWin = function(v) {
			/*鼠标按下时取消窗体的透明度，IE标准模式下透明度为1则直接清除透明属性，防止iframe窗口不能拖动滚动条*/
			! frameset && setStyle(ym_win, v == 1 && isCompat ? {
				filter: '',
				opacity: ''
			}: {
				filter: 'Alpha(opacity=' + v * 100 + ')',
				opacity: v
			});
		};
		/*mousemove事件*/
		var mEvent = function(e) {
			var sLeft = dragVar.offX + e.clientX;
			var sTop = dragVar.offY + e.clientY;
			if (IE6 && dftCfg.fixPosition)sTop -= rootEl.scrollTop;
			if (!curCfg.dragOut) { //页面可见区域内拖动
				var pos = getScrollPos(),
				sl = pos[0],
				st = pos[1];
				sLeft = Math.min(Math.max(sLeft, sl), rootEl.clientWidth - ym_win.offsetWidth + sl);
				sTop = Math.min(Math.max(sTop, st), rootEl.clientHeight - ym_win.offsetHeight + st);
			} else if (curCfg.showMask && '' + winSize != '' + getWinSize()) //及时调整遮罩大小
			resizeMask(true);
			setStyle(ym_wins, {
				left: sLeft + 'px',
				top: sTop + 'px'
			});
		};
		/*mouseup事件*/
		var uEvent = function() {
			filterWin(1);
			detachEvent("mousemove", mEvent, bindEl);
			detachEvent("mouseup", uEvent, bindEl);
			saveWinInfo(); //保存当前窗口的位置
			curCfg.iframe && setStyle(getPage().nextSibling, 'display', 'none');
			/*IE下窗口外部拖动*/
			bindEl && (detachEvent("losecapture", uEvent, bindEl), bindEl.releaseCapture());
		};
		addEvent('mousedown',function(e) {
			if ((e.srcElement || e.target).parentNode == ym_hTool) return false; //点击操作按钮不进行启用拖动处理
			filterWin(curCfg.winAlpha); //鼠标按下时窗体的透明度
			/*鼠标与弹出框的左上角的位移差*/
			ymPrompt.apply(dragVar, {
				offX: parseInt(ym_win.style.left) - e.clientX,
				offY: parseInt(ym_win.style.top) - e.clientY
			});
			addEvent("mousemove", mEvent, bindEl);
			addEvent("mouseup", uEvent, bindEl);
			if (curCfg.iframe) {
				var cfg = {display: ''}, pg = getPage();
				isCompat && IE6 && ymPrompt.apply(cfg, {
					width: pg.offsetWidth,
					height: pg.offsetHeight
				}); //IE6必须设置高度
				setStyle(pg.nextSibling, cfg)
			}
			/*IE下窗口外部拖动*/
			bindEl && (addEvent("losecapture", uEvent, bindEl), bindEl.setCapture());
		},
		ym_head);
		/*页面滚动弹出窗口滚动*/
		var scrollEvent = function() {
			setStyle(ym_win, {
				left: dragVar._offX + rootEl.scrollLeft + 'px',
				top: dragVar._offY + rootEl.scrollTop + 'px'
			});
		};
		/*键盘监听*/
		var keydownEvent = function(e) {
			var keyCode = e.keyCode;
			if (keyCode == 27) destroy(); //esc键
			
			if (btnCache) {
				var l = btnCache.length, nofocus;
				/*tab键/左右方向键切换焦点*/
				document.activeElement && document.activeElement.id != btnCache[btnIndex].id && (nofocus = true);
				if (keyCode == 9 || keyCode == 39) nofocus && (btnIndex = -1),
				$(btnCache[++btnIndex == l ? (--btnIndex) : btnIndex].id).focus();
				if (keyCode == 37) nofocus && (btnIndex = l),
				$(btnCache[--btnIndex < 0 ? (++btnIndex) : btnIndex].id).focus();
				if (keyCode == 13) return true;
			}
			return true;
			
			/*禁止F1-F12/ tab 回车*/
			//return keyEvent(e, (keyCode > 110 && keyCode < 123) || keyCode == 9);
		};
		/*监听键盘事件*/
		var keyEvent = function(e, d , k) {
			e = e || event;
			/*允许对表单项进行操作*/
			if (!d && /input|select|textarea/i.test((e.srcElement || e.target).tagName)) return true;
			try {
				e.returnValue = false;
				e.keyCode = 0;
			} catch(ex) {
				e.preventDefault && e.preventDefault();
			}
			return false;
		};
		maskLevel.oncontextmenu = keyEvent; //禁止右键和选择
		/*重新计算遮罩的大小*/
		var resizeMask = function(noDelay) {
			setStyle(maskEl, 'display', 'none'); //先隐藏
			var size = getWinSize();
			if (IE6 && !dftCfg.fixPosition)
			{
				size = [size[0],size[1],size[0],size[1]];
			}
			var resize = function() {
				setStyle(maskEl, {
					width: size[2] + 'px',
					height: size[3] + 'px',
					display: ''
				});
			};
			isIE ? noDelay === true ? resize() : setTimeout(resize, 0) : resize();
			cur_state == 'min' ? doMin() : cur_state == 'max' ? doMax() : setWinSize(); //最大化最小化状态还原
		};
		/*蒙版的显示隐藏,state:true显示,false隐藏，默认为true*/
		var maskVisible = function(visible) {
			if (!curCfg.showMask) return; //无遮罩
			(visible === false ? detachEvent: addEvent)("resize", resizeMask, window); //页面大小改变及时调整遮罩大小
			if (visible === false) return setStyle(maskEl, 'display', 'none'); //隐藏遮罩
			setStyle(maskLevel, {
				background: curCfg.maskAlphaColor,
				filter: 'Alpha(opacity=' + curCfg.maskAlpha * 100 + ')',
				opacity: curCfg.maskAlpha
			});
			resizeMask(true);
		};
		/*计算指定位置的坐标，返回数组*/
		var getPos = function(f) {
			/*传入有效的数组，则采用用户坐标（需要做简单处理），否则根据传入字符串到map中匹配，如果匹配不到则默认采用c配置*/
			f = isArray(f) && f.length == 2 ? (f[0] + '+{2},{3}+' + f[1]) : (posMap[f] || posMap['c']);
			var pos = [rootEl.clientWidth - ym_win.offsetWidth, rootEl.clientHeight - ym_win.offsetHeight].concat(getScrollPos());
			var arr = f.replace(/\{(\d)\}/g,function(s, s1) {return pos[s1]}).split(',');
			return [eval(arr[0]), eval(arr[1])];
		}; //9个常用位置常数
		var posMap = {
			c: '{0}/2+{2},{1}/2+{3}',
			l: '{2},{1}/2+{3}',
			r: '{0}+{2},{1}/2+{3}',
			t: '{0}/2+{2},{3}',
			b: '{0}/2,{1}+{3}',
			lt: '{2},{3}',
			lb: '{2},{1}+{3}',
			rb: '{0}+{2},{1}+{3}',
			rt: '{0}+{2},{3}'
		};
		
		/*设定窗口大小及定位*/
		var setWinSize = function(w, h, pos) {
			var ishidden = ym_win.style.display == 'none'?true:false;
			if (ishidden) {
				//当前不可见则处理
				ym_win.style.visibility = 'hidden';
				ym_win.style.display = '';
			}
			if (bodyAmendHeight===false){
				bodyAmendHeight = $height($('ym-ml'))-$height(ym_body);
			}
			/*默认使用配置的宽高*/
			h = parseInt(h) || curCfg.height;
			w = parseInt(w) || curCfg.width;
			setStyle(ym_wins, {
				width: w + 'px',
				height: h + 'px'
			});
			pos = getPos(pos || curCfg.winPos); //支持自定义坐标，或者默认配置

			setStyle(ym_wins, {
				top: pos[1] + 'px',
				left: pos[0] + 'px'
			});
			saveWinInfo(); //保存当前窗口位置信息
			
			setStyle(ym_body, 'height', h - bodyAmendHeight - $height(ym_headbox) - $height(ym_btn) - $height(ym_bottom) + 'px'); //设定内容区的高度
			isCompat && IE6 && curCfg.iframe && setStyle(getPage(), {height: ym_body.clientHeight}); //IE6标准模式下要计算iframe高度
			
			if (ishidden) {
				ym_win.style.display = 'none';
				ym_win.style.visibility = '';
			}
		};
		//var _obj = []; //IE中可见的obj元素
		var cacheWin = []; //队列中的窗口
		var winVisible = function(visible) {
			var fn = visible === false ? detachEvent: addEvent;
			fn('scroll', curCfg.fixPosition && !useFixed ? scrollEvent: saveWinInfo, window);
			setStyle(ym_wins, 'position', !IE6 && curCfg.fixPosition && useFixed ? 'fixed': 'absolute');
			fn('keydown', keydownEvent);
			if (visible === false) { //关闭
				setStyle(ym_shadow, 'display', 'none');
				/*关闭窗口执行的操作*/
				var closeFn = function() {
					setStyle(ym_win, 'display', 'none');
					//setStyle(_obj, 'visibility', 'visible');
					//_obj = []; //把当前弹出移除
					cacheWin.shift(); //读取队列中未执行的弹出
					if (cacheWin.length) ymPrompt.show.apply(null, cacheWin[0].concat(true))
				};
				/*渐变方式关闭*/
				var alphaClose = function() {
					var alpha = 1;
					var hideFn = function() {
						alpha = Math.max(alpha - curCfg.slideCfg.increment, 0);
						filterWin(alpha);
						if (alpha == 0) {
							maskVisible(false);
							closeFn();
							clearInterval(it);
						}
					};
					hideFn();
					var it = setInterval(hideFn, curCfg.slideCfg.interval);
				};
				curCfg.useSlide ? alphaClose() : closeFn();
				return;
			}
			//for (var o = document.getElementsByTagName('object'), i = o.length - 1; i > -1; i--) o[i].style.visibility != 'hidden' && _obj.push(o[i]) && (o[i].style.visibility = 'hidden');
			setStyle([ym_hText, ym_hTool], 'display', (curCfg.titleBar ? '': 'none'));
			ym_head.className = 'ym-tc' + (curCfg.titleBar ? '': ' ym-ttc'); //无标题栏
			ym_hText.innerHTML = curCfg.title; //标题
			for (var i = 0, c = ['min', 'max', 'close']; i < 3; i++) {
				ym_ico[i].style.display = curCfg[c[i] + 'Btn'] ? '': 'none';
				ym_ico[i].title = curCfg[c[i] + 'Txt'];
			}
			/*iframe如果不加上opacity=100，则ym-win和用于遮罩iframe的div也透明时，iframe也就透明了*/
			var ifmStyle = 'position:absolute;width:100%;height:100%;top:0;left:0;opacity:1;filter:alpha(opacity=100)';
			var msg;
			if(curCfg.middlevalign){
				msg = '<table cellspacing="0" cellpadding="0" border="0" style="border:0px;height:100%;"><tbody><tr><td><div class="' + curCfg.msgCls + '">' + curCfg.message + '</div></td></tr></tbody></table>';
			}else{
				msg = '<div class="' + curCfg.msgCls + '">' + curCfg.message + '</div>';
			}
			var html = !curCfg.iframe ? msg : "<iframe style='" + ifmStyle + "' border='0' frameborder='0' src='" + curCfg.message + "'></iframe><div style='" + ifmStyle + ";background:#000;opacity:0.1;filter:alpha(opacity=10);display:none'></div>"; //内容
			if (curCfg.supperjs){
				VeryCD.innerHTML(ym_body,html);
			}else{
				ym_body.innerHTML = html;
			}
			(function(el, obj) {
				for (var i in obj) try {
					el[i] = obj[i]
				} catch(e) {}
			})(ym_body.firstChild, curCfg.iframe); //为iframe添加自定义属性
			ym_body.className = "ym-body " + curCfg.icoCls; //图标类型
			setStyle(ym_btn, 'display', ((ym_btnContent.innerHTML = joinBtn(mkBtn(curCfg.btn))) ? 'block': 'none')); //没有按钮则隐藏
			isIE && ! curCfg.useSlide && curCfg.showShadow && setStyle(ym_shadow, 'display', 'block');
			setStyle(ym_win, 'display', '');
			doNormal(true);
			filterWin(curCfg.useSlide ? 0 : 1); //此处使用filter同时可以解决IE非标准模式下有时下边会出现1px空白，使内容与下部不衔接的问题
			/*渐变方式显示*/
			curCfg.useSlide && (function() {
				var alpha = 0;
				var showFn = function() {
					alpha = Math.min(alpha + curCfg.slideCfg.increment, 1);
					filterWin(alpha);
					if (alpha == 1) {
						clearInterval(it);
						isIE && curCfg.showShadow && setStyle(ym_shadow, 'display', '')
					}
				}
				showFn();
				var it = setInterval(showFn, curCfg.slideCfg.interval);
			})();
			if (btnCache){
				$(btnCache[btnIndex = 0].id).focus(); //第一个按钮获取焦点
				$(btnCache[btnIndex = 0].id).className += ' hover';
			}
			/*是否禁止选择、禁止右键*/
			ym_win.onselectstart = curCfg.allowSelect?null:keyEvent;
			ym_win.oncontextmenu = curCfg.allowRightMenu?null:keyEvent;
		}; //初始化
		var init = function() {
			ym_wins = [ym_win].concat(isIE && curCfg.showShadow ? ym_shadow: ''); //是否使用阴影
			maskVisible();
			winVisible();
		}; //销毁
		var destroy = function(dd) { ! curCfg.useSlide && maskVisible(false);
			winVisible(false);
		}; //取得iframe
		var getPage = function() {
			return curCfg.iframe ? ym_body.firstChild: null
		}
		ymPrompt.apply(ymPrompt, {
			close: destroy,
			max: max,
			min: min,
			normal: doNormal,
			getPage: getPage,
			/*显示消息框,fargs:优先配置，会覆盖args中的配置*/
			/*show 强制显示*/
			show: function(args, fargs, show) {
				//如果有窗口未关闭则将本次传入的信息放到队列里
				//if (!show && cacheWin.push([args, fargs]) && cacheWin.length > 1) return;
				/*支持两种参数传入方式:(1)JSON方式 (2)多个参数传入*/
				var a = [].slice.call(args, 0), o = {}, j = -1;
				if (!isObj(a[0])) {
					for (var i in dftCfg) if (a[++j]) o[i] = a[j];
				} else {
					o = a[0];
				}
				ymPrompt.apply(curCfg, ymPrompt.apply({}, o, fargs), ymPrompt.setDefaultCfg()); //先还原默认配置
				/*修正curCfg中的无效值(null/undefined)改为默认值*/
				for (var i in curCfg) curCfg[i] = curCfg[i] != null ? curCfg[i] : ymPrompt.cfg[i];
				init();
			},
			doHandler: function(sign, autoClose, closeFirst) {
				if (autoClose == undefined ? curCfg.autoClose: autoClose) destroy();
				if (curCfg.handler == undefined)return;
				try { (curCfg.handler)(sign)
				} catch(e) {
					alert(e.message)
				};
			},
			resizeWin: function(w,h) {
				curCfg.width = parseInt(w) || curCfg.width;
				curCfg.height = parseInt(h) || curCfg.height;
				setWinSize();
			},
			/*设定默认配置*/
			setDefaultCfg: function(cfg) {
				return ymPrompt.cfg = ymPrompt.apply({}, cfg, ymPrompt.apply({}, ymPrompt.cfg, dftCfg));
			},
			getButtons: function() {
				var btns = btnCache || [], btn, rBtn = [];
				while (btn = btns.shift()) rBtn.push($(btn.id));
				return rBtn;
			}
		});
		ymPrompt.setDefaultCfg(); //初始化默认配置
		/*执行用户初始化时的调用*/
		var t;
		for (var i in _initFn) while (t = _initFn[i].shift()) ymPrompt[i].apply(null, t);
		/*取消事件绑定*/
		addEvent('unload', function() {
			while (ymPrompt.eventList.length) detachEvent.apply(null, ymPrompt.eventList.shift());
		}, window);
	})(auto_ymPrompt_Run);
})(); //各消息框的相同操作
ymPrompt.apply(ymPrompt, {
	alert: function() {
		ymPrompt.show(arguments, {
			icoCls: 'ymPrompt_alert',
			btn: ['OK']
		});
	},
	succeedInfo: function() {
		ymPrompt.show(arguments, {
			icoCls: 'ymPrompt_succeed',
			btn: ['OK']
		});
	},
	errorInfo: function() {
		ymPrompt.show(arguments, {
			icoCls: 'ymPrompt_error',
			btn: ['OK']
		});
	},
	confirmInfo: function() {
		ymPrompt.show(arguments, {
			icoCls: 'ymPrompt_confirm',
			btn: ['OK', 'CANCEL']
		});
	},
	win: function() {
		ymPrompt.show(arguments);
	}
});