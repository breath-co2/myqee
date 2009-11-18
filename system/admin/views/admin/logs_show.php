<?php
$page_index or $page_index = 'index';
$page_title = '查看日志';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; 
<a href="<?php echo Myqee::url('index/logs');?>">日志管理</a> -&gt; 
查看日志
</div>

<script type="text/javascript">
	var makesuredelete='您确认要删除此日志？';
</script>
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
<tr>
	<th class="td1" width="40">序号</th>
	<th class="td1" width="60">日志类型</th>
	<th class="td1">日志信息</th>
	<th class="td1" width="200">记录时间</th>
</tr>
</table>
<div id="logshow_div"></div>


<script type="text/javascript">
var log_ii = 0;
var more_code = '<?php echo $code;?>';
var more_time = '<?php echo $time;?>';
var more_ftell = '<?php echo $ftell;?>';

function showlog(arr){
	if (typeof(arr) != 'object')return;
	
	var obj = $('logshow_div');
	var old_ii=log_ii;
	var oldheight = dom.scrollHeight;
	var tmphtml = '';
	var beginnum = log_ii+1;
	for (var i in arr){
		log_ii++;
		tmphtml += '<tr align="center"'+(log_ii%2==0?' class="td3"':'')+' onmouseover="tr_moveover(this)" onmouseout="tr_moveout(this)">'+
		'	<td class="td1" width="40">'+log_ii+'</td>'+
		'	<td class="td1" width="60">'+arr[i]['type']+'</td>'+
		'	<td class="td2" align="left">'+arr[i]['info']+'</td>'+
		'	<td class="td2" width="200">'+arr[i]['time']+'</td>'+
		'</tr>';
	}
	if (tmphtml=='')return;
	tmphtml = '<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-top:none;"><tr><th colspan="4">第'+beginnum+'到'+log_ii+'条记录</th></tr>'+tmphtml+'</table>';
	var div = document.createElement("div");
	div.innerHTML = tmphtml;
	obj.appendChild(div);
	$('log_readmore_btn').title = '查看第'+(log_ii+1)+'到'+(log_ii+100)+'条记录！';
	
	set_control_fixed();
	if (old_ii>0){
		
		scrollwin( div.offsetTop-67 );
	}
}

function scrollwin(topscoll,n){
	if (!topscoll>0)return;
	n = n || 0;
	if (n>10)return;
	window.scrollTo(dom.scrollLeft,dom.scrollTop+(topscoll-dom.scrollTop)/2);
	n++;
	setTimeout('scrollwin('+topscoll+','+n+')',20);
}

var dosetTimeout;
function readmore(){
	clearTimeout(dosetTimeout);
	ajax.method = 'GET';
	ajax.requestFile = '<?php echo Myqee::url('index/logs_more?log='.$logfile);?>&code='+more_code+'&time='+more_time+'&ftell='+more_ftell;
	ajax.onCompletion = function (){
		var arr;
		var iserror=false;
		try{
			arr = eval('('+this.response+')');
			if (typeof(arr)!='object'){
				iserror = true;
			}else if(arr['error']){
				iserror = true;
			}
		}catch(e){
			iserror = true;
		};
		if (iserror==true){
			this.onError(arr['error']);
		}else{
			showlog(arr['log']);
			if (arr['isall']){
				$("showmore_div").style.display="none";
			}
			more_code = arr['code']||'';
			more_time = arr['time']||'';
			more_ftell = arr['ftell']||0;

			if ($('renewinput').value>0&&$('renewinput').value<100)
				dosetTimeout = setTimeout('readmore()',$('renewinput').value*1000);
		}
		var d = new Date;
		$('renewtimediv').innerHTML = d.getHours()+':'+d.getMinutes()+':'+d.getSeconds();
	}
	ajax.onError = function(info){
		info = info||'读取列表失败,可能已登录超时，请重试！';
		alert(info,400);
		$("showmore_div").style.display='';
	}
	ajax.runAJAX();
}

</script>
<div style="height:40px">
	<div id="control_div" style="width:100%;min-width:800px" class="control">
	<img src="<?php echo ADMIN_IMGPATH;?>/admin/spacer.gif" style="width:800px;height:0px" /><br/>

	<div>
	<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-top:none;">
	<tr>
		<td class="td1" align="center">
		<div style="float:left;padding:2px 0 0 3px" >
		每 
		<input type="text" id="renewinput" onchange="if(this.value>0&&this.value<100){setTimeout('readmore()',this.value*1000);}else{clearTimeout(dosetTimeout);}" value="<?php echo ($renewtime>0&&$renewtime<100?$renewtime:$renewtime=0);?>" maxlength="2" class="input" size="2" title="0表示不自动更新，最大99秒" /> 
		秒自动更新一次！最后读取时间：<span id="renewtimediv"><?php echo date("H:i:s");?></span></div>
		<span id="showmore_div" style="float:right;padding:1px 0 0 0;display:none;">
		<input onclick="readmore()" id="log_readmore_btn" type="button" class="btnl" value="列取更多..." />
		</span>
		</td>
		<td class="td1" width="200" align="center">
		<input onclick="ask_del('<?php echo $logfile;?>','index/logs_del/?close=yes&log=',makesuredelete);" type="button" value="删除日志" class="bbtn" />
		</td>
	</tr>
	</table>
	</div>

</div>
</div>

<script type="text/javascript">

set_control_fixed();
window.onscroll = set_control_fixed;

showlog(<?php echo $list;?>);

<?php 
if ($readall!=true){
	echo '$("showmore_div").style.display="";';
}
if($renewtime){
	echo 'readmore();';
}
?>


</script>


<?php View::factory('admin/footer') -> render(TRUE);?>