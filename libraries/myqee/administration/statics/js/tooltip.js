if (typeof pltsoffsetX == 'undefined'){
    // 弹出窗口位于鼠标左侧或者右侧的距离；3-12 合适
    var pltsoffsetX = 10;
}
if (typeof pltsoffsetY == 'undefined'){
    // 弹出窗口位于鼠标下方的距离；3-12 合适
    var pltsoffsetY = 16;
}

var _pls_old_obj = null;
var pltsTipLayer_Left = 0;
var pltsTipLayer_Top = 0;
function _plts(ev){
    ev = ev || window.event;
    var o = ev.target || ev.srcElement;
    if (!o)return;
    var dom = (document.compatMode &&document.compatMode=="CSS1Compat")?document.documentElement:document.body;
    var pltsTipLayer=MyQEE.$('pltsTipLayer');
    if(o.alt!=null && o.alt!='')
    {
        o.dypop=o.alt;
        o.alt='';
    }
    if(o.title!=null && o.title!='')
    {
        o.dypop=o.title;
        o.title='';
    }
    var pltsPop=o.dypop;
    if( pltsPop!=null&&pltsPop!="" && typeof(pltsPop)!="undefined" )
    {
        pltsTipLayer.style.left="-1000px";
        pltsTipLayer.style.display='';

        MyQEE.$("toolTipMessage").innerHTML=pltsPop;

        var toolTipFrame = MyQEE.$('toolTipFrame');
        if (toolTipFrame)toolTipFrame.style.width = 'auto';
        MyQEE.$("toolTipTalbe").style.width = 'auto';
        MyQEE.$("toolTipTalbe").style.width = Math.min(pltsTipLayer.clientWidth+2,dom.clientWidth/2.2)+"px";
        if (toolTipFrame){
            toolTipFrame.style.width = MyQEE.$("toolTipTalbe").style.width;
            toolTipFrame.style.height = pltsTipLayer.clientHeight +'px';
        }

        toolTipFrame=null;
        return true;
    }else{
        pltsTipLayer.style.display='none';
        return true;
    }
}

function _moveToMouseLoc(e)
{
    e = e || window.event;
    var o = e.target || e.srcElement;
    if (!o)return;
    if (_pls_old_obj)if(_pls_old_obj!=o)if(_pls_old_obj.dypop)_pls_old_obj.title = _pls_old_obj.dypop;
    _pls_old_obj = o;
    var pltsTipLayer=document.getElementById("pltsTipLayer");
    if(pltsTipLayer.innerHTML=='')return true;
    if (!o.dypop){return true};

    var dom = (document.compatMode &&document.compatMode=="CSS1Compat")?document.documentElement:document.body;

    if (MyQEE.is_ie)
    {
        var MouseX=event.x-dom.scrollLeft;
        var MouseY=event.y-(dom.scrollTop||document.body.scrollTop);
    }else{
        var MouseX=e.pageX-dom.scrollLeft;
        var MouseY=e.pageY-(dom.scrollTop||document.body.scrollTop);
    }
    var popHeight=pltsTipLayer.clientHeight;
    var popWidth=pltsTipLayer.clientWidth;
    var bigHeight = dom.clientHeight;
    var popTopAdjust = 0;
    if(MouseY+pltsoffsetY+popHeight>bigHeight)
    {
        popTopAdjust=-popHeight-pltsoffsetY*1.2;
    }
    else
    {
        popTopAdjust=0;
    }
    var bigWidth = dom.clientWidth;
    var popLeftAdjust = 0;
    if(MouseX+pltsoffsetX+popWidth>bigWidth)
    {
        popLeftAdjust=-popWidth-pltsoffsetX*2;
    }
    if (MyQEE.is_ie && MyQEE.ie<7){
        pltsTipLayer_Left = MouseX+pltsoffsetX+popLeftAdjust+dom.scrollLeft;
        pltsTipLayer_Top =  MouseY+pltsoffsetY+popTopAdjust+dom.scrollTop;
        pltsTipLayer.style.left=pltsTipLayer_Left+"px";
        pltsTipLayer.style.top=pltsTipLayer_Top+"px";
    }else{
        pltsTipLayer.style.left=MouseX+pltsoffsetX+popLeftAdjust+"px";
        pltsTipLayer.style.top=MouseY+pltsoffsetY+popTopAdjust+"px";
    }

    return true;
}

(function ()
{
    var ObjDiv= document.createElement("div");
    ObjDiv.innerHTML='<div id="pltsTipLayer" style="display:none;">'
        +(MyQEE.is_ie&&MyQEE.ie<7?'<iframe style="FILTER:alpha(opacity=0);z-index:-1;position:absolute;width:200px;height:100px;" scrolling="no" frameborder="0" id="toolTipFrame" name="toolTipFrame"></iframe>':'')
        +'<table id="toolTipTalbe" border="0" cellspacing="0" cellpadding="0" style="width:100%"><tr><td><div id="toolTipMessage" style="white-space:pre;"></div></td></tr></table></div>';
    document.body.appendChild(ObjDiv);
    document.onmouseover = _plts;
    document.onmousemove = _moveToMouseLoc;
    ObjDiv = null;
}
)();