<script type="text/javascript" src="/images/calendar.js"></script>
<br/>
<table border="0" cellpadding="2" cellspacing="1" width="96%" align="center" class="tableborder">
<tr>
	<th class="td1" style="text-align:left">
	<table border="0" cellpadding="0" cellspacing="0" style="float:left;"><tr>
	<td><input type="button" value="静态单页" class="btn<?php echo $viewit==1?' btnover':''?>" onclick="goUrl('<?php echo Myqee::url('admin/page/index/html')?>')" /></td>

	<td><input type="button" value="静态列表" class="btn<?php echo $viewit==1?' btnover':''?>" onclick="goUrl('<?php echo Myqee::url('admin/page/index/html/list')?>')" /></td>
	<td>&nbsp;&nbsp;&nbsp;<input type="button" value="动态单页" class="btn<?php echo $viewit==1?' btnover':''?>" onclick="goUrl('<?php echo Myqee::url('admin/page/index/php')?>')" /></td>
	<td><input type="button" value="动态列表" class="btn<?php echo $viewit==1?' btnover':''?>" onclick="goUrl('<?php echo Myqee::url('admin/page/index/php/list')?>')" /></td>
	<td>起始日期：</td>
	<td><input type="text" size="10" id="beginTime" class="input" readonly="readonly" class="input" value="<?php echo $begindate?>" onclick="showcalendar(event,this);" onfocus="showcalendar(event, this);if(this.value=='0000-00-00')this.value=''" /></td>
	<td>&nbsp;&nbsp;结束日期：</td>
	<td><input type="text" size="10" id="endTime" class="input" readonly="readonly" class="input" value="<?php echo $enddate?>" onclick="showcalendar(event,this);" onfocus="showcalendar(event, this);if(this.value=='0000-00-00')this.value=''" /></td>
	
	<!--<td>&nbsp;&nbsp;排序：</td>
	<td><select name="notice[indexshow]">
		<option value="1"<?php if('a'.$notice['indexshow']=='a1'){echo ' selected="selected"';}?>>ID</option>
		<option value="0"<?php if('a'.$notice['indexshow']=='a0'){echo ' selected="selected"';}?>>IP</option>
		<option value="0"<?php if('a'.$notice['indexshow']=='a0'){echo ' selected="selected"';}?>>PV</option>
	</select>
	<select name="notice[indexshow]">
		<option value="DESC"<?php if('a'.$notice['indexshow']=='a1'){echo ' selected="selected"';}?>>从高到低</option>
		<option value="ASC"<?php if('a'.$notice['indexshow']=='a0'){echo ' selected="selected"';}?>>从低到高</option>
	</select>
	</td>
	<td>&nbsp;&nbsp;每页：</td>
	<td><select name="notice[indexshow]">
		<option value="20"<?php if('a'.$notice['indexshow']=='a1'){echo ' selected="selected"';}?>>20</option>
		<option value="10"<?php if('a'.$notice['indexshow']=='a1'){echo ' selected="selected"';}?>>10</option>
		<option value="50"<?php if('a'.$notice['indexshow']=='a0'){echo ' selected="selected"';}?>>50</option>
		<option value="80"<?php if('a'.$notice['indexshow']=='a0'){echo ' selected="selected"';}?>>80</option>
		<option value="100"<?php if('a'.$notice['indexshow']=='a0'){echo ' selected="selected"';}?>>100</option>
	</select>
	</td>-->
	
	
	<td>&nbsp;&nbsp;搜索域名：</td>
	<td><input type="text" size="20" id="hostName" class="input" class="input" value="<?php echo $hostname?>" onkeydown="(function (e){if (e.keyCode == 13){$('searchBtn').onclick();}})(event)" /></td>
	<td>&nbsp;<input type="button" id="searchBtn" value="查询" class="btns" onclick="goUrl('/admin/count/index/1/'+$('beginTime').value+'/'+$('endTime').value+($('hostName').value==''?'':'/'+encodeURIComponent($('hostName').value))+'.do')" /></td>
	</tr></table>
	</th>
</tr>
</table>