<?php
$page_index = 'task';
$page_title = '节点管理';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('task/index');?>">任务管理</a> -&gt; <a href="<?php echo Myqee::url('acquisition/index');?>">采集管理</a> -&gt; 
<?php
if ($copyacquid==true){
	echo '复制任务';
}elseif ($isedit == true){
	echo '修改任务：'.$data['name'];
}else{
	echo '添加任务';
}
?>
</div>

<div style="width:96%;margin:auto;">
<ul class="ul tag">
	<li class="now" id="mytag_1" onclick="tag(this.id,'mytag','mytagmain');document.location.hash='#tag1'">基本设置</li>
	<li id="mytag_2" onclick="tag(this.id,'mytag','mytagmain');document.location.hash='#tag2'">默认值设置</li>
	<li id="mytag_3" onclick="tag(this.id,'mytag','mytagmain');document.location.hash='#tag3'">模拟登陆</li>
	<li id="mytag_4" onclick="tag(this.id,'mytag','mytagmain');document.location.hash='#tag4'">入库操作</li>
</ul>
</div>
<div style="clear:both"></div>

<form action="<?php echo Myqee::url('acquisition/save/'.$id);?>" method="POST" target="hiddenFrame">
<div id="mytagmain_1">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
<tr>
	<th class="td1" colspan="2">基本设置</th>
</tr>
<tr>
<td class="td1" align="right" width="150">任务名称：</td>
<td class="td2"><?php echo form::input('acqu[name]',$data['name'],'class="input" size="30"');?></td>
</tr>
<tr>
<td class="td1" align="right">是否启用：</td>
<td class="td2">
<?php echo form::radio('acqu[isuse]',1,!isset($data['isuse']) || (int)$data['isuse']==1);?>是 
<?php echo form::radio('acqu[isuse]',0,isset($data['isuse']) && (int)$data['isuse']==0);?>否
</td>
</tr>
<tr>
<td class="td1" align="right">所属栏目：</td>
<td class="td2">
<?php echo form::classlist('acqu[classid]',$class,'id="acqu[classid]" onchange="if(this.value==0){$(\'acqu[modelid]\').disabled=false;$(\'acqu[modelid]\').onchange();}else{$(\'acqu[modelid]\').disabled=$(\'acqu[dbname]\').disabled=true;}"',$data['classid'],array('请选择栏目'),true);?> 
若选择本项，则下面的“模型”和“数据表”不需要选择，因为每一个栏目都已对应相应的模型和数据表。</td>
</tr>
<tr>
<td class="td1" align="right">所属模型：</td>
<td class="td2"><?php echo form::dropdown('acqu[modelid]',(array)$model,$data['modelid'],'id="acqu[modelid]" onchange="if(this.value==0){$(\'acqu[dbname]\').disabled=false;}else{$(\'acqu[dbname]\').disabled=true;}"');?> 若选择栏目，则此项不需要选择。</td>
</tr>
<tr>
<td class="td1" align="right">所属数据表：</td>
<td class="td2"><?php echo form::dropdown('acqu[dbname]',(array)$dblist,$data['dbname'],'id="acqu[dbname]"');?> 
若选择栏目或模型，则此项不需要选择。</td>
</tr>
<tr>
<td class="td1" align="right">复制任务节点：</td>
<td class="td2"><?php echo form::checkbox('copyacqu',$copyacquid,0,$copyacquid>0?'':'disabled="disabled"');?> 
当<b>复制任务时</b>可以选择把被复制任务节点全部复制过来。</td>
</tr>
</table>
<script type="text/javascript">
$('acqu[classid]').onchange();
</script>
</div>
<div id="mytagmain_2" style="display:none;">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
<tr>
	<th class="td1" colspan="2">默认值设置</th>
</tr>
<tr>
<td class="td1" align="right" width="150">默认提交方式：</td>
<td class="td2"><?php echo form::dropdown('acqu[method]',array('GET'=>'GET','POST'=>'POST'),$data['method']);?> 通常都是GET方式，支持GET或POST方式。</td>
</tr>
<tr>
<td class="td1" align="right">默认提取页面编码：</td>
<td class="td2"><?php echo form::input('acqu[charset]',$data['charset'],'id="acqu[charset]" class="input" size="12"');?> 
<select onchange="$('acqu[charset]').value=this.value" >
<option value="">请选择</option>
<option value="GBK">GBK/GB2312</option>
<option value="UTF-8">UTF-8</option>
<option value="BIG5">BIG5</option>
</select>
可选择预设的编码，也可填入其它编码，不区分大小写，留空表示不进行编码转换。
</td>
</tr>
<tr>
<td class="td1" align="right">默认引用页：</td>
<td class="td2"><?php echo form::input('acqu[post][referer]',$data['post']['referer'],'class="input" size="40"');?> 
<font style="font-size:9px;color:#f90">$_SERVER["HTTP_REFERER"]</font> 留空则不传递，下同
</td>
</tr>
<tr>
<td class="td1" align="right">默认接受类型：</td>
<td class="td2"><?php echo form::input('acqu[post][accept]',isset($data['post']['accept'])?$data['post']['accept']:'*/*','class="input" size="12"');?> 
<font style="font-size:9px;color:#f90">$_SERVER["HTTP_ACCEPT"]</font>
</td>
</tr>
<tr>
<td class="td1" align="right">默认接受的编码类型：</td>
<td class="td2"><?php echo form::input('acqu[post][accept_encoding]',isset($data['post']['accept_encoding'])?$data['post']['accept_encoding']:'gzip, deflate','class="input" size="20"');?> 
<font style="font-size:9px;color:#f90">$_SERVER["HTTP_ACCEPT_ENCODING"]</font>
</td>
</tr>
<tr>
<td class="td1" align="right" >默认客户端信息：</td>
<td class="td2"><?php echo form::textarea('acqu[post][user_agent]',isset($data['post']['user_agent'])?$data['post']['user_agent']:$_SERVER['HTTP_USER_AGENT'],'class="input" cols="80" rows="4"');?> 
<font style="font-size:9px;color:#f90">$_SERVER["HTTP_USER_AGENT"]</font>
</td>
</tr>
<tr>
<td class="td1" align="right" >默认提交Cookie：</td>
<td class="td2"><?php echo form::textarea('acqu[post][cookie]',$data['post']['cookie'],'class="input" cols="80" rows="4"');?> 
<font style="font-size:9px;color:#f90">$_SERVER["HTTP_COOKIE"]</font>
</td>
</tr>
<tr>
<td class="td1" align="right" >其它提交内容：</td>
<td class="td2"><?php echo form::textarea('acqu[other_post]',$data['other_post'],'class="input" cols="80" rows="6"');?> 
<font style="color:#999">多个参数用;分开，例如mykey=123;mykey2=456;</font>
</td>
</tr>
</table>
</div>


<div id="mytagmain_3" style="display:none;">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
<tr>
	<th class="td1" colspan="2">模拟登陆</th>
</tr>
<tr>
<td class="td1" align="right" width="150">登陆后采集：</td>
<td class="td2">
<?php echo form::radio('acqu[islogin]',1,(int)$data['islogin']==1);?>是 
<?php echo form::radio('acqu[islogin]',0,(int)$data['islogin']==0);?>否
</td>
</tr>
<tr>
<td class="td1" align="right">提交网页地址：</td>
<td class="td2">
<?php echo form::input('acqu[loginactionurl]',$data['loginactionurl'],'class="input" size="30"');?> 
表单提交的地址
</td>
</tr>
<tr>
<td class="td1" align="right">提交数据：</td>
<td class="td2">
<?php echo form::textarea('acqu[loginpost]',$data['loginpost'],'class="input" cols="50" rows="4"');?> 
例如：username=myqee&password=123456
</td>
</tr>
<tr>
<td class="td1" align="right">登陆验证码图片地址：</td>
<td class="td2">
<?php echo form::input('acqu[loginimageurl]',$data['loginimageurl'],'class="input" size="30"');?> 
无需输入验证码请留空，否则请填入验证码地址，例如：http://myqee.com/captcha/img.png
</td>
</tr>
</table>
</div>

<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-top:none;">
<tr>
<td class="td1" width="150">&nbsp;</td>
<td class="td1"><input type="submit" class="bbtn" value="保存任务" /> <input type="button" onclick="goback(-1,'<?php echo Myqee::url('acquisition/index');?>')" class="btns" value="返回" /></td>
</tr>
</table>
</form>
<script type="text/javascript">
if (document.location.hash =='#tag1'||document.location.hash =='#tag2'||document.location.hash =='#tag3'||document.location.hash =='#tag4'){
	tag('mytag_'+document.location.hash.substr(4),'mytag','mytagmain');
}
</script>


<?php View::factory('admin/footer') -> render(TRUE);?>