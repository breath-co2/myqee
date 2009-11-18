<?php
$page_index = 'task';
if ($iscopy == true) {
	$page_title = '复制任务';
} elseif ($isedit == true) {
	$page_title = '修改任务：' . $data ['name'];
} else {
	$page_title = '添加任务';
}
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<script type="text/javascript" src="/images/admin/calendar.js"></script>
<div class="loaction">您的位置：<a
	href="<?php
	echo Myqee::url ( 'index' );
	?>">管理首页</a> -&gt; <a
	href="<?php
	echo Myqee::url ( 'task/index' );
	?>">任务管理</a> -&gt;
<?php
echo $page_title;
?>
</div>
<script type="text/javascript">

function chkforms(obj){

	if (obj['task[name]'].value==''){
		alert("请设置计划任务名称！",null,null,null,function(){
			obj['task[name]'].focus();
		});
		return false;
	}
	
	if (obj['task[cycletype]'].value==''){
		alert("请设置计划任务周期！",null,null,null,function(){
				obj['task[cycletype]'].focus();
	    });
		return false;
	}
	var chktimes = new RegExp("^[0-9]+$");
	if (obj['task[maxtimes]'].value!='' && !chktimes.test(obj['task[maxtimes]'].value)){
		alert("任务脚本文件只允许允许“数字”！",null,null,null,function(){
				obj['task[maxtimes]'].focus();
	    });
		return false;
	}	
	var chkname = new RegExp("^[0-9a-zA-Z\_]+$");
	if (obj['task[taskfile]'].value=='' || !chkname.test(obj['task[taskfile]'].value)){
		alert("任务脚本文件只允许允许“数字、英文、下划线”且不能空！",null,null,null,function(){
				obj['task[taskfile]'].focus();
	    });
		return false;
	}	

	if (obj['task[taskmode]'].value==''){
		alert("请设置计划任务模式！",null,null,null,function(){
				obj['task[taskmode]'].focus();
	    });
		return false;
	}
	return true;
}

</script>
<div style="clear: both"></div>

<form action="<?php
echo Myqee::url ( 'task/save/' . $id );
?>"
	method="POST" onsubmit="return chkforms(this)" target="hiddenFrame">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
	<tr>
		<th class="td1" colspan="2"><?php echo $page_title;?></th>
	</tr>
	<tr>
		<td class="td1" align="right" width="150">任务名称(<font color="red">*</font>)：</td>
		<td class="td2"><?php
		echo form::input ( 'task[name]', $data ['name'], 'class="input" size="30"' );
		?></td>
	</tr>
	<tr>
		<td class="td1" align="right">是否启用：</td>
		<td class="td2">
<?php
echo form::radio ( 'task[isuse]', 1, ! isset ( $data ['isuse'] ) || ( int ) $data ['isuse'] == 1 );
?>是 
<?php
echo form::radio ( 'task[isuse]', 0, isset ( $data ['isuse'] ) && ( int ) $data ['isuse'] == 0 );
?>否
</td>
	</tr>
	<tr>
		<td class="td1" align="right">类别：</td>
		<td class="td2"><?php
		echo form::changeinput ( 'task[cate]', $data ['cate'], ' size="10" class="input"', $taskcate, array ('' => '选择已有分类' ) );
		?></td>
	</tr>
	<tr>
		<td class="td1" align="right">周期(<font color="red">*</font>)：</td>
		<td class="td2">
		<?php
		if (!isset($data['cycletype']))$data ['cycletype'] = 4;
		echo form::radio ( array ('name' => 'task[cycletype]', 'id' => 'task[cycletype]' ), 1,$data['cycletype'] == 1, 'onclick="changetag(this.id,\'task[cycletype]\',\'mycycle\',this.value);"' );
		?>秒	
		<?php
		echo form::radio ( array ('name' => 'task[cycletype]', 'id' => 'task[cycletype]' ), 2,$data['cycletype'] == 2, 'onclick="changetag(this.id,\'task[cycletype]\',\'mycycle\',this.value);"' );
		?>分  <?php
		echo form::radio ( array ('name' => 'task[cycletype]', 'id' => 'task[cycletype]' ), 3,$data ['cycletype'] == 3, 'onclick="changetag(this.id,\'task[cycletype]\',\'mycycle\',this.value);"' );
		?>时 <?php
		echo form::radio ( array ('name' => 'task[cycletype]', 'id' => 'task[cycletype]' ), 4,$data ['cycletype'] == 4, 'onclick="changetag(this.id,\'task[cycletype]\',\'mycycle\',this.value);"' );
		?>日 <?php
		echo form::radio ( array ('name' => 'task[cycletype]', 'id' => 'task[cycletype]' ), 5,$data ['cycletype'] == 5, 'onclick="changetag(this.id,\'task[cycletype]\',\'mycycle\',this.value);"' );
		?>周 <?php
		echo form::radio ( array ('name' => 'task[cycletype]', 'id' => 'task[cycletype]' ), 6,$data ['cycletype'] == 6, 'onclick="changetag(this.id,\'task[cycletype]\',\'mycycle\',this.value);"' );
		?>月 <?php
		echo form::radio ( array ('name' => 'task[cycletype]', 'id' => 'task[cycletype]' ), 7,$data ['cycletype'] == 7, 'onclick="changetag(this.id,\'task[cycletype]\',\'mycycle\',this.value);"' );
		?>年</td>
	</tr>
	<tr>
		<td class="td1" align="right">&nbsp;</td>
		<td class="td2">
		<div id="mycycle_1">
		<?php
		$daysarray = array ();
		$daysarray = array ('10'=>10,'20'=>20,'30'=>30,'40'=>40,'50'=>50);
		echo '每 ', form::dropdown ( array ('name' => 'task[domins]', 'id' => 'task[domins]', 'selected' => $data ['cycletype'] == 1 ? $data ['cycle'] : '' ), $daysarray ), ' 秒钟'?>
		
		</div>
		
		<div id="mycycle_2" style="display: none"><?php
		$daysarray = array ();
		for($i = 1; $i < 60; $i ++) {
			$daysarray [$i] = $i;
		}

		echo '每 ', form::dropdown ( array ('name' => 'task[dosecs]', 'id' => 'task[dosecs]', 'selected' => $data ['cycletype'] == 2 ? $data ['cycle'] : '' ), $daysarray ), ' 分钟'?></div>
		<div id="mycycle_3" style="display: none"><?php
		$daysarray = array ();
		for($i = 1; $i < 24; $i ++) {
			$daysarray [$i] = $i;
		}
		echo '每 ', form::dropdown ( array ('name' => 'task[dohours]', 'id' => 'task[dohours]', 'selected' => $data ['cycletype'] == 3 ? $data ['cycle'] : '' ), $daysarray ), ' 小时'?></div>
		<div id="mycycle_4" style="display: none"><?php
		$daysarray = array ();
		for($i = 1; $i < 32; $i ++) {
			$daysarray [$i] = $i;
		}
		echo '每 ', form::dropdown ( array ('name' => 'task[dodays]', 'id' => 'task[dodays]', 'selected' => $data ['cycletype'] == 4 ? $data ['cycle'] : '' ), $daysarray ), ' 天'?></div>
		<div id="mycycle_5" style="display: none"><?php
		echo form::checkbox ( array ('name' => 'task[doweeks][]', 'id' => 'task[doweeks][]' ), 1 )?>周一
				<?php
				echo form::checkbox ( array ('name' => 'task[doweeks][]', 'id' => 'task[doweeks][]' ), 2 )?>周二
				<?php
				echo form::checkbox ( array ('name' => 'task[doweeks][]', 'id' => 'task[doweeks][]' ), 3 )?>周三
				<?php
				echo form::checkbox ( array ('name' => 'task[doweeks][]', 'id' => 'task[doweeks][]' ), 4 )?>周四
				<?php
				echo form::checkbox ( array ('name' => 'task[doweeks][]', 'id' => 'task[doweeks][]' ), 5 )?>周五
				<?php
				echo form::checkbox ( array ('name' => 'task[doweeks][]', 'id' => 'task[doweeks][]' ), 6 )?>周六
				<?php
				echo form::checkbox ( array ('name' => 'task[doweeks][]', 'id' => 'task[doweeks][]' ), 7 )?>周日
			</div>
		<div id="mycycle_6" style="display: none">
				<?php
				$daysarray = array ();
				for($i = 1; $i < 12; $i ++) {
					$daysarray [$i] = $i;
				}
				echo '每 ', form::dropdown ( array ('name' => 'task[domonths]', 'id' => 'task[domonths]', 'selected' => $data ['cycletype'] == 6 ? $data ['cycle'] : '' ), $daysarray ), ' 月'?></div>
		<div id="mycycle_7" style="display: none"><?php
		$daysarray = array ();
		for($i = 1; $i < 11; $i ++) {
			$daysarray [$i] = $i;
		}
		echo '每 ', form::dropdown ( array ('name' => 'task[doyears]', 'id' => 'task[doyears]', 'selected' => $data ['cycletype'] == 7 ? $data ['cycle'] : '' ), $daysarray ), ' 年'?></div>
		<script type="text/javascript">
		$('task[cycletype]').onclick();
		</script>
		</td>
	</tr>
	<tr>
		<td class="td1" align="right">开始时间：</td>
		<td class="td2"><?php
		echo form::timeinput ( array ('name' => 'task[starttime]', 'time' => 'H:i' ), $data ['starttime'] );
		?> </td>
	</tr>
	<tr>
		<td class="td1" align="right">结束时间：</td>
		<td class="td2"><?php
		echo form::timeinput ( array ('name' => 'task[endtime]', 'time' => 'H:i' ), $data ['endtime'] );
		?> </td>
	</tr>
	<tr>
		<td class="td1" align="right">最大执行次数：</td>
		<td class="td2"><?php
		echo form::input ( 'task[maxtimes]', $data ['maxtimes'], 'class="input" size="3"' );
		?> <font color="#a3a3a3">输入此项会覆盖结束时间设置,0 为不限次数。</font> </td>
	</tr>

	<tr>
		<td class="td1" align="right">任务脚本路径：</td>
		<td class="td2"><?php
		echo form::input ( 'task[taskfile]', $data ['taskfile'], 'class="input" size="30"' );
		?></td>
	</tr>
	<tr>
		<td class="td1" align="right">任务方式(<font color="red">*</font>)：</td>
		<td class="td2"><?php
		echo form::changeinput ( 'task[taskmode]', $data ['taskmode'], ' size="10" class="input"', $taskmodearr, array ('' => '选择已有方式' ) );
		?></td>
	</tr>
</table>
<table border="0" cellpadding="4" cellspacing="1" align="center"
	class="tableborder" style="border-top: none;">
	<tr>
		<td class="td1" width="150">&nbsp;</td>
		<td class="td1"><input type="submit" class="bbtn" value="保存任务" /> <input
			type="button"
			onclick="goback(-1,'<?php
			echo Myqee::url ( 'task/index' );
			?>')"
			class="btns" value="返回" /></td>
	</tr>
</table>
<script>
function changetag(thisid,titlePrefix,mainPrefix,thisvalue){
	var mainobj;
		mainobj = document.body;
	var thisTitleId = mainPrefix+'_'+thisvalue;
	var el = document.getElementsByName(titlePrefix);
	for (var i=0;i<el.length;i++)
	{
		if (el[i].id)
		{   
			if (el[i].id == titlePrefix)
			{
				
				var mainobj = $(mainPrefix +'_'+ el[i].value);
				if (thisTitleId != mainobj.id)
				{
					if (mainobj)
					{
						mainobj.style.display = 'none';
					}
				} else {
					if (mainobj)
					{
						mainobj.style.display = 'block';
					}
				}
			}
		}
	}
}


</script>
<?php
if ($iscopy == true || $isedit == true) {
	
	?>
	<script>changetag('task[cycletype]','task[cycletype]','mycycle',<?php
	echo $data ['cycletype'];
	?>);
	</script>
<?php
	$cyclearr = str_split ( $data ['cycle'] );
	foreach ( $cyclearr as $cycle ) {
		if ($data ['cycletype']==5 && $cycle != '' && $cycle != '|') {
			?>
			<script>
			var aa = document.getElementsByName('task[doweeks][]');
			aa[<?php
			echo ( int ) $cycle - 1?>].checked = true;</script>
			<?php
		}
	
	}
}
?>
</form>
<?php
View::factory('admin/footer') -> render(TRUE);
?>