<?php
$page_title = '参数设置';
$page_index = 'index';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>

<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; 参数设置
</div>

<form method="post" name="myforms" action="<?php echo Myqee::url('index/configsave');?>" target="hiddenFrame">
<div style="width:96%;margin:auto;">
<ul class="ul tag">
	<li class="now" id="mytag_1" onclick="tag(this.id,'mytag','mytagmain');document.location.hash='#tag1'">基本属性</li>
	<li id="mytag_2" onclick="tag(this.id,'mytag','mytagmain');document.location.hash='#tag2'">数据库设置</li>
	<li id="mytag_3" onclick="tag(this.id,'mytag','mytagmain');document.location.hash='#tag3'">安全设置</li>
	<li id="mytag_4" onclick="tag(this.id,'mytag','mytagmain');document.location.hash='#tag4'">上传设置</li>
	<li id="mytag_5" onclick="tag(this.id,'mytag','mytagmain');document.location.hash='#tag5'">水印设置</li>
	<li id="mytag_6" onclick="tag(this.id,'mytag','mytagmain');document.location.hash='#tag6'">路由设置</li>
	<li id="mytag_7" onclick="tag(this.id,'mytag','mytagmain');document.location.hash='#tag7'">模块设置</li>
	<li id="mytag_8" onclick="tag(this.id,'mytag','mytagmain');document.location.hash='#tag8'">会员设置</li>
	<li id="mytag_9" onclick="tag(this.id,'mytag','mytagmain');document.location.hash='#tag9'">其它设置</li>
</ul>
</div>
<div style="clear:both"></div>
<div id="mytagmain_1">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
	<tr>
		<th class="td1" colspan="2">基本属性</th>
	</tr>
	<tr>
		<td class="td1" align="right" width="150">网站名称：</td>
		<td class="td2"><?php echo form::input('core[sitename]',$coreconfig['sitename'],'size="30" class="input"') ?> <font color="#999999">例如：麦琪CMS系统</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">默认前台网站域名：</td>
		<td class="td2"><?php echo form::input('core[mysite_domain]',$coreconfig['mysite_domain'],'size="30" class="input"') ?> <font color="#999999">例如：www.myqee.com</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">默认前台网站地址前缀：</td>
		<td class="td2"><?php echo form::input('core[mysite_url]',$coreconfig['mysite_url'],'size="30" class="input"') ?> <font color="#999999">例如：“/”或“http://www.myqee.com/” 以/结尾</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">动态索引页：</td>
		<td class="td2"><?php echo form::input('core[myqee_page]',$coreconfig['myqee_page'],'size="30" class="input"') ?> <font color="#999999">默认为myqee.php若启用rewrite隐藏文件名，此项可留空。</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">首页模板：</td>
		<td class="td2"><?php echo form::dropdown('core[index_template]',$tplgroup,$coreconfig['index_template']) ?> <input type="button" style="letter-spacing:0" onclick="goUrl('<?php echo Myqee::url('template/index/1/cover');?>','_blank')" class="btn" value="封面模板" /></td>
	</tr>
	<tr>
		<td class="td1" align="right">首页文件名：</td>
		<td class="td2"><?php echo form::input('core[index_filename]',$coreconfig['index_filename'],'size="20" class="input"') ?> <font color="#999999">例如：“index.html”</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">动态页面虚拟后缀：</td>
		<td class="td2"><?php echo form::input('core[url_suffix]',$coreconfig['url_suffix'],'size="6" class="input"') ?> <font color="#999999">用于SEO优化，例如：“.html”，“.php”，可留空</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">批量生成每次生成页面数：</td>
		<td class="td2"><?php echo form::input('core[tohtml_limit]',$coreconfig['tohtml_limit'],'size="6" class="input"') ?> <font color="#999999">建议每次100页，请不要超过1000页/次</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">图片生成函数：</td>
		<td class="td2"><?php echo form::dropdown('core[image][driver]',array('GD'=>'GD库','IMAGEMAGICK'=>'ImageMagick'),$coreconfig['image']['driver']) ?> <font color="#999999">GD库必须是GD2以上版本</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">定时自动执行任务功能：</td>
		<td class="td2"><?php echo form::dropdown('core[allow_task]',array('禁用','启用'),$coreconfig['allow_task']) ?> <font color="#999999">启动任务后，本程序将能够定时执行任务。</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">输出错误Debug信息：</td>
		<td class="td2"><?php echo form::dropdown('core[display_errors]',array('禁用','输出'),$coreconfig['display_errors']) ?> <font color="#999999">内部调试建议开启，外网建议关闭。</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">动态加载文件缓存：</td>
		<td class="td2"><?php echo form::dropdown('core[internal_cache]',array('禁用','手动更新','定时更新'),$coreconfig['internal_cache']>1?2:$coreconfig['internal_cache'],'id="core[internal_cache]" onchange="if (this.value==2){$(\'cache_time\').style.display=\'\'}else{$(\'cache_time\').style.display=\'none\'}"') ?> 
		<span style="display:none;" id="cache_time">缓存时间：<?php echo form::input(array('name'=>'internal_cache','class'=>'input','size'=>'4','title'=>'单位秒，至少2秒，留空则默认1天，即86400','value'=>$coreconfig['internal_cache']>1?$coreconfig['internal_cache']:''));?></span>
		<span class="helpicon" title="主要缓存：配置文件，语言包，项目内所有类、助手、API文件列表<br/>调试阶段推荐关闭缓存功能<br/>发布版本推荐手动更新方式。<br/>手动更新缓存请到：首页管理-&gt;缓存管理里。">&nbsp;</span>
		<script type="text/javascript">
			$('core[internal_cache]').onchange();
		</script>
		</td>
	</tr>
</table>
</div>

<div id="mytagmain_2" style="display:none;">
<?php
$databaseconfig = Myqee::config('database');
?>
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
	<tr>
		<th class="td1" colspan="2">数据库设置</th>
	</tr>
	<tr>
		<td class="td1" align="right" width="150">服务器：</td>
		<td class="td2"><?php echo form::input('database[default][connection][host]',$databaseconfig['default']['connection']['host'],'size="30" class="input"') ?> <font color="#999999">例如：localhost</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">端口：</td>
		<td class="td2"><?php echo form::input('database[default][connection][port]',$databaseconfig['default']['connection']['port'],'size="5" class="input"') ?> <font color="#999999">默认请留空</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">用户名：</td>
		<td class="td2"><?php echo form::input('database[default][connection][user]',$databaseconfig['default']['connection']['user'],'size="20" class="input"') ?> <font color="#999999">例如：root</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">密码：</td>
		<td class="td2"><?php echo form::password('database[default][connection][pass]',$databaseconfig['default']['connection']['pass'],'size="20" class="input"') ?> <font color="#999999">例如：123456</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">&nbsp;</td>
		<td class="td2"><font color="#999999">这里只列出常用的设置项，更详细的设置请直接修改myapp/config/database.php文件</font></td>
	</tr>
</table>
</div>


<div id="mytagmain_3" style="display:none;">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
	<tr>
		<th class="td1" colspan="2">安全设置</th>
	</tr>
	<tr>
		<td class="td1" align="right" width="150">网站密钥：</td>
		<td class="td2"><?php echo form::input('encryption[default][key]',MyqeeCMS::config('encryption.default.key'),'size="40" maxlength="100" class="input"') ?> <font color="#999999">可用字母、数字、字符等混合，越复杂越好</font></td>
	</tr>
</table>
</div>


<div id="mytagmain_4" style="display:none;">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
	<tr>
		<th class="td1" colspan="2">上传设置</th>
	</tr>
	<tr>
		<td class="td1" align="right" width="150">附件URL前缀：</td>
		<td class="td2"><?php echo form::input('core[upload][urlpath]',$coreconfig['upload']['urlpath'],'size="28" maxlength="100" class="input"') ?> <font color="#999999">默认为“/upload/”可以http://开头</font></td>
	</tr>
	<tr>
		<td class="td1" align="right" width="150">上传路径：</td>
		<td class="td2"><?php echo form::input('core[upload][filepath]',$coreconfig['upload']['filepath'],'size="28" maxlength="200" class="input"') ?> 
		<font color="#999999">默认为“upload”可以是绝对路径，也可以是相对于wwwroot的相对路径</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">最大上传大小：</td>
		<td class="td2"><?php echo form::input('core[upload][maxsize]',$coreconfig['upload']['maxsize'],'size="10" maxlength="100" class="input"') ?> 
		<span class="helpicon" title="您当前PHP的设置最大POST为<b><?php echo get_cfg_var("post_max_size");?></b><br/>允许上传文件最大为<b><?php echo (get_cfg_var("upload_max_filesize")?get_cfg_var("upload_max_filesize"):"不允许上传附件")?></b><br/>最终能够成功上传的大小为您设置的值和这两个值中最小值！<br/><br/>您可修改php.ini文件中post_max_size值和upload_max_filesize两个值以满足需求。">&nbsp;</span> 
		<font color="#999999">0表示不限制，单位为kb，1MB=1024 KB,1G=1024 MB=1048576 KB</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">允许上传的文件类型：</td>
		<td class="td2"><?php echo form::textarea('core[upload][extension]',$coreconfig['upload']['extension'],'class="input" cols="60" rows="4"'); ?> 
		<font color="#999999">用“,”分开，例如“jpg,gif,rar”，不区分大小写，留空则不限制。</font></td>
	</tr>
	<tr>
	<td class="td1" align="right">目录存放形式：</td>
	<td class="td2">
	<?php echo form::changeinput(
		'core[upload][selfpath]',
		$coreconfig['upload']['selfpath'] = $coreconfig['upload']['selfpath']?$coreconfig['upload']['selfpath']:'Y-m-d',
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
	</td></tr>
	<tr>
		<td class="td1" align="right">命名方式算法：</td>
		<td class="td2"><?php echo form::dropdown('core[upload][setname]',array(''=>'使用原名称','time'=>'时间数字(18位)','abc123'=>'随机字符(20位)','md5'=>'md5(32位)','sha1'=>'sha1(40位)'),$coreconfig['upload']['setname']) ?> 
		<span class="helpicon" title="时间数字类似于：200809252207188547<br/>随机字符类似于：y9MraJjtX2lG1YBiCLwv<br/>md5类似于：e9fbf6daf353f429b1967473d8fc4d72<br/>sha1类似于：51136b880dca89d9bd17496d0ace5f7c6ddc206c">&nbsp;</span>
		<font color="#999999">对上传的文件名称进行重命名。</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">上传图片是否打水印：</td>
		<td class="td2"><?php echo form::radio('core[upload][autowatermark]',1,(int)$coreconfig['upload']['autowatermark']==1) ?>是 
		<?php echo form::radio('core[upload][autowatermark]',0,(int)$coreconfig['upload']['autowatermark']==0) ?>否 
		<font color="#999999">给上传的图片打水印，具体设置请切换至水印设置！</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">是否开启智能缩略图生成：</td>
		<td class="td2"><?php echo form::radio('core[aptitudethumb]',1,!isset($coreconfig['aptitudethumb'])||$coreconfig['aptitudethumb']==1) ?>是 
		<?php echo form::radio('core[aptitudethumb]',0,isset($coreconfig['aptitudethumb'])&&(int)$coreconfig['aptitudethumb']===0) ?>否
		<font color="#999999">本系统默认可以根据访问需求智能的生成缩略图，建议开启！</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">图片自动生成缩略图：</td>
		<td class="td2">
		<?php echo form::radio('core[upload][autothumb]',1,(int)$coreconfig['upload']['autothumb']==1) ?>是 
		<?php echo form::radio('core[upload][autothumb]',0,(int)$coreconfig['upload']['autothumb']==0) ?>否  
		<font color="#999999">若系统开启智能缩略图生成功能，此选项可关闭！</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">默认缩略图宽度：</td>
		<td class="td2"><?php echo form::input('core[upload][thumbwidth]',$coreconfig['upload']['thumbwidth'],'size="5" maxlength="4" class="input"') ?> 
		<font color="#999999">推荐120或80</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">默认缩略图高度：</td>
		<td class="td2"><?php echo form::input('core[upload][thumbheight]',$coreconfig['upload']['thumbheight'],'size="5" maxlength="4" class="input"') ?> 
		<font color="#999999">推荐90或60</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">对上传文件进行权限设置：</td>
		<td class="td2"><?php echo form::input('core[upload][chmod]',$coreconfig['upload']['chmod'],'size="5" maxlength="4" class="input"') ?> 
		<font color="#999999">推荐0644</font></td>
	</tr>
</table>
</div>



<div id="mytagmain_5" style="display:none;">
<?php
$watermarktype = array('0'=>'随机','1'=>'左上角');
?>
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
	<tr>
		<th class="td1" colspan="2">水印设置</th>
	</tr>
	<tr>
		<td class="td1" align="right" width="150">水印位置：</td>
		<td class="td2">
<table border="0" cellpadding="6" cellspacing="1" class="tableborder" style="width:110px;text-align:center;">
<tr>
<td class="td1"><?php echo form::radio('core[watermark][position]',1,$coreconfig['watermark']['position']==1) ?></td>
<td class="td1"><?php echo form::radio('core[watermark][position]',2,$coreconfig['watermark']['position']==2) ?></td>
<td class="td1"><?php echo form::radio('core[watermark][position]',3,$coreconfig['watermark']['position']==3) ?></td>
</tr>
<tr>
<td class="td1"><?php echo form::radio('core[watermark][position]',4,$coreconfig['watermark']['position']==4) ?></td>
<td class="td1"><?php echo form::radio('core[watermark][position]',5,$coreconfig['watermark']['position']==5) ?></td>
<td class="td1"><?php echo form::radio('core[watermark][position]',6,$coreconfig['watermark']['position']==6) ?></td>
</tr>
<tr>
<td class="td1"><?php echo form::radio('core[watermark][position]',7,$coreconfig['watermark']['position']==7) ?></td>
<td class="td1"><?php echo form::radio('core[watermark][position]',8,$coreconfig['watermark']['position']==8) ?></td>
<td class="td1"><?php echo form::radio('core[watermark][position]',9,$coreconfig['watermark']['position']==9) ?></td>
</tr>
</table>
<?php echo form::radio('core[watermark][position]',0,$coreconfig['watermark']['position']==0) ?>随机
<font color="#999999">选择水印的位置！</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">水平偏移量：</td>
		<td class="td2"><?php echo form::input('core[watermark][posx]',$coreconfig['watermark']['posx'],'size="5" maxlength="20" class="input"') ?> 
		<font color="#999999">0表示不偏移，可以是正数也可以是负数，也可以是一个范围内的随机数，数值用,隔开。例如：<b>-10,20</b></font></td>
	</tr>
	<tr>
		<td class="td1" align="right">垂直偏移量：</td>
		<td class="td2"><?php echo form::input('core[watermark][posy]',$coreconfig['watermark']['posy'],'size="5" maxlength="10" class="input"') ?> 
		<font color="#999999">同上。</font><span class="helpicon" title="<b>区域内随机位置的技巧：</b><br/><font color='green'>例如需要水印产生在右下角某一区域内随机位置，方法如下：</font><br/>水印位置选择右下角，水平、垂直偏移量都输入“0,30”，<br/>这样水印就会在右下角30*30的范围内随机产生了。">&nbsp;</span></td>
	</tr>
	<tr>
		<td class="td1" align="right">对GIF图片不打水印：</td>
		<td class="td2">
		<?php echo form::radio('core[watermark][gifwatermark]',1,!isset($coreconfig['watermark']['gifwatermark'])||$coreconfig['watermark']['gifwatermark']==1) ?>是 
		<?php echo form::radio('core[watermark][gifwatermark]',0,isset($coreconfig['watermark']['gifwatermark'])&&(int)$coreconfig['watermark']['gifwatermark']===0) ?>否
		<font color="#999999">当采用GD2打水印时可能导致动画丢失，选择此项可排除GIF图片。</font></td>
	</tr>
	<tr>
	<td class="td1" align="right">生成图片质量：</td>
	<td class="td2"><select name="core[watermark][quality]">
<script type="text/javascript">
for (i=1;i<=100;i++){
document.write('<option value="'+i+'"'+(i==<?php echo (int)$coreconfig['watermark']['quality'];?>?' selected="selected"':'')+'>'+i+'</option>');
}
</script>
</select>
	<font color="#999999">此项只对上传的图片是jpg有效，100为最佳效果，保存的图像文件也最大。60-90之间为佳，建议值80。</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">选择使用水印方式：</td>
		<td class="td2"><?php echo form::dropdown('core[watermark][type]',array('文字','图片','随机使用'),$coreconfig['watermark']['type'],'id="core[watermark][type]" onchange="if (this.value==0 || this.value==1){$(\'mytag2_\'+(1-this.value)).onclick();}"'); ?> 
		<font color="#999999">选择对应的水印方式请确保相关设置正确。</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">参数设置：</td>
		<td class="td2">
<div style="margin:auto;">
<ul class="ul tag">
	<li class="now" id="mytag2_0" onclick="if($('core[watermark][type]').value==0){return;}tag(this.id,'mytag2','mytagmain2');">图片水印</li>
	<li id="mytag2_1" onclick="if($('core[watermark][type]').value==1){return;}tag(this.id,'mytag2','mytagmain2');">文字水印</li>
</ul>
</div>
<div style="clear:both"></div>
<div id="mytagmain2_0">

<table border="0" cellpadding="3" cellspacing="1" class="tableborder" style="width:600px;">
<tr>
<th class="td1" colspan="2">图片水印设置</th>
</tr>
<tr>
<td class="td1" align="right" width="90">水印图片路径：</td>
<td class="td2"><?php echo form::input('core[watermark][image][path]',$coreconfig['watermark']['image']['path'],'size="20" class="input"') ?> <font color="#999999">位于application/watermarkimage目录</font>
</td>
</tr>
<tr>
<td class="td1" align="right">透明度：</td>
<td class="td2"><select name="core[watermark][image][opacity]">
<script type="text/javascript">
for (i=0;i<=100;i++){
document.write('<option value="'+i+'"'+(i==<?php echo (int)$coreconfig['watermark']['image']['opacity'];?>?' selected="selected"':'')+'>'+i+'</option>');
}
</script>
</select>
<font color="#999999">100表示不透明。</font>注意，若水印图片是PNG透明图，此项请务必选择100。</td>
</tr>
</table>
</div>
<div id="mytagmain2_1" style="display:none;">
<table border="0" cellpadding="3" cellspacing="1" class="tableborder" style="width:600px;">
<tr>
<th class="td1" colspan="2">文字水印设置</th>
</tr>
<tr>
<td class="td1" align="right" width="90">文字内容：</td>
<td class="td2"><?php echo form::input('core[watermark][font][text]',$coreconfig['watermark']['font']['text'],'size="30" maxlength="200" class="input"') ?></td>
</tr>
<tr>
<td class="td1" align="right">文字颜色：</td>
<td class="td2"><?php echo form::input('core[watermark][font][color]',$coreconfig['watermark']['font']['color'],'size="8" maxlength="7" class="input"') ?></td>
</tr>
<tr>
<td class="td1" align="right">水印字体：</td>
<td class="td2"><?php echo form::input('core[watermark][font][fonts]',$coreconfig['watermark']['font']['fonts'],'size="16" class="input"') ?> <font color="#999999">位于userapp目录的fonts目录</font>
</td>
</tr>
<tr>
<td class="td1" align="right">文字大小：</td>
<td class="td2"><?php echo form::input('core[watermark][font][size]',$coreconfig['watermark']['font']['size'],'size="8" class="input"') ?> 
默认为16。</td>
</tr>
</table>
</div>
</td>
	</tr>
	<tr>
	<td class="td1" align="right">预览水印设置效果：</td>
	<td class="td2"><input onclick="goUrl('<?php echo ADMIN_IMGPATH.'/admin/imageview.html#'.Myqee::url('index/testwatermark');?>'+getwatermarkque(),'_blank')" type="button" value="预览设置效果" class="btnl" title="使用修改后的参数预览缩略图效果<br/>预览时不会覆盖原有配置！" /></td>
	</tr>
</table>
<script type="text/javascript">
$('core[watermark][type]').onchange();
</script>
</div>

<script type="text/javascript">
function getwatermarkque(){
	var str = '';
	var allkey = ['[position]','[posx]','[posy]','[type]','[gifwatermark]','[quality]','[font][text]','[font][color]','[font][fonts]','[font][size]','[image][path]','[image][opacity]'];
	for(var i=0;i<allkey.length;i++){
		str += '&watermark' + allkey[i] + '=' + escape(getvalue('core[watermark]'+allkey[i]));
	}
	return escape('?'+str.substr(1));
}
function getvalue(name){
	var obj = document.forms['myforms'][name];
	if (!obj)return '';
	if (obj.value){
		return obj.value;
	}else{
		for (var i=0;i<obj.length;i++){
			if (obj[i].checked){
				return obj[i].value||'';
			}
		}
	}
	return '';
}
</script>


<div id="mytagmain_6" style="display:none;">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
	<tr>
		<th class="td1" colspan="2">路由设置</th>
	</tr>
	<tr>
		<td class="td1" align="right" width="150">是否启用：</td>
		<td class="td2"><?php echo form::radio('core[useroutes]',1,(int)$coreconfig['useroutes']==1) ?>是 
		<?php echo form::radio('core[useroutes]',0,(int)$coreconfig['useroutes']==0) ?>否 
		<font color="#999999">只有启用路由功能，下面的设置才会生效！</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">安全模式：</td>
		<td class="td2"><?php echo form::radio('core[saferoutes]',1,$coreconfig['saferoutes']==1) ?>是 
		<?php echo form::radio('core[saferoutes]',0,$coreconfig['saferoutes']==0) ?>否
		<font color="#999999">启用安全模式，将自动忽略路由正则的错误，不建议开启！</font></td>
	</tr>
	<tr>
		<td class="td1" align="right" valign="top"><br/>路由正则：</td>
		<td class="td2"><?php
$tmpstr = '';
if (is_array($coreconfig['routes']['key'])){
	foreach ($coreconfig['routes']['key'] as $value){
		$maxlen = max(strlen($value),$maxlen);
	}
	foreach ($coreconfig['routes']['key'] as $key => $value){
		$tmpstr .= str_pad(substr($value,1,-2),$maxlen) .'=> ' .$coreconfig['routes']['value'][$key] ."\n";
	}
}
?><textarea name="core[routes]" class="input" style="width:96%;height:180px;"><?php echo $tmpstr;?></textarea><font color="#666" style="line-height:1.8em"><br/>网站路由设置可以方便的转换页面地址到另一个页面，类似apache的rewrite，少数的路由规则不会影响系统性能。<br/>每行一个，正则匹配，避免产生重复替代，请尽量用^开头，不设置请留空。条件和结果之间用=>分开。<br/>例如：<br/>
^test(.*) => mynewurl\${1}<br/>
^newfloder\/(.*) => upload/\${1}<br/>
^myqee\/page_([0-9]+)\.html$ => upload/list_\${1}.php<br/>
^sina.htm.* => http://www.sina.com/<br/>
注意：不需要加/.../i，匹配会忽略大小写，任何一条错误的正则都将导致全部路由失效，请测试后使用！本路由只针对path_info部分，不包括网页?后面的内容。<br/>
未尽细则请参照PHP手册的preg_replace函数及PHP正则规则
		</font></td>
	</tr>
</table>
</div>


<div id="mytagmain_7" style="display:none;">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" id="myEditorTable">
	<tr>
		<th class="td1" width="52">排序操作</th>
		<th class="td1" width="120">模块目录</th>
		<th class="td1" width="150">名称</th>
		<th class="td1">模块网址</th>
		<th class="td1" width="70">导航栏显示</th>
		<th class="td1" width="100">更新导航栏域名</th>
		<th class="td1" width="60">是否启用</th>
	</tr>
</table>

<table cellspacing="1" cellpadding="4" bgcolor="#ffffff" class="tableborder" align="center" border="0" style="border-top:none"><tr>
	<td class="td1"><input type="button" value="上移选定" onclick="if (!myTable['mytable'])return;myTable['mytable'].up(1);" class="btn" />
	<input type="button" value="下移选定" class="btn" onclick="if (!myTable['mytable'])return;myTable['mytable'].down();" />
	<input type="button" value="按模块目录排序" onclick="if (!myTable['mytable'])return;myTable['mytable'].sort(1);" class="btnl" /> 
	<input type="button" value="按模块名称排序" onclick="if (!myTable['mytable'])return;myTable['mytable'].sort(2);" class="btnl" /> 
	<input type="button" value="恢复" class="btns" onclick="ymPrompt.confirmInfo('包括其它栏的所有修改将不保存，是否要恢复？',400,null,'请选择',function(t){if(t=='ok')goback(0)});" />
	</td>
</tr><tr>
	<td class="td2">
	怎样添加模块？在主APP目录（即：<?php echo MYAPPPATH;?>）的modules目录内创建模块目录即可，每一个模块内都可添加自有的classes、views等。
	</td>
</tr></table>
</div>



<script type="text/javascript">
	function showEditorTable(){
		var dataArray = <?php echo $modules;?>;
		var tableInfo= new Array();
		var i = 0;
		if (dataArray.length==0)return;
		for (var item in dataArray)
		{
			tableInfo[i] = new Array(
			'<center>'+item+'</center>',
			'<center><input type="text" name="core[modules]['+item+'][name]" class="input" size="22" value="'+(dataArray[item]['name']||item)+'" maxlength="50" /></center>',
			'<!--[if lte IE 6]><div style="position:absolute;height:24px;"><div style="position:absolute;top:-4px;width:95%;padding:0 12px 0 0;"><![endif]--><input type="text" name="core[modules]['+item+'][url]" class="input" style="width:92%" size="22" value="'+(dataArray[item]['url']||item)+'" maxlength="300" /><!--[if lte IE 6]></div></div>&nbsp;<![endif]-->',
			'<center><select name="core[modules]['+item+'][navshow]"><option value="1"'+(dataArray[item]['navshow']==1?' selected="selected"':'')+'>是</option><option value="0"'+(dataArray[item]['navshow']==1?'':' selected="selected"')+'>否</option></center>',
			'<center><input type="checkbox" name="core[modules_renewnav]['+item+']" value="1" title="启用导航栏显示且修改了域名时才有必要" />更新</center>',
			'<center><select name="core[modules]['+item+'][isuse]"><option value="1"'+(dataArray[item]['isuse']==1?' selected="selected"':'')+'>是</option><option value="0"'+(dataArray[item]['isuse']==1?'':' selected="selected"')+'>否</option></center>'
			);
			i++;
		}
		myTable['mytable']=new CreateTable('myEditorTable',tableInfo);
	}

	showEditorTable();
</script>




<div id="mytagmain_8" style="display:none;">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
	<tr>
		<th class="td1" colspan="2">会员参数设置</th>
	</tr>
	<tr>
		<td class="td1" align="right" width="150">用户数据表：</td>
		<td class="td2"><?php echo form::input('member[dbname]',$member['dbname'],'size="20" maxlength="100" class="input"') ?></td>
	</tr>
	<tr>
		<td class="td1" align="right" width="150">用户组数据表：</td>
		<td class="td2"><?php echo form::input('member[group_dbname]',$member['group_dbname'],'size="20" maxlength="100" class="input"') ?></td>
	</tr>
	<tr>
		<td class="td1" align="right" width="150">头像路径设置：</td>
		<td class="td2"><?php echo form::input('member[imagepath]',$member['imagepath'],'size="40" maxlength="100" class="input"') ?></td>
	</tr>
	<tr>
		<td class="td1" align="right">用户空间地址：</td>
		<td class="td2"><?php echo form::input('core[home_url]',$coreconfig['home_url'],'size="30" class="input"') ?> <font color="#999999">最后不需要加"/"。例如：http://home.myurl.com</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">论坛地址：</td>
		<td class="td2"><?php echo form::input('core[bbs_url]',$coreconfig['bbs_url'],'size="30" class="input"') ?> <font color="#999999">最后不需要加"/"。例如：http://bbs.myurl.com</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">是否启用UCenter：</td>
		<td class="td2"><?php echo form::radio('core[use_ucenter]',1,$coreconfig['use_ucenter']==1?true:false) ?>是 
		<?php echo form::radio('core[use_ucenter]',0,$coreconfig['use_ucenter']==1?false:true) ?>否 <font color="#999999">是否开启Discuz的Ucenter</font></td>
	</tr>
	<tr>
		<td class="td1" align="right">是否启用UCHOME：</td>
		<td class="td2"><?php echo form::radio('core[use_uchome]',1,$coreconfig['use_uchome']==1?true:false) ?>是 
		<?php echo form::radio('core[use_uchome]',0,$coreconfig['use_uchome']==1?false:true) ?>否 <font color="#999999">是否开启Discuz的UCHOME</font></td>
	</tr>
</table>
</div>


<div id="mytagmain_9" style="display:none;" disable="disable">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
	<tr>
		<th class="td1" colspan="2">其它设置</th>
	</tr>
	<tr>
		<td class="td1" align="right" width="150">后台首页欢迎语：</td>
		<td class="td2"><?php echo form::input('core[admin_welcome]',$coreconfig['admin_welcome'],'size="40" maxlength="100" class="input"') ?> <font color="#999999">后台首页欢迎语，留空则为默认</font></td>
	</tr>
</table>
</div>



<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-top:none;">
<tr>
	<td class="td1" width="150" align="right">&nbsp;</td>
	<td class="td1"><input type="submit" value="保存配置" class="bbtn" /> 
	<input onclick="goback(-1,'<?php echo Myqee::url('index/index'); ?>')" type="button" value="返回" class="btns" /> 
	<input type="button" value="恢复到上次设置" title="数据库设置，安全设置不能恢复！" class="btnl" onclick="confirm('将恢复成上一个备份配置，同时，当前的配置被设为备份<br/><br/>操作恢复备份将存在一定风险，是否要恢复？',500,null,'请选择',function(t){if(t=='ok')goUrl('<?php echo Myqee::url('index/configbak');?>','hiddenFrame')});"  /> 
	</td>
</tr>
</table>

</form>
<script type="text/javascript">
if (document.location.hash =='#tag1'||document.location.hash =='#tag2'||document.location.hash =='#tag3'||document.location.hash =='#tag4'||document.location.hash =='#tag5'||document.location.hash =='#tag6'||document.location.hash =='#tag7'||document.location.hash =='#tag8'||document.location.hash =='#tag9'){
	tag('mytag_'+document.location.hash.substr(4),'mytag','mytagmain');
}
</script>

<?php View::factory('admin/footer') -> render(TRUE);?>