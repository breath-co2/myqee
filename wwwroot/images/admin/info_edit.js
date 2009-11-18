var _my_advNum = {};
var _advArr = {};
var _advValue = {};
var _mynamestr = 'info';
function ini_adv_field(fieldstr,numstr){
	fieldstr = fieldstr||'';
	numstr = numstr||'';
	var myfstr = fieldstr.substr(1).split('.');
	var mynstr = numstr.substr(1).split('_');
	var arr;
	var val;
	if (fieldstr=='.'){
		return;
	}else{
		var myfs = '';
		var myns = '';
		var namestr = _mynamestr;
		for (var ii=0;ii<myfstr.length;ii++){
			myfs += '["'+myfstr[ii]+'"]';
			namestr += '['+myfstr[ii]+']'+(mynstr[ii]?'['+mynstr[ii]+']':'');
			myns += '["'+myfstr[ii]+'"]'+(mynstr[ii]?'['+mynstr[ii]+']':'');
		}
		eval("try{arr = _advArr"+myfs+";}catch(e){}");
		eval("try{val = _advValue"+myns+"||[{}];}catch(e){val=[{}];}");
	}
	if (typeof(arr)!='object')return '';
	if (typeof(arr['_set'])!='object'){
		//或略缺少设置的数组
		return '';
	}

	if(val.length==0)val=[{}];
//	_alert(val+'|'+fieldstr)
	if (typeof(val)!='object'){
		return;
	}
	var myform=document.forms['myeditform'];
	for(var i=0;i<val.length;i++){
		if (i<val.length-1){
			add_adv_field(fieldstr,numstr,true);
		}
		var myval=val[i];
		//_alert(JSON.stringify(arr))
		var newnumstr = numstr+'_'+i;
		for (var k in arr){
			if (k=='_set')continue;
			var myset = arr[k]['_set'];
			if(typeof(myset)!='object')continue;
			var newfieldstr = fieldstr+'.'+k;
			var newnamestr = namestr+'['+i+']['+k+']';
			if (myset['type']=='checkbox'){
				newnamestr +='[]';
			}
			var myinput = myform[newnamestr];
			myval[k] = myval[k]!=null&&myval[k]!=undefined?myval[k]:myset['default'];
			if (typeof(myinput)=='object'){
				if (myinput.length>0)
				{
					if(myinput[0].tagName=='OPTION'){
						//下拉框
						myinput.value = deformatstr(myval[k]);
						continue;
					}
					if(typeof(myval[k])!='object')myval[k] = ((myval[k]||'')+'').split("\n");

					for(var ii=0;ii<myinput.length;ii++){
						if (myinput[ii].type=='checkbox'){
							myinput[ii].checked = _in_array(myinput[ii].value,myval[k]);
						}else if (myinput[ii].type=='radio'){
							myinput[ii].checked = myinput[ii].value==myval[k]?true:false;
						}
					}
				}else{
					myinput.value = deformatstr(myval[k]);
				}
			}else{
				ini_adv_field(newfieldstr,newnumstr);
			}
		}
	}
}


function add_adv_field(fieldstr,numstr,isinivalue){
	fieldstr = fieldstr||'';
	numstr = numstr||'';
	var myfstr = fieldstr.substr(1).split('.');
	var arr;
	if (fieldstr=='.'){
		return;
	}else{
		var myfs = '';
		for (var ii=0;ii<myfstr.length;ii++){
			myfs += '["'+myfstr[ii]+'"]';
		}
		eval("try{arr = _advArr"+myfs+";}catch(e){}");
	}
	if (typeof(arr)!='object')return '';
	if (typeof(arr['_set'])!='object'){
		//或略缺少设置的数组
		return '';
	}
	if(arr['_set']['isfield'])return '';
	var tmphtml = '';
	var tmptr='';
	
	var num;
	var mynumstr = fieldstr+','+numstr;
	if (_my_advNum[mynumstr]>=0){
		_my_advNum[mynumstr]++;
		num = _my_advNum[mynumstr];
	}else{
		num = _my_advNum[mynumstr] = 0;
	}

	if(/^_[0-9]+$/.exec(numstr)){
		if(!_my_advNum['.'+myfstr[0]+',_'+numstr.substr(1)])
			_my_advNum['.'+myfstr[0]+',_'+numstr.substr(1)]=numstr.substr(1);
	}

	if (arr['_set']['num']>0){
		var myobj=$('_my_field_table'+fieldstr+','+numstr+'_DIV');
		if (myobj)
		{
			var therow=0;
			if (arr['_set']['type']=='1'){therow=2;}
			if (myobj.rows.length-therow>=arr['_set']['num']){
				if(!isinivalue)alert('本组最多创建'+arr['_set']['num']+'个子项！已达到最多，不允许创建！',400);
				return;
			}
		}
		myobj = null;
	}

	if (arr['_set']['type']=='1'){
		//横排
		if(num==0)tmphtml += '<table border="0" cellpadding="2" cellspacing="1" class="tableborder" style="width:auto;" id="_my_field_table'+fieldstr+','+numstr+'_DIV"><tr>'+(arr['_set']['isdel']==1?'<th width="20">选</th>':'');
	}else{
		//切换
		tmphtml += '<table border="0" cellpadding="2" cellspacing="1" class="tableborder" style="width:100%"><tr><th colspan="10" style="text-align:right"><div style="float:left;padding:1px 0 0 5px;">'+arr['_set']['name']+'</div>'+
			
		(arr['_set']['isorder']==1?'<input type="button" class="btns" value="向前移" onclick="var obj=$(\'_field_div'+fieldstr+','+numstr+'_'+num+'\');if(obj.previousSibling){id1=obj.id.replace(\'_field_div\',\'_field_page\');id2=obj.previousSibling.id.replace(\'_field_div\',\'_field_page\');window.swapNodes(obj,obj.previousSibling);'+(arr['_set']['type']=='2'?'':'var obj1=$(id1);var obj2=$(id2);var tmp=obj1.innerHTML;obj1.innerHTML=obj2.innerHTML;obj2.innerHTML=tmp;window.swapNodes(obj1,obj2);obj2=obj2=null;')+'}obj=null;" /><input type="button" class="btns" value="向后移" onclick="var obj=$(\'_field_div'+fieldstr+','+numstr+'_'+num+'\');if(obj.nextSibling){id1=obj.id.replace(\'_field_div\',\'_field_page\');id2=obj.nextSibling.id.replace(\'_field_div\',\'_field_page\');window.swapNodes(obj,obj.nextSibling);'+(arr['_set']['type']=='2'?'':'var obj1=$(id1);var obj2=$(id2);var tmp=obj1.innerHTML;obj1.innerHTML=obj2.innerHTML;obj2.innerHTML=tmp;window.swapNodes(obj1,obj2);obj2=obj2=null;')+'}obj=null;" />':'')+
		
		(arr['_set']['isadd']==1?'<input type="button" class="btn" value="添加新组" onclick="add_adv_field(\''+fieldstr+'\',\''+numstr+'\');changeHeight(true);" />':'')+
		
		(arr['_set']['isdel']==1?'<input type="button" class="btns" value="删除" onclick="confirm({\'title\':\'确实要删除？\',\'message\':\'确认要删除当前组？\',\'handler\':function(e){if(e!=\'ok\')return;'+(arr['_set']['type']=='2'?'':'var obj=$(\'_field_page'+fieldstr+','+numstr+'_'+num+'\');var nobj=obj.previousSibling||obj.nextSibling;if(nobj){nobj.onclick();}var liobj=obj.parentNode;obj.parentNode.removeChild(obj);')+'var obj=$(\'_field_div'+fieldstr+','+numstr+'_'+num+'\');'+(arr['_set']['type']=='2'?'obj.style.height=(obj.offsetHeight-4)+\'px\';obj.style.overflow=\'hidden\';doDelObj(obj,function(){window._check_adv_isaddbtn(\''+fieldstr+'\',\''+numstr+'\');window.changeHeight(true);});':'obj.parentNode.removeChild(obj);liobj=liobj.getElementsByTagName(\'LI\');for(var i=0;i<liobj.length;i++){liobj[i].innerHTML=\'-\'+(i+1)+\'-\';}window._check_adv_isaddbtn(\''+fieldstr+'\',\''+numstr+'\');window.changeHeight(true);')+';}})" />':'')+

		'</th></tr></table>';
	}

	var trhtml = '';
	var trarr = [];
	var autowidth = 100;
	for (var key in arr){
		if (typeof(arr[key])=='object'){
			if (typeof(arr[key]['_set'])!='object'){
				//或略缺少设置的数组
				continue;
			}
			var myset = arr[key]['_set'];
			var newfieldstr=fieldstr+'.'+myset['flag'];
			var objtype = 0;
			
			if ( typeof(arr[key]['_html'])=='string'){
				objtype = 1;
			}
			if (myset['editwidth']>0){
				mywidth = autowidth = myset['editwidth'];
			}else if(myset['editwidth']==''||myset['editwidth']==null){
				mywidth = autowidth;
			}else{
				mywidth = 0;
			}

			if (arr['_set']['type']=='1'){
				if(num==0)tmphtml += '<th'+(mywidth>0?' width='+mywidth:'')+'>'+arr[key]['_set']['name']+'<br/><img src="'+_admin_inmpath+'/admin/spacer.gif" style="height:0px;" width="'+mywidth+'" /></th>';
			}else{
				trhtml += '<table border="0" cellpadding="2" cellspacing="1" align="center" class="tableborder" style="width:100%;border-top:none;border-bottom:1px solid #c3d3dc;"><tr'+(myset['type']=='hidden'?' style="display:none;':'')+'>';
				if (mywidth>0){
					trhtml += '<td class="td1" align="right" width="'+mywidth+'" style="border-top:none">'+myset['name']+'：<br/><img src="'+_admin_inmpath+'/admin/spacer.gif" style="height:0px;" width="'+mywidth+'" /></td>';
				}
				trhtml += '<td class="td2" style="border-top:none;">';
			}

			trhtml += '<table style="width:100%" border="0" cellpadding="0" cellspacing="0" id="_my_field_div'+newfieldstr+','+numstr+'_'+num+'_DIV"><tr id="_field_div'+newfieldstr+','+numstr+'_'+num+'_0"><td>';

			if (objtype=='1'){
				//当前为字段的根部
				var myhtml = arr[key]['_html'];
				
				var preg = /\{\{\.[a-z0-9_\.]+\}\}/gi;
				var mactches = myhtml.match(preg);
				var tmpmac = '|';
				var numsp = numstr.split('_');
				for(var i=0;i<mactches.length;i++){
					var myfield = mactches[i].substr(2,mactches[i].length-4);
					if (tmpmac.indexOf('|'+myfield+'|')==-1)
					{
						tmpmac+=myfield+'|';

						var fieldsp = myfield.split('.');
						var tmpstr = '';
						for(var ii=0;ii<fieldsp.length;ii++){
							if(numsp[ii])tmpstr += '_'+numsp[ii];
						}
						var tmpnum = _my_advNum[myfield+','+tmpstr]||0;

						myhtml = myhtml.replace(eval('/\\\{\\\{'+(myfield.replace(/\./g,'\\.'))+'\\\}\\\}/g'),tmpnum);
					}
				}
				/*
				var kstr = '';
				var knumstr = '_0';
				for (var k=0;k<myfstr.length;k++){
					kstr += '.'+myfstr[k];
					var tmpnum = _my_advNum[kstr+','+knumstr]||0;
					knumstr += '_'+tmpnum;
					myhtml = myhtml.replace(eval('/\\\{\\\{'+(kstr.replace(/\./g,'\\.'))+'\\\}\\\}/g'),tmpnum);
				}
				*/

				trhtml += myhtml;
			}else{
				//当前还存在子数组
				trhtml += add_adv_field(newfieldstr,numstr+'_'+num,isinivalue);
			}
			trhtml += '</td></tr></table>';
			
			if (objtype!='1' && myset['type']=='0'){
				trhtml += '<ul class="ul tag2" id="_my_field_page'+newfieldstr+','+numstr+'_'+num+'"><li id="_field_page'+newfieldstr+','+numstr+'_'+num+'_0" class="now" onclick="tag(this.id,\'_field_page'+newfieldstr+','+numstr+'_'+num+'\',\'_field_div'+newfieldstr+','+numstr+'_'+num+'\',\'_my_field_page'+newfieldstr+','+numstr+'_'+num+'\');changeHeight(true);">-1-</li></ul>';
			}

			if (arr['_set']['type']=='1'){
				trarr[trarr.length] = trhtml;
				trhtml = '';
			}else{
				trhtml += '</td></tr></table>';
			}
		}
	}

	if (arr['_set']['type']=='1'){
		var isdotd = (arr['_set']['isorder']==1||arr['_set']['isdel']==1);
		var addbtn = '';
		if (arr['_set']['isdel']==1)addbtn='<input type="button" class="btn" value="添加新组" onclick="add_adv_field(\''+fieldstr+'\',\''+numstr+'\');changeHeight(true);" />';
		if(num==0)tmphtml += 
			(isdotd?'<th style="width:130px;">操作</th>':'')+'</tr>{{TR_TYPE1_HTML}}<tr'+(arr['_set']['isdel']==1||isdotd?'':' style="display:none;"')+'>'+
		
			(arr['_set']['isdel']==1?'<td class="td1"><input type="checkbox" onclick="field_select_all(\''+fieldstr+','+numstr+'\',this.checked)" /></td><td class="td1" colspan="'+trarr.length+'"><input type="button" value="删除选定" class="btn" onclick="confirm({\'title\':\'确实要删除？\',\'message\':\'确认要删除选中的组？\',\'handler\':function(e){if (e==\'ok\'){var trs=$(\'_my_field_table'+fieldstr+','+numstr+'_DIV\').rows;var len=trs.length-2;for(var i=len;i>0;i--){var inputs=trs[i].cells[0].getElementsByTagName(\'INPUT\');if (inputs){if (inputs[0].type==\'checkbox\' && inputs[0].checked)trs[i].parentNode.removeChild(trs[i]);}}window.changeHeight(true);}}},300,160)" />':'<td class="td1" colspan="'+trarr.length+'"><div class="nbsp"></div>')+
		
			(isdotd?'</td><td class="td1" align="center">'+addbtn+'</td>':'<div style="float:right">'+addbtn+'</div></td>')+
			
			'</tr></table>';
	}else{
		tmphtml += trhtml;
		
		var pagehtml;

		if (arr['_set']['type']!='2'){
			var pageobj = $('_my_field_page'+fieldstr+','+numstr);
			if (pageobj){
				var newpage = document.createElement('li');
				newpage.id = '_field_page'+fieldstr+','+numstr+'_'+num;
				newpage.innerHTML = '-'+(pageobj.getElementsByTagName('LI').length-0+1)+'-';
				newpage.onclick = function (){tag(this.id,'_field_page'+fieldstr+','+numstr,'_field_div'+fieldstr+','+numstr,'_my_field_page'+fieldstr+','+numstr);changeHeight(true);};
				pageobj.appendChild(newpage);
				if(!isinivalue)newpage.onclick();
				pageobj = null;
			}else{
				pagehtml = '<li id="_field_page'+fieldstr+','+numstr+'_'+num+'" class="now" onclick="tag(this.id,\'_field_page'+fieldstr+','+numstr+'\',\'_field_div'+fieldstr+','+numstr+'\',\'_my_field_page'+fieldstr+','+numstr+'\');changeHeight(true);">-1-</li>';
			}
		}

	}

	var obj = $('_my_field_'+(arr['_set']['type']=='1'?'table':'div')+fieldstr+','+numstr+'_DIV');
	if (obj){
		if (arr['_set']['type']=='1'){
			var tr = obj.insertRow(obj.rows.length - 1);
			tr.id = '_my_field_tr'+fieldstr+','+numstr+'_'+num;
			if(arr['_set']['isorder']==1){
				//调换位置的函数
				tr.swapNodes = function(item1){window.swapNodes(item1,this)};
				tr.move = function(type){
					var rowindex;
					if(type=='down'){
						rowindex=this.rowIndex+1;if (rowindex>=this.parentNode.rows.length-1)return;
					}else{
						rowindex=this.rowIndex-1;if (rowindex<=0)return;
					}
					var obj = this.parentNode.rows[rowindex];
					this.swapNodes(obj);
				}
			}
			var td;
			if (arr['_set']['isdel']==1){
				td = tr.insertCell(-1);
				td.innerHTML = '<input type="checkbox" />';
				td.className = 'td1';
			}
			for (var ir=0;ir<trarr.length;ir++){
				td = tr.insertCell(-1);
				td.className ='td2';
				td.innerHTML = trarr[ir];
			}
			
			if(arr['_set']['isorder']==1||arr['_set']['isdel']==1){
				td = tr.insertCell(-1);
				td.className ='td2';
				td.align ='center';
				td.style.whiteSpace ='nowrap';
				td.innerHTML = 
				'<input type="button" value="上移" class="btnss'+(arr['_set']['isorder']==1?'" onclick="this.parentNode.parentNode.move(\'up\');this.onmouseout();"':' btn_disabled"')+' />'+

				'<input type="button" value="下移" class="btnss'+(arr['_set']['isorder']==1?'" onclick="this.parentNode.parentNode.move(\'down\');this.onmouseout();"':' btn_disabled"')+' />'+
				
				'<input type="button" value="删除" class="btnss'+(arr['_set']['isdel']==1?'" onclick="confirm({\'title\':\'确实要删除？\',\'message\':\'确认要删除这一组？\',\'handler\':function(e){if (e==\'ok\'){var tr=$(\'_my_field_tr'+fieldstr+','+numstr+'_'+num+'\');tr.parentNode.removeChild(tr);changeHeight(true);}}},300,160)"':' btn_disabled"')+' />';
			
			}
			myqee(tr);
			obj = tr = td = null;
		}else{
			//切换式
			if (!isinivalue&&arr['_set']['type']!='2'){
				if (num>0){
					//将之前的隐藏掉
					for (var i=0;i<num;i++){
						var obj2 = $('_field_div'+fieldstr+','+numstr+'_'+i);
						if (obj2)obj2.style.display='none';
					}
				}
			}
			//var newobj = document.createElement('div');
			var newtr = obj.insertRow(-1);
			newtr.id = '_field_div'+fieldstr+','+numstr+'_'+num;
			if(num>0&&isinivalue)newtr.style.display = 'none';
			var newobj = newtr.insertCell(-1);
			//newobj.style.padding ='2px';
			newobj.innerHTML = tmphtml;
			myqee(newobj);
			newtr = newobj = null;

			if (pagehtml && !$('_my_field_page'+fieldstr+','+numstr)){
				newobj = document.createElement('ul');
				newobj.id = '_my_field_page'+fieldstr+','+numstr;
				newobj.className = 'ul tag2';
				newobj.innerHTML = pagehtml;
				obj.parentNode.appendChild(newobj);
			}
		}
		tmphtml = '';
	}else{
		if (arr['_set']['type']=='1'){
			tmptr += '<tr id="_my_field_tr'+fieldstr+','+numstr+'_'+num+'">'+(arr['_set']['isdel']==1?'<td class="td1"><input type="checkbox" /></td>':'');
			for (var ir=0;ir<trarr.length;ir++){
				tmptr += '<td class="td2">'+trarr[ir]+'</td>';
			}
			if(arr['_set']['isorder']==1||arr['_set']['isdel']==1){
				tmptr += '<td class="td2" style="white-space:nowrap" align="center"'+(arr['_set']['isorder']==1?' onmousedown="var _mytr= this.parentNode;if(_mytr.up){return;}else{_mytr.swapNodes=function(t1){window.swapNodes(t1,_mytr);};_mytr.move=function(type){var rowindex;if(type==\'down\'){rowindex=_mytr.rowIndex+1;if (rowindex>=_mytr.parentNode.rows.length-1)return;}else{rowindex=_mytr.rowIndex-1;if (rowindex<=0)return;}var obj = _mytr.parentNode.rows[rowindex];_mytr.swapNodes(obj);};}"':'')+'>'+
				
				'<input type="button" value="上移" class="btnss'+(arr['_set']['isorder']==1?'" onclick="this.parentNode.parentNode.move();this.onmouseout();"':' btn_disabled"')+' />'+
					
				'<input type="button" value="下移" class="btnss'+(arr['_set']['isorder']==1?'" onclick="this.parentNode.parentNode.move(\'down\');this.onmouseout();"':' btn_disabled"')+' />'+
					
				'<input type="button" value="删除" class="btnss'+(arr['_set']['isdel']==1?'" onclick="confirm({\'title\':\'确实要删除？\',\'message\':\'确认要删除这一组？\',\'handler\':function(e){if (e==\'ok\'){var tr=$(\'_my_field_tr'+fieldstr+','+numstr+'_'+num+'\');tr.parentNode.removeChild(tr);window.changeHeight(true);}}},300,160)"':' btn_disabled"')+' /></td></tr>';
			}
			tmphtml = tmphtml.replace(/\{\{TR_TYPE1_HTML\}\}/g,tmptr);
		}else if(numstr==''){
			tmphtml = '<table style="width:100%" border="0" cellpadding="0" cellspacing="0" id="_my_field_div'+fieldstr+',_DIV"><tr id="_field_div'+fieldstr+','+numstr+'_0"><td>'+tmphtml+'</td></tr></table>';
			
			if (arr['_set']['type']!='2')tmphtml += '<ul class="ul tag2" id="_my_field_page'+fieldstr+','+numstr+'"><li id="_field_page'+fieldstr+','+numstr+'_'+num+'" class="now" onclick="tag(this.id,\'_field_page'+fieldstr+','+numstr+'\',\'_field_div'+fieldstr+','+numstr+'\',\'_my_field_page'+fieldstr+','+numstr+'\');changeHeight(true);">-1-</li></ul>';
		}
	}
	return tmphtml;
}


function field_select_all(objstr,ischeck){
	var obj = $('_my_field_table'+objstr+'_DIV');
	if (!obj)return;
	for (var i=1;i<obj.rows.length-1 ;i++ )
	{
		var td = obj.rows[i].cells[0];
		var input = td.getElementsByTagName('INPUT');
		if (input)
		{
			if (input[0].type='checkbox')
			{
				input[0].checked = ischeck?true:false;
			}
		}
	}
}


function _check_adv_isaddbtn(s1,s2){
	var obj = $('_my_field_div'+s1+','+s2+'_DIV');
	if (!obj)return;
	divs = obj.getElementsByTagName('DIV');
	if (divs.length==0)
	{
		var nowobj = document.createElement('span');
		nowobj.innerHTML = '<input type="button" class="btn" value="添加新组" onclick="this.parentNode.parentNode.removeChild(this.parentNode);add_adv_field(\''+s1+'\',\''+s2+'\');changeHeight(true);" />';
		obj.parentNode.insertBefore(nowobj,obj);
		myqee(nowobj);
	}
}


function _in_array(needle,arr) {
	if(typeof needle == 'string' || typeof needle == 'number') {
		for(var i in arr) {
			if(deformatstr(arr[i])==needle){
				return true;
			}
		}
	}
	return false;
}


function deformatstr(str){
	return str.replace(/&#92;/g,"\"").replace(/&#39;/g,'\'').replace(/&#124;/g,'|').replace(/&#92;/g,"\\").replace(/&lt;/g,'<').replace(/&gt;/g,'>').replace(/&amp;/g,'&');
}