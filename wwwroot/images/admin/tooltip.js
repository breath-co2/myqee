var pltsPop=null;
var pltsoffsetX = 10;   // 弹出窗口位于鼠标左侧或者右侧的距离；3-12 合适
var pltsoffsetY = 15;  // 弹出窗口位于鼠标下方的距离；3-12 合适
var pltsPopbg="#FFFFEE"; //背景色
var pltsPopfg="#111111"; //前景色
var pltsTitle="";
var pltsTipLayer_Top=0;
var pltsTipLayer_Left=0;


function pltsinits()
{
	var ObjDiv= document.createElement("div");
	ObjDiv.innerHTML='<div style="position:absolute;z-index:1;top: -100px"><table><tr><td class="title_th"> </td></tr></table></div><div id="pltsTipLayer" style="display:none;position:fixed;_position:absolute;_top:expression(document.documentElement.scrollTop+pltsTipLayer_Top);_left:expression(document.documentElement.scrollLeft+pltsTipLayer_Left);z-index:10006;">'+(is_ie&&ie<7?'<iframe style="FILTER:alpha(opacity=0);z-index:-1;position:absolute;width:200px;height:100px;" scrolling="no" frameborder="0" id="toolTipFrame" name="toolTipFrame"></iframe>':'')+'<table style="FILTER:alpha(opacity=80) shadow(color=#bbbbbb,direction=135);opacity:0.8;" id="toolTipTalbe" border="0"><tr><td width="100%"><table class="titletableBorder" cellspacing="0" cellpadding="0" style="width:100%"><tr id="pltsPoptop"><th height="18" valign="bottom" class="title_th"><p id="topleft" align="left"><font style="font-family:\'宋体\'">↖</font><font id="toolTipFrame_Title1"></font></p><p id="topright" align="right" style="display:none"><font id="toolTipFrame_Title2"></font><font style="font-family:\'宋体\'">↗</font></p></th></tr><tr><td class="titleBodytd" id="toolTipMessage"></td></tr><tr id="pltsPopbot" style="display:none"><th height="18" valign="bottom" class="title_th" style="display:block"><p id="botleft" align="left"><font style="font-family:\'宋体\'">↙</font><font id="toolTipFrame_Title3"></font></p><p id="botright" align="right" style="display:none"><font id="toolTipFrame_Title4"></font><font style="font-family:\'宋体\'">↘</font></p></th></tr></table></td></tr></table></div>';
	document.body.appendChild(ObjDiv);
	document.onmouseover = plts;
	document.onmousemove = moveToMouseLoc;
	ObjDiv = null;
}
function plts(ev){
	ev = ev || window.event;
	var o = ev.target || ev.srcElement;
	if (!o)return;
	var dom = (document.compatMode &&document.compatMode=="CSS1Compat")?document.documentElement:document.body;
	var pltsTipLayer=$("pltsTipLayer");
	if(o.alt!=null && o.alt!=""){o.dypop=o.alt;o.alt=""};
	if(o.title!=null && o.title!=""){o.dypop=o.title;o.title=""};
	pltsPop=o.dypop;
	if(pltsPop!=null&&pltsPop!=""&&typeof(pltsPop)!="undefined"){
		pltsTipLayer.style.left="-1000px";
		pltsTipLayer.style.display='';
		var Msg=pltsPop.replace(/\r\n/g,'<br />').replace(/\r/g,'<br />').replace(/\n/g,'<br />');
		Msg=Msg.replace(/\0x13/g,'<br />');
		var re=/\{(.[^\{]*)\}/ig;
		if (typeof SiteName=='undefined' ){
			window['SiteName'] = "信息提示";
		}
		if(!re.test(Msg))pltsTitle = SiteName+" - <font face='Arial'>"+window.location.host+"</font>&nbsp;";
		else{
			re=/\{(.[^\{]*)\}(.*)/ig;
			pltsTitle=Msg.replace(re,"$1")+" ";
			re=/\{(.[^\{]*)\}/ig;
			Msg=Msg.replace(re,"");
			Msg=Msg.replace('<br>','');}

			$("toolTipMessage").innerHTML=Msg;
			$("toolTipFrame_Title1").innerHTML=
			$("toolTipFrame_Title2").innerHTML=
			$("toolTipFrame_Title3").innerHTML=
			$("toolTipFrame_Title4").innerHTML= pltsTitle;

			var toolTipFrame = $('toolTipFrame');
			if (toolTipFrame)toolTipFrame.style.width = 'auto';
			$("toolTipTalbe").style.width = 'auto';
			$("toolTipTalbe").style.width = Math.min(pltsTipLayer.clientWidth,dom.clientWidth/2.2)+"px";
			if (toolTipFrame){
				toolTipFrame.style.width = $("toolTipTalbe").style.width;
				toolTipFrame.style.height = pltsTipLayer.clientHeight +'px';
			}
			moveToMouseLoc;

			toolTipFrame=null;
			return true;
		}else{
			pltsTipLayer.style.display='none';
			return true;
		}
}
function moveToMouseLoc(e)
{
	var pltsTipLayer=document.getElementById("pltsTipLayer");
	if(pltsTipLayer.innerHTML=='')return true;
	e = e || window.event;
	var o = e.target || e.srcElement;
	if (!o)return;
	if (!o.dypop){return true};

	var dom = (document.compatMode &&document.compatMode=="CSS1Compat")?document.documentElement:document.body;

	var pltsPoptop=$("pltsPoptop");
	var pltsPopbot=$("pltsPopbot");
	var topleft=$("topleft");
	var botleft=$("botleft");
	var topright=$("topright");
	var botright=$("botright");
	if (is_ie)
	{
		var MouseX=event.x-dom.scrollLeft;
		var MouseY=event.y-dom.scrollTop;
	}else{
		var MouseX=e.pageX-dom.scrollLeft;
		var MouseY=e.pageY-dom.scrollTop;
	}
	var popHeight=pltsTipLayer.clientHeight;
	var popWidth=pltsTipLayer.clientWidth;
	var bigHeight = dom.clientHeight;
	var popTopAdjust = 0;
	if(MouseY+pltsoffsetY+popHeight>bigHeight)
	{
	  	popTopAdjust=-popHeight-pltsoffsetY*1.5;
	  	pltsPoptop.style.display="none";
	  	pltsPopbot.style.display="block";
	}
	 else
	{
	   	popTopAdjust=0;
	  	pltsPoptop.style.display="";
	  	pltsPopbot.style.display="none";
	}
	var bigWidth = dom.clientWidth;
	var popLeftAdjust = 0;
	if(MouseX+pltsoffsetX+popWidth>bigWidth)
	{
		popLeftAdjust=-popWidth-pltsoffsetX*2;
		topleft.style.display="none";
		botleft.style.display="none";
		topright.style.display="block";
		botright.style.display="block";
	}
	else
	{
		topleft.style.display="block";
		botleft.style.display="block";
		topright.style.display="none";
		botright.style.display="none";
	}
	if (is_ie && ie<7){
		pltsTipLayer_Left = MouseX+pltsoffsetX+popLeftAdjust+dom.scrollLeft;
		pltsTipLayer_Top =  MouseY+pltsoffsetY+popTopAdjust+dom.scrollTop;
		pltsTipLayer.style.left=pltsTipLayer_Left+dom.scrollLeft+"px";
		pltsTipLayer.style.top=pltsTipLayer_Top+dom.scrollTop+"px";
	}else{
		pltsTipLayer.style.left=MouseX+pltsoffsetX+popLeftAdjust+"px";
		pltsTipLayer.style.top=MouseY+pltsoffsetY+popTopAdjust+"px";
	}

  	return true;
}

pltsinits();