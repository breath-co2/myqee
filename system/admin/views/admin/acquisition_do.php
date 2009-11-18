<?php
$page_index = 'task';
$page_title = '采集信息';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>


<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('task/index');?>">任务管理</a> -&gt; <a href="<?php echo Myqee::url('acquisition/index');?>">采集管理</a>
 -&gt; 任务：<a href="<?php echo Myqee::url('acquisition/node_list/'.$acquisition_id);?>"><?php echo $acquisition_name;?></a>
 -&gt; <?php echo $page_title;?>
</div>

<script type="text/javascript">
var dosubmit = {
	'onCompletion':function(){
		if(this.response=='OK'){
			succeed({'message':'采集程序已启动！正在采集中...','width':400,'btn':[['查看状态','view'],['关闭','cancel']],'handler':function(ee){if(ee=='view'){goUrl('<?php echo Myqee::url('index/logs_view')?>?log=acqu_<?php echo $acquisition_id;?>_<?php echo $data['id'];?>_<?php echo date("Y-m-d");?>-'+$('dotime').value+'.log.txt&renewtime=2','_blank')}}});
		}else if (this.response=='REQUEST EXPRIED'){
			alert('操作页面停留时间过长，请刷新页面后重新执行！',400);
		}else if(this.response=='LISTS EXIST'){
			confirm({message:'上次采集未完成或异常退出或正在采集，是否继续？如果不能确定是否正在采集，请点击“取消执行”，然后在节点管理里面点击“停止采集”，过一会再点击“开始采集”',btn:[['继续执行','next'],['重新开始','del'],['取消执行','cancel']]},450,null,null,function(el){
				if (el=='next' || el=='del'){
					$('dotype').value = el;
					ajax.submit($('myform'),dosubmit);
					$('dotype').value = '';
				}
			});
		}else if(this.response.substr(0,5)=='info:'){
			alert(this.response.substr(5),400);
		}else{
			error('操作失败，请刷新页面或联系管理员<br/><br/>错误信息：'+this.response,400);
		}
	},
	'onError':function(){alert('ERROR');}
};

function setdotime(){
	var obj = $('dotime');
	var d = new Date;
	var v = d.getTime();
	obj.value=v;
}
</script>
<form action="<?php echo Myqee::url('acquisition/acqu_run/'.$acquisition_id.'/'.$node_id);?>" id="myform" name="myform" method="POST" target="hiddenFrame" onsubmit="setdotime();return ajax.submit(this,dosubmit);">
<input type="hidden" name="dotime" id="dotime" value="" />
<input type="hidden" name="dotype" id="dotype" value="" />
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
<tr>
	<th class="td1" colspan="2">采集信息</th>
</tr>
<tr>
<td class="td1" align="right">采节点名称：</td>
<td class="td2"><?php echo htmlspecialchars($data['name']);?> <a href="<?php echo Myqee::url('acquisition/node_edit/'.$acquisition_id.'/'.$data['id']);?>" class="btn">修改配置</a></td>
</tr>
<tr>
<td class="td1" width="120" align="right">获取采集地址方式：
</td>
<td class="td2">
<?php echo form::dropdown('acqu_node[urltype]',array('一组固定的地址列表','根据当前采集页面分析下一页面地址','有规律的页面地址','调用其它节点输出的地址'),$data['urltype'],'id="acqu_node[urltype]" onchange="tag(\'urltagtype_\'+this.value,\'urltagtype\',\'urltype\',\'theurltypetag\');"');?> 
<span class="helpicon" title="“<b>一组固定的地址列表</b>”通常是一些个别的较固定且较少的页面！<br/>“<b>根据当前采集页面分析下一页面地址</b>”通常可用户列表页。<br/>“<b>有规律的页面地址</b>”例如list_1.html,list_2.html这样的页面<br/>“<b>调用其它节点输出的地址</b>”是调用其它采集点（例如列表采集页）采集的输出的页面地址列表。">&nbsp;</span>
</td>
</tr>
<tr>
<td class="td1" align="right" valign="top" style="line-height:2em;">采集地址：</td>
<td class="td2" id="theurltypetag">
<div style="display:none;">
<ul class="ul tag">
	<li class="now" id="urltagtype_0"></li>
	<li id="urltagtype_1"></li>
	<li id="urltagtype_2"></li>
	<li id="urltagtype_3"></li>
</div>
<div id="urltype_0"><?php echo form::textarea('acqu_node[theurl0]',$data['theurl0'],'class="input" cols="90" rows="10"');?> 
（一行一条页面）
</div>
<div id="urltype_1" style="display:none;">
<table border="0" cellpadding="4" cellspacing="1" style="width:550px;" class="tableborder">
<tr>
	<td class="td1" align="right" width="90">原始采集页面：</td>
	<td class="td2"><?php echo form::input('acqu_node[theurl1][url]',$data['theurl1']['url'],'class="input" size="40"');?> 
	<span class="helpicon" title="当程序执行时传入一个页面地址时，此项会被覆盖掉<br/>例如：http://www.myqee.com/class1/list_1.html"></span></td>
</tr>
<tr>
	<td class="td1" align="right">下一地址规则：</td>
	<td class="td2">
<?php echo form::textarea('acqu_node[theurl1][next]',$data['theurl1']['next'],'class="input" cols="60" rows="8"');?> 
<span class="helpicon" title="在当前采集页面源文件里进行匹配，与后面的采集规则相同！<br/>例如：/&#38;lt;a href=&quot;\/class\/\?id=1&page=([0-9]+)&quot;&#38;gt;下一页&#38;lt;\/a&#38;gt;/Uis">&nbsp;</span>
<div style="padding:3px 0;">将上面的规则结果转换为需要的结果</div>
<?php echo form::input('acqu_node[theurl1][tourl]',$data['theurl1']['tourl'],'class="input" size="40"');?> 
<span class="helpicon" title="将上面的匹配内容转换为需要的结果<br/>例如：http://www.myqee.com/class1/list_&#38;#123;$1&#38;#125;.html！">&nbsp;</span>
</td>
</tr>
<tr>
	<td class="td1" align="right">&nbsp;</td>
	<td class="td2"><input type="button" class="btn" value="测试规则" /></td>
</tr>
</table>
</div>
<div id="urltype_2" style="display:none;">
<table border="0" cellpadding="4" cellspacing="1" style="width:550px;" class="tableborder">
<tr>
	<td class="td1" align="right" width="70">规则地址：</td>
	<td class="td2"><?php echo form::input('acqu_node[theurl2][url]',$data['theurl2']['url'],'class="input" size="40"');?> 
	<span class="helpicon" title="例如：http://www.myqee.com/class1/list_&#38;#123;&#38;#123;page&#38;#125;&#38;#125;.html">&nbsp;</span> 
	</td>
</tr>
<tr>
	<td class="td1" align="right">替换变量：</td>
	<td class="td2"><?php echo form::input('acqu_node[theurl2][replace]',isset($data['theurl2']['replace']) && !empty($data['theurl2']['replace'])?$data['theurl2']['replace']:'{{page}}','class="input" size="8"');?> 将上面地址中出现的此内容替换为操作页码/ID</td>
</tr>
<tr>
	<td class="td1" align="right">起始页码：</td>
	<td class="td2"><?php echo form::input('acqu_node[theurl2][begin]',$data['theurl2']['begin']>1?$data['theurl2']['begin']:1,'class="input" size="8"');?> 
	</td>
</tr>
<tr>
	<td class="td1" align="right">结束页码：</td>
	<td class="td2"><?php echo form::input('acqu_node[theurl2][end]',$data['theurl2']['end']>1?$data['theurl2']['end']:1,'class="input" size="8"');?> 
	</td>
</tr>
<tr>
	<td class="td1" align="right">间隔倍数：</td>
	<td class="td2"><?php echo form::input('acqu_node[theurl2][limit]',$data['theurl2']['limit']>1?$data['theurl2']['limit']:1,'class="input" size="8"');?> 
<span class="helpicon" title="间隔倍数为1，则采集的页码为1,2,3,4,5...<br/>间隔倍数为10则为1,11,21,31,41,51..."></span> 
</td>
</tr>
<tr>
	<td class="td1" align="right">补足位数：</td>
	<td class="td2">向 <?php echo form::dropdown('acqu_node[theurl2][makeup]',array('前','后'),(int)$data['theurl2']['makeup']);?> 
	补足<?php echo form::input('acqu_node[theurl2][makeupnum]',$data['theurl2']['makeupnum']>0?$data['theurl2']['makeupnum']:0,'class="input" size="4"');?> 位，补足字符 <?php echo form::input('acqu_node[theurl2][makeupstr]',$data['theurl2']['makeupstr'],'class="input" size="6"');?> 
	<span class="helpicon" title="例如可以进行补0操作<br/>补0操作：补足字符输入“0”，补足位数输入“2”。">&nbsp;</span><font color="#999999">“补足0位”表示不进行补足操作</font>
</td>
</tr>
<tr>
	<td class="td1" align="right">倒序：</td>
	<td class="td2"><?php echo form::checkbox('acqu_node[theurl2][reverse]',1,$data['theurl2']['reverse']==1?true:false);?>
</td>
</tr>
<tr>
	<td class="td1" align="right">&nbsp;</td>
	<td class="td2"><input type="button" class="btnl" value="查看输出结果" /></td>
</tr>
</table>
</div>
<div id="urltype_3" style="display:none;">
<?php echo form::dropdown('acqu_node[theurl3]',$otheracqu_forurl,$data['theurl3']['id'].'|'.$data['theurl3']['nodeid']);?> 
可以选择调用其它节点输出的采集地址
</div>
<script type="text/javascript">
$('acqu_node[urltype]').onchange();
</script>
</td>
</tr>
<tr>
<td class="td1">&nbsp;</td>
<td class="td1"><input type="submit" class="bbtn" value="开始采集" /> 
<a href="<?php echo Myqee::url('acquisition/logs_list/'.$acquisition_id.'/'.$data['id']);?>" class="btn">采集日志</a> 
<a href="<?php echo Myqee::url('acquisition/info_list/'.$acquisition_id.'/'.$data['id']);?>" class="btn">采集数据</a> 
<input type="button" onclick="goback(-1,'<?php echo Myqee::url('acquisition/node_list/'.$acquisition_id);?>')" class="btns" value="返回" />
</td>
</tr>
</table>
</form>

<?php View::factory('admin/footer') -> render(TRUE);?>