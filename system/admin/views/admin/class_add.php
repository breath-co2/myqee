<?php
$page_index = 'class';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<div class="loaction">您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a>
-&gt; <a href="<?php echo Myqee::url('class/index');?>">栏目管理</a> -&gt;
<?php echo $page_title;?>
</div>
<script type="text/javascript">
var classpath;
try{
	classpath = <?php echo $allclass_path;?>;
}catch(e){}
if (!classpath)classpath = [''];

var thisclasspath = classpath[<?php echo $class['bclassid']?$class['bclassid']:0;?>];
thisclasspath = thisclasspath ? thisclasspath+'/':'';

function changebclass(obj){
	var selid = obj.value;
	var newhtml ='';
	if (classpath[selid]){
		newhtml = classpath[selid] +'/';
		$('class[isnothtml]').disabled=false;
		$('isoutinfoDiv2').style.display='none';
	}else{
		if (selid==0){
			$('class[isnothtml]').disabled=false;
			$('isoutinfoDiv2').style.display='none';
		}else{
			$('isoutinfoDiv2').style.display='block';
			$('class[isnothtml]').disabled=true;
		}
	}
	$('classpathshow').innerHTML = newhtml;
	if ($('class[isnothtml]').disabled==true){
		$('isoutinfoDiv').style.display ='none';
	}else if($('class[isnothtml]').checked==true){
		$('isoutinfoDiv').style.display ='block';
	}
}
</script>

<form method="post" name="myforms"
	action="<?php echo Myqee::url('class/save');?>" target="hiddenFrame"><input
	type="hidden" name="class[classid]"
	value="<?php echo $class['classid'];?>" />

<div class="mainTable">
<ul class="ul tag" id="mytag">
	<li class="now" id="mytag_1"
		onclick="tag(this.id,'mytag','mytagmain','mytag');document.location.hash='#tag1'">基本属性</li>
	<li id="mytag_2"
		onclick="tag(this.id,'mytag','mytagmain','mytag');document.location.hash='#tag2'">封面设置</li>
	<li id="mytag_3"
		onclick="tag(this.id,'mytag','mytagmain','mytag');document.location.hash='#tag3'">列表设置</li>
	<li id="mytag_4"
		onclick="tag(this.id,'mytag','mytagmain','mytag');document.location.hash='#tag4'">内容设置</li>
	<li id="mytag_5"
		onclick="tag(this.id,'mytag','mytagmain','mytag');document.location.hash='#tag5'">搜索设置</li>
	<li id="mytag_6"
		onclick="tag(this.id,'mytag','mytagmain','mytag');document.location.hash='#tag6'">其它设置</li>
	<li id="mytag_7"
		onclick="tag(this.id,'mytag','mytagmain','mytag');document.location.hash='#tag7'">SEO设置</li>
</ul>
</div>
<div style="clear: both"></div>
<div id="mytagmain_1">
<table border="0" cellpadding="4" cellspacing="1" align="center"
	class="tableborder">
	<tr>
		<th class="td1" colspan="2">基本属性</th>
	</tr>
	<tr>
		<td class="td1" align="right" width="120"><font color="red">*</font>栏目名称：</td>
		<td class="td2"><?php echo form::input('class[classname]',$class['classname'],'id="class[classname]" size="30" class="input"') ?> <input
			type="checkbox" name="class[uptonav][name]" value="1" />更新导航栏名称 <span
			class="helpicon" title="将栏目名称更新到导航栏中，此项只有在设置“显示到导航”才有效！">&nbsp;</span>
		&nbsp; <input type="button" class="btnl" value="生成拼音目录" onclick="get_pinyin_path()" />
		</td>
	</tr>
	<tr>
		<td class="td1" align="right"><font color="red">*</font>所属系统模型：</td>
		<td class="td2"><?php echo form::dropdown('class[modelid]' , $models , $class['modelid']); ?> <input
			type="button"
			onclick="goUrl('<?php echo Myqee::url('model/index')?>')"
			value="管理系统模型" class="btnl" /></td>
	</tr>
	<tr>
		<td class="td1" align="right"><font color="red">*</font>所属父栏目：</td>
		<td class="td2"><?php echo form::classlist('class[bclassid]',$classtree,'size="18" class="select" style="width:420px;" id="class[bclassid]" onchange="changebclass(this)"',(int)$class['bclassid']);?>
		</td>
	</tr>
	<tr>
		<td class="td1" align="right">指定栏目目录：</td>
		<td class="td2">
		<div style="float: left; padding-right: 5px;"><?php echo form::checkbox('class[isnothtml]',0,(int)$class['isnothtml'] ==0,'onfocus="this.blur()" id="class[isnothtml]" onclick="if (this.checked){$(\'isoutinfoDiv\').style.display=\'block\'}else{$(\'isoutinfoDiv\').style.display=\'none\'}"');?></div>
		<div id="isoutinfoDiv" style="float:left;<?php if ((int)$class['isnothtml'] !=0){echo 'display:none;';}?>">
		<table border="0" cellpadding="2" cellspacing="1" class="tableborder"
			style="width: auto;">
			<tr>
				<th>上级栏目目录</th>
				<th>本栏目目录</th>
			</tr>
			<tr>
				<td class="td1">/<span id="classpathshow"><script>document.write(thisclasspath||'');</script></span></td>
				<td class="td2"><input type="text" size="15" name="class[classpath]"
					id="class[classpath]"
					value="<?php $mypath = explode('/',$class['classpath']);echo $mypath[count($mypath)-1];?>"
					class="input" /> 结尾不需要加“/”</td>
			</tr>
		</table>
		</div>
		<div id="isoutinfoDiv2" style="display: none; line-height: 1.9em;">所选父栏目为动态栏目，此选项无效。</div>
		<script type="text/javascript">changebclass($('class[bclassid]'));</script>
		&nbsp;<span class="helpicon" title="若不指定栏目目录，则本栏目将全部采用动态读取方式！">&nbsp;</span>
		<script type="text/javascript">
			$('class[isnothtml]').onclick();
		</script>
		</td>
	</tr>
	<?php
	if ($changesite){
	?>
	<tr>
		<td class="td1" align="right">所属站点：</td>
		<td class="td2">
			<?php echo form::dropdown('class[siteid]',$mysite,$class['siteid']);?>
			<font class="helpicon"
			title="您拥有改变数据表所属于站点权限<br>修改了站点后，需要到对应站点或主站点才能显示。<br/><br/>另：由于不同的站点可以绑定不同的模板组，所以有可能切换站点后需要重新选择栏目的各个模板文件">&nbsp;</font>
		注意，只有父栏目为根目录的栏目才允许设置站点</td>
	</tr>
	<?php
	}
	?>
</table>

<script type="text/javascript">
function get_pinyin_path(){
	var obj = $('class[classname]');
	ajax.requestFile = '<?php echo Myqee::url('index/pinyin')?>';
	ajax.setVar('pinyin',obj.value);
	ajax.onError = function (info){
		alert(info||'请求失败，请稍后再试');
	}
	ajax.onCompletion = function (){
		var arr;
		var iserror=false;
		try{
			arr = eval('('+this.response+')');
			if (typeof(arr)!='object'){
				iserror = true;
			}
		}catch(e){
			iserror = true;
		};
		if (iserror==true || arr.error){
			this.onError(arr.error);
		}else if (arr['pinyin']){
			$('class[classpath]').value = arr['pinyin'];
		}
	}
	ajax.runAJAX();
}
</script>
</div>


<div id="mytagmain_2" style="display: none;">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
	<tr>
		<th class="td1" colspan="2">栏目封面设置</th>
	</tr>
	<tr>
		<td class="td1" align="right" width="120"><b>是否使用封面：</b></td>
		<td class="td2">
		<?php echo form::radio('class[iscover]',1,$class['iscover']==1?true:false);?>是 
		<?php echo form::radio('class[iscover]',0,$class['iscover']==1?false:true);?>否 &nbsp; 
		<font color="red">只有选择是，下面的设置才有效</font>
		</td>
	</tr>
	<tr>
		<td class="td1" align="right">输出方式：</td>
		<td class="td2"><?php echo form::radio('class[cover_tohtml]','0',$class['cover_tohtml']==0,'onclick="if(this.checked){$(\'cover_tohtmlshow\').style.display=\'none\';}"');?>生成静态封面页
	<?php echo form::radio('class[cover_tohtml]','1',$class['cover_tohtml']==1,'onclick="if(this.checked){$(\'cover_tohtmlshow\').style.display=\'\';}" id="class[cover_tohtml]_1"');?>使用动态封面输出
	<span style="display: none;" id="cover_tohtmlshow">
		&nbsp; 缓存时间(秒)：<?php echo form::input('class[cover_cachetime]',(int)$class['cover_cachetime'],'size="4"')?> 0表示不缓存
	</span> <script type="text/javascript">if ($('class[cover_tohtml]_1').checked){$('cover_tohtmlshow').style.display='';}</script>
		</td>
	</tr>
	<tr>
		<td class="td1" align="right">文件名：</td>
		<td class="td2"><?php echo form::input('class[cover_filename]',$class['cover_filename']?$class['cover_filename']:'index.html','class="input" size="20"'); ?> 
		<?php echo form::checkbox('class[cover_hiddenfilename]',1,!isset($class['cover_hiddenfilename'])||$class['cover_hiddenfilename']);?>URL地址隐藏文件名 <span
			class="helpicon"
			title="例如index.html，选择“生成静态封面页”才有效<br/>URL地址隐藏文件名是指通过http://yourhost.com/myclass/这样的形式访问">&nbsp;</span></td>
	</tr>

	<tr>
		<td class="td1" align="right">封面模板：</td>
		<td class="td2"><?php echo form::dropdown('class[cover_tplid]',$cover_tplarray,$class['cover_tplid']); ?> &nbsp;&nbsp;<input
			type="button" value="管理封面模板" class="btnl"
			onclick="goUrl('<?php echo Myqee::url('template/index/1/cover');?>','_blank')" /></td>
	</tr>
</table>
</div>

<div id="mytagmain_3" style="display: none;">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
	<tr>
		<th class="td1" colspan="2">栏目列表设置</th>
	</tr>
	<tr>
	<td class="td1" align="right"><b>是否使用列表页：</b></td>
	<td class="td2">
		<?php echo form::radio('class[islist]',1,$class['islist']==1?true:false);?>是 
		<?php echo form::radio('class[islist]',0,$class['islist']==1?false:true);?>否 &nbsp; 
		<font color="red">只有选择是，下面的设置才有效</font>
	</td>
	<tr>
		<td class="td1" align="right" width="120" height="26">输出方式：</td>
		<td class="td2"><?php echo form::radio('class[list_tohtml]','0',$class['list_tohtml']==0,'onclick="if(this.checked){$(\'list_tohtmlshow\').style.display=\'none\';}"');?>生成静态列表页
		<?php echo form::radio('class[list_tohtml]','1',$class['list_tohtml']==1,'onclick="if(this.checked){$(\'list_tohtmlshow\').style.display=\'\';}" id="class[list_tohtml]_1"');?>使用动态列表输出
		<span style="display: none;" id="list_tohtmlshow">
			&nbsp; 缓存时间(秒)：<?php echo form::input('class[list_cachetime]',(int)$class['list_cachetime'],'size="4"')?> 0表示不缓存
		</span> <script type="text/javascript">if ($('class[list_tohtml]_1').checked){$('list_tohtmlshow').style.display='';}</script>
		</td>
	</tr>
	<tr>
		<td class="td1" align="right">文件名：</td>
		<td class="td2"><?php echo form::input('class[list_filename]',$class['list_filename']?$class['list_filename']:'list_{{page}}.html','class="input" size="20"'); ?> &nbsp;页码用<font
			color="red">{{page}}</font>代替，例如list_{{page}}.html</td>
	</tr>
	<tr>
		<td class="td1" align="right">列表模板：</td>
		<td class="td2">&nbsp;<?php echo form::dropdown('class[list_tplid]',$list_tplarray,$class['list_tplid']); ?> &nbsp;&nbsp;<input
			type="button" value="管理列表模板" class="btnl"
			onclick="goUrl('<?php echo Myqee::url('template/index/1/list');?>','_blank')" /></td>
	</tr>
	<tr>
		<td class="td1" align="right">列取信息：</td>
		<td class="td2"><?php echo form::radio('class[list_nosonclass]',0,(int)$class['list_nosonclass']==0);?>本栏目及子栏目 <?php echo form::radio('class[list_nosonclass]',1,$class['list_nosonclass']==1);?>仅本栏目 <?php echo form::radio('class[list_nosonclass]',2,$class['list_nosonclass']==2);?>仅子栏目 &nbsp;<font
			class="helpicon" title="必须和此栏目在同一个数据表的栏目才会被列出">&nbsp;</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">每页显示：</td>
		<td class="td2">&nbsp;<?php echo form::input('class[list_pernum]',$class['list_pernum']>0?$class['list_pernum']:20,'size="5" class="input"'); ?> 条信息 </td>
	</tr>
	<tr>
		<td class="td1" align="right">列出页码：</td>
		<td class="td2">&nbsp;<?php echo form::input('class[list_allpage]',(int)$class['list_allpage'],'size="5" class="input"'); ?> 页 <font
			color="#999999">(0为显示所有记录)</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">排序方式：</td>
		<td class="td2">&nbsp;按字段<?php echo form::dropdown('class[list_byfield]',$dbfield,$class['list_byfield']?$class['list_byfield']:'id'); ?>
		排列方式<?php echo form::dropdown('class[list_orderby]',array('DESC'=>'倒序排序','ASC'=>'顺序排序'),$class['list_orderby']); ?>
		</td>
	</tr>
</table>
</div>

<div id="mytagmain_4" style="display: none;">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
<tr>
	<th class="td1" colspan="2">栏目信息内容设置</th>
</tr>
<tr>
<td class="td1" align="right"><b>是否可录入信息：</b></td>
<td class="td2">
<?php echo form::radio('class[iscontent]',1,$class['iscontent']==1?true:false);?>是 
<?php echo form::radio('class[iscontent]',0,$class['iscontent']==1?false:true);?>否 &nbsp; 
<font color="red">只有选择是，下面的设置才有效</font>
</td>
</tr>
<tr>
	<td class="td1" align="right" width="120" height="26">内容页输出方式</td>
	<td class="td2"><?php echo form::radio('class[content_tohtml]','0',$class['content_tohtml']==0,'onclick="if(this.checked){$(\'content_tohtmlshow\').style.display=\'none\';}"');?>生成静态内容页
	<?php echo form::radio('class[content_tohtml]','1',$class['content_tohtml']==1,'onclick="if(this.checked){$(\'content_tohtmlshow\').style.display=\'\';}" id="class[content_tohtml]_1"');?>使用动态内容输出
	<span style="display: none;" id="content_tohtmlshow">
		&nbsp; 缓存时间(秒)：<?php echo form::input('class[content_cachetime]',(int)$class['content_cachetime'],'size="4"')?> 0表示不缓存
	</span> <script type="text/javascript">if ($('class[content_tohtml]_1').checked){$('content_tohtmlshow').style.display='';}</script>
	</td>
</tr>
<tr>
	<td class="td1" align="right">内容页模板</td>
	<td class="td2"><?php echo form::dropdown('class[content_tplid]',$content_tplarray,$class['content_tplid']); ?> &nbsp;&nbsp;<input
		type="button" value="管理内容模板" class="btnl"
		onclick="goUrl('<?php echo Myqee::url('template/index/1/content');?>','_blank')" /></td>
</tr>
<tr>
	<td class="td1" align="right">存放目录</td>
	<td class="td2"><?php echo form::radio('class[content_pathtype]',0,(int)$class['content_pathtype']==0,'onclick="$(\'content_pathtypeDiv\').style.display=\'none\'"'); ?>栏目目录 <?php echo form::radio('class[content_pathtype]',1,(int)$class['content_pathtype']==1,'onclick="$(\'content_pathtypeDiv\').style.display=\'block\'"'); ?>自定义目录
	<div id="content_pathtypeDiv" style="padding:4px 0;<?php if ((int)$class['content_pathtype']==0){echo 'display:none';}?>">&nbsp;<?php echo str_replace("\\",'/',WWWROOT) .' '. form::input('class[content_path]',$class['content_path'],'class="input" size="20"'); ?> <font
		color="#999999">(从www根目录开始)</font></div>
	</td>
</tr>
<tr>
	<td class="td1" align="right">目录存放形式</td>
	<td class="td2">
	<?php echo form::changeinput(
		'class[content_selfpath]',
		$class['content_selfpath'] = $class['content_selfpath']?$class['content_selfpath']:'Y-m-d',
		'class="input" size="10"',
		array(
			'Y-m-d' => '2008-08-09',
			'Y/m-d' => '2008/08-09',
			'Y/m/d' => '2008/08/09',
			'ymd'   => '20080809',
			''      => '不设置目录'
		),
		null,
		$class['content_selfpath']
		);
	?> <span class="helpicon" title="将用date时间函数进行转换，只允许出现a-z0-9_-~/\这些字符<br/>如Y-m-d，Y/m-d等形式，<br/>不需要转换字符请加\例如\d)" >&nbsp;</span>
	</td>
</tr>
<tr>
	<td class="td1" align="right">文件名前缀</td>
	<td class="td2"><?php echo form::input('class[content_prefix]',$class['content_prefix'],'class="input" size="12"');?></td>
</tr>
<tr>
	<td class="td1" align="right">文件名</td>
	<td class="td2">
	<?php echo form::radio('class[content_filenametype]',0,(int)$class['content_filenametype']==0);?>信息ID
	<?php echo form::radio('class[content_filenametype]',1,(int)$class['content_filenametype']==1);?>Time()
	<?php echo form::radio('class[content_filenametype]',2,(int)$class['content_filenametype']==2);?>md5(32位)
	<?php echo form::radio('class[content_filenametype]',3,(int)$class['content_filenametype']==3);?>md5(16位)
	&nbsp;&nbsp;<font class="helpicon"
		title="选择信息ID则必须数据表中有ID匹配字段<br/>选择后面3项则数据表中存在content_name对应字段<br/>更详细说明请见网站帮助中关于栏目设置项">&nbsp;</font>
	</td>
</tr>
<tr>
	<td class="td1" align="right">文件扩展名</td>
	<td class="td2"><?php echo form::changeinput(
		'class[content_suffix]',
		$class['content_suffix']=$class['content_suffix']?$class['content_suffix']:'.html',
		'class="input" size="10"',
		array(
			'.html' => '.html',
			'.htm' => '.htm',
			'.php' => '.php',
			'.shtml'   => '.shtml',
			'.xml'      => '.xml'
		),
		null,
		$class['content_selfpath']
		);
	?> <font color="#999999">(如.html,.xml,.htm等)</font></td>
</tr>
</table>
</div>

<div id="mytagmain_5" style="display: none;">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
<tr>
	<th class="td1" colspan="2">搜索页参数设置</th>
</tr>
<tr>
<td class="td1" align="right" width="120"><b>是否启用搜索功能：</b></td>
<td class="td2">
<?php echo form::radio('class[issearch]',1,$class['issearch']==1?true:false);?>是 
<?php echo form::radio('class[issearch]',0,$class['issearch']==1?false:true);?>否 &nbsp; 
<font color="red">只有选择是，下面的设置才有效</font>
</td>
<tr>
	<td class="td1" align="right">搜索列表模板：</td>
	<td class="td2">&nbsp;<?php echo form::dropdown('class[search_tplid]',$search_tplarray,$class['search_tplid']); ?> &nbsp;&nbsp;<input
		type="button" value="管理搜索模板" class="btnl"
		onclick="goUrl('<?php echo Myqee::url('template/index/1/search');?>','_blank')" /></td>
</tr>
<tr>
	<td class="td1" align="right">默认排序方式：</td>
	<td class="td2">&nbsp;按字段
	<?php echo form::dropdown('class[search_byfield]',$dbfield,$class['search_byfield']?$class['search_byfield']:'id'); ?>
	排列方式：<?php echo form::dropdown('class[search_orderby]',array('DESC'=>'倒序排序','ASC'=>'顺序排序'),$class['search_orderby']); ?>
	</td>
</tr>
</table>

</div>

<div id="mytagmain_6" style="display: none;">
<table border="0" cellpadding="4" cellspacing="1" align="center"
	class="tableborder">
	<tr>
		<th class="td1" colspan="2">参数设置</th>
	</tr>
	<tr>
		<td width="120" class="td1" align="right">是否显示到导航：</td>
		<td class="td2">
		<?php echo form::radio('class[isnavshow]',1,(int)$class['isnavshow']==1); ?>显示
		<?php echo form::radio('class[isnavshow]',0,(int)$class['isnavshow']==0); ?>不显示 &nbsp;
		<font color="#999999">只有父栏目显示到导航，此设置才有效</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">栏目显示排序：</td>
		<td class="td2"><?php echo form::input('class[myorder]',$class['myorder'],'size="6" class="input"') ?> <font
			color="#999999">(值越小越前面)</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">绑定域名：</td>
		<td class="td2"><?php echo form::input('class[hostname]',$class['hostname'],'size="30" class="input" title="留空则为默认<br/>请不要加http://结尾也不需要加/"') ?>
		<input name="class[hostset_tosmallclass]" type="checkbox" value="1" />应用于子栏目
		<input name="class[uptonav][url]" type="checkbox" value="1" />更新导航栏地址
		</td>
	</tr>
	<tr>
		<td class="td1" align="right">栏目缩略图：</td>
		<td class="td2"><?php echo form::imginput('class[classimg]',$class['classimg'],'id="class[classimg]"') ?></td>
	</tr>
	<tr>
		<td class="td1" align="right">栏目简介：</td>
		<td class="td2"><?php echo form::textarea(array(
			'name' => 'class[htmlintro]',
			'value' =>$class['htmlintro'],
			'class' => 'input',
			'cols' => 70,
			'rows' => 6,
		));
		?> 支持HTML</td>
	</tr>
	<tr>
		<td class="td1" align="right">后台管理列表每页：</td>
		<td class="td2">
		<?php echo form::input('class[manage_limit]',$class['manage_limit']?$class['manage_limit']:20,'class="input" size="5"'); ?> 条
		</td>
	</tr>
	<tr>
		<td class="td1" align="right">后台管理排序方式：</td>
		<td class="td2">
		按字段<?php echo form::dropdown('class[manage_orderbyfield]',$dbfield,$class['manage_orderbyfield']); ?>
		排列方式<?php echo form::dropdown('class[manage_orderby]',array('DESC'=>'倒序排序','ASC'=>'顺序排序'),$class['manage_orderby']); ?>
		</td>
	</tr>
</table>
</div>


<div id="mytagmain_7" style="display: none;">
<table border="0" cellpadding="4" cellspacing="1" align="center"
	class="tableborder">
	<tr>
		<th class="td1" colspan="2">SEO设置</th>
	</tr>
	<tr>
		<td class="td1" align="right" width="120">页面关键字：</td>
		<td class="td2"><?php echo form::input('class[keyword]',$class['keyword'],'size="30" class="input"') ?> (keyword)</td>
	</tr>
	<tr>
		<td class="td1" align="right">栏目简介：</td>
		<td class="td2"><?php echo form::textarea(array(
			'name' => 'class[description]',
			'value' =>$class['description'],
			'class' => 'input',
			'cols' => 70,
			'rows' => 6,
		));
		?> (description)</td>
	</tr>
</table>
</div>
<script type="text/javascript">
if (document.location.hash =='#tag1'||document.location.hash =='#tag2'||document.location.hash =='#tag3'||document.location.hash =='#tag4'||document.location.hash =='#tag5'||document.location.hash =='#tag6'||document.location.hash =='#tag7'){
	tag('mytag_'+document.location.hash.substr(4),'mytag','mytagmain');
}
</script>

<table border="0" cellpadding="4" cellspacing="1" align="center"
	class="tableborder" style="border-top: none;">
	<tr>
		<td class="td1" width="120" align="right">&nbsp;</td>
		<td class="td1"><input type="submit" value="保存栏目" class="bbtn" /> <input
			onclick="goback(-1,'<?php echo Myqee::url('class/index'); ?>')"
			type="button" value="返回" class="btns" /></td>
	</tr>
</table>

</form>

<?php View::factory('admin/footer') -> render(TRUE);?>