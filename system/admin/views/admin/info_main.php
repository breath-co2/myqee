<?php
$page_index = 'info';
if ($showheader == 'fullpage'):
	$page_title = '添加信息';
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
			if (objs[i].id=='dbLink_'+id){
				objs[i].style.fontWeight = 'bold';
			}else{
				objs[i].style.fontWeight = 'normal';
			}
		}
	}
}catch(e){}
}
if (self.location.href != parent.location.href){
	setLink('<?php echo $select_dbtable?>');
}
</script>
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
<a href="<?php echo Myqee::url('info/main'),($showheader=='fullpage'?'?fullpage=yes':'');?>">内容管理</a> -&gt; 
<?php
if ($classid>0){
	echo $class['classname']?$class['classname']:'管理信息';
}else{
	echo '数据表管理  -&gt; '.$dbtable[$mydbname];
}
?>
</div>
<div class="clear"></div>
</div>
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
<tr>
	<th class="td1"><div style="float:right;font-weight:normal">
<form method="get" action="<?php echo Myqee::url( 'info/main/1/' . $select_dbtable);?>">
搜索：<?php
echo form::input('search[keyword]',$search['keyword'],'class="input" size="14"').' ';
echo form::dropdown('search[field]',$dbfield,$search['field'],'style="width:120px"') . ' ';
echo form::dropdown('search[type]',array('模糊','精确'),$search['type']) . ' 排序';
echo form::dropdown('search[myorder]',$dbfield,$search['myorder'],'style="width:120px"') . ' ';
echo form::dropdown('search[orderby]',array('降序','升序'),$search['orderby']) . ' 每页';
echo form::dropdown('search[limit]',array('20'=>'20','30'=>'30','50'=>'50','100'=>'100','150'=>'150','200'=>'200'),$search['limit']) . ' ';
?>
<input type="submit" value="搜索" class="btns" />
</form>
</div></th>
</tr>
</table>
<br/>
<table border="0" cellpadding="3" cellspacing="1" align="center" class="tableborder" style="border-bottom:none;">
<tr>
	<th class="td1">
		<?php
			$showclasstree = Passport::getisallow('info.list_showclasstree');
			$showdbtree = Passport::getisallow('info.list_showdbtree');
			if ($showclasstree || $showdbtree):
		?>
		<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr>
			<?php if ($showdbtree):?>
			<td align="left">
				<table border="0" cellpadding="0" cellspacing="0"><tr>
					<td align="right">
						当前数据表：
					</td>
					<td><?php
						echo form::dropdown('theDbName',$dbtable,$select_dbtable,'id="theDbName" onchange="document.location=\''.ADMIN_URLPATH.'info/main/1/\'+this.value'.( $showheader=='fullpage'?'+\'?fullpage=yes\'':'' ).';"');
						?>
					</td>
					<td>
						<input onclick="goUrl('<?php echo ADMIN_URLPATH;?>info/addbydbname/'+$('theDbName').value+'?<?php echo ($showheader=='fullpage')?'fullpage=yes&':'';?>forward='+encodeURIComponent(document.location.href))" type="button" value="添加信息" class="bbtn" />
					</td>
					</tr>
				</table>
			</td>
			<?php
				endif;
			?>
			<?php
			if ($showclasstree):
			?>
			<td align="right">
				<table border="0" cellpadding="0" cellspacing="0"><tr>
					<td>按栏目管理：</td>
					<td><?php echo form::classlist('addInfoSelect',$classtree,'',0,array('请选择栏目'),true);?>&nbsp;</td>
					<td width="80"><input onclick="if ($('addInfoSelect').value>0){goUrl('<?php echo ADMIN_URLPATH;?>info/add/'+$('addInfoSelect').value+'?forward='+encodeURIComponent(document.location.href)+(self.location.href == parent.location.href?'&fullpage=yes':''))}else{alert('请先选择栏目！')}" type="button" value="添加信息" class="bbtn" /></td>
				</tr></table>
			</td>
			<?php
			endif;
			?>
		</tr></table>
		<?php
			else:
			echo '网站信息管理';
			endif;
		?>
</th>
</tr>
</table>

<?php echo $db_info_html;?>

<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-top:none;">
<tr>
	<td class="td1" width="20" align="center"><input type="checkbox" title="选择上面全部" onclick="selectbox(this,'select_id')" /></td>
	<td class="td1">
<?php if (Passport::getisallow('info.del')):?>
		<input type="button" value="删除" class="btns" onclick="confirm('确认执行删除操作？',null,null,null,function(e){if(e=='ok')submitbox('select_id','info/del/<?php echo $select_dbtable;?>/[id]/','hiddenFrame')})" />
<?php
endif;
if (Passport::getisallow('info.setvalue')):?>
		<select size="1" onchange="var thisObj=this;confirm('确认执行此操作？',null,null,null,function(e){if(e=='ok'){if(!submitbox('select_id','info/setvalue/<?php echo $select_dbtable;?>/'+thisObj.value+'/[id]/','hiddenFrame')){thisObj.value='';}}else{thisObj.value='';}});">
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
		</select><?php
endif;
		if ($showclasstree):
		?> &nbsp;或&nbsp;
		<?php echo Form::classlist('moveclassid',$classtree,'',0,'移动或复制到以下栏目','color:red');?><input onclick="submitbox('select_id','info/copy/<?php echo $select_dbtable;?>/'+$('moveclassid').value+'/[id]/','hiddenFrame')" type="button" value="复制" class="btns" /><input onclick="submitbox('select_id','info/move/<?php echo $select_dbtable;?>/'+$('moveclassid').value+'/[id]/','hiddenFrame')"  type="button" value="移动" class="btns" />
	<?php endif;?>
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