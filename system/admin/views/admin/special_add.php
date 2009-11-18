<?php
$page_index = 'class';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>

<div class="loaction">您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('special/index');?>">专题管理</a> -&gt; <?php echo $page_title;?> </div>

<form method="post" name="myforms" action="<?php echo Myqee::url('special/save');?>" target="hiddenFrame">
  <input type="hidden" name="info[sid]" value="<?php echo $info['sid'];?>" />
  <div class="mainTable">
    <ul class="ul tag" id="mytag">
      <li class="now" id="mytag_1"
		onclick="tag(this.id,'mytag','mytagmain','mytag');document.location.hash='#tag1'">基本属性</li>
      <li id="mytag_2"
		onclick="tag(this.id,'mytag','mytagmain','mytag');document.location.hash='#tag2'">封面设置</li>
      <li id="mytag_3"
		onclick="tag(this.id,'mytag','mytagmain','mytag');document.location.hash='#tag3'">列表设置</li>
      <li id="mytag_4"
		onclick="tag(this.id,'mytag','mytagmain','mytag');document.location.hash='#tag4'">其它设置</li>
      <li id="mytag_5"
		onclick="tag(this.id,'mytag','mytagmain','mytag');document.location.hash='#tag5'">SEO设置</li>
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
        <td class="td1" align="right" width="120"><font color="red">*</font>专题名称：</td>
        <td class="td2"><?php echo form::input('info[title]',$info['title'],'id="info[title]" size="30" class="input"') ?>
          <input type="button" class="btnl" value="生成拼音目录" onclick="get_pinyin_path()" /></td>
      </tr>
      <tr>
        <td class="td1" align="right" width="120"><font color="red">*</font>专题分类：</td>
        <td class="td2"><?php echo form::changeinput('info[cate]',$info['cate'],' size="15" class="input"',$tplcate,array('默认'=>'默认'));?></td>
      </tr>
      <tr>
        <td class="td1" align="right"><font color="red">*</font>所属栏目：</td>
        <td class="td2"><?php echo form::classlist('info[classides][]',$classtree,'size="18" multiple="multiple"  class="select" style="width:420px;" id="info[classides]" ',$info['classides']);?></td>
      </tr>
      <tr>
        <td class="td1" align="right"><font color="red">*</font>是否包含子栏目：</td>
        <td class="td2"><?php echo form::checkbox('info[isrecursion]',$info['isrecursion'],$info['isrecursion']);?></td>
      </tr>
      <tr>
        <td class="td1" align="right">指定专题目录：</td>
        <td class="td2"><div style="float: left; padding-right: 5px;"><?php echo form::checkbox('info[isnothtml]',0,(int)$info['isnothtml'] ==0,'onfocus="this.blur()" id="info[isnothtml]" onclick="if (this.checked){$(\'isoutinfoDiv\').style.display=\'block\'}else{$(\'isoutinfoDiv\').style.display=\'none\'}"');?></div>
          <div id="isoutinfoDiv" style="float:left;<?php if ((int)$info['isnothtml'] !=0){echo 'display:none;';}?>">
            <?php echo str_replace("\\",'/',WWWROOT) .' '. form::input('info[filepath]',$info['filepath'],'id="info[filepath]" class="input" size="20"'); ?> <font
		color="#999999">(从www根目录开始,可以使用 ../ 跳出根目录)</font>
          </div>
          <div id="isoutinfoDiv2" style="display: none; line-height: 1.9em;"></div>
          &nbsp;<span class="helpicon" title="若不指定专题目录，则本专题将全部采用动态读取方式！">&nbsp;</span>
          <script type="text/javascript">
			$('info[isnothtml]').onclick();
		</script></td>
      </tr>
    </table>
<script type="text/javascript">
function get_pinyin_path(){
	var obj = $('info[title]');
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
			$('info[filepath]').value += arr['pinyin'];
		}
	}
	ajax.runAJAX();
}
</script>
  </div>
  <div id="mytagmain_2" style="display: none;">
    <table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
      <tr>
        <th class="td1" colspan="2">专题封面设置</th>
      </tr>
      <tr>
        <td class="td1" align="right" width="120"><b>是否使用封面：</b></td>
        <td class="td2"><?php echo form::radio('info[iscover]',1,$info['iscover']==1?true:false);?>是 <?php echo form::radio('info[iscover]',0,$info['iscover']==1?false:true);?>否 &nbsp; <font color="red">只有选择是，下面的设置才有效</font></td>
      </tr>
      <tr>
        <td class="td1" align="right" width="50" height="26">输出方式：</td>
        <td class="td2"><?php echo form::radio('info[cover_tohtml]','0',$info['cover_tohtml']==0,'onclick="if(this.checked){$(\'cover_tohtmlshow\').style.display=\'none\';}"');?>生成静态封面页 <?php echo form::radio('info[cover_tohtml]','1',$info['cover_tohtml']==1,'onclick="if(this.checked){$(\'cover_tohtmlshow\').style.display=\'\';}" id="info[cover_tohtml]_1"');?>使用动态封面输出 <span style="display: none;" id="cover_tohtmlshow"> &nbsp; 缓存时间(秒)：<?php echo form::input('info[cover_cachetime]',(int)$info['cover_cachetime'],'size="4"')?> 0表示不缓存 </span>
          <script type="text/javascript">if ($('info[cover_tohtml]_1').checked){$('cover_tohtmlshow').style.display='';}</script></td>
      </tr>
      <tr>
        <td class="td1" align="right">封面模板：</td>
        <td class="td2"><?php echo form::dropdown('info[cover_tplid]',$cover_tplarray,$info['cover_tplid']); ?> &nbsp;&nbsp;
          <input
			type="button" value="管理封面模板" class="btnl"
			onclick="goUrl('<?php echo Myqee::url('template/index/1/cover');?>','_blank')" /></td>
      </tr>
       <tr>
        <td class="td1" align="right">文件名：</td>
        <td class="td2"><?php echo form::input('info[cover_filename]',$info['cover_filename']?$info['cover_filename']:'index.html','class="input" size="20"'); ?> </td>
      </tr>
    </table>
  </div>
  <div id="mytagmain_3" style="display: none;">
    <table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
      <tr>
        <th class="td1" colspan="2">专题列表设置</th>
      </tr>
      <tr>
        <td class="td1" align="right"><b>是否使用列表页：</b></td>
        <td class="td2"><?php echo form::radio('info[islist]',1,$info['islist']==1?true:false);?>是 <?php echo form::radio('info[islist]',0,$info['islist']==1?false:true);?>否 &nbsp; <font color="red">只有选择是，下面的设置才有效，选择列表则封面无效</font></td>
      <tr>
        <td class="td1" align="right" width="120" height="26">输出方式：</td>
        <td class="td2"><?php echo form::radio('info[list_tohtml]','0',$info['list_tohtml']==0,'onclick="if(this.checked){$(\'list_tohtmlshow\').style.display=\'none\';}"');?>生成静态列表页 <?php echo form::radio('info[list_tohtml]','1',$info['list_tohtml']==1,'onclick="if(this.checked){$(\'list_tohtmlshow\').style.display=\'\';}" id="info[list_tohtml]_1"');?>使用动态列表输出 <span style="display: none;" id="list_tohtmlshow"> &nbsp; 缓存时间(秒)：<?php echo form::input('info[list_cachetime]',(int)$info['list_cachetime'],'size="4"')?> 0表示不缓存 </span>
          <script type="text/javascript">if ($('info[list_tohtml]_1').checked){$('list_tohtmlshow').style.display='';}</script></td>
      </tr>
      <tr>
        <td class="td1" align="right">文件名：</td>
        <td class="td2"><?php echo form::input('info[list_filename]',$info['list_filename']?$info['list_filename']:'list_{{page}}.html','class="input" size="20"'); ?> &nbsp;页码用<font
			color="red">{{page}}</font>代替，例如list_{{page}}.html</td>
      </tr>
      <tr>
        <td class="td1" align="right">列表模板：</td>
        <td class="td2">&nbsp;<?php echo form::dropdown('info[list_tplid]',$list_tplarray,$info['list_tplid']); ?> &nbsp;&nbsp;
          <input
			type="button" value="管理列表模板" class="btnl"
			onclick="goUrl('<?php echo Myqee::url('template/index/1/list');?>','_blank')" /></td>
      </tr>
     
      <tr>
        <td class="td1" align="right">每页显示：</td>
        <td class="td2">&nbsp;<?php echo form::input('info[list_pernum]',$info['list_pernum']>0?$info['list_pernum']:20,'size="5" class="input"'); ?> 条信息 </td>
      </tr>
      <tr>
        <td class="td1" align="right">列出页码：</td>
        <td class="td2">&nbsp;<?php echo form::input('info[list_count]',(int)$info['list_count'],'size="5" class="input"'); ?> 条	 <font
			color="#999999">(0为显示所有记录)</font></td>
      </tr>
      <tr>
		<td class="td1" align="right">排序方式：</td>
		<td class="td2">&nbsp;按字段<?php echo form::dropdown('info[list_byfield]',$dbfield,$info['list_byfield']?$info['list_byfield']:'id'); ?>
		排列方式<?php echo form::dropdown('info[list_orderby]',array('DESC'=>'倒序排序','ASC'=>'顺序排序'),$info['list_orderby']); ?>
		</td>
	</tr>
    </table>
  </div>
  <div id="mytagmain_4" style="display: none;">
    <table border="0" cellpadding="4" cellspacing="1" align="center"
	class="tableborder">
      <tr>
        <th class="td1" colspan="2">参数设置</th>
      </tr>
      <tr>
        <td class="td1" align="right">专题显示排序：</td>
        <td class="td2"><?php echo form::input('info[myorder]',$info['myorder'],'size="6" class="input"') ?> <font
			color="#999999">(值越小越前面)</font></td>
      </tr>
      <tr>
        <td class="td1" align="right">绑定域名：</td>
        <td class="td2"><?php echo form::input('info[hostname]',$info['hostname'],'size="30" class="input" title="留空则为默认<br/>请不要加http://结尾也不需要加/"') ?> </td>
      </tr>
      <tr>
        <td class="td1" align="right">专题缩略图：</td>
        <td class="td2"><?php echo form::imginput('info[thumb]',$info['thumb'],'id="class[thumb]"') ?></td>
      </tr>
      <tr>
        <td class="td1" align="right">专题简介：</td>
        <td class="td2"><?php echo form::textarea(array(
			'name' => 'info[htmlintro]',
			'value' =>$info['htmlintro'],
			'class' => 'input',
			'cols' => 70,
			'rows' => 6,
		));
		?> 支持HTML</td>
      </tr>
      <tr>
        <td class="td1" align="right">后台管理列表每页：</td>
        <td class="td2"><?php echo form::input('info[manage_pernum]',$info['manage_pernum']?$info['manage_pernum']:20,'class="input" size="5"'); ?> 条 </td>
      </tr>
    </table>
  </div>
  <div id="mytagmain_5" style="display: none;">
    <table border="0" cellpadding="4" cellspacing="1" align="center"
	class="tableborder">
      <tr>
        <th class="td1" colspan="2">SEO设置</th>
      </tr>
      <tr>
        <td class="td1" align="right" width="120">页面关键字：</td>
        <td class="td2"><?php echo form::input('info[keyword]',$info['keyword'],'size="30" class="input"') ?> (keyword)</td>
      </tr>
      <tr>
        <td class="td1" align="right">专题简介：</td>
        <td class="td2"><?php echo form::textarea(array(
			'name' => 'info[description]',
			'value' =>$info['description'],
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
      <td class="td1"><input type="submit" value="保存专题" class="bbtn" />
        <input
			onclick="goback(-1,'<?php echo Myqee::url('class/index'); ?>')"
			type="button" value="返回" class="btns" /></td>
    </tr>
  </table>
</form>
<?php View::factory('admin/footer') -> render(TRUE);?>
