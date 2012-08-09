var controlid = null;
var currdate = null;
var startdate = null;
var enddate  = null;
var yy = null;
var mm = null;
var hh = null;
var ii = null;
var ss = null;
var currday = null;
var addtime = false;
var today = new Date();
var lastcheckedyear = false;
var lastcheckedmonth = false;
var calender_m_h_run = null;
var calender_y_h_run = null;

function _cancelBubble(event) {
	e = event ? event : window.event ;
	if(MyQEE.is_ie) {
		e.cancelBubble = true;
	} else {
		e.stopPropagation();
	}
}

function loadcalender() {

	s = '';
	s += '<div id="calender" style="display:none; position:absolute; z-index:9;" onclick="_cancelBubble(event)">';
	if (MyQEE.is_ie)
	{
		s += '<iframe width="190" height="160" src="about:blank" style="position: absolute;z-index:-1;"></iframe>';
	}
	s += '<div style="width: 200px;"><table class="tableborder calender" cellspacing="0" cellpadding="0" width="100%" style="text-align: center">';
	s += '<tr><th class="th" style="border-bottom:1px solid #86B9D6;height:16px;font-weight:normal;font-size:12px;height:24px;" colspan="7" align="center"><a href="#" onclick="setthetime();return false;" id="setnowtime_a">当前时间</a>&nbsp; <span id="setnowtime_b"><a href="#" onclick="var d=currday;d.setMinutes(0);d.setSeconds(0);setthetime(d);return false;">整点</a>&nbsp; <a href="#" onclick="var d=currday;d.setMinutes(0);d.setSeconds(0);d.setHours(0);setthetime(d);return false;">零点</a></span><span id="setnowtime_c"><a href="#" onclick="var d=today;d.setDate(d.getDate()-1);setthetime(d);return false;">昨日</a>&nbsp; <a href="#" onclick="var d=today;d.setDate(d.getDate()+1);setthetime(d);return false;">明日</a>&nbsp; <a href="#" onclick="var d=currday;d.setDate(1);setthetime(d);return false;">月初</a>&nbsp; <a href="#" onclick="var d=currday;d.setDate(new Date(d.getFullYear(),d.getMonth()+1,0).getDate());setthetime(d);return false;">月末</a></span>&nbsp; <a href="#" onclick="controlid.value=\'\';controlid.focus();return false;">清除</a></th></tr>';
	s += '<tr><th class="th"><a href="#" onclick="refreshcalender(yy, mm-1);return false" title="上一月">&lt;&lt;</a></th><th class="th" colspan="5" style="text-align: center">';
	s += '<a id="year"  href="#" onclick="e=e||window.event;showdiv(\'year\');_cancelBubble(e);return false;"  onmouseover="clearTimeout(calender_y_h_run);calender_y_h_run=null;calender_y_h_run=setTimeout(this.onclick,100);return false" onmouseout="if (calender_y_h_run){clearTimeout(calender_y_h_run);calender_y_h_run=null;}calender_y_h_run=setTimeout(&quot;MyQEE.$(\'calender_year\').style.display=\'none\';&quot;,500)"></a>&nbsp; - &nbsp;';
	s += '<a id="month" href="#" onclick="e=e||window.event;showdiv(\'month\');_cancelBubble(e);return false;" onmouseover="clearTimeout(calender_m_h_run);calender_m_h_run=null;calender_m_h_run=setTimeout(this.onclick,100);return false" onmouseout="if (calender_m_h_run){clearTimeout(calender_m_h_run);calender_m_h_run=null;}calender_m_h_run=setTimeout(&quot;MyQEE.$(\'calender_month\').style.display=\'none\';&quot;,600);"></a></th><th class="th"><a href="#" onclick="refreshcalender(yy, mm+1);return false" title="下一月">&gt;&gt;</a></th></tr>';
	s += '<tr class="category"><td>日</td><td>一</td><td>二</td><td>三</td><td>四</td><td>五</td><td>六</td></tr>';
	for(var i = 0; i < 6; i++) {
		s += '<tr class="altbg2">';
		for(var j = 1; j <= 7; j++)
			s += "<td id=d" + (i * 7 + j) + " height=\"19\"></td>";
		s += "</tr>";
	}
	s += '<tr id="hourminute"><td colspan="7" align="center" style="padding:4px 0;"><input type="text" size="1" value="" id="hour" onmouseover="this.focus();"'+(MyQEE.is_firefox?'':' title="可滚动滚轮递增或递减"')+' onmousewheel="var d=get_wheel_value(event)>0?1:-1;var nv=this.value-0+d;this.value=nv<0?0:(nv>23?23:nv);this.select();return false;" onfocus="this.select()" onblur=\'this.value=this.value > 23 ? 23 : zerofill(this.value);controlid.value=controlid.value.replace(/\\d+(\:\\d+\:\\d+)/ig, this.value+"$1")\' maxlength="2" /> 点 <input type="text" size="1" value="" onmouseover="this.focus();" id="minute"'+(MyQEE.is_firefox?'':' title="可滚动滚轮递增或递减"')+' onmousewheel="var d=get_wheel_value(event)>0?1:-1;var nv=this.value-0+d;this.value=nv<0?0:(nv>59?59:nv);this.select();return false;" onfocus="this.select()" onblur=\'this.value=this.value > 59 ? 59 : zerofill(this.value);controlid.value=controlid.value.replace(/(\\d+\:)\\d+(\:\\d+)/ig, "$1"+this.value+"$2")\' maxlength="2" /> 分 <input type="text" size="1" value="" id="second"'+(MyQEE.is_firefox?'':' title="可滚动滚轮递增或递减"')+' onmousewheel="var d=get_wheel_value(event)>0?1:-1;var nv=this.value-0+d;this.value=nv<0?0:(nv>59?59:nv);this.select();return false;" onmouseover="this.focus();" onfocus="this.select()" onblur=\'this.value=this.value > 59 ? 59 : zerofill(this.value);controlid.value=controlid.value.replace(/(\\d+\:\\d+\:)\\d+/ig, "$1"+this.value)\' maxlength="2" /> 秒</td></tr>';
	s += '</table></div></div>';
	s += '<div id="calender_year" style="display:none" onmouseover="clearTimeout(calender_y_h_run);calender_y_h_run=null;" onmouseout="if (null==calender_y_h_run)calender_y_h_run=setTimeout(&quot;MyQEE.$(\'calender_year\').style.display=\'none\';&quot;,350)" onclick="_cancelBubble(event)"><div class="col">';
	for(var k = 1980; k <= 2029; k++) {
		s += k != 1980 && k % 10 == 0 ? '</div><div class="col">' : '';
		s += '<a href="#" onclick="refreshcalender(' + k + ', mm);MyQEE.$(\'calender_year\').style.display=\'none\';return false"><span' + (today.getFullYear() == k ? ' class="today"' : '') + ' id="calender_year_' + k + '">' + k + '</span></a><br />';
	}
	s += '</div></div>';
	s += '<div id="calender_month" onmouseover="clearTimeout(calender_m_h_run);calender_m_h_run=null;" onmouseout="if (null==calender_m_h_run)calender_m_h_run=setTimeout(&quot;MyQEE.$(\'calender_month\').style.display=\'none\';&quot;,350)" style="display:none" onclick="_cancelBubble(event)">';
	for(var k = 1; k <= 12; k++) {
		s += '<a href="#" onclick="refreshcalender(yy, ' + (k - 1) + ');MyQEE.$(\'calender_month\').style.display=\'none\';return false"><span' + (today.getMonth()+1 == k ? ' class="today"' : '') + ' id="calender_month_' + k + '">' + k + ( k < 10 ? '&nbsp;' : '') + ' 月</span></a><br />';
	}
	s += '</div>';

	var nElement = document.createElement("div");
	nElement.innerHTML=s;
	document.body.appendChild(nElement);

//	document.write(s);
	document.onclick = function(event) {
		MyQEE.$('calender').style.display = 'none';
		MyQEE.$('calender_year').style.display = 'none';
		MyQEE.$('calender_month').style.display = 'none';
	}
	MyQEE.$('calender').onclick = function(event) {
		_cancelBubble(event);
		MyQEE.$('calender_year').style.display = 'none';
		MyQEE.$('calender_month').style.display = 'none';
	}
}

function parsedate(s) {
	/(\d+)\-(\d+)\-(\d+)\s*(\d*):?(\d*):?(\d*)/.exec(s);
	var m1 = (RegExp.$1 && RegExp.$1 > 1899 && RegExp.$1 < 2101) ? parseFloat(RegExp.$1) : today.getFullYear();
	var m2 = (RegExp.$2 && (RegExp.$2 > 0 && RegExp.$2 < 13)) ? parseFloat(RegExp.$2) : today.getMonth() + 1;
	var m3 = (RegExp.$3 && (RegExp.$3 > 0 && RegExp.$3 < 32)) ? parseFloat(RegExp.$3) : today.getDate();
	var m4 = (RegExp.$4 && (RegExp.$4 > -1 && RegExp.$4 < 24)) ? parseFloat(RegExp.$4) : 0;
	var m5 = (RegExp.$5 && (RegExp.$5 > -1 && RegExp.$5 < 60)) ? parseFloat(RegExp.$5) : 0;
	var m6 = (RegExp.$6 && (RegExp.$6 > -1 && RegExp.$6 < 60)) ? parseFloat(RegExp.$6) : 0;
	/(\d+)\-(\d+)\-(\d+)\s*(\d*):?(\d*):?(\d*)/.exec("0000-00-00 00\:00\:00");
	return new Date(m1, m2 - 1, m3, m4, m5 , m6);
}

function settime(d) {
	MyQEE.$('calender').style.display = 'none';
	controlid.value = yy + "-" + zerofill(mm + 1) + "-" + zerofill(d) + (addtime ? ' ' + zerofill(MyQEE.$('hour').value) + ':' + zerofill(MyQEE.$('minute').value) + ':'+zerofill(MyQEE.$('second').value) : '');
}

function setthetime(mydate) {
    MyQEE.$('calender').style.display = 'none';
    mydate=mydate||new Date();
    var mmmm=mydate.getMonth()+1;
    var dddd=mydate.getDate();
    var hhhh=mydate.getHours();
    var iiii=mydate.getMinutes();
    var ssss=mydate.getSeconds();
    controlid.value = mydate.getFullYear()+'-'+(mmmm<10?'0'+mmmm:mmmm)+'-'+(dddd<10?'0'+dddd:dddd) + 
    (addtime ? ' ' + (hhhh<10?'0'+hhhh:hhhh) + ':' + (iiii<10?'0'+iiii:iiii) + ':'+(ssss<10?'0'+ssss:ssss) :'');
}

function showcalender(event, controlid1, addtime1, startdate1, enddate1) {
    today = new Date();
	controlid = controlid1;
	addtime = addtime1;
	startdate = startdate1 ? parsedate(startdate1) : false;
	enddate = enddate1 ? parsedate(enddate1) : false;
	currday = controlid.value ? parsedate(controlid.value) : today;
	hh = currday.getHours();
	ii = currday.getMinutes();
	ss = currday.getSeconds();
	var p = MyQEE.position(controlid);
	var iel = MyQEE.is_ie?-1:0;
	MyQEE.$('calender').style.display = 'block';
	MyQEE.$('calender').style.left = (p['x']+1+iel)+'px';
	MyQEE.$('calender').style.top  = (p['y']+22+iel)+'px';
	_cancelBubble(event);
	refreshcalender(currday.getFullYear(), currday.getMonth());
	if(lastcheckedyear != false) {
	    var tmpobj = MyQEE.$('calender_year_' + lastcheckedyear);
		if (tmpobj)tmpobj.className = 'default';
		var tmpobj = MyQEE.$('calender_year_' + today.getFullYear());
		if (tmpobj)tmpobj.className = 'today';
	}
	if(lastcheckedmonth != false) {
		MyQEE.$('calender_month_' + lastcheckedmonth).className = 'default';
		MyQEE.$('calender_month_' + (today.getMonth() + 1)).className = 'today';
	}
	var tmpobj = MyQEE.$('calender_year_' + currday.getFullYear());
	if (tmpobj)tmpobj.className = 'checked';
	MyQEE.$('calender_month_' + (currday.getMonth() + 1)).className = 'checked';
	MyQEE.$('hourminute').style.display = addtime ? '' : 'none';
	if (addtime)
	{
	    MyQEE.$('setnowtime_a').innerHTML = '当前时间';
	    MyQEE.$('setnowtime_b').style.display = ''; 
	    MyQEE.$('setnowtime_c').style.display = 'none'; 
	}
	else
    {
	    MyQEE.$('setnowtime_a').innerHTML = '今日';
	    MyQEE.$('setnowtime_b').style.display = 'none'; 
	    MyQEE.$('setnowtime_c').style.display = ''; 
    }
	lastcheckedyear = currday.getFullYear();
	lastcheckedmonth = currday.getMonth() + 1;
}

function wheelcalender(event, obj, addtime)
{
    currday = obj.value ? parsedate(obj.value) : new Date();
    var yy = currday.getFullYear();
    var mm = currday.getMonth()+1;
    var dd = currday.getDate();
    if (addtime)
    {
        var hh = currday.getHours();
        var ii = currday.getMinutes();
        var ss = currday.getSeconds();
    }
    var d = get_wheel_value(event)>0?1:-1;
    if ( obj.value!='' )
    {
        var ox = event.offsetX||event.layerX||0;
        if ( ox<=35 )
        {
            currday.setFullYear(currday.getFullYear()+d);
        }
        else if ( ox<=57 )
        {
            currday.setMonth(currday.getMonth()+d);
        }
        else if ( ox<=75 || !addtime )
        {
            currday.setDate(currday.getDate()+d);
        }
        else if ( ox<=97 )
        {
            currday.setHours(currday.getHours()+d);
        }
        else if ( ox<=120 )
        {
            currday.setMinutes(currday.getMinutes()+d);
        }
        else
        {
            currday.setSeconds(currday.getSeconds()+d);
        }
    }
    var yy = currday.getFullYear();
    var mm = currday.getMonth()+1;
    var dd = currday.getDate();
    if (addtime)
    {
        var hh = currday.getHours();
        var ii = currday.getMinutes();
        var ss = currday.getSeconds();
    }
    obj.value = yy+'-'+(mm<10?'0'+mm:mm)+'-'+(dd<10?'0'+dd:dd) + 
    (addtime ? ' ' + (hh<10?'0'+hh:hh) + ':' + (ii<10?'0'+ii:ii) + ':'+(ss<10?'0'+ss:ss) :'');

    return false;
}

function refreshcalender(y, m) {
	var x = new Date(y, m, 1);
	var mv = x.getDay();
	var d = x.getDate();
	var dd = null;
	yy = x.getFullYear();
	mm = x.getMonth();
	MyQEE.$("year").innerHTML = yy;
	MyQEE.$("month").innerHTML = mm + 1 > 9  ? (mm + 1) : '0' + (mm + 1);

	var last_m = new Date(y, m, 1);
	for(var i = mv; i >= 1; i--) {
	    last_m.setDate(last_m.getDate()-1);
		dd = MyQEE.$("d" + i);
		dd.innerHTML = '<a href="###" onclick="yy='+last_m.getFullYear()+';mm='+last_m.getMonth()+';settime(' + last_m.getDate() + ');return false">' + last_m.getDate() + '</a>';
		dd.className = 'lastmonth expire';
	}
	last_m = null;

	while(x.getMonth() == mm) {
		dd = MyQEE.$("d" + (d + mv));
		dd.innerHTML = '<a href="###" onclick="settime(' + d + ');return false">' + d + '</a>';
		if(x.getTime() < today.getTime() || (enddate && x.getTime() > enddate.getTime()) || (startdate && x.getTime() < startdate.getTime())) {
			dd.className = 'thismonth expire';
		} else {
			dd.className = 'thismonth default';
		}
		if(x.getFullYear() == today.getFullYear() && x.getMonth() == today.getMonth() && x.getDate() == today.getDate()) {
			dd.className = 'thismonth today';
			dd.firstChild.title = '今天';
		}
		if(x.getFullYear() == currday.getFullYear() && x.getMonth() == currday.getMonth() && x.getDate() == currday.getDate()) {
			dd.className += ' checked';
		}
		x.setDate(++d);
	}

    var next_m = new Date(y, m+1, 1);
	while(d + mv <= 42) {
		dd = MyQEE.$("d" + (d + mv));
        dd.innerHTML = '<a href="###" onclick="yy='+next_m.getFullYear()+';mm='+next_m.getMonth()+';settime(' + next_m.getDate() + ');return false">' + next_m.getDate() + '</a>';
        dd.className = 'nextmonth expire';
		d++;
		next_m.setDate(next_m.getDate()+1);
	}

	if(addtime) {
		MyQEE.$('hour').value = zerofill(hh);
		MyQEE.$('minute').value = zerofill(ii);
		MyQEE.$('second').value = zerofill(ss);
	}
}

function showdiv(id) {

	var p = MyQEE.position(MyQEE.$(id));
	MyQEE.$('calender_' + id).style.left = p['x']+'px';
	MyQEE.$('calender_' + id).style.top = (p['y'] + 16)+'px';
	MyQEE.$('calender_' + id).style.display = 'block';
}

function zerofill(s) {
	var s = parseFloat(s.toString().replace(/(^[\s0]+)|(\s+$)/g, ''));
	s = isNaN(s) ? 0 : s;
	return (s < 10 ? '0' : '') + s.toString();
}

function get_wheel_value(e) 
{ 
    e = e||event; 
    return ( e.wheelDelta ? e.wheelDelta/120 : -( e.detail%3 == 0 ? e.detail/3 : e.detail ) ) ; 
}

loadcalender();