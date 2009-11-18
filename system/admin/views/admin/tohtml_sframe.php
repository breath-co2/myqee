<?php
$page_index = 'task';
$page_title = '批量生成专题页';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<script type="text/javascript">
var allTime = 0;				//总耗时
var allDoPageOk = 0;			//生成成功数
var allDopageError = 0;			//生成失败数
var allDoingOk = 0;				//任务完成数
var theDoingOk = 0;				//当前任务完成比例
var doingTime = {};				//各任务完成时间数

function doit(theurl){
//	ajax.setVar("myTextBox", '123');
	ajax.requestFile = theurl;
	ajax.method = 'GET';
	ajax.onLoading = function (){
		//obj =$('mainInfoDiv');
		//if (obj)obj.innerHTML = '';
	};

	ajax.onError = function (){
		obj =$('mainInfoDiv');
		alert('模板执行生成错误！',null,null,null,function(){
			if (obj)obj.innerHTML = '执行错误'+ajax.response;
		});
	};

	ajax.onCompletion = function (){
		var info = this.response;
		try{
			info = eval ('('+info+')');
		}catch(e){
		}
		var obj =$('mainInfoDiv');
		if (!obj)return;
		if (typeof info == 'object') {

			if (info.docancel == true){
				myinfo  = '当前执行项目已取消，原因如下：<br/><br/>'+(info.errorinfo||'')+'<br/><br/><br/><br/>';
			}else{
				myinfo  = '已完成项目：'+(info.doinfo||'')+'<br/>';
				myinfo += '此次总任务数：<b>'+(info.allcount||'')+'</b><br/>';
				myinfo += '执行成功数：<b>'+(info.dook||'')+'</b><br/>';
				myinfo += '执行失败数：<b>'+(info.doerror||'')+'</b><br/>';
				myinfo += '本次执行时间：<b>'+(info.runtime||'')+'</b> 秒<br/>';
				myinfo += '完成时间：'+(info.dotime||'')+'<br/>';
			}

			obj.innerHTML = myinfo;

			if (info.runtime>0){
				allTime = info.runtime-0+allTime;
				//alltimestring =  allTime.split('.');
				$('allusetime').innerHTML = allTime.toFixed(4);
			}
			if (info.dook>0){
				allDoPageOk = info.dook-0+allDoPageOk;
				$('alldopage').innerHTML = allDoPageOk;
			}
			if (info.doerror>0){
				allDopageError = info.doerror-0+allDopageError;
				$('alldopageerror').innerHTML = allDopageError;
			}

			if (info.thisdoingok==true){
				allDoingOk += 1;
				theDoingOk = 0;
			}else{
				theDoingOk = info.thedoingpage/info.allcount;
			}
			if (info.specialid>0){
				updatadoing(info.specialid,info.thedoingpage+'/'+info.allcount,0,info.runtime);
			}

			if (info.nexturl){
				doit(info.nexturl);
			}else if (info.alldook==true){
				tmphtml = '<center style="margin:auto;padding:25px 0;">恭喜，任务执行完毕，共成功生成页面：<b style="color:red">'+allDoPageOk+'</b>，执行失败：<b style="color:red">'+allDopageError+'</b>，总用时：<b style="color:red">'+allTime.toFixed(4)+'</b> 秒<br/><br/><br/><input type="button" value="全部重新执行" onclick="document.location.href=document.location.href" class="btnl" /> <input type="button" value="关闭网页" onclick="window.close()" class="btn" /></center>';
				var obj1 = $('mainDoingDiv');
				if (obj1)obj1.innerHTML = tmphtml;
				obj.innerHTML += '<br/><b>任务已全部完成！</b>';

				alert('恭喜，任务执行完毕！',null,null,null,function(){
					myqee(obj1);
				});
			}
		}else{
			obj.innerHTML = info;
		}

	};

	ajax.onStop = function (){
		alert('已取消!');
	};

	ajax.runAJAX();
}

function updatadoing(specialid,count,offset,runtime){
	$obj_count = $('do_count_'+specialid);
	$obj_time = $('do_time_'+specialid);
	if (!$obj_count || !$obj_time)return;
	$obj_count.innerHTML = count;

	if (runtime>0){
		runtime = parseFloat(runtime);
		if (doingTime['specialid_'+specialid]>0){
			doingTime['specialid_'+specialid] += runtime;
		}else{
			doingTime['specialid_'+specialid] = runtime;
		}
		$obj_time.innerHTML = doingTime['specialid_'+specialid].toFixed(4);
	}else{
		$obj_time.innerHTML = runtime||'';
	}

	var obj = $('dopercent');
<?php
	if (count($does)>0):
?>
	var alldongingnum = <?php echo count($does);?>;
	var mypercent = 100*((allDoingOk+theDoingOk)/alldongingnum);
	mypercent = Math.round(mypercent) +'%';
	obj.style.width = mypercent;
	obj.innerHTML = mypercent;
<?php
endif;
?>
}

</script>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('task/index');?>">任务管理</a> -&gt; <a href="<?php echo Myqee::url('info/tohtml');?>">生成静态页</a> -&gt; <?php echo $page_title; ?>
</div>

<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
	<tr>
		<th class="td1" colspan="2"><?php echo $page_title;?></th>
	</tr>
	<tr>
		<td class="td1" valign="top">
<div style="padding:5px;">
<table border="0" cellpadding="4" cellspacing="1" align="center" style="width:100%" class="tableborder">
<tr>
<th class="td1" width="50">总进度</th>
<td class="td1">
<div style="border:1px #e4ab12 solid;background:#fff"><div style="width:100%;height:9px;overflow:hidden;"><div style="background:#ffb924;width:1px;font-size:8px;text-align:center"" id="dopercent">0%</div></div></div>
</td>
</tr>
</table>
<br/>
<table border="0" cellpadding="4" cellspacing="1" align="center" style="width:100%" class="tableborder">
<tr>
<th class="td1" colspan="3">任务列表</th>
</tr>
<tr align="center" style="font-weight:bold;">
<td class="td1">任务名</td>
<td class="td1">已完成/总数</td>
<td class="td1">耗时</td>
</tr>
<?php
if (is_array($does)){
	$i = 0;
	foreach ($does as $item){
		$i++;
		echo '
<tr'.($i % 2 == 0 ? ' class="td3"':'').'>
<td class="td2" id="do_info_'.$item['sid'].'">'.$item['info'].'</td>
<td class="td2" id="do_count_'.$item['sid'].'" align="center">?</td>
<td class="td2" id="do_time_'.$item['sid'].'" align="center">排队中...</td>
</tr>';
	}
}
?>

</table>
<div style="padding:10px 0 0 0;text-align:right">每组生成页面数量为：<?php echo $dohtml_limit; ?></div>
</div>
		</td>
		<td width="50%" class="td2" valign="top">
<div style="padding:5px;">
<table border="0" cellpadding="4" cellspacing="1" align="center" style="width:100%" class="tableborder">
<tr>
<th class="td1" >正在执行的信息</th>
</tr>
<tr>
<td class="td2" id="mainDoingDiv">
<center style="margin:auto;padding:15px 0;"><img src="<?php echo ADMIN_IMGPATH;?>/admin/loading1.gif" /><br /><br />
正在执行的任务：【栏目生成】...
<br/><br/><input type="button" onclick="ajax.stop();" value="取消操作" class="btn" /></center>
</td>
</tr>
<tr>
<th class="td1" align="center">
完成信息
</th>
</tr>
<tr>
<td class="td2" id="mainInfoDiv">&nbsp;</td>
</tr>
<tr>
<td class="td1">
总用时：<span id="allusetime" style="color:red">0</span>
&nbsp;&nbsp;生成页面总数：<span id="alldopage" style="color:red">0</span>
&nbsp;&nbsp;生成失败数：<span id="alldopageerror" style="color:red">0</span>
</td>
</tr>
</table>
</div>
		</td>
	</tr>
</table>
<textarea style="display:none" id="myhtml">
<html>
<head><style>body{font-size:12px;}</style></head>
<body>

</body>
</html>
</textarea>
<script type="text/javascript">
doit('<?php echo $tohtmlurl;?>');
</script>

<?php View::factory('admin/footer') -> render(TRUE);?>