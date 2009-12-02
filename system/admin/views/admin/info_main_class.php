<?php
$page_index = 'info';
if ($showheader == 'fullpage'):
	$page_title = '信息管理';
	View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<script type="text/javascript">
	function changeHeight(){return false;}
</script>
<?php
else:
	$showheader = '';
	View::factory('admin/header_frame') -> render(TRUE);
?>
<script type="text/javascript">
function setLink(id){
try{
	var mylink=parent.$('classLinkDiv');
	if (!mylink)return;
	var objs = mylink.getElementsByTagName('a');
	for (var i in objs){
		if (objs[i].id){
			if (objs[i].id=='classLink_'+id){
				objs[i].style.fontWeight = 'bold';
			}else{
				objs[i].style.fontWeight = 'normal';
			}
		}
	}
}catch(e){}
}
if (self.location.href != parent.location.href){
	setLink(<?php echo $class['classid']?>);
}
</script>
</head>
<body>
<?php
endif;
?>
<script type="text/javascript">
function gohistory(go){
	try {
		if( typeof(parent.goHistory)=='function'){
			parent.goHistory(go);
			return;
		}
	}catch(e){goback(go);}
}
</script>
<div class="loaction">
<div style="float: right"><input type="button" onclick="gohistory();return false;" class="btn" style="margin:0" value="返回上一页" /></div>
<div style="line-height:2em;">
您的位置：<a href="<?php echo Myqee::url('index');?>" target="_top">管理首页</a> -&gt; 
<a href="<?php echo Myqee::url('info/main'),($showheader=='fullpage'?'?fullpage=yes':'');?>">内容管理</a>
<?php
if (is_array($location)){
	foreach ($location as $item){
		echo ' -&gt; <a href="'.Myqee::url('info/myclass/1/'.$item['classid']).'">'.$item['classname'].'</a>';
	}
}
?>
<div class="clear"></div>
</div>
</div>
<iframe width="1" height="1" name="hiddenFrame" id="hiddenFrame" src="about:blank" style="display:none;"></iframe>
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
<tr>
	<th class="td1"><div style="float:right;font-weight:normal">
<form method="get" action="<?php echo Myqee::url( 'info/myclass/1/'.$class['classid']);?>" name="searchinfo">
<select size="1" name="search[showspecial]" id="search[showspecial]" style="width:120px;" onchange="document.forms['searchinfo'].submit()">
	<option value="">筛选信息</option>
	<optgroup label="审核信息"><option value="isshow=0">未审核</option><option value="isshow=1">已通过审核</option><option value="isshow=-1">未通过审核</option></optgroup>
	<optgroup label="首页显示"><option value="is_indexshow=1">首页显示</option><option value="is_indexshow=0">首页不显示</option></optgroup>
	<optgroup label="推荐信息">
		<option value="iscommend">所有推荐</option>
		<option value="iscommend=0">未推荐</option>
		<option value="iscommend=1">1 级推荐</option>
		<option value="iscommend=2">2 级推荐</option>
		<option value="iscommend=3">3 级推荐</option>
		<option value="iscommend=4">4 级推荐</option>
		<option value="iscommend=5">5 级推荐</option>
		<option value="iscommend=6">6 级推荐</option>
		<option value="iscommend=7">7 级推荐</option>
		<option value="iscommend=8">8 级推荐</option>
		<option value="iscommend=9">9 级推荐</option>
	</optgroup>
	<optgroup label="头条设置"><option value="isheadlines=1">头条</option><option value="isheadlines=0">非头条</option></optgroup>
	<optgroup label="热门设置"><option value="is_hot=1">热门</option><option value="is_hot=0">非热门</option></optgroup>
	<optgroup label="置顶设置">
		<option value="ontop=0">未置顶</option>
		<option value="ontop">所有置顶</option>
		<option value="ontop=1">1 级设置</option>
		<option value="ontop=2">2 级设置</option>
		<option value="ontop=3">3 级设置</option>
		<option value="ontop=4">4 级设置</option>
		<option value="ontop=5">5 级设置</option>
		<option value="ontop=6">6 级设置</option>
		<option value="ontop=7">7 级设置</option>
		<option value="ontop=8">8 级设置</option>
		<option value="ontop=9">9 级设置</option>
	</optgroup>
</select>
<script type="text/javascript">
$('search[showspecial]').value = '<?php echo $search['showspecial'];?>';
</script>
搜索<?php
//echo form::dropdown('search[showspecial]',array('不限属性','置顶','推荐','头条','未审核','已签发'),$search['showspecial']) . ' ';
echo form::input('search[keyword]',$search['keyword'],'class="input" size="12"').' ';
echo form::dropdown('search[field]',$dbfield,$search['field'],'style="width:80px;"') . ' ';
echo form::dropdown('search[type]',array('模糊','精确'),$search['type']) . ' 排序';
echo form::dropdown('search[myorder]',$dbfield,$search['myorder'],'style="width:80px;"') . ' ';
echo form::dropdown('search[orderby]',array('降序','升序'),$search['orderby']) . ' 每页';
echo form::dropdown('search[limit]',array(''=>'','10'=>'10','20'=>'20','30'=>'30','50'=>'50','100'=>'100','150'=>'150','200'=>'200'),$search['limit']) . ' ';
?>
<input type="submit" value="查询" class="btns" />
</form>
</div></th>
</tr>
</table>
<br />
<table border="0" cellpadding="2" cellspacing="1" align="center" class="tableborder" style="border-bottom:none;">
<tr>
	<th class="td1"><div style="float:left;padding-top:2px;"><input value="栏目设置" onclick="goUrl('<?php echo Myqee::url('class/edit/'.$class['classid']);?>','_blank')" type="button" class="btn"><input value="更新栏目页" onclick="goUrl('<?php echo Myqee::url('task/tohtml/frame?type=class&classid%5B%5D='.$class['classid']);?>','_blank')" type="button" class="btnl"><input value="生成首页" onclick="goUrl('<?php echo Myqee::url('task/tohtml/index');?>','_blank')" type="button" class="btn" /></div>
	<div style="float:right;padding:1px"><input onclick="goUrl('<?php echo Myqee::url('info/add/' .$class['classid'].'/'.$showheader.'?forward='.urlencode($_SERVER['REQUEST_URI'])); ?>')" type="button" value="添加信息" class="bbtn" /></div></th>
</tr>
</table>

<?php echo $db_info_html;?>

<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-top:none;">
<tr>
	<td class="td1" width="20" align="center"><input type="checkbox" title="选择上面全部" onclick="selectbox(this,'select_id')" /></td>
	<td class="td1">
		<input type="button" value="删除" class="btns" onclick="confirm('确认执行删除操作？',null,null,null,function(t){if(t=='ok')submitbox('select_id','<?php echo Myqee::url("info/del/{$class['dbname']}/{{moveclassid}}/[id]/");?>'.replace('{{moveclassid}}',$('moveclassid').value),'hiddenFrame')});" />
		<select size="1" onchange="var thisObj=this;confirm('确认执行此操作？',null,null,null,function(t){if(t=='ok'){if(!submitbox('select_id','<?php echo Myqee::url("info/setvalue/{$class['dbname']}/{{value}}/[id]/");?>'.replace('{{value}}',thisObj.value),'hiddenFrame')){thisObj.value='';}}else{thisObj.value='';}})">
			<option value="">对选中信息进行操作</option>
			<optgroup label="审核信息"><option value="isshow=1">通过审核</option><option value="isshow=-1">审核不通过</option><option value="isshow=0">取消审核</option></optgroup>
			<optgroup label="首页显示"><option value="is_indexshow=1">首页显示</option><option value="is_indexshow=0">首页不显示</option></optgroup>
			<optgroup label="推荐信息">
				<option value="iscommend=0">取消推荐</option>
				<option value="iscommend=1">1 级推荐</option>
				<option value="iscommend=2">2 级推荐</option>
				<option value="iscommend=3">3 级推荐</option>
				<option value="iscommend=4">4 级推荐</option>
				<option value="iscommend=5">5 级推荐</option>
				<option value="iscommend=6">6 级推荐</option>
				<option value="iscommend=7">7 级推荐</option>
				<option value="iscommend=8">8 级推荐</option>
				<option value="iscommend=9">9 级推荐</option>
			</optgroup>
			<optgroup label="头条设置"><option value="isheadlines=1">设为头条</option><option value="isheadlines=0">取消头条</option></optgroup>
			<optgroup label="热门设置"><option value="is_hot=1">设为热门</option><option value="is_hot=0">取消热门</option></optgroup>
			<optgroup label="置顶设置">
				<option value="ontop=0">取消置顶</option>
				<option value="ontop=1">1 级设置</option>
				<option value="ontop=2">2 级设置</option>
				<option value="ontop=3">3 级设置</option>
				<option value="ontop=4">4 级设置</option>
				<option value="ontop=5">5 级设置</option>
				<option value="ontop=6">6 级设置</option>
				<option value="ontop=7">7 级设置</option>
				<option value="ontop=8">8 级设置</option>
				<option value="ontop=9">9 级设置</option>
			</optgroup>
		</select> &nbsp;或&nbsp;
		<?php echo Form::classlist('moveclassid',$classtree,'',0,'移动或复制到以下栏目','color:red');?><input onclick="submitbox('select_id','<?php echo Myqee::url("info/copy/{$class['dbname']}/{{moveclassid}}/[id]/");?>'.replace('{{moveclassid}}',$('moveclassid').value),'hiddenFrame')" type="button" value="复制" class="btns" /><input onclick="submitbox('select_id','<?php echo Myqee::url("info/move/{$class['dbname']}/{{moveclassid}}/[id]/");?>'.replace('{{moveclassid}}',$('moveclassid').value),'hiddenFrame')"  type="button" value="移动" class="btns" />
         &nbsp;或&nbsp; <?php echo form::dropdown('specialid',$specials);?><input onclick="submitbox('select_id','<?php echo Myqee::url("info/copy2special/{$class['dbname']}/{{specialid}}/[id]/");?>'.replace('{{specialid}}',$('specialid').value),'hiddenFrame')" type="button" value="复制" class="btns" />
	</td>
	<td class="td1" width="160" align="center">
		共 <font color="red"><?php echo $total;?></font> 条信息
	</td>
</tr>
</table>

<center><?php echo $pagehtml;?></center>
<?php
if ($showheader == 'fullpage'):
View::factory('admin/footer') -> render(TRUE);
else:
?>
<script type="text/javascript">
myqee();
changeHeight();
</script>
</body>
</html>
<?php
endif;
?>