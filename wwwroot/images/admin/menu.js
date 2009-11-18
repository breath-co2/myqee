/*
	$RCSfile: menu.js,v $
	$Revision: 1.7 $
	$Date: 2009/09/07 00:44:18 $
*/

var jsmenu = new Array();
var ctrlobjclassName = new Array();
var isMenufilter=true;
jsmenu['active'] = new Array();
jsmenu['timer'] = new Array();
jsmenu['iframe'] = new Array();

function initCtrl(ctrlobj, click, duration, timeout, layer) {
	if(ctrlobj && !ctrlobj.initialized) {
		ctrlobj.initialized = true;
		ctrlobj.unselectable = true;

		ctrlobj.outfunc = typeof ctrlobj.onmouseout == 'function' ? ctrlobj.onmouseout : null;
		ctrlobj.onmouseout = function() {
			if(this.outfunc) this.outfunc();
			if(duration < 3) jsmenu['timer'][ctrlobj.id] = setTimeout('hideMenu("' + layer + '")', timeout);
		}

		if(click && duration) {
			ctrlobj.clickfunc = typeof ctrlobj.onclick == 'function' ? ctrlobj.onclick : null;
			ctrlobj.onclick = function (e) {
				doane(e);
				if(jsmenu['active'][layer] == null || jsmenu['active'][layer].ctrlkey != this.id) {
					if(this.clickfunc) this.clickfunc();
					else showMenu(this.id, true);
				} else {
					hideMenu(layer);
				}
			}
		}

		ctrlobj.overfunc = typeof ctrlobj.onmouseover == 'function' ? ctrlobj.onmouseover : null;
		ctrlobj.onmouseover = function(e) {
			doane(e);
			if(this.overfunc) this.overfunc();
			if(click) {
				clearTimeout(jsmenu['timer'][this.id]);
			} else {
				for(var id in jsmenu['timer']) {
					if(jsmenu['timer'][id]) clearTimeout(jsmenu['timer'][id]);
				}
			}
		}
	}
}

function initMenu(ctrlid, menuobj, duration, timeout, layer) {
	if(menuobj && !menuobj.initialized) {
		menuobj.initialized = true;
		menuobj.ctrlkey = ctrlid;
		menuobj.onclick = ebygum;
		menuobj.style.position = 'absolute';
		if(duration < 3) {
			if(duration > 1) {
				menuobj.onmouseover = function() {
					clearTimeout(jsmenu['timer'][ctrlid]);
				}
			}
			if(duration != 1) {
				menuobj.onmouseout = function() {
					jsmenu['timer'][ctrlid] = setTimeout('hideMenu("' + layer + '")', timeout);
				}
			}
		}
		menuobj.style.zIndex = 50;
		if(is_ie && isMenufilter) {
			menuobj.style.filter += "progid:DXImageTransform.Microsoft.shadow(direction=135,color=#cccccc,strength=2)";
		}
	}
}

function showMenu(ctrlid, click, offset, duration, timeout, layer, showid, maxh,isfixed) {
	var ctrlobj = $(ctrlid);
	if(!ctrlobj) return;
	click = click || false;
	offset = offset || 0;
	duration = duration || 2;
	timeout = timeout || 500;
	layer = layer || 0;
	showid = showid || ctrlid;
	isfixed = isfixed || false;
	var showobj = $(showid);
	var menuobj = $(showid + '_menu');
	if(!showobj|| !menuobj) return;
	maxh = maxh || 400;

	for(var lay in jsmenu['active']){
		if(jsmenu['active'][lay])hideMenu(lay);
	}

	for(var id in jsmenu['timer']) {
		if(jsmenu['timer'][id]) clearTimeout(jsmenu['timer'][id]);
	}

	initCtrl(ctrlobj, click, duration, timeout, layer);
	ctrlobjclassName = ctrlobj.className[layer];
	ctrlobj.className += ' hover';
	initMenu(ctrlid, menuobj, duration, timeout, layer);

	if(!is_opera) {
		menuobj.style.clip = 'rect(auto, auto, auto, auto)';
	}


	menuobj.style.display = '';
	menuobj.style.visibility = 'hidden';
	setMenuPosition(showid, offset,isfixed);
	menuobj.style.visibility = 'visible';

	if(is_ie && ie < 7) {
		if(!jsmenu['iframe'][layer]) {
			var iframe = document.createElement('iframe');
			iframe.style.display = 'none';
			iframe.style.position = 'absolute';
			iframe.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(style=0,opacity=0)';			
			$('append_parent') ? $('append_parent').appendChild(iframe) : menuobj.parentNode.appendChild(iframe);
			jsmenu['iframe'][layer] = iframe;
		}
		jsmenu['iframe'][layer].style.top = menuobj.style.top;
		jsmenu['iframe'][layer].style.left = menuobj.style.left;
		jsmenu['iframe'][layer].style.width = menuobj.w+'px';
		jsmenu['iframe'][layer].style.height = menuobj.h+'px';
		jsmenu['iframe'][layer].style.display = 'block';
		iframe = null;
	}

	if(maxh && menuobj.scrollHeight > maxh) {
		menuobj.style.height = maxh + 'px';
		if(is_opera) {
			menuobj.style.overflow = 'auto';
		} else {
			menuobj.style.overflowY = 'auto';
		}
	}

	if(!duration) {
		setTimeout('hideMenu("' + layer + '")', timeout);
	}

	jsmenu['active'][layer] = menuobj;
}

function setMenuPosition(showid, offset ,isfixed) {
	var showobj = $(showid);
	var menuobj = $(showid + '_menu');
	offset = offset || 0;
	offset2="N_"+offset;
	if(showobj) {
		showobj.pos = fetchOffset(showobj ,isfixed);
		showobj.X = showobj.pos['left'];
		showobj.Y = showobj.pos['top'];
		showobj.w = showobj.offsetWidth;
		showobj.h = showobj.offsetHeight;
		menuobj.w = menuobj.offsetWidth;
		menuobj.h = menuobj.offsetHeight;
		menuobj.style.left = (showobj.X + menuobj.w > document.body.clientWidth) && (showobj.X + showobj.w - menuobj.w >= 0) ? showobj.X + showobj.w - menuobj.w + 'px' : showobj.X + 'px';
		if (offset == 1){
			menuobj.style.top=showobj.Y + 'px';
		}else if(offset == 2 || ((showobj.Y + showobj.h + menuobj.h > document.documentElement.scrollTop + document.documentElement.clientHeight) && (showobj.Y - menuobj.h >= 0)))
		{
			menuobj.style.top=(showobj.Y - menuobj.h) + 'px'
			if (offset == 3){
				menuobj.style.left=showobj.X+ showobj.w + 'px';
			}
		}else if (offset == 3){
			menuobj.style.top=showobj.Y + 'px';
			menuobj.style.left=showobj.X+ showobj.w + 'px';
		}else if (offset2.indexOf("|")>=0){
			var offsetX=offset.split("|")[0];
			var offsetY=offset.split("|")[1];
			offsetX=offsetX-0;
			offsetY=offsetY-0;
			menuobj.style.top=showobj.Y + offsetY + 'px';
			menuobj.style.left=showobj.X + offsetX + 'px';
		}else{
			menuobj.style.top=showobj.Y + showobj.h + 'px';
		}
		//menuobj.style.top = offset == 1 ? showobj.Y + 'px' : (offset == 2 || ((showobj.Y + showobj.h + menuobj.h > document.documentElement.scrollTop + document.documentElement.clientHeight) && (showobj.Y - menuobj.h >= 0)) ? (showobj.Y - menuobj.h) + 'px' : showobj.Y + showobj.h + 'px');
		if(menuobj.style.clip && !is_opera) {
			menuobj.style.clip = 'rect(auto, auto, auto, auto)';
		}
	}
}

function hideMenu(layer) {
	layer = layer|| 0;
	if(jsmenu['active'][layer]) {
		try {
			$(jsmenu['active'][layer].ctrlkey).className = ctrlobjclassName[layer];
		} catch(e) {}
		clearTimeout(jsmenu['timer'][jsmenu['active'][layer].ctrlkey]);
		if (!jsmenu['active'][layer].style)return;
		jsmenu['active'][layer].style.display = 'none';
		if(is_ie && ie < 7 && jsmenu['iframe'][layer]) {
			jsmenu['iframe'][layer].style.display = 'none';
		}
		jsmenu['active'][layer] = null;
	}
}

function fetchOffset(obj,isfixed) {
	var left_offset = obj.offsetLeft;
	var top_offset = obj.offsetTop;
	while((obj = obj.offsetParent) != null) {
		left_offset += obj.offsetLeft;
		top_offset += obj.offsetTop;
	}
	if (is_ie && ie<7 && isfixed)
	{
		var dom = (document.compatMode &&document.compatMode=="CSS1Compat")?document.documentElement:document.body;
		return { 'left' : left_offset- dom.scrollLeft, 'top' : top_offset - dom.scrollTop };
	}else{
		return { 'left' : left_offset, 'top' : top_offset };
	}
}

function ebygum(eventobj) {
	if(!eventobj || is_ie) {
		window.event.cancelBubble = true;
		return window.event;
	} else {
		if(eventobj.target.type == 'submit') {
			eventobj.target.form.submit();
		}
		eventobj.stopPropagation();
		return eventobj;
	}
}

function menuoption_onclick_function(e) {
	this.clickfunc();
	hideMenu();
}

function menuoption_onclick_link(e) {
	choose(e, this);
}

function menuoption_onmouseover(e) {
	this.className = 'popupmenu_highlight';
}

function menuoption_onmouseout(e) {
	this.className = 'popupmenu_option';
}

function choose(e, obj) {
	var links = obj.getElementsByTagName('a');
	if(links[0]) {
		if(is_ie) {
			links[0].click();
			window.event.cancelBubble = true;
		} else {
			if(e.shiftKey) {
				window.open(links[0].href);
				e.stopPropagation();
				e.preventDefault();
			} else {
				window.location = links[0].href;
				e.stopPropagation();
				e.preventDefault();
			}
		}
		hideMenu();
	}
}
