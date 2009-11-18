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

function _cancelBubble(event) {
	e = event ? event : window.event ;
	if(ie) {
		e.cancelBubble = true;
	} else {
		e.stopPropagation();
	}
}

function getposition(obj) {
	var r = new Array();
	r['x'] = obj.offsetLeft;
	r['y'] = obj.offsetTop;
	while(obj = obj.offsetParent) {
		r['x'] += obj.offsetLeft;
		r['y'] += obj.offsetTop;
	}
	return r;
}


function loadcalendar() {

	s = '';
	s += '<div id="calendar" style="display:none; position:absolute; z-index:9;" onclick="_cancelBubble(event)">';
	if (ie)
	{
		s += '<iframe width="190" height="160" src="about:blank" style="position: absolute;z-index:-1;"></iframe>';
	}
	s += '<div style="width: 200px;"><table class="tableborder calender" cellspacing="0" cellpadding="0" width="100%" style="text-align: center">';
	s += '<tr><th><a href="#" onclick="refreshcalendar(yy, mm-1);return false" title="上一月">&lt;&lt;</a></th><th colspan="5" style="text-align: center"><a href="#" onclick="showdiv(\'year\');_cancelBubble(event);return false" title="点击选择年份" id="year"></a>&nbsp; - &nbsp;<a id="month" title="点击选择月份" href="#" onclick="showdiv(\'month\');_cancelBubble(event);return false"></a></th><th><a href="#" onclick="refreshcalendar(yy, mm+1);return false" title="下一月">&gt;&gt;</a></th></tr>';
	s += '<tr class="category"><td>日</td><td>一</td><td>二</td><td>三</td><td>四</td><td>五</td><td>六</td></tr>';
	for(var i = 0; i < 6; i++) {
		s += '<tr class="altbg2">';
		for(var j = 1; j <= 7; j++)
			s += "<td id=d" + (i * 7 + j) + " height=\"19\">0</td>";
		s += "</tr>";
	}
	s += '<tr id="hourminute"><td colspan="7" align="center"><input type="text" size="1" value="" id="hour" onfocus="this.select()" onchange=\'this.value=this.value > 23 ? 23 : zerofill(this.value);controlid.value=controlid.value.replace(/\\d+(\:\\d+\:\\d+)/ig, this.value+"$1")\' maxlength="2" /> 点 <input type="text" size="1" value="" id="minute" onfocus="this.select()" onchange=\'this.value=this.value > 59 ? 59 : zerofill(this.value);controlid.value=controlid.value.replace(/(\\d+\:)\\d+(\:\\d+)/ig, "$1"+this.value+"$2")\' maxlength="2" /> 分 <input type="text" size="1" value="" id="second" onfocus="this.select()" onchange=\'this.value=this.value > 59 ? 59 : zerofill(this.value);controlid.value=controlid.value.replace(/(\\d+\:\\d+\:)\\d+/ig, "$1"+this.value)\' maxlength="2" /> 秒</td></tr>';
	s += '</table></div></div>';
	s += '<div id="calendar_year" onclick="_cancelBubble(event)"><div class="col">';
	for(var k = 1980; k <= 2029; k++) {
		s += k != 1980 && k % 10 == 0 ? '</div><div class="col">' : '';
		s += '<a href="#" onclick="refreshcalendar(' + k + ', mm);$(\'calendar_year\').style.display=\'none\';return false"><span' + (today.getFullYear() == k ? ' class="today"' : '') + ' id="calendar_year_' + k + '">' + k + '</span></a><br />';
	}
	s += '</div></div>';
	s += '<div id="calendar_month" onclick="_cancelBubble(event)">';
	for(var k = 1; k <= 12; k++) {
		s += '<a href="#" onclick="refreshcalendar(yy, ' + (k - 1) + ');$(\'calendar_month\').style.display=\'none\';return false"><span' + (today.getMonth()+1 == k ? ' class="today"' : '') + ' id="calendar_month_' + k + '">' + k + ( k < 10 ? '&nbsp;' : '') + ' 月</span></a><br />';
	}
	s += '</div>';

	var nElement = document.createElement("div");
	nElement.innerHTML=s;
	$('appendChildDiv').appendChild(nElement);

//	document.write(s);
	document.onclick = function(event) {
		$('calendar').style.display = 'none';
		$('calendar_year').style.display = 'none';
		$('calendar_month').style.display = 'none';
	}
	$('calendar').onclick = function(event) {
		_cancelBubble(event);
		$('calendar_year').style.display = 'none';
		$('calendar_month').style.display = 'none';
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
	$('calendar').style.display = 'none';
	controlid.value = yy + "-" + zerofill(mm + 1) + "-" + zerofill(d) + (addtime ? ' ' + zerofill($('hour').value) + ':' + zerofill($('minute').value) + ':'+zerofill($('second').value) : '');
}

function showcalendar(event, controlid1, addtime1, startdate1, enddate1) {
	controlid = controlid1;
	addtime = addtime1;
	startdate = startdate1 ? parsedate(startdate1) : false;
	enddate = enddate1 ? parsedate(enddate1) : false;
	currday = controlid.value ? parsedate(controlid.value) : today;
	hh = currday.getHours();
	ii = currday.getMinutes();
	ss = currday.getSeconds();
	var p = getposition(controlid);
	$('calendar').style.display = 'block';
	$('calendar').style.left = p['x']+'px';
	$('calendar').style.top	= (p['y'] + 20)+'px';
	_cancelBubble(event);
	refreshcalendar(currday.getFullYear(), currday.getMonth());
	if(lastcheckedyear != false) {
		$('calendar_year_' + lastcheckedyear).className = 'default';
		$('calendar_year_' + today.getFullYear()).className = 'today';
	}
	if(lastcheckedmonth != false) {
		$('calendar_month_' + lastcheckedmonth).className = 'default';
		$('calendar_month_' + (today.getMonth() + 1)).className = 'today';
	}
	$('calendar_year_' + currday.getFullYear()).className = 'checked';
	$('calendar_month_' + (currday.getMonth() + 1)).className = 'checked';
	$('hourminute').style.display = addtime ? '' : 'none';
	lastcheckedyear = currday.getFullYear();
	lastcheckedmonth = currday.getMonth() + 1;
}

function refreshcalendar(y, m) {
	var x = new Date(y, m, 1);
	var mv = x.getDay();
	var d = x.getDate();
	var dd = null;
	yy = x.getFullYear();
	mm = x.getMonth();
	$("year").innerHTML = yy;
	$("month").innerHTML = mm + 1 > 9  ? (mm + 1) : '0' + (mm + 1);

	for(var i = 1; i <= mv; i++) {
		dd = $("d" + i);
		dd.innerHTML = "&nbsp;";
		dd.className = "";
	}

	while(x.getMonth() == mm) {
		dd = $("d" + (d + mv));
		dd.innerHTML = '<a href="###" onclick="settime(' + d + ');return false">' + d + '</a>';
		if(x.getTime() < today.getTime() || (enddate && x.getTime() > enddate.getTime()) || (startdate && x.getTime() < startdate.getTime())) {
			dd.className = 'expire';
		} else {
			dd.className = 'default';
		}
		if(x.getFullYear() == today.getFullYear() && x.getMonth() == today.getMonth() && x.getDate() == today.getDate()) {
			dd.className = 'today';
			dd.firstChild.title = '今天';
		}
		if(x.getFullYear() == currday.getFullYear() && x.getMonth() == currday.getMonth() && x.getDate() == currday.getDate()) {
			dd.className = 'checked';
		}
		x.setDate(++d);
	}

	while(d + mv <= 42) {
		dd = $("d" + (d + mv));
		dd.innerHTML = "&nbsp;";
		d++;
	}

	if(addtime) {
		$('hour').value = zerofill(hh);
		$('minute').value = zerofill(ii);
		$('second').value = zerofill(ss);
	}
}

function showdiv(id) {

	var p = getposition($(id));
	$('calendar_' + id).style.left = p['x']+'px';
	$('calendar_' + id).style.top = (p['y'] + 16)+'px';
	$('calendar_' + id).style.display = 'block';
}

function zerofill(s) {
	var s = parseFloat(s.toString().replace(/(^[\s0]+)|(\s+$)/g, ''));
	s = isNaN(s) ? 0 : s;
	return (s < 10 ? '0' : '') + s.toString();
}

loadcalendar();